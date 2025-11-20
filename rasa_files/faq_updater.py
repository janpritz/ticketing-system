# rasa_files/faq_updater.py
# Flask microservice that accepts POST /batch-update-faqs with FAQ data and generates dynamic actions
#
# Usage:
#   export FAQ_UPDATER_SECRET="your-secret"                # optional (recommended)
#   export RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"  # optional
#   python rasa_files/faq_updater.py
#
# The /batch-update-faqs endpoint expects JSON:
#   {
#     "faqs": [
#       {
#         "id": 1,
#         "intent": "Enrollment Schedule",
#         "description": "Handles queries about enrollment dates.",
#         "response": "The enrollment schedule is...",
#         "response_disabled": false,
#         "sync_type": "update"
#       }
#     ]
#   }
#
# It will:
#  - Store FAQ data in faq_cache.json
#  - Regenerate all action classes in rasa_files/actions.py
#  - Update flow blocks in rasa_files/data/flows/faqs_flow.yml
#  - Optionally restart Rasa actions if RASA_ACTIONS_RESTART_CMD is set
#
# Safety:
#  - Uses file locks (filelock) while writing files.
#  - Optional shared secret verification via X-FAQ-UPDATER-TOKEN header (FAQ_UPDATER_SECRET env var).
#  - Persists FAQ data to disk for reliability.

from flask import Flask, request, jsonify
import os
import re
import sys
import traceback
from filelock import FileLock
from datetime import datetime
import hmac
import subprocess
from pathlib import Path
import json

app = Flask(__name__)

# Paths (relative to this file)
BASE_DIR = Path(__file__).parent
ACTIONS_FILE = BASE_DIR / "actions.py"
FAQS_FLOW_FILE = BASE_DIR / "data/flows/faqs_flow.yml"
FAQ_CACHE_FILE = BASE_DIR / "faq_cache.json"

# Lock suffix and timeout
LOCK_SUFFIX = ".lock"
LOCK_TIMEOUT = 10

# Optional secret for simple verification
FAQ_UPDATER_SECRET = os.environ.get("FAQ_UPDATER_SECRET")

# In-memory FAQ cache
faq_cache = {}

def load_faq_cache():
    """Load FAQ cache from file"""
    global faq_cache
    try:
        if os.path.exists(FAQ_CACHE_FILE):
            with open(FAQ_CACHE_FILE, 'r', encoding='utf-8') as f:
                faq_cache = json.load(f)
            print(f"[faq_updater] Loaded {len(faq_cache)} FAQs from cache")
        else:
            faq_cache = {}
            print("[faq_updater] No cache file found, starting with empty cache")
    except Exception as e:
        print(f"[faq_updater] Error loading FAQ cache: {e}", file=sys.stderr)
        faq_cache = {}

def save_faq_cache():
    """Save FAQ cache to file"""
    try:
        with open(FAQ_CACHE_FILE, 'w', encoding='utf-8') as f:
            json.dump(faq_cache, f, indent=2, ensure_ascii=False)
        print(f"[faq_updater] Saved {len(faq_cache)} FAQs to cache")
    except Exception as e:
        print(f"[faq_updater] Error saving FAQ cache: {e}", file=sys.stderr)

def update_faq_cache(faqs_data):
    """Update FAQ cache with new data"""
    global faq_cache
    updated_count = 0

    for faq in faqs_data:
        faq_id = str(faq.get('id'))
        intent = faq.get('intent', '').strip()

        if intent:
            # Normalize intent for consistent lookup
            intent_norm = normalize_intent(intent)

            faq_cache[intent_norm] = {
                'id': faq_id,
                'intent': intent,
                'description': faq.get('description', ''),
                'response': faq.get('response', ''),
                'response_disabled': faq.get('response_disabled', False),
                'updated_at': datetime.now().isoformat()
            }
            updated_count += 1

    save_faq_cache()
    print(f"[faq_updater] Updated cache with {updated_count} FAQs")
    return updated_count

def normalize_intent(intent: str) -> str:
    """
    Normalizes intent according to rules:
     - lowercase
     - spaces => underscores
     - strip non-alphanumeric/underscore characters
    """
    s = intent.strip().lower()
    s = re.sub(r"\s+", "_", s)
    s = re.sub(r"[^a-z0-9_]", "", s)
    return s

def camel_case(s: str) -> str:
    parts = s.split("_")
    return "".join(p.capitalize() for p in parts if p)

def action_class_name(intent_norm: str) -> str:
    return f"ActionUtter{camel_case(intent_norm)}"

def action_function_name(intent_norm: str) -> str:
    return f"action_utter_{intent_norm}"

def flow_key(intent_norm: str) -> str:
    return f"{intent_norm}_flow"

def append_action_class(intent_norm: str, intent_raw: str) -> bool:
    """
    Appends an action class to actions.py.
    Returns True if appended, False if already exists.
    Generated class uses cached FAQ data instead of database queries.
    """
    cls_name = action_class_name(intent_norm)
    func_name = action_function_name(intent_norm)
    pattern = rf"class\s+{re.escape(cls_name)}\s*\("
    lock_path = str(ACTIONS_FILE) + LOCK_SUFFIX
    os.makedirs(os.path.dirname(str(ACTIONS_FILE)), exist_ok=True)

    # Ensure actions.py exists with the required helpers
    if not os.path.exists(ACTIONS_FILE):
        try:
            with open(ACTIONS_FILE, "w", encoding="utf-8") as f:
                f.write("# actions.py (auto-generated header)\n\n")
                f.write("from typing import Any, Text, Dict, List, Optional\n")
                f.write("import os\n")
                f.write("import json\n")
                f.write("from pathlib import Path\n")
                f.write("from rasa_sdk import Action, Tracker\n")
                f.write("from rasa_sdk.executor import CollectingDispatcher\n\n")
                f.write("# Global FAQ cache\n")
                f.write("faq_cache = {}\n\n")
                f.write("def load_faq_cache():\n")
                f.write("    global faq_cache\n")
                f.write("    cache_file = Path(__file__).parent / 'faq_cache.json'\n")
                f.write("    try:\n")
                f.write("        if cache_file.exists():\n")
                f.write("            with open(cache_file, 'r', encoding='utf-8') as f:\n")
                f.write("                faq_cache = json.load(f)\n")
                f.write("    except Exception:\n")
                f.write("        faq_cache = {}\n\n")
                f.write("# Load cache on import\n")
                f.write("load_faq_cache()\n\n")
                f.write("# Dynamic FAQ actions will be appended below\n\n")
            print(f"[faq_updater] Created new actions file at {ACTIONS_FILE}")
        except Exception as e:
            print(f"[faq_updater] ERROR creating actions.py at {ACTIONS_FILE}: {e}", file=sys.stderr)
            traceback.print_exc()
            raise

    try:
        with FileLock(lock_path, timeout=LOCK_TIMEOUT):
            with open(ACTIONS_FILE, "r+", encoding="utf-8") as f:
                content = f.read()
                if re.search(pattern, content):
                    print(f"[faq_updater] Action class {cls_name} already exists in {ACTIONS_FILE}")
                    return False

                # Prepare class code that uses cached data
                class_code = f"""
class {cls_name}(Action):
    def name(self) -> str:
        return "{func_name}"

    def run(self, dispatcher: CollectingDispatcher,
            tracker: Tracker,
            domain: dict):

        # Load latest FAQ data from cache
        load_faq_cache()

        # Look up FAQ by normalized intent
        faq_data = faq_cache.get("{intent_norm}")

        if not faq_data:
            dispatcher.utter_message(
                text="Sorry, I am not yet trained to answer this question. You can submit a ticket for further assistance."
            )
            return []

        # Check if FAQ is disabled
        if faq_data.get("response_disabled", False):
            dispatcher.utter_message(
                text="Sorry, I am not yet trained to answer this question. You can submit a ticket for further assistance."
            )
            return []

        # Get response
        response = faq_data.get("response", "").strip()
        if not response:
            dispatcher.utter_message(
                text="Sorry, I am not yet trained to answer this question. You can submit a ticket for further assistance."
            )
            return []

        # Send the response
        dispatcher.utter_message(text=response)
        return []
"""
                f.seek(0, os.SEEK_END)
                f.write(class_code)
                print(f"[faq_updater] Appended action class {cls_name} to {ACTIONS_FILE}")
        return True
    except Exception as e:
        print(f"[faq_updater] ERROR appending action class {cls_name}: {e}", file=sys.stderr)
        traceback.print_exc()
        return False

def append_flow(intent_norm: str, description: str) -> bool:
    """
    Appends a flow block to faqs_flow.yml.
    Returns True if appended, False if already exists.
    Tries to preserve existing file indentation:
      - If file contains an indented flow key (under a 'flows:' section), append with same indent.
      - If file contains a top-level 'flows:' key, append under it with 2-space indent.
      - Otherwise append as a top-level flow block.
    """
    # Ensure parent directory exists
    os.makedirs(os.path.dirname(str(FAQS_FLOW_FILE)), exist_ok=True)
    lock_path = str(FAQS_FLOW_FILE) + LOCK_SUFFIX
    try:
        if not os.path.exists(str(FAQS_FLOW_FILE)):
            # create empty file
            with open(str(FAQS_FLOW_FILE), "w", encoding="utf-8") as f:
                f.write("# FAQ flows (auto-appended)\n\n")
            print(f"[faq_updater] Created new flows file at {FAQS_FLOW_FILE}")
    except Exception as e:
        print(f"[faq_updater] ERROR creating flows file {FAQS_FLOW_FILE}: {e}", file=sys.stderr)
        traceback.print_exc()
        return False

    key = flow_key(intent_norm)
    try:
        with FileLock(lock_path, timeout=LOCK_TIMEOUT):
            with open(str(FAQS_FLOW_FILE), "r+", encoding="utf-8") as f:
                content = f.read()

                # If the exact key exists anywhere (top-level or indented), skip
                if re.search(rf"^\s*{re.escape(key)}\s*:", content, flags=re.MULTILINE):
                    print(f"[faq_updater] Flow {key} already exists in {FAQS_FLOW_FILE}")
                    return False

                # Detect indentation style:
                # 1) look for an indented flow key (e.g., "  appendix_f_flow:")
                m = re.search(r"^(\s+)[a-z0-9_]+_flow\s*:", content, flags=re.MULTILINE)
                if m:
                    indent = m.group(1)
                    print(f"[faq_updater] Detected indented flow style (indent={len(indent)} spaces)")
                else:
                    # 2) detect a top-level 'flows:' section
                    has_flows_section = bool(re.search(r'^\s*flows\s*:\s*$', content, flags=re.MULTILINE))
                    if has_flows_section:
                        indent = "  "  # default 2-space indent under flows:
                        print("[faq_updater] Detected 'flows:' section; will append under it with 2-space indent")
                    else:
                        indent = None
                        print("[faq_updater] No flows section detected; will append top-level flow")

                desc_single = description.replace("\n", " ").replace(":", "\\:")

                if indent is not None:
                    # Append under flows: (indented block)
                    flow_block = f"\n{indent}{key}:\n{indent}  description: {desc_single}\n{indent}  steps:\n{indent}    - action: {action_function_name(intent_norm)}\n"
                else:
                    # Append as top-level flow
                    flow_block = f"\n{key}:\n  description: {desc_single}\n  steps:\n    - action: {action_function_name(intent_norm)}\n"

                f.seek(0, os.SEEK_END)
                f.write(flow_block)
                print(f"[faq_updater] Appended flow {key} to {FAQS_FLOW_FILE} (indent={'top' if indent is None else len(indent)})")
        return True
    except Exception as e:
        print(f"[faq_updater] ERROR appending flow {key}: {e}", file=sys.stderr)
        traceback.print_exc()
        return False

def verify_secret(req) -> bool:
    """
    Verifies the request using a simple token header if FAQ_UPDATER_SECRET is set.
    Header: X-FAQ-UPDATER-TOKEN
    Prints header for debugging (but avoids printing secret value).
    """
    if not FAQ_UPDATER_SECRET:
        print("[faq_updater] No FAQ_UPDATER_SECRET configured — accepting requests without token")
        return True
    token = req.headers.get("X-FAQ-UPDATER-TOKEN", "")
    if not token:
        print("[faq_updater] Missing X-FAQ-UPDATER-TOKEN header")
        return False
    # Debug: do not print token value directly; just show presence and length
    print(f"[faq_updater] Received token header of length {len(token)}")
    return hmac.compare_digest(token, FAQ_UPDATER_SECRET)

@app.route("/batch-update-faqs", methods=["POST"])
def batch_update_faqs():
    """
    Batch update multiple FAQs in a single request.
    Expects JSON: { "faqs": [{"id": 1, "intent": "...", "description": "...", "response": "...", "response_disabled": false, "sync_type": "update"}, ...] }
    Stores FAQs in cache and regenerates action classes.
    Returns: { "ok": true, "results": [{"faq_id": 1, "success": true, "intent": "..."}, ...] }
    """
    try:
        print("[faq_updater] /batch-update-faqs called")

        if not verify_secret(request):
            print("[faq_updater] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        faqs = data.get("faqs", [])

        if not isinstance(faqs, list):
            return jsonify({"ok": False, "error": "faqs must be an array"}), 400

        print(f"[faq_updater] Processing batch of {len(faqs)} FAQs")

        # Update cache with all FAQ data
        updated_count = update_faq_cache(faqs)

        # Regenerate all action classes based on current cache
        regenerate_all_actions()

        results = []
        for faq_data in faqs:
            try:
                intent = faq_data.get("intent")
                faq_id = faq_data.get("id")
                sync_type = faq_data.get("sync_type", "update")

                if not intent:
                    results.append({
                        "faq_id": faq_id,
                        "success": False,
                        "error": "intent required"
                    })
                    continue

                intent_norm = normalize_intent(intent)

                if sync_type == "delete":
                    # Remove from cache
                    if intent_norm in faq_cache:
                        del faq_cache[intent_norm]
                        save_faq_cache()
                    results.append({
                        "faq_id": faq_id,
                        "success": True,
                        "intent": intent,
                        "note": "removed from cache"
                    })
                else:
                    results.append({
                        "faq_id": faq_id,
                        "success": True,
                        "intent": intent,
                        "intent_normalized": intent_norm
                    })

            except Exception as e:
                print(f"[faq_updater] Error processing FAQ {faq_data.get('id')}: {e}", file=sys.stderr)
                traceback.print_exc()
                results.append({
                    "faq_id": faq_data.get("id"),
                    "success": False,
                    "error": str(e)
                })

        successful = sum(1 for r in results if r.get("success"))
        print(f"[faq_updater] Batch complete: {successful}/{len(results)} successful")

        return jsonify({
            "ok": True,
            "results": results,
            "summary": {
                "total": len(results),
                "successful": successful,
                "failed": len(results) - successful,
                "cached_faqs": len(faq_cache)
            }
        })

    except Exception as e:
        print(f"[faq_updater] Unexpected error in /batch-update-faqs: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

def regenerate_all_actions():
    """Regenerate all action classes based on current FAQ cache"""
    try:
        print("[faq_updater] Regenerating all action classes...")

        # Clear existing actions file and recreate header
        if os.path.exists(ACTIONS_FILE):
            os.remove(ACTIONS_FILE)

        # Ensure actions.py exists with the required helpers
        os.makedirs(os.path.dirname(str(ACTIONS_FILE)), exist_ok=True)
        with open(ACTIONS_FILE, "w", encoding="utf-8") as f:
            f.write("# actions.py (auto-generated header)\n\n")
            f.write("from typing import Any, Text, Dict, List, Optional\n")
            f.write("import os\n")
            f.write("import json\n")
            f.write("from pathlib import Path\n")
            f.write("from rasa_sdk import Action, Tracker\n")
            f.write("from rasa_sdk.executor import CollectingDispatcher\n\n")
            f.write("# Global FAQ cache\n")
            f.write("faq_cache = {}\n\n")
            f.write("def load_faq_cache():\n")
            f.write("    global faq_cache\n")
            f.write("    cache_file = Path(__file__).parent / 'faq_cache.json'\n")
            f.write("    try:\n")
            f.write("        if cache_file.exists():\n")
            f.write("            with open(cache_file, 'r', encoding='utf-8') as f:\n")
            f.write("                faq_cache = json.load(f)\n")
            f.write("    except Exception:\n")
            f.write("        faq_cache = {}\n\n")
            f.write("# Load cache on import\n")
            f.write("load_faq_cache()\n\n")
            f.write("# Dynamic FAQ actions will be appended below\n\n")

        # Generate action class for each FAQ in cache
        for intent_norm, faq_data in faq_cache.items():
            intent_raw = faq_data.get('intent', intent_norm)
            append_action_class(intent_norm, intent_raw)

        print(f"[faq_updater] Regenerated {len(faq_cache)} action classes")

    except Exception as e:
        print(f"[faq_updater] Error regenerating actions: {e}", file=sys.stderr)
        traceback.print_exc()

@app.route("/update-faq", methods=["POST"])
def update_faq():
    try:
        print("[faq_updater] /update-faq called")
        # Print headers for debugging (avoid printing secret value)
        for k, v in request.headers.items():
            if k.lower() == "x-faq-updater-token":
                print(f"[faq_updater] Header {k}: <HIDDEN token length {len(v)}>")
            else:
                print(f"[faq_updater] Header {k}: {v}")
        if not verify_secret(request):
            print("[faq_updater] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        print(f"[faq_updater] Payload: {data}")

        intent = data.get("intent")
        description = data.get("description", "") or ""
        if not intent:
            print("[faq_updater] Missing 'intent' in payload")
            return jsonify({"ok": False, "error": "intent required"}), 400

        intent_norm = normalize_intent(intent)
        print(f"[faq_updater] Normalized intent: {intent_norm}")

        # Attempt to append action and flow, capture errors separately
        try:
            action_appended = append_action_class(intent_norm, intent)
        except Exception as e:
            print(f"[faq_updater] Exception while appending action for {intent_norm}: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": "action_append_failed", "details": str(e)}), 500

        try:
            flow_appended = append_flow(intent_norm, description)
        except Exception as e:
            print(f"[faq_updater] Exception while appending flow for {intent_norm}: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": "flow_append_failed", "details": str(e)}), 500

        # Optionally trigger a restart command (non-blocking) if requested or env var set
        restart_flag = data.get("restart_actions", False)
        if restart_flag:
            cmd = os.environ.get("RASA_ACTIONS_RESTART_CMD")
            if cmd:
                try:
                    print(f"[faq_updater] Spawning restart command: {cmd}")
                    subprocess.Popen(cmd, shell=True)
                except Exception as e:
                    print(f"[faq_updater] Failed to spawn restart command: {e}", file=sys.stderr)
                    traceback.print_exc()

        result = {
            "ok": True,
            "intent": intent,
            "intent_normalized": intent_norm,
            "action_appended": action_appended,
            "flow_appended": flow_appended
        }
        print(f"[faq_updater] Result: {result}")
        return jsonify(result)
    except Exception as e:
        print(f"[faq_updater] Unexpected error in /update-faq: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

if __name__ == "__main__":
    # Load FAQ cache on startup
    load_faq_cache()

    port = int(os.environ.get("FAQ_UPDATER_PORT", 5001))
    print(f"[faq_updater] Starting server on port {port} with {len(faq_cache)} cached FAQs")
    app.run(host="0.0.0.0", port=port)
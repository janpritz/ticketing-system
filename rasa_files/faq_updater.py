# rasa_files/faq_updater.py
# Flask microservice that accepts POST /sync-faqs with FAQ data and generates dynamic actions
#
# Usage:
#   export FAQ_UPDATER_SECRET="your-secret"                # optional (recommended)
#   export RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"  # optional
#   python rasa_files/faq_updater.py
#
# The /sync-faqs endpoint expects JSON:
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
from flask_cors import CORS
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
from dotenv import load_dotenv

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Paths (relative to this file)
BASE_DIR = Path(__file__).parent
PROJECT_ROOT = BASE_DIR.parent
ACTIONS_FILE = BASE_DIR / "actions.py"
FAQS_FLOW_FILE = BASE_DIR / "data/flows/faqs_flow.yml"
FAQS_TXT_FILE = PROJECT_ROOT / "docs" / "faqs.txt"
# Load environment variables from .env file
load_dotenv(BASE_DIR.parent / '.env')

# Lock suffix and timeout
LOCK_SUFFIX = ".lock"
LOCK_TIMEOUT = 10

# Optional secret for simple verification
FAQ_UPDATER_SECRET = os.environ.get("FAQ_UPDATER_SECRET")


def save_faqs_as_txt(faqs: list):
    """Convert FAQs to text format and save to docs/faqs.txt"""
    try:
        # Ensure docs directory exists
        FAQS_TXT_FILE.parent.mkdir(parents=True, exist_ok=True)
        
        lines = []
        lines.append("=" * 80)
        lines.append("FAQ DATABASE - TEXT FORMAT")
        lines.append(f"Generated: {datetime.now().isoformat()}")
        lines.append(f"Total FAQs: {len(faqs)}")
        lines.append("=" * 80)
        lines.append("")
        
        for faq in faqs:
            intent = faq.get('intent', 'Unknown')
            response = faq.get('response', '')
            description = faq.get('description', '')
            faq_id = faq.get('id', 'N/A')
            
            lines.append("-" * 80)
            lines.append(f"ID: {faq_id}")
            lines.append(f"INTENT: {intent}")
            if description:
                lines.append(f"DESCRIPTION: {description}")
            lines.append("")
            lines.append("RESPONSE:")
            lines.append(response)
            lines.append("")
        
        lines.append("-" * 80)
        lines.append("END OF FAQ DATABASE")
        lines.append("=" * 80)
        
        with open(FAQS_TXT_FILE, 'w', encoding='utf-8') as f:
            f.write('\n'.join(lines))
        
        print(f"[faq_updater] Saved {len(faqs)} FAQs to {FAQS_TXT_FILE}")
        return True
    except Exception as e:
        print(f"[faq_updater] Error saving FAQs to txt: {e}", file=sys.stderr)
        traceback.print_exc()
        return False


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
                f.write("from rasa_sdk import Action, Tracker\n")
                f.write("from rasa_sdk.executor import CollectingDispatcher\n\n")
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

                # Prepare class code
                class_code = f"""
class {cls_name}(Action):
    def name(self) -> str:
        return "{func_name}"

    def run(self, dispatcher: CollectingDispatcher,
            tracker: Tracker,
            domain: dict):

        # Placeholder response - FAQ data will be loaded from database
        dispatcher.utter_message(
            text="FAQ response for {intent_raw}"
        )
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

@app.route("/sync-faqs", methods=["POST"])
def sync_faqs():
    """
    Sync multiple FAQs in a single request.
    Expects JSON: { "faqs": [{"id": 1, "intent": "...", "description": "...", "response": "...", "response_disabled": false, "status": "..."}, ...] }
    Saves ALL FAQs to database/faqs.json (including disabled ones).
    Returns: { "ok": true, "count": X, "message": "..." }
    """
    try:
        print("[faq_sync] /sync-faqs endpoint called")

        if not verify_secret(request):
            print("[faq_sync] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        faqs = data.get("faqs", [])

        if not isinstance(faqs, list):
            print("[faq_sync] ERROR: faqs must be an array")
            return jsonify({"ok": False, "error": "faqs must be an array"}), 400

        print(f"[faq_sync] Received {len(faqs)} FAQs to sync")

        # Save ALL FAQs to database/faqs.json (no filtering)
        database_dir = BASE_DIR / "database"
        database_dir.mkdir(parents=True, exist_ok=True)
        faqs_json_path = database_dir / "faqs.json"

        # Clear existing content first to ensure fresh data
        try:
            if faqs_json_path.exists():
                faqs_json_path.unlink()
                print(f"[faq_sync] Cleared existing {faqs_json_path}")
        except Exception as e:
            print(f"[faq_sync] WARNING: Could not clear existing faqs.json: {e}", file=sys.stderr)

        # Prepare data structure for storage
        faqs_data = {
            "faqs": faqs,
            "last_synced": datetime.now().isoformat(),
            "total_count": len(faqs)
        }

        # Write fresh data to database/faqs.json
        try:
            with open(faqs_json_path, 'w', encoding='utf-8') as f:
                json.dump(faqs_data, f, indent=2, ensure_ascii=False)
            print(f"[faq_sync] Successfully saved {len(faqs)} FAQs to {faqs_json_path}")
        except Exception as e:
            print(f"[faq_sync] ERROR saving to faqs.json: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": f"Failed to save FAQs: {str(e)}"}), 500

        # Also save to docs/faqs.txt
        save_faqs_as_txt(faqs)


        print(f"[faq_sync] Sync complete: {len(faqs)} FAQs saved")

        return jsonify({
            "ok": True,
            "count": len(faqs),
            "message": f"Successfully synced {len(faqs)} FAQs",
            "summary": {
                "total": len(faqs),
                "successful": len(faqs),
                "failed": 0
            }
        })

    except Exception as e:
        print(f"[faq_sync] Unexpected error in /sync-faqs: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

def regenerate_all_actions():
    """Regenerate all action classes based on FAQs"""
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
            f.write("from rasa_sdk import Action, Tracker\n")
            f.write("from rasa_sdk.executor import CollectingDispatcher\n\n")
            f.write("# Dynamic FAQ actions will be appended below\n\n")

        print("[faq_updater] Regenerated action classes")

    except Exception as e:
        print(f"[faq_updater] Error regenerating actions: {e}", file=sys.stderr)
        traceback.print_exc()

@app.route("/upload-file", methods=["POST"])
def upload_file():
    """
    Upload a text file and save it to docs/ directory.
    Expects JSON: { "file_content": "...", "file_name": "...", "file_type": "..." }
    Returns: { "ok": true, "message": "..." }
    """
    try:
        print("[file_upload] /upload-file endpoint called")

        if not verify_secret(request):
            print("[file_upload] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        file_content = data.get("file_content", "")
        file_name = data.get("file_name", "")
        file_type = data.get("file_type", "")

        if not file_content:
            print("[file_upload] ERROR: file_content is required")
            return jsonify({"ok": False, "error": "file_content is required"}), 400

        print(f"[file_upload] Received file: {file_name} ({file_type}) with {len(file_content)} characters")

        # Save file to docs/ directory
        docs_dir = PROJECT_ROOT / "docs"
        docs_dir.mkdir(parents=True, exist_ok=True)

        file_path = docs_dir / file_name
        try:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(file_content)
            print(f"[file_upload] Successfully saved file to {file_path}")
        except Exception as e:
            print(f"[file_upload] ERROR saving file: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": f"Failed to save file: {str(e)}"}), 500

        return jsonify({
            "ok": True,
            "message": f"Successfully uploaded file {file_name}",
            "file_path": str(file_path)
        })

    except Exception as e:
        print(f"[file_upload] Unexpected error in /upload-file: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

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
    port = int(os.environ.get("FAQ_UPDATER_PORT", 5001))
    print(f"[faq_updater] Starting server on port {port}")
    app.run(host="0.0.0.0", port=port)
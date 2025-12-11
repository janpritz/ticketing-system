# programs/faq_updater.py
# Flask microservice that accepts POST /sync-faqs with FAQ data and generates dynamic actions
#
# Usage:
#   export FAQ_UPDATER_SECRET="your-secret"                # optional (recommended)
#   export RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"  # optional
#   python programs/faq_updater.py
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
#  - Store FAQ data in database/faqs.json
#  - Generate dynamic action classes in programs/actions.py
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
# Enable CORS for all routes with explicit configuration
CORS(app,
     origins=["*"],
     methods=["GET", "POST", "PUT", "DELETE", "OPTIONS"],
     allow_headers=["Content-Type", "Authorization", "X-FAQ-UPDATER-TOKEN", "X-Requested-With"],
     expose_headers=["Content-Type"],
     supports_credentials=False)

# Paths (relative to this file)
BASE_DIR = Path(__file__).parent
PROJECT_ROOT = BASE_DIR.parent
ACTIONS_FILE = BASE_DIR / "actions.py"
FAQS_TXT_FILE = PROJECT_ROOT / "docs" / "faqs.txt"
# Load environment variables from .env file
load_dotenv(BASE_DIR.parent / '.env')

# Lock suffix and timeout
LOCK_SUFFIX = ".lock"
LOCK_TIMEOUT = 10

FAQ_UPDATER_SECRET = os.environ.get("FAQ_UPDATER_SECRET")

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

def normalize_intent(intent: str) -> str:
    """Normalize intent name for consistent usage"""
    return re.sub(r'[^a-zA-Z0-9]+', '_', intent.strip().lower()).strip('_')

def save_faqs_as_txt(faqs: list) -> None:
    """Save FAQs to a text file for documentation purposes"""
    try:
        with open(FAQS_TXT_FILE, 'w', encoding='utf-8') as f:
            f.write("# FAQs Documentation\n\n")
            for faq in faqs:
                f.write(f"## {faq.get('intent', 'Unknown Intent')}\n")
                f.write(f"Description: {faq.get('description', 'No description')}\n")
                f.write(f"Response: {faq.get('response', 'No response')}\n\n")
        print(f"[faq_sync] Saved FAQs documentation to {FAQS_TXT_FILE}")
    except Exception as e:
        print(f"[faq_sync] WARNING: Failed to save FAQs as txt: {e}", file=sys.stderr)

def append_action_class(intent_norm: str, intent_raw: str) -> bool:
    """Append a dynamic action class to actions.py"""
    try:
        action_class_name = f"ActionFaq{intent_norm.title().replace('_', '')}"
        
        # Create action class content
        action_content = f'''
class {action_class_name}(Action):
    def name(self) -> Text:
        return "action_faq_{intent_norm}"

    async def run(
        self,
        dispatcher: CollectingDispatcher,
        tracker: Tracker,
        domain: Dict[Text, Any],
    ) -> List[Dict[Text, Any]]:
        try:
            # Load FAQ data from database/faqs.json
            faqs_file = Path(__file__).parent / "database" / "faqs.json"
            response = "Sorry, I don't have information about that."
            
            if faqs_file.exists():
                try:
                    with open(faqs_file, 'r', encoding='utf-8') as f:
                        faqs_data = json.load(f)
                        faqs = faqs_data.get("faqs", [])
                        
                    # Find FAQ for this intent
                    for faq in faqs:
                        if normalize_intent(faq.get("intent", "")) == "{intent_norm}":
                            response = faq.get("response", "Sorry, I don't have information about that.")
                            break
                except Exception:
                    pass
            
            dispatcher.utter_message(text=response)
            return []
        except Exception as e:
            dispatcher.utter_message(text="Sorry, I encountered an error while processing your request.")
            return []

'''
        
        # Append to actions file
        with open(ACTIONS_FILE, 'a', encoding='utf-8') as f:
            f.write(action_content)
        
        print(f"[faq_updater] Added action class {action_class_name} for intent '{intent_raw}'")
        return True
    except Exception as e:
        print(f"[faq_updater] Failed to append action class: {e}", file=sys.stderr)
        return False

def append_flow(intent_norm: str, description: str) -> bool:
    """Append a flow block to the FAQ flows file"""
    try:
        flow_content = f'''
  - intent: {intent_norm}
    description: {description}
    action: action_faq_{intent_norm}
'''
        
        # Create flows directory if it doesn't exist
        flows_dir = BASE_DIR / "data" / "flows"
        flows_dir.mkdir(parents=True, exist_ok=True)
        
        flows_file = flows_dir / "faqs_flow.yml"
        
        # Append flow to file
        with open(flows_file, 'a', encoding='utf-8') as f:
            f.write(flow_content)
        
        print(f"[faq_updater] Added flow for intent '{intent_norm}'")
        return True
    except Exception as e:
        print(f"[faq_updater] Failed to append flow: {e}", file=sys.stderr)
        return False

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

@app.route("/list-docs", methods=["GET"])
def list_docs():
    """
    List text files in the docs/ directory.
    Returns: { "ok": true, "files": [{"name": "...", "size": 123, "modified": "..."}, ...] }
    """
    try:
        print("[list_docs] /list-docs endpoint called")
        print(f"[list_docs] Request method: {request.method}")
        print(f"[list_docs] Request headers: {dict(request.headers)}")

        # Debug: Print all environment variables related to FAQ
        print(f"[list_docs] FAQ_UPDATER_SECRET: {FAQ_UPDATER_SECRET is not None}")
        print(f"[list_docs] PROJECT_ROOT: {PROJECT_ROOT}")
        print(f"[list_docs] Docs directory path: {PROJECT_ROOT / 'docs'}")

        if not verify_secret(request):
            print("[list_docs] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        print("[list_docs] Secret verification passed")

        docs_dir = PROJECT_ROOT / "docs"
        print(f"[list_docs] Docs directory exists: {docs_dir.exists()}")

        docs_dir.mkdir(parents=True, exist_ok=True)  # Ensure directory exists
        print(f"[list_docs] Ensured docs directory exists")

        files = []
        print(f"[list_docs] Scanning directory: {docs_dir}")
        for file_path in docs_dir.iterdir():
            print(f"[list_docs] Found item: {file_path.name} (is_file: {file_path.is_file()}, suffix: {file_path.suffix.lower()})")
            if file_path.is_file() and file_path.suffix.lower() in ['.txt', '.md']:
                try:
                    stat = file_path.stat()
                    files.append({
                        "name": file_path.name,
                        "size": stat.st_size,
                        "modified": datetime.fromtimestamp(stat.st_mtime).isoformat()
                    })
                except Exception as e:
                    print(f"[list_docs] Error getting info for {file_path}: {e}", file=sys.stderr)

        print(f"[list_docs] Found {len(files)} text files in docs directory")

        # Debug: Print detailed file information
        if files:
            print("[list_docs] File details:")
            for i, file in enumerate(files, 1):
                print(f"  {i}. {file['name']} - Size: {file['size']} bytes, Modified: {file['modified']}")
        else:
            print("[list_docs] No text files found in docs directory")

        result = {
            "ok": True,
            "files": files,
            "count": len(files)
        }
        print(f"[list_docs] Returning result: {result}")
        return jsonify(result)

    except Exception as e:
        print(f"[list_docs] Unexpected error in /list-docs: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/download/<filename>", methods=["GET"])
def download_file(filename):
    """
    Download a specific file from the docs/ directory.
    Returns the file content as text.
    Accepts token via header (X-FAQ-UPDATER-TOKEN) or query parameter (token).
    """
    try:
        print(f"[download] /download/{filename} called")

        # Check for token in header first, then query parameter
        token = request.headers.get("X-FAQ-UPDATER-TOKEN") or request.args.get("token")
        if not token:
            print("[download] Missing X-FAQ-UPDATER-TOKEN header or token query parameter")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Verify the token manually since verify_secret expects it in headers
        if FAQ_UPDATER_SECRET and not hmac.compare_digest(token, FAQ_UPDATER_SECRET):
            print("[download] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        print(f"[download] Authentication successful")

        docs_dir = PROJECT_ROOT / "docs"
        file_path = docs_dir / filename

        # Security check: ensure file is within docs directory and has allowed extension
        if not file_path.is_file() or file_path.parent != docs_dir:
            print(f"[download] File not found or not allowed: {filename}")
            return jsonify({"ok": False, "error": "File not found"}), 404

        if file_path.suffix.lower() not in ['.txt', '.md']:
            print(f"[download] File type not allowed: {filename}")
            return jsonify({"ok": False, "error": "File type not allowed"}), 403

        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            print(f"[download] Successfully read file: {filename} ({len(content)} characters)")
            return content, 200, {'Content-Type': 'text/plain; charset=utf-8'}
        except Exception as e:
            print(f"[download] Error reading file {filename}: {e}", file=sys.stderr)
            return jsonify({"ok": False, "error": "Error reading file"}), 500

    except Exception as e:
        print(f"[download] Unexpected error in /download/{filename}: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/update-document", methods=["POST", "OPTIONS"])
def update_document():
    """
    Update the content of a document in the docs/ directory.
    Expects JSON: { "file_name": "...", "file_content": "...", "file_type": "..." }
    Returns: { "ok": true, "message": "...", "file_path": "..." }
    Accepts token via header (X-FAQ-UPDATER-TOKEN) or query parameter (token).
    """
    # Handle preflight OPTIONS request
    if request.method == "OPTIONS":
        response = jsonify({"status": "OK"})
        response.headers.add("Access-Control-Allow-Origin", "*")
        response.headers.add("Access-Control-Allow-Headers", "Content-Type, Authorization, X-FAQ-UPDATER-TOKEN, X-Requested-With")
        response.headers.add("Access-Control-Allow-Methods", "POST, OPTIONS")
        return response

    try:
        print("[update_document] /update-document endpoint called")

        # Check for token in header first, then query parameter
        token = request.headers.get("X-FAQ-UPDATER-TOKEN") or request.args.get("token")
        if not token:
            print("[update_document] Missing X-FAQ-UPDATER-TOKEN header or token query parameter")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Verify the token manually since verify_secret expects it in headers
        if FAQ_UPDATER_SECRET and not hmac.compare_digest(token, FAQ_UPDATER_SECRET):
            print("[update_document] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        print("[update_document] Authentication successful")

        data = request.get_json(force=True)
        file_name = data.get("file_name", "")
        file_content = data.get("file_content", "")
        file_type = data.get("file_type", "")

        if not file_name:
            print("[update_document] ERROR: file_name is required")
            return jsonify({"ok": False, "error": "file_name is required"}), 400

        if file_content is None:
            print("[update_document] ERROR: file_content is required")
            return jsonify({"ok": False, "error": "file_content is required"}), 400

        print(f"[update_document] Received update request for: {file_name} ({file_type}) with {len(file_content)} characters")

        # Validate file extension
        if not file_name.lower().endswith(('.txt', '.md')):
            print(f"[update_document] ERROR: File type not allowed: {file_name}")
            return jsonify({"ok": False, "error": "Only .txt and .md files can be edited"}), 403

        # Check content size limit (1MB)
        if len(file_content) > 1024 * 1024:
            print(f"[update_document] ERROR: File content too large: {len(file_content)} bytes")
            return jsonify({"ok": False, "error": "File content too large (max 1MB)"}), 413

        docs_dir = PROJECT_ROOT / "docs"
        docs_dir.mkdir(parents=True, exist_ok=True)
        file_path = docs_dir / file_name

        # Security check: ensure file is within docs directory
        if file_path.parent != docs_dir:
            print(f"[update_document] ERROR: Invalid file path: {file_name}")
            return jsonify({"ok": False, "error": "Invalid file path"}), 403

        # Check if file exists
        if not file_path.exists():
            print(f"[update_document] ERROR: File does not exist: {file_name}")
            return jsonify({"ok": False, "error": "File does not exist"}), 404

        # Create backup of original file (optional)
        backup_path = file_path.with_suffix(file_path.suffix + '.backup')
        try:
            import shutil
            shutil.copy2(file_path, backup_path)
            print(f"[update_document] Created backup: {backup_path}")
        except Exception as e:
            print(f"[update_document] WARNING: Could not create backup: {e}")

        # Write new content to file
        try:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(file_content)
            print(f"[update_document] Successfully updated file: {file_path}")
        except Exception as e:
            print(f"[update_document] ERROR saving file: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": f"Failed to save file: {str(e)}"}), 500

        return jsonify({
            "ok": True,
            "message": f"Successfully updated document {file_name}",
            "file_path": str(file_path),
            "backup_path": str(backup_path) if backup_path.exists() else None
        })

    except Exception as e:
        print(f"[update_document] Unexpected error in /update-document: {e}", file=sys.stderr)
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

@app.route("/upload-file", methods=["POST", "OPTIONS"])
@app.route("/sync-faqs", methods=["POST", "OPTIONS"])
@app.route("/update-faq", methods=["POST", "OPTIONS"])
@app.route("/update-document", methods=["POST", "OPTIONS"])
@app.route("/list-docs", methods=["GET", "OPTIONS"])
@app.route("/download/<filename>", methods=["GET", "OPTIONS"])
def handle_preflight():
    """Handle preflight CORS requests"""
    if request.method == "OPTIONS":
        response = jsonify({"status": "OK"})
        response.headers.add("Access-Control-Allow-Origin", "*")
        response.headers.add("Access-Control-Allow-Headers", "Content-Type, Authorization, X-FAQ-UPDATER-TOKEN, X-Requested-With")
        response.headers.add("Access-Control-Allow-Methods", "POST, GET, OPTIONS")
        return response
    else:
        # This should not be reached as Flask will route to the appropriate function
        return jsonify({"error": "Method not allowed"}), 405

if __name__ == "__main__":
    port = int(os.environ.get("FAQ_UPDATER_PORT", 5001))
    print(f"[faq_updater] Starting server on port {port}")
    app.run(host="0.0.0.0", port=port)
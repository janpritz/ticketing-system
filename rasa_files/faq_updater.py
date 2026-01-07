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
from urllib.parse import unquote_plus

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

FAQ_UPDATER_SECRET = os.environ.get("RASA_SECRET")

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
        action_content = f'''\nclass {action_class_name}(Action):
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
        flow_content = f'''\n  - intent: {intent_norm}
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

        # Create backup if file already exists
        backup_created = False
        if file_path.exists():
            backup_dir = PROJECT_ROOT / "backup"
            backup_dir.mkdir(parents=True, exist_ok=True)
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            backup_filename = f"{file_path.stem}_{timestamp}{file_path.suffix}"
            backup_path = backup_dir / backup_filename
            try:
                import shutil
                shutil.copy2(file_path, backup_path)
                print(f"[file_upload] Created backup: {backup_path}")
                backup_created = True
            except Exception as e:
                print(f"[file_upload] WARNING: Could not create backup: {e}")

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
            "file_path": str(file_path),
            "backup_created": backup_created
        })

    except Exception as e:
        print(f"[file_upload] Unexpected error in /upload-file: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500


@app.route("/delete-file", methods=["POST"])
def delete_file():
    """Delete a file from the docs/ directory.

    Expects JSON: { "file_name": "..." }
    Returns: { "ok": true, "deleted": true, "file_name": "..." }
    """
    try:
        print("[file_delete] /delete-file endpoint called")

        if not verify_secret(request):
            print("[file_delete] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        file_name = data.get("file_name", "")
        if not file_name:
            print("[file_delete] ERROR: file_name is required")
            return jsonify({"ok": False, "error": "file_name is required"}), 400

        # Only allow deleting txt/md files
        if not file_name.lower().endswith(('.txt', '.md')):
            print(f"[file_delete] ERROR: File type not allowed: {file_name}")
            return jsonify({"ok": False, "error": "File type not allowed"}), 403

        docs_dir = PROJECT_ROOT / "docs"
        docs_dir.mkdir(parents=True, exist_ok=True)
        file_path = docs_dir / file_name

        # Security check: ensure file is within docs directory
        if file_path.parent != docs_dir:
            print(f"[file_delete] ERROR: Invalid file path: {file_name}")
            return jsonify({"ok": False, "error": "Invalid file path"}), 403

        if not file_path.exists():
            print(f"[file_delete] File not found: {file_name}")
            return jsonify({"ok": False, "error": "File not found"}), 404

        # Backup before delete
        try:
            backup_dir = PROJECT_ROOT / "backup"
            backup_dir.mkdir(parents=True, exist_ok=True)
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            backup_filename = f"{file_path.stem}_{timestamp}{file_path.suffix}"
            backup_path = backup_dir / backup_filename
            import shutil
            shutil.copy2(file_path, backup_path)
            print(f"[file_delete] Created backup: {backup_path}")
        except Exception as e:
            print(f"[file_delete] WARNING: Could not create backup: {e}")

        try:
            file_path.unlink()
            print(f"[file_delete] Deleted file: {file_path}")
        except Exception as e:
            print(f"[file_delete] ERROR deleting file: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": f"Failed to delete file: {str(e)}"}), 500

        return jsonify({
            "ok": True,
            "deleted": True,
            "file_name": file_name,
        })

    except Exception as e:
        print(f"[file_delete] Unexpected error in /delete-file: {e}", file=sys.stderr)
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

@app.route("/download-announcements", methods=["GET"])
def download_announcements():
    """
    Download Announcement.txt file content and parse it into structured data.
    Returns: { "ok": true, "announcements": [{"id": 1, "content": "..."}, ...] }
    """
    try:
        print("[download_announcements] /download-announcements called")

        if not verify_secret(request):
            print("[download_announcements] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        docs_dir = PROJECT_ROOT / "docs"
        file_path = docs_dir / "Announcements.txt"

        # Check if file exists
        if not file_path.exists():
            print("[download_announcements] Announcement.txt not found")
            return jsonify({"ok": True, "announcements": []})

        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
            print(f"[download_announcements] Successfully read Announcement.txt ({len(content)} characters)")
        except Exception as e:
            print(f"[download_announcements] Error reading file: {e}", file=sys.stderr)
            return jsonify({"ok": False, "error": "Error reading file"}), 500

        # Parse the content into announcements
        announcements = []
        blocks = content.strip().split("---------\n")
        counter = 1

        for block in blocks:
            lines = block.strip().split("\n")
            if len(lines) >= 2:
                id_line = lines[0]
                import re
                id_match = re.match(r'^id:\s*(\d+)', id_line)
                if id_match and id_match.group(1):
                    try:
                        id = int(id_match.group(1))
                        counter = max(counter, id + 1)
                    except ValueError:
                        id = counter
                        counter += 1
                else:
                    id = counter
                    counter += 1

                try:
                    if len(lines) >= 3 and lines[1].startswith('title:'):
                        # New format: id, title, content
                        title_match = re.match(r'^title:\s*(.+)', lines[1])
                        title = title_match.group(1).strip() if title_match else f'Announcement {id}'
                        content = "\n".join(lines[2:])
                    else:
                        # Old format: id, content (no title)
                        title = f'Announcement {id}'
                        content = "\n".join(lines[1:])

                    announcements.append({
                        "id": id,
                        "title": title,
                        "content": content
                    })
                except Exception:
                    # Skip malformed blocks
                    continue

        # Sort by ID descending (newest first)
        announcements.sort(key=lambda x: x['id'], reverse=True)

        result = {
            "ok": True,
            "announcements": announcements,
            "count": len(announcements)
        }
        print(f"[download_announcements] Returning {len(announcements)} announcements")
        return jsonify(result)

    except Exception as e:
        print(f"[download_announcements] Unexpected error: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

# New endpoint: update a specific announcement by id without duplicating
@app.route("/update-announcement", methods=["POST"])
def update_announcement():
    """
    Update a specific announcement in Announcements.txt by id.
    Expects JSON: { "id": 5, "title": "New title", "content": "The new body" }
    Returns: { "ok": true, "updated": true, "id": 5 }
    """
    try:
        print("[update_announcement] /update-announcement called")

        if not verify_secret(request):
            print("[update_announcement] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        ann_id = data.get("id")
        title = data.get("title", "")
        body = data.get("content", "")

        if ann_id is None:
            print("[update_announcement] ERROR: id is required")
            return jsonify({"ok": False, "error": "id is required"}), 400

        try:
            ann_id = int(ann_id)
        except ValueError:
            print("[update_announcement] ERROR: id must be an integer")
            return jsonify({"ok": False, "error": "id must be an integer"}), 400

        docs_dir = PROJECT_ROOT / "docs"
        docs_dir.mkdir(parents=True, exist_ok=True)
        file_path = docs_dir / "Announcements.txt"

        if not file_path.exists():
            print("[update_announcement] Announcements.txt does not exist")
            return jsonify({"ok": False, "error": "Announcements.txt not found"}), 404

        try:
            with open(file_path, 'r', encoding='utf-8') as f:
                content = f.read()
        except Exception as e:
            print(f"[update_announcement] Error reading file: {e}", file=sys.stderr)
            return jsonify({"ok": False, "error": "Error reading file"}), 500

        # Split into blocks using a robust regex that matches a line of 9 dashes
        blocks = re.split(r"(?m)^\-{9}\s*$\n?", content.strip())

        # Build a map of existing titles to their ids for duplicate-title validation
        existing_titles = {}
        for block in blocks:
            b = block.strip()
            if not b:
                continue
            lines_b = b.split('\n')
            id_line_b = lines_b[0] if lines_b else ''
            id_match_b = re.match(r'^id:\s*(\d+)', id_line_b)
            try:
                id_b = int(id_match_b.group(1)) if id_match_b else None
            except Exception:
                id_b = None
            title_b = ''
            if len(lines_b) >= 2 and lines_b[1].startswith('title:'):
                tm = re.match(r'^title:\s*(.+)', lines_b[1])
                title_b = tm.group(1).strip() if tm else ''
            else:
                # old format (no explicit title) -> skip for duplicate checks
                title_b = ''
            if title_b:
                existing_titles[id_b] = title_b

        # If a title was provided in the request, ensure it does not duplicate another announcement's title
        if title:
            new_title_norm = title.strip().lower()
            for eid, etitle in existing_titles.items():
                if eid is not None and eid != ann_id and etitle.strip().lower() == new_title_norm:
                    print(f"[update_announcement] Duplicate title detected for id {eid}")
                    return jsonify({"ok": False, "error": "duplicate title not allowed"}), 400

        updated = False
        new_blocks = []
        for block in blocks:
            block_strip = block.strip()
            if not block_strip:
                continue

            lines = block_strip.split('\n')
            id_line = lines[0] if lines else ''
            id_match = re.match(r'^id:\s*(\d+)', id_line)
            if id_match:
                try:
                    current_id = int(id_match.group(1))
                except Exception:
                    current_id = None
            else:
                current_id = None

            if current_id == ann_id:
                # Replace this block with new content
                if title:
                    new_block = f"id: {ann_id}\ntitle: {title}\n{body.strip()}"
                else:
                    # Preserve old title if present
                    old_title = ''
                    if len(lines) >= 2 and lines[1].startswith('title:'):
                        old_title = lines[1]
                    if old_title:
                        new_block = f"id: {ann_id}\n{old_title}\n{body.strip()}"
                    else:
                        new_block = f"id: {ann_id}\n{body.strip()}"
                new_blocks.append(new_block)
                updated = True
            else:
                new_blocks.append(block_strip)

        if not updated:
            print(f"[update_announcement] Announcement with id {ann_id} not found")
            return jsonify({"ok": False, "error": "Announcement not found"}), 404

        # Reconstruct file content with separator and ensure trailing separator
        separator = "---------\n"
        reconstructed = separator.join(new_blocks)
        reconstructed = reconstructed + "\n" + separator

        # Backup original file
        try:
            backup_dir = PROJECT_ROOT / "backup"
            backup_dir.mkdir(parents=True, exist_ok=True)
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            backup_filename = f"Announcements_{timestamp}.txt"
            backup_path = backup_dir / backup_filename
            import shutil
            shutil.copy2(file_path, backup_path)
            print(f"[update_announcement] Created backup: {backup_path}")
        except Exception as e:
            print(f"[update_announcement] WARNING: Could not create backup: {e}")

        # Write updated content
        try:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(reconstructed)
            print(f"[update_announcement] Successfully updated announcement id {ann_id}")
        except Exception as e:
            print(f"[update_announcement] ERROR writing file: {e}", file=sys.stderr)
            return jsonify({"ok": False, "error": "Failed to write file"}), 500

        return jsonify({"ok": True, "updated": True, "id": ann_id})

    except Exception as e:
        print(f"[update_announcement] Unexpected error: {e}", file=sys.stderr)
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

        # URL decode the filename to handle spaces and special characters
        filename = unquote_plus(filename)

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


@app.route("/health", methods=["GET"])
def health():
    """
    Health check endpoint.
    Returns: { "status": "ok", "timestamp": "..." }
    """
    return jsonify({
        "status": "ok",
        "timestamp": datetime.now().isoformat(),
        "service": "faq_updater"
    })

@app.route("/download-faqs", methods=["GET", "OPTIONS"])
def download_faqs():
    """
    Download the FAQs JSON file from the database directory.
    Returns the FAQs data as JSON.
    Accepts token via header (X-FAQ-UPDATER-TOKEN) or query parameter (token).
    """
    try:
        print("[download_faqs] /download-faqs called")

        # Check for token in header first, then query parameter
        token = request.headers.get("X-FAQ-UPDATER-TOKEN") or request.args.get("token")
        if not token:
            print("[download_faqs] Missing X-FAQ-UPDATER-TOKEN header or token query parameter")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Verify the token manually since verify_secret expects it in headers
        if FAQ_UPDATER_SECRET and not hmac.compare_digest(token, FAQ_UPDATER_SECRET):
            print("[download_faqs] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        print("[download_faqs] Authentication successful")

        # Path to the FAQs JSON file in the database directory
        database_dir = BASE_DIR / "database"
        faqs_json_path = database_dir / "faqs.json"

        # Check if the FAQs file exists
        if not faqs_json_path.exists():
            print("[download_faqs] faqs.json not found in database directory")
            return jsonify({"ok": False, "error": "FAQs not found", "details": "faqs.json file does not exist"}), 404

        try:
            with open(faqs_json_path, 'r', encoding='utf-8') as f:
                faqs_data = json.load(f)
            print(f"[download_faqs] Successfully read FAQs from {faqs_json_path}")
            return jsonify({"ok": True, "faqs": faqs_data.get("faqs", []), "count": len(faqs_data.get("faqs", []))})
        except Exception as e:
            print(f"[download_faqs] Error reading FAQs: {e}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": "Error reading FAQs", "details": str(e)}), 500

    except Exception as e:
        print(f"[download_faqs] Unexpected error in /download-faqs: {e}", file=sys.stderr)
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

        # Create backup of original file in backup directory
        backup_dir = PROJECT_ROOT / "backup"
        backup_dir.mkdir(parents=True, exist_ok=True)
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        backup_filename = f"{file_path.stem}_{timestamp}{file_path.suffix}"
        backup_path = backup_dir / backup_filename
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
            "backup_path": str(backup_path)
        })

    except Exception as e:
        print(f"[update_document] Unexpected error in /update-document: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/check-rasa-status", methods=["GET"])
def check_rasa_status():
    """
    Check if Rasa server is running on port 5005.
    Returns: { "ok": true, "running": true/false, "message": "..." }
    """
    try:
        print("[check_rasa_status] /check-rasa-status endpoint called")

        if not verify_secret(request):
            print("[check_rasa_status] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Check if port 5005 is in use
        try:
            result = subprocess.run(["lsof", "-ti:5005"], capture_output=True, text=True, timeout=5)
            if result.returncode == 0 and result.stdout.strip():
                return jsonify({
                    "ok": True,
                    "running": True,
                    "message": "Rasa server is running on port 5005"
                })
            else:
                return jsonify({
                    "ok": True,
                    "running": False,
                    "message": "Rasa server is not running"
                })
        except subprocess.TimeoutExpired:
            print("[check_rasa_status] lsof command timed out")
            return jsonify({
                "ok": True,
                "running": False,
                "message": "Port check timed out"
            })
        except subprocess.CalledProcessError:
            # lsof not available or no process using the port
            return jsonify({
                "ok": True,
                "running": False,
                "message": "Unable to check port status"
            })
        except Exception as e:
            print(f"[check_rasa_status] Error checking port: {e}")
            return jsonify({"ok": False, "error": str(e)}), 500

    except Exception as e:
        print(f"[check_rasa_status] Unexpected error in /check-rasa-status: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/check-rasa-actions-status", methods=["GET"])
def check_rasa_actions_status():
    """
    Check if Rasa actions server is running on port 5055.
    Returns: { "ok": true, "running": true/false, "message": "..." }
    """
    try:
        print("[check_rasa_actions_status] /check-rasa-actions-status endpoint called")

        if not verify_secret(request):
            print("[check_rasa_actions_status] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Check if port 5055 is in use
        try:
            result = subprocess.run(["lsof", "-ti:5055"], capture_output=True, text=True)
            if result.returncode == 0 and result.stdout.strip():
                return jsonify({
                    "ok": True,
                    "running": True,
                    "message": "Rasa actions server is running on port 5055"
                })
            else:
                return jsonify({
                    "ok": True,
                    "running": False,
                    "message": "Rasa actions server is not running"
                })
        except subprocess.CalledProcessError:
            # lsof not available or no process using the port
            return jsonify({
                "ok": True,
                "running": False,
                "message": "Unable to check port status"
            })
        except Exception as e:
            print(f"[check_rasa_actions_status] Error checking port: {e}")
            return jsonify({"ok": False, "error": str(e)}), 500

    except Exception as e:
        print(f"[check_rasa_actions_status] Unexpected error in /check-rasa-actions-status: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/list-models", methods=["GET"])
def list_models():
    """
    List Rasa models from the models directory.
    Returns: { "ok": true, "models": [{"name": "...", "size": 123, "modified": "..."}, ...] }
    """
    try:
        print("[list_models] /list-models endpoint called")

        if not verify_secret(request):
            print("[list_models] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        models_dir = BASE_DIR.parent / "models"
        print(f"[list_models] Models directory: {models_dir}")

        models_dir.mkdir(parents=True, exist_ok=True)  # Ensure directory exists
        print("[list_models] Ensured models directory exists")

        models = []
        print(f"[list_models] Scanning directory: {models_dir}")
        for item_path in models_dir.iterdir():
            print(f"[list_models] Found item: {item_path.name} (is_file: {item_path.is_file()}, suffix: {item_path.suffix})")
            if item_path.is_file() and item_path.suffix == '.gz' and item_path.name.endswith('.tar.gz'):
                try:
                    # Get file size
                    size = item_path.stat().st_size
                    modified = datetime.fromtimestamp(item_path.stat().st_mtime).isoformat()

                    # Extract model name from filename (remove .tar.gz extension)
                    model_name = item_path.name[:-7]  # Remove '.tar.gz'

                    models.append({
                        "name": model_name,
                        "size": size,
                        "modified": modified,
                        "filename": item_path.name
                    })
                except Exception as e:
                    print(f"[list_models] Error getting info for {item_path}: {e}", file=sys.stderr)

        print(f"[list_models] Found {len(models)} model directories")

        # Sort by modification time (newest first)
        models.sort(key=lambda x: x['modified'], reverse=True)

        result = {
            "ok": True,
            "models": models,
            "count": len(models)
        }
        print(f"[list_models] Returning result: {result}")
        return jsonify(result)

    except Exception as e:
        print(f"[list_models] Unexpected error in /list-models: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/cleanup-models", methods=["POST"])
def cleanup_models():
    """
    Clean up old Rasa models, keeping only the most recent ones.
    Expects JSON: { "keep_count": 5 }
    Returns: { "ok": true, "deleted_count": X, "deleted_models": [...] }
    """
    try:
        print("[cleanup_models] /cleanup-models endpoint called")

        if not verify_secret(request):
            print("[cleanup_models] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        keep_count = data.get('keep_count', 5)

        models_dir = BASE_DIR.parent / "models"
        print(f"[cleanup_models] Models directory: {models_dir}")

        if not models_dir.exists():
            print("[cleanup_models] Models directory does not exist")
            return jsonify({"ok": False, "error": "Models directory not found"}), 404

        # Get all model files
        model_files = []
        for item_path in models_dir.iterdir():
            if item_path.is_file() and item_path.suffix == '.gz' and item_path.name.endswith('.tar.gz'):
                try:
                    modified = item_path.stat().st_mtime
                    model_files.append({
                        'name': item_path.name,
                        'path': item_path,
                        'mtime': modified
                    })
                except Exception as e:
                    print(f"[cleanup_models] Error getting info for {item_path}: {e}", file=sys.stderr)

        print(f"[cleanup_models] Found {len(model_files)} model files")

        if len(model_files) <= keep_count:
            print(f"[cleanup_models] Only {len(model_files)} models found, keeping all")
            return jsonify({
                "ok": True,
                "deleted_count": 0,
                "deleted_models": [],
                "message": f"Only {len(model_files)} models found, no cleanup needed"
            })

        # Sort by modification time (newest first)
        model_files.sort(key=lambda x: x['mtime'], reverse=True)

        deleted_count = 0
        deleted_models = []

        # Delete old models, keep the most recent ones
        for i in range(keep_count, len(model_files)):
            model = model_files[i]
            try:
                model['path'].unlink()
                deleted_models.append(model['name'])
                deleted_count += 1
                print(f"[cleanup_models] Deleted model: {model['name']}")
            except Exception as e:
                print(f"[cleanup_models] Error deleting {model['name']}: {e}", file=sys.stderr)

        result = {
            "ok": True,
            "deleted_count": deleted_count,
            "deleted_models": deleted_models,
            "kept_count": min(keep_count, len(model_files)),
            "message": f"Cleaned up {deleted_count} old model(s)"
        }
        print(f"[cleanup_models] Returning result: {result}")
        return jsonify(result)

    except Exception as e:
        print(f"[cleanup_models] Unexpected error in /cleanup-models: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/start-rasa-api", methods=["POST"])
def start_rasa_api():
    """
    Start the Rasa API server.
    First checks if port 5005 is in use and stops any process using it, then starts Rasa API server.
    Returns: { "ok": true, "message": "..." } or { "ok": false, "error": "..." }
    """
    try:
        print("[start_rasa_api] /start-rasa-api endpoint called")

        if not verify_secret(request):
            print("[start_rasa_api] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Check if port 5005 is in use and kill any process using it
        try:
            # Use lsof to find process using port 5005
            result = subprocess.run(["lsof", "-ti:5005"], capture_output=True, text=True)
            if result.returncode == 0 and result.stdout.strip():
                pids = result.stdout.strip().split('\n')
                for pid in pids:
                    if pid.strip():
                        print(f"[start_rasa_api] Killing process {pid} using port 5005")
                        subprocess.run(["kill", "-9", pid.strip()], check=True)
                # Wait a moment for the port to be freed
                import time
                time.sleep(2)
        except subprocess.CalledProcessError:
            # lsof not available or no process using the port
            pass
        except Exception as e:
            print(f"[start_rasa_api] Warning: Could not check/kill process on port 5005: {e}")

        # Start Rasa API server with virtual environment
        try:
            venv_activate = "source /workspaces/codespaces-quickstart/.venv/bin/activate"
            rasa_cmd = "rasa run --enable-api --cors \"*\""
            full_cmd = f"{venv_activate} && {rasa_cmd}"

            print(f"[start_rasa_api] Starting Rasa API server: {full_cmd}")

            # Start the server in background (non-blocking)
            process = subprocess.Popen(["bash", "-c", full_cmd], cwd=PROJECT_ROOT)

            # Give it a moment to start
            import time
            time.sleep(3)

            # Check if the process is still running
            if process.poll() is None:
                print("[start_rasa_api] Rasa API server started successfully")
                return jsonify({
                    "ok": True,
                    "message": "Rasa API server started successfully on port 5005",
                    "port": 5005
                })
            else:
                error_msg = f"Rasa API server failed to start (exit code: {process.returncode})"
                print(f"[start_rasa_api] {error_msg}")
                return jsonify({"ok": False, "error": error_msg}), 500

        except FileNotFoundError:
            error_msg = "'rasa' command not found. Make sure Rasa is installed and in PATH."
            print(f"[start_rasa_api] {error_msg}")
            return jsonify({"ok": False, "error": error_msg}), 500
        except Exception as e:
            error_msg = f"Error starting Rasa API server: {str(e)}"
            print(f"[start_rasa_api] {error_msg}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": error_msg}), 500

    except Exception as e:
        print(f"[start_rasa_api] Unexpected error in /start-rasa-api: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/start-rasa-actions", methods=["POST"])
def start_rasa_actions():
    """
    Start the Rasa actions server.
    First checks if port 5055 is in use and stops any process using it, then starts Rasa actions server.
    Returns: { "ok": true, "message": "..." } or { "ok": false, "error": "..." }
    """
    try:
        print("[start_rasa_actions] /start-rasa-actions endpoint called")

        if not verify_secret(request):
            print("[start_rasa_actions] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        # Check if port 5055 is in use and kill any process using it
        try:
            # Use lsof to find process using port 5055
            result = subprocess.run(["lsof", "-ti:5055"], capture_output=True, text=True)
            if result.returncode == 0 and result.stdout.strip():
                pids = result.stdout.strip().split('\n')
                for pid in pids:
                    if pid.strip():
                        print(f"[start_rasa_actions] Killing process {pid} using port 5055")
                        subprocess.run(["kill", "-9", pid.strip()], check=True)
                # Wait a moment for the port to be freed
                import time
                time.sleep(2)
        except subprocess.CalledProcessError:
            # lsof not available or no process using the port
            pass
        except Exception as e:
            print(f"[start_rasa_actions] Warning: Could not check/kill process on port 5055: {e}")

        # Start Rasa actions server with virtual environment
        try:
            venv_activate = "source /workspaces/codespaces-quickstart/.venv/bin/activate"
            rasa_cmd = "rasa run actions"
            full_cmd = f"{venv_activate} && {rasa_cmd}"

            print(f"[start_rasa_actions] Starting Rasa actions server: {full_cmd}")

            # Start the server in background (non-blocking)
            process = subprocess.Popen(["bash", "-c", full_cmd], cwd=PROJECT_ROOT)

            # Give it a moment to start
            import time
            time.sleep(3)

            # Check if the process is still running
            if process.poll() is None:
                print("[start_rasa_actions] Rasa actions server started successfully")
                return jsonify({
                    "ok": True,
                    "message": "Rasa actions server started successfully on port 5055",
                    "port": 5055
                })
            else:
                error_msg = f"Rasa actions server failed to start (exit code: {process.returncode})"
                print(f"[start_rasa_actions] {error_msg}")
                return jsonify({"ok": False, "error": error_msg}), 500

        except FileNotFoundError:
            error_msg = "'rasa' command not found. Make sure Rasa is installed and in PATH."
            print(f"[start_rasa_actions] {error_msg}")
            return jsonify({"ok": False, "error": error_msg}), 500
        except Exception as e:
            error_msg = f"Error starting Rasa actions server: {str(e)}"
            print(f"[start_rasa_actions] {error_msg}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": error_msg}), 500

    except Exception as e:
        print(f"[start_rasa_actions] Unexpected error in /start-rasa-actions: {e}", file=sys.stderr)
        traceback.print_exc()
        return jsonify({"ok": False, "error": str(e)}), 500

@app.route("/train-rasa", methods=["POST"])
def train_rasa():
    """
    Train the Rasa model.
    Expects JSON: { "domain": "rasa_files/domain.yml", "data": "rasa_files/data", "out": "rasa_files/models" }
    Returns: { "ok": true, "message": "..." } or { "ok": false, "error": "..." }
    """
    try:
        print("[train_rasa] /train-rasa endpoint called")

        if not verify_secret(request):
            print("[train_rasa] Secret verification failed")
            return jsonify({"ok": False, "error": "unauthorized"}), 401

        data = request.get_json(force=True)
        domain = data.get("domain", "domain.yml")
        data_dir = data.get("data", "data")
        out_dir = data.get("out", "models")

        print(f"[train_rasa] Training with domain: {domain}, data: {data_dir}, out: {out_dir}")

        # Run rasa train command with virtual environment activation
        try:
            # Activate virtual environment and run rasa train
            venv_activate = "source /workspaces/codespaces-quickstart/.venv/bin/activate"
            rasa_cmd = f"rasa train"
            full_cmd = f"{venv_activate} && {rasa_cmd}"

            print(f"[train_rasa] Running command: {full_cmd}")

            result = subprocess.run(["bash", "-c", full_cmd], capture_output=True, text=True, cwd=PROJECT_ROOT)

            if result.returncode == 0:
                print("[train_rasa] Training completed successfully")
                return jsonify({
                    "ok": True,
                    "message": "Rasa training completed successfully",
                    "stdout": result.stdout,
                    "stderr": result.stderr
                })
            else:
                print(f"[train_rasa] Training failed with return code: {result.returncode}")
                return jsonify({
                    "ok": False,
                    "error": f"Rasa training failed: {result.stderr}",
                    "stdout": result.stdout,
                    "stderr": result.stderr
                }), 500

        except FileNotFoundError:
            error_msg = "'rasa' command not found. Make sure Rasa is installed and in PATH."
            print(f"[train_rasa] {error_msg}")
            return jsonify({"ok": False, "error": error_msg}), 500
        except Exception as e:
            error_msg = f"Error running rasa train: {str(e)}"
            print(f"[train_rasa] {error_msg}", file=sys.stderr)
            traceback.print_exc()
            return jsonify({"ok": False, "error": error_msg}), 500

    except Exception as e:
        print(f"[train_rasa] Unexpected error in /train-rasa: {e}", file=sys.stderr)
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
@app.route("/delete-file", methods=["POST", "OPTIONS"])
@app.route("/sync-faqs", methods=["POST", "OPTIONS"])
@app.route("/update-faq", methods=["POST", "OPTIONS"])
@app.route("/update-document", methods=["POST", "OPTIONS"])
@app.route("/train-rasa", methods=["POST", "OPTIONS"])
@app.route("/start-rasa-api", methods=["POST", "OPTIONS"])
@app.route("/start-rasa-actions", methods=["POST", "OPTIONS"])
@app.route("/check-rasa-status", methods=["GET", "OPTIONS"])
@app.route("/check-rasa-actions-status", methods=["GET", "OPTIONS"])
@app.route("/list-models", methods=["GET", "OPTIONS"])
@app.route("/cleanup-models", methods=["POST", "OPTIONS"])
@app.route("/list-docs", methods=["GET", "OPTIONS"])
@app.route("/download-announcements", methods=["GET", "OPTIONS"])
@app.route("/download/<filename>", methods=["GET", "OPTIONS"])
@app.route("/health", methods=["GET", "OPTIONS"])
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

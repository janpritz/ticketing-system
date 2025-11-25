import threading
import time
import json
import os
from pathlib import Path
from .db import get_db_connection

# Global cache: dict[intent] = list of {'response': str, 'status': str}
faq_cache = {}
cache_lock = threading.Lock()
last_updated = None
CACHE_FILE = Path(__file__).parent / "database/faq_cache.json"
FAQS_JSON_FILE = Path(__file__).parent / "database/faqs.json"

def save_faq_cache():
    """Save the current faq_cache and last_updated to JSON file."""
    try:
        CACHE_FILE.parent.mkdir(parents=True, exist_ok=True)
        data = {
            'faq_cache': faq_cache,
            'last_updated': last_updated.isoformat() if last_updated else None
        }
        with open(CACHE_FILE, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2)
    except Exception as e:
        print(f"Error saving FAQ cache to file: {str(e)}")

def load_faq_cache_from_faqs_json():
    """Load faq_cache from database/faqs.json (synced from Laravel), filtering disabled FAQs."""
    global faq_cache, last_updated
    if FAQS_JSON_FILE.exists():
        try:
            print(f"[cache] Loading FAQs from {FAQS_JSON_FILE}")
            with open(FAQS_JSON_FILE, 'r', encoding='utf-8') as f:
                data = json.load(f)
            
            faqs_list = data.get('faqs', [])
            print(f"[cache] Found {len(faqs_list)} FAQs in faqs.json")
            
            # Build cache, grouping by intent and filtering disabled FAQs
            temp_cache = {}
            enabled_count = 0
            disabled_count = 0
            
            for faq in faqs_list:
                intent = faq.get('intent')
                response_disabled = faq.get('response_disabled', False)
                
                if not intent:
                    continue
                
                # Filter out disabled FAQs when loading into cache
                if response_disabled:
                    disabled_count += 1
                    print(f"[cache] Skipping disabled FAQ: {intent}")
                    continue
                
                enabled_count += 1
                if intent not in temp_cache:
                    temp_cache[intent] = []
                
                temp_cache[intent].append({
                    'response': faq.get('response', ''),
                    'status': faq.get('status', 'untrained'),
                    'response_disabled': response_disabled
                })
            
            faq_cache = temp_cache
            last_updated_str = data.get('last_synced')
            if last_updated_str:
                from datetime import datetime
                last_updated = datetime.fromisoformat(last_updated_str)
            
            print(f"[cache] Loaded {enabled_count} enabled FAQs into cache (skipped {disabled_count} disabled)")
            save_faq_cache()  # Save to faq_cache.json for backup
            return True
        except Exception as e:
            print(f"[cache] Error loading from faqs.json: {str(e)}")
            return False
    return False

def load_faq_cache_from_file():
    """Load faq_cache and last_updated from JSON file if exists."""
    global faq_cache, last_updated
    if CACHE_FILE.exists():
        try:
            with open(CACHE_FILE, 'r', encoding='utf-8') as f:
                data = json.load(f)
            faq_cache = data.get('faq_cache', {})
            last_updated_str = data.get('last_updated')
            last_updated = None
            if last_updated_str:
                from datetime import datetime
                last_updated = datetime.fromisoformat(last_updated_str)
            return True
        except Exception as e:
            print(f"Error loading FAQ cache from file: {str(e)}")
            return False
    return False

def fetch_faq_to_file():
    """Fetch all active FAQs from DB and save to file without loading into cache."""
    connection = get_db_connection()
    if not connection:
        print("Failed to connect to DB for fetching FAQs.")
        return 0

    try:
        with connection.cursor(dictionary=True) as cursor:
            cursor.execute(
                "SELECT intent, response, status, response_disabled, updated_at FROM faqs WHERE status != 'deleted' AND response_disabled = 0"
            )
            results = cursor.fetchall()

            # Group by intent
            temp_cache = {}
            max_updated = None
            for row in results:
                intent = row['intent']
                if intent not in temp_cache:
                    temp_cache[intent] = []
                temp_cache[intent].append({
                    'response': row['response'],
                    'status': row['status'],
                    'response_disabled': row['response_disabled']
                })
                if max_updated is None or row['updated_at'] > max_updated:
                    max_updated = row['updated_at']

            # Save to file
            try:
                CACHE_FILE.parent.mkdir(parents=True, exist_ok=True)
                data = {
                    'faq_cache': temp_cache,
                    'last_updated': max_updated.isoformat() if max_updated else None
                }
                with open(CACHE_FILE, 'w', encoding='utf-8') as f:
                    json.dump(data, f, indent=2)
                return len(results)
            except Exception as e:
                print(f"Error saving FAQ to file: {str(e)}")
                return 0
    except Exception as e:
        print(f"Error fetching FAQ from DB: {str(e)}")
        return 0
    finally:
        connection.close()

def load_faq_cache_from_db():
    """Load all active FAQs from DB into the global cache and save to file."""
    global faq_cache, last_updated

    print("Loading FAQ cache from DB.")
    connection = get_db_connection()
    if not connection:
        print("Failed to connect to DB for cache loading.")
        return 0

    try:
        with connection.cursor(dictionary=True) as cursor:
            cursor.execute(
                "SELECT intent, response, status, response_disabled, updated_at FROM faqs WHERE status != 'deleted' AND response_disabled = 0"
            )
            results = cursor.fetchall()

            # Group by intent
            temp_cache = {}
            max_updated = None
            for row in results:
                intent = row['intent']
                if intent not in temp_cache:
                    temp_cache[intent] = []
                temp_cache[intent].append({
                    'response': row['response'],
                    'status': row['status'],
                    'response_disabled': row['response_disabled']
                })
                if max_updated is None or row['updated_at'] > max_updated:
                    max_updated = row['updated_at']

            with cache_lock:
                faq_cache = temp_cache
                last_updated = max_updated
                save_faq_cache()  # Save to file after loading from DB
            return len(results)
    except Exception as e:
        print(f"Error loading FAQ cache: {str(e)}")
        return 0
    finally:
        connection.close()

def load_faq_cache():
    """Load all active FAQs into the global cache, prioritizing faqs.json from Laravel sync."""
    global faq_cache, last_updated

    # First priority: Load from database/faqs.json (synced from Laravel)
    if load_faq_cache_from_faqs_json():
        print("[cache] Loaded FAQ cache from faqs.json (Laravel sync)")
        return

    # Second priority: Load from faq_cache.json backup
    if load_faq_cache_from_file():
        print("[cache] Loaded FAQ cache from faq_cache.json backup")
        return

    # Fallback to DB (if available)
    print("[cache] Falling back to DB load")
    load_faq_cache_from_db()

def refresh_faq_cache():
    """Refresh the cache by reloading from DB and save to file."""
    load_faq_cache()
    # load_faq_cache already saves if loaded from DB

def get_faq_responses(intent):
    """Get list of FAQ responses for an intent from cache."""
    with cache_lock:
        return faq_cache.get(intent, [])
def check_and_refresh_cache():
    """Check if DB has newer data and refresh cache if so."""
    global last_updated
    connection = get_db_connection()
    if not connection:
        return

    try:
        with connection.cursor() as cursor:
            cursor.execute(
                "SELECT MAX(updated_at) FROM faqs WHERE status != 'deleted' AND response_disabled = 0"
            )
            result = cursor.fetchone()
            if result and result[0]:
                db_max_updated = result[0]
                with cache_lock:
                    if last_updated is None or db_max_updated > last_updated:
                        load_faq_cache()
                        # load_faq_cache saves to file
    except Exception as e:
        print(f"Error checking for cache refresh: {str(e)}")
    finally:
        connection.close()

def start_cache_monitor(interval=300):  # 5 minutes default
    """Start a background thread to monitor DB changes."""
    def monitor():
        while True:
            check_and_refresh_cache()
            time.sleep(interval)

    thread = threading.Thread(target=monitor, daemon=True)
    thread.start()
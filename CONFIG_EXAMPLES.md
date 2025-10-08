# Configuration Examples — Laravel <-> Rasa Integration

This file collects concrete environment variable examples and commands for the two supported runtime patterns:
- Pattern A: Rasa actions fetch FAQ responses from Laravel API (recommended).
- Pattern B: Rasa actions fetch FAQ responses directly from the database (lower latency, more sensitive).

Place these values into the appropriate `.env` files or the environment the process runs in.

-------------------------------------------------------------------------------
1) Laravel (.env) — notify faq_updater when a new FAQ is created
-------------------------------------------------------------------------------
# Where the faq_updater Flask service listens (internal host or Codespaces)
FAQ_UPDATER_URL="http://127.0.0.1:5005/update-faq"

# Shared secret (optional but recommended). Laravel will send this value in header X-FAQ-UPDATER-TOKEN
FAQ_UPDATER_SECRET="replace_with_a_long_random_secret"

# Example (append to your Laravel .env)
# FAQ_UPDATER_URL and FAQ_UPDATER_SECRET must match the faq_updater service
FAQ_UPDATER_URL="http://faq-updater-host:5005/update-faq"
FAQ_UPDATER_SECRET="my-super-secret-token-please-change"

-------------------------------------------------------------------------------
2) faq_updater.py environment (where you run the Flask updater)
-------------------------------------------------------------------------------
# Optional but recommended: restrict callers with a shared secret
export FAQ_UPDATER_SECRET="my-super-secret-token-please-change"

# Optional: command to restart actions server after updater writes files
# Example using supervisorctl or pm2 - set to the command that restarts your actions service
export RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"

# Port where updater listens (default 5005)
export FAQ_UPDATER_PORT=5005

# Start the updater
python rasa_files/faq_updater.py

# Manual test of updater (replace host/secret)
curl -v -X POST http://127.0.0.1:5005/update-faq \
  -H "Content-Type: application/json" \
  -H "X-FAQ-UPDATER-TOKEN: my-super-secret-token-please-change" \
  -d '{"intent":"Enrollment Schedule","description":"Handles queries about enrollment dates."}'

-------------------------------------------------------------------------------
3) Rasa Pattern A (API mode) — Rasa actions call Laravel API
-------------------------------------------------------------------------------
# In Rasa actions environment (actions process)
# Set the base URL the actions should call for FAQ responses:
export LARAVEL_API_BASE="https://your-laravel-app.com"

# Example: start actions server (in Rasa project)
# Ensure `actions.py` contains fetch_faq_response calling the API (LARAVEL_API_BASE set)
rasa run actions --actions actions

# Example of generated action behavior (pseudocode):
# reply = requests.get(f"{LARAVEL_API_BASE}/api/faqs/{intent_normalized}").json()['response']
# dispatcher.utter_message(text=reply)

-------------------------------------------------------------------------------
4) Rasa Pattern B (DB mode) — Rasa actions query DB directly
-------------------------------------------------------------------------------
# Provide DB connection details in the environment where actions server runs:
export FAQ_FETCH_MODE="db"   # optional switch if you want the actions file to check this
export FAQ_DB_DRIVER="mysql"        # or "postgres"
export FAQ_DB_HOST="db-host.example.com"
export FAQ_DB_PORT="3306"           # 5432 for Postgres
export FAQ_DB_DATABASE="ticketing_system"
export FAQ_DB_USERNAME="rasa_user"
export FAQ_DB_PASSWORD="very_secret_password"

# Install DB driver in actions env:
# MySQL:
pip install pymysql
# Postgres:
pip install psycopg2-binary

# Start actions server:
rasa run actions --actions actions

# The actions.py helper will connect directly and run:
# SELECT response FROM faqs WHERE LOWER(REPLACE(intent, ' ', '_')) = %s LIMIT 1

Security notes:
- Pattern B requires strong network controls (private network/VPC) and secret management.
- Prefer Pattern A for production if you can keep the API internal and secure.

-------------------------------------------------------------------------------
5) Verify the end-to-end flow (quick checklist)
-------------------------------------------------------------------------------
1. Start Laravel app (ensure `routes/api.php` and Api\FaqController exist).
2. Start faq_updater.py (Flask) on a host reachable by Laravel.
3. Ensure ADMIN controller in Laravel has FAQ_UPDATER_URL set (so it notifies updater).
4. Ensure Rasa actions server is running, and its `actions.py` is the copy used by the Rasa project.
5. Create a new FAQ in Laravel (intent, description, response).
6. Confirm updater received POST (check updater logs) and appended:
   - actions.py contains new class ActionUtter<IntentNormalized>
   - data/flows/faqs_flow.yml contains <intent_normalized>_flow block
7. Restart Rasa actions server (if not automatic).
8. Trigger intent in Rasa (via chat UI) and confirm it replies with the `response` from the Laravel DB.

-------------------------------------------------------------------------------
6) Example environment .env templates (quick copy)
-------------------------------------------------------------------------------
# Laravel .env (append)
FAQ_UPDATER_URL="http://faq-updater-host:5005/update-faq"
FAQ_UPDATER_SECRET="ReplaceWithStrongSecret"

# Rasa actions (Pattern A)
LARAVEL_API_BASE="https://your-laravel-app.com"

# Rasa actions (Pattern B)
FAQ_DB_DRIVER="mysql"
FAQ_DB_HOST="db-host.internal"
FAQ_DB_PORT="3306"
FAQ_DB_DATABASE="ticketing_system"
FAQ_DB_USERNAME="rasa_user"
FAQ_DB_PASSWORD="very_secret_password"

# Updater
FAQ_UPDATER_SECRET="ReplaceWithStrongSecret"
RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"
FAQ_UPDATER_PORT=5005

-------------------------------------------------------------------------------
7) Want me to make these files in repo?
- I can add `.env.example.rasa` and `.env.example.laravel` files with the content above.
- I can add a runtime switch in `rasa_files/actions.py` to choose API vs DB by environment variable `FAQ_FETCH_MODE`.
- I can convert `faq_updater.py` to use PyYAML for safer flow writes.

Reply with which of the above you'd like next and I'll implement it.
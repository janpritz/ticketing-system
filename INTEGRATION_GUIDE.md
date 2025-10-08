# Integration Guide: Laravel ticketing-system ↔ Rasa Calm

This guide shows concrete configuration and steps to connect your Laravel ticketing-system to Rasa so that FAQs are served dynamically by Rasa actions. It assumes you already applied the code artifacts generated in this workspace (see clickable references below).

Files referenced (already created in the repo)
- Rasa updater microservice: [`rasa_files/faq_updater.py`](rasa_files/faq_updater.py:1)
- Rasa actions helper: [`rasa_files/actions.py`](rasa_files/actions.py:1)
- Rasa flows target: [`rasa_files/data/flows/faqs_flow.yml`](rasa_files/data/flows/faqs_flow.yml:1)
- Laravel API controller: [`app/Http/Controllers/Api/FaqController.php`](app/Http/Controllers/Api/FaqController.php:1)
- Laravel Admin store hook: [`app/Http/Controllers/AdminController.php`](app/Http/Controllers/AdminController.php:1)
- Laravel route file: [`routes/api.php`](routes/api.php:1)
- Updater README: [`rasa_files/README_INSTRUCTIONS.md`](rasa_files/README_INSTRUCTIONS.md:1)

Goal
- When a new FAQ is created in Laravel (intent, description, response):
  1. Laravel saves the FAQ.
  2. Laravel POSTs to the faq_updater service to create a Rasa action class and flow.
  3. Rasa action (when executed) fetches the FAQ response (either directly from DB or via Laravel API, per your environment) and utters it.

Two deployment patterns (pick one)
- Pattern A (recommended for security and separation): Rasa actions call Laravel API to fetch the response.
  - Pros: no DB credentials in Rasa, easier auditing, uses existing Laravel access control.
  - Cons: small HTTP latency.
- Pattern B (direct DB access from Rasa actions): Rasa connects to DB directly to read faqs table.
  - Pros: lower latency.
  - Cons: needs DB credentials in Rasa env, network and security considerations.

Below are configuration, env variables, and commands for both patterns.

1) Laravel configuration (ticketing-system)
- .env entries to add
  - For notifying the updater service:
    FAQ_UPDATER_URL="http://faq-updater-host:5005/update-faq"
    FAQ_UPDATER_SECRET="some-long-secret"         # optional but recommended
  - Example .env snippet:
    - Add these lines to your Laravel `.env`:
      FAQ_UPDATER_URL="http://127.0.0.1:5005/update-faq"
      FAQ_UPDATER_SECRET="replace_with_strong_secret"
- What the AdminController does
  - See [`app/Http/Controllers/AdminController.php`](app/Http/Controllers/AdminController.php:1)
  - After a successful FAQ creation it performs a POST to `FAQ_UPDATER_URL` with JSON:
    {
      "intent": "Enrollment Schedule",
      "description": "Handles queries about enrollment dates.",
      "restart_actions": true
    }
  - It optionally sets header `X-FAQ-UPDATER-TOKEN: <FAQ_UPDATER_SECRET>`

- The public FAQ read API used by Rasa (if using Pattern A)
  - Route: GET `/api/faqs/{intent}` (see [`routes/api.php`](routes/api.php:1))
  - Controller: [`app/Http/Controllers/Api/FaqController.php`](app/Http/Controllers/Api/FaqController.php:1)
  - Returns JSON: { "response": "The answer text..." }

2) Rasa FAQ updater service (faq_updater.py)
- Purpose: Accepts POST /update-faq to append action classes + flows.
- Key environment variables
  - FAQ_UPDATER_SECRET — secret token to protect the endpoint (must match Laravel).
  - RASA_ACTIONS_RESTART_CMD — optional shell command to restart Rasa actions server (e.g., supervisorctl, systemctl)
  - FAQ_UPDATER_PORT — port to run the Flask service on (default 5005)
- Start the service
  - In Codespaces or a host reachable by Laravel:
    pip install flask filelock requests
    export FAQ_UPDATER_SECRET="replace_with_strong_secret"
    export RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"   # optional
    python rasa_files/faq_updater.py
- Behavior: appends to [`rasa_files/actions.py`](rasa_files/actions.py:1) and [`rasa_files/data/flows/faqs_flow.yml`](rasa_files/data/flows/faqs_flow.yml:1) using `filelock`.

3) Rasa actions server configuration
- Copy files into your Rasa project:
  - Copy `rasa_files/actions.py` → in your Rasa repo `actions/actions.py` (or replace the content if you already have one). The updater will append action classes into this file.
  - Copy `rasa_files/data/flows/faqs_flow.yml` → in Rasa project `data/flows/faqs_flow.yml`.
  - Copy `rasa_files/faq_updater.py` to where you will run the updater (can be separate).
- Rasa actions runtime env (Pattern A: use Laravel API)
  - Ensure your actions file has LARAVEL_API_BASE set correctly:
    - Edit [`rasa_files/actions.py`](rasa_files/actions.py:1) and set:
      LARAVEL_API_BASE = "https://your-laravel-app.com"
  - Start Rasa actions:
    rasa run actions --actions actions
- Rasa actions runtime env (Pattern B: direct DB)
  - Provide DB credentials in environment for Rasa actions process:
    FAQ_DB_DRIVER=mysql
    FAQ_DB_HOST=your-db-host
    FAQ_DB_PORT=3306
    FAQ_DB_DATABASE=your_db
    FAQ_DB_USERNAME=db_user
    FAQ_DB_PASSWORD=secret
  - Install DB driver in actions environment:
    - For MySQL: pip install pymysql
    - For Postgres: pip install psycopg2-binary
  - Start actions server:
    rasa run actions --actions actions

4) Firewall / Network
- Ensure the following network paths are allowed:
  - Laravel → faq_updater (HTTP POST on port where updater listens)
  - Rasa actions → Laravel API (if Pattern A)
  - Rasa actions → DB (if Pattern B)
- If Codespaces hosts Rasa, ensure Codespaces runner has access to Laravel host or DB.

5) Example: Create a FAQ and verify full flow (Pattern A - use API)
- Step A: Create FAQ in Laravel UI or via API (the UI or AdminController will call updater)
- Step B: Laravel posts to updater:
  Example manual curl:
  curl -X POST http://127.0.0.1:5005/update-faq \
    -H "Content-Type: application/json" \
    -H "X-FAQ-UPDATER-TOKEN: replace_with_strong_secret" \
    -d '{"intent":"Enrollment Schedule","description":"Handles queries about enrollment dates."}'
- Step C: Updater appends class to actions.py and block to faqs_flow.yml
- Step D: Restart Rasa actions server (or let updater run restart command)
- Step E: Trigger intent in Rasa; action class `action_utter_enrollment_schedule` (generated) will run and call:
  GET https://your-laravel-app.com/api/faqs/enrollment_schedule
  which returns JSON { "response": "..." }, and Rasa will utter that text.

6) Example: Create a FAQ and verify full flow (Pattern B - DB)
- Step A: Same create in Laravel
- Step B: Updater appends action and flow
- Step C: Rasa action class when executed will connect directly to DB using env vars (see section 3) and fetch the response from `faqs` table.
- Step D: No Laravel API call needed at runtime; DB read happens inside action.

7) Security and best practices
- Prefer Pattern A (API) in production:
  - Avoid giving direct DB credentials to Rasa actions.
  - Use `FAQ_UPDATER_SECRET` to protect updater POST endpoint.
  - Run updater behind internal network or VPN; do not expose publicly.
- Use HTTPS for all HTTP traffic (Laravel, updater).
- If you need stronger authentication between Laravel and updater, consider HMAC signing of payloads.
- Always back up Rasa `actions.py` and `faqs_flow.yml` before letting updater append.

8) Troubleshooting checklist
- If action not found in Rasa:
  - Confirm `actions.py` contains the appended class (open [`rasa_files/actions.py`](rasa_files/actions.py:1)).
  - Restart Rasa actions server and check logs.
- If action runs but returns "No answer available.":
  - Pattern A: confirm Laravel endpoint `/api/faqs/{intent}` returns JSON with `response` field.
    - Test example:
      curl https://your-laravel-app.com/api/faqs/enrollment_schedule
  - Pattern B: confirm Rasa actions environment has correct DB env vars and DB driver installed; test DB connection from actions environment.
- Check updater logs for errors when appending files.

9) Useful commands (summary)
- Start updater:
  export FAQ_UPDATER_SECRET="secret"
  python rasa_files/faq_updater.py
- Restart Rasa actions (example):
  rasa run actions --actions actions
- Test Laravel GET API:
  curl -sS https://your-laravel-app.com/api/faqs/enrollment_schedule | jq

10) Where to look in this repo
- Updater: [`rasa_files/faq_updater.py`](rasa_files/faq_updater.py:1)
- Rasa actions helper: [`rasa_files/actions.py`](rasa_files/actions.py:1)
- Rasa flows file: [`rasa_files/data/flows/faqs_flow.yml`](rasa_files/data/flows/faqs_flow.yml:1)
- Laravel API: [`app/Http/Controllers/Api/FaqController.php`](app/Http/Controllers/Api/FaqController.php:1)
- Laravel admin store: [`app/Http/Controllers/AdminController.php`](app/Http/Controllers/AdminController.php:1)
- Laravel route: [`routes/api.php`](routes/api.php:1)

If you want, I can:
- Produce a concrete `.env.example` for Rasa actions and Laravel showing all required keys (I can write it into repo).
- Implement the optional runtime switch so Rasa actions choose DB vs API based on an env var.
- Add a safe YAML generator (PyYAML) into `faq_updater.py` so flows are written as structured YAML nodes.

Which of the above would you like next? 
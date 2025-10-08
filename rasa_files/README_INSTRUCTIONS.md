# Rasa + Laravel FAQ Integration (rasa_files)

This folder contains helper files to integrate Laravel FAQ CRUD with a Rasa Calm chatbot using dynamic actions.

Files included:
- `actions.py` - Rasa actions file where dynamic FAQ action classes will be appended.
- `faq_updater.py` - Flask microservice that accepts POST /update-faq and appends action classes and flows.
- `data/flows/faqs_flow.yml` - Where flow sections for FAQs are appended.

Quick setup (Codespaces -> Rasa repo):
1. Copy `rasa_files/actions.py` to your Rasa project's actions directory (e.g., `actions/actions.py`).
2. Copy `rasa_files/data/flows/faqs_flow.yml` to `data/flows/faqs_flow.yml` in your Rasa project.
3. Run `pip install flask filelock requests` in the environment that will run `faq_updater.py`.
4. Run the updater service:
   ```
   export FAQ_UPDATER_SECRET="your-secret"      # optional
   export RASA_ACTIONS_RESTART_CMD="supervisorctl restart rasa-actions"  # optional
   python rasa_files/faq_updater.py
   ```
5. Ensure your Rasa action server is running (e.g., `rasa run actions`).

Laravel integration:
- Add `FAQ_UPDATER_URL` and optionally `FAQ_UPDATER_SECRET` to your `.env`.
- The AdminController store method will POST to the updater service after creating a FAQ (non-blocking).

Security:
- Use `FAQ_UPDATER_SECRET` on both sides and the header `X-FAQ-UPDATER-TOKEN`.
- Restrict access to the updater service to your internal network / Codespaces.

Testing flow:
1. Create a new FAQ via Laravel UI or API.
2. Laravel will call `/update-faq` on the updater service.
3. Updater appends to `actions.py` and `faqs_flow.yml`.
4. Restart Rasa actions server (if not automated).
5. Trigger the intent in Rasa; action calls Laravel `/api/faqs/{intent}` to fetch the response dynamically.

Notes:
- The updater service uses simple YAML-style appends — for large-scale projects consider generating proper YAML nodes using PyYAML for structural safety.
- Always back up Rasa `actions.py` and `faqs_flow.yml` before running automated appends.
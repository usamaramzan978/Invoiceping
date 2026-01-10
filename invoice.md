✅ Correct Mental Model (Very Important)
👉 There is ONLY ONE “Send Invoice” action
Inside it, the admin chooses HOW MUCH automation they want.

🧾 ONE BUTTON: “Send Invoice”
When admin clicks Send Invoice, they get options.

🔀 Two Send MODES (inside same flow)
1️⃣ Simple Send (No Reminders)
Use case:
Admin just wants to send invoice once.

Admin selects:
✔ Channel(s): Email / WhatsApp
✔ Initial message template
❌ Reminders OFF

System does:

-   status → sent
-   send initial message
-   NO reminders created
    ✅ Clean
    ✅ Safe
    ✅ No spam

2️⃣ Send + Schedule Reminders
Use case:
Admin wants automatic follow-ups.

Admin selects:

✔ Channel(s)
✔ Initial template
✔ Enable reminders
✔ Reminder schedule
✔ Reminder template

System does:

-   status → sent
-   send initial message
-   create reminder schedules
-   queue handles follow-ups
    🔥 This is your core paid feature

🚫 What You Should NOT Do
❌ Two separate buttons
❌ Auto-scheduling without consent
❌ Hidden reminder logic

🧠 Why This Design Is Perfect
Benefit Why it matters
Admin control No accidental reminders
User trust Professional behavior
Legal safety Explicit consent
Upgrade path “Automation” = paid
📊 Visual Flow
[ Draft Invoice ]
↓
[ Send Invoice ]
↓
Choose:
├─ Send only
└─ Send + Reminders

🧩 Database Perspective
Nothing changes structurally:

invoices → sent
invoice_reminders → created only if enabled
reminder_logs → always created

💰 Monetization Hint (Important Later)
Later you can:

Free plan → Send only
Paid plan → Send + Reminders

🔥 Very easy upsell.

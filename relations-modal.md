./vendor/bin/phpstan analyse --memory-limit=2G
./vendor/bin/pint
./vendor/bin/rector process

Recent Activity , Cards, Charts , Tables

User
└── hasOne BusinessProfile
└── hasMany Subscriptions
└── ReminderRules
└── MessageTemplates

BusinessProfile
└── belongsTo User
└── hasMany Clients
└── hasMany Invoices
└── hasMany MessageTemplates

Client
└── belongsTo BusinessProfile
└── hasMany Invoices

Invoice
└── belongsTo BusinessProfile
└── belongsTo Client
└── hasMany InvoiceItems
└── hasMany ReminderSchedules

Subscription
└── belongsTo User
└── belongsTo SubscriptionPlan

User
└── hasMany ReminderRules

ReminderRule
└── hasMany ReminderRuleSteps

ReminderRuleStep
└── hasMany ReminderRuleStepTemplates

ReminderSchedule
└── belongsTo Invoice
└── belongsTo ReminderRule
└── belongsTo ReminderRuleStep
└── belongsTo MessageTemplate

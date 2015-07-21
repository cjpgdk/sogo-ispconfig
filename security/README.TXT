The folder containes the required ids.whitelist values needed if you use IDS filter


NOTE**
the files IS NOT copied or merged with the existing one in ISPConfig
the reason for this is that i would consider any custom changes to
security critical files that is not done by myself is highly insecure

so you will have to add them yourself if needed



ids.whitelist-mail.module:
    contains ids whitelist rules to use for clients in the mail module

ids.whitelist-admin.module:
    contains ids whitelist rules to use for admins in the admin module
    domain edit (Mail domain edit).

ids.whitelist-admin.module-config_edit:
    contains ids whitelist rules to use for admins in the admin module
    config edit (Server config edit).
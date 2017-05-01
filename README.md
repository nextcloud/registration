# Registration
This app allows users to register a new account.

Flow:

1. User enters his/her email
2. Verification link is sent to the email address
3. User clicks on the verification link
4. User is lead to a form where one can choose username and password
5. New account is created and is logged in automatically

# Requirements
1. ownCloud 9.1.0.7+
2. Nextcloud 9+

Supports SQLite and MariaDB, PostgreSQL support is [enabled but not tested](https://github.com/pellaeon/registration/issues/24#issuecomment-294504028).

# Install
1. Place this app in `owncloud/apps/` or `nextcloud/apps`
2. Enable "Registration" in */settings/apps* (Upper left dropdown -> plus sign -> "Disabled")
3. Make sure you have correctly set up your mail server according to the [documentation](https://docs.nextcloud.com/server/11/admin_manual/configuration_server/email_configuration.html)
4. Log out, and you should see "Register" under "Other login methods"

# Features

- Admin can specify which group the newly created users belong
- Admin can limit the email domains allowed to register
- Admin will be notified by email for new user creation

# FAQ

**Q: A problem occurred sending email, please contact your administrator.**

A: your Nextcloud mail configurations or your mail server is incorrectly configured, please refer to the [Nextcloud documentation](https://docs.nextcloud.com/server/11/admin_manual/configuration_server/email_configuration.html).

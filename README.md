# Registration
This app allows users to register a new account.

Flow:

1. User enters his/her email
2. Verification link is sent to the email address
3. User clicks on the verification link
4. User is lead to a form where one can choose username and password
5. New account is created and is logged in automatically

# Requirements
1. ownCloud/Nextcloud 9.1.0.7+
2. Backend database is MariaDB/MySQL

# Install
1. Place this app in **owncloud/apps/**
2. Enable "Registration" in /settings/apps
3. Log out, and you should see "Register" under "Other login methods"

# Features

- Admin can specify which group the newly created users belong
- Admin can limit the email domains allowed to register
- Admin will be notified by email for new user creation

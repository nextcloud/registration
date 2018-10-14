[![Build Status](https://travis-ci.org/pellaeon/registration.svg?branch=master)](https://travis-ci.org/pellaeon/registration)

# Registration
This app allows users to register a new account.


# Requirements
1. ownCloud 9.1.0.7+
2. Nextcloud 9+

Supports SQLite, MySQL/MariaDB and PostgreSQL.

# Install
## ownCloud
1. Place this app in the apps folder
2. Enable "Registration" in */settings/apps* (Upper left dropdown -> plus sign -> "Disabled")
3. Make sure you have correctly set up your mail server according to the [documentation](https://doc.owncloud.com/server/10.0/admin_manual/configuration/server/email_configuration.html)
4. Log out, and you should see "Register" under "Other login methods"

## Nextcloud AppStore
From your Nextcloud instance, click: your profile to the upper right -> Apps -> Tools (in the left column), find Registration, click Enable

# Features

- Admin can specify which group the newly created users belong
- Admin can limit the email domains allowed to register
- Admin will be notified by email for new user creation

# Web form registration flow

1. User enters his/her email
2. Verification link is sent to the email address
3. User clicks on the verification link
4. User is lead to a form where one can choose username and password
5. New account is created and is logged in automatically

# OCS API registration flow

See https://gist.github.com/juliushaertl/5a1d1132e7370b5ad38fbd6da3cae5b8

# Donate

Send Ethereum to `0x941613eBB948C2C547cb957B55fEB2609fa6Fe66`
Send BTC to `33pStaSaf4sDUA8XBAHTq7ZDQpCVFQArxQ`

# FAQ

**Q: A problem occurred sending email, please contact your administrator.**

A: your Nextcloud mail configurations or your mail server is incorrectly configured, please refer to the [Nextcloud documentation](https://docs.nextcloud.com/server/11/admin_manual/configuration_server/email_configuration.html).

# Testing

See TESTING.md

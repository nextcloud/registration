# Registration
This app allows users to register a new account.

![Registration form](https://raw.githubusercontent.com/nextcloud/registration/master/docs/demo.gif)

# Install from appstore

From your Nextcloud instance, click: your profile to the upper right -> Apps -> Tools (in the left column), find Registration, click Enable

# Features

- Add users to a given group
- Allow-list with email domains (including wildcard) to register with
- Admins will be notified via email for new user creation or require approval
- Supports Nextcloud's Client [Login Flow v1 and v2](https://docs.nextcloud.com/server/stable/developer_manual/client_apis/LoginFlow/index.html) - Allowing registration in the mobile Apps and Desktop clients

# Web form registration flow

1. User enters their email address
2. Verification link is sent to the email address
3. User clicks on the verification link
4. User is lead to a form where they can choose their username and password
5. New account is created and is logged in automatically

# Donate

You can donate to Pellaeon the original author of the app:

* Send Ethereum to `0x941613eBB948C2C547cb957B55fEB2609fa6Fe66`
* Send BTC to `33pStaSaf4sDUA8XBAHTq7ZDQpCVFQArxQ`

# FAQ

**Q: A problem occurred sending email, please contact your administrator.**

A: Your Nextcloud mail settings are incorrectly configured, please refer to the [Nextcloud documentation](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/email_configuration.html).

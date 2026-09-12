<!--
  - SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
  - SPDX-FileCopyrightText: 2014 Pellaeon Lin <pellaeon@hs.ntnu.edu.tw>
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# 🖋️ Registration

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/registration)](https://api.reuse.software/info/github.com/nextcloud/registration)
This app allows users to register a new account.

![Registration form](https://raw.githubusercontent.com/nextcloud/registration/master/docs/demo.gif)

## 🚢 Installation

The app is distributed through the [app store](https://apps.nextcloud.com/apps/registration) and you can install it [right from your Nextcloud installation](https://docs.nextcloud.com/server/latest/admin_manual/apps_management.html).

Release tarballs are hosted at https://github.com/nextcloud-releases/registration/releases.

## ✨ Features

* 👥 Add users to a given group
* 🛃 Allow-list with email domains (including wildcard) or exact email addresses to register with
* 🎟️ Invitation codes and invitation links: restrict who can register, limit the number of uses per link, set a storage quota for invited users and optionally skip the email verification step
* 🔔 Administrator will be notified via email for new user creation or require approval
* 📱 Supports Nextcloud's Client [Login Flow v1 and v2](https://docs.nextcloud.com/server/stable/developer_manual/client_apis/LoginFlow/index.html) - allowing registration in the mobile Apps and Desktop clients
* 📜 Integrates with [Terms of service](https://apps.nextcloud.com/apps/terms_of_service)

## 🔁 Web form registration flow

1. User enters their email address
2. Verification link is sent to the email address
3. User clicks on the verification link
4. User is lead to a form where they can choose their username and password
5. New account is created and is logged in automatically

## ❓ FAQ

**Q: A problem occurred sending email, please contact your administrator.**

A: Your Nextcloud mail settings are incorrectly configured, please refer to the [Nextcloud documentation](https://docs.nextcloud.com/server/latest/admin_manual/configuration_server/email_configuration.html).

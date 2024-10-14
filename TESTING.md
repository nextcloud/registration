<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
You need a fully working nextcloud/owncloud instance. Check-out this app into the `apps` folder, and follow the instructions below. The tests will modify your database but should automatically revert the changes in the cleanup stage. (As provided by https://github.com/ChristophWurst/nextcloud_testing)

# Enable the app

```
cd <nextcloud root>
sudo -u php occ app:enable registration
```

# Make sure you have latest dev dependencies

If you don't have `composer` installed yet, follow https://getcomposer.org/download/

Then install dev dependencies:
```
composer install
```

# Run tests manually

```
sudo -u www-data vendor/bin/phpunit -c tests/phpunit.unit.xml
sudo -u www-data vendor/bin/phpunit -c tests/phpunit.integration.xml
```

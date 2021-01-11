# Changelog
All notable changes to this project will be documented in this file.

## 0.6.1 – 2021-01-08
### Added
 - Compatibility with Nextcloud 21 beta 5

## 0.6.0 – 2020-12-14
### Added
 - Allow forcing user name patterns and providing hints on the registration form and email (by @pxlfrk )
    [#259](https://github.com/nextcloud/registration/pull/259)
 - Compatibility with Nextcloud 21

## 0.5.2 – 2020-11-30
### Fixed
 - Fix compatibility with password policy app
    [#258](https://github.com/nextcloud/registration/pull/258)
 - Allow mail confirmation to be optional
    [#248](https://github.com/nextcloud/registration/pull/248)
 - Don't allow to limit the app to groups as the users are all guests and therefor in no groups
    [#255](https://github.com/nextcloud/registration/pull/255)
 - Fix invalide route on user data form
    [#251](https://github.com/nextcloud/registration/pull/251)

## 0.5.1 – 2020-10-05
### Fixed
 - Fix rate limitation to avoid spaming users
    [#246](https://github.com/nextcloud/registration/pull/246)

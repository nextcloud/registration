# Changelog
All notable changes to this project will be documented in this file.

## 1.4.0 – 2021-12-02
### Added
- Compatibility with Nextcloud 23

## 1.3.0 – 2021-06-17
### Added
- Compatibility with Nextcloud 22

## 1.2.1 – 2021-04-21
### Fixed
- Don't append # to the URL so the Android WebView doesn't chock on it.

## 1.2.0 – 2021-04-15
### Added
- Allow apps to hook into the registration flow, e.g. Terms of Service
  [#293](https://github.com/nextcloud/registration/pull/293)

## 1.1.2 – 2021-04-08
### Fixed
- Disable submit button up on submitting the form
  [#286](https://github.com/nextcloud/registration/pull/286)
- Add HTML5 required property to fields that can not be empty
  [#286](https://github.com/nextcloud/registration/pull/286)
- Some browsers fail to render the administration settings
  [#286](https://github.com/nextcloud/registration/pull/286)

## 1.1.1 – 2021-04-04
### Fixed
- Fix registration with loginname field
  [#284](https://github.com/nextcloud/registration/pull/284)

## 1.1.0 – 2021-04-01
### Added
- Added settings to display and enforce a full name field on the registration form
  [#280](https://github.com/nextcloud/registration/pull/280)
- Added settings to display and enforce a phone number field on the registration form (Requires Nextcloud 21.0.1)
  [#280](https://github.com/nextcloud/registration/pull/280)
  
### Changed
- Reorganized the administration settings bringing in structure to the list of settings and hiding settings that exclude each others correctly

## 1.0.0 – 2021-03-22
### Fixed
- Some layout issues when the screen is integrated into the Nextcloud mobile apps
  [#277](https://github.com/nextcloud/registration/pull/277)
  [#276](https://github.com/nextcloud/registration/pull/276)

## 0.7.0 – 2021-02-23
### Added
 - Compatibility with Nextcloud 21

### Fixed
- Show an error instead of exception when mail server is not set up
  [#274](https://github.com/nextcloud/registration/pull/274)

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

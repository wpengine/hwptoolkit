# WPGraphQL Logging

## [1.1.0](https://github.com/wpengine/hwptoolkit/compare/@wpengine/wpgraphql-logging-wordpress-plugin-1.0.2...@wpengine/wpgraphql-logging-wordpress-plugin-1.1.0) (2026-11-03)


### Features

* **logging:** add a search bar to the All Logs page ([7f1828b](https://github.com/wpengine/hwptoolkit/commit/7f1828bd73c020717a1c8002e15468a7ed219fc1))
* **logging:** add a search bar to the All Logs page ([68d0434](https://github.com/wpengine/hwptoolkit/commit/68d0434aa5b003da75550933c6f9ad813bd465b5))
* **logging:** add an admin dashboard with charts ([885c4a6](https://github.com/wpengine/hwptoolkit/commit/885c4a67c559eb0102b3121df1047b12d433f51a))
* **logging:** add an admin dashboard with charts ([af952de](https://github.com/wpengine/hwptoolkit/commit/af952debc4a0007c17a3cc7c30df29d645716550))
* **logging:** add bulk CSV download for selected log entries ([fe5341a](https://github.com/wpengine/hwptoolkit/commit/fe5341a15e296fff25d2fa91e53735f7339c38ef))
* **logging:** add request duration to log records ([0caf979](https://github.com/wpengine/hwptoolkit/commit/0caf979b0642d31b47da8788291f95ef28b1d4a7))
* **logging:** add request duration to log records ([084f2f4](https://github.com/wpengine/hwptoolkit/commit/084f2f4f4c7828218b7831cf68f10c73bede2469))
* **logging:** bulk download selected log entries as CSV ([5618144](https://github.com/wpengine/hwptoolkit/commit/5618144cc53bb157df98e4b24bbbc5139be15aba))


### Bug Fixes

* **logging:** drop impossible false check on ob_get_status() result ([dd2a70e](https://github.com/wpengine/hwptoolkit/commit/dd2a70e9e1ee328049a920cbe9cee8328ed71138))
* **logging:** encode filter values in the logs redirect URLs ([a2df704](https://github.com/wpengine/hwptoolkit/commit/a2df70456b71e9256426a6a46d325998c08389ed))
* **logging:** follow local calendar days and user locale on the dashboard ([4f287ad](https://github.com/wpengine/hwptoolkit/commit/4f287adaa94579b531707bc8a1c7eee8714a88e1))
* **logging:** harden CSV log downloads against formula injection ([ef6c222](https://github.com/wpengine/hwptoolkit/commit/ef6c222b34fe59f87f97fe31ecbdd0c8698f8c51))
* **logging:** start the request timer per HTTP request and guard against leaks ([482f158](https://github.com/wpengine/hwptoolkit/commit/482f158e000c7ca502f729873b9a18e9ab8a10ba))

## [1.0.2](https://github.com/wpengine/hwptoolkit/compare/@wpengine/wpgraphql-logging-wordpress-plugin-1.0.1...@wpengine/wpgraphql-logging-wordpress-plugin-1.0.2) (2026-10-26)


### Bug Fixes

* **logging:** block direct access to the files Plugin Check flagged ([c820a6e](https://github.com/wpengine/hwptoolkit/commit/c820a6e2e004da912593550f86d89530e8867d8e))
* **logging:** declare GPL-2.0-or-later consistently ([9fbe16f](https://github.com/wpengine/hwptoolkit/commit/9fbe16fe0f48840182027b436d198bb191b4a193))
* **logging:** keep the log view template's docblock above its guard ([be94aa6](https://github.com/wpengine/hwptoolkit/commit/be94aa600284e5b2f5d55a4dca79fa2fee4d45b0))
* **logging:** note why the CSV export closes php://output directly ([9dae020](https://github.com/wpengine/hwptoolkit/commit/9dae02056647b6fd3c888ed2b2e6af8f33c7a18f))
* **logging:** resolve Plugin Check errors in the shipped plugin ([77aee39](https://github.com/wpengine/hwptoolkit/commit/77aee39405d16245bbc757a1d45efb55dcf9d0b8))
* **logging:** use a plain version in the Requires PHP header ([e0acd48](https://github.com/wpengine/hwptoolkit/commit/e0acd4833354f62c44ebd659084dbf57b5d021e0))

## 1.0.1

### Patch Changes

- [#531](https://github.com/wpengine/hwptoolkit/pull/531) [`959ba90`](https://github.com/wpengine/hwptoolkit/commit/959ba9084e6aa61f90dd65307b5d3919f9b8cfc9) Thanks [@josephfusco](https://github.com/josephfusco)! - Verify WordPress 6.9 compatibility and update CI test matrix

  - Tested compatibility with WordPress 6.9
  - Updated CI test matrix to WordPress 6.9, 6.8, 6.7 (dropped 6.5, 6.6)
  - Updated dev dependencies (wordpress-core, wordpress-stubs to ^6.9, phpcompatibility to ^9.3)
  - Reduced readme.txt tags to 5 for WordPress.org compliance

## 1.0.0

### Major Changes

- [#497](https://github.com/wpengine/hwptoolkit/pull/497) [`01fc6c0`](https://github.com/wpengine/hwptoolkit/commit/01fc6c0eb29f432bc346d3ec5604eaa441ceedcd) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Initial release of WPGraphQL Logging Plugin.

## 0.2.3

### Patch Changes

- [#485](https://github.com/wpengine/hwptoolkit/pull/485) [`3741a12`](https://github.com/wpengine/hwptoolkit/commit/3741a129f7e6f6d5fc8c882a5104434d0d09c195) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Added nonce for the detail page for the download link.

## 0.2.2

### Patch Changes

- [#482](https://github.com/wpengine/hwptoolkit/pull/482) [`99c82ad`](https://github.com/wpengine/hwptoolkit/commit/99c82adc56041d7dca358a7d62849e08eb37c1e8) Thanks [@ahuseyn](https://github.com/ahuseyn)! - UI improvements for WPGraphQL Logging plugin: refactored styles, added GraphQL query formatting, and implemented unsaved changes warning

## 0.2.1

### Patch Changes

- [#476](https://github.com/wpengine/hwptoolkit/pull/476) [`8156902`](https://github.com/wpengine/hwptoolkit/commit/8156902a1f4cc9dae655129abb788056bf9f76cc) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Add default configuration on plugin activation if no configuration already exists.

## 0.2.0

### Minor Changes

- [#456](https://github.com/wpengine/hwptoolkit/pull/456) [`389a324`](https://github.com/wpengine/hwptoolkit/commit/389a32440d21a9dbfee18dac39e695af6aa0816e) Thanks [@theodesp](https://github.com/theodesp)! - chore: Various improvement to the logging plugin:

  - Implemented BufferHandler to batch write database entries for performance
  - Implemented LogStoreService to remove hard dependencies of the Database services in the admin and various classes
  - Refactored Database and CRUD log services to use interfaces to make it easy to be exctended for other data storage solutions.
  - Added missing indexes for performance
  - Added filters for caching configuration
  - Better error handling
  - Added missing nonce for admin pages

## 0.1.0

### Minor Changes

- [#431](https://github.com/wpengine/hwptoolkit/pull/431) [`c3c7776`](https://github.com/wpengine/hwptoolkit/commit/c3c7776000d5ae0836946bcc1ac545d1c4a6bb6e) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Fixing some snags and updating docs for BETA release.

## 0.0.12

### Patch Changes

- [#423](https://github.com/wpengine/hwptoolkit/pull/423) [`ef373fa`](https://github.com/wpengine/hwptoolkit/commit/ef373fa9710dd05bde9c33b7eb758f73686f0bd3) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Fixed some snags for events and event manager. Added and updated PHPUnit tests and coverage now above 90%.

## 0.0.11

### Patch Changes

- [#420](https://github.com/wpengine/hwptoolkit/pull/420) [`7bf9cb6`](https://github.com/wpengine/hwptoolkit/commit/7bf9cb625e1bee86af9436f8747ee1a24d1b273d) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Added data management for the logging plugin.

## 0.0.10

### Patch Changes

- [#412](https://github.com/wpengine/hwptoolkit/pull/412) [`4aae0fa`](https://github.com/wpengine/hwptoolkit/commit/4aae0fa56aedd64b30add448cf0df43e55b71455) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Various snags.

## 0.0.9

### Patch Changes

- [#416](https://github.com/wpengine/hwptoolkit/pull/416) [`641fa27`](https://github.com/wpengine/hwptoolkit/commit/641fa27d11a62fe2433a96299776732435a1eacd) Thanks [@ahuseyn](https://github.com/ahuseyn)! - Fixed security vulnerability by updating @wordpress/scripts from 30.18.0 to 30.24.0.
  Other packages bumped:

  - @changesets/cli
  - @playwright/test
  - @wordpress/e2e-test-utils-playwright
  - @wordpress/env
  - @wordpress/jest-console
  - @wordpress/scripts

## 0.0.8

### Patch Changes

- [#403](https://github.com/wpengine/hwptoolkit/pull/403) [`821908b`](https://github.com/wpengine/hwptoolkit/commit/821908b7a7b8743a44cdbdbd98eedfff7faac34a) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Added admin view, filters and CSV downloads.

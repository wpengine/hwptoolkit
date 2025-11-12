# WPGraphQL Logging

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

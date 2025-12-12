# HWP Previews

## 1.0.0

### Major Changes

- [#524](https://github.com/wpengine/hwptoolkit/pull/524) [`e6060f7`](https://github.com/wpengine/hwptoolkit/commit/e6060f7818cbf3abdd7a95d6f0e0defd71985d85) Thanks [@ahuseyn](https://github.com/ahuseyn)! - chore: Initial release of the HWP Previews Plugin
  - Updated Readme and docs
  - Upgraded wordpress-core into 6.9

## 0.0.15

### Patch Changes

- [#530](https://github.com/wpengine/hwptoolkit/pull/530) [`447530b`](https://github.com/wpengine/hwptoolkit/commit/447530b0b5a966bcc03fbbfd2b1c1c94264037e7) Thanks [@josephfusco](https://github.com/josephfusco)! - Verify WordPress 6.9 compatibility and update CI test matrix

  - Tested compatibility with WordPress 6.9
  - Updated CI test matrix to WordPress 6.9, 6.8, 6.7 (dropped 6.5, 6.6)
  - Updated dev dependencies (mockery/mockery to ^1.6, wp-graphql/wp-graphql-testcase to ^3.4)
  - Updated Docker defaults to WordPress 6.9
  - Reduced readme.txt tags to 5 for WordPress.org compliance

## 0.0.14

### Patch Changes

- [#525](https://github.com/wpengine/hwptoolkit/pull/525) [`a120c89`](https://github.com/wpengine/hwptoolkit/commit/a120c899d6f8fe2b11ec122e142da24d7859dbda) Thanks [@ahuseyn](https://github.com/ahuseyn)! - Add optional data cleanup on uninstall via HWP_PREVIEWS_UNINSTALL_PLUGIN constant

## 0.0.13

### Patch Changes

- [#519](https://github.com/wpengine/hwptoolkit/pull/519) [`0dff8ff`](https://github.com/wpengine/hwptoolkit/commit/0dff8fff953767fd33c2a864909955b28a620b75) Thanks [@ahuseyn](https://github.com/ahuseyn)! - Changed documentation links on wp-admin

## 0.0.12

### Patch Changes

- [#416](https://github.com/wpengine/hwptoolkit/pull/416) [`641fa27`](https://github.com/wpengine/hwptoolkit/commit/641fa27d11a62fe2433a96299776732435a1eacd) Thanks [@ahuseyn](https://github.com/ahuseyn)! - Fixed security vulnerability by updating @wordpress/scripts from 30.18.0 to 30.24.0.
  Other packages bumped:

  - @changesets/cli
  - @playwright/test
  - @wordpress/e2e-test-utils-playwright
  - @wordpress/env
  - @wordpress/jest-console
  - @wordpress/scripts

## 0.0.11

### Patch Changes

- [#406](https://github.com/wpengine/hwptoolkit/pull/406) [`7e5a134`](https://github.com/wpengine/hwptoolkit/commit/7e5a13476a3bfba0b92479ff2a03acc01875ff28) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Fix for the admin settings page to check capabilities.

## 0.0.10

### Patch Changes

- [#377](https://github.com/wpengine/hwptoolkit/pull/377) [`90e14d8`](https://github.com/wpengine/hwptoolkit/commit/90e14d85c338ca23d20678ff57cf8496c37585dd) Thanks [@colinmurphy](https://github.com/colinmurphy)! - Fixed priority issue with ACF and Previews.

## 0.0.9

### Patch Changes

- [#370](https://github.com/wpengine/hwptoolkit/pull/370) [`e8f3904`](https://github.com/wpengine/hwptoolkit/commit/e8f39041e114a05db37de6dc38714e278a2d9f95) Thanks [@colinmurphy](https://github.com/colinmurphy)! - bug: Fixed issue with retrieving post types before ACF hook initialisation.

## 0.0.8

### Patch Changes

- [#333](https://github.com/wpengine/hwptoolkit/pull/333) [`cf0a040`](https://github.com/wpengine/hwptoolkit/commit/cf0a0405ae04e0355745a81bf53b3c9065f10739) Thanks [@ahuseyn](https://github.com/ahuseyn)! - 1. Disables Faust front-end redirects for preview url's to solve the iframe conflict. 2. Introduced methods in Faust_Integration to replace Faust-generated preview URLs with the site’s home URL as needed.

## 0.0.7

### Patch Changes

- [#317](https://github.com/wpengine/hwptoolkit/pull/317) [`4dcff2d`](https://github.com/wpengine/hwptoolkit/commit/4dcff2dd2bbb36b62e525fa534b9d16faafaaa32) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Renamed composer name to "hwp-previews" to fix composer installation issue.

## 0.0.6

### Patch Changes

- [#312](https://github.com/wpengine/hwptoolkit/pull/312) [`456be7e`](https://github.com/wpengine/hwptoolkit/commit/456be7e7e477c547a6bf0a1c004639857ec4717d) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Changes to the github workflow to automate version updates. No existing changes to previews.

## 0.0.5

### Patch Changes

- [#308](https://github.com/wpengine/hwptoolkit/pull/308) [`8906c22`](https://github.com/wpengine/hwptoolkit/commit/8906c22fa5192776f80bd69325037ec261dee64c) Thanks [@colinmurphy](https://github.com/colinmurphy)! - docs: Updated docs for testing.

## 0.0.4

### Patch Changes

- [#292](https://github.com/wpengine/hwptoolkit/pull/292) [`8ab4aa5`](https://github.com/wpengine/hwptoolkit/commit/8ab4aa54c9595320e63315aae78dd899f54e81f3) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Updated action and filter docs.

## 0.0.3

### Patch Changes

- [#288](https://github.com/wpengine/hwptoolkit/pull/288) [`4e892ce`](https://github.com/wpengine/hwptoolkit/commit/4e892ce6474b7751c254211f0561d08dd698e5f3) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Minor changes to testing docs and updating workflows.

## 0.0.2

### Patch Changes

- [#284](https://github.com/wpengine/hwptoolkit/pull/284) [`9e3e968`](https://github.com/wpengine/hwptoolkit/commit/9e3e968c2cb8e09071a80f096a3a1f4b65aaba81) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Updated testing docs to trigger workflow process.

## 0.0.1

### Patch Changes

- [#282](https://github.com/wpengine/hwptoolkit/pull/282) [`9b9c496`](https://github.com/wpengine/hwptoolkit/commit/9b9c4968c3f83bb456e73d07845976e0b180e42a) Thanks [@colinmurphy](https://github.com/colinmurphy)! - chore: Initial beta release of hwp-previews.

## 0.0.1-beta

- Proof of concept. A WordPress plugin for headless previews.
- RFC - https://github.com/wpengine/hwptoolkit/discussions/67

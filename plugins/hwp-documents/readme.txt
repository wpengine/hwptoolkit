=== HWP Documents ===
Contributors: wpengine
Tags: headless, rest api, json, documents, app data
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.2
Stable tag: 0.0.0
License: GPL-2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Store your app's JSON documents in WordPress over REST, with versioned writes that reject stale updates instead of overwriting them.

== Description ==

**HWP Documents** gives your headless app a place to store JSON documents, such as settings, structured drafts or form submissions, in the WordPress site you already have.

Documents are grouped into collections. Each document has an ID that's unique within its collection, a version number, and data that's a JSON object or array. Every write names the version it was based on, and WordPress rejects a write based on an old version with `409 Conflict`, returning the current document so your app can merge or retry.

= Features =

* REST routes to read, create, update, delete, list and add documents under `headless/v1`
* Compare-and-swap writes: of two writers sending the same version, only one succeeds
* A dedicated database table, created on activation and kept when the plugin is deleted unless you opt in
* Access through WordPress users and the `read_hwp_documents` and `edit_hwp_documents` capabilities
* `hwp_document_saved` and `hwp_document_deleted` actions
* A read-only Tools → HWP Documents screen

= Documentation =

For REST examples and setup, see the [README on GitHub](https://github.com/wpengine/hwptoolkit/tree/main/plugins/hwp-documents).

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/hwp-documents/`, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Authenticate your app's REST requests, for example with an Application Password for a user who can manage options

== Frequently Asked Questions ==

= Who can read and write documents? =

Administrators can. Give other users the `read_hwp_documents` capability to read, and both `read_hwp_documents` and `edit_hwp_documents` to write.

= What happens to my documents if I deactivate or delete the plugin? =

Deactivating or deleting the plugin keeps them. To drop the documents table and every document in it when you delete the plugin, define `HWP_DOCUMENTS_UNINSTALL_PLUGIN` as `true` in `wp-config.php` first.

= Does it support WPGraphQL? =

Not yet. The plugin has REST routes only.

== Support ==

For support, feature requests, or bug reports, please visit our [GitHub issues page](https://github.com/wpengine/hwptoolkit/issues).

== Changelog ==

For the changelog, see [CHANGELOG.md](https://github.com/wpengine/hwptoolkit/blob/main/plugins/hwp-documents/CHANGELOG.md).

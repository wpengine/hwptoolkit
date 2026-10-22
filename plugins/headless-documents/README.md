# Headless Documents

Store your app's JSON documents in WordPress over REST. Every write names the version it was based on, and WordPress rejects a write based on an old version with `409 Conflict` instead of silently overwriting the newer one.

* [Join the Headless WordPress community on Discord.](https://discord.gg/headless-wordpress-836253505944813629)
* [RFC: Use WordPress as your app's data store](https://github.com/wpengine/hwptoolkit/issues/817)

-----

[![Version](https://img.shields.io/github/v/release/wpengine/hwptoolkit?include_prereleases&label=prerelease&filter=%40wpengine%2Fheadless-documents-wordpress-plugin-*)](https://github.com/wpengine/hwptoolkit/releases)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Testing Integration](https://github.com/wpengine/hwptoolkit/actions/workflows/codeception.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/codeception.yml)
[![Code Quality](https://github.com/wpengine/hwptoolkit/actions/workflows/code-quality.yml/badge.svg)](https://github.com/wpengine/hwptoolkit/actions/workflows/code-quality.yml)

-----

## Overview

Documents are grouped into collections, such as `preferences` or `drafts`. Each document has an ID that's unique within its collection, a version number, and data that's a JSON object or array. Use it for app data such as settings, structured drafts or form submissions, without adding a second database.

- Documents live in their own table, `wp_headless_documents` (with your site's table prefix).
- Writes and deletes use compare-and-swap on the version, so of two writers sending the same version, only one succeeds.
- WordPress users and capabilities control access.
- **Tools → Headless Documents** lists what your app has stored. The screen is read-only.

Realtime updates, complex queries and high write volume are out of scope.

## Setup

1. Download the plugin from [Releases](https://github.com/wpengine/hwptoolkit/releases) and upload it to WordPress, or copy this folder into `wp-content/plugins` and run `composer install --no-dev` in it.
2. Activate it. Activation creates the documents table. On a multisite network, each site creates its table on its first request after the plugin is network activated.
3. Authenticate your app's requests with any WordPress REST API authentication, such as [Application Passwords](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/).

Deactivating or deleting the plugin keeps your documents. To also drop the table, and every document in it, on every site when you delete the plugin, add this to `wp-config.php` first:

```php
define( 'HEADLESS_DOCUMENTS_UNINSTALL_PLUGIN', true );
```

### Permissions

Users who can `manage_options` (administrators) can read and write documents. To give other users access, add capabilities to their role or account:

| Capability | Allows |
|---|---|
| `read_headless_documents` | Reading and listing documents |
| `read_headless_documents` and `edit_headless_documents` | Creating, updating and deleting documents |

Writers need both, because a rejected write returns the current document. These capabilities don't grant access to anything else in WordPress.

```php
get_role( 'editor' )->add_cap( 'read_headless_documents' );
```

## REST API

All routes are under `/wp-json/headless/v1/collections/{collection}/documents`.

| Method | Route | Does |
|---|---|---|
| `GET` | `/{id}` | Read a document |
| `PUT` | `/{id}` | Create (version `0`) or update (current version) a document |
| `DELETE` | `/{id}?version=N` | Delete the current version of a document |
| `GET` | `?limit=&cursor=` | List a collection's documents, newest first |
| `POST` | | Add a document with a generated ID |

Collection names and document IDs use lowercase letters, numbers and hyphens, up to 64 and 190 characters. A collection exists as soon as it has a document.

### Read a document

```http
GET /wp-json/headless/v1/collections/preferences/documents/site-settings
```

```json
{
	"collection": "preferences",
	"id": "site-settings",
	"version": 3,
	"data": { "theme": "dark" },
	"created": "2026-10-01T09:30:00Z",
	"modified": "2026-10-20T15:04:05Z"
}
```

`created` and `modified` are UTC timestamps. A document that doesn't exist returns `404` with the code `headless_document_not_found`.

### Create or update a document

Send the version you read, or `0` to create a new document:

```http
PUT /wp-json/headless/v1/collections/preferences/documents/site-settings
Content-Type: application/json

{ "version": 3, "data": { "theme": "light" } }
```

A successful write returns the document with its new version: `201 Created` for a new document and `200 OK` for an update.

If the document has changed since the version you sent, or a create finds that the document already exists, the write returns `409 Conflict` and changes nothing. The response includes the current document, or `null` if it doesn't exist, so you can merge the changes or ask the user what to keep, then retry with the current version:

```json
{
	"code": "headless_document_conflict",
	"message": "The document has changed since the version this write was based on.",
	"data": {
		"status": 409,
		"collection": "preferences",
		"id": "site-settings",
		"version": 4,
		"document": { "collection": "preferences", "id": "site-settings", "version": 4, "data": { "theme": "blue" }, "created": "…", "modified": "…" }
	}
}
```

A deleted document keeps counting versions, so a client holding a version from before the delete can't overwrite a document created again with the same ID.

### Add a document with a generated ID

To create a document without choosing an ID, for example a form submission, `POST` to the collection. WordPress generates a random 20-character ID and returns the new document with `201 Created`:

```http
POST /wp-json/headless/v1/collections/waitlist/documents
Content-Type: application/json

{ "data": { "email": "ada@example.com" } }
```

### List documents

Documents are listed newest first, by when each was first created; updating a document doesn't move it. Pass `limit` (1 to 100, default 20). To get the next page, pass the previous response's `next_cursor` as `cursor`. `next_cursor` is `null` on the last page.

```http
GET /wp-json/headless/v1/collections/waitlist/documents?limit=20
```

```json
{
	"documents": [
		{ "collection": "waitlist", "id": "3f9c2a7e1b0d4c6a8e5f", "version": 1, "data": { "email": "ada@example.com" }, "created": "…", "modified": "…" }
	],
	"next_cursor": null
}
```

### Delete a document

Send the version you read. Like a write, a stale version returns `409 Conflict`, and a document that doesn't exist returns `404`:

```http
DELETE /wp-json/headless/v1/collections/waitlist/documents/3f9c2a7e1b0d4c6a8e5f?version=1
```

```json
{ "deleted": true, "previous": { "collection": "waitlist", "id": "3f9c2a7e1b0d4c6a8e5f", "version": 1, "data": { "email": "ada@example.com" }, "created": "…", "modified": "…" } }
```

### Errors

| Status | Code | When |
|---|---|---|
| `400` | `rest_invalid_json`, `rest_missing_callback_param`, `rest_invalid_param` | The body isn't valid JSON, or `version` or `data` is missing or invalid. `data` must be a JSON object or array. |
| `401`, `403` | `rest_forbidden` | The request isn't authenticated, or the user lacks the capability. |
| `404` | `headless_document_not_found` | The document doesn't exist. |
| `409` | `headless_document_conflict` | The version is stale. |
| `413` | `headless_document_too_large` | The document is larger than the maximum size. |

## Actions and filters

| Hook | Type | Arguments |
|---|---|---|
| `headless_document_saved` | Action | `array $document`, `bool $created` |
| `headless_document_deleted` | Action | `array $previous` |
| `headless_document_max_bytes` | Filter | `int $max_bytes`, default 100 KB of encoded JSON |

For example, to send an email for each waitlist signup:

```php
add_action(
	'headless_document_saved',
	function ( $document, $created ) {
		if ( $created && 'waitlist' === $document['collection'] ) {
			wp_mail( get_option( 'admin_email' ), 'New waitlist signup', $document['data']->email );
		}
	},
	10,
	2
);
```

Document data is decoded with JSON objects as PHP objects, so an empty object stays `{}`.

## Testing

The plugin uses Codeception with WPBrowser, like the other toolkit plugins. With Docker running:

```bash
cp .docker/.env.ci .env
composer install
composer run docker:build
docker compose --env-file .env up --detach
docker exec $(docker compose ps -q wordpress) init-docker.sh
composer run test
```

Run the code quality checks with `composer run qa`.

## Contributing

If you feel like something is missing or you want to add documentation, we encourage you to contribute! Please check out our [Contributing Guide](https://github.com/wpengine/hwptoolkit/blob/main/CONTRIBUTING.md) for more details.

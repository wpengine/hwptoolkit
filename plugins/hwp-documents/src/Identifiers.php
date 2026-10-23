<?php

declare(strict_types=1);

namespace HWP\Documents;

/**
 * Names the plugin registers with WordPress.
 *
 * Hook names and the text domain are written out where they're used, because
 * WordPress tooling only finds literal strings.
 *
 * @package HWP\Documents
 */
final class Identifiers {
	/**
	 * REST API namespace for the document routes.
	 *
	 * @var string
	 */
	public const REST_NAMESPACE = 'headless/v1';

	/**
	 * Documents table name, without the site's table prefix.
	 *
	 * @var string
	 */
	public const TABLE = 'hwp_documents';

	/**
	 * Option holding the installed documents table schema version.
	 *
	 * @var string
	 */
	public const DB_VERSION_OPTION = 'hwp_documents_db_version';

	/**
	 * Name of the lock held while the documents table is created or updated.
	 *
	 * @var string
	 */
	public const INSTALL_LOCK = 'hwp_documents_db';

	/**
	 * Capability needed to read documents.
	 *
	 * @var string
	 */
	public const READ_CAPABILITY = 'read_hwp_documents';

	/**
	 * Capability needed to create, update and delete documents.
	 *
	 * @var string
	 */
	public const EDIT_CAPABILITY = 'edit_hwp_documents';

	/**
	 * Prefix of the error codes the document routes return.
	 *
	 * @var string
	 */
	public const ERROR_PREFIX = 'hwp_document_';

	/**
	 * Slug of the Tools screen that lists documents.
	 *
	 * @var string
	 */
	public const ADMIN_PAGE_SLUG = 'hwp-documents';
}

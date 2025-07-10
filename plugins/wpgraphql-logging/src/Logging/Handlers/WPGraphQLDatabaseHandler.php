<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Logging\Handlers;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class WPGraphQLDatabaseHandler extends AbstractProcessingHandler {
	/**
	 * @inheritDoc
	 */
	public function __construct( $level = Logger::INFO, $bubble = true ) {
		parent::__construct( $level, $bubble );
	}

	/**
	 * {@inheritDoc}
	 */
	protected function write( array $record ): void {

		$entity = new \WPGraphQL\Logging\Database\LoggingEntity();
		$entity->setMessage( $record['message'] ?? '');
		$entity->setContext( $record['context'] ?? [] );
		$entity->setLevel( $record['level'] ?? Logger::INFO );
		$entity->setLevelName( $record['level_name'] ?? 'INFO' );
		$entity->setChannel( $record['channel'] ?? 'wpgraphql_logging' );
		$entity->setDatetime( $record['datetime'] ?? new \DateTimeImmutable());
		$entity->setExtra( $record['extra'] ?? [] );
		$entity->setFormatted( $record['formatted'] ?? '' );
		$entity->write();
	}
}

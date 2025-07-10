<?php

declare( strict_types=1 );

namespace WPGraphQL\Logging\Database;

use DateTimeImmutable;

class LoggingEntity {
	private int $id;
	private int $level;
	private string $level_name;
	private string $message;
	private array $context = [];
	private array $extra = [];
	private ?string $formatted = null;
	private DateTimeImmutable  $datetime;
	private ?string $channel = null;

	public static function read( int $id ): ?self {
		global $wpdb;
		$table = self::getTableName();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ), ARRAY_A );
		if ( ! $row ) {
			return null;
		}
		$entity = new self();
		$entity->setId( (int) $row['id'] );
		$entity->setLevel( $row['level'] );
		$entity->setLevelName( $row['level_name'] );
		$entity->setMessage( $row['message'] );
		$entity->setContext( $row['context'] );
		$entity->setExtra( $row['extra'] );
		$entity->setFormatted( $row['formatted'] );
		$entity->setDatetime( $row['datetime'] );
		$entity->setChannel( $row['channel'] );

		return $entity;
	}

	public function write(): void {
		// @TODO some type of try/catch or error handling here.
		global $wpdb;
		$table = self::getTableName();
		$data  = [
			'level'      => $this->getLevel(),
			'level_name' => $this->getLevelName(),
			'message'    => $this->getMessage(),
			'context'    => wp_json_encode($this->getContext()),
			'extra'      => wp_json_encode($this->getExtra()),
			'formatted'  => $this->getFormatted(),
			'datetime'   => $this->getDatetime(),
			'channel'    => $this->getChannel(),
		];


		if ( isset( $this->id ) ) {
			$wpdb->update( $table, $data, [ 'id' => $this->id ] );
		} else {
			$wpdb->insert( $table, $data );
			$this->setId( (int) $wpdb->insert_id );
		}
	}

	public function getLevel(): int {
		return $this->level;
	}

	public function setLevel( int $level ): void {
		$this->level = $level;
	}

	public function getLevelName(): string {
		return $this->level_name;
	}

	public function setLevelName( string $level_name ): void {
		$this->level_name = $level_name;
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function setMessage( string $message ): void {
		$this->message = $message;
	}

	public function getContext(): array {
		return $this->context;
	}

	public function setContext( array $context ): void {
		$this->context = $context;
	}

	public function getExtra(): array {
		return $this->extra;
	}

	public function setExtra( array $extra ): void {
		$this->extra = $extra;
	}

	public function getFormatted(): ?string {
		return $this->formatted;
	}

	public function setFormatted( ?string $formatted ): void {
		$this->formatted = $formatted;
	}

	public function getDatetime(): DateTimeImmutable  {
		return $this->datetime;
	}

	public function setDatetime( DateTimeImmutable  $datetime ): void {
		$this->datetime = $datetime;
	}

	public function getChannel(): ?string {
		return $this->channel;
	}

	public function setChannel( ?string $channel ): void {
		$this->channel = $channel;
	}

	public function getId(): int {
		return $this->id;
	}

	public function setId( int $id ): void {
		$this->id = $id;
	}

	public static function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . 'wpgraphql_logging';
	}

	/**
	 * @TODO POC
	 *
	 * This is probably not the best way to do this, but it works for now.
	 */
	public static function get_schema(): string {
		return '
			CREATE TABLE ' . self::getTableName() . " (
				id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				level INT NOT NULL,
				level_name VARCHAR(50) NOT NULL,
				message TEXT NOT NULL,
				context JSON NULL,
				extra JSON NULL,
				formatted TEXT NULL,
				datetime DATETIME NOT NULL,
				channel VARCHAR(100) NULL
			) {$GLOBALS['wpdb']->get_charset_collate()};
		";
	}
}

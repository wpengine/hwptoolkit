<?php

declare(strict_types=1);

namespace HWP\Previews\Settings;

class Tabbed_Settings {

	/**
	 * Settings option group.
	 *
	 * @var string
	 */
	private string $option_group;

	/**
	 * Settings option name.
	 *
	 * @var string
	 */
	private string $option_name;

	/**
	 * Tabs as items allowed in the settings.
	 *
	 * @var array<string>
	 */
	private array $tabs;

	/**
	 * Array of sanitization options where keys area options and values are types.
	 *
	 * @var array<string, string>
	 */
	private array $sanitization_options;

	/**
	 * Constructor.
	 *
	 * @param string                $option_group Settings option group.
	 * @param string                $option_name Settings option name.
	 * @param array<string>         $tabs Tabs array as items allowed in the settings.
	 * @param array<string, string> $sanitization_options Array of sanitization options where keys are options and values are types.
	 */
	public function __construct(
		string $option_group,
		string $option_name,
		array $tabs,
		array $sanitization_options
	) {
		$this->option_group         = $option_group;
		$this->option_name          = $option_name;
		$this->tabs                 = $tabs;
		$this->sanitization_options = $sanitization_options;
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			$this->option_group,
			$this->option_name,
			[
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'type'              => 'array',
				'default'           => [],
			]
		);
	}

	/**
	 * Sanitize and merge new settings per-tab, pruning unknown fields.
	 *
	 * @param array<string,mixed> $new_input New settings input for the specific tab that comes from the form for the sanitization.
	 *
	 * @return array<string,mixed>
	 */
	public function sanitize_settings( array $new_input ): array {
		$old_input = (array) get_option( $this->option_name, [] );

		// Remove redundant tabs.
		$old_input = array_intersect_key( $old_input, array_flip( $this->tabs ) );

		$tab = array_keys( $new_input );
		if ( ! isset( $tab[0] ) ) {
			return $old_input; // Wrong settings structure.
		}

		$tab_to_sanitize = (string) $tab[0];
		if ( ! is_array( $new_input[ $tab_to_sanitize ] ) ) {
			return $old_input; // Wrong settings structure.
		}

		// Sanitize the fields in the tab.
		$sanitized_fields = [];
		foreach ( $new_input[ $tab_to_sanitize ] as $key => $value ) {
			if ( ! isset( $this->sanitization_options[ $key ] ) ) {
				continue;
			}

			$sanitized_fields[ $key ] = $this->sanitize_field( $key, $value );
		}

		// Merge the sanitized fields with the old input.
		$old_input[ $tab_to_sanitize ] = $sanitized_fields;

		return $old_input;
	}

	/**
	 * Sanitize a single field value by type.
	 *
	 * @param string $key   Field key.
	 * @param mixed  $value Raw value.
	 *
	 * @return bool|int|string
	 */
	private function sanitize_field( string $key, $value ): bool|int|string {
		$type = $this->sanitization_options[ $key ];

		switch ( $type ) {
			case 'bool':
				return ! empty( $value );
			case 'int':
				return intval( $value );
			case 'text':
			default:
				return sanitize_text_field( (string) $value );
		}
	}

}

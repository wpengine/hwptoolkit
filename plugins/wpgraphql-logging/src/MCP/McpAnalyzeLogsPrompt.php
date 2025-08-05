<?php

declare(strict_types=1);

namespace WPGraphQL\Logging\MCP;

use Automattic\WordpressMcp\Core\RegisterMcpPrompt;

/**
 * Class McpAnalyzeLogs
 *
 * This class registers a prompt to allow an AI to analyze WPGraphQL log data
 * and provide diagnostic feedback and solutions to the developer.
 *
 * @package WPGraphQL\Logging\MCP
 */
class McpAnalyzeLogsPrompt {
	/**
	 * The single instance of the class.
	 *
	 * @var \WPGraphQL\Logging\MCP\McpAnalyzeLogsPrompt|null
	 */
	private static ?McpAnalyzeLogsPrompt $instance = null;

	/**
	 * Get or create the single instance of the class.
	 */
	public static function init(): McpAnalyzeLogsPrompt {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Set up the action hook to register the prompt.
	 */
	public function setup(): void {
		add_action( 'wordpress_mcp_init', [ $this, 'register_prompt' ] );
	}

	/**
	 * Register the prompt with the WordPress MCP framework.
	 */
	public function register_prompt(): void {
		new RegisterMcpPrompt(
			[
				'name'        => 'analyze-logs',
				'description' => 'Analyzes WPGraphQL logs to diagnose issues and provide solutions.',
				'arguments'   => [
					[
						'name'        => 'timeframe_hours',
						'description' => 'The number of hours to look back. Defaults to 24.',
						'required'    => false,
						'type'        => 'integer',
					],
					[
						'name'        => 'level',
						'description' => "The log level to filter by (e.g., 'ERROR', 'WARNING', 'ERRORS_AND_ABOVE'). Optional.",
						'required'    => false,
						'type'        => 'string',
					],
					[
						'name'        => 'search_text',
						'description' => 'Text to search for within the log message. Optional.',
						'required'    => false,
						'type'        => 'string',
					],
				],
			],
			$this->messages()
		);
	}

	/**
	 * Get the messages for the prompt.
	 *
	 * @return array The messages to be passed to the AI.
	 */
	public function messages(): array {
		return [
			[
				'role'    => 'user',
				'content' => [
					'type' => 'text',
					'text' => 'Using the `wpgraphql_logging_custom_tool` with arguments `timeframe_hours: {{timeframe_hours}}`, `level: {{level}}`, and `search_text: {{search_text}}`, retrieve all relevant logs.
                    
Analyze the retrieved logs, identify any potential issues, explain the source of the problem, and provide actionable suggestions for the developer to fix the problem.',
				],
			],
		];
	}
}

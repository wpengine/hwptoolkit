<?php


namespace WPGraphQL\Velocity\Rules;


// @TODO Not yet definned


/**
 * Result class for rule analysis
 */
class RuleResult
{
	public $ruleId;
	public $level;
	public $message;
	public $metadata;

	public function __construct(string $ruleId, string $level, string $message, array $metadata = [])
	{
		$this->ruleId = $ruleId;
		$this->level = $level;
		$this->message = $message;
		$this->metadata = $metadata;
	}

	/**
	 * Add a recommendation to the result
	 *
	 * @param array $recommendation
	 * @return void
	 */
	public function get_data(): array
	{
		// @TODO not fully thought out yet, but this is a start
		return [
			$this->ruleId => [
				'level' => $this->level,
				'message' => $this->message,
				'metadata' => $this->metadata,
			]
		];
	}
}

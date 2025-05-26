<?php
namespace WPGraphQL\Webhooks\DTO;

use \WPGraphQL\Webhooks\Events\Event;

class WebhookDTO {
    public string $type;
    public string $label;
    public string $description;
    public array $config;
    /**
	 * @var Event[]
	 */
    public array $events;

    public function __construct(string $type, string $label = '', string $description = '', array $config = [], array $events = []) {
        $this->type = $type;
        $this->label = $label ?: $type;
        $this->description = $description;
        $this->config = $config;
        $this->events = $events;
    }
}
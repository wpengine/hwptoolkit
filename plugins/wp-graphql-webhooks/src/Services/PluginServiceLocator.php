<?php
namespace WPGraphQL\Webhooks\Services;
use WPGraphQL\Webhooks\Services\Interfaces\ServiceLocator;

use UnexpectedValueException;

class PluginServiceLocator implements ServiceLocator {
    /**
     * @var callable[]
     */
    private $factories = [];

    /**
     * @var object[]
     */
    private $instances = [];

    public function set(string $name, callable $factory): void {
        $this->factories[$name] = $factory;
        unset($this->instances[$name]);
    }

    public function has(string $name): bool {
        return isset($this->factories[$name]);
    }

    public function get(string $name) {
        if (!isset($this->factories[$name])) {
            throw new UnexpectedValueException("Service not found: {$name}");
        }

        if (!isset($this->instances[$name])) {
            $this->instances[$name] = call_user_func($this->factories[$name]);
        }

        return $this->instances[$name];
    }
}
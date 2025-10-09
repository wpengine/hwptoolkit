## How to Add a New Rule (Query must contain string)

This guide shows how to create a custom logging rule that only passes when the GraphQL query contains a specific substring, and how to register it with the RuleManager.

### What is a Rule?

Rules implement `WPGraphQL\Logging\Logger\Rules\LoggingRuleInterface` and are evaluated by the `RuleManager`. All rules must pass for logging to proceed.

Interface reference (methods):
- `passes( array $config, ?string $query_string ): bool`
- `get_name(): string`

### Step 1: Create the rule class

Create a class that implements the interface and returns true only if the query contains a given substring.

```php
<?php
namespace MyPlugin\Logging\Rules;

use WPGraphQL\Logging\Logger\Rules\LoggingRuleInterface;

class ContainsStringRule implements LoggingRuleInterface {
    public function __construct( private readonly string $needle ) {}

    public function passes( array $config, ?string $query_string = null ): bool {
        if ( ! is_string( $query_string ) || '' === trim( $query_string ) ) {
            return false; // No query => fail
        }
        return stripos( $query_string, $this->needle ) !== false;
    }

    public function get_name(): string {
        // Ensure unique name per rule; adjust if you need multiple variants
        return 'contains_string_rule';
    }
}
```

### Step 2: Register the rule with the RuleManager

Use the `wpgraphql_logging_rule_manager` filter to add your rule. This runs when the logger helper initializes rules.

```php
<?php
add_filter( 'wpgraphql_logging_rule_manager', function( $rule_manager ) {
    // Only pass when the query contains the word "GetPost"
    $rule_manager->add_rule( new \MyPlugin\Logging\Rules\ContainsStringRule( 'GetPost' ) );
    return $rule_manager;
});
```

### Step 3: Verify

- GraphQL requests whose query string contains `GetPost` will be logged (assuming other rules also pass).
- Requests without that substring will be skipped by this rule, causing `is_enabled` to be false.

### Related

- See the [Logger reference](../reference/logging.md#filter-wpgraphql_logging_rule_manager) for the `wpgraphql_logging_rule_manager` filter.

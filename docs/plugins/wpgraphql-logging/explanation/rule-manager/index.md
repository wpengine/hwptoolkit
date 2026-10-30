---
title: "Explanation: Rule Manager"
description: "Understanding the Rule Manager system in WPGraphQL Logging Plugin and how it controls which GraphQL queries get logged."
---

The Rule Manager is the gatekeeper of the WPGraphQL Logging plugin. Before any event enters the logging pipeline, before processors enrich data or handlers write to storage, the Rule Manager evaluates a series of conditions to determine whether logging should proceed. This architecture provides fine-grained control over what gets logged, enabling developers to reduce noise, comply with privacy requirements, and optimize performance.

## Why a Rule System?

Logging every GraphQL query on a high-traffic site creates several challenges:

1. **Database bloat**: Thousands of queries per hour can fill gigabytes of storage quickly.
2. **Performance impact**: Even buffered writes add overhead that compounds at scale.
3. **Signal-to-noise ratio**: Introspection queries, health checks, and automated polling drown out meaningful data.
4. **Privacy concerns**: Logging everything may capture sensitive user data unnecessarily.
5. **Cost**: Storage, processing, and retention all scale with log volume.

The Rule Manager addresses these challenges by providing a composable system of checks that must all pass for logging to occur. This "all rules must pass" approach implements an AND logic: if any single rule fails, logging stops immediately, short-circuiting the evaluation chain for maximum efficiency.

## Architectural Design

### The LoggingRuleInterface

Every rule implements `LoggingRuleInterface`, which requires two methods:

- **passes( array $config, ?string $query_string ): bool**: Evaluates whether this specific rule allows logging. Returns `true` if logging should continue, `false` to block it.
- **get_name(): string**: Returns a unique identifier for the rule, used for debugging and rule management.

This interface creates a consistent contract: rules are stateless, side-effect-free predicates that make binary decisions based on configuration and query content.

### The RuleManager Class

The `RuleManager` maintains a registry of rules and provides a single method: `all_rules_pass()`. This method iterates through registered rules in order, calling `passes()` on each. If any rule returns `false`, iteration stops immediately and `all_rules_pass()` returns `false`. Only if every rule returns `true` does the manager allow logging to proceed.

This design has several advantages:

- **Short-circuit evaluation**: Expensive rules (IP checks, regex matching) never run if a cheap rule (enabled check) fails first.
- **Composition**: Complex conditions emerge from combining simple rules rather than writing nested conditionals.
- **Testability**: Each rule is independently testable without mocking the entire logging system.
- **Extensibility**: Add or remove rules via filters without modifying core logic.

## Default Rules

The plugin registers five default rules via `LoggingHelper::get_rule_manager()`, evaluated in this order:

### 1. QueryNullRule

Checks if the query string exists and is not empty. Prevents logging when WPGraphQL encounters malformed or missing queries, which would pollute logs with unhelpful entries. This rule typically fails on OPTIONS requests or invalid HTTP methods.

### 2. SamplingRateRule

Implements probabilistic logging by checking a random number against the configured sampling rate (1-100%). The plugin defaults to a 10% sampling rate, meaning only 1 in 10 queries proceeds to logging. This rule is critical for high-traffic sites where logging everything is impractical. The randomness (using `wp_rand`) ensures representative samples without systematic bias.

### 3. EnabledRule

Checks the `BasicConfigurationTab::ENABLED` setting. This simple boolean provides a master kill switch: administrators can disable all logging instantly without uninstalling the plugin. Failed rules don't log errors, making this a clean way to pause logging during maintenance or troubleshooting.

### 4. IpRestrictionsRule

Evaluates whether the request IP matches any configured allowlist IPs. When no IPs are configured, the rule passes (allows logging from all IPs). When IPs are configured, only requests from those specific IPs proceed to logging. This enables targeted logging: only log queries from specific developer IPs during debugging. IP-based filtering complements sampling by adding deterministic control over which clients get logged.

### 5. ExcludeQueryRule

Performs substring matching against the query string to exclude specific operations. The default configuration excludes `__schema` (introspection queries) and `GetSeedNode` (Faust.js internal queries). These automated queries add no debugging value but can dominate log volume on some sites. The rule uses case-insensitive matching to catch variations in query formatting.

## Extensibility

The plugin provides two filters for extending the rule system:

**wpgraphql_logging_rules**: Applied in `LoggingHelper::get_rule_manager()` before rules are registered. This is the primary extension point for custom rule logic. Because the filter receives the full array, you can add, remove, or replace rules entirely.

**wpgraphql_logging_is_enabled**: Applied in `LoggingHelper::is_logging_enabled()` after all rules evaluate. This filter receives the boolean result of `all_rules_pass()` and the configuration array, enabling dynamic control that bypasses all rules when necessary.

## Rule Evaluation Timing

Rules are evaluated multiple times during a request lifecycle, not just once. The `LoggingHelper::should_log_event()` method checks rules before logging each event (pre-request, execution, response). Some rules receive the query string, others don't, meaning the same request might pass rules for one event but fail for another. The configuration array is retrieved once per request and passed to all rules, ensuring consistent decisions within a single request.

## Performance Characteristics

The Rule Manager is designed for minimal overhead through lazy initialization (instantiates only when first called), short-circuit evaluation (early rule failures prevent later rule execution), and a singleton pattern (one instance serves all logging decisions in a request). All rules register once per request, not per log operation. The most expensive rule should be placed last in the filter to ensure it only runs when all cheaper rules pass.

## Related Documentation

For practical implementation examples, see:

- [How to add or remove a Rule](../../how-to/logger-add-remove-rules/index.md)

---
title: "Explanation: Rule Manager"
description: "How the WPGraphQL Logging plugin decides whether to log a GraphQL event."
---

Before the plugin logs an event, the Rule Manager runs a list of rules, and the event is logged only if every rule passes. This happens before any processor or handler runs, so a skipped event costs almost nothing.

## Why rules

Logging every GraphQL request on a busy site fills the database quickly, and much of it is noise such as introspection queries. Rules let you cut the volume down to what you need: a sample of traffic, requests from your own IP, or everything except a few known operations. Each rule checks one thing, and you combine them by adding or removing rules.

## How evaluation works

Every rule implements `LoggingRuleInterface`, which has two methods. `passes( array $config, ?string $query_string ): bool` returns whether logging should continue, and `get_name(): string` returns an identifier for debugging. Rules get the plugin configuration and the query string and should not have side effects.

`RuleManager::all_rules_pass()` calls `passes()` on each rule in the order they were added and stops at the first rule that returns `false`. Put cheap rules before expensive ones so the expensive ones run less often.

`QueryActionLogger` and `QueryFilterLogger` each build their `RuleManager` the first time they need it and reuse it after that.

## Default rules

`LoggingHelper::get_rule_manager()` registers five rules in this order.

`QueryNullRule` fails when there is no query string or it is empty after trimming, so malformed requests are not logged.

`SamplingRateRule` logs a percentage of events. It picks a number from 1 to 100 with `wp_rand()` and passes if the number is at or below the sampling setting, which defaults to 10. A setting of 0 never logs.

`EnabledRule` checks the Enabled setting in Basic Configuration, so you can turn logging off without deactivating the plugin.

`IpRestrictionsRule` checks the request IP against the IP Restrictions setting. If the setting is empty, every IP passes. If it lists addresses, only requests from those addresses are logged.

`ExcludeQueryRule` fails when the query contains any of the excluded strings, ignoring case. The default list is `__schema` for introspection queries and `GetSeedNode`, an internal Faust.js query.

## Extending

The `wpgraphql_logging_rules` filter receives the default rule list before it is registered, so you can add, remove, reorder or replace rules.

The `wpgraphql_logging_is_enabled` filter runs after the rules with their combined result and the configuration. Whatever it returns is final, so it can turn logging on or off regardless of the rules.

## When rules run

The rules run for each event, not once per request. `LoggingHelper::should_log_event()` checks them and then checks whether the event is one of the selected log points. Because the sampling roll happens each time, at 10% a request can have some of its events logged and others skipped.

## Related documentation

- [How to add or remove a Rule](../../how-to/logger-add-remove-rules/index.md)

# WPGraphQL Debug Extensions

## Overview

A WordPress plugin that extends [WPGraphQL](https://www.wpgraphql.com/) to provide advanced debugging, performance analysis, and metric collection capabilities for headless WordPress applications. This plugin is designed to assist developers in identifying and resolving key performance issues within their GraphQL queries and underlying data fetching.

**⚠️ Work in Progress:** This plugin is under active development and not yet production-ready.

## Features

This plugin aims to offer two primary sets of features:

### 1. Enhanced Query Analyzer with Heuristic Rules

This feature set extends the existing WPGraphQL QueryAnalyzer to incorporate heuristic rules that automatically detect and flag GraphQL query patterns likely to lead to performance degradation.

* **Proactive Performance Identification:** Identifies potential performance issues during development.
* **Guidance on Best Practices:** Steers developers towards more efficient query implementations without manual intervention.
* **Direct WPGraphQL Integration:** Seamlessly integrates with the WPGraphQL environment.
* **Configurable Rules:** Allows developers to configure rule thresholds (e.g., maximum nesting depth) and enable/disable specific rules.

**Key Heuristic Rules:**
* **Nested Queries:** Detects and flags deeply nested GraphQL queries (e.g., more than 3 levels of nested relations) that could lead to N+1 problems.
* **Excessive Field Selection:** Identifies queries that select an unusually large number of fields from a single type, suggesting over-fetching of data.
* **Unfiltered Lists:** Warns about queries requesting large lists of items without appropriate filtering or pagination, potentially leading to heavy database loads.
* **Complexity Score:** Calculates a "complexity score" for each query based on factors like nesting depth, number of fields, and relationships, flagging queries that exceed a predefined threshold.

### 2. Tracing with Additional Metrics and Debug Toolbar

This feature set augments WPGraphQL's QueryLog and TracingLog to capture more detailed metrics and tracing information (e.g., memory usage, CPU time, database query count). This data can then be consumed by a debug toolbar or IDE, enabling developers to drill down into performance at the GraphQL field and SQL query level.

* **Granular Performance Insights:** Provides detailed performance data for deep-dive analysis at the field and SQL query level.
* **Identification of Bottlenecks:** Helps pinpoint memory leaks and inefficient data retrieval.
* **Development-only Overhead:** Logging detail can be locked behind a flag, enabling it only during development.

**Key Tracing Enhancements:**
* **Enhanced Tracing Data:** Extends QueryLog and TracingLog to include memory usage, CPU time, and database query count for each GraphQL field and associated SQL query.
* **Debug Toolbar/IDE Integration (Prototype):** Aims to develop a prototype debug toolbar or integrate with an existing IDE plugin to visually represent the enhanced logging data, allowing developers to drill down into performance metrics for individual GraphQL fields and SQL queries.

## Requirements

* WordPress 6.0+
* WPGraphQL 2.0.0+
* PHP 7.4+

## Installation

Clone the repository or download the latest release and place it in your WordPress `plugins` directory. After placing the plugin files, run `composer install` within the plugin directory to install its dependencies.

## Documentation

For detailed usage instructions, developer references, and examples, please visit the [Documentation](docs/index.md) folder included with this plugin.

## License
BSD-0-Clause
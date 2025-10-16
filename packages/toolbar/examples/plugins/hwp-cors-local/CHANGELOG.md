# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-01-09

### Added
- Initial release
- CORS header management for local headless WordPress development
- Configurable frontend origins via HEADLESS_FRONTEND_URL constant
- Automatic preflight OPTIONS request handling
- Support for credentials, custom headers, and multiple HTTP methods
- Environment-aware activation (local development only)
- Security-first design preventing production activation

[0.1.0]: https://github.com/wpengine/hwptoolkit/releases/tag/hwp-cors-local-v0.1.0

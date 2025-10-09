# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-01-09

### Added
- Initial release of @wpengine/hwp-toolbar
- Framework-agnostic core toolbar with modern state management
- React adapter with hooks (useToolbar, useToolbarState, useWordPressContext)
- Vanilla JavaScript renderer for non-React applications
- WordPress context management with separate API (setWordPressContext)
- Plugin system via register() method for custom toolbar nodes
- Configurable positioning (top/bottom) and theming (light/dark/auto)
- Minimal base styles for full developer control
- TypeScript support with complete type definitions
- CSS exports for styling integration

[0.1.0]: https://github.com/wpengine/hwptoolkit/releases/tag/toolbar-v0.1.0

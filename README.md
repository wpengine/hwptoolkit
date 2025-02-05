# Headless WordPress Toolkit

A modern toolkit for building headless WordPress applications. Provides CLI tools, plugins, and best practices for decoupling WordPress into a powerful headless CMS.

## Features

- **CLI Tools**: Command-line interface for managing WordPress plugins and their status
- **Core Plugins**: Essential WordPress plugins optimized for headless architecture
- **Development Tools**: Local development environment and testing utilities
- **Best Practices**: Standardized approaches for common headless WordPress patterns

## Quick Start

```bash
# Check CLI plugin status
pnpm dlx @hwp/cli status

# List installed HWP plugins
pnpm dlx @hwp/cli plugins
```

Example output:
```bash
# Status command
WordPress Status:

Environment: local
URL: http://localhost:8889
Version: 6.4.2
Debug Mode: Disabled

HWP Status:

Plugin: hwp-cli
Status: Active
Version: 1.0.0
REST API: Available

# Plugins command
Installed HWP Plugins:

HWP CLI Plugin v1.0.0
Status: Active
NPM Package: Not specified
---
```

## Documentation

- [Getting Started](docs/getting-started.md)
- [CLI Commands](docs/cli.md)
- [Plugin Development](docs/plugins.md)
- [Best Practices](docs/best-practices.md)
- [Examples](examples/README.md)

## Project Standards

- ES Modules for all JavaScript code
- Named exports over default exports
- PNPM for package management
- WordPress VIP Coding Standards for PHP
- Playwright for end-to-end testing
- Jest for unit testing
- Comprehensive documentation and examples

## Best Practices

1. **Decoupled Architecture**: Maintain clear separation between WordPress backend and frontend applications. Use REST API or GraphQL for communication.

2. **Version Control**: Track plugin and theme code separately from content. Use `.gitignore` to exclude WordPress core files and uploads.

3. **Environment Configuration**: Use `.wp-env.json` for WordPress configuration. Store sensitive data in environment variables.

4. **Plugin Management**: Install plugins through version control rather than WordPress admin. Document required plugins in project configuration.

5. **Development Workflow**: Use local development environments that match production. Automate setup with CLI tools.

6. **API Design**: Create consistent, well-documented REST endpoints. Use proper HTTP methods and status codes.

7. **Security**: Implement proper authentication. Restrict WordPress admin access. Use environment-specific security measures.

8. **Performance**: Cache API responses. Optimize database queries. Use CDN for media assets.

9. **Testing**: Write unit tests with Jest and end-to-end tests with Playwright. Focus on critical user paths and API functionality.

10. **Documentation**: Maintain clear documentation for setup, development, and deployment processes. Include examples for common tasks.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup and guidelines.

## License

MIT &copy; HWP Team. See [LICENSE](LICENSE) for details.
# RFC 001: Improved Development Workflow

## Summary
This RFC proposes improvements to the development workflow for the HWP Toolkit, focusing on streamlining environment management and providing a consistent development experience across packages.

## Motivation
Currently, managing development environments across different packages can be cumbersome and error-prone. Issues include:
- Inconsistent cleanup of Docker containers and volumes
- Permission issues with wp-env directories
- Need to manually navigate to package directories
- Lack of standardized commands across packages

## Detailed Design

### 1. Centralized Scripts
All development-related scripts will be managed from the root directory:

```bash
scripts/
  ├── clean.sh     # Handles environment cleanup
  └── dev.sh       # Manages development environments
```

### 2. Development Commands
From the root directory:

```bash
pnpm dev [package]     # Start dev environment for a package (defaults to cli)
pnpm stop             # Stop all running environments
pnpm clean            # Clean up all environments
```

### 3. Environment Cleanup
The cleanup process handles:
- Stopping wp-env containers
- Removing wp-env containers
- Cleaning up WordPress volumes
- Removing test volumes
- Pruning unused networks

### 4. Package Development
Each package can define its own development workflow in its package.json, but must include:
```json
{
  "scripts": {
    "dev": "...",    // Start development environment
    "stop": "..."    // Stop development environment
  }
}
```

## Implementation

1. **Clean Script** (`scripts/clean.sh`):
```bash
#!/bin/bash
# Stop and remove containers
docker ps -a -q -f name=wp-env | xargs -r docker rm -f
# Remove volumes
docker volume ls -q -f name=wordpress | xargs -r docker volume rm -f
docker volume ls -q -f name=tests-wordpress | xargs -r docker volume rm -f
# Clean networks
docker network prune -f
```

2. **Dev Script** (`scripts/dev.sh`):
```bash
#!/bin/bash
PACKAGE=${1:-cli}
bash ./scripts/clean.sh
pnpm --filter "@hwp/$PACKAGE" dev
```

3. **Root Package.json**:
```json
{
  "scripts": {
    "clean": "bash ./scripts/clean.sh",
    "dev": "bash ./scripts/dev.sh",
    "stop": "pnpm --filter '*' stop"
  }
}
```

## Benefits
1. **Simplified Commands**: Developers can run everything from the root directory
2. **Consistent Experience**: Same commands work across all packages
3. **Clean State**: Environments are always cleaned before starting
4. **No Permission Issues**: Using Docker commands instead of file system operations
5. **Flexibility**: Easy to add support for new packages

## Backwards Compatibility
This change is backwards compatible as it:
- Doesn't remove existing package-specific scripts
- Maintains the same wp-env configuration
- Preserves existing Docker container naming

## Drawbacks
1. Slightly longer startup time due to cleanup before each start
2. Requires Docker to be running
3. May need adjustments for packages with different development requirements

## Alternatives Considered
1. **Package-specific Scripts**: Rejected due to maintenance overhead
2. **Node-based Scripts**: Rejected in favor of shell scripts for better Docker integration
3. **Manual Cleanup**: Rejected due to inconsistency and error-prone nature

## Open Questions
1. Should we add support for running multiple environments simultaneously?
2. Do we need to add environment-specific configuration options?
3. Should we add development environment health checks?

## Future Work
1. Add support for custom development configurations
2. Implement environment status monitoring
3. Add development environment validation tests
4. Create development environment templates for new packages

## Security Considerations
1. Docker commands are run without sudo
2. No sensitive data is stored in environment files
3. Development environments are isolated using Docker networking

## References
- [WordPress wp-env Documentation](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [PNPM Workspace Guide](https://pnpm.io/workspaces)

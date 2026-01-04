# Devcontainer Setup Guide

This project is now configured to run inside a VS Code Dev Container for a consistent development environment.

## Prerequisites

- VS Code
- [Dev Containers extension](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers)
- Docker Desktop or Docker Engine
- Docker Compose

## Quick Start

1. **Open the project in VS Code**
   ```bash
   code .
   ```

2. **Reopen in container**
   - Press `Ctrl+Shift+P` (or `Cmd+Shift+P` on Mac)
   - Select "Dev Containers: Reopen in Container"
   - VS Code will build and start the container environment

3. **First-time setup** (automatically runs `postCreateCommand`)
   - Development environment is configured automatically
   - Database initialized from `konsulta_100725.sql`

4. **Access the application**
   - **Application**: http://localhost:8080
   - **MySQL**: localhost:3306 (user: root, password: roottoor)

## Port Configuration

The application now uses **port 8080** instead of 9000 to avoid conflicts with other applications.

- **Host Port**: 8080 → **Container Port**: 80 (Apache)
- **Host Port**: 3306 → **Container Port**: 3306 (MySQL)

## Available Commands

Once inside the container:

```bash
# Switch to development environment
php setenv.php dev

# Switch to production environment
php setenv.php prod

# Access MySQL
mysql -h mysql -u root -p konsulta
```

## Useful VS Code Features

- **Integrated Terminal**: Open a new terminal (`Ctrl+Backtick`) - it runs inside the container
- **Remote Explorer**: View and manage containers from VS Code sidebar
- **Port Forwarding**: Configured to auto-forward ports 8080 and 3306
- **File Sync**: Your local files automatically sync with the container

## Extensions Installed in Container

The devcontainer automatically installs PHP development extensions:
- MySQL support
- PHP IntelliSense
- PHP Debug
- PHPUnit
- Docker support

## Troubleshooting

### Container won't start
```bash
# Clean up and rebuild
docker compose down -v
docker compose up -d --build
```

### Database connection issues
- Ensure the MySQL container is healthy: `docker compose exec mysql mysqladmin ping`
- Check credentials in `config.dev.ini`

### Port already in use
If port 8080 is already in use, modify the port mapping in `compose.yaml`:
```yaml
ports:
  - 9001:80  # Change 9001 to your preferred port
```

## Development Workflow

1. Code changes are immediately reflected in the container
2. Use VS Code's integrated terminal to run PHP/database commands
3. Debug using PHP extensions
4. Commit changes with Git (available in container)

## Stopping Development

- **Stop container**: Press `Ctrl+Shift+P` → "Dev Containers: Reopen Locally"
- **Full cleanup**: `docker compose down`

## Further Reading

- [VS Code Dev Containers Documentation](https://code.visualstudio.com/docs/devcontainers/containers)
- [Docker Compose Reference](https://docs.docker.com/compose/)

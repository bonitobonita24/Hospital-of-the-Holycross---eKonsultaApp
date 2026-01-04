# eKONSULTA Local Development Setup

## Prerequisites

- **Docker Desktop** or Docker + Docker Compose
- **WSL2** (Windows) or native Linux/Mac
- **VS Code** (for editing)

## Quick Start

### 1. Switch to Development Config

**Important:** The default `config.ini` is set to **production** for safe cPanel deployment. Switch to dev config first:

```bash
php setenv.php dev
```

### 2. Start the Application

```bash
# Start all services
docker-compose up -d

# Check if services are running
docker-compose ps
```

### 3. Access the Application

- **Application**: http://localhost:8080
- **PHPInfo**: http://localhost:8080/info.php
- **MySQL**: `localhost:3307` (from host machine)

### 3. Edit Files

Open the project folder in VS Code and edit files normally. All changes are **instantly reflected** in the running container thanks to volume mounts.

```bash
code /home/me/UbuntuDevFiles/HospitaloftheHolyCross/Hospital-of-the-Holycross---eKonsultaApp
```

## Database Access

### From Host Machine

```bash
mysql -h localhost -P 3307 -u root -p
# Password: roottoor
```

### From Application

The app uses these credentials (configured in `config.ini`):
- **Development**: `localhost:3306` with user `ekon_app_user`
- **Production**: Remote MySQL server

### Create Development User

If not already created:

```bash
docker-compose exec mysql mysql -u root -proottoor -e "
  CREATE USER IF NOT EXISTS 'ekon_app_user'@'%' IDENTIFIED BY 'xxx123';
  GRANT ALL ON konsulta.* TO 'ekon_app_user'@'%';
  FLUSH PRIVILEGES;
"
```

## Common Commands

### View Logs

```bash
# All services
docker-compose logs -f

# Just the app
docker-compose logs -f server

# Just MySQL
docker-compose logs -f mysql
```

### Access Container Shell

```bash
# App container
docker-compose exec server bash

# MySQL container
docker-compose exec mysql bash
```

### Restart Services

```bash
# Restart everything
docker-compose restart

# Restart just the app
docker-compose restart server
```

### Stop Services

```bash
# Stop but keep data
docker-compose stop

# Stop and remove containers (keeps data in volumes)
docker-compose down

# Remove everything including database data
docker-compose down -v
```

## Environment Configuration

The app uses `setenv.php` to switch between dev and production:

```bash
# Switch to development
php setenv.php dev

# Switch to production
php setenv.php prod

# Check current environment
grep "ENV_TYPE" config.ini
```

By default, `config.ini` is set to **production** for cPanel deployment safety.

## Rebuilding the Container

If you modify the `Dockerfile`:

```bash
# Rebuild and restart
docker-compose up -d --build
```

## Troubleshooting

### Port Already in Use

If port 8080 or 3307 is already taken:

```bash
# Check what's using the port
sudo lsof -i :8080
sudo lsof -i :3307

# Stop other services or change ports in compose.yaml
```

### Database Not Initialized

If the database is empty after first start:

```bash
# The SQL dump should auto-import, but if not:
docker-compose exec mysql mysql -u root -proottoor konsulta < konsulta_100725.sql
```

### Permission Issues

If you see permission errors:

```bash
# Fix ownership
docker-compose exec server chown -R www-data:www-data /var/www/html

# Fix permissions
docker-compose exec server chmod -R 755 /var/www/html
docker-compose exec server chmod -R 777 /var/www/html/files /var/www/html/tmp
```

### MySQL Connection Issues

Ensure the dev user exists and the app is using correct credentials:

```bash
# Test connection
docker-compose exec server php -r "
  \$conn = new PDO('mysql:host=mysql;dbname=konsulta', 'ekon_app_user', 'xxx123');
  echo 'Connection successful!';
"
```

## Architecture

### Services

- **server**: PHP 5.6 Apache container with all extensions
- **mysql**: MySQL 5.7 with auto-imported database

### Volumes

- `.` → `/var/www/html` - Live code syncing
- `mysql-data` - Persistent database storage

### Network

- `eKonsulta_network` - Bridge network for service communication

## Production Deployment

For production (cPanel):

1. **Switch to production config** (if you were in dev mode):
   ```bash
   php setenv.php prod
   ```

2. Upload all files to `/home/jerlanlo/powerbyteitsolutions_com_hckonsulta`
3. Ensure `config.ini` shows production settings:
   - `DBSERVER = "s1105.usc1.mysecurecloudhost.com"`
   - `ENV_TYPE = "production"`
4. Create MySQL database and user on cPanel (if not already exists)
5. The app will connect to the production database automatically

**Note:** The default `config.ini` is already set to production, so no manual config change is needed on cPanel.

The Docker setup is **only for local development**.

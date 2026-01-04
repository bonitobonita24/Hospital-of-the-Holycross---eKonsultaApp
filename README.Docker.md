### Environments

- Production (default): config.ini targets the cPanel MySQL `s1105.usc1.mysecurecloudhost.com:3306` with schema/user `jerlanlo_pbe_hckonsulta`.
- Development: config.dev.ini targets Docker compose (host `mysql`, schema `konsulta`, user `ekon_app_user`).
- Production template: config.prod.ini mirrors the live cPanel database and can restore the default.

Switch environment (CLI-only helper copies template to config.ini):

```bash
php setenv.php dev   # use Docker dev settings
php setenv.php prod  # revert to production defaults
```

### Development with Docker

**Important:** The default `config.ini` is set to **production** for safe cPanel deployment. You must switch to development config before using Docker:

1) Switch to dev config (required for local Docker)
```bash
php setenv.php dev
```

2) Start services (imports konsulta_100725.sql on a clean volume)
```bash
docker compose up -d --build
```

3) App URL
```
http://localhost:8080
```

**Before deploying to production**, switch back to production config:
```bash
php setenv.php prod
```

### Quick Commands

#### View Logs
```bash
# All services
docker-compose logs -f

# Just the app
docker-compose logs -f server

# Just MySQL
docker-compose logs -f mysql
```

#### Access Container Shell
```bash
# App container
docker-compose exec server bash

# MySQL container
docker-compose exec mysql bash
```

#### Database Access
```bash
# From host machine
mysql -h localhost -P 3307 -u root -p
# Password: roottoor

# Create dev user (if needed)
docker-compose exec mysql mysql -u root -proottoor -e "CREATE USER IF NOT EXISTS 'ekon_app_user'@'%' IDENTIFIED BY 'xxx123'; GRANT ALL ON konsulta.* TO 'ekon_app_user'@'%'; FLUSH PRIVILEGES;"
```

#### Restart Services
```bash
# Restart everything
docker-compose restart

# Restart just the app
docker-compose restart server
```

#### Stop Services
```bash
# Stop but keep data
docker-compose stop

# Stop and remove containers (keeps data in volumes)
docker-compose down

# Remove everything including database data
docker-compose down -v
```

### PHP extensions
Extensions are installed in the Dockerfile via `docker-php-ext-install pdo pdo_mysql mysqli gd mcrypt zip`. Add more there if needed.
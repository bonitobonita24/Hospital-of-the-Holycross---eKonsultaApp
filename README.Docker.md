### Environments

- **Production (default)**: `config.ini` targets the cPanel MySQL `s1105.usc1.mysecurecloudhost.com:3306` with schema/user `jerlanlo_pbe_hckonsulta`.
- **Development**: `config.dev.ini` targets Docker Compose (host `mysql`, schema `konsulta`, user `ekon_app_user`).
- **Production template**: `config.prod.ini` mirrors the live cPanel database and can restore the default.

### Environment Switching

**Always use `setenv.php` to switch between dev and production.**

```bash
# Switch to development (for local Docker)
php setenv.php dev

# Switch to production (for cPanel deployment)
php setenv.php prod

# Check current environment
php setenv.php status
```

**Important Notes:**
- The default `config.ini` is **production** for safe cPanel deployment.
- Always switch to `dev` before running Docker Compose locally.
- Always switch back to `prod` before pulling/deploying to production.
- Do NOT manually edit `config.ini`; use `setenv.php` to manage it.

### Development with Docker

1) **Switch to dev config** (required before Docker)
```bash
php setenv.php dev
```

2) **Start services** (imports `konsulta_010826.sql` on clean init)
```bash
docker compose up -d --build
```

3) **Access app**
```
http://localhost:8080
```

4) **Before deploying to production**, switch back
```bash
php setenv.php prod
```

### Production Deployment

After pulling updated code from main:

1) **Ensure production config is active**
```bash
php setenv.php prod
# or just verify config.ini has production settings
```

2) **Deploy files to cPanel**
   - Upload to `/public_html` or your doc root
   - Database is auto-connected via production config

3) **Database updates** (if needed, via cPanel phpMyAdmin or SSH)
   - Import any new `.sql` dumps
   - Ensure table names match code (case-sensitive on Linux—use uppercase: `TSEKAP_TBL_*`)

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
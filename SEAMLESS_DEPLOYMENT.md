# Seamless Deployment Workflow

## Database Credential Alignment

All database credentials are now unified across development and production environments for seamless deployment.

### Unified Credentials

| Property | Value |
|----------|-------|
| **Database Name** | `jerlanlo_pbe_hckonsulta` |
| **Database User** | `jerlanlo_pbe_hckonsulta` |
| **Database Password** | `!kx^|MU6ASjP#HdN8` |
| **Root Password** | `roottoor` |

### Configuration Files

#### Development Mode (Docker)
```ini
# config.dev.ini
[DBCONNECT]
DBSERVER = "mysql"           # Docker hostname
DBPORT = "3306"
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"
APPPASSWORD = "!kx^|MU6ASjP#HdN8"
[SCHEMA]
EPCB = "jerlanlo_pbe_hckonsulta"
[ENVIRONMENT]
ENV_TYPE = "development"
```

#### Production Mode (cPanel)
```ini
# config.prod.ini
[DBCONNECT]
DBSERVER = "s1105.usc1.mysecurecloudhost.com"  # cPanel hostname
DBPORT = "3306"
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"
APPPASSWORD = "!kx^|MU6ASjP#HdN8"
[SCHEMA]
EPCB = "jerlanlo_pbe_hckonsulta"
[ENVIRONMENT]
ENV_TYPE = "production"
```

## Seamless Deployment Steps

### 1. Development Work (Local Docker)

```bash
# Switch to dev mode
php setenv.php dev

# Start Docker
docker compose up -d

# Work on features, test locally
# - App: http://localhost:8080
# - phpMyAdmin: http://localhost:8081 (root / roottoor)
# - Database: localhost:3307
```

### 2. Push to GitHub

```bash
# Git workflow - these files are ready to push!
git add .
git commit -m "Feature: [description]"
git push origin main

# Note: config.ini is in .gitignore, so it won't be committed
```

### 3. Deploy to Production

```bash
# On production server (cPanel)
cd /path/to/eKonsultaApp

# Pull latest code from GitHub
git pull origin main

# Switch to production mode (IMPORTANT!)
php setenv.php prod

# Import UPPERCASE SQL file if first deployment
# Or run migrations if any

# App will now connect to cPanel MySQL with same credentials
```

## Why This Works

✅ **Same Database Credentials** - `jerlanlo_pbe_hckonsulta` user exists on both Docker and cPanel
✅ **Same Database Name** - Both use `jerlanlo_pbe_hckonsulta`
✅ **Same Password** - `!kx^|MU6ASjP#HdN8` works everywhere
✅ **Only DBSERVER changes** - Docker uses `mysql`, production uses `s1105.usc1.mysecurecloudhost.com`
✅ **Config templates** - `setenv.php` CLI tool switches between dev/prod modes

## Key Files

- **config.dev.ini** - Development template (Docker localhost)
- **config.prod.ini** - Production template (cPanel remote)
- **config.ini** - Active config (managed by setenv.php, in .gitignore)
- **setenv.php** - Switch between dev/prod modes
- **compose.yaml** - Docker setup with unified credentials

## Environment Switching

```bash
# Switch to development (Docker)
php setenv.php dev
# Result: config.ini copied from config.dev.ini
# DBSERVER = "mysql" (Docker container)

# Switch to production (cPanel)
php setenv.php prod
# Result: config.ini copied from config.prod.ini
# DBSERVER = "s1105.usc1.mysecurecloudhost.com" (remote server)

# Check current status
php setenv.php status
```

## Database Details

### Docker (Development)
- **Host:** mysql (Docker internal)
- **Port:** 3306 (internal) / 3307 (external)
- **Database:** jerlanlo_pbe_hckonsulta
- **User:** jerlanlo_pbe_hckonsulta
- **Password:** !kx^|MU6ASjP#HdN8
- **Access phpMyAdmin:** http://localhost:8081

### cPanel (Production)
- **Host:** s1105.usc1.mysecurecloudhost.com
- **Port:** 3306
- **Database:** jerlanlo_pbe_hckonsulta
- **User:** jerlanlo_pbe_hckonsulta
- **Password:** !kx^|MU6ASjP#HdN8
- **Access phpMyAdmin:** Via cPanel

## Deployment Checklist

- [ ] All features tested locally
- [ ] Code committed and pushed to GitHub
- [ ] Production server pulled latest code
- [ ] Run `php setenv.php prod` on production
- [ ] Database imported with UPPERCASE SQL (if first time)
- [ ] Test app login on production
- [ ] Verify all features work
- [ ] Monitor production logs

## Troubleshooting

If connection fails on production:
1. Verify production `config.ini` has correct DBSERVER
2. Confirm credentials with cPanel hosting
3. Check MySQL permissions for user
4. Verify database exists and user has access

If connection fails in Docker:
1. Verify `config.ini` has DBSERVER = "mysql"
2. Check Docker containers are running: `docker compose ps`
3. Check MySQL user created: `docker exec eKonsulta_mysql mysql -uroot -proottoor -e "SELECT user FROM mysql.user;"`

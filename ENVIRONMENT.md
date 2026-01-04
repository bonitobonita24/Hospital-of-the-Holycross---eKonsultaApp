# Environment Configuration Guide

## Overview

eKonsulta has two distinct environments for different deployment scenarios:

### Development (Local)
- **Target**: Local development in VS Code with devcontainer
- **LAMP Stack**: Fully containerized (Apache + PHP 5.6 + MySQL 5.7)
- **Config File**: `config.dev.ini`
- **Auto-Enabled**: When opening in devcontainer
- **Database**: Docker MySQL container on port 3307

### Production (cPanel)
- **Target**: Shared hosting (cPanel with Apache + MySQL already installed)
- **LAMP Stack**: Host's Apache + MySQL (no containers)
- **Config File**: `config.prod.ini` → `config.ini` (default)
- **Auto-Enabled**: By default, no setup needed
- **Database**: cPanel remote MySQL on s1105.usc1.mysecurecloudhost.com:3306

---

## Quick Reference

| Aspect | Development | Production |
|--------|-------------|-----------|
| **Entry Point** | Devcontainer in VS Code | cPanel file manager |
| **PHP Runtime** | Docker container | cPanel Apache |
| **MySQL Server** | Docker container | cPanel remote server |
| **MySQL Host** | `mysql` (in container) / `localhost` (from host) | `s1105.usc1.mysecurecloudhost.com` |
| **MySQL Port** | 3306 (container) / 3307 (host) | 3306 |
| **Config File** | `config.dev.ini` | `config.prod.ini` |
| **Database Name** | `konsulta` | `jerlanlo_pbe_hckonsulta` |
| **DB User** | `ekon_app_user` | `jerlanlo_pbe_hckonsulta` |
| **App URL** | http://localhost:8080 | https://yourdomain.com |
| **Setup Required** | Automated | None (production is default) |

---

## Configuration Files

### `config.ini` (Active Configuration)
This is the **active** configuration file used by the application. It gets copied from either:
- `config.dev.ini` when in development
- `config.prod.ini` when in production (default)

**⚠️ Never edit `config.ini` directly!** Edit the template files instead.

### `config.dev.ini` (Development Template)
```ini
[SYSUSERS]
APPUSERNAME = "ekon_app_user"
APPPASSWORD = "xxx123"

[SCHEMA]
EPCB = "konsulta"

[DBCONNECT]
DBSERVER = "mysql"          # Docker container name
DBPORT = "3306"

[ENVIRONMENT]
ENV_TYPE = "development"
```

**Used in**: Local Docker containers
**When**: Running devcontainer or manual `php setenv.php dev`

### `config.prod.ini` (Production Template)
```ini
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"
APPPASSWORD = "!kx^|MU6ASjP#HdN8"

[SCHEMA]
EPCB = "jerlanlo_pbe_hckonsulta"

[DBCONNECT]
DBSERVER = "s1105.usc1.mysecurecloudhost.com"
DBPORT = "3306"

[ENVIRONMENT]
ENV_TYPE = "production"
```

**Used in**: cPanel shared hosting
**When**: Default (no setup needed) or manual `php setenv.php prod`

---

## How It Works

### Development Workflow

```
1. Open project in VS Code
2. Reopen in Devcontainer
        ↓
3. postCreateCommand runs:
   php setenv.php dev
        ↓
4. config.dev.ini → config.ini
        ↓
5. App reads config.ini and connects to Docker MySQL
        ↓
6. Docker containers start with mysql:3307 on host
```

### Production Workflow

```
1. Push code to GitHub/Deploy to cPanel
        ↓
2. config.prod.ini → config.ini (already in repo as default)
        ↓
3. App reads config.ini and connects to cPanel MySQL
        ↓
4. cPanel Apache serves the pages normally
```

---

## Switching Environments Locally

### Check Current Environment
```bash
php setenv.php status

# Output examples:
# ✓ Current Environment: development
#   Database Server: mysql
# 
# ✓ Current Environment: production
#   Database Server: s1105.usc1.mysecurecloudhost.com
```

### Switch to Development
```bash
php setenv.php dev
# or
php setenv.php development
```

### Switch to Production
```bash
php setenv.php prod
# or
php setenv.php production
```

---

## Using in Code

The application code doesn't need to change. It simply reads from `config.ini`:

```php
// function.php (simplified)
$ini = parse_ini_file("config.ini");

$conn = new PDO(
    "mysql:host=" . $ini["DBSERVER"] . ";dbname=" . $ini["EPCB"],
    $ini['APPUSERNAME'],
    $ini['APPPASSWORD']
);
```

The configuration is transparent to the code!

---

## Deployment Checklist

### Before Pushing to Production
- [ ] Test locally in devcontainer
- [ ] Run `php setenv.php prod` to verify production config
- [ ] Test with production database (if accessible locally)
- [ ] Verify `config.ini` contains production credentials
- [ ] Check that `config.prod.ini` has correct cPanel credentials

### Deploying to cPanel
1. Push code to GitHub (includes `config.prod.ini`)
2. SSH/Git pull on cPanel server
3. **Done!** No environment setup needed
4. `config.ini` defaults to production
5. App immediately connects to cPanel MySQL

### If You Accidentally Left in Development
```bash
# From cPanel SSH/Terminal
php setenv.php prod

# Verify
php setenv.php status
```

---

## Troubleshooting

### "Can't connect to MySQL server"
1. Check which environment is active: `php setenv.php status`
2. If in development:
   - Ensure Docker containers are running: `docker compose ps`
   - Verify MySQL is healthy: `docker compose exec mysql mysqladmin ping`
3. If in production:
   - Check cPanel MySQL is accessible
   - Verify credentials in `config.prod.ini`
   - Test connection: `mysql -h s1105.usc1.mysecurecloudhost.com -u jerlanlo_pbe_hckonsulta -p jerlanlo_pbe_hckonsulta`

### Wrong Environment Active
```bash
# Check current
php setenv.php status

# Reset to production (safe default)
php setenv.php prod

# Or to development (for local work)
php setenv.php dev
```

### Config File Not Syncing
1. Ensure `setenv.php` has execute permissions
2. Verify `config.dev.ini` and `config.prod.ini` exist
3. Check file permissions on `config.ini`

---

## Security Notes

1. **Production Credentials** are in `config.prod.ini` (not committed to unsafe locations)
2. **Development Credentials** are generic/demo only
3. **On cPanel**: Credentials are in `config.prod.ini` which should be protected
4. **Never** hardcode credentials in PHP files
5. **Always** use environment-based configuration through `config.ini`

---

## Git Ignore

Add to `.gitignore` to protect credentials:
```
config.ini          # Active config (auto-generated, shouldn't commit)
.env                # Local environment file
.env.local          # Local overrides
```

Note: `config.prod.ini` **should be committed** as it's the production template!

---

## Advanced: Custom Environments

To add another environment (e.g., staging):

1. Create `config.staging.ini`:
```ini
[SYSUSERS]
APPUSERNAME = "staging_user"
APPPASSWORD = "staging_pass"

[SCHEMA]
EPCB = "staging_db"

[DBCONNECT]
DBSERVER = "staging.example.com"
DBPORT = "3306"

[ENVIRONMENT]
ENV_TYPE = "staging"
```

2. Use it:
```bash
php setenv.php staging
```

3. The `setenv.php` script automatically supports any `config.*.ini` file!

---

## Summary

- **config.ini** = Active config (auto-switched)
- **config.dev.ini** = Development template (Docker)
- **config.prod.ini** = Production template (cPanel)
- **setenv.php** = Environment switcher
- **devcontainer.json** = Auto-configures dev on startup
- **No code changes** = Environments are transparent to app logic

# eKonsulta - Development vs Production Guide

## Environment Setup Complete! ✓

Your project now has separate **Development** and **Production** environments that require NO code changes between them.

### 🚀 For Local Development

1. **Start the dev environment:**
   ```bash
   ./dev-startup.sh
   ```
   Or open in VS Code devcontainer (automatic setup)

2. **Access the app:**
   - URL: http://localhost:8080
   - MySQL: localhost:3307

3. **Check environment:**
   ```bash
   php setenv.php status
   ```

### 🌐 For Production (cPanel)

1. **Just deploy the code:**
   - Push to GitHub
   - Pull on cPanel server
   - Done! No setup commands needed

2. **Verify on cPanel:**
   ```bash
   php setenv.php status
   # Should show: production with s1105.usc1.mysecurecloudhost.com
   ```

---

## Configuration Files

| File | Purpose | Environment | Edit? |
|------|---------|-------------|-------|
| `config.ini` | Active (auto-switched) | Both | ❌ Never |
| `config.dev.ini` | Development template | Dev only | ✅ If credentials change |
| `config.prod.ini` | Production template | Prod only | ✅ If credentials change |
| `setenv.php` | Switcher script | Both | ❌ Rarely |

---

## Key Differences

### Development (Docker - Local)
```
Docker Container                Host Machine
┌─────────────────┐
│  PHP 5.6        │
│  Apache         │────────→ http://localhost:8080
│  MySQL 5.7      │
│  konsulta DB    │────────→ localhost:3307
└─────────────────┘
```
- Config: `config.dev.ini`
- Database: Docker MySQL (konsulta)
- Startup: `./dev-startup.sh` or devcontainer

### Production (cPanel - Shared Hosting)
```
cPanel Shared Hosting
┌──────────────────────────────────┐
│ Apache + PHP (pre-installed)      │
│ MySQL remote (s1105.usc...)      │
│ jerlanlo_pbe_hckonsulta DB       │
└──────────────────────────────────┘
```
- Config: `config.prod.ini` (default)
- Database: Remote MySQL (jerlanlo_pbe_hckonsulta)
- Startup: None! Just deploy.

---

## Environment Switching

### Check Current
```bash
php setenv.php status
```

### Switch to Development
```bash
php setenv.php dev
```

### Switch to Production
```bash
php setenv.php prod
```

---

## What Gets Deployed?

**All files** including:
- ✅ `config.prod.ini` (template for production)
- ✅ `config.dev.ini` (template for development)
- ✅ `setenv.php` (environment switcher)
- ✅ All app code
- ⚠️ `config.ini` will be in whatever state you left it in

**Best practice before deploying:**
```bash
php setenv.php prod    # Ensure production config is active
git status             # Verify config.ini is production
git commit -am "Pre-deploy: set to production"
git push
```

---

## Database Credentials

### Development (Docker)
- **Host:** localhost
- **Port:** 3307
- **User:** root
- **Password:** roottoor
- **Database:** konsulta
- **Inside Container:** use `mysql:3306` instead

### Production (cPanel)
- **Host:** s1105.usc1.mysecurecloudhost.com
- **Port:** 3306
- **User:** jerlanlo_pbe_hckonsulta
- **Password:** !kx^|MU6ASjP#HdN8
- **Database:** jerlanlo_pbe_hckonsulta

---

## Troubleshooting

### "Can't connect to database"
```bash
# Check current environment
php setenv.php status

# Are you in the right environment for what you're doing?
# Dev environment but Docker not running?
docker compose ps

# Production environment but on cPanel?
mysql -h s1105.usc1.mysecurecloudhost.com -u jerlanlo_pbe_hckonsulta -p
```

### Docker containers not starting
```bash
cd /path/to/project
docker compose down -v
docker compose up -d --build
```

### Wrong config active before deployment
```bash
# Reset to production (safe default for cPanel)
php setenv.php prod
git add config.ini
git commit -m "Reset to production config"
git push
```

---

## Migration: From Old Setup

If you were using the old manual config system:

**Old way:**
```bash
# Had to manually edit config.ini for each environment
```

**New way:**
```bash
# Automatic switching via templates
php setenv.php dev     # For local work
php setenv.php prod    # For cPanel deployment
```

---

## Architecture Summary

```
Application Code (unchanged)
    ↓
function.php reads: config.ini
    ↓
config.ini is auto-switched between:
    ├─ config.dev.ini (Development)
    │   └─ → Docker MySQL
    └─ config.prod.ini (Production)
        └─ → cPanel MySQL
```

**No code modifications needed!** The app just reads the config file that's currently active.

---

## For Team Development

When multiple developers work on this project:

1. **Everyone gets the same configs:**
   - `config.dev.ini` (shared Docker setup)
   - `config.prod.ini` (shared cPanel credentials)

2. **No conflicts:**
   - Never commit `config.ini` variations
   - Each dev switches with `php setenv.php dev`

3. **Before pushing:**
   ```bash
   php setenv.php prod    # Set to production template
   git push               # Production is default
   ```

---

## Documentation

See [ENVIRONMENT.md](./ENVIRONMENT.md) for comprehensive environment documentation.

---

## Quick Command Reference

```bash
# Check environment
php setenv.php status

# Switch to development (Docker)
php setenv.php dev

# Switch to production (cPanel)
php setenv.php prod

# Start development (with Docker)
./dev-startup.sh

# View Docker logs
docker compose logs -f server

# Stop Docker
docker compose down
```

---

✅ **Setup Complete!** Your project is ready for both local development and cPanel production deployment.

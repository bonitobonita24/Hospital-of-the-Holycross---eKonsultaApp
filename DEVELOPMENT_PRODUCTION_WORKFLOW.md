# Development & Production Workflow

## Default Configuration Strategy

**config.ini defaults to PRODUCTION** - This is the safest approach for shared hosting.

### Why?
- Shared hosting typically has no SSH/CLI access
- Can't run `php setenv.php prod` on production
- Production just needs to pull code and work immediately
- Development must explicitly switch to Docker

---

## 🚀 Quick Start Workflows

### Development Workflow (Local Docker)

```bash
# 1. First time setup
cd /path/to/eKonsultaApp
php setenv.php dev

# 2. Start Docker
docker compose up -d

# 3. Access locally
# - App: http://localhost:8080
# - phpMyAdmin: http://localhost:8081 (root / roottoor)

# 4. Work, test, commit
git add .
git commit -m "Feature: ..."
git push origin main

# 5. Continue development (config stays in dev mode)
# Just keep working!
```

### Production Workflow (Shared Hosting - cPanel)

```bash
# 1. SSH into cPanel (or file manager)
cd /public_html/eKonsultaApp

# 2. Pull latest code from GitHub
git pull origin main

# 3. Done! ✨
# App automatically connects to cPanel MySQL
# (config.ini is already set to production)

# Note: Do NOT run setenv.php - it's not needed!
```

---

## 📋 Configuration Files

### config.ini (Active - DEFAULT PRODUCTION)
```ini
[DBCONNECT]
DBSERVER = "s1105.usc1.mysecurecloudhost.com"  ← cPanel server
DBPORT = "3306"
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"
APPPASSWORD = "!kx^|MU6ASjP#HdN8"
[SCHEMA]
EPCB = "jerlanlo_pbe_hckonsulta"
[ENVIRONMENT]
ENV_TYPE = "production"
```
**Status:** ✅ Ready for production - shared hosting can use as-is

### config.dev.ini (Template for Local Development)
```ini
[DBCONNECT]
DBSERVER = "mysql"                  ← Docker container
DBPORT = "3306"
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"
APPPASSWORD = "!kx^|MU6ASjP#HdN8"
[SCHEMA]
EPCB = "jerlanlo_pbe_hckonsulta"
[ENVIRONMENT]
ENV_TYPE = "development"
```
**Status:** ✅ Used only when developing locally

### config.prod.ini (Reference - NOT USED)
```ini
[DBCONNECT]
DBSERVER = "s1105.usc1.mysecurecloudhost.com"
DBPORT = "3306"
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"
APPPASSWORD = "!kx^|MU6ASjP#HdN8"
[SCHEMA]
EPCB = "jerlanlo_pbe_hckonsulta"
[ENVIRONMENT]
ENV_TYPE = "production"
```
**Status:** 📖 Reference only - config.ini is already production

---

## 🔄 setenv.php Script

### Usage

```bash
# Switch TO Docker (development)
php setenv.php dev
# Copies config.dev.ini → config.ini
# DBSERVER = "mysql" (Docker)

# Switch TO Production (cPanel)
php setenv.php prod
# Copies config.prod.ini → config.ini
# DBSERVER = "s1105.usc1.mysecurecloudhost.com"

# Check current status
php setenv.php status
# Shows which mode is active
```

### Important Notes
- ✅ Use `php setenv.php dev` ONLY in Docker for local development
- ❌ Never use this script on production (shared hosting doesn't have CLI PHP)
- ⚠️ If accidentally run on production, just git pull again to restore

---

## 📊 Comparison: Old vs New

### OLD WORKFLOW ❌
```
Production: Must manually run php setenv.php prod
Problem: Shared hosting has no CLI access!
```

### NEW WORKFLOW ✅
```
Development: php setenv.php dev (one time, then work)
Production: git pull origin main (done! no extra steps)
```

---

## 🐳 Docker Local Development

### Setup
```bash
# First time
php setenv.php dev
docker compose down -v
docker compose up -d

# Check status
docker compose ps
docker logs eKonsulta_app
```

### Access Points
| Service | URL | Credentials |
|---------|-----|-------------|
| App | http://localhost:8080 | (app login) |
| phpMyAdmin | http://localhost:8081 | root / roottoor |
| MySQL | localhost:3307 | root / roottoor |

### Database Info
- Database: `jerlanlo_pbe_hckonsulta`
- User: `jerlanlo_pbe_hckonsulta`
- Password: `!kx^|MU6ASjP#HdN8`

---

## 🔐 Production Server (cPanel)

### Database Connection
- **Server:** s1105.usc1.mysecurecloudhost.com
- **Database:** jerlanlo_pbe_hckonsulta
- **User:** jerlanlo_pbe_hckonsulta
- **Password:** !kx^|MU6ASjP#HdN8

### Access phpMyAdmin
- Via cPanel control panel
- Or phpMyAdmin link in cPanel

### Deployment Steps
```bash
# SSH into server
ssh user@server.com

# Go to app directory
cd public_html/eKonsultaApp

# Pull latest
git pull origin main

# Verify connection
# Access http://your-site.com/index.php
# Should show login page (no database errors)

# If first deployment, import UPPERCASE SQL:
# - Go to cPanel phpMyAdmin
# - Import jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql
```

---

## ✅ Deployment Checklist

### Before Pushing to GitHub
- [ ] Tested locally (Docker)
- [ ] All features working
- [ ] No hardcoded server names in code
- [ ] Credentials only in config files

### After git pull on Production
- [ ] No changes needed - config.ini is already production
- [ ] Access app login page
- [ ] Test patient lookup
- [ ] Test data entry
- [ ] Check logs if any issues

---

## 🚨 Troubleshooting

### App shows "Connection refused" on Production
**Cause:** config.ini still has Docker hostname
**Fix:** Verify config.ini has `DBSERVER = "s1105.usc1.mysecurecloudhost.com"`

### App works locally but not on production
**Cause:** Usually database credentials mismatch
**Check:**
1. cPanel MySQL user exists: `jerlanlo_pbe_hckonsulta`
2. Password is correct: `!kx^|MU6ASjP#HdN8`
3. Database exists: `jerlanlo_pbe_hckonsulta`
4. User has all permissions

### Need to revert to default production settings
```bash
git pull origin main
# config.ini will be restored to production defaults
```

---

## 📁 Git Configuration

### .gitignore
`config.ini` should be in .gitignore so it's never committed:

```
# .gitignore
config.ini
admin/
```

This ensures:
- Local config changes never push to GitHub
- Production always uses fresh config.ini from repo
- Each environment has correct credentials

---

## 🎯 Summary

| Step | Dev | Prod |
|------|-----|------|
| **Setup** | `php setenv.php dev` | Just `git pull` |
| **Work** | `docker compose up -d` | App runs automatically |
| **Config** | Docker: localhost | cPanel: remote server |
| **Credentials** | Same for all | Same for all |
| **Result** | Seamless testing | Seamless deployment |

**No more manual config switches on production!** ✨

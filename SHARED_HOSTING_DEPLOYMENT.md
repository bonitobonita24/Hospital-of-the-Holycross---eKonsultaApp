# Shared Hosting Deployment Strategy

## The Problem With Shared Hosting

Shared hosting (like cPanel) typically:
- ❌ No SSH/CLI access or limited to basic commands
- ❌ Can't run `php setenv.php prod` scripts
- ❌ Must be "push and play" - code works immediately
- ✅ But **does** have git support (many providers)

## The Solution

**Default config.ini to PRODUCTION** and only switch to Dev locally.

---

## 🔄 Complete Workflow

```
┌─────────────────────────────────────────────────────────────┐
│                   DEVELOPMENT (Local)                       │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  1. Clone repo                                               │
│     git clone https://github.com/bonitobonita24/...          │
│                                                               │
│  2. Switch to Docker (ONE TIME)                              │
│     php setenv.php dev                                       │
│     → config.ini: DBSERVER = "mysql"                         │
│                                                               │
│  3. Start Docker                                             │
│     docker compose up -d                                     │
│                                                               │
│  4. Access locally                                           │
│     App: http://localhost:8080 ✓                             │
│     phpMyAdmin: http://localhost:8081 ✓                      │
│                                                               │
│  5. Work on features                                         │
│     (config.ini stays in dev mode - don't change it)         │
│                                                               │
│  6. Test thoroughly                                          │
│     Patient lookups, forms, reports, etc.                    │
│                                                               │
│  7. Commit and push                                          │
│     git add .                                                │
│     git commit -m "Feature: [description]"                   │
│     git push origin main                                     │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                           ↓
                    GITHUB REPO
                           ↓
┌─────────────────────────────────────────────────────────────┐
│               PRODUCTION (cPanel Shared Hosting)             │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  1. SSH into server (or use file manager)                    │
│     ssh user@domain.com                                      │
│                                                               │
│  2. Navigate to app directory                                │
│     cd public_html/eKonsultaApp                              │
│                                                               │
│  3. Pull latest code                                         │
│     git pull origin main                                     │
│                                                               │
│  4. Done! ✨                                                 │
│     App automatically works!                                 │
│     → config.ini: DBSERVER = "s1105.usc1.mysecurecloudhost   │
│     → Connects to cPanel MySQL                               │
│                                                               │
│  5. Verify                                                   │
│     Visit http://your-domain.com                             │
│     Should show login page                                   │
│                                                               │
│  NO NEED TO:                                                 │
│     ❌ Run php setenv.php                                    │
│     ❌ Manually edit config.ini                              │
│     ❌ Do any special setup                                  │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## 📊 Configuration Matrix

```
                    │   Development   │   Production
────────────────────┼─────────────────┼──────────────────
DBSERVER            │   mysql         │   cPanel hostname
DBPORT              │   3306          │   3306
Database            │   jerlanlo_...  │   jerlanlo_...
User                │   jerlanlo_...  │   jerlanlo_...
Password            │   !kx^|MU6A...  │   !kx^|MU6A...
setenv.php needed?  │   YES (once)    │   NO
CLI access needed?  │   YES (local)   │   NO
How to switch?      │   php setenv.py │   Not needed!
```

---

## 🎯 Key Insights

### Why Default to Production?

1. **Shared hosting can't run CLI scripts** - Many providers disable PHP CLI for security
2. **Safe by default** - If someone forgets to switch, it goes to production (expected)
3. **Zero setup on production** - Just git pull and it works
4. **Development is explicit** - Only devs run setenv.php locally

### Why Not Default to Development?

1. **Dangerous** - Someone forgets setenv.php, production connects to local Docker 😱
2. **Extra step** - Every production deploy needs manual switch
3. **Shared hosting incompatible** - No CLI to run setenv.php anyway

---

## ✅ Checklist Before Push to GitHub

- [ ] Tested locally with Docker
- [ ] All features working
- [ ] Logged in and navigated through app
- [ ] Data entry forms working
- [ ] Reports/exports working
- [ ] No hardcoded IPs/hostnames in PHP code
- [ ] Only config.* files have server names
- [ ] config.ini is in .gitignore

---

## 📋 First-Time Production Deployment

### On Production Server (cPanel)

```bash
# 1. SSH to server
ssh user@domain.com

# 2. Go to web directory
cd public_html
rm -rf eKonsultaApp  # If exists
git clone https://github.com/bonitobonita24/Hospital-of-the-Holycross---eKonsultaApp.git eKonsultaApp
cd eKonsultaApp

# 3. Create database (if not exists)
# Use cPanel MySQL or phpMyAdmin

# 4. Import data
# Via cPanel phpMyAdmin, import:
# database_queries/main_db/jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql

# 5. Test
# Visit http://your-domain.com
# Should show login page

# That's it! ✨
```

---

## 🔐 Security Notes

### config.ini Protection

Since config.ini stays in production, ensure:

```ini
[SYSUSERS]
APPUSERNAME = "jerlanlo_pbe_hckonsulta"  ← Not ideal to expose
APPPASSWORD = "!kx^|MU6ASjP#HdN8"       ← Sensitive!
```

**Better approach (optional future improvement):**
- Use environment variables instead
- Use .env files with PHP dotenv library
- Store credentials in cPanel environment

For now, config.ini works but keep it safe:
- Ensure config.ini is not publicly accessible (usually .ini files aren't served by Apache)
- Keep database passwords strong
- Limit database user permissions

---

## 🚀 Future Considerations

### If You Move Away from Shared Hosting

If you later get a VPS/Dedicated server:

```bash
# You CAN run setenv.php there
php setenv.php prod

# You COULD revert to old workflow if desired
# But the current system still works great
```

The current approach is **always compatible** - works with both:
- ✅ Shared hosting (no setenv.php needed)
- ✅ VPS/Dedicated (setenv.php still available if needed)

---

## Summary

**Development:** Only local change needed
```bash
php setenv.php dev  # ONE TIME
```

**Production:** Zero changes needed
```bash
git pull origin main  # That's it!
```

**Result:** Seamless, secure, shared-hosting compatible deployment! 🎉

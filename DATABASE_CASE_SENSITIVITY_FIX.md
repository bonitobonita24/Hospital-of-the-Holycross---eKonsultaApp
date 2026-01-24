# Solution: SQL Table Name Case Sensitivity Fix

## Problem
Linux MySQL is case-sensitive for table names by default, but the production database has lowercase table names while the PHP code queries them using UPPERCASE names (e.g., `TSEKAP_TBL_HCI_PROFILE`). This causes "table doesn't exist" errors on Linux servers.

## Solution
A new database SQL file has been created with all table names converted to UPPERCASE:

**File:** `database_queries/main_db/jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql` (201MB)

### What Changed
All table names and references have been converted to uppercase:
- `CREATE TABLE` statements
- `INSERT INTO` statements  
- `SELECT FROM` clauses
- `JOIN` operations
- `ALTER TABLE` statements
- `UPDATE` statements

**Examples:**
```sql
-- BEFORE (lowercase)
CREATE TABLE `lib_barangay` (...);
INSERT INTO `tsekap_lib_abdomen` (...);
SELECT * FROM `tsekap_tbl_hci_profile` WHERE ...;

-- AFTER (uppercase)
CREATE TABLE `LIB_BARANGAY` (...);
INSERT INTO `TSEKAP_LIB_ABDOMEN` (...);
SELECT * FROM `TSEKAP_TBL_HCI_PROFILE` WHERE ...;
```

## Deployment Steps

### Option 1: Import to Production (Recommended)
1. **Backup existing database** (critical!)
   ```bash
   mysqldump -h s1105.usc1.mysecurecloudhost.com -u jerlanlo_pbe_hckonsulta -p jerlanlo_pbe_hckonsulta > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Drop existing tables** in phpMyAdmin:
   ```sql
   -- Execute in phpMyAdmin SQL tab
   DROP DATABASE jerlanlo_pbe_hckonsulta;
   CREATE DATABASE jerlanlo_pbe_hckonsulta;
   ```

3. **Import the new UPPERCASE SQL file** in phpMyAdmin:
   - Go to phpMyAdmin → Import
   - Select `jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql`
   - Execute

4. **Verify** by accessing the app at:
   ```
   http://hckonsulta.powerbyteitsolutions.com/index.php
   ```
   - Login should work without "table doesn't exist" errors
   - Test patient lookups and data entry

### Option 2: Test Locally First (Recommended First Step)
1. **Update Docker Compose:**
   ```yaml
   volumes:
     - mysql-data:/var/lib/mysql
     - ./database_queries/main_db/jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql:/docker-entrypoint-initdb.d/init.sql:ro
   ```

2. **Start fresh Docker environment:**
   ```bash
   php setenv.php dev
   docker compose down -v
   docker compose up -d
   ```

3. **Verify locally:**
   ```bash
   # Check app at http://localhost:8080
   # Login should work
   # Test patient searches and form submissions
   ```

4. **After verification, deploy to production** using Option 1

## Files Created
- `database_queries/main_db/jerlanlo_pbe_hckonsulta_UPPERCASE_v2.sql` - Main corrected SQL file (201MB)
- `convert_sql_to_uppercase.py` - Conversion script (for reference/future use)

## Why This Works
- **Linux MySQL**: Treats table names as case-sensitive by default
- **Windows/macOS MySQL**: Treat table names as case-insensitive (by design)
- **PHP Code**: All queries use UPPERCASE table names (e.g., `TSEKAP_TBL_HCI_PROFILE`)
- **Solution**: Database tables must match code's uppercase naming to work on Linux

## Verification Checklist
After import:
- [ ] No "table doesn't exist" errors
- [ ] App login page loads
- [ ] Can search for patients
- [ ] Can create/edit records
- [ ] XML generation works
- [ ] PDF printing works

## Rollback Plan
If issues occur:
1. Stop the application
2. Restore from backup:
   ```bash
   mysql -h host -u user -p database < backup_file.sql
   ```
3. Contact support

## Notes
- The file size (201MB) is the same as the original
- Conversion was done programmatically with Python regex
- All SQL syntax and data integrity is preserved
- Only table names and references were changed

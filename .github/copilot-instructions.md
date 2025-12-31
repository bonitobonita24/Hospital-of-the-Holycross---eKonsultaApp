# eKONSULTA System - AI Coding Agent Instructions

## System Overview
This is a PHP-based healthcare management system for Hospital of the Holy Cross that manages patient consultations, health screenings, laboratory results, and prescription dispensing. The system generates PhilHealth-compliant XML reports and provides electronic consultation slips (eKAS) and prescription slips (ePresS).

## Architecture Patterns

### Core Structure
- **Entry Point**: `index.php` → `authenticate.php` → `home.php`
- **Session Management**: Every protected page calls `checkLogin()` from `function.php`
- **Layout Pattern**: All pages follow: `header.php` → `menu.php` → content → `footer.php`
- **Database Layer**: All DB operations go through `function.php` with PDO connections using `config.ini`

### Key Configuration
- **Database**: MySQL container `mysql-data` with schema `konsultatemp` (see `config.ini`)
- **Docker**: Uses PHP 5.5-Apache container with MySQL 8.0 (see `compose.yaml`)
- **Connection**: PDO with prepared statements throughout, always using `$ini['EPCB']` schema reference

### Database Schema Convention
All tables follow `TSEKAP_TBL_*` naming pattern:
- `TSEKAP_TBL_ENLIST` - Patient enrollment
- `TSEKAP_TBL_PROFILE` - Patient profiles  
- `TSEKAP_TBL_SOAP` - Consultation records
- `TSEKAP_TBL_PROF_*` - Profile sub-modules (medical history, social history, etc.)

## Core Modules

### 1. Registration/HSA (Health Screening & Assessment)
- Files: `hsa_*.php`, `registration_*.php`
- Primary workflow: Search → Data Entry → Profile completion
- Generates patient profiles with medical/social history

### 2. Consultation (SOAP Notes)
- Files: `consultation_*.php`  
- SOAP format: Subjective, Objective, Assessment, Plan
- Links to patient profiles via `CASE_NO`

### 3. Laboratory/Imaging
- Files: `labs_*.php`
- Diagnostic results (FBS, RBS blood tests via `TSEKAP_TBL_DIAG_*`)
- Integration with consultation records

### 4. Medicine Dispensing
- Files: `medicine_*.php`, `followup_meds_*.php`
- Prescription management and follow-up medication tracking
- Generates ePresS (electronic prescription slips)

### 5. XML Generation & PhilHealth Integration
- Files: `generate_xml*.php`, `fx_xml.php`
- Creates encrypted XML for PhilHealth submission
- Complex joins across multiple profile tables for comprehensive patient data

## Development Conventions

### File Organization
- **Data Entry**: `*_data_entry.php` - Forms for creating/editing records
- **Search**: `*_search.php` - Patient/record lookup interfaces  
- **Lists**: `*_list*.php` - Display patient records and results
- **Load**: `load*.php` - AJAX endpoints for dynamic form population
- **Print**: `print_*.php` - PDF generation using TCPDF

### Code Patterns
- **Function Files**: `function.php` (core), `function_global.php` (utilities)
- **Global Functions**: Always prefix with descriptive names (`getPrevPxRecordEnlist`, `saveCarryoverProfilingInfo`)
- **Database Queries**: Use schema reference `$ini['EPCB']` for all table names
- **Error Handling**: Basic try-catch with PDO exceptions, output to user via JavaScript alerts

### UI/UX Standards
- **Bootstrap 3.x** for responsive design
- **jQuery** for DOM manipulation and AJAX
- **DataTables** for list views (standard table ID: `listRecord`)
- **Chosen.js** for enhanced select dropdowns
- **Panel Structure**: Primary panels with `panel-primary` class for main content

## Development Workflow

### Local Development
```bash
# Start services
docker-compose up -d

# Access application
http://localhost:9000

# Database access
mysql -h localhost -P 3306 -u root -p
```

### Key Integration Points
- **Session Variables**: User authentication state in PHP sessions
- **AJAX Endpoints**: Load* files provide dynamic data for forms
- **PDF Generation**: TCPDF integration for eKAS/ePresS document generation
- **File Uploads**: Uses `files/` and `tmp/` directories for document storage

### Testing Patterns
- Test patient flows: Registration → HSA → Consultation → Lab → Medicine
- Verify XML generation for PhilHealth compliance
- Check PDF generation for eKAS/ePresS outputs
- Validate session management and authentication flows

## Critical Notes
- **Legacy PHP**: Uses PHP 5.5 - be mindful of syntax limitations
- **Database Transactions**: Complex operations use `begintransaction()` with rollback handling
- **Year Validation**: System enforces year-based validation for consultation entries
- **Case Numbers**: `CASE_NO` is the primary patient identifier across all modules
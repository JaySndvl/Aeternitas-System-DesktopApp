# Redundant & Useless Files in Codebase

This document lists all files that are redundant, unused, or should be removed from the codebase to improve maintainability.

---

##  CRITICAL - Remove Immediately

### 1. **Duplicate Test Files (Boilerplate)**
**Status:** Safe to delete  
**Files:**
- 	ests/Unit/ExampleTest.php - Boilerplate unit test
- 	ests/Feature/ExampleTest.php - Boilerplate feature test

### 2. **Script Files (Development Utilities)**
**Status:** Safe to delete  
**Files:**
- create_server_file.php - Redundant; use php artisan serve
- ix_log.php - One-time fix script (obsolete)
- insert_timekeeping_data.php - Should use database seeders

### 3. **Backup Views Folder**
**Status:** Safe to delete  
**Location:** esources/views/Backup/
- index.blade.php
- eports.blade.php
- 	emplates.blade.php
- schedule.backup

---

##  MEDIUM PRIORITY - Should Remove

### 4. **Unused Pagination Views**
**Location:** esources/views/vendor/pagination/

**DELETE:**
- bootstrap-4.blade.php
- bootstrap-5.blade.php
- default.blade.php
- semantic-ui.blade.php
- simple-bootstrap-4.blade.php
- simple-bootstrap-5.blade.php
- simple-default.blade.php

**KEEP:**
- tailwind.blade.php (actively used)
- simple-tailwind.blade.php

### 5. **Email Templates (Verify Usage)**
**Files:**
- resources/views/emails/welcome.blade.php
- resources/views/emails/test.blade.php
- resources/views/emails/password-reset.blade.php

---

##  LOW PRIORITY - Consider Removing

### 6. **Multiple Payroll Export Views (Duplication)**
**Files:**
- resources/views/payroll/export-pdf.blade.php
- resources/views/payroll/export-pdf-simple.blade.php
- resources/views/payroll/simple-payslip.blade.php
- resources/views/payroll/instant-payslip.blade.php
- resources/views/payslips/pdf.blade.php
- resources/views/payslips/company-payslip.blade.php

### 7. **Attendance Export PDFs (Likely Duplicated)**
**Files:**
- resources/views/attendance/exports/reports-pdf.blade.php
- resources/views/attendance/exports/pdf.blade.php
- resources/views/attendance/exports/overtime-pdf.blade.php
- resources/views/attendance/exports/leave-pdf.blade.php
- resources/views/attendance/exports/daily-pdf.blade.php

---

##  DATABASE MIGRATION NOTES

### Empty Migrations (Don't Delete - Immutable)
- database/migrations/2025_10_18_040316_add_night_shift_to_attendance_records_table.php

---

##  Quick Cleanup Checklist

1. [ ] Delete: resources/views/Backup/
2. [ ] Delete: tests/Unit/ExampleTest.php
3. [ ] Delete: tests/Feature/ExampleTest.php
4. [ ] Delete: unused pagination files (keep tailwind variants)
5. [ ] Archive: create_server_file.php, fix_log.php, insert_timekeeping_data.php
6. [ ] Review & consolidate: payroll and attendance exports

---

## Historical - Previously Removed

**Removed:**
- resources/views/positions/create.blade.php
- resources/views/positions/edit.blade.php
- resources/views/departments/create.blade.php
- resources/views/departments/edit.blade.php
- resources/views/tax-brackets/create.blade.php
- resources/views/tax-brackets/edit.blade.php

**Kept:**
- resources/views/positions/form.blade.php
- resources/views/departments/form.blade.php
- resources/views/tax-brackets/form.blade.php

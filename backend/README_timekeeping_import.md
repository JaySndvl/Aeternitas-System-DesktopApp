# Timekeeping Data Import Scripts

This directory contains scripts to import timekeeping/attendance data into the Aeternitas System database.

## Available Scripts

### 1. Manual SQL Script (`manual_timekeeping_insert.sql`)
**Recommended for direct database insertion**

This script can be run directly in your database management tool (phpMyAdmin, MySQL Workbench, etc.).

**Features:**
- Creates employees automatically if they don't exist
- Inserts attendance records with time_in and time_out
- Creates employee schedules for working days (8:00 AM - 5:00 PM)
- Uses a default "General" department
- Provides summary statistics after insertion

**How to use:**
1. Open your database management tool
2. Select your database
3. Copy and paste the entire `manual_timekeeping_insert.sql` file
4. Execute the script
5. Check the summary output for confirmation

### 2. Laravel Artisan Command (`app/Console/Commands/ImportTimekeepingData.php`)
**For Laravel integration**

This is a proper Laravel Artisan command that can be run from the command line.

**How to use:**
```bash
php artisan timekeeping:import
```

**Features:**
- Integrates with Laravel's database models
- Provides detailed console output
- Handles errors gracefully
- Uses Laravel's built-in validation and relationships

### 3. Standalone PHP Script (`insert_timekeeping_data.php`)
**For standalone execution**

This script can be run independently but requires Laravel's autoloader.

**How to use:**
```bash
php insert_timekeeping_data.php
```

## Data Structure

The scripts will create:

### Employees
- **Employee Code**: EMP-0001, EMP-0002, etc.
- **Names**: Parsed from the provided data
- **Department**: "General" (created automatically)
- **Position**: "Employee"
- **Salary**: 0 (default)
- **Hire Date**: 2025-01-01

### Attendance Records
- **Date**: Converted from MM/DD/YYYY to YYYY-MM-DD format
- **Time In**: Converted to full datetime format
- **Time Out**: Converted to full datetime format
- **Status**: "present" for records with both time_in and time_out
- **Hours**: Calculated as 8 hours per day

### Employee Schedules
- **Date**: Same as attendance records
- **Time In**: 08:00:00 (8:00 AM)
- **Time Out**: 17:00:00 (5:00 PM)
- **Status**: "Working"

## Data Summary

The imported data includes:
- **9 employees** (EMP-0001 through EMP-0009)
- **Date range**: September 9, 2025 to October 15, 2025
- **Total attendance records**: Approximately 100+ records
- **Working days**: Monday to Friday (weekends excluded)

## Notes

1. **Duplicate Prevention**: All scripts use `INSERT IGNORE` or check for existing records to prevent duplicates
2. **Error Handling**: Scripts will skip invalid records and continue processing
3. **Date Format**: Converts MM/DD/YYYY to YYYY-MM-DD format
4. **Time Format**: Converts H:MM to full datetime format
5. **Missing Data**: Records with missing time_in or time_out are handled appropriately

## Verification

After running any script, you can verify the data by:

1. **Check employees table:**
   ```sql
   SELECT * FROM employees WHERE employee_id LIKE 'EMP-%';
   ```

2. **Check attendance records:**
   ```sql
   SELECT COUNT(*) FROM attendance_records;
   ```

3. **Check employee schedules:**
   ```sql
   SELECT COUNT(*) FROM employee_schedules;
   ```

4. **View specific employee data:**
   ```sql
   SELECT e.employee_id, e.first_name, e.last_name, 
          ar.date, ar.time_in, ar.time_out, ar.status
   FROM employees e
   JOIN attendance_records ar ON e.id = ar.employee_id
   WHERE e.employee_id = 'EMP-0001'
   ORDER BY ar.date;
   ```

## Troubleshooting

If you encounter issues:

1. **Database Connection**: Ensure your database connection is working
2. **Permissions**: Ensure you have INSERT permissions on the tables
3. **Foreign Keys**: Ensure the departments table exists
4. **Date Format**: Check that your database accepts the date format used
5. **Duplicate Keys**: The scripts handle duplicates, but check for constraint violations

## Support

If you need to modify the data or add more employees, you can:
1. Edit the SQL script to add more data
2. Modify the PHP scripts to parse additional data formats
3. Use the Laravel Artisan command for programmatic data import




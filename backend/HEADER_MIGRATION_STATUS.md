# Header Component Migration Status

## Completed ✅
- [x] resources/views/companies/index.blade.php
- [x] resources/views/positions/index.blade.php
- [x] resources/views/tax-brackets/index.blade.php
- [x] resources/views/attendance/settings.blade.php

## To Do (Simple Headers) 🔄
- [ ] resources/views/attendance/temp-timekeeping.blade.php
- [ ] resources/views/attendance/daily.blade.php
- [ ] resources/views/attendance/reports.blade.php
- [ ] resources/views/attendance/leave-management.blade.php
- [ ] resources/views/attendance/create-record.blade.php
- [ ] resources/views/attendance/edit-record.blade.php
- [ ] resources/views/attendance/import-dtr-review.blade.php
- [ ] resources/views/attendance/leave-request-create.blade.php
- [ ] resources/views/attendance/schedule-v2/index.blade.php
- [ ] resources/views/attendance/schedule-v2/show.blade.php
- [ ] resources/views/attendance/schedule-v2/create.blade.php
- [ ] resources/views/attendance/schedule-v2/edit.blade.php
- [ ] resources/views/positions/show.blade.php
- [ ] resources/views/tax-brackets/show.blade.php

## To Do (Complex Headers - Multiple Actions/Conditionals) 🔴
- [ ] resources/views/attendance/timekeeping.blade.php (has export dropdown)
- [ ] resources/views/attendance/overtime.blade.php (has export dropdown)
- [ ] resources/views/attendance/period-management/index.blade.php (different header structure, role-based)
- [ ] resources/views/attendance/period-management/show.blade.php (complex layout)
- [ ] resources/views/attendance/period-management/create.blade.php (complex form)
- [ ] resources/views/attendance/period-management/payroll-preview.blade.php
- [ ] resources/views/attendance/period-management/payroll-summary.blade.php
- [ ] resources/views/attendance/import-dtr.blade.php (complex header with dropdowns)
- [ ] resources/views/attendance/time-in-out.blade.php (complex structure)

## Component Usage Example

```blade
<x-page-header 
    title="Companies"
    description="Manage company information and settings"
    :actions="[
        ['type' => 'link', 'label' => 'Add Company', 'href' => route('companies.create'), 'icon' => 'plus', 'variant' => 'primary']
    ]"
>
    <!-- Your content goes here -->
</x-page-header>
```

## Action Options

- **type**: 'link' or 'button'
- **label**: Button/link text
- **href**: (for links) Route path
- **icon**: Font Awesome icon name (without 'fa-' prefix)
- **variant**: 'primary' (blue), 'secondary' (white), or 'danger' (red)
- **onclick**: (for buttons) JavaScript to execute

## Notes

- Simple headers are straightforward - just extract title, description, and single action
- Complex headers with export dropdowns or role-based logic need to be refactored carefully
- The component automatically wraps content in `<div class="space-y-6">` structure

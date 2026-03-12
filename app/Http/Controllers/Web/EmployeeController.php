<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Account;
use App\Helpers\CompanyHelper;
use App\Mail\EmployeeWelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $query = Employee::with(['department', 'account']);
        
        // Filter by current company if set
        if ($currentCompany) {
            $query->forCompany($currentCompany->id);
        }
        
        $employees = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $user = Auth::user();

        return view('employees.index', compact('employees', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $departments = Department::query();
        if ($currentCompany) {
            $departments->forCompany($currentCompany->id);
        }
        $departments = $departments->get();
        
        // Load positions
        $positions = Position::query();
        if ($currentCompany) {
            $positions->forCompany($currentCompany->id);
        }
        $positions = $positions->active()->with('department')->orderBy('name')->get();
        
        $user = Auth::user();
        return view('employees.create', compact('departments', 'positions', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:accounts,email',
            'phone' => 'required|string|max:20',
            'mobile_number' => 'nullable|string|max:11',
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'date_of_birth' => 'nullable|date',
            'civil_status' => 'nullable|string|max:100',
            'home_address' => 'nullable|string|max:1000',
            'current_address' => 'nullable|string|max:1000',
            'facebook_link' => 'nullable|url|max:255',
            'linkedin_link' => 'nullable|url|max:255',
            'ig_link' => 'nullable|url|max:255',
            'other_link' => 'nullable|url|max:255',
            'emergency_full_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_home_address' => 'nullable|string|max:1000',
            'emergency_current_address' => 'nullable|string|max:1000',
            'emergency_mobile_number' => 'nullable|string|max:20',
            'emergency_email' => 'nullable|email|max:255',
            'emergency_facebook_link' => 'nullable|url|max:255',
            'loan_start_date' => 'nullable|date',
            'loan_end_date' => 'nullable|date|after_or_equal:loan_start_date',
            'loan_total_amount' => 'nullable|numeric|min:0',
            'loan_monthly_amortization' => 'nullable|numeric|min:0',
            'password' => 'required|string|min:8',
            'employee_id' => 'nullable|string|max:50|unique:employees,employee_id',
        ]);

        $currentCompany = CompanyHelper::getCurrentCompany();

        // Create employee
        $employeeData = [
            'employee_id' => $request->employee_id, // Will be auto-generated if null
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'mobile_number' => $request->mobile_number,
            'position' => $request->position,
            'department_id' => $request->department_id,
            'salary' => $request->salary,
            'hire_date' => $request->hire_date,
            'date_of_birth' => $request->date_of_birth,
            'civil_status' => $request->civil_status,
            'home_address' => $request->home_address,
            'current_address' => $request->current_address,
            'facebook_link' => $request->facebook_link,
            'linkedin_link' => $request->linkedin_link,
            'ig_link' => $request->ig_link,
            'other_link' => $request->other_link,
            'emergency_full_name' => $request->emergency_full_name,
            'emergency_relationship' => $request->emergency_relationship,
            'emergency_home_address' => $request->emergency_home_address,
            'emergency_current_address' => $request->emergency_current_address,
            'emergency_mobile_number' => $request->emergency_mobile_number,
            'emergency_email' => $request->emergency_email,
            'emergency_facebook_link' => $request->emergency_facebook_link,
            'loan_start_date' => $request->loan_start_date,
            'loan_end_date' => $request->loan_end_date,
            'loan_total_amount' => $request->loan_total_amount,
            'loan_monthly_amortization' => $request->loan_monthly_amortization,
        ];
        
        if ($currentCompany) {
            $employeeData['company_id'] = $currentCompany->id;
        }
        
        $employee = Employee::create($employeeData);

        // Create account
        $account = Account::create([
            'employee_id' => $employee->id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'employee',
        ]);

        // Send welcome email to employee
        try {
            Mail::to($account->email)->send(new EmployeeWelcomeMail(
                $employee->fresh(['department']), 
                $request->password, 
                $account
            ));
        } catch (\Exception $e) {
            // Log error but don't fail the employee creation
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully and welcome email sent.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $user = Auth::user();
        $requestedEmployeeId = $employee->id;
        
        // If logged-in user is an employee, they can ONLY view their own profile and records
        if ($user->role === 'employee') {
            $loggedInEmployee = $user->employee;
            if (!$loggedInEmployee && $user->employee_id) {
                $loggedInEmployee = Employee::find($user->employee_id);
            }
            
            // If no employee record found, redirect with error
            if (!$loggedInEmployee) {
                return redirect()->route('dashboard')
                    ->with('error', 'Your account is not linked to an employee record. Please contact administrator.');
            }
            
            // If they tried to access another employee's profile, redirect to their own
            if ($requestedEmployeeId !== $loggedInEmployee->id) {
                return redirect()->route('employees.show', $loggedInEmployee)
                    ->with('error', 'You can only view your own employee profile.');
            }
            
            // Always use the logged-in employee's record to ensure they ONLY see their own attendance records
            $employee = $loggedInEmployee;
        }
        
        // Load employee relationships
        $employee->load(['department', 'account', 'payrolls']);
        
        // Get attendance records - for employees, this will ONLY be their own records
        // For admin/hr/manager, this will be the selected employee's records
        $attendanceRecords = $employee->attendanceRecords()
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(10);
        
        return view('employees.show', compact('employee', 'user', 'attendanceRecords'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $currentCompany = CompanyHelper::getCurrentCompany();
        
        $departments = Department::query();
        if ($currentCompany) {
            $departments->forCompany($currentCompany->id);
        }
        $departments = $departments->get();
        
        $employee->load('account');
        $user = Auth::user();
        return view('employees.edit', compact('employee', 'departments', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:accounts,email,' . ($employee->account?->id ?? ''),
            'phone' => 'required|string|max:20',
            'mobile_number' => 'nullable|string|max:20',
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'date_of_birth' => 'nullable|date',
            'civil_status' => 'nullable|string|max:100',
            'home_address' => 'nullable|string|max:1000',
            'current_address' => 'nullable|string|max:1000',
            'facebook_link' => 'nullable|url|max:255',
            'linkedin_link' => 'nullable|url|max:255',
            'ig_link' => 'nullable|url|max:255',
            'other_link' => 'nullable|url|max:255',
            'emergency_full_name' => 'nullable|string|max:255',
            'emergency_relationship' => 'nullable|string|max:100',
            'emergency_home_address' => 'nullable|string|max:1000',
            'emergency_current_address' => 'nullable|string|max:1000',
            'emergency_mobile_number' => 'nullable|string|max:20',
            'emergency_email' => 'nullable|email|max:255',
            'emergency_facebook_link' => 'nullable|url|max:255',
            'loan_start_date' => 'nullable|date',
            'loan_end_date' => 'nullable|date|after_or_equal:loan_start_date',
            'loan_total_amount' => 'nullable|numeric|min:0',
            'loan_monthly_amortization' => 'nullable|numeric|min:0',
            'role' => 'required|in:admin,hr,manager,employee',
        ]);

        // Update employee
        $employee->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'mobile_number' => $request->mobile_number,
            'position' => $request->position,
            'department_id' => $request->department_id,
            'salary' => $request->salary,
            'hire_date' => $request->hire_date,
            'date_of_birth' => $request->date_of_birth,
            'civil_status' => $request->civil_status,
            'home_address' => $request->home_address,
            'current_address' => $request->current_address,
            'facebook_link' => $request->facebook_link,
            'linkedin_link' => $request->linkedin_link,
            'ig_link' => $request->ig_link,
            'other_link' => $request->other_link,
            'emergency_full_name' => $request->emergency_full_name,
            'emergency_relationship' => $request->emergency_relationship,
            'emergency_home_address' => $request->emergency_home_address,
            'emergency_current_address' => $request->emergency_current_address,
            'emergency_mobile_number' => $request->emergency_mobile_number,
            'emergency_email' => $request->emergency_email,
            'emergency_facebook_link' => $request->emergency_facebook_link,
            'loan_start_date' => $request->loan_start_date,
            'loan_end_date' => $request->loan_end_date,
            'loan_total_amount' => $request->loan_total_amount,
            'loan_monthly_amortization' => $request->loan_monthly_amortization,
        ]);

        // Update account if it exists
        if ($employee->account) {
            $employee->account->update([
                'email' => $request->email,
                'role' => $request->role ?? $employee->account->role,
            ]);
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        // Delete account first if it exists
        if ($employee->account) {
            $employee->account->delete();
        }
        
        // Delete employee
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Show payroll information for the employee.
     */
    public function payroll(Employee $employee)
    {
        $employee->load(['payrolls' => function($query) {
            $query->orderBy('created_at', 'desc');
        }]);
        
        $user = Auth::user();
        return view('employees.payroll', compact('employee', 'user'));
    }

    /**
     * Show BIO-ZK page.
     */
    public function bioZk()
    {
        $user = Auth::user();

        return view('employees.bio-zk', compact('user'));
    }

    /**
     * Show YTD-INFO page.
     */
    public function ytdInfo()
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();

        $employeesQuery = Employee::query()->orderBy('first_name')->orderBy('last_name');
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }

        $employees = $employeesQuery->get(['id', 'employee_id', 'first_name', 'last_name']);

        return view('employees.ytd-info', compact('user', 'employees'));
    }

    /**
     * Show Education/Training/Rating page.
     */
    public function educationTrainingRating()
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();

        $employeesQuery = Employee::query()->orderBy('first_name')->orderBy('last_name');
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }

        $employees = $employeesQuery->get(['id', 'employee_id', 'first_name', 'last_name']);

        return view('employees.education-training-rating', compact('user', 'employees'));
    }

    /**
     * Show Other Employee Info page.
     */
    public function otherEmployeeInfo(Request $request)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        $hasOtherInfoTable = Schema::hasTable('employee_other_infos');

        $employeesQuery = Employee::query()->orderBy('first_name')->orderBy('last_name');
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }

        $employees = $employeesQuery->get(['id', 'employee_id', 'first_name', 'last_name']);

        $selectedEmployeeId = $request->query('employee_id');
        $selectedEmployee = null;
        $otherInfo = null;

        if ($selectedEmployeeId && $hasOtherInfoTable) {
            $selectedEmployeeQuery = Employee::query()
                ->with('otherInfo')
                ->where('id', $selectedEmployeeId);

            if ($currentCompany) {
                $selectedEmployeeQuery->forCompany($currentCompany->id);
            }

            $selectedEmployee = $selectedEmployeeQuery->first();
            $otherInfo = $selectedEmployee?->otherInfo;
        }

        return view('employees.other-employee-info', compact('user', 'employees', 'selectedEmployeeId', 'selectedEmployee', 'otherInfo', 'hasOtherInfoTable'));
    }

    /**
     * Save Other Employee Info fields.
     */
    public function saveOtherEmployeeInfo(Request $request)
    {
        $currentCompany = CompanyHelper::getCurrentCompany();

        if (!Schema::hasTable('employee_other_infos')) {
            return redirect()->route('employees.other-employee-info', ['employee_id' => $request->input('employee_id')])
                ->with('error', 'Other employee info table is not ready yet. Please run database migrations first.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'address' => 'nullable|string|max:255',
            'pov_address' => 'nullable|string|max:255',
            'no_street' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'town_district' => 'nullable|string|max:255',
            'city_province' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'religion' => 'nullable|string|max:255',
            'blood_type' => 'nullable|string|max:255',
            'citizenship' => 'nullable|string|max:255',
            'height' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'drivers_license' => 'nullable|string|max:255',
            'prc_no' => 'nullable|string|max:255',
            'father' => 'nullable|string|max:255',
            'mother' => 'nullable|string|max:255',
            'spouse' => 'nullable|string|max:255',
            'spouse_employed' => 'nullable|boolean',
        ]);

        $employeeQuery = Employee::query()->where('id', $validated['employee_id']);
        if ($currentCompany) {
            $employeeQuery->forCompany($currentCompany->id);
        }
        $employee = $employeeQuery->firstOrFail();

        $payload = $validated;
        unset($payload['employee_id']);
        $payload['spouse_employed'] = $request->boolean('spouse_employed');

        $otherInfo = $employee->otherInfo()->firstOrNew([]);
        $otherInfo->employee_id = $employee->id;
        $otherInfo->fill($payload);
        $otherInfo->save();

        return redirect()->route('employees.other-employee-info', ['employee_id' => $employee->id])
            ->with('success', 'Other employee info saved successfully.');
    }

    /**
     * Upload/change employee photo in Other Employee Info.
     */
    public function uploadOtherEmployeePhoto(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admin can upload employee photo.');
        }

        $currentCompany = CompanyHelper::getCurrentCompany();

        if (!Schema::hasTable('employee_other_infos')) {
            return redirect()->route('employees.other-employee-info', ['employee_id' => $request->input('employee_id')])
                ->with('error', 'Other employee info table is not ready yet. Please run database migrations first.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $employeeQuery = Employee::query()->where('id', $validated['employee_id']);
        if ($currentCompany) {
            $employeeQuery->forCompany($currentCompany->id);
        }
        $employee = $employeeQuery->firstOrFail();

        $otherInfo = $employee->otherInfo()->firstOrNew([]);
        $otherInfo->employee_id = $employee->id;

        if ($otherInfo->photo_path && Storage::disk('public')->exists($otherInfo->photo_path)) {
            Storage::disk('public')->delete($otherInfo->photo_path);
        }

        $otherInfo->photo_path = $request->file('photo')->store('employee-photos', 'public');
        $otherInfo->save();

        return redirect()->route('employees.other-employee-info', ['employee_id' => $employee->id])
            ->with('success', 'Employee photo uploaded successfully.');
    }

    /**
     * Clear/remove employee photo in Other Employee Info.
     */
    public function clearOtherEmployeePhoto(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Only admin can clear employee photo.');
        }

        $currentCompany = CompanyHelper::getCurrentCompany();

        if (!Schema::hasTable('employee_other_infos')) {
            return redirect()->route('employees.other-employee-info', ['employee_id' => $request->input('employee_id')])
                ->with('error', 'Other employee info table is not ready yet. Please run database migrations first.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
        ]);

        $employeeQuery = Employee::query()->where('id', $validated['employee_id']);
        if ($currentCompany) {
            $employeeQuery->forCompany($currentCompany->id);
        }
        $employee = $employeeQuery->firstOrFail();

        $otherInfo = $employee->otherInfo;
        if ($otherInfo && $otherInfo->photo_path) {
            if (Storage::disk('public')->exists($otherInfo->photo_path)) {
                Storage::disk('public')->delete($otherInfo->photo_path);
            }
            $otherInfo->photo_path = null;
            $otherInfo->save();
        }

        return redirect()->route('employees.other-employee-info', ['employee_id' => $employee->id])
            ->with('success', 'Employee photo removed successfully.');
    }

    /**
     * Show Previous Employer & Other page.
     */
    public function prevEmpOth(Request $request)
    {
        $user = Auth::user();
        $currentCompany = CompanyHelper::getCurrentCompany();
        $hasPreviousEmploymentsTable = Schema::hasTable('previous_employments');

        $employeesQuery = Employee::query()->orderBy('first_name')->orderBy('last_name');
        if ($currentCompany) {
            $employeesQuery->forCompany($currentCompany->id);
        }

        $employees = $employeesQuery->get(['id', 'employee_id', 'first_name', 'last_name']);

        $selectedEmployeeId = $request->query('employee_id');
        $selectedEmployee = null;
        $prefilledRows = collect();

        if ($selectedEmployeeId && $hasPreviousEmploymentsTable) {
            $selectedEmployeeQuery = Employee::query()
                ->with('previousEmployments')
                ->where('id', $selectedEmployeeId);

            if ($currentCompany) {
                $selectedEmployeeQuery->forCompany($currentCompany->id);
            }

            $selectedEmployee = $selectedEmployeeQuery->first();

            if ($selectedEmployee) {
                $prefilledRows = $selectedEmployee->previousEmployments->keyBy('sequence');
            }
        }

        $yearNow = now()->year;
        $years = range($yearNow, $yearNow - 60);

        return view('employees.prev-emp-oth', compact('user', 'employees', 'selectedEmployeeId', 'selectedEmployee', 'prefilledRows', 'years', 'hasPreviousEmploymentsTable'));
    }

    /**
     * Save Previous Employer & Other page.
     */
    public function savePrevEmpOth(Request $request)
    {
        $currentCompany = CompanyHelper::getCurrentCompany();

        if (!Schema::hasTable('previous_employments')) {
            return redirect()->route('employees.prev-emp-oth', ['employee_id' => $request->input('employee_id')])
                ->with('error', 'Previous employment table is not ready yet. Please run database migrations first.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|uuid|exists:employees,id',
            'rows' => 'required|array|size:5',
            'rows.*.employment_name' => 'nullable|string|max:255',
            'rows.*.position' => 'nullable|string|max:255',
            'rows.*.start_month' => 'nullable|integer|min:1|max:12',
            'rows.*.start_year' => 'nullable|integer|min:1900|max:2100',
            'rows.*.end_month' => 'nullable|integer|min:1|max:12',
            'rows.*.end_year' => 'nullable|integer|min:1900|max:2100',
        ]);

        $employeeQuery = Employee::query()->where('id', $validated['employee_id']);
        if ($currentCompany) {
            $employeeQuery->forCompany($currentCompany->id);
        }

        $employee = $employeeQuery->firstOrFail();

        DB::transaction(function () use ($employee, $validated) {
            $employee->previousEmployments()->delete();

            foreach ($validated['rows'] as $index => $row) {
                $hasValue = filled($row['employment_name'] ?? null)
                    || filled($row['position'] ?? null)
                    || filled($row['start_month'] ?? null)
                    || filled($row['start_year'] ?? null)
                    || filled($row['end_month'] ?? null)
                    || filled($row['end_year'] ?? null);

                if (!$hasValue) {
                    continue;
                }

                $employee->previousEmployments()->create([
                    'sequence' => $index + 1,
                    'employment_name' => $row['employment_name'] ?? null,
                    'position' => $row['position'] ?? null,
                    'start_month' => $row['start_month'] ?? null,
                    'start_year' => $row['start_year'] ?? null,
                    'end_month' => $row['end_month'] ?? null,
                    'end_year' => $row['end_year'] ?? null,
                ]);
            }
        });

        return redirect()->route('employees.prev-emp-oth', ['employee_id' => $employee->id])
            ->with('success', 'Previous employment details saved successfully.');
    }

public function documents($id)
{
    $user = auth()->user();
    $currentCompanyId = session('current_company_id');
    
    // Get employee only if they belong to the current company
    $employee = Employee::with(['department'])
        ->where('company_id', $currentCompanyId)
        ->find($id);
    
    if (!$employee) {
        abort(404, 'Employee not found or not in current company');
    }
    
    $documents = [];
    
    return view('employees.documents', compact('employee', 'documents', 'user'));
}
}
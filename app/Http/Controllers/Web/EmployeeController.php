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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
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
            'position' => $request->position,
            'department_id' => $request->department_id,
            'salary' => $request->salary,
            'hire_date' => $request->hire_date,
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
            'position' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'role' => 'required|in:admin,hr,manager,employee',
        ]);

        // Update employee
        $employee->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'position' => $request->position,
            'department_id' => $request->department_id,
            'salary' => $request->salary,
            'hire_date' => $request->hire_date,
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
}
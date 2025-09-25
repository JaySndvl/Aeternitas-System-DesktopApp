<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with(['department', 'account'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $user = auth()->user();

        return view('employees.index', compact('employees', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $user = auth()->user();
        return view('employees.create', compact('departments', 'user'));
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
        ]);

        // Create employee
        $employee = Employee::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'position' => $request->position,
            'department_id' => $request->department_id,
            'salary' => $request->salary,
            'hire_date' => $request->hire_date,
        ]);

        // Create account
        Account::create([
            'employee_id' => $employee->id,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'employee',
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['department', 'account', 'payrolls']);
        $user = auth()->user();
        return view('employees.show', compact('employee', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $employee->load('account');
        $user = auth()->user();
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
        
        $user = auth()->user();
        return view('employees.payroll', compact('employee', 'user'));
    }
}
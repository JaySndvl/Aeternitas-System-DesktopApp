<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get current company from session or user
        $currentCompanyId = session('current_company_id');
        
        // If no company in session, try to get from user's employee record
        if (!$currentCompanyId && $user->employee) {
            $currentCompanyId = $user->employee->company_id;
        }
        
        // If still no company, get the first company (for admin users)
        if (!$currentCompanyId) {
            $firstCompany = Company::first();
            $currentCompanyId = $firstCompany ? $firstCompany->id : null;
        }
        
        // Store in session for future use
        if ($currentCompanyId) {
            session(['current_company_id' => $currentCompanyId]);
        }
        
        // Get departments for the current company
        $departments = Department::where('company_id', $currentCompanyId)
            ->orderBy('name')
            ->get();
        
        // Get employees for the current company with their department
        $employees = Employee::with(['department'])
            ->where('company_id', $currentCompanyId)
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('position', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString();
        
        // Get all companies for admin users to switch between
        $companies = [];
        if ($user->role === 'admin' || $user->role === 'hr') {
            $companies = Company::orderBy('name')->get();
        }
        
        return view('documents.index', compact('user', 'employees', 'departments', 'companies', 'currentCompanyId'));
    }
    
    public function getEmployeeDetails($id)
    {
        $user = auth()->user();
        $currentCompanyId = session('current_company_id');
        
        // Get employee only if they belong to the current company
        $employee = Employee::with(['department'])
            ->where('company_id', $currentCompanyId)
            ->find($id);
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found or not in current company'
            ], 404);
        }
        
        $html = view('documents.partials.employee-details', compact('employee'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    public function export(Request $request)
    {
        $user = auth()->user();
        $currentCompanyId = session('current_company_id');
        
        // Get filtered employees for current company
        $employees = Employee::with(['department'])
            ->where('company_id', $currentCompanyId)
            ->when($request->department_id, function ($query, $departmentId) {
                return $query->where('department_id', $departmentId);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->get();
        
        // Format for export
        $exportData = [];
        foreach ($employees as $employee) {
            $exportData[] = [
                'employee_id' => $employee->employee_id,
                'full_name' => $employee->full_name,
                'department' => $employee->department->name ?? 'N/A',
                'position' => $employee->position,
                'status' => ucfirst($employee->status),
                'email' => $employee->email,
                'phone' => $employee->phone,
                'salary' => '₱' . number_format($employee->salary, 2),
                'hire_date' => $employee->hire_date ? $employee->hire_date->format('Y-m-d') : 'N/A',
                'address' => $employee->address,
                'employment_type' => ucfirst($employee->employment_type ?? 'Regular'),
            ];
        }
        
        // Get current company name for export
        $currentCompany = Company::find($currentCompanyId);
        $companyName = $currentCompany ? $currentCompany->name : 'Unknown Company';
        
        // Get export type from request or default to CSV
        $exportType = $request->get('type', 'csv');
        
        if ($exportType === 'pdf') {
            return $this->exportPDF($exportData, $request, $companyName);
        } else {
            return $this->exportCSV($exportData, $companyName);
        }
    }
    
    private function exportCSV($data, $companyName)
    {
        $filename = $companyName . '-employee-documents-' . date('Y-m-d-H-i') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data, $companyName) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Add company header
            fputcsv($file, ['Company:', $companyName]);
            fputcsv($file, ['Export Date:', date('F j, Y g:i A')]);
            fputcsv($file, []); // Empty row
            
            // Add headers
            fputcsv($file, array_keys($data[0] ?? []));
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportPDF($data, $request, $companyName)
    {
        $filename = $companyName . '-employee-documents-' . date('Y-m-d-H-i') . '.pdf';
        
        $filters = [
            'company' => $companyName,
            'department' => $request->department_id ? 
                Department::find($request->department_id)->name ?? 'All' : 'All Departments',
            'status' => $request->status ? ucfirst($request->status) : 'All Status',
            'search' => $request->search ?: 'None',
            'export_date' => now()->format('F j, Y g:i A'),
            'total_employees' => count($data),
        ];
        
        $pdf = Pdf::loadView('documents.exports.pdf', [
            'employees' => $data,
            'filters' => $filters,
        ]);
        
        return $pdf->download($filename);
    }
    
    public function exportEmployee($id)
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
        
        // Get company name
        $currentCompany = Company::find($currentCompanyId);
        $companyName = $currentCompany ? $currentCompany->name : 'Unknown Company';
        
        $filename = $companyName . '-employee-' . $employee->employee_id . '-details-' . date('Y-m-d') . '.pdf';
        
        $pdf = Pdf::loadView('documents.exports.employee-details', [
            'employee' => $employee,
            'companyName' => $companyName,
            'export_date' => now()->format('F j, Y g:i A'),
        ]);
        
        return $pdf->download($filename);
    }
    
    // Add method to switch company
    public function switchCompany(Request $request)
    {
        $companyId = $request->company_id;
        $company = Company::find($companyId);
        
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found.');
        }
        
        // Store selected company in session
        session(['current_company_id' => $companyId]);
        
        return redirect()->route('documents.index')->with('success', 'Switched to ' . $company->name);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->paginate(15);
        return response()->json($departments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string|max:1000',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $department = Department::create($request->validated());

        return response()->json($department, 201);
    }

    public function show(Department $department)
    {
        $department->load('employees');
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string|max:1000',
            'budget' => 'nullable|numeric|min:0',
        ]);

        $department->update($request->validated());

        return response()->json($department);
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json(null, 204);
    }

    public function employees(Department $department)
    {
        $employees = $department->employees()->paginate(15);
        return response()->json($employees);
    }
}

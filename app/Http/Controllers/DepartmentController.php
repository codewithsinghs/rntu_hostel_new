<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if($request->query('faculty_id'))
            {
                $facultyId = $request->query('faculty_id');
                $departments = Department::where('faculty_id', $facultyId)->with('faculty')->get();
            } else {
                $departments = Department::with('faculty')->get();
            }
            if ($departments->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No departments found.', 'data' => null], 404);
            }

            return response()->json(['success' => true, 'message' => 'Departments fetched successfully.', 'data' => $departments], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching departments.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:0,1',
                'faculty_id' => 'required|exists:faculties,id',
            ]);

            $department = new Department();
            $department->name = $request->name;
            $department->status = $request->status;
            $department->faculty_id = $request->faculty_id;
            $department->save();

            return response()->json(['success' => true, 'message' => 'Department created successfully.', 'data' => $department], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An unexpected error occurred.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }       

        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $department = Department::with('faculty')->findOrFail($id);
            return response()->json(['success' => true, 'message' => 'Department fetched successfully.', 'data' => $department], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Department not found.', 'data' => null], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching the department.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Log::info($request->all());
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:0,1',
                'faculty_id' => 'required|exists:faculties,id',
            ]);
            $department = Department::findOrFail($id);
            $department->name = $request->name;
            $department->status = $request->status;
            $department->faculty_id = $request->faculty_id;
            $department->save();

            return response()->json(['success' => true, 'message' => 'Department updated successfully.', 'data' => $department], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Department not found.', 'data' => null], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the department.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $department = Department::findOrFail($id);
            $department->delete();
            return response()->json(['success' => true, 'message' => 'Department deleted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Department not found.', 'data' => null], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the department.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }   
    }

    public function getActiveDepartments($facultyId = null)
    {
        try {
            $query = Department::where('status', 1)->with('faculty');
            if ($facultyId) {
                $query->where('faculty_id', $facultyId);
            }
            $departments = $query->get();
            if ($departments->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active departments found.', 'data' => null], 404);
            }
            return response()->json(['success' => true, 'message' => 'Active departments fetched successfully.', 'data' => $departments], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching active departments.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }
}

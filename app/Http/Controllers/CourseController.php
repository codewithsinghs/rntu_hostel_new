<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Log;



class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $courses = Course::with('department')->with('faculty')->get();
            Log::info($courses);
            if ($courses->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No courses found.', 'data' => null], 404);
            }

            return response()->json(['success' => true, 'message' => 'Courses fetched successfully.', 'data' => $courses], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching courses.', 'errors' => ['exception' => $e->getMessage()]], 500);
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
            Log::info($request->all());
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:0,1',
                'department_id' => 'required|exists:departments,id',
            ]);
            $course = new Course();
            $course->name = $request->name;
            $course->status = $request->status;
            $course->department_id = $request->department_id;
            $course->save();
            
            return response()->json(['success' => true, 'message' => 'Course created successfully.', 'data' => $course], 201);
        } catch (\Exception $e) {   
            return response()->json(['success' => false, 'message' => 'An error occurred while creating the course.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
        

    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $course = Course::with('department.faculty')->findOrFail($id);
            return response()->json(['success' => true, 'message' => 'Course fetched successfully.', 'data' => $course], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Course not found.', 'errors' => ['exception' => $e->getMessage()]], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching the course.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:0,1',
                'department_id' => 'required|exists:departments,id',
            ]);
            $course->name = $request->name;
            $course->status = $request->status;
            $course->department_id = $request->department_id;
            $course->save();

            return response()->json(['success' => true, 'message' => 'Course updated successfully.', 'data' => $course], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the course.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();
            return response()->json(['success' => true, 'message' => 'Course deleted successfully.'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Course not found.', 'data' => null], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the course.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }

    public function getActiveCourses($department_id = null)
    {
        try {
            $query = Course::where('status', 1)->with('department')->with('faculty');
            if ($department_id) {
                $query->where('department_id', $department_id);
            }
            $courses = $query->get();
            if ($courses->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active courses found.', 'data' => null], 404);
            }
            return response()->json(['success' => true, 'message' => 'Active courses fetched successfully.', 'data' => $courses], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching active courses.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }       
}

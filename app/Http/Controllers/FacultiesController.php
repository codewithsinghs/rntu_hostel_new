<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Faculty;

class FacultiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $faculties = Faculty::with('university')->get();

            if ($faculties->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No faculties found.', 'data' => null], 404);
            }

            return response()->json(['success' => true, 'message' => 'Faculties fetched successfully.', 'data' => $faculties], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching faculties.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }   

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
        $user = User::find($request->header('auth-id'));
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);

        // Create a new faculty
        $faculty = new \App\Models\Faculty();
        $faculty->name = $request->name;
        $faculty->status = $request->status;
        $faculty->university_id = $user->university_id;
        $faculty->save();
        
        return response()->json(['success' => true, 'message' => 'Faculty created successfully.', 'data' => $faculty], 201);
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
            $faculty = \App\Models\Faculty::findOrFail($id);
            return response()->json(['success' => true, 'message' => 'Faculty fetched successfully.', 'data' => $faculty], 200);
        }       
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Faculty not found.', 'data' => null], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching the faculty.', 'errors' => ['exception' => $e->getMessage()]], 500);
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
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:0,1',
            ]);     
            // Find the faculty by ID
            $faculty = \App\Models\Faculty::findOrFail($id);

            // Update the faculty details
            $faculty->name = $request->name;
            $faculty->status = $request->status;
            $faculty->save();

            return response()->json(['success' => true, 'message' => 'Faculty updated successfully.', 'data' => $faculty], 200);
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Faculty not found.', 'data' => null], 404);
        }   
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while updating the faculty.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $faculty = \App\Models\Faculty::findOrFail($id);
            $faculty->delete();

            return response()->json(['success' => true, 'message' => 'Faculty deleted successfully.', 'data' => null], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Faculty not found.', 'data' => null], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while deleting the faculty.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }   
        
    }

    public function getActiveFaculties()
    {
        try {
            $faculties = \App\Models\Faculty::where('status', 1)->get();

            if ($faculties->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No active faculties found.', 'data' => null], 404);
            }

            return response()->json(['success' => true, 'message' => 'Active faculties fetched successfully.', 'data' => $faculties], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching active faculties.', 'errors' => ['exception' => $e->getMessage()]], 500);
        }
    }
}

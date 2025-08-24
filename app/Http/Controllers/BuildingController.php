<?php

namespace App\Http\Controllers;



use App\Models\Building;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;

class BuildingController extends Controller
{
    private function apiResponse($success, $message, $data = null, $status = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data ?? null,
            'errors' => $errors ?? null
        ], $status);
    }

    // ✅ Get all buildings
    public function index()
    {
        try {
            $buildings = Building::all();
            return $this->apiResponse(true, 'Buildings fetched successfully.', $buildings);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch buildings.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Create a new building
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'building_code' => 'required|string|unique:buildings,building_code',
            'floors' => 'required|integer|min:1',
        ]);
       
        try {
            $user = Helper::get_auth_admin_user($request);
             
            $validatedData = $request->validate([
                'name' => 'required|string',
                'building_code' => 'required|string|unique:buildings,building_code',
                'floors' => 'required|integer|min:1',
            ]);
            $validatedData["university_id"] = $user->university_id;
            $validatedData["status"] ="active"; // Default status
            $validatedData["created_by"] = $user->id;

            $building = Building::create($validatedData);
            Log::info('here');
            return $this->apiResponse(true, 'Building created successfully.', $building, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
             Log::info($e->errors());
            return $this->apiResponse(false, 'Validation error.', null, 422, $e->errors());
        } catch (Exception $e) {
            Log::info($e);
            return $this->apiResponse(false, 'Something went wrong while creating building.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Get a building by ID
    public function show($id)
    {
        try {
            $building = Building::findOrFail($id);
            return $this->apiResponse(true, 'Building fetched successfully.', $building);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Building not found.', null, 404, [
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Something went wrong while fetching building.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Update a building
    public function update(Request $request, $id)
    {
        try {
            $building = Building::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string',
                'building_code' => [
                    'sometimes',
                    'required',
                    'string',
                    Rule::unique('buildings', 'building_code')->ignore($id),
                ],
                // 'university_id' => 'sometimes|required|exists:universities,id',
                'status' => 'sometimes|required|in:active,inactive',
                'floors' => 'sometimes|required|integer|min:1',
            ]);
            // $validatedData['university_id'] = Helper::get_auth_admin_user($request)->university_id;

            $building->update($validatedData);

            return $this->apiResponse(true, 'Building updated successfully.', $building);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Building not found.', null, 404, [
                'error' => $e->getMessage()
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->apiResponse(false, 'Validation error.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Something went wrong while updating building.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Delete a building
    public function destroy($id)
    {
        try {
            $building = Building::findOrFail($id);
            $building->delete();

            return $this->apiResponse(true, 'Building deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Building not found.', null, 404, [
                'error' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Something went wrong while deleting building.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

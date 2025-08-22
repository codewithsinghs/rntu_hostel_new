<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FeeHead;
use App\Http\Controllers\Controller;

class FeeHeadController extends Controller
{
    // Reusable response format
    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if (!is_null($data)) $response['data'] = $data;
        else $response['data'] = null;

        if (!is_null($errors)) $response['errors'] = $errors;
        else $response['errors'] = null;

        return response()->json($response, $statusCode);
    }

    /**
     * Store a new fee head
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:fee_heads,name',
            'created_by' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation Error.', null, 422, $validator->errors());
        }

        try {
            $feeHead = FeeHead::create([
                'name' => $request->name,
                'created_by' => $request->created_by,
            ]);

            return $this->apiResponse(true, 'Fee head created successfully.', $feeHead, 201);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to create fee head.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update an existing fee head
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:fee_heads,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation Error.', null, 422, $validator->errors());
        }

        try {
            $feeHead = FeeHead::findOrFail($id);
            $feeHead->name = $request->name;
            $feeHead->save();

            return $this->apiResponse(true, 'Fee head updated successfully.', $feeHead);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Fee head not found.', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to update fee head.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get all fee heads
     */
    public function index()
    {
        try {
            $feeHeads = FeeHead::orderBy('created_at', 'desc')->get();

            return $this->apiResponse(true, 'Fee heads retrieved successfully.', $feeHeads);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve fee heads.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get a single fee head by ID
     */
    public function show($id)
    {
        try {
            $feeHead = FeeHead::findOrFail($id);
            return $this->apiResponse(true, 'Fee head retrieved successfully.', $feeHead);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Fee head not found.', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve fee head.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a fee head
     */
    public function destroy($id)
    {
        try {
            $feeHead = FeeHead::findOrFail($id);
            $feeHead->delete();

            return $this->apiResponse(true, 'Fee head deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Fee head not found.', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse(false, 'Failed to delete fee head.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}

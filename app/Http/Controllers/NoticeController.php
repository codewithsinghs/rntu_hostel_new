<?php

namespace App\Http\Controllers;


use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class NoticeController extends Controller
{
    private function apiResponse($success, $message, $data = null, $status = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data ?? null,
            'errors' => $errors ?? null,
        ], $status);
    }

    // Create a Notice (POST)
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'message_from' => 'required|string',
                'message' => 'required|string',
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
            ]);

            $notice = Notice::create($validatedData);

            return $this->apiResponse(true, 'Notice created successfully!', $notice, 201);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to create notice.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // Get All Notices (GET)
    public function index()
    {
        try {
            $notices = Notice::all();

            return $this->apiResponse(true, 'Notices retrieved successfully!', $notices);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve notices.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // Get Single Notice (GET)
    public function show($id)
    {
        try {
            $notice = Notice::findOrFail($id);

            return $this->apiResponse(true, 'Notice retrieved successfully!', $notice);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Notice not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve notice.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // Update Notice (PUT/PATCH)
    public function update(Request $request, $id)
    {
        try {
            $notice = Notice::findOrFail($id);

            $validatedData = $request->validate([
                'message_from' => 'sometimes|string',
                'message' => 'sometimes|string',
                'from_date' => 'sometimes|date',
                'to_date' => 'sometimes|date|after_or_equal:from_date',
            ]);

            $notice->update($validatedData);

            return $this->apiResponse(true, 'Notice updated successfully!', $notice);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Notice not found.', null, 404);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to update notice.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // Delete Notice (DELETE)
    public function destroy($id)
    {
        try {
            $notice = Notice::findOrFail($id);
            $notice->delete();

            return $this->apiResponse(true, 'Notice deleted successfully!', null);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Notice not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to delete notice.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}

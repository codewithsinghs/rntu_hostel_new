<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use App\Helpers\Helper;
use PHPUnit\TextUI\Help;

class FeedbackController extends Controller
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

    // Store Feedback (Resident ID from URL)
    public function store(Request $request)
    {
        try {
            $resident_id = Helper::get_resident_details($request->header('auth-id'))->id;
            $request->validate([
                'facility_name' => 'required|string',
                'feedback' => 'required|string',
                'suggestion' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('feedback_photos', 'public');
            }

            $feedback = Feedback::create([
                'resident_id' => $resident_id,
                'facility_name' => $request->facility_name,
                'feedback' => $request->feedback,
                'suggestion' => $request->suggestion,
                'photo_path' => $photoPath
            ]);

            return $this->apiResponse(true, 'Feedback submitted successfully.', $feedback, 201);

        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation error.', null, 422, $e->errors());
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to submit feedback.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // Fetch all feedbacks with resident & user details
    public function index()
    {
        try {
            $feedbacks = Feedback::with(['resident.user'])->get();

            $feedbacks->transform(function ($feedback) {
                if ($feedback->photo_path) {
                    $feedback->photo_url = asset('storage/' . $feedback->photo_path);
                }
                return $feedback;
            });

            return $this->apiResponse(true, 'Feedbacks retrieved successfully.', $feedbacks);

        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch feedbacks.', null, 500, ['error' => $e->getMessage()]);
        }
    }

    // Get feedback by ID with resident & user details
    public function show($id)
    {
        try {
            $feedback = Feedback::with(['resident.user'])->findOrFail($id);

            if ($feedback->photo_path) {
                $feedback->photo_url = asset('storage/' . $feedback->photo_path);
            }

            return $this->apiResponse(true, 'Feedback retrieved successfully.', $feedback);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Feedback not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch feedback.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}

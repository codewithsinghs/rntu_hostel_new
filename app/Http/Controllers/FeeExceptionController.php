<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Models\FeeException;
use App\Models\Guest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;



class FeeExceptionController extends Controller
{

    private function apiResponse($success, $message, $data = null, $statusCode = 200, $errors = null)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        $response['data'] = $data !== null ? $data : null;
        $response['errors'] = $errors !== null ? $errors : null;

        return response()->json($response, $statusCode);
    }






    // public function store(Request $request)
    // {
    //     // Step 1: Validate input
    //     $validator = Validator::make($request->all(), [
    //         'guest_id'       => 'required|exists:guests,id',
    //         'created_by'     => 'required|integer',
    //         'hostel_fee'     => 'required|numeric|min:0',
    //         'caution_money'  => 'required|numeric|min:0',
    //         'facility'       => 'nullable|string|max:255',
    //         'remarks'        => 'nullable|string',
    //         'approved_by'    => 'nullable|string|max:255',
    //         'months'         => 'nullable|integer|min:0',
    //         'days'           => 'nullable|integer|min:0',
    //         'document'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    //     ]);

    //     // Step 2: Return validation errors if any
    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'data' => null,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     // Step 3: Get validated input
    //     $validated = $validator->validated();

    //     try {
    //         // Step 4: Fetch guest and check conditions
    //         $guest = Guest::findOrFail($validated['guest_id']);


    //         if ($guest->fee_waiver === 0) {
    //             return $this->apiResponse(false, 'Guest did not apply for waiver.', null, 400);
    //         } elseif ($guest->status === 'paid') {
    //             return $this->apiResponse(false, 'Guest has already paid.', null, 400);
    //         }

    //         // Step 5: Prepare data
    //         $data = [
    //             'guest_id'       => $validated['guest_id'],
    //             'created_by'     => $validated['created_by'],
    //             'hostel_fee'     => $validated['hostel_fee'],
    //             'caution_money'  => $validated['caution_money'],
    //             'total_amount'   => $validated['hostel_fee'] + $validated['caution_money'],
    //             'facility'       => $validated['facility'] ?? null,
    //             'remarks'        => $validated['remarks'] ?? null,
    //             'approved_by'    => $validated['approved_by'] ?? null,
    //         ];

    //         // Step 6: Handle document upload
    //         if ($request->hasFile('document')) {
    //             $file = $request->file('document');
    //             $filename = time() . '_' . $file->getClientOriginalName();
    //             $path = $file->storeAs('fee_documents', $filename, 'public');
    //             $data['document_path'] = 'storage/' . $path;
    //         }

    //         // Step 7: Create or update fee exception
    //         $feeException = FeeException::updateOrCreate(
    //             ['guest_id' => $data['guest_id']],
    //             $data
    //         );

    //         // Step 8: Update guest status, months, and days
    //         $guest->status = 'waiver_approved';
    //         if (isset($validated['months'])) {
    //             $guest->months = $validated['months'];
    //         }
    //         if (isset($validated['days'])) {
    //             $guest->days = $validated['days'];
    //         }

    //         $guest->status = 'waiver_approved';
    //         $guest->save();

    //         // Step 9: Return success response
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Fee exception saved successfully.',
    //             'data' => $feeException,
    //             'errors' => null
    //         ]);
    //     } catch (Exception $e) {
    //         Log::error('Fee exception store error: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An unexpected error occurred',
    //             'data' => null,
    //             'errors' => ['exception' => [$e->getMessage()]]
    //         ], 500);
    //     }
    // }





    /**
     * Stores a new fee exception for a guest.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'guest_id'       => 'required|exists:guests,id',
            'created_by'     => 'required|integer',
            'hostel_fee'     => 'required|numeric|min:0',
            'caution_money'  => 'required|numeric|min:0',
            'facility'       => 'nullable|string|max:255',
            'remarks'        => 'nullable|string',
            'approved_by'    => 'nullable|string|max:255',
            'months'         => 'nullable|integer|min:0',
            'days'           => 'nullable|integer|min:0',
            'document'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'start_date'     => 'nullable|date', // Added validation for start_date
            'end_date'       => 'nullable|date|after_or_equal:start_date', // Added validation for end_date
        ]);

        // Step 2: Return validation errors if any
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 3: Get validated input
        $validated = $validator->validated();

        try {
            // Step 4: Fetch guest and check conditions
            $guest = Guest::findOrFail($validated['guest_id']);

            if ($guest->fee_waiver === 0) {
                return $this->apiResponse(false, 'Guest did not apply for waiver.', null, 400);
            } elseif ($guest->status === 'paid') {
                return $this->apiResponse(false, 'Guest has already paid.', null, 400);
            }

            // Step 5: Prepare data for FeeException
            $data = [
                'guest_id'       => $validated['guest_id'],
                'created_by'     => $validated['created_by'],
                'hostel_fee'     => $validated['hostel_fee'],
                'caution_money'  => $validated['caution_money'],
                'total_amount'   => $validated['hostel_fee'] + $validated['caution_money'],
                'facility'       => $validated['facility'] ?? null,
                'remarks'        => $validated['remarks'] ?? null,
                'approved_by'    => $validated['approved_by'] ?? null,
                'start_date'     => $validated['start_date'] ?? null, // Add start_date to data for FeeException
                'end_date'       => $validated['end_date'] ?? null,   // Add end_date to data for FeeException
            ];

            // Step 6: Handle document upload
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('fee_documents', $filename, 'public');
                $data['document_path'] = 'storage/' . $path;
            }

            // Step 7: Create or update fee exception
            $feeException = FeeException::updateOrCreate(
                ['guest_id' => $data['guest_id']],
                $data
            );

            // Step 8: Update guest status and potentially months/days for *guest's overall stay*
            $guest->status = 'waiver_approved';
            if (isset($validated['months'])) {
                $guest->months = $validated['months'];
            }
            if (isset($validated['days'])) {
                $guest->days = $validated['days'];
            }


            $guest->save();

            // Step 9: Return success response
            return response()->json([
                'success' => true,
                'message' => 'Fee exception saved successfully.',
                'data' => $feeException,
                'errors' => null
            ]);
        } catch (Exception $e) {
            Log::error('Fee exception store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    // public function adminWaiverApproved(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'guest_id' => 'required|exists:guests,id'
    //         ]);

    //         $guest = Guest::findOrFail($validatedData['guest_id']);

    //         if ($guest->status === 'waiver_approved') {
    //             return $this->apiResponse(false, 'Waiver already approved.', null, 400);
    //         } elseif ($guest->fee_waiver === 0) {
    //             return $this->apiResponse(false, 'Guest not applyed for waiver', null, 400);
    //         } elseif ($guest->status === 'paid') {
    //             return $this->apiResponse(false, 'Guest is already paid', null, 400);
    //         }

    //         $guest->save();

    //         return $this->apiResponse(true, 'Guest waiver approved successfully.', [
    //             'guest' => [
    //                 'id' => $guest->id,
    //                 'status' => $guest->status,
    //             ]
    //         ]);
    //     } catch (ValidationException $e) {
    //         return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
    //     } catch (Exception $e) {
    //         return $this->apiResponse(false, 'An error occurred while approving guest.', null, 500, ['error' => $e->getMessage()]);
    //     }
    // }



    public function updateGuestStatusWithRemark(Request $request) //for accountant
    {
        // 1. Validate the incoming request
        $validator = Validator::make($request->all(), [
            'guest_id'       => 'required|exists:guests,id',
            'status'         => 'nullable|string|max:255',
            'account_remark' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(
                false,
                'Validation failed',
                null,
                422,
                $validator->errors()
            );
        }

        $validated = $validator->validated();

        try {
            // 2. Find the guest
            $guest = Guest::findOrFail($validated['guest_id']);

            // 3. Update guest status if provided
            if (array_key_exists('status', $validated)) {
                $guest->status = $validated['status'];
                $guest->save();
            }

            // 4. Find or create the FeeException
            $feeException = FeeException::firstOrCreate(['guest_id' => $validated['guest_id']]);

            // 5. Update account remark if provided
            if (array_key_exists('account_remark', $validated)) {
                $feeException->account_remark = $validated['account_remark'];
                $feeException->save();
            }

            // 6. Return success response
            return $this->apiResponse(
                true,
                "Guest status" .
                    (isset($validated['status']) ? " updated to '{$validated['status']}'" : "") .
                    " and remark saved successfully.",
                [
                    'guest' => $guest->fresh(),
                    'fee_exception' => $feeException->fresh(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error updating guest status: ' . $e->getMessage());

            return $this->apiResponse(
                false,
                'An unexpected error occurred while updating guest status.',
                null,
                500,
                ['exception' => [$e->getMessage()]]
            );
        }
    }



    public function showGuestManagement() //for accountant
    {
        return view('accountant.guest_management');
    }


    public function showFeeException(Guest $guest)
    {
        try {
            // Eager load the feeException relationship
            $feeException = $guest->feeException;

            if (!$feeException) {
                return response()->json([
                    'success' => false,
                    'message' => 'No fee exception found for this guest.',
                    'data' => null,
                    'errors' => [
                        'fee_exception' => 'Fee exception record not available for the specified guest.'
                    ]
                ], 404);
            }

            // Prepare the data with a public URL for the document if available
            $data = [
                'id'             => $feeException->id,
                'guest_id'       => $feeException->guest_id,
                'hostel_fee'     => $feeException->hostel_fee,
                'caution_money'  => $feeException->caution_money,
                'total_amount'   => $feeException->total_amount,
                'facility'       => $feeException->facility,
                'approved_by'    => $feeException->approved_by,
                'remarks'        => $feeException->remarks,
                'created_by'     => $feeException->created_by,
                'account_remark' => $feeException->account_remark,
                'created_at'     => $feeException->created_at,
                'updated_at'     => $feeException->updated_at,
                'document_path'  => $feeException->document_path,
                'document_url'   => $feeException->document_path
                    ? Storage::url(ltrim(str_replace('storage/', '', $feeException->document_path), '/'))
                    : null,
                'start_date'     => $feeException->start_date, // Added start_date
                'end_date'       => $feeException->end_date,   // Added end_date
            ];

            return response()->json([
                'success' => true,
                'message' => 'Fee exception details fetched successfully.',
                'data'    => $data,
                'errors'  => null
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Error fetching fee exception for guest {$guest->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch fee exception details.',
                'data'    => null,
                'errors'  => [
                    'server_error' => $e->getMessage()
                ]
            ], 500);
        }
    }


    public function getFeeExceptionDetailsForEdit(Guest $guest)
    {
        Log::info('getFeeexp');
        try {
            // Retrieve the fee exception record associated with the guest.
            // Using latest() and first() is good for getting the most recent.
            $feeException = $guest->feeException()->latest()->first();

            // Prepare the data to send.
            // Fields from fee_exception will be null if no exception record exists.
            $data = [
                'hostel_fee'     => $feeException->hostel_fee ?? null,
                'caution_money'  => $feeException->caution_money ?? null,
                'total_amount'   => $feeException->total_amount ?? null,
                'facility'       => $feeException->facility ?? null,
                'approved_by'    => $feeException->approved_by ?? null,
                'remarks'        => $feeException->remarks ?? null,
                'account_remark' => $feeException->account_remark ?? null,
                'document_path'  => $feeException->document_path ?? null,
                'document_url'   => ($feeException && $feeException->document_path) ? Storage::url($feeException->document_path) : null,
                'start_date'     => $feeException->start_date ?? $guest->start_date ?? null, // Prefer feeException's date, then guest's
                'end_date'       => $feeException->end_date ?? $guest->end_date ?? null,     // Prefer feeException's date, then guest's
            ];

            // Fetch months and days from the Guest model, as they are directly on the guest record.
            $data['months'] = $guest->months ?? null;
            $data['days'] = $guest->days ?? null;

            // If no fee exception details are found, we still return the guest's months/days
            // and indicate that no fee exception record exists.
            $message = 'Fee exception details fetched successfully.';
            $statusCode = 200;
            $success = true;

            if (!$feeException) {
                $message = 'No fee exception record found for this guest. Default values are shown.';
            }

            return $this->apiResponse(
                $success,
                $message,
                $data,
                $statusCode
            );
        } catch (\Exception $e) {
            Log::error("Error fetching fee exception details for guest {$guest->id}: " . $e->getMessage());
            return $this->apiResponse(
                false,
                'Failed to fetch fee exception details due to a server error.',
                null,
                500, // Internal Server Error
                ['exception' => $e->getMessage()]
            );
        }
    }

    public function waiverRejected(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'guest_id' => 'required|exists:guests,id',
                'admin_remarks' => 'nullable|string|max:1000', // Make it nullable if not always required, otherwise 'required'
            ]);

            $guest = Guest::findOrFail($validatedData['guest_id']);

            if ($guest->status === 'waiver_rejected') {
                return $this->apiResponse(false, 'Payment request already rejected.', null, 400);
            }

            $guest->status = 'waiver_rejected';
            $guest->admin_remarks = $validatedData['admin_remarks'] ?? null; // Store the remarks
            $guest->save();

            return $this->apiResponse(true, 'Guest payment request rejected successfully.', [
                'guest' => [
                    'id' => $guest->id,
                    'status' => $guest->status,
                    'admin_remarks' => $guest->admin_remarks, // Include in response
                ]
            ]);
        } catch (ValidationException $e) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $e->errors());
        } catch (Exception $e) {
            // Log the exception for debugging purposes
            \Log::error('Error rejecting guest payment request: ' . $e->getMessage(), [
                'guest_id' => $request->input('guest_id'),
                'exception' => $e
            ]);
            return $this->apiResponse(false, 'An error occurred while rejecting guest payment request.', null, 500, ['error' => $e->getMessage()]);
        }
    }
}

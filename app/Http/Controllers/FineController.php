<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Fee;
use App\Models\Mess;
use App\Models\Payment;
use App\Models\FeeHead;
use App\Models\Resident;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;



class FineController extends Controller
{


    // public function adminSetFine(Request $request)
    // {
    //     try {
    //         // Base validation for admin side
    //         $rules = [
    //             'resident_id' => 'required|exists:residents,id',
    //             'fee_head_id' => 'required|exists:fees,fee_head_id', // Make sure 'fees' has 'fee_head_id' column or adjust
    //             'subscription_type' => 'required|string|in:Other', // Updated types
    //             'duration' => 'nullable|string|in:1 Month,3 Months,6 Months,1 Year', // For standard types
    //             // Admin does NOT set custom_amount directly here.
    //             'remarks' => 'nullable|string', // Remarks for general notes or for 'Other' type
    //             'created_by' => 'nullable|exists:users,id', // Assuming 'users' table for created_by
    //         ];

    //         // Conditional validation based on subscription_type
    //         if ($request->subscription_type === 'Other') {
    //             $rules['remarks'] = 'required|string'; // Remarks mandatory for 'Other'
    //             $rules['duration'] = 'nullable'; // Duration is not applicable for 'Other'
    //         } else {
    //             $rules['duration'] = 'required|string|in:1 Month,3 Months,6 Months,1 Year'; // Duration mandatory for standard types
    //         }

    //         $request->validate($rules);

    //         $resident = Resident::findOrFail($request->resident_id);

    //         // Fetch the active fee details
    //         $fee = Fee::where('fee_head_id', $request->fee_head_id)->where('is_active', true)->first();

    //         if (!$fee) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Active fee not found for the given fee_head_id.',
    //                 'data' => null,
    //                 'errors' => ['fee_head_id' => ['Active fee not found.']],
    //             ], 404);
    //         }

    //         $startDate = null;
    //         $endDate = null;
    //         $pricePerUnit = $fee->amount; // This is the base amount per month/period
    //         $calculatedTotalAmount = 0;

    //         if ($request->subscription_type === 'Other') {
    //             // For 'Other', admin sets a 'placeholder' total amount as 0, accountant will fill it
    //             $calculatedTotalAmount = 0;
    //             $startDate = null; // Or Carbon::now() if 'Other' can still have a start date
    //             $endDate = null;   // And a corresponding end date
    //         } else {
    //             $months = match ($request->duration) {
    //                 '1 Month' => 1,
    //                 '3 Months' => 3,
    //                 '6 Months' => 6,
    //                 '1 Year' => 12,
    //                 default => throw ValidationException::withMessages(['duration' => 'Invalid duration.']),
    //             };

    //             $startDate = Carbon::now();
    //             $endDate = $startDate->copy()->addMonths($months);
    //             $calculatedTotalAmount = $pricePerUnit * $months;
    //         }

    //         // Create subscription (no payment entry from admin side)
    //         $subscription = Subscription::create([
    //             'resident_id' => $resident->id,
    //             'fee_head_id' => $fee->fee_head_id,
    //             'subscription_type' => $request->subscription_type,
    //             'price' => $pricePerUnit, // This is the base fee amount (e.g., monthly fee)
    //             'total_amount' => $calculatedTotalAmount, // Calculated based on duration or 0 for 'Other'
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'status' => 'Pending', // Initial status is pending payment
    //             'remarks' => $request->remarks,
    //             'created_by' => $request->created_by ?? auth()->id(), // Use authenticated user if not provided
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription created successfully. Awaiting accountant review and payment.',
    //             'data' => ['subscription_id' => $subscription->id],
    //             'errors' => null,
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error.',
    //             'data' => null,
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Resident or Fee not found.',
    //             'data' => null,
    //             'errors' => null,
    //         ], 404);
    //     } catch (Exception $e) {
    //         \Log::error("Error in adminSubscribeResident: " . $e->getMessage() . " - " . $e->getFile() . " on line " . $e->getLine());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error occurred during subscription creation.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()],
    //         ], 500);
    //     }
    // }


    public function adminSetFine(Request $request)
    {
        try {
            // Step 1: Validate incoming request
            $rules = [
                'resident_id' => 'required|exists:residents,id',
                'subscription_type' => 'required|string|in:Other',
                'duration' => 'nullable|string|in:1 Month,3 Months,6 Months,1 Year',
                'remarks' => 'nullable|string',
                'created_by' => 'nullable|exists:users,id',
            ];

            // For "Other" subscription type, remarks required and duration not needed
            if ($request->subscription_type === 'Other') {
                $rules['remarks'] = 'required|string';
                $rules['duration'] = 'nullable';
            } else {
                $rules['duration'] = 'required|string|in:1 Month,3 Months,6 Months,1 Year';
            }

            $request->validate($rules);

            // Step 2: Get resident
            $resident = Resident::findOrFail($request->resident_id);

            // Step 3: Auto-fetch fee by subscription type name (e.g., "Other")
            $fee = Fee::where('name', $request->subscription_type)
                ->where('is_active', true)
                ->first();

            if (!$fee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active fee not found for the subscription type: ' . $request->subscription_type,
                    'data' => null,
                    'errors' => ['fee_head_id' => ['Fee for this type is missing.']],
                ], 404);
            }

            $feeHeadId = $fee->fee_head_id;
            $pricePerUnit = $fee->amount;
            $calculatedTotalAmount = 0;
            $startDate = null;
            $endDate = null;

            // Step 4: Handle amount & duration based on subscription type
            if ($request->subscription_type === 'Other') {
                $calculatedTotalAmount = 0;
                $startDate = null;
                $endDate = null;
            } else {
                $months = match ($request->duration) {
                    '1 Month' => 1,
                    '3 Months' => 3,
                    '6 Months' => 6,
                    '1 Year' => 12,
                    default => throw ValidationException::withMessages(['duration' => 'Invalid duration.']),
                };

                $startDate = Carbon::now();
                $endDate = $startDate->copy()->addMonths($months);
                $calculatedTotalAmount = $pricePerUnit * $months;
            }

            // Step 5: Create subscription
            $subscription = Subscription::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $feeHeadId,
                'subscription_type' => $request->subscription_type,
                'price' => $pricePerUnit,
                'total_amount' => $calculatedTotalAmount,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Pending',
                'remarks' => $request->remarks,
                'created_by' => $request->created_by ?? auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fine assigned successfully. Awaiting accountant approval.',
                'data' => ['subscription_id' => $subscription->id],
                'errors' => null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resident not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (\Exception $e) {
            \Log::error("adminSetFine Error: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred during fine assignment.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }




    // public function accountantSetFineAmount(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'subscription_id' => 'required|exists:subscriptions,id',
    //             'amount_paid' => 'required|numeric|min:0',
    //             'payment_method' => 'required|string|in:Cash,UPI,Bank Transfer,Card,Other,Null',
    //             'payment_remarks' => 'nullable|string|max:1000',
    //             'created_by' => 'nullable|exists:users,id',
    //         ]);

    //         $subscription = Subscription::findOrFail($request->subscription_id);

    //         if ($subscription->payments()->exists()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'A payment record for this subscription already exists.',
    //                 'data' => null,
    //                 'errors' => ['subscription_id' => ['Subscription already has an initial payment.']],
    //             ], 409);
    //         }

    //         $totalAmountExpected = $subscription->total_amount;

    //         if ($subscription->subscription_type === 'Other' && $subscription->total_amount == 0) {
    //             $totalAmountExpected = $request->amount_paid;
    //             $subscription->total_amount = $totalAmountExpected;
    //             $subscription->price = $request->amount_paid;
    //             $subscription->save();
    //         }

    //         $paymentAmountInRecord = 0;
    //         $remainingAmountForPaymentRecord = $totalAmountExpected;
    //         $paymentStatusForPaymentRecord = 'Pending';
    //         $transactionIdForPaymentRecord = null;

    //         $subscriptionRemainingBalance = $totalAmountExpected - $request->amount_paid;
    //         $subscription->status = $subscriptionRemainingBalance <= 0 ? 'Active' : 'Partially Paid';
    //         $subscription->save();

    //         $dueDate = ($subscriptionRemainingBalance > 0 && $subscription->start_date)
    //             ? Carbon::now()->addDays(7)
    //             : null;

    //         $payment = Payment::create([
    //             'resident_id' => $subscription->resident_id,
    //             'fee_head_id' => $subscription->fee_head_id,
    //             'subscription_id' => $subscription->id,
    //             'total_amount' => $totalAmountExpected,
    //             'amount' => $paymentAmountInRecord,
    //             'remaining_amount' => $remainingAmountForPaymentRecord,
    //             'transaction_id' => $transactionIdForPaymentRecord,
    //             'payment_method' => $request->payment_method,
    //             'payment_status' => $paymentStatusForPaymentRecord,
    //             'due_date' => $dueDate,
    //             'payment_date' => Carbon::now(),
    //             'created_by' => $request->created_by ?? auth()->id(),
    //             'remarks' => $request->payment_remarks,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Payment record created and subscription updated successfully.',
    //             'data' => ['payment_id' => $payment->id, 'subscription_id' => $subscription->id],
    //             'errors' => null,
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         \Log::error("Validation Error: " . json_encode($e->errors()));
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error.',
    //             'data' => null,
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (ModelNotFoundException $e) {
    //         \Log::error("Model Not Found: " . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Subscription not found.',
    //             'data' => null,
    //             'errors' => null,
    //         ], 404);
    //     } catch (Exception $e) {
    //         \Log::error("Server Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error occurred during payment processing.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()],
    //         ], 500);
    //     }
    // }




    public function accountantSetFineAmount(Request $request)
    {
        try {
            $request->validate([
                'subscription_id' => 'required|exists:subscriptions,id',
                'amount_paid' => 'required|numeric|min:0',
                'payment_method' => 'required|string|in:Cash,UPI,Bank Transfer,Card,Other,Null',
                'payment_remarks' => 'nullable|string|max:1000',
                'created_by' => 'nullable|exists:users,id',
            ]);

            $subscription = Subscription::findOrFail($request->subscription_id);

            if ($subscription->payments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A payment record for this subscription already exists.',
                    'data' => null,
                    'errors' => ['subscription_id' => ['Subscription already has an initial payment.']],
                ], 409);
            }

            $totalAmountExpected = $subscription->total_amount;

            // Set price and total amount if subscription type is 'Other' and total is 0
            if ($subscription->subscription_type === 'Other' && $subscription->total_amount == 0) {
                $totalAmountExpected = $request->amount_paid;
                $subscription->total_amount = $totalAmountExpected;
                $subscription->price = $request->amount_paid;
            }

            // ✅ Always set status to 'Pending'
            $subscription->status = 'Pending';
            $subscription->save();

            // Set due date only if there's remaining amount
            $dueDate = ($subscription->total_amount > $request->amount_paid && $subscription->start_date)
                ? Carbon::now()->addDays(7)
                : null;

            $payment = Payment::create([
                'resident_id' => $subscription->resident_id,
                'fee_head_id' => $subscription->fee_head_id,
                'subscription_id' => $subscription->id,
                'total_amount' => $totalAmountExpected,
                'amount' => 0,
                'remaining_amount' => $totalAmountExpected,
                'transaction_id' => null,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Pending',
                'due_date' => $dueDate,
                'payment_date' => Carbon::now(),
                'created_by' => $request->created_by ?? auth()->id(),
                'remarks' => $request->payment_remarks,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment record created and subscription updated successfully.',
                'data' => [
                    'payment_id' => $payment->id,
                    'subscription_id' => $subscription->id,
                ],
                'errors' => null,
            ], 201);
        } catch (ValidationException $e) {
            \Log::error("Validation Error: " . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            \Log::error("Model Not Found: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found.',
                'data' => null,
                'errors' => null,
            ], 404);
        } catch (Exception $e) {
            \Log::error("Server Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred during payment processing.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }




    public function viewAllFineDetails()
    {
        try {
            // Fetch all subscriptions where subscription_type is 'Other' (i.e., Fines)
            $fines = Subscription::with(['resident', 'feeHead', 'payments', 'createdBy'])
                ->where('subscription_type', 'Other')
                ->where('status', 'Pending')
                ->where('total_amount', 0)
                ->orderByDesc('created_at')
                ->get();

            $data = [];

            foreach ($fines as $subscription) {
                $fineData = [
                    'subscription_id' => $subscription->id,
                    'resident_name' => $subscription->resident->name ?? 'N/A',
                    'resident_scholar_number' => $subscription->resident->scholar_number ?? 'N/A',
                    'fee_head_name' => $subscription->feeHead->name ?? 'N/A',
                    'subscription_type' => $subscription->subscription_type,
                    'base_fee_per_unit' => $subscription->price,
                    'calculated_total_amount_by_admin' => $subscription->total_amount,
                    'start_date' => $subscription->start_date ? $subscription->start_date->toDateString() : null,
                    'end_date' => $subscription->end_date ? $subscription->end_date->toDateString() : null,
                    'subscription_status' => $subscription->status,
                    'admin_remarks' => $subscription->remarks,
                    'created_by_admin' => $subscription->createdBy->name ?? 'Admin',
                    'created_at' => $subscription->created_at->toDateTimeString(),
                    'payment_details' => [],
                ];

                if ($subscription->payments->isNotEmpty()) {
                    foreach ($subscription->payments as $payment) {
                        $fineData['payment_details'][] = [
                            'payment_id' => $payment->id,
                            'total_amount_expected' => $payment->total_amount,
                            'amount_paid_this_transaction' => $payment->amount,
                            'remaining_amount' => $payment->remaining_amount,
                            'payment_method' => $payment->payment_method,
                            'payment_status' => $payment->payment_status,
                            'transaction_id' => $payment->transaction_id,
                            'due_date' => $payment->due_date ? $payment->due_date->toDateString() : null,
                            'payment_date' => $payment->payment_date ? $payment->payment_date->toDateTimeString() : null,
                            'accountant_remarks' => $payment->remarks,
                            'processed_by' => $payment->createdBy->name ?? 'Accountant',
                            'paid_at' => $payment->created_at->toDateTimeString(),
                        ];
                    }
                } else {
                    $fineData['payment_details'][] = [
                        'message' => 'No payment has been processed for this fine yet.',
                        'payment_status' => 'Pending',
                    ];
                }

                $data[] = $fineData;
            }

            return response()->json([
                'success' => true,
                'message' => 'All fine (Other type) subscriptions fetched successfully.',
                'data' => $data,
                'errors' => null,
            ], 200);
        } catch (Exception $e) {
            \Log::error("Error in viewAllFineDetails: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred while fetching fine details.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }



    public function showFineAssignmentForm()
    {
        return view('admin.fine');
    }
}

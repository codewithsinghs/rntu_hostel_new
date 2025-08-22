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
use App\Helpers\Helper;



class SubscriptionController extends Controller
{

    // public function subscribeToService(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'resident_id' => 'required|exists:residents,id',
    //             'fee_head_id' => 'required|exists:fee_heads,id',
    //             'subscription_type' => 'required|string',
    //             'duration' => 'required|in:1 Month,3 Months,6 Months,1 Year',
    //         ]);

    //         $resident = Resident::findOrFail($request->resident_id);
    //         $fee = Fee::where('fee_head_id', $request->fee_head_id)->firstOrFail();

    //         $months = match ($request->duration) {
    //             '1 Month' => 1,
    //             '3 Months' => 3,
    //             '6 Months' => 6,
    //             '1 Year' => 12,
    //             default => throw ValidationException::withMessages(['duration' => 'Invalid duration selected.']),
    //         };

    //         $startDate = now();
    //         $endDate = $startDate->copy()->addMonths($months);
    //         $price = $fee->amount;
    //         $totalAmount = $price * $months;

    //         $subscription = Subscription::create([
    //             'resident_id' => $resident->id,
    //             'fee_head_id' => $request->fee_head_id,
    //             'subscription_type' => $request->subscription_type,
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'price' => $price,
    //             'total_amount' => $totalAmount,
    //             'status' => 'Pending',
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription created successfully.',
    //             'data' => $subscription,
    //             'errors' => null,
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'data' => null,
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server Error.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()],
    //         ], 500);
    //     }
    // } for single subscription



    public function subscribeToService(Request $request) // combined
    {
        try {
            $request->validate([
                'resident_id' => 'required|exists:residents,id',
                'subscription_type' => 'required|string|in:combined',
                'duration' => 'required|in:1 Month,3 Months,6 Months,1 Year',
                'food_preference' => 'required|in:veg,non_veg'
            ]);

            $resident = Resident::findOrFail($request->resident_id);

            $months = match ($request->duration) {
                '1 Month' => 1,
                '3 Months' => 3,
                '6 Months' => 6,
                '1 Year' => 12,
                default => throw ValidationException::withMessages(['duration' => 'Invalid duration selected.']),
            };

            $startDate = now();
            $endDate = $startDate->copy()->addMonths($months);

            $hostelFeeHead = FeeHead::where('name', 'Hostel Fee')->first();
            $messFeeHead = FeeHead::where('name', 'Mess Fee')->first();

            if (!$hostelFeeHead || !$messFeeHead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required fee types not configured.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $hostelFee = Fee::where('fee_head_id', $hostelFeeHead->id)->where('is_active', true)->first();
            $messFee = Fee::where('fee_head_id', $messFeeHead->id)->where('is_active', true)->first();

            if (!$hostelFee || !$messFee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active fees not found for hostel or mess.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $hostelTotal = $hostelFee->amount * $months;
            $messTotal = $messFee->amount * $months;

            $subscriptions = [];

            $hostelSub = Subscription::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $hostelFee->fee_head_id,
                'subscription_type' => 'Hostel Fee',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $hostelFee->amount,
                'total_amount' => $hostelTotal,
                'status' => 'Pending',
            ]);

            Payment::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $hostelFee->fee_head_id,
                'subscription_id' => $hostelSub->id,
                'total_amount' => $hostelTotal,
                'amount' => 0,
                'remaining_amount' => $hostelTotal,
                'transaction_id' => null,
                'payment_method' => 'Null',
                'payment_status' => 'Pending',
            ]);

            $subscriptions[] = $hostelSub;

            $messSub = Subscription::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $messFee->fee_head_id,
                'subscription_type' => 'Mess Fee',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'price' => $messFee->amount,
                'total_amount' => $messTotal,
                'status' => 'Pending',
            ]);

            Payment::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $messFee->fee_head_id,
                'subscription_id' => $messSub->id,
                'total_amount' => $messTotal,
                'amount' => 0,
                'remaining_amount' => $messTotal,
                'transaction_id' => null,
                'payment_method' => 'Null',
                'payment_status' => 'Pending',
            ]);

            Mess::create([
                'resident_id' => $resident->id,
                'user_id' => $resident->user_id ?? null,
                'building_id' => $resident->building_id ?? null,
                'university_id' => $resident->university_id ?? null,
                'created_by' => auth()->id(),
                'food_preference' => $request->food_preference,
                'from_date' => $startDate->toDateString(),
                'to_date' => $endDate->toDateString(),
                'due_date' => null,
            ]);

            $subscriptions[] = $messSub;

            return response()->json([
                'success' => true,
                'message' => 'Combined hostel and mess subscription created.',
                'data' => $subscriptions,
                'errors' => null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }


    // public function adminSubscribeResident(Request $request)
    // {
    //     try {
    //         // Base validation
    //         $rules = [
    //             'resident_id' => 'required|exists:residents,id',
    //             'fee_head_id' => 'required|exists:fees,fee_head_id',
    //             'subscription_type' => 'required|string',
    //             'duration' => 'nullable|in:1 Month,3 Months,6 Months,1 Year', // Nullable for 'Other'
    //             'custom_amount' => 'nullable|numeric|min:0',
    //             'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other,Null',
    //             'created_by' => 'nullable',
    //         ];

    //         // Add conditional validation: remarks required only if subscription_type is Other
    //         if ($request->subscription_type === 'Other') {
    //             $rules['remarks'] = 'required|string';
    //             $rules['custom_amount'] = 'required|numeric|min:0';
    //         } else {
    //             $rules['remarks'] = 'nullable|string';
    //             $rules['duration'] = 'required|in:1 Month,3 Months,6 Months,1 Year';
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
    //                 'errors' => null,
    //             ], 404);
    //         }

    //         $startDate = null;
    //         $endDate = null;
    //         $price = 0;
    //         $totalAmount = 0;

    //         if ($request->subscription_type === 'Other') {
    //             $price = $request->custom_amount;
    //             $totalAmount = $request->custom_amount;
    //             $startDate = null;
    //             $endDate = null;
    //         } else {
    //             $months = match ($request->duration) {
    //                 '1 Month' => 1,
    //                 '3 Months' => 3,
    //                 '6 Months' => 6,
    //                 '1 Year' => 12,
    //                 default => throw ValidationException::withMessages(['duration' => 'Invalid duration.']),
    //             };

    //             $startDate = now();
    //             $endDate = $startDate->copy()->addMonths($months);
    //             $price = $fee->amount;
    //             $totalAmount = $price * $months;
    //         }

    //         // Create subscription with remarks
    //         $subscription = Subscription::create([
    //             'resident_id' => $resident->id,
    //             'fee_head_id' => $fee->fee_head_id,
    //             'subscription_type' => $request->subscription_type,
    //             'price' => $price,
    //             'total_amount' => $totalAmount,
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'status' => 'Pending',
    //             'remarks' => $request->remarks,
    //         ]);

    //         // Create payment entry without remarks
    //         Payment::create([
    //             'resident_id' => $resident->id,
    //             'fee_head_id' => $fee->fee_head_id,
    //             'subscription_id' => $subscription->id,
    //             'total_amount' => $totalAmount,
    //             'amount' => 0,
    //             'remaining_amount' => $totalAmount,
    //             'transaction_id' => null,
    //             'payment_method' => $request->payment_method,
    //             'payment_status' => 'Pending',
    //             'due_date' => ($startDate !== null) ? now()->addDays(7) : null,
    //             'created_by' => $request->created_by,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription and payment entry created successfully.',
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
    //             'message' => 'Resident not found.',
    //             'data' => null,
    //             'errors' => null,
    //         ], 404);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()],
    //         ], 500);
    //     }
    // }





    // public function adminSubscribeResident(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'resident_id' => 'required|exists:residents,id',
    //             'fee_head_id' => 'required|exists:fees,fee_head_id',
    //             'subscription_type' => 'required|string',
    //             'duration' => 'required_if:payment_method,!Other|in:1 Month,3 Months,6 Months,1 Year',
    //             'remarks' => 'nullable|string',
    //             'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other,Null',
    //             'created_by' => 'nullable',
    //             'custom_amount' => 'required_if:payment_method,Other|nullable|numeric|min:1',
    //         ]);

    //         $resident = Resident::findOrFail($request->resident_id);

    //         $fee = Fee::where('fee_head_id', $request->fee_head_id)
    //             ->where('is_active', true)
    //             ->first();

    //         if (!$fee) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Active fee not found for the given fee_head_id.',
    //                 'data' => null,
    //                 'errors' => null,
    //             ], 404);
    //         }

    //         $startDate = now();
    //         $endDate = null;
    //         $months = 0;
    //         $price = 0;
    //         $totalAmount = 0;

    //         if ($request->payment_method === 'Other') {
    //             // Manual amount with no duration
    //             $price = $request->custom_amount;
    //             $totalAmount = $price;
    //         } else {
    //             // Use fee amount * duration
    //             $months = match ($request->duration) {
    //                 '1 Month' => 1,
    //                 '3 Months' => 3,
    //                 '6 Months' => 6,
    //                 '1 Year' => 12,
    //                 default => throw ValidationException::withMessages(['duration' => 'Invalid duration.']),
    //             };

    //             $endDate = $startDate->copy()->addMonths($months);
    //             $price = $fee->amount;
    //             $totalAmount = $price * $months;
    //         }

    //         $subscription = Subscription::create([
    //             'resident_id' => $resident->id,
    //             'fee_head_id' => $fee->fee_head_id,
    //             'subscription_type' => $request->subscription_type,
    //             'price' => $price,
    //             'total_amount' => $totalAmount,
    //             'start_date' => $startDate,
    //             'end_date' => $endDate,
    //             'status' => 'Pending',
    //         ]);

    //         Payment::create([
    //             'resident_id' => $resident->id,
    //             'fee_head_id' => $fee->fee_head_id,
    //             'subscription_id' => $subscription->id,
    //             'total_amount' => $totalAmount,
    //             'amount' => 0,
    //             'remaining_amount' => $totalAmount,
    //             'transaction_id' => null,
    //             'payment_method' => $request->payment_method,
    //             'payment_status' => 'Pending',
    //             'due_date' => now()->addDays(7),
    //             'remarks' => $request->remarks,
    //             'created_by' => $request->created_by,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Subscription and payment entry created successfully.',
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
    //             'message' => 'Resident not found.',
    //             'data' => null,
    //             'errors' => null,
    //         ], 404);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Server error.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()],
    //         ], 500);
    //     }
    // } old method where dont have fine functionality



    public function adminSubscribeResident(Request $request)
    {
        try {
            // Base validation
            $rules = [
                'resident_id' => 'required|exists:residents,id',
                'fee_head_id' => 'required|exists:fees,fee_head_id',
                'subscription_type' => 'required|string',
                'duration' => 'nullable|in:1 Month,3 Months,6 Months,1 Year', // Nullable for 'Other'
                'custom_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other,Null',
                'created_by' => 'nullable',
            ];

            // Add conditional validation: remarks required only if subscription_type is Other
            if ($request->subscription_type === 'Other') {
                $rules['remarks'] = 'required|string';
                $rules['custom_amount'] = 'required|numeric|min:0';
            } else {
                $rules['remarks'] = 'nullable|string';
                $rules['duration'] = 'required|in:1 Month,3 Months,6 Months,1 Year';
            }

            $request->validate($rules);

            $resident = Resident::findOrFail($request->resident_id);

            // Fetch the active fee details
            $fee = Fee::where('fee_head_id', $request->fee_head_id)->where('is_active', true)->first();

            if (!$fee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active fee not found for the given fee_head_id.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $startDate = null;
            $endDate = null;
            $price = 0;
            $totalAmount = 0;

            if ($request->subscription_type === 'Other') {
                $price = $request->custom_amount;
                $totalAmount = $request->custom_amount;
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

                $startDate = now();
                $endDate = $startDate->copy()->addMonths($months);
                $price = $fee->amount;
                $totalAmount = $price * $months;
            }

            // Create subscription with remarks
            $subscription = Subscription::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $fee->fee_head_id,
                'subscription_type' => $request->subscription_type,
                'price' => $price,
                'total_amount' => $totalAmount,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'Pending',
                'remarks' => $request->remarks,
            ]);

            // Create payment entry without remarks
            Payment::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $fee->fee_head_id,
                'subscription_id' => $subscription->id,
                'total_amount' => $totalAmount,
                'amount' => 0,
                'remaining_amount' => $totalAmount,
                'transaction_id' => null,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Pending',
                'due_date' => ($startDate !== null) ? now()->addDays(7) : null,
                'created_by' => $request->created_by,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription and payment entry created successfully.',
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()],
            ], 500);
        }
    }

    

    public function getSubscriptions()
    {
        try {
            $subscriptions = Subscription::with(['resident', 'fee'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Subscriptions retrieved successfully.',
                'data' => ['subscriptions' => $subscriptions],
                'errors' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscriptions.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }



    public function getResidentSubscriptions(Request $request)
    {
        $resident_id = Helper::get_resident_details($request->header('auth-id'))->id;
        try {
            $subscriptions = Subscription::where('resident_id', $resident_id)
                ->where('status', 'Active')
                ->with('fee')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Resident subscriptions retrieved successfully.',
                'data' => ['subscriptions' => $subscriptions],
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve resident subscriptions.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }



    public function getCombinedSubscription(Request $request)
    {
        try {
            $request->validate([
                'resident_id' => 'required|exists:residents,id'
            ]);

            $residentId = $request->resident_id;

            $subscriptions = Subscription::with('feeHead')
                ->where('resident_id', $residentId)
                ->whereIn('subscription_type', ['Hostel Fee', 'Mess Fee'])
                ->get();

            if ($subscriptions->count() < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Combined subscription not found.',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            $startDate = Carbon::parse($subscriptions->min('start_date'));
            $endDate = Carbon::parse($subscriptions->max('end_date'));
            $months = $startDate->diffInMonths($endDate);

            $status = $subscriptions->pluck('status')->contains('Pending') ? 'Pending' : 'Completed';

            return response()->json([
                'success' => true,
                'message' => 'Combined subscription retrieved successfully.',
                'data' => [
                    'resident_id' => $residentId,
                    'subscription_type' => 'Hostel + Mess',
                    'duration' => $months . ' Months',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'status' => $status,
                    'total_amount' => $subscriptions->sum('total_amount'),
                    'details' => $subscriptions->mapWithKeys(function ($sub) {
                        return [$sub->subscription_type => $sub->total_amount];
                    }),
                ],
                'errors' => null
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve combined subscription.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }



    public function getPendingResidentSubscriptions($resident_id)
    {
        try {
            $subscriptions = Subscription::where('resident_id', $resident_id)
                ->where('status', 'Pending')
                ->whereNotNull('id')
                ->with(['fee'])
                ->get();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending subscriptions found for this resident.',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            $formattedSubscriptions = $subscriptions->map(function ($subscription) {
                $latestPayment = $subscription->payments()->orderByDesc('id')->first();

                if (!$latestPayment || $latestPayment->remaining_amount <= 0) {
                    return null;
                }

                return [
                    'subscription_id' => $subscription->id,
                    'status' => $subscription->status,
                    'subscription_type' => $subscription->subscription_type,
                    'remarks' => $subscription->remarks,
                    'subscription_fee' => $subscription->fee,
                    'payments' => [
                        [
                            'payment_id' => $latestPayment->id,
                            'amount' => $latestPayment->amount,
                            'total_amount' => $latestPayment->total_amount,
                            'remaining_amount' => $latestPayment->remaining_amount,
                            'payment_method' => $latestPayment->payment_method,
                            'payment_status' => $latestPayment->payment_status,
                            'created_at' => $latestPayment->created_at
                        ]
                    ]
                ];
            })->filter()->values();

            if ($formattedSubscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending payments found for this resident.',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pending subscriptions retrieved successfully.',
                'data' => ['subscriptions' => $formattedSubscriptions],
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }
}

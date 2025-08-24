<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Exception;
use App\Models\User;
use App\Models\Fee;
use Carbon\Carbon;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Resident;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\Subscription;
use App\Models\StudentAccessory;
use App\Models\Mess;
use App\Models\GuestAccessory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{

    public function guestPayment(Request $request)
    {
        try {
            $request->validate([
                'guest_id' => 'required|exists:guests,id',
                'transaction_id' => 'nullable|unique:payments,transaction_id',
                'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other',
                'remarks' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $guest = Guest::findOrFail($request->guest_id);

            if ($guest->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Guest has already paid.',
                    'data' => null,
                    'errors' => ['status' => ['Guest already marked as paid']]
                ], 400);
            }

            $controller = app(GuestController::class);
            $apiResponse = $controller->getGuestTotalAmount($request, $guest->id);
            $feeData = $apiResponse->getData();

            if (!isset($feeData->data)) {
                throw new \Exception('Invalid response from getGuestTotalAmount. Data missing.');
            }

            $feeDetails = $feeData->data;

            $hostelFee = (float) $feeDetails->hostel_fee;
            $cautionMoney = (float) $feeDetails->caution_money;
            $accessoryAmount = (float) $feeDetails->total_accessory_amount;
            $totalAmount = (float) $feeDetails->final_total_amount;
            $months = $feeDetails->months ?? 3;

            $user = User::create([
                'name' => $guest->name,
                'gender' => $guest->gender,
                'email' => $guest->email,
                'password' => Hash::make('12345678'),
            ]);

            $residentRole = Role::where('name', 'resident')->firstOrFail();
            $user->roles()->attach($residentRole->id, ['model_type' => User::class]);

            $resident = Resident::create([
                'name' => $guest->name,
                'email' => $guest->email,
                'gender' => $guest->gender,
                'scholar_number' => $guest->scholar_number,
                'number' => $guest->number,
                'parent_no' => $guest->parent_no,
                'guardian_no' => $guest->guardian_no,
                'mothers_name' => $guest->mothers_name,
                'fathers_name' => $guest->fathers_name,
                'user_id' => $user->id,
                'guest_id' => $guest->id,
                'status' => 'pending',
                'created_by' => $user->id,
            ]);

            $guest->update(['status' => 'paid']);

            $fromDate = Carbon::now();
            $toDate = $fromDate->copy()->addMonths($months);
            $dueDate = $toDate->copy();

            $mess = Mess::create([
                'user_id' => $user->id,
                'resident_id' => $resident->id,
                'guest_id' => $guest->id,
                'building_id' => $guest->building_id ?? null,
                'university_id' => $guest->university_id ?? null,
                'created_by' => $user->id,
                'food_preference' => $guest->food_preference,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'due_date' => $dueDate,
            ]);

            Payment::create([
                'guest_id' => $guest->id,
                'resident_id' => $resident->id,
                'amount' => $hostelFee + $accessoryAmount,
                'total_amount' => $hostelFee + $accessoryAmount,
                'remaining_amount' => 0,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Completed',
                'due_date' => $dueDate,
                'created_by' => $user->id,
                'remarks' => $request->remarks,
            ]);

            Payment::create([
                'guest_id' => $guest->id,
                'resident_id' => $resident->id,
                'amount' => $cautionMoney,
                'total_amount' => $cautionMoney,
                'remaining_amount' => 0,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Completed',
                'due_date' => $dueDate,
                'created_by' => $user->id,
                'is_caution_money' => 1,
                'remarks' => 'Caution Money',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'resident' => $resident,
                    'mess' => $mess,
                    'paid_total' => $totalAmount
                ],
                'errors' => null
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found or related data missing',
                'data' => null,
                'errors' => ['model' => ['Resource not found']]
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment.',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    public function subscribePay(Request $request)
    {
        try {
            $request->validate([
                'transaction_id' => 'required|unique:payments,transaction_id',
                'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card',
                'subscription_id' => 'required|exists:subscriptions,id',
                'amount' => 'required|numeric|min:1'
            ]);

            DB::beginTransaction();

            $subscription = Subscription::findOrFail($request->subscription_id);

            if ($subscription->status === 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is already active.',
                    'data' => null,
                    'errors' => ['status' => ['Subscription already active']]
                ], 400);
            }

            $firstPayment = Payment::where('subscription_id', $subscription->id)->first();
            $totalAmount = $firstPayment ? $firstPayment->total_amount : $subscription->total_amount;
            $totalPaid = Payment::where('subscription_id', $subscription->id)->sum('amount');
            $remainingBalance = max($totalAmount - $totalPaid, 0);

            if ($request->amount > $remainingBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount exceeds the remaining balance.',
                    'data' => [
                        'total_amount' => $totalAmount,
                        'remaining_balance' => $remainingBalance
                    ],
                    'errors' => ['amount' => ['Exceeds balance']]
                ], 400);
            }

            $newRemainingAmount = max($remainingBalance - $request->amount, 0);
            $paymentStatus = $newRemainingAmount == 0 ? 'Completed' : 'Pending';

            Payment::create([
                'resident_id' => $subscription->resident_id,
                'fees_id' => $subscription->fee_id,
                'subscription_id' => $subscription->id,
                'total_amount' => $totalAmount,
                'amount' => $request->amount,
                'remaining_amount' => $newRemainingAmount,
                'transaction_id' => $request->transaction_id,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
            ]);

            if ($newRemainingAmount == 0) {
                $subscription->update(['status' => 'Active']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'payment_status' => $paymentStatus,
                    'subscription_status' => $subscription->status,
                    'remaining_balance' => $newRemainingAmount
                ],
                'errors' => null
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
                'data' => null,
                'errors' => ['subscription' => ['Not found']]
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment.',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    public function makePayment(Request $request)
    {
        try {
            $request->validate([
                'resident_id' => 'required|exists:residents,id',
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other',
                'payment_for' => 'required|in:subscription,accessory,fee',
                'subscription_id' => 'nullable|exists:subscriptions,id',
                'student_accessory_id' => 'nullable|exists:student_accessory,id',
                'fees_id' => 'nullable|exists:fees,id',
            ]);

            return DB::transaction(function () use ($request) {
                $residentId = $request->resident_id;
                $amountPaid = $request->amount;
                $paymentMethod = $request->payment_method;
                $paymentFor = $request->payment_for;
                $totalAmount = 0;
                $relatedId = null;

                if ($paymentFor === 'subscription' && $request->subscription_id) {
                    $subscription = Subscription::find($request->subscription_id);
                    $totalAmount = Fee::find($subscription->fee_id)->amount;
                    $relatedId = $subscription->id;
                } elseif ($paymentFor === 'accessory' && $request->student_accessory_id) {
                    $accessory = StudentAccessory::find($request->student_accessory_id);
                    $totalAmount = $accessory->price;
                    $relatedId = $accessory->id;
                } elseif ($paymentFor === 'fee' && $request->fees_id) {
                    $fee = Fee::find($request->fees_id);
                    $totalAmount = $fee->amount;
                    $relatedId = $fee->id;
                }

                $previousPayments = Payment::where('resident_id', $residentId)
                    ->where($paymentFor . '_id', $relatedId)
                    ->sum('amount');
                $remainingAmount = max($totalAmount - ($previousPayments + $amountPaid), 0);

                $payment = Payment::create([
                    'resident_id' => $residentId,
                    $paymentFor . '_id' => $relatedId,
                    'total_amount' => $totalAmount,
                    'amount' => $amountPaid,
                    'remaining_amount' => $remainingAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $remainingAmount > 0 ? 'Pending' : 'Completed',
                    'transaction_id' => $request->transaction_id,
                    'created_by' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'data' => $payment,
                    'errors' => null
                ], 201);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to make payment',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    public function accountSubscribePay(Request $request)
    {
        try {
            $request->validate([
                'resident_id' => 'required|exists:residents,id',
                'transaction_id' => 'required|unique:payments,transaction_id',
                'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card',
                'subscription_id' => 'required|exists:subscriptions,id',
                'amount' => 'required|numeric|min:1'
            ]);

            DB::beginTransaction();

            $subscription = Subscription::findOrFail($request->subscription_id);

            if ($subscription->resident_id != $request->resident_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resident ID does not match the subscription.',
                    'data' => null,
                    'errors' => ['resident_id' => ['Mismatch with subscription']]
                ], 400);
            }

            if ($subscription->status === 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription is already active.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            $firstPayment = Payment::where('subscription_id', $subscription->id)->first();
            $totalAmount = $firstPayment ? $firstPayment->total_amount : $subscription->total_amount;

            $totalPaid = Payment::where('subscription_id', $subscription->id)->sum('amount');
            $remainingBalance = max($totalAmount - $totalPaid, 0);

            if ($request->amount > $remainingBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount exceeds the remaining balance.',
                    'data' => [
                        'total_amount' => $totalAmount,
                        'remaining_balance' => $remainingBalance
                    ],
                    'errors' => null
                ], 400);
            }

            $newRemainingAmount = max($remainingBalance - $request->amount, 0);
            $paymentStatus = ($newRemainingAmount == 0) ? 'Completed' : 'Pending';

            Payment::create([
                'resident_id' => $request->resident_id,
                'fee_head_id' => $subscription->fee_head_id,
                'subscription_id' => $subscription->id,
                'total_amount' => $totalAmount,
                'amount' => $request->amount,
                'remaining_amount' => $newRemainingAmount,
                'transaction_id' => $request->transaction_id,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
            ]);

            if ($newRemainingAmount == 0) {
                $subscription->update(['status' => 'Active']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'payment_status' => $paymentStatus,
                    'subscription_status' => $subscription->status,
                    'remaining_balance' => $newRemainingAmount
                ],
                'errors' => null
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment.',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    public function payAsResident(Request $request)
    {
        try {
            $request->validate([
                'resident_id' => 'required|exists:residents,id',
                'fee_head_id' => 'required|exists:fees,fee_head_id',
                'transaction_id' => 'nullable|unique:payments,transaction_id',
                'amount' => 'required|numeric|min:1',
                'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other',
                'remarks' => 'nullable|string'
            ]);

            $resident = Resident::findOrFail($request->resident_id);
            $fee = Fee::where('fee_head_id', $request->fee_head_id)->firstOrFail();

            if ($request->amount < $fee->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid amount. Please pay the correct fee.',
                    'data' => null,
                    'errors' => null
                ], 400);
            }

            Payment::create([
                'resident_id' => $resident->id,
                'fee_head_id' => $fee->fee_head_id,
                'amount' => $request->amount,
                'transaction_id' => $request->transaction_id,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Completed',
                'created_by' => null,
                'remarks' => $request->remarks
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful.',
                'data' => null,
                'errors' => null
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fee or Resident not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    public function getPendingPayments($resident_id)
    {
        try {
            $validator = Validator::make(['resident_id' => $resident_id], [
                'resident_id' => 'required|exists:residents,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => null,
                    'errors' => $validator->errors()
                ], 422);
            }

            $pendingPayments = Payment::where('resident_id', $resident_id)
                ->where('remaining_amount', '>', 0)
                ->get();

            if ($pendingPayments->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No pending payments found.',
                    'data' => [],
                    'errors' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pending payments retrieved successfully.',
                'data' => $pendingPayments,
                'errors' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    // public function getAccessoryPendingPayments($resident_id)
    // {
    //     try {
    //         $validator = Validator::make(['resident_id' => $resident_id], [
    //             'resident_id' => 'required|exists:residents,id',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation failed',
    //                 'data' => null,
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $latestPaymentIds = Payment::where('resident_id', $resident_id)
    //             ->whereNotNull('student_accessory_id')
    //             ->select(DB::raw('MAX(id) as id'))
    //             ->groupBy('student_accessory_id')
    //             ->pluck('id');

    //         $latestPayments = Payment::with([
    //             'resident.user',
    //             'resident.guest',
    //             'studentAccessory.accessory'
    //         ])
    //             ->whereIn('id', $latestPaymentIds)
    //             ->where('remaining_amount', '>', 0)
    //             ->get();

    //         if ($latestPayments->isEmpty()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'No pending payments found.',
    //                 'data' => [],
    //                 'errors' => null
    //             ]);
    //         }

    //         $formattedPayments = $latestPayments->map(function ($payment) {
    //             return [
    //                 'payment_id' => $payment->id,
    //                 'amount' => $payment->amount,
    //                 'remaining_amount' => $payment->remaining_amount,
    //                 'student_accessory_id' => $payment->student_accessory_id,
    //                 'accessory_name' => $payment->studentAccessory->accessory->name ?? 'N/A',
    //                 'resident_name' => $payment->resident->user->name ?? 'N/A',
    //                 'scholar_number' => $payment->resident->guest->scholar_number ?? 'N/A',
    //             ];
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pending accessory payments retrieved successfully.',
    //             'data' => $formattedPayments,
    //             'errors' => null
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An unexpected error occurred.',
    //             'data' => null,
    //             'errors' => ['exception' => [$e->getMessage()]]
    //         ], 500);
    //     }
    // } not getting accessory name


    public function getAccessoryPendingPayments(Request $request)
    {
        try {
            $resident_id = Helper::get_resident_details($request->header('auth-id'))->id;
            $validator = Validator::make(['resident_id' => $resident_id ], [
                'resident_id' => 'required|exists:residents,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'data' => null,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Subquery to get the latest payment ID for each student_accessory_id
            $latestPaymentSubquery = Payment::select(DB::raw('MAX(id) as id'))
                ->where('resident_id', $resident_id)
                ->whereNotNull('student_accessory_id')
                ->groupBy('student_accessory_id');

            $formattedPayments = DB::table('payments as p')
                ->joinSub($latestPaymentSubquery, 'latest_payments', function ($join) {
                    $join->on('p.id', '=', 'latest_payments.id');
                })
                ->join('student_accessory as sa', 'p.student_accessory_id', '=', 'sa.id')
                ->join('accessory as a', 'sa.accessory_head_id', '=', 'a.id')
                ->join('accessory_heads as ah', 'a.accessory_head_id', '=', 'ah.id')
                ->join('residents as r', 'p.resident_id', '=', 'r.id')
                ->leftJoin('users as u', 'r.user_id', '=', 'u.id')
                ->leftJoin('guests as g', 'r.guest_id', '=', 'g.id')
                ->where('p.remaining_amount', '>', 0)
                ->select(
                    'p.id as payment_id',
                    'p.amount',
                    'p.remaining_amount',
                    'p.student_accessory_id',
                    'ah.name as accessory_name',
                    DB::raw('COALESCE(u.name, "N/A") as resident_name'),
                    DB::raw('COALESCE(g.scholar_number, "N/A") as scholar_number')
                )
                ->get();


            if ($formattedPayments->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No pending payments found.',
                    'data' => [],
                    'errors' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pending accessory payments retrieved successfully.',
                'data' => $formattedPayments,
                'errors' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]]
            ], 500);
        }
    }




    // public function getPendingPayments($resident_id)
    // {
    //     try {
    //         // Validate the resident_id manually
    //         $validator = Validator::make(['resident_id' => $resident_id], [
    //             'resident_id' => 'required|exists:residents,id',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'error' => 'Validation failed',
    //                 'messages' => $validator->errors()
    //             ], 422);
    //         }

    //         // Get the latest payment ID for this resident
    //         $latestPaymentId = Payment::where('resident_id', $resident_id)
    //             ->orderByDesc('id')
    //             ->value('id');

    //         if (!$latestPaymentId) {
    //             return response()->json([
    //                 'message' => 'No payments found for this resident.'
    //             ], 404);
    //         }

    //         // Fetch the latest pending payment with relationships
    //         $payment = Payment::with('resident.user')
    //             ->where('id', $latestPaymentId)
    //             ->where('remaining_amount', '>', 0)
    //             ->first();

    //         if (!$payment) {
    //             return response()->json([
    //                 'message' => 'No pending payments found for this resident.'
    //             ], 404);
    //         }

    //         // Format the response
    //         $response = [
    //             'payment_id'       => $payment->id,
    //             'resident_id'      => $payment->resident_id,
    //             'resident_name'    => optional($payment->resident->user)->name,
    //             'total_amount'     => $payment->total_amount,
    //             'amount_paid'      => $payment->amount,
    //             'remaining_amount' => $payment->remaining_amount,
    //             'payment_method'   => $payment->payment_method,
    //             'payment_status'   => $payment->payment_status,
    //             'due_date'         => $payment->due_date,
    //             'created_at'       => $payment->created_at->toDateTimeString(),
    //         ];

    //         return response()->json([
    //             'pending_payment' => $response
    //         ], 200);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'error' => 'Resident not found'
    //         ], 404);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'error' => 'An unexpected error occurred.',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }



    public function getAllPendingPayments()
    {
        try {
            $latestPayments = \App\Models\Payment::selectRaw('MAX(id) as latest_id')
                ->whereNotNull('resident_id')
                ->groupBy('resident_id')
                ->pluck('latest_id');

            $payments = \App\Models\Payment::with('resident.user')
                ->whereIn('id', $latestPayments)
                ->where('remaining_amount', '>', 0)
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending payments found for any resident.',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            $response = $payments->map(function ($payment) {
                return [
                    'payment_id'       => $payment->id,
                    'resident_id'      => $payment->resident_id,
                    'resident_name'    => optional($payment->resident->user)->name,
                    'subscription_id'  => $payment->subscription_id,
                    'total_amount'     => $payment->total_amount,
                    'amount_paid'      => $payment->amount,
                    'remaining_amount' => $payment->remaining_amount,
                    'payment_method'   => $payment->payment_method,
                    'payment_status'   => $payment->payment_status,
                    'due_date'         => $payment->due_date,
                    'created_at'       => $payment->created_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Latest pending payments fetched successfully.',
                'data'    => $response,
                'errors'  => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while fetching pending payments.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }



    public function getPaymentsByResident($id)
    {
        $validator = Validator::make(['resident_id' => $id], [
            'resident_id' => 'required|integer|exists:residents,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $payments = DB::table('payments')
                ->select(
                    'payments.transaction_id',
                    'payments.total_amount',
                    'payments.amount',
                    'payments.remaining_amount',
                    'payments.payment_method',
                    'payments.payment_status',
                    'payments.due_date',
                    'payments.remarks',
                    'payments.created_at',
                    'fees.name as fee_head_name',
                    'accessory_heads.name as accessory_name',
                    'subscriptions.subscription_type as subscription_name'
                )
                ->leftJoin('fees', 'payments.fee_head_id', '=', 'fees.id')
                ->leftJoin('subscriptions', 'payments.subscription_id', '=', 'subscriptions.id')
                // Joining sequence: payments -> student_accessory -> accessory -> accessory_heads
                ->leftJoin('student_accessory', 'payments.student_accessory_id', '=', 'student_accessory.id')
                ->leftJoin('accessory', 'student_accessory.accessory_head_id', '=', 'accessory.id')
                ->leftJoin('accessory_heads', 'accessory.accessory_head_id', '=', 'accessory_heads.id')
                ->where('payments.resident_id', $id)
                ->orderBy('payments.created_at', 'desc')
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payments found for this resident.',
                    'data' => null,
                    'errors' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully.',
                'data'    => $payments,
                'errors'  => null
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Error fetching payments with joins: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching payments.',
                'data'    => null,
                'errors'  => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function getAllPaymentsByResidentId($residentId)
    {
        try {
            $payments = Payment::with([
                'feeHead',
                'subscription',
                'studentAccessory.accessoryHead'
            ])
                ->where('resident_id', $residentId)
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payments found for this resident.',
                    'data'    => null,
                    'errors'  => null
                ], 404);
            }

            $formatted = $payments->map(function ($payment) {
                return [
                    'transaction_id'     => $payment->transaction_id,
                    'total_amount'       => $payment->total_amount,
                    'amount'             => $payment->amount,
                    'remaining_amount'   => $payment->remaining_amount,
                    'payment_method'     => $payment->payment_method,
                    'payment_status'     => $payment->payment_status,
                    'due_date'           => $payment->due_date,
                    'fee_head_name'      => optional($payment->feeHead)->name,
                    'accessory_name'     => optional(optional($payment->studentAccessory)->accessory)->name,
                    'subscription_name'  => optional($payment->subscription)->subscription_type,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Payments fetched successfully.',
                'data'    => $formatted,
                'errors'  => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching payments.',
                'data'    => null,
                'errors'  => ['exception' => $e->getMessage()]
            ], 500);
        }
    }




    public function getAllPayments()
    {
        try {
            $payments = Payment::with([
                'guest',
                'resident',
                'fees',
                'subscription',
                'studentAccessory',
                'createdBy'
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            if ($payments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payments found.',
                    'data'    => null,
                    'errors'  => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payments retrieved successfully.',
                'data'    => $payments,
                'errors'  => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching payments.',
                'data'    => null,
                'errors'  => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function showAccessoryPaymentForm(Request $request)
    {
        $residentId = $request->query('resident_id');
        $studentAccessoryId = $request->query('student_accessory_id');

        return view('accountant.accessory_pay', compact('residentId', 'studentAccessoryId'));
    }
}

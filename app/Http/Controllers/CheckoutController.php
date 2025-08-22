<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Bed;
use App\Models\Payment;
use App\Models\Checkout;
use App\Models\Resident;
use Illuminate\Http\Request;
use App\Models\GuestAccessory;
use App\Models\AccessoryCheckoutLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function requestCheckout(Request $request)
    {
        try {
            $user_id = $request->header('auth-id');
            $request["resident_id"]= Helper::get_resident_details($user_id)->id; // Resident ID from header
            
            // $formattedDate = Carbon::createFromFormat('d-m-Y', trim($request->date))->format('Y-m-d');
            // $request->merge(['date' => $formattedDate]); // merge updates request data

            $validator = Validator::make($request->all(), [
                'resident_id' => 'required|exists:residents,id',
                'date' => 'required|date_format:Y-m-d',
                'reason' => 'required|string',
            ]);
            if ($validator->fails()) {
                Log::info($validator->errors());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => null,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();


            $cautionAmount = Payment::where('resident_id', $validated['resident_id'])
                ->where('is_caution_money', true)
                ->sum('amount');

            $checkout = Checkout::create([
                'resident_id' => $validated['resident_id'],
                'date' => $validated['date'],
                'reason' => $validated['reason'],
                'deposited_amount' => $cautionAmount,
                'created_by' => $user_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout request submitted successfully.',
                'data' => $checkout,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit checkout request.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function getCheckoutStatus(Request $request)
    {
        try {
            $user_id = $request->header('auth-id');
            $resident_id= Helper::get_resident_details($user_id)->id; // Resident ID from header
            $checkout = Checkout::where('resident_id', $resident_id)->latest()->first();

            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'No checkout request found for this resident.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Checkout status fetched successfully.',
                'data' => [
                    'resident_id' => $checkout->resident_id,
                    'date' => $checkout->date,
                    'reason' => $checkout->reason,
                    'admin_approval' => $checkout->admin_approval,
                    'account_approval' => $checkout->account_approval,
                    'remarks' => $checkout->remarks,
                    'action' => $checkout->action,
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch checkout status.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAllCheckoutRequests()
    {
        try {
            $checkouts = Checkout::with('resident.user')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($checkouts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No checkout requests found.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $formattedCheckouts = $checkouts->map(function ($checkout) {
                return [
                    'checkout_id'      => $checkout->id,
                    'resident_id'      => $checkout->resident_id,
                    'resident_name'    => optional($checkout->resident->user)->name,
                    'checkout_date'    => $checkout->date,
                    'reason'           => $checkout->reason,
                    'admin_approval'   => $checkout->admin_approval,
                    'account_approval' => $checkout->account_approval,
                    'remarks'          => $checkout->remarks,
                    'action'           => $checkout->action,
                    'created_at'       => $checkout->created_at->toDateTimeString()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Checkout requests fetched successfully.',
                'data' => $formattedCheckouts,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch checkout requests.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function accountApproval(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approved,denied,pending',
                'remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $checkout = Checkout::findOrFail($id);

            if ($checkout->account_approval !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account has already reviewed this request.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            if ($checkout->action !== 'admin_checked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pending admin review for accessory verification.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            $checkout->update([
                'account_approval' => $request->status,
                'remark' => $request->remark,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account approval updated.',
                'data' => $checkout,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account approval.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function adminApproval(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:approved,denied,pending',
                'remark' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => null,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $checkout = Checkout::findOrFail($id);

            if ($checkout->account_approval !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve without account approval.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            if ($checkout->admin_approval !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin has already reviewed this request.',
                    'data' => null,
                    'errors' => null,
                ], 400);
            }

            $checkout->update([
                'admin_approval' => $request->status,
                'remark' => $request->remark,
                'action' => 'completed',
            ]);

            if ($request->status === 'approved') {
                $resident = Resident::findOrFail($checkout->resident_id);
                $resident->update(['status' => 'checkout']);

                if ($resident->bed_id) {
                    Bed::where('id', $resident->bed_id)->update(['status' => 'available']);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Admin approval updated.',
                'data' => $checkout,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin approval.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function getDefaultAccessoryByResidentId($residentId)
    {
        try {
            $resident = Resident::findOrFail($residentId);
            $guestId = $resident->guest_id;

            $defaultAccessories = GuestAccessory::with('accessoryHead')
                ->where('guest_id', $guestId)
                ->where('price', 0)
                ->get();

            $checkout = Checkout::where('resident_id', $residentId)
                ->latest()
                ->first();

            $depositedAmount = $checkout ? $checkout->deposited_amount : 0;

            if ($defaultAccessories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No default accessories found for this resident.',
                    'data' => ['deposited_amount' => $depositedAmount],
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Default accessories fetched successfully.',
                'data' => [
                    'deposited_amount' => $depositedAmount,
                    'accessories' => $defaultAccessories,
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch default accessories or deposited amount.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAccessoryByResidentId($residentId)
    {
        try {
            $resident = Resident::findOrFail($residentId);
            $guestId = $resident->id;

            $accessories = GuestAccessory::with('accessoryHead')
                ->where('guest_id', $guestId)
                ->get();

            $checkout = Checkout::where('resident_id', $residentId)
                ->latest()
                ->first();

            $depositedAmount = $checkout ? $checkout->deposited_amount : 0;

            if ($accessories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No accessories found for this resident.',
                    'data' => ['deposited_amount' => $depositedAmount],
                    'errors' => null,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Default accessories fetched successfully.',
                'data' => [
                    'deposited_amount' => $depositedAmount,
                    'accessories' => $accessories,
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch default accessories or deposited amount.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }



    public function adminAccessoryChecking(Request $request, $residentId)
    {
        $validator = Validator::make($request->all(), [
            'accessories' => 'required|array',
            'accessories.*.accessory_head_id' => 'required|exists:accessory,id',
            'accessories.*.is_returned' => 'required|boolean',
            'accessories.*.debit_amount' => 'nullable|numeric|min:0',
            'accessories.*.remark' => 'nullable|string'
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
            $resident = Resident::findOrFail($residentId);
            $guestId = $resident->guest_id;

            $checkout = Checkout::where('resident_id', $residentId)->latest()->first();
            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'No checkout record found.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $totalDebit = 0;
            foreach ($request->accessories as $accessory) {
                // Assuming you want to update something here (your original code misses ->update())
                GuestAccessory::where('guest_id', $guestId)
                    ->where('accessory_head_id', $accessory['accessory_head_id']);
                    // ->update(['is_returned' => $accessory['is_returned']]);

                DB::table('accessory_checkout_logs')->insert([
                    'checkout_id' => $checkout->id,
                    'accessory_head_id' => $accessory['accessory_head_id'],
                    'is_returned' => $accessory['is_returned'],
                    'debit_amount' => $accessory['debit_amount'] ?? 0,
                    'remark' => $accessory['remark'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $totalDebit += $accessory['debit_amount'] ?? 0;
            }

            $checkout->deposited_amount = max(0, $checkout->deposited_amount - $totalDebit);
            $checkout->action = 'admin_checked';
            $checkout->save();

            return response()->json([
                'success' => true,
                'message' => 'Accessories checked and debits applied.',
                'data' => [
                    'total_debit' => $totalDebit,
                    'remaining_deposit' => $checkout->deposited_amount
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }


    public function getCheckoutLogs(Request $request)
    {
        try {
            $user_id = $request->header('auth-id');
            $residentId = Helper::get_resident_details($user_id)->id; // Resident ID from header            

            $checkout = Checkout::where('resident_id', $residentId)->latest()->first();

            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'No checkout record found for this resident.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $logs = AccessoryCheckoutLog::with('accessory')
                ->where('checkout_id', $checkout->id)
                ->get()
                ->map(function ($log) {
                    return [
                        'accessory_head_id' => $log->accessory_head_id,
                        'accessory_name' => $log->accessory->name ?? null,
                        'is_returned' => $log->is_returned,
                        'debit_amount' => $log->debit_amount,
                        'remark' => $log->remark,
                        'logged_at' => $log->created_at->toDateTimeString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Checkout logs fetched successfully.',
                'data' => $logs,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch checkout logs.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function adminGetCheckoutLogs(Request $request, $residentId)
    {
        try {
            $checkout = Checkout::where('resident_id', $residentId)->latest()->first();

            if (!$checkout) {
                return response()->json([
                    'success' => false,
                    'message' => 'No checkout record found for this resident.',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $logs = AccessoryCheckoutLog::with('accessory')
                ->where('checkout_id', $checkout->id)
                ->get()
                ->map(function ($log) {
                    return [
                        'accessory_head_id' => $log->accessory_head_id,
                        'accessory_name' => $log->accessory->name ?? null,
                        'is_returned' => $log->is_returned,
                        'debit_amount' => $log->debit_amount,
                        'remark' => $log->remark,
                        'logged_at' => $log->created_at->toDateTimeString(),
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Checkout logs fetched successfully.',
                'data' => $logs,
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch checkout logs.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}



<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Fee;
use App\Models\Guest;
use App\Helpers\Helper;
use App\Models\Accessory;
use App\Models\FeeException;
use Illuminate\Http\Request;
use App\Models\AccessoryHead;
use App\Models\GuestAccessory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GuestController extends Controller
{

    // public function register(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'email' => 'required|email|unique:guests,email',
    //             'gender' => 'required|in:Male,Female,Other',
    //             'scholar_number' => 'required|unique:guests,scholar_number',
    //             'fathers_name' => 'required|string|max:255',
    //             'mothers_name' => 'required|string|max:255',
    //             'local_guardian_name' => 'nullable|string|max:255',
    //             'emergency_no' => 'required|string|max:20',
    //             'room_preference' => 'required|string|max:255', // Consider Rule::in(['Single', 'Double', 'Triple']) for better validation
    //             'food_preference' => 'required|string|max:255', // Consider Rule::in(['Veg', 'Non-Veg']) for better validation
    //             'months' => 'nullable|integer|min:1|max:12',
    //             'accessory_head_ids' => 'nullable|array',
    //             'accessory_head_ids.*' => 'exists:accessory_heads,id',
    //             // New fields:
    //             'fee_waiver' => 'boolean', // It will be true/false (0/1 from checkbox)
    //             'remarks' => [
    //                 'nullable',
    //                 'string',
    //                 'max:1000',
    //                 'required_if:fee_waiver,true', // Required only if fee_waiver is true
    //             ],
    //             'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // <--- NEW: Optional file attachment (Max 5MB)
    //         ]);

    //         // Start a database transaction
    //         DB::beginTransaction();

    //         $months = $validatedData['months'] ?? 3;

    //         $attachmentPath = null;
    //         // Handle attachment upload if a file is present
    //         if ($request->hasFile('attachment')) {
    //             // Store the file in the 'attachments' directory within the 'public' disk.
    //             // This will typically save to `storage/app/public/attachments/`
    //             // and be publicly accessible via `your-app-url/storage/attachments/filename.ext`
    //             $attachmentPath = $request->file('attachment')->store('attachments', 'public');
    //         }

    //         // Prepare guest data for creation
    //         $guestData = collect($validatedData)->except(['accessory_head_ids', 'attachment'])->toArray();
    //         $guestData['months'] = $months;
    //         $guestData['attachment_path'] = $attachmentPath; // Add the attachment path

    //         // Ensure fee_waiver is a proper boolean, as FormData might send 'true'/'false' strings
    //         $guestData['fee_waiver'] = filter_var($validatedData['fee_waiver'] ?? false, FILTER_VALIDATE_BOOLEAN);

    //         // Create the Guest record
    //         $guest = Guest::create($guestData);

    //         // Handle accessories if provided
    //         if (!empty($validatedData['accessory_head_ids'])) {
    //             $fromDate = Carbon::now();
    //             $toDate = Carbon::now()->addMonths($months);

    //             foreach ($validatedData['accessory_head_ids'] as $headId) {
    //                 $accessory = Accessory::where('accessory_head_id', $headId)
    //                     ->where('is_active', true)
    //                     ->latest('from_date')
    //                     ->first();

    //                 if ($accessory) {
    //                     GuestAccessory::create([
    //                         'guest_id' => $guest->id,
    //                         'accessory_head_id' => $headId,
    //                         'price' => $accessory->price,
    //                         'total_amount' => $accessory->price * $months,
    //                         'from_date' => $fromDate,
    //                         'to_date' => $toDate
    //                     ]);
    //                 }
    //             }
    //         }

    //         // Commit the transaction if everything was successful
    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Guest registered successfully.',
    //             'data' => $guest,
    //             'errors' => null
    //         ], 201);
    //     } catch (ValidationException $e) {
    //         // Rollback the transaction on validation failure
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'data' => null,
    //             'errors' => $e->errors()
    //         ], 422);
    //     } catch (\Exception $e) { // Catching a general Exception for broader error handling
    //         // Rollback the transaction on any other unexpected error
    //         DB::rollBack();
    //         Log::error('Guest registration failed: ' . $e->getMessage(), ['exception' => $e]); // Log the full exception

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Something went wrong during guest registration.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // } old without number etc

    public function register(Request $request)
    {
        Log::info($request->all());
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:guests,email',
                'gender' => 'required|in:Male,Female,Other',
                'scholar_number' => 'required|unique:guests,scholar_number',
                'fathers_name' => 'required|string|max:255',
                'mothers_name' => 'required|string|max:255',
                'local_guardian_name' => 'nullable|string|max:255',
                'emergency_contact' => 'required|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'parent_contact' => 'nullable|string|max:20',
                'guardian_contact' => 'nullable|string|max:20',
                'room_preference' => 'required|string|max:255',
                'food_preference' => 'required|string|max:255',
                'months' => 'nullable|integer|min:1|max:12',
                'accessory_head_ids' => 'nullable|array',
                'accessory_head_ids.*' => 'exists:accessory_heads,id',
                'fee_waiver' => 'boolean', // It will be true/false (0/1 from checkbox)
                'remarks' => [
                    'nullable',
                    'string',
                    'max:1000',
                    'required_if:fee_waiver,true', // Required only if fee_waiver is true
                ],
                'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // Optional file attachment (Max 5MB)
            ]);

            Log::info($validatedData);

            // Start a database transaction
            DB::beginTransaction();

            $months = $validatedData['months'] ?? 3;

            $attachmentPath = null;
            // Handle attachment upload if a file is present
            // if ($request->hasFile('attachment')) {
            //     $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            // }
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');

                // Create a unique and identifiable filename
                $filename = 'g_' . $validatedData['scholar_number'] . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

                // Store the file in storage/app/public/guest/
                $attachmentPath = $file->storeAs('guest', $filename, 'public');
            }

            // Prepare guest data for creation
            $guestData = collect($validatedData)->except(['accessory_head_ids', 'attachment'])->toArray();
            $guestData['months'] = $months;
            $guestData['attachment'] = $attachmentPath; // Add the attachment path

            // Ensure fee_waiver is a proper boolean, as FormData might send 'true'/'false' strings
            $guestData['fee_waiver'] = filter_var($validatedData['fee_waiver'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Create the Guest record
            $guest = Guest::create($guestData);

            // Handle accessories if provided
            if (!empty($validatedData['accessory_head_ids'])) {
                $fromDate = Carbon::now();
                $toDate = Carbon::now()->addMonths($months);

                // foreach ($validatedData['accessory_head_ids'] as $headId) {
                //     $accessory = Accessory::where('accessory_head_id', $headId)
                //         ->where('is_active', true)
                //         ->latest('from_date')
                //         ->first();

                //     if ($accessory) {
                //         GuestAccessory::create([
                //             'guest_id' => $guest->id,
                //             'accessory_head_id' => $headId,
                //             'price' => $accessory->price,
                //             'total_amount' => $accessory->price * $months,
                //             'from_date' => $fromDate,
                //             'to_date' => $toDate
                //         ]);
                //     }
                // }

                // foreach ($validatedData['accessory_head_ids'] as $headId) {
                //     $accessoryHead = AccessoryHead::where('id', $headId)
                //         ->where('is_active', true)
                //         ->first();

                //     if ($accessoryHead) {
                //         GuestAccessory::create([
                //             'guest_id' => $guest->id,
                //             'accessory_head_id' => $headId,
                //             'price' => $accessoryHead->default_price,
                //             'billing_cycle' => $accessoryHead->billing_cycle,
                //             'start_date' => Carbon::now(),
                //             'end_date' => Carbon::now()->addMonths($months),
                //             'is_complementary' => !$accessoryHead->is_paid,
                //             'status' => 'active'
                //         ]);
                //     }
                // }
                foreach ($validatedData['accessory_head_ids'] as $headId) {
                    $accessoryHead = AccessoryHead::where('id', $headId)
                        ->where('is_active', true)
                        ->first();

                    if ($accessoryHead) {
                        $price = $accessoryHead->default_price ?? 0;
                        $cycle = strtolower($accessoryHead->billing_cycle);
                        $isPaid = $accessoryHead->is_paid;

                        // Calculate how many billing cycles fit into the guest's stay
                        switch ($cycle) {
                            case 'monthly':
                                $cycles = $months;
                                break;
                            case 'quarterly':
                                $cycles = ceil($months / 3);
                                break;
                            case 'annually':
                                $cycles = ceil($months / 12);
                                break;
                            default:
                                $cycles = $months; // fallback to monthly
                        }

                        $totalAmount = $isPaid ? ($price * $cycles) : 0;

                        GuestAccessory::create([
                            'guest_id' => $guest->id,
                            'accessory_head_id' => $headId,
                            'price' => $price,
                            'total_amount' => $totalAmount,
                            'billing_cycle' => $accessoryHead->billing_cycle,
                            'from_date' => Carbon::now(),
                            'to_date' => Carbon::now()->addMonths($months),
                            'is_complementary' => !$isPaid,
                            'status' => 'active'
                        ]);
                    }
                }
            }

            // Commit the transaction if everything was successful
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Guest registered successfully.',
                'data' => $guest,
                'errors' => null
            ], 201);
        } catch (ValidationException $e) {
            // Rollback the transaction on validation failure
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) { // Catching a general Exception for broader error handling
            // Rollback the transaction on any other unexpected error
            DB::rollBack();
            Log::error('Guest registration failed: ' . $e->getMessage(), ['exception' => $e]); // Log the full exception

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong during guest registration.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }



    // public function getGuestTotalAmount(Request $request, $guest_id)
    // {
    //     try {
    //         $guest = Guest::findOrFail($guest_id);

    //         $months = $guest->months ?? 1;

    //         $guestAccessories = GuestAccessory::where('guest_id', $guest_id)->get();
    //         $accessoryTotal = $guestAccessories->sum('total_amount');
    //         $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

    //         $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
    //             ->where('is_active', true)
    //             ->latest('from_date')
    //             ->value('amount') ?? 0;

    //         $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
    //             ->where('is_active', true)
    //             ->latest('from_date')
    //             ->value('amount') ?? 0;

    //         $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
    //             ->where('is_active', true)
    //             ->latest('from_date')
    //             ->value('amount') ?? 0;

    //         $hostelFee = $hostelFeePerMonth * $months;
    //         $messFee = $messFeePerMonth * $months;

    //         $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Guest total amount fetched successfully.',
    //             'data' => [
    //                 'guest_id' => $guest_id,
    //                 'months' => $months,
    //                 'total_accessory_amount' => $accessoryTotal,
    //                 'hostel_fee' => $hostelFee + $messFee,
    //                 'caution_money' => $cautionMoney,
    //                 'final_total_amount' => $finalTotal,
    //                 'accessory_head_ids' => $accessoryHeadIds,
    //             ],
    //             'errors' => null
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Guest not found',
    //             'data' => null,
    //             'errors' => null
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch data',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // } old method


    // public function getGuestTotalAmount(Request $request, $guest_id)
    // {
    //     try {
    //         $guest = Guest::findOrFail($guest_id);
    //         $months = $guest->months ?? 1;

    //         // Get all guest accessories and total
    //         $guestAccessories = GuestAccessory::where('guest_id', $guest_id)->get();
    //         $accessoryTotal = $guestAccessories->sum('total_amount');
    //         $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

    //         $hostelFee = 0;
    //         $messFee = 0;
    //         $cautionMoney = 0;
    //         $waiverFeeUpdated = false;

    //         // If waiver is approved, pull from fee_exceptions
    //         if ($guest->status === 'waiver_approved') {
    //             $feeException = \App\Models\FeeException::where('guest_id', $guest_id)->first();

    //             if ($feeException) {
    //                 $hostelFee = $feeException->hostel_fee ?? 0;
    //                 $cautionMoney = $feeException->caution_money ?? 0;
    //                 $waiverFeeUpdated = true;
    //             }
    //         }

    //         // If not waiver_approved or exception not found, use regular fee table
    //         if (!$waiverFeeUpdated) {
    //             $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $hostelFee = $hostelFeePerMonth * $months;
    //             $messFee = $messFeePerMonth * $months;
    //         }

    //         $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Guest total amount fetched successfully.',
    //             'data' => [
    //                 'guest_id' => $guest_id,
    //                 'months' => $months,
    //                 'total_accessory_amount' => $accessoryTotal,
    //                 'hostel_fee' => $hostelFee + $messFee,
    //                 'caution_money' => $cautionMoney,
    //                 'final_total_amount' => $finalTotal,
    //                 'accessory_head_ids' => $accessoryHeadIds,
    //                 'waiver_fee_updated' => $waiverFeeUpdated,
    //             ],
    //             'errors' => null
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Guest not found',
    //             'data' => null,
    //             'errors' => null
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch data',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // } old without days


    // Origi
    public function getGuestTotalAmount(Request $request)
    {
        try {
            $user = Helper::get_auth_guest_user($request);
            $guest = Guest::select('id', 'months', 'days', 'status', 'fee_waiver')->findOrFail($user->id);

            // Log::info('running'. ' '. $guest);
            $months = $guest->months ?? 1;
            $days = $guest->days ?? 0;

            // $guestAccessories = GuestAccessory::where('guest_id', $guest->id)->where('status', 'active')->get();
            $guestAccessories = GuestAccessory::where('guest_id', $guest->id)->get();
            $accessoryTotal = $guestAccessories->sum('total_amount');
            $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

            $hostelFee = 0;
            $messFee = 0;
            $cautionMoney = 0;
            $waiverFeeUpdated = false;

            // Log::info('running'. ' '. 'fine');

            if ($guest->status === 'waiver_approved') {
                $feeException = \App\Models\FeeException::where('guest_id', $guest->id)->first();

                if ($feeException) {
                    $hostelFee = $feeException->hostel_fee ?? 0;
                    $cautionMoney = $feeException->caution_money ?? 0;
                    $waiverFeeUpdated = true;
                }
            }


            if (!$waiverFeeUpdated) {
                $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
                    ->where('is_active', true)
                    ->latest('from_date')
                    ->value('amount') ?? 0;

                $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
                    ->where('is_active', true)
                    ->latest('from_date')
                    ->value('amount') ?? 0;

                $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
                    ->where('is_active', true)
                    ->latest('from_date')
                    ->value('amount') ?? 0;

                $hostelFee = $hostelFeePerMonth * $months;
                $messFee = $messFeePerMonth * $months;
            }

            $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

            $data =  [
                    'guest_id' => $guest->id,
                    'months' => $months,
                    'days' => $days,
                    'total_accessory_amount' => $accessoryTotal,
                    'hostel_fee' => $hostelFee + $messFee,
                    'caution_money' => $cautionMoney,
                    'final_total_amount' => $finalTotal,
                    'accessory_head_ids' => $accessoryHeadIds,
                    'waiver_fee_updated' => $waiverFeeUpdated,
            ];
            Log::info($data);
            return response()->json([
                'success' => true,
                'message' => 'Guest total amount fetched successfully.',
                // 'data' => [
                //     'guest_id' => $guest->id,
                //     'months' => $months,
                //     'days' => $days,
                //     'total_accessory_amount' => $accessoryTotal,
                //     'hostel_fee' => $hostelFee + $messFee,
                //     'caution_money' => $cautionMoney,
                //     'final_total_amount' => $finalTotal,
                //     'accessory_head_ids' => $accessoryHeadIds,
                //     'waiver_fee_updated' => $waiverFeeUpdated,
                // ],
                $data,
                'errors' => null
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Guest not found',
                'data' => null,
                'errors' => null
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    // public function getGuestTotalAmount(Request $request)
    // {
    //     try {
    //         $user = Helper::get_auth_guest_user($request);
    //         $guest = Guest::select('id', 'months', 'days', 'status', 'fee_waiver')->findOrFail($user->id);

    //         $months = $guest->months ?? 1;
    //         $days = $guest->days ?? 0;

    //         $guestAccessories = GuestAccessory::where('guest_id', $guest->id)->get();
    //         $guestAccessories = GuestAccessory::where('guest_id', $guest->id)->get();
    //         $accessoryTotal = $guestAccessories->sum('total_amount');
    //         $accessoryHeadIds = $guestAccessories->pluck('accessory_head_id');

    //         $hostelFee = 0;
    //         $messFee = 0;
    //         $cautionMoney = 0;
    //         $waiverFeeUpdated = false;

    //         // Check for waiver-approved fee exceptions
    //         if ($guest->status === 'waiver_approved') {
    //             $feeException = FeeException::where('guest_id', $guest->id)->first();

    //             if ($feeException) {
    //                 $hostelFee = $feeException->hostel_fee ?? 0;
    //                 $cautionMoney = $feeException->caution_money ?? 0;
    //                 $waiverFeeUpdated = true;
    //             }
    //         }

    //         // If no waiver exception, fetch standard fees
    //         if (!$waiverFeeUpdated) {
    //             $hostelFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Hostel Fee'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $messFeePerMonth = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Mess Fee'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $cautionMoney = Fee::whereHas('feeHead', fn($q) => $q->where('name', 'Caution Money'))
    //                 ->where('is_active', true)
    //                 ->latest('from_date')
    //                 ->value('amount') ?? 0;

    //             $hostelFee = $hostelFeePerMonth * $months;
    //             $messFee = $messFeePerMonth * $months;
    //         }

    //         $finalTotal = $accessoryTotal + $hostelFee + $messFee + $cautionMoney;

    //         $accessoryDetails = $guestAccessories->map(function ($item) use ($months) {
    //             return [
    //                 'accessory_head_id' => $item->accessory_head_id,
    //                 'name' => optional($item->accessoryHead)->name,
    //                 'price' => $item->price,
    //                 'total' => $item->price * $months
    //             ];
    //         });


    //         $responseData = [
    //             'guest_id' => $guest->id,
    //             'months' => $months,
    //             'days' => $days,
    //             'total_accessory_amount' => $accessoryTotal,
    //             'hostel_fee' => $hostelFee + $messFee,
    //             'caution_money' => number_format($cautionMoney, 2, '.', ''),
    //             'final_total_amount' => $finalTotal,
    //             'accessory_head_ids' => $accessoryHeadIds,
    //             'accessory_details' => $accessoryDetails,
    //             'waiver_fee_updated' => $waiverFeeUpdated,
    //         ];

    //         Log::info('Guest total amount calculated:', $responseData);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Guest total amount fetched successfully.',
    //             'data' => $responseData,
    //             'errors' => null
    //         ]);
    //     } catch (ModelNotFoundException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Guest not found',
    //             'data' => null,
    //             'errors' => null
    //         ], 404);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch data',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // }


    // Orig
    public function pendingGuests(Request $request)
    {
        Log::info('pending-guest');
        try {
            $user = Helper::get_auth_guest_user($request);
            $guests = Guest::Where('id', $user->id)
                ->with(['accessories.accessoryHead:id,name'])
                ->whereNotIn('status', ['paid', 'approved', 'rejected', 'waiver_approved', 'waiver_rejected'])
                ->with('feeException')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Pending guests with accessories fetched successfully',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending guests',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    // public function pendingGuests(Request $request)
    // {
    //     Log::info('pending-guests' . $request->all());
    //     try {
    //         $user = Helper::get_auth_guest_user($request);

    //         $guests = Guest::where('id', $user->id)
    //             ->whereNotIn('status', [
    //                 'paid',
    //                 'approved',
    //                 'rejected',
    //                 'waiver_approved',
    //                 'waiver_rejected'
    //             ])
    //             ->with([
    //                 'accessoryHeads:id,name', // updated relationship name
    //                 'feeException'
    //             ])
    //             ->get();

    //         Log::info('pending-guests', [
    //             'user_id' => $user->id,
    //             'guests' => $guests->toArray()
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pending guests with accessory heads fetched successfully.',
    //             'data' => $guests,
    //             'errors' => null
    //         ], 200);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch pending guests.',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // }





    // public function pendingGuestsForAccountant()
    // {
    //     try {
    //         // Only fetch guests whose status is NOT 'paid' or 'rejected'
    //         $guests = Guest::with([
    //             'accessories.accessoryHead:id,name'
    //         ])
    //             ->whereNotIn('status', ['paid', 'approved', 'rejected', 'accountant_reject'])
    //             ->get();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Pending guests with accessories fetched successfully',
    //             'data' => $guests,
    //             'errors' => null
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch pending guests',
    //             'data' => null,
    //             'errors' => ['exception' => $e->getMessage()]
    //         ], 500);
    //     }
    // }

    public function pendingGuestsForAccountant()
    {
        try {
            $guests = Guest::with([
                'accessories.accessoryHead' // accessories = guest_accessory, accessoryHead = accessory_heads
            ])
                ->whereNotIn('status', ['paid', 'approved', 'rejected', 'accountant_reject'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Pending guests with accessories fetched successfully',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pending guests for accountant:', ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending guests',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }




    public function guestsStatus()
    {
        try {
            // Only fetch guests whose status is NOT 'paid' or 'rejected'
            $guests = Guest::with([
                'accessories.accessoryHead:id,name'
            ])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Pending guests with accessories fetched successfully',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending guests',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function getPaidGuests(Request $request)
    {
        try {
            $user = Helper::get_auth_guest_user($request);
            $guests = Guest::find($user->id)->where('status', 'paid')->get();

            if ($guests->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'This guest is not found with paid status.',
                    'data' => [],
                    'errors' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Paid guests fetched successfully.',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    public function getApprovedOrRejectedGuests(Request $request)
    {
        try {
            $user = Helper::get_auth_guest_user($request);
            $guests = Guest::where('id', $user->id)->whereIn('status', ['approved', 'rejected', 'pending', 'waiver_approved', 'waiver_rejected'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Approved, rejected, or pending guests retrieved successfully',
                'data' => $guests,
                'errors' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch guests',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }


    // public function showPendingGuests()
    // {
    //     $guests = Guest::with(['accessories.accessoryHead:id,name'])
    //         ->whereNotIn('status', ['paid', 'approved', 'rejected'])
    //         ->get();

    //     return view('admin.Pending_guest', compact('guests'));
    // }

    public function showPendingGuests()
    {
        Log::info('showPendingGuests');

        $guests = Guest::with([
            'accessories.accessoryHead:id,name' // Load only name from accessory_heads
        ])
            ->whereNotIn('status', ['paid', 'approved', 'rejected'])
            ->latest()
            ->get();

        return view('admin.Pending_guest', ['guests' => $guests]);
    }




    // In your GuestController or a relevant controller
    public function getTotalAmount(Guest $guest) // Assuming route model binding
    {
        try {
            // You'll need to fetch the relevant fee/payment details.
            // This might come from the Guest model directly, or a related model like FeeException or a Payments model.
            // Let's assume for this example, some fields are on the Guest model and some on FeeException.
            // Ensure FeeException is eagerly loaded if you need its data.
            $guest->load('feeException'); // Load feeException if it's related

            $data = [
                'hostel_fee' => $guest->hostel_fee ?? 0, // Assuming hostel_fee is on Guest or fetched
                'caution_money' => $guest->caution_money ?? 0, // Assuming caution_money is on Guest or fetched
                'months' => $guest->feeException->months ?? null, // Example: Months from FeeException
                'days' => $guest->feeException->days ?? null,     // Example: Days from FeeException
                'facility' => $guest->feeException->facility ?? null, // Example: Facility from FeeException
                'remarks' => $guest->remarks ?? null, // Guest's general remarks
                'approved_by' => $guest->feeException->approved_by ?? null, // Example: Approved by from FeeException
                'document_path' => $guest->feeException->document_path ?? null, // Example: Document path from FeeException
            ];

            return response()->json([
                'success' => true,
                'message' => 'Payment details fetched successfully.',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching total amount for guest {$guest->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment details.',
                'errors' => ['server_error' => $e->getMessage()]
            ], 500);
        }
    }
}

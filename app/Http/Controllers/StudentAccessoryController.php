<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bed;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\Accessory;
use Illuminate\Http\Request;
use App\Models\GuestAccessory;
use App\Models\StudentAccessory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Helpers\Helper;



class StudentAccessoryController extends Controller
{

    public function showPaymentForm($resident_id, $student_accessory_id)
    {
        try {
            // Check if the resident exists
            $resident = Resident::findOrFail($resident_id);

            // Check if the accessory exists
            $accessory = StudentAccessory::findOrFail($student_accessory_id);

            return view('resident.make_payment', [
                'resident_id' => $resident_id,
                'accessory_id' => $student_accessory_id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Resident or Accessory not found.',
                'details' => $e->getMessage()
            ], 404);
        }
    }


    public function addAccessory(Request $request)
    {
        try {
        Log::info("resident_id");
        $resident_id = Helper::get_resident_details($request->header('auth-id'))->id;
        Log::info($resident_id);
        // Validate request
        $validated = $request->validate([
            'accessory_head_id' => 'required|exists:accessory,id', // Ensure accessory exists
            'duration' => 'required|in:1 Month,3 Months,6 Months,1 Year' // Validate duration
        ]);

        DB::beginTransaction();

            $resident = Resident::findOrFail($resident_id);
            $accessory = Accessory::findOrFail($validated['accessory_head_id']);

            // Get current date
            $fromDate = now();

            // Determine the number of months for the selected duration
            $months = match ($validated['duration']) {
                '1 Month' => 1,
                '3 Months' => 3,
                '6 Months' => 6,
                '1 Year' => 12,
            };

            // Calculate `to_date` based on the duration
            $toDate = $fromDate->copy()->addMonths($months);

            // Set a fixed due date (30 days from now)
            $dueDate = now()->addDays(30);

            // Calculate the total amount (price × duration in months)
            $totalAmount = $accessory->price * $months;

            // Attach accessory to the resident
            $studentAccessory = StudentAccessory::create([
                'resident_id' => $resident->id,
                'accessory_head_id' => $accessory->accessory_head_id,
                'price' => $accessory->price,
                'total_amount' => $totalAmount, // Store total amount
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'due_date' => $dueDate
            ]);

            // Automatically create a pending payment record
            Payment::create([
                'resident_id' => $resident->id,
                'student_accessory_id' => $studentAccessory->id,
                'total_amount' => $totalAmount, // Store total amount
                'amount' => 0, // No initial payment
                'remaining_amount' => $totalAmount, // Ensure full amount is pending
                'payment_status' => 'Pending',
                'payment_method' => 'Null',
                'due_date' => $dueDate,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Accessory added successfully, waiting for payment.',
                'total_amount' => $totalAmount,
                'remaining_amount' => $totalAmount,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'due_date' => $dueDate
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to add accessory.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function payAccessory(Request $request, $student_accessory_id)
    {
        $resident_id = Helper::get_resident_details($request->header('auth-id'))->id;
        // Validate payment request
        $validated = $request->validate([
            'transaction_id' => 'nullable|unique:payments,transaction_id',
            'payment_method' => 'required|in:Cash,UPI,Bank Transfer,Card,Other',
            'amount' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            $resident = Resident::findOrFail($resident_id);

            // Find the accessory record using `student_accessory_id`
            $accessory = StudentAccessory::where('resident_id', $resident_id)
                ->where('id', $student_accessory_id)
                ->firstOrFail();

            // Fetch the first recorded payment for this accessory
            $firstPayment = Payment::where('student_accessory_id', $accessory->id)->first();

            // Ensure total amount is derived correctly
            $totalAmount = $firstPayment ? $firstPayment->total_amount : $accessory->total_amount;

            // Get total payments made so far for this accessory
            $totalPaid = Payment::where('student_accessory_id', $accessory->id)->sum('amount');

            // Calculate remaining balance
            $remainingBalance = max($totalAmount - $totalPaid, 0);

            // Ensure payment does not exceed the remaining balance
            if ($validated['amount'] > $remainingBalance) {
                return response()->json([
                    'error' => 'Amount exceeds the remaining balance.',
                    'total_amount' => $totalAmount,
                    'remaining_balance' => $remainingBalance
                ], 400);
            }

            // Calculate new remaining amount after payment
            $newRemainingAmount = max($remainingBalance - $validated['amount'], 0);

            // Determine new payment status
            $paymentStatus = ($newRemainingAmount == 0) ? 'Completed' : 'Pending';

            // Record the new payment entry (Partial or Full)
            $payment = Payment::create([
                'resident_id' => $resident->id,
                'student_accessory_id' => $accessory->id,
                'total_amount' => $totalAmount, // Keep total amount unchanged
                'amount' => $validated['amount'], // Amount paid in this transaction
                'remaining_amount' => $newRemainingAmount, // Updated remaining amount
                'transaction_id' => $validated['transaction_id'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'due_date' => $accessory->due_date,
            ]);

            // If fully paid, update the accessory status
            if ($newRemainingAmount == 0) {
                $accessory->update(['status' => 'Paid']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Accessory payment recorded successfully.',
                'transaction_id' => $validated['transaction_id'],
                'remaining_balance' => $newRemainingAmount
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process payment.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function adminSendAccessoryToResident(Request $request)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'accessory_head_id' => 'required|exists:accessory_heads,id', // ✅ validate from accessory_heads table
            'duration' => 'required|in:1 Month,3 Months,6 Months,1 Year',
            // 'created_by' => 'required|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $resident = Resident::findOrFail($request->resident_id);

            // ✅ Find active accessory using accessory_head_id
            $accessory = Accessory::where('accessory_head_id', $request->accessory_head_id)
                ->where('is_active', true)
                ->first();

            if (!$accessory) {
                return response()->json([
                    'error' => 'Active accessory not found for the given accessory_head_id'
                ], 404);
            }

            $fromDate = now();

            $months = match ($request->duration) {
                '1 Month' => 1,
                '3 Months' => 3,
                '6 Months' => 6,
                '1 Year' => 12,
            };

            $toDate = $fromDate->copy()->addMonths($months);
            $dueDate = now()->addDays(30);
            $price = $accessory->price;
            $totalAmount = $price * $months;

            // ✅ 1. Store accessory_head_id in student_accessory
            $studentAccessory = StudentAccessory::create([
                'resident_id' => $resident->id,
                'accessory_head_id' => $accessory->accessory_head_id, // ✅ changed here
                'price' => $price,
                'total_amount' => $totalAmount,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'due_date' => $dueDate,
            ]);

            // ✅ 2. Create Payment with accessory_head_id
            Payment::create([
                'resident_id' => $resident->id,
                'accessory_head_id' => $accessory->accessory_head_id, // ✅ changed here
                'student_accessory_id' => $studentAccessory->id,
                'total_amount' => $totalAmount,
                'amount' => 0,
                'remaining_amount' => $totalAmount,
                'transaction_id' => null,
                'payment_method' => 'Null',
                'payment_status' => 'Pending',
                'created_by' => $request->header("auth-id") ?? null,
                'due_date' => $dueDate,
                'remarks' => $request->remarks,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Accessory assigned and payment created successfully.',
                'student_accessory_id' => $studentAccessory->id,
                'payment_status' => 'Pending',
                'total_amount' => $totalAmount,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to assign accessory or create payment.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }



    public function getAccessories($resident_id)
    {
        try {
            $resident = Resident::with('accessories')->findOrFail($resident_id);

            $total_price = $resident->accessories->sum('pivot.price');

            return response()->json([
                'resident' => $resident,
                'accessories' => $resident->accessories,
                'total_price' => $total_price
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Resident not found.',
                'details' => $e->getMessage()
            ], 404);
        }
    }
}

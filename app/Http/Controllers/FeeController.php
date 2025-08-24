<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\FeeHead;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Exception;

class FeeController extends Controller
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

    // ✅ Admin Adds or Updates Fee for a Head
    public function createOrUpdate(Request $request)
    {
        $request->validate([
            'fee_head_id' => 'required|exists:fee_heads,id',
            'amount' => 'required|numeric',
            'created_by' => 'nullable|integer|exists:users,id',
        ]);

        try {
            $today = Carbon::today();
            $feeHead = FeeHead::find($request->fee_head_id);

            if (!$feeHead) {
                return $this->apiResponse(false, 'Fee Head not found.', null, 404);
            }

            $existing = Fee::where('fee_head_id', $request->fee_head_id)
                ->where('is_active', true)
                ->first();

            if ($existing) {
                $fromDate = Carbon::parse($existing->from_date);
                $diffInDays = $fromDate->diffInDays($today, false);

                if ($diffInDays < 0) {
                    return $this->apiResponse(false, 'Cannot update fee. Please wait at least 30 days from the last update.', null, 403, [
                        'days_remaining' => 0 - $diffInDays
                    ]);
                }

                $existing->update([
                    'to_date' => $today,
                    'is_active' => false,
                ]);
            }

            $newFee = Fee::create([
                'fee_head_id' => $feeHead->id,
                'name' => $feeHead->name,
                'amount' => $request->amount,
                'from_date' => $today,
                'is_active' => true,
                'created_by' => $request->created_by ?? null,
            ]);

            return $this->apiResponse(true, 'Fee added/updated successfully.', $newFee, 201);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'An error occurred while creating/updating the fee.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Get All Active Fees
    public function getAllActiveFees()
    {
        try {
            $fees = Fee::where('is_active', true)->get();
            return $this->apiResponse(true, 'Active fees fetched successfully.', $fees);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch active fees.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Get All Fees (Active + Inactive)
    public function getAllFees()
    {
        try {
            $fees = Fee::all();
            return $this->apiResponse(true, 'All fees fetched successfully.', $fees);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch all fees.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Get Fee by ID
    public function getFeeById($id)
    {
        try {
            $fee = Fee::findOrFail($id);
            return $this->apiResponse(true, 'Fee fetched successfully.', $fee);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Fee not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch fee.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Delete Fee
    public function deleteFee($id)
    {
        try {
            $fee = Fee::findOrFail($id);
            $fee->delete();
            return $this->apiResponse(true, 'Fee deleted successfully.', null);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Fee not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to delete fee.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

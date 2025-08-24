<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Accessory;
use Illuminate\Http\Request;
use App\Models\AccessoryHead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AccessoryController extends Controller
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

    // ✅ Create or Update Accessory
    public function createOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accessory_head_id' => 'required|exists:accessory_heads,id',
            'price' => 'required|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
        }

        $today = Carbon::today();

        try {
            $existing = Accessory::where('accessory_head_id', $request->accessory_head_id)
                ->where('is_active', true)
                ->first();

            if ($existing) {
                $fromDate = Carbon::parse($existing->from_date);
                $diffInDays = $fromDate->diffInDays($today, false);

                if ($diffInDays < 0) {
                    return $this->apiResponse(false, 'Cannot update accessory. Please wait at least 30 days from the last update.', null, 403, [
                        'days_remaining' => 0 - $diffInDays
                    ]);
                }

                $existing->update([
                    'to_date' => $today,
                    'is_active' => false,
                ]);
            }

            $newAccessory = Accessory::create([
                'accessory_head_id' => $request->accessory_head_id,
                'price' => $request->price,
                'is_default' => $request->is_default ?? false,
                'from_date' => $today,
                'is_active' => true,
                'created_by' => $request->header("auth-id") ?? null,
            ]);

            return $this->apiResponse(true, 'Accessory added/updated successfully.', $newAccessory, 201);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Something went wrong while creating/updating accessory.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Get All Accessories
    public function getAllAccessories()
    {
        try {
            $accessories = Accessory::with('accessoryHead')->get();

            return $this->apiResponse(true, 'All accessories fetched successfully.', $accessories);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch accessories.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Original
    // ✅ Get Active Accessories
    public function getActiveAccessories()
    {

        try {
            $activeAccessories = Accessory::with('accessoryHead')
                ->where('is_active', true)
                ->get();

            return $this->apiResponse(true, 'Active accessories fetched successfully.', $activeAccessories);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch active accessories.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // public function getActiveAccessories()
    // {
    //     try {
    //         $activeAccessories = AccessoryHead::where('is_active', true)
    //             ->select('id', 'name', 'is_paid', 'default_price', 'billing_cycle')
    //             ->get();
    //         Log::info('accessory'. $activeAccessories);
    //         return $this->apiResponse(true, 'Active accessories fetched successfully.', $activeAccessories);
    //     } catch (\Exception $e) {
    //         return $this->apiResponse(false, 'Failed to fetch active accessories.', null, 500, [
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }

    // ✅ Get Accessory By ID
    public function getAccessoryById($id)
    {
        try {
            $accessory = Accessory::with('accessoryHead')->findOrFail($id);

            return $this->apiResponse(true, 'Accessory fetched successfully.', $accessory);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Accessory not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch accessory.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    // ✅ Delete Accessory
    public function deleteAccessory($id)
    {
        try {
            $accessory = Accessory::findOrFail($id);
            $accessory->delete();

            return $this->apiResponse(true, 'Accessory deleted successfully.', null);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Accessory not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to delete accessory.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }


    // ✅ Get All Accessories
    public function ResidentAccessories($residentId)
    {
        try {
            $accessories = Accessory::with('accessoryHead')->get();

            return $this->apiResponse(true, 'All accessories fetched successfully.', $accessories);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch accessories.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}

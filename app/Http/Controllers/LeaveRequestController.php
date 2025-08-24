<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\LeaveRequest;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\LeaveNotificationService;
use App\Helpers\Helper; // Import the Helper class for utility



// class LeaveRequestController extends Controller
// {
//     private function apiResponse($success, $message, $data = null, $status = 200, $errors = null)
//     {
//         return response()->json([
//             'success' => $success,
//             'message' => $message,
//             'data' => $data ?? null,
//             'errors' => $errors ?? null
//         ], $status);
//     }

//     public function store(Request $request, $resident_id)
//     {
//         try {
//             $resident = Resident::findOrFail($resident_id);
//         } catch (ModelNotFoundException $e) {
//             return $this->apiResponse(false, 'Resident not found.', null, 404);
//         }

//         $validator = Validator::make($request->all(), [
//             'from_date' => 'required|date',
//             'to_date' => 'required|date|after_or_equal:from_date',
//             'reason' => 'required|string',
//             'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
//         ]);

//         if ($validator->fails()) {
//             return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
//         }

//         try {
//             $photoPath = null;
//             if ($request->hasFile('photo')) {
//                 $photoPath = $request->file('photo')->store('leave_photos', 'public');
//             }

//             $leaveRequest = LeaveRequest::create([
//                 'resident_id' => $resident_id,
//                 'from_date' => $request->from_date,
//                 'to_date' => $request->to_date,
//                 'reason' => $request->reason,
//                 'photo' => $photoPath,
//                 'hod_status' => 'pending',
//                 'admin_status' => 'pending'
//             ]);

//             return $this->apiResponse(true, 'Leave request submitted successfully.', $leaveRequest, 201);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to submit leave request.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     public function leaveReqById($residentId)
//     {
//         try {
//             $leaveRequests = LeaveRequest::with('resident.user:id,name,email')
//                 ->where('resident_id', $residentId)
//                 ->get();

//             if ($leaveRequests->isEmpty()) {
//                 return $this->apiResponse(false, 'No leave requests found for this resident.', null, 404);
//             }

//             return $this->apiResponse(true, 'Leave requests retrieved successfully.', $leaveRequests);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to fetch leave requests.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     public function hodApprove($id)
//     {
//         try {
//             $leaveRequest = LeaveRequest::findOrFail($id);
//             $leaveRequest->update(['hod_status' => 'approved']);

//             return $this->apiResponse(true, 'Leave request approved by HOD.');
//         } catch (ModelNotFoundException $e) {
//             return $this->apiResponse(false, 'Leave request not found.', null, 404);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to approve leave request by HOD.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     public function hodDeny($id)
//     {
//         try {
//             $leaveRequest = LeaveRequest::findOrFail($id);
//             $leaveRequest->update(['hod_status' => 'denied']);

//             return $this->apiResponse(true, 'Leave request denied by HOD.');
//         } catch (ModelNotFoundException $e) {
//             return $this->apiResponse(false, 'Leave request not found.', null, 404);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to deny leave request by HOD.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     public function index()
//     {
//         try {
//             $leaveRequests = LeaveRequest::with('resident.user:id,name,email')->get();

//             return $this->apiResponse(true, 'Leave requests retrieved successfully.', $leaveRequests);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to retrieve leave requests.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     public function adminApprove($id)
//     {
//         try {
//             $leaveRequest = LeaveRequest::findOrFail($id);

//             if ($leaveRequest->hod_status !== 'approved') {
//                 return $this->apiResponse(false, 'HOD approval is required before admin approval.', null, 403);
//             }

//             $leaveRequest->update(['admin_status' => 'approved']);

//             $resident = $leaveRequest->resident;
//             $user = $resident->user ?? null;
//             $guest = $resident->guest ?? null;

//             $data = [
//                 'resident_name' => $user->name ?? null,
//                 'scholar_number' => $guest->scholar_number ?? null,
//                 'from_date' => $leaveRequest->from_date,
//                 'to_date' => $leaveRequest->to_date,
//                 'reason' => $leaveRequest->reason,
//                 'hod_status' => $leaveRequest->hod_status,
//                 'admin_status' => $leaveRequest->admin_status,
//             ];

//             return $this->apiResponse(true, 'Leave request approved by Admin.', $data);
//         } catch (ModelNotFoundException $e) {
//             return $this->apiResponse(false, 'Leave request not found.', null, 404);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to approve leave request by Admin.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     public function adminDeny($id)
//     {
//         try {
//             $leaveRequest = LeaveRequest::findOrFail($id);
//             $leaveRequest->update(['admin_status' => 'denied']);

//             return $this->apiResponse(true, 'Leave request denied by Admin.');
//         } catch (ModelNotFoundException $e) {
//             return $this->apiResponse(false, 'Leave request not found.', null, 404);
//         } catch (Exception $e) {
//             return $this->apiResponse(false, 'Failed to deny leave request by Admin.', null, 500, [
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }
// } without notification



class LeaveRequestController extends Controller
{
    protected $leaveNotificationService;

    // Inject the LeaveNotificationService into the controller's constructor
    public function __construct(LeaveNotificationService $leaveNotificationService)
    {
        $this->leaveNotificationService = $leaveNotificationService;
    }

    private function apiResponse($success, $message, $data = null, $status = 200, $errors = null)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data ?? null,
            'errors' => $errors ?? null
        ], $status);
    }

    public function store(Request $request)
    {
        try {
            $resident = Helper::get_resident_details($request->header('auth-id'));
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Resident not found.', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'Validation failed.', null, 422, $validator->errors());
        }

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('leave_photos', 'public');
            }

            $leaveRequest = LeaveRequest::create([
                'resident_id' => $resident->id,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'reason' => $request->reason,
                'photo' => $photoPath,
                'hod_status' => 'pending',
                'admin_status' => 'pending'
            ]);

            return $this->apiResponse(true, 'Leave request submitted successfully.', $leaveRequest, 201);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to submit leave request.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function leaveReqById($residentId)
    {
        try {
            // Eager load the 'resident' relationship to get contact numbers if needed later
            $leaveRequests = LeaveRequest::with('resident.user:id,name,email')
                ->where('resident_id', $residentId)
                ->get();

            if ($leaveRequests->isEmpty()) {
                return $this->apiResponse(false, 'No leave requests found for this resident.', null, 404);
            }

            return $this->apiResponse(true, 'Leave requests retrieved successfully.', $leaveRequests);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to fetch leave requests.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function hodApprove($id)
    {
        try {
            // Eager load the 'resident' to ensure its data is available for notifications
            $leaveRequest = LeaveRequest::with('resident')->findOrFail($id);
            $leaveRequest->update(['hod_status' => 'approved']);

            // Send notification after HOD approval
            $this->leaveNotificationService->sendLeaveStatusNotification($leaveRequest, 'HOD', 'approved');

            return $this->apiResponse(true, 'Leave request approved by HOD.');
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Leave request not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to approve leave request by HOD.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function hodDeny($id)
    {
        try {
            // Eager load the 'resident' to ensure its data is available for notifications
            $leaveRequest = LeaveRequest::with('resident')->findOrFail($id);
            $leaveRequest->update(['hod_status' => 'denied']);

            // Send notification after HOD denial
            $this->leaveNotificationService->sendLeaveStatusNotification($leaveRequest, 'HOD', 'denied');

            return $this->apiResponse(true, 'Leave request denied by HOD.');
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Leave request not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to deny leave request by HOD.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function leaveRequestByResident()
    {
        try {
            $resident = Helper::get_resident_details(request()->header('auth-id'));
            $leaveRequests = LeaveRequest::with('resident.user:id,name,email')->where('resident_id',$resident->id)->get();

            return $this->apiResponse(true, 'Leave requests retrieved successfully.', $leaveRequests);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve leave requests.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function allLeaveRequests()
    {
        try {
            $leaveRequests = LeaveRequest::with('resident.user:id,name,email')->get();

            return $this->apiResponse(true, 'Leave requests retrieved successfully.', $leaveRequests);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to retrieve leave requests.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function adminApprove($id)
    {
        try {
            // Eager load the 'resident' to ensure its data is available for notifications
            $leaveRequest = LeaveRequest::with('resident')->findOrFail($id);

            if ($leaveRequest->hod_status !== 'approved') {
                return $this->apiResponse(false, 'HOD approval is required before admin approval.', null, 403);
            }

            $leaveRequest->update(['admin_status' => 'approved']);

            // Send notification after Admin approval
            $this->leaveNotificationService->sendLeaveStatusNotification($leaveRequest, 'Admin', 'approved');

            $resident = $leaveRequest->resident;
            $user = $resident->user ?? null;
            $guest = $resident->guest ?? null;

            $data = [
                'resident_name' => $user->name ?? null,
                'scholar_number' => $guest->scholar_number ?? null,
                'from_date' => $leaveRequest->from_date,
                'to_date' => $leaveRequest->to_date,
                'reason' => $leaveRequest->reason,
                'hod_status' => $leaveRequest->hod_status,
                'admin_status' => $leaveRequest->admin_status,
            ];

            return $this->apiResponse(true, 'Leave request approved by Admin.', $data);
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Leave request not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to approve leave request by Admin.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function adminDeny($id)
    {
        try {
            // Eager load the 'resident' to ensure its data is available for notifications
            $leaveRequest = LeaveRequest::with('resident')->findOrFail($id);
            $leaveRequest->update(['admin_status' => 'denied']);

            // Send notification after Admin denial
            $this->leaveNotificationService->sendLeaveStatusNotification($leaveRequest, 'Admin', 'denied');

            return $this->apiResponse(true, 'Leave request denied by Admin.');
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(false, 'Leave request not found.', null, 404);
        } catch (Exception $e) {
            return $this->apiResponse(false, 'Failed to deny leave request by Admin.', null, 500, [
                'error' => $e->getMessage()
            ]);
        }
    }
}
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    GuestController,
    HostelController,
    BuildingController,
    BedController,
    AccessoryController,
    StaffController,
    ResidentController,
    RoomController,
    NoticeController,
    LeaveRequestController,
    RoomChangeController,
    MessController,
    StudentAccessoryController,
    FeeController,
    FineController,
    PaymentController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/




// Show login form
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// Handle login POST request
Route::post('/login', [LoginController::class, 'login']);

// Logout route (optional)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



// ✅ Default Home Route
Route::get('/', function () {
    return view('index');
})->name('home');


Route::get('/resident/room-change-requests', function () {
    return view('resident.room_change_status');
});


// ✅ Resident Routes
Route::middleware(['auth:sanctum', 'role:resident'])->group(function () {


    Route::view('/resident/dashboard', 'resident.resident_dashboard')->name('resident.dashboard');
    Route::view('/resident/notices', 'resident.notices')->name('resident.notices');
    Route::view('/resident/feedback', 'resident.feedback')->name('resident.feedback');
    Route::view('/resident/accessories', 'resident.accessories')->name('resident.accessories');
    Route::view('/resident/leave-request', 'resident.leave_request')->name('resident.leave_request');
    Route::view('/resident/grievances/submit', 'resident.submit_grievance')->name('resident.submit_grievance');
    Route::view('/resident/leave_request_status', 'resident.leave_request_status')->name('resident.leave_request_status');
    Route::view('/resident/room_change_status', 'resident.room_change_status')->name('resident.room_change_status');
    Route::view('/resident/grievance_status', 'resident.grievance_status')->name('grievance_status');
    Route::view('/resident/payment', 'resident.payment')->name('resident.payment');
    // Route::view('/resident/subscription', 'resident.subscription')->name('resident.subscription');
    Route::view('/resident/subscription_type', 'resident.subscription_type')->name('resident.subscription.show');
    Route::view('/resident/subscription_payment', 'resident.subscription_payment')->name('resident.subscription.payment');
    Route::view('/resident/checkout', 'resident.checkout')->name('resident.checkout');
    Route::get('/resident/room-change', fn() => view('resident.room_change_request'))->name('resident.room_change_request');
    Route::get('/resident/checkout-status', fn() => view('resident.checkout_status'));
    Route::get('/resident/checkout_status', fn() => view('resident.checkout_status'))->name('resident.checkout.status');
    Route::get('/resident/payment/{resident_id}/{student_accessory_id}', [StudentAccessoryController::class, 'showPaymentForm']);
    Route::post('/resident/payment/process', [StudentAccessoryController::class, 'processPayment'])->name('resident.payment.process');
});



Route::post('/checkout/request', [CheckoutController::class, 'requestCheckout'])->middleware('auth');

// ✅ Admin Routes
// Route::middleware(['admin_api_auth'])->group(function(){
    Route::view('/admin/dashboard', 'admin.admin')->name('admin.dashboard');
    
// });

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::view('/admin/buildings', 'admin.building')->name('admin.building');
    Route::view('/admin/buildings/create', 'admin.create_building')->name('admin.create_building');
    Route::post('/admin/buildings', [BuildingController::class, 'store'])->name('admin.store_building');
    Route::delete('/admin/buildings/{id}', [BuildingController::class, 'destroy'])->name('admin.delete_building');

    Route::view('/admin/beds', 'admin.beds')->name('admin.beds');
    Route::view('/admin/beds/create', 'admin.create_bed')->name('admin.create_bed');
    Route::view('/admin/assignbed', 'admin.assignbed')->name('admin.assignbed');
    Route::get('/admin/api/beds', [BedController::class, 'index'])->name('admin.list_beds');
    Route::post('/admin/api/beds', [BedController::class, 'store'])->name('admin.store_bed');


 


    Route::view('/admin/accessories', 'admin.accessory')->name('admin.accessories');
    Route::view('/admin/accessories/create', 'admin.create_accessory')->name('admin.create_accessory');
    Route::post('/admin/accessories', [AccessoryController::class, 'store'])->name('admin.store_accessory');

    Route::view('/admin/staff', 'admin.staff')->name('admin.staff');
    Route::view('/admin/staff/create', 'admin.create_staff')->name('admin.create_staff');
    Route::post('/admin/staff', [StaffController::class, 'createStaff'])->name('admin.store_staff');
    Route::delete('/admin/staff/{id}', [StaffController::class, 'destroy'])->name('admin.delete_staff');

    Route::view('/admin/rooms', 'admin.rooms')->name('admin.rooms');
    Route::get('/admin/rooms/list', [RoomController::class, 'index'])->name('admin.list_rooms');
    Route::view('/admin/rooms/create', 'admin.create_rooms')->name('admin.create_rooms');
    Route::post('/admin/rooms', [RoomController::class, 'store'])->name('admin.store_room');
    Route::delete('/admin/rooms/{id}', [RoomController::class, 'destroy'])->name('admin.delete_room');

    Route::view('/admin/residents', 'admin.residents')->name('admin.residents');
    Route::get('/admin/residents/list', [ResidentController::class, 'index'])->name('admin.list_residents');
    Route::post('/admin/residents', [ResidentController::class, 'store'])->name('admin.store_resident');

    Route::view('/admin/leave-requests', 'admin.leave_requests')->name('admin.leave_requests');
    Route::view('/admin/grievances', 'admin.grievances')->name('admin.grievances');
    Route::view('/admin/room-change-requests', 'admin.room_change')->name('admin.room_change');
    Route::view('/admin/feedbacks', 'admin.feedback')->name('admin.feedbacks');

    // Route::view('/admin/fee_list', 'admin.fee_list')->name('admin.fee_list');
    // Route::get('/admin/fees', [FeeController::class, 'getAllFees']);
    // Route::post('/admin/add-fees', [FeeController::class, 'addOrUpdateFees']);

    Route::view('/admin/notices', 'admin.notices')->name('admin.notices');
    Route::view('/admin/notices/create', 'admin.create_notice')->name('admin.create_notice');
    Route::post('/admin/notices', [NoticeController::class, 'store'])->name('admin.store_notice');
    Route::delete('/admin/notices/{id}', [NoticeController::class, 'destroy'])->name('admin.delete_notice');

    Route::view('/admin/pendingpayments', 'admin.pendingpayments')->name('admin.pendingpayments');
    Route::view('/admin/checkout', 'admin.checkout')->name('admin.checkout');

    Route::view('/admin/pending-guests', 'admin.pending_guest')->name('guest.pending');
    Route::view('/admin/guests/paid', 'admin.paidguest')->name('admin.paid.guests');



});

// ✅ Superadmin Routes
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::view('/superadmin', 'superadmin.superadmin')->name('superadmin.dashboard');
    Route::view('/superadmin/universities', 'superadmin.universities')->name('superadmin.universities');
    Route::view('/superadmin/universities/create', 'superadmin.create_university')->name('superadmin.create_university');
    Route::view('/superadmin/admins', 'superadmin.admin')->name('superadmin.admins');
    Route::view('/superadmin/admins/create', 'superadmin.create_admin')->name('superadmin.create_admin');
});

// ✅ Guest Routes
// Route::get('/guest/register', fn() => view('guest.register'));
Route::view('/guest/registration-status', 'Guest.registration_status')->name('guest.registration_status');
// Handle login POST request
Route::post('/guest/regs_status', [LoginController::class, 'guestLogin'])->name('guest.regs_status');
Route::get('/guest/registration', fn() => view('guest.register'))->name('guest.register');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::view('/guest', 'guest.guest')->name('guest');
    Route::view('/guest/status', 'guest.status')->name('guest.status');
    Route::get('/guest/payment/{guestId}', function ($guestId) {
        $guest = \App\Models\Guest::findOrFail($guestId);
        return view('guest.payment', compact('guest'));
    })->name('guest.payment');
    Route::view('/guest/makepayment', 'Guest.makepayment')->name('Guest.makepayment');
    // Route::view('/guest/application-status', 'guest_status')->name('guest_status');
});

// ✅ Mess & Accountant

Route::middleware(['auth:sanctum', 'role:accountant'])->group(function () {
Route::get('/accountant/dashboard', fn() => view('accountant.account'))->name('accountant.account');
});
Route::get('/mess', fn() => view('mess.dashboard'))->name('mess.dashboard');
Route::get('/mess/records', [MessController::class, 'indexBlade'])->name('messes.index');

// ✅ APIs
// Room Change Request Routes
Route::get('/room-change-requests', [RoomChangeController::class, 'getAllRequests']);
Route::put('/room-change-requests/{id}/respond', [RoomChangeController::class, 'respondToRequest']);
Route::put('/room-change-requests/{id}/confirm', [RoomChangeController::class, 'confirmRoomChange']);

// Leave Request Routes
Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
Route::patch('/leave-requests/{id}/hod-approve', [LeaveRequestController::class, 'hodApprove']);
Route::patch('/leave-requests/{id}/hod-deny', [LeaveRequestController::class, 'hodDeny']);
Route::patch('/leave-requests/{id}/admin-approve', [LeaveRequestController::class, 'adminApprove']);
Route::patch('/leave-requests/{id}/admin-deny', [LeaveRequestController::class, 'adminDeny']);


Route::post('/api/leave-request', [LeaveRequestController::class, 'store']);

// ✅ Accessory Payment
Route::post('/residents/{resident_id}/accessories/{accessory_id}/pay', [StudentAccessoryController::class, 'payAccessory']);
Route::get('/residents/{resident_id}/accessories/{accessory_id}/pay', [StudentAccessoryController::class, 'payAccessory']);

// ✅ Subscription Payment Redirect
Route::post('/payments/{id}/process', fn($id) => redirect('/resident/subscription_payment/' . $id)->with('success', 'Payment Successful'));
Route::get('/resident/subscription_payment/{id}', fn($id) => view('resident.subscription_payment', ['subscription_id' => $id]));

// ✅ Fallback / Miscellaneous
Route::middleware(['auth'])->get('/admin-dashboard', fn() => view('dashboard'))->name('admin.dashboard');
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');



Route::get('/accountant/resident-payments', function () {
    return view('accountant.Residentpayment');
});

Route::get('/admin/subscribe-resident', function () {
    return view('admin.subscribe_resident');
});

Route::get('/admin/add-accessory', function () {
    return view('admin.addaccessories');
});

Route::get('/head', function () {
    return view('admin.Head');
})->name('head');


Route::view('/accountant/resident/pay', 'accountant.payaccount')->name('resident.pay.form');

Route::get('/accountant/payments', function () {
    return view('accountant.payments');
})->name('accountant.payments');



Route::get('/accountant/dashboard', function () {
    return view('accountant.dashboard');
})->name('accountant.dashboard');

Route::get('/accountant/account', function () {
    return view('accountant.account');
})->name('accountant.account');


    Route::get('/accountant/fees', function () {
    return view('accountant.fee'); 
})->name('accountant.fees');


Route::get('/accountant/feemaster', function () {
    return view('accountant.feemaster');
})->name('accountant.feemaster');

Route::get('/admin/fine', function () {
    return view('admin.fine');
})->name('admin.fine');

Route::get('/resident/fine', function () {
    return view('resident.fine');
});


Route::get('/admin/fine', [FineController::class, 'showFineAssignmentForm'])->name('admin.fine.form');

// In routes/web.php
Route::get('/accountant/guests', [App\Http\Controllers\FeeExceptionController::class, 'showGuestManagement'])->name('accountant.guests');


Route::get('/accountant/fines', function () {
    return view('accountant.fines');
})->name('accountant.fines');


Route::get('/accountant/resident/accessory-pay', [PaymentController::class, 'showAccessoryPaymentForm'])->name('accountant.resident.accessory-pay');


Route::get('/accountant/resident/accessory-pay', function () {
    return view('accountant.accessory_pay');
})->name('accountant.resident.accessory-pay');

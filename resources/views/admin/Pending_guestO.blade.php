@extends('admin.layout') {{-- Ensure this is your admin's layout --}}

@section('content')
<div class="container mt-4">
    <h3>Pending Guest Requests</h3>

    <div id="mainResponseMessage" class="mt-3"></div> {{-- Message container for the page --}}

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>S.No.</th> {{-- Changed from ID to S.No. --}}
                <th>Scholar No</th>
                <th>Name</th>
                <th>Father's Name</th>
                <th>Mother's Name</th>
                <th>Local Guardian</th>
                <th>Emergency No</th>
                <th>Gender</th>
                <th>Room Preference</th>
                <th>Food Preference</th>
                <th>Fee Waiver</th>
                <th>Account Remark</th> {{-- NEW COLUMN --}}
                <th>Status</th> {{-- NEW COLUMN --}}
                <th>Guest Remarks</th>
                <th>Guest Attachment</th>
                <th>Accessories</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="guestList">
            {{-- Adjusted colspan based on the new column count (14 static + 3 new = 17) --}}
            <tr>
                <td colspan="17" class="text-center">Loading pending guests...</td>
            </tr>
        </tbody>
    </table>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <div class="modal fade" id="editAmountModal" tabindex="-1" aria-labelledby="editAmountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAmountModalLabel">Edit Payment Amount</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAmountForm" enctype="multipart/form-data">
                        <input type="hidden" name="guest_id" id="edit_guest_id">
                        <input type="hidden" name="created_by" id="created_by" value="{{ auth()->user()->id ?? '' }}">

                        <div class="mb-3">
                            <label for="hostel_fee" class="form-label">Hostel Fee*</label>
                            <input type="number" class="form-control" id="hostel_fee" name="hostel_fee" required>
                        </div>

                        <div class="mb-3">
                            <label for="caution_money" class="form-label">Caution Money*</label>
                            <input type="number" class="form-control" id="caution_money" name="caution_money" required>
                        </div>

                        <div class="mb-3">
                            <label for="months" class="form-label">Months</label>
                            <input type="number" class="form-control" id="months" name="months" min="0">
                        </div>

                        <div class="mb-3">
                            <label for="days" class="form-label">Days</label>
                            <input type="number" class="form-control" id="days" name="days" min="0" max="31">
                        </div>

                        {{-- NEW: Start Date Field --}}
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>

                        {{-- NEW: End Date Field --}}
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>

                        <div class="mb-3">
                            <label for="facility" class="form-label">Facility</label>
                            <input type="text" class="form-control" id="facility" name="facility">
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="approved_by" class="form-label">Approved By</label>
                            <input type="text" class="form-control" id="approved_by" name="approved_by">
                        </div>

                        <div class="mb-3">
                            <label for="document" class="form-label">Upload Document</label>
                            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="mb-3 text-danger" id="editAmountErrors"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="reviewRejectModal" tabindex="-1" aria-labelledby="reviewRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewRejectModalLabel">Review & Adjust Rejected Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewRejectForm" enctype="multipart/form-data">
                        <input type="hidden" name="guest_id" id="review_guest_id">
                        <input type="hidden" name="created_by" id="review_created_by" value="{{ auth()->user()->id ?? '' }}">

                        <div class="mb-3">
                            <label for="review_hostel_fee" class="form-label">Hostel Fee*</label>
                            <input type="number" class="form-control" id="review_hostel_fee" name="hostel_fee" required>
                        </div>

                        <div class="mb-3">
                            <label for="review_caution_money" class="form-label">Caution Money*</label>
                            <input type="number" class="form-control" id="review_caution_money" name="caution_money" required>
                        </div>

                        <div class="mb-3">
                            <label for="review_months" class="form-label">Months</label>
                            <input type="number" class="form-control" id="review_months" name="months" min="0">
                        </div>

                        <div class="mb-3">
                            <label for="review_days" class="form-label">Days</label>
                            <input type="number" class="form-control" id="review_days" name="days" min="0" max="31">
                        </div>

                        {{-- NEW: Start Date Field for Review/Reject Modal --}}
                        <div class="mb-3">
                            <label for="review_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="review_start_date" name="start_date">
                        </div>

                        {{-- NEW: End Date Field for Review/Reject Modal --}}
                        <div class="mb-3">
                            <label for="review_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="review_end_date" name="end_date">
                        </div>

                        <div class="mb-3">
                            <label for="review_facility" class="form-label">Facility</label>
                            <input type="text" class="form-control" id="review_facility" name="facility">
                        </div>

                        <div class="mb-3">
                            <label for="review_remarks" class="form-label">Remarks</label>
                            <textarea class="form-control" id="review_remarks" name="remarks"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="review_approved_by" class="form-label">Approved By</label>
                            <input type="text" class="form-control" id="review_approved_by" name="approved_by">
                        </div>

                        <div class="mb-3">
                            <label for="review_document" class="form-label">Upload New Document (optional)</label>
                            <input type="file" class="form-control" id="review_document" name="document" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted" id="review_currentDocumentInfo"></small>
                        </div>

                        <div class="mb-3 text-danger" id="reviewAmountErrors"></div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Save Review & Update</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- Accessory Details Modal --}}
    <div class="modal fade" id="accessoryModal" tabindex="-1" role="dialog" aria-labelledby="accessoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accessories</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="accessoryList">
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="confirmProcessModal" tabindex="-1" aria-labelledby="confirmProcessModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmProcessModalLabel">Process Guest Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>What action would you like to take for this guest application?</p>
                    <div id="processGuestButtons" class="d-grid gap-2">
                        {{-- Buttons will be dynamically added here by JavaScript --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">



<script>
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
        const messageContainer = document.getElementById(targetElementId);
        if (messageContainer) {
            messageContainer.innerHTML = "";
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    function showModalMessage(message, type = 'info', targetElementId = 'adjustPaymentMessage') {
        const messageContainer = document.getElementById(targetElementId);
        if (messageContainer) {
            messageContainer.innerHTML = "";
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            messageContainer.appendChild(alertDiv);
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    function fetchPendingGuests() {
        const guestList = document.getElementById("guestList");
        if (!guestList) {
            console.error("Guest list table body element #guestList not found.");
            return;
        }

        guestList.innerHTML = `<tr><td colspan="17" class="text-center">Loading pending guests...</td></tr>`;

        fetch("{{ url('/api/admin/guests/pending') }}", {
            method: "GET",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(response => {
                const guests = response.data;
                guestList.innerHTML = "";

                if (!response.success || !Array.isArray(guests) || guests.length === 0) {
                    guestList.innerHTML = `<tr><td colspan="17" class="text-center">No pending guests found.</td></tr>`;
                    if (!response.success && response.message) {
                        showCustomMessageBox(response.message, 'danger');
                    }
                    return;
                }

                guests.forEach((guest, index) => {
                    const accessoriesButton = `<button class="btn btn-info btn-sm" onclick='viewAccessories(${JSON.stringify(guest.accessories || [])})'>View</button>`;
                    const feeWaiverStatus = guest.fee_waiver ? 'Yes' : 'No';
                    const remarksContent = guest.remarks || 'N/A';
                    const accountRemarkContent = guest.fee_exception && guest.fee_exception.account_remark ? guest.fee_exception.account_remark : 'N/A';
                    const guestStatusDisplay = guest.status || 'N/A';

                    let attachmentLink = 'N/A';
                    if (guest.attachment_path) {
                        attachmentLink = `<a href="/storage/${guest.attachment_path}" target="_blank" class="btn btn-sm btn-secondary">View</a>`;
                    }

                    let actionButtons = '';
                    if (guest.status === 'pending') {
                        if (guest.fee_waiver) {
                            actionButtons = `
                                <button class="btn btn-primary btn-sm mb-1" onclick="showProcessGuestModal(${guest.id}, true)">Process</button>
                                <button class="btn btn-danger btn-sm mb-1" onclick="rejectApplication(${guest.id})">Reject Application</button>
                            `;
                        } else {
                            actionButtons = `
                                <button class="btn btn-success btn-sm mb-1" onclick="approveGuest(${guest.id})">Approve</button>
                                <button class="btn btn-danger btn-sm mb-1" onclick="rejectApplication(${guest.id})">Reject Application</button>
                            `;
                        }
                    } else if (guest.status === 'accountant_reject') {
                        actionButtons += `
                            <button class="btn btn-warning btn-sm mb-1" onclick="showReviewRejectModal(${guest.id})">
                                Review Rejected Status
                            </button>
                        `;
                    }

                    guestList.innerHTML += `
                        <tr id="guest-${guest.id}">
                            <td>${index + 1}</td>
                            <td>${guest.scholar_no || 'N/A'}</td>
                            <td>${guest.name || '-'}</td>
                            <td>${guest.fathers_name || 'N/A'}</td>
                            <td>${guest.mothers_name || 'N/A'}</td>
                            <td>${guest.local_guardian_name || 'N/A'}</td>
                            <td>${guest.emergency_no || 'N/A'}</td>
                            <td>${guest.gender || '-'}</td>
                            <td>${guest.room_preference || 'N/A'}</td>
                            <td>${guest.food_preference || 'N/A'}</td>
                            <td>${feeWaiverStatus}</td>
                            <td>${accountRemarkContent}</td>
                            <td>${guestStatusDisplay}</td>
                            <td>${remarksContent}</td>
                            <td>${attachmentLink}</td>
                            <td>${accessoriesButton}</td>
                            <td>${actionButtons}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                console.error('Error fetching guests:', error);
                guestList.innerHTML = `<tr><td colspan="17" class="text-center text-danger">Failed to load guests.</td></tr>`;
                showCustomMessageBox('Failed to load pending guests.', 'danger');
            });
    }

    window.approveGuest = function(guestId) {
        fetch("{{ url('/api/admin/approve-guest') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json",
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                },
                body: JSON.stringify({
                    guest_id: guestId
                })
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    showCustomMessageBox(response.message || "Guest approved successfully.", 'success');
                    if (confirmProcessModal) {
                        confirmProcessModal.hide();
                    }
                    fetchPendingGuests();
                } else {
                    showCustomMessageBox(response.message || "Approval failed.", 'danger');
                }
            })
            .catch(error => {
                console.error('Error approving guest:', error);
                showCustomMessageBox('An error occurred during approval.', 'danger');
            });
    };

    window.rejectApplication = function(guestId) {
        const remark = prompt("Please enter a remark for rejecting this application:");
        if (remark === null) {
            return;
        }

        fetch("{{ url('/api/admin/payment/reject') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json",
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                },
                body: JSON.stringify({
                    guest_id: guestId,
                    admin_remarks: remark
                })
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    showCustomMessageBox(response.message || "Guest application rejected successfully.", 'success');
                    if (confirmProcessModal) {
                        confirmProcessModal.hide();
                    }
                    fetchPendingGuests();
                } else {
                    showCustomMessageBox(response.message || "Application rejection failed.", 'danger');
                }
            })
            .catch(error => {
                console.error('Error rejecting guest application:', error);
                showCustomMessageBox('An error occurred during application rejection.', 'danger');
            });
    };

    window.rejectWaiver = function(guestId) {
        const remark = prompt("Please enter a remark for rejecting this fee waiver:");
        if (remark === null) {
            return;
        }

        fetch("{{ url('/api/admin/reject-waiver') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    "Accept": "application/json",
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                },
                body: JSON.stringify({
                    guest_id: guestId,
                    admin_remarks: remark
                })
            })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    showCustomMessageBox(response.message || "Fee waiver rejected successfully.", 'success');
                    if (confirmProcessModal) {
                        confirmProcessModal.hide();
                    }
                    fetchPendingGuests();
                } else {
                    showCustomMessageBox(response.message || "Fee waiver rejection failed.", 'danger');
                }
            })
            .catch(error => {
                console.error('Error rejecting fee waiver:', error);
                showCustomMessageBox('An error occurred during fee waiver rejection.', 'danger');
            });
    };

    window.viewAccessories = function(accessories) {
        const accessoryList = document.getElementById("accessoryList");
        if (!accessoryList) {
            console.error("Accessory list element #accessoryList not found.");
            return;
        }
        accessoryList.innerHTML = "";

        if (!Array.isArray(accessories) || accessories.length === 0) {
            accessoryList.innerHTML = "<p>No accessories found for this guest.</p>";
        } else {
            let table = `
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>From Date</th>
                            <th>To Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${accessories.map(item => `
                            <tr>
                                <td>${item.accessory_head ? item.accessory_head.name : 'N/A'}</td>
                                <td>${item.pivot ? item.pivot.price : 'N/A'}</td>
                                <td>${item.pivot ? item.pivot.from_date : 'N/A'}</td>
                                <td>${item.pivot ? item.pivot.to_date || 'N/A' : 'N/A'}</td>
                            </tr>
                        `).join("")}
                    </tbody>
                </table>
            `;
            accessoryList.innerHTML = table;
        }

        const accessoryModal = new bootstrap.Modal(document.getElementById('accessoryModal'));
        accessoryModal.show();
    };

    let editAmountModal;
    let reviewRejectModal;
    let confirmProcessModal;

    document.addEventListener("DOMContentLoaded", function() {
        editAmountModal = new bootstrap.Modal(document.getElementById('editAmountModal'), {
            backdrop: 'static',
            keyboard: false
        });

        reviewRejectModal = new bootstrap.Modal(document.getElementById('reviewRejectModal'), {
            backdrop: 'static',
            keyboard: false
        });

        confirmProcessModal = new bootstrap.Modal(document.getElementById('confirmProcessModal'), {
            backdrop: 'static',
            keyboard: false
        });

        fetchPendingGuests();

        const editAmountForm = document.getElementById('editAmountForm');
        if (editAmountForm) {
            editAmountForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(editAmountForm);

                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken()
                        }
                    });

                    const result = await response.json();
                    const errorBox = document.getElementById('editAmountErrors');
                    if (errorBox) {
                        errorBox.textContent = '';
                        errorBox.classList.add('d-none');
                    }

                    if (result.success) {
                        showCustomMessageBox(result.message || 'Payment updated successfully.', 'success');
                        editAmountModal.hide();
                        fetchPendingGuests();
                    } else {
                        if (errorBox) {
                            errorBox.classList.remove('d-none');
                            errorBox.textContent = result.message || 'Something went wrong.';
                            if (result.errors) {
                                for (const key in result.errors) {
                                    errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                                }
                            }
                        } else {
                            showCustomMessageBox(result.message || 'Something went wrong.', 'danger');
                        }
                    }

                } catch (error) {
                    console.error('Error submitting editAmountForm:', error);
                    const errorBox = document.getElementById('editAmountErrors');
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                        errorBox.innerText = 'An unexpected error occurred. Please try again.';
                    } else {
                        showCustomMessageBox('An unexpected error occurred. Please try again.', 'danger');
                    }
                }
            });
        }

        const reviewRejectForm = document.getElementById('reviewRejectForm');
        if (reviewRejectForm) {
            reviewRejectForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(reviewRejectForm);

                try {
                    const response = await fetch('/api/admin/modify-waiver/payments', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json',
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')
                        }
                    });
                    const result = await response.json();
                    const errorBox = document.getElementById('reviewAmountErrors');
                    if (errorBox) {
                        errorBox.textContent = '';
                        errorBox.classList.add('d-none');
                    }

                    if (result.success) {
                        showCustomMessageBox(result.message || 'Rejected payment reviewed and updated successfully.', 'success');
                        reviewRejectModal.hide();
                        fetchPendingGuests();
                    } else {
                        if (errorBox) {
                            errorBox.classList.remove('d-none');
                            errorBox.textContent = result.message || 'Review update failed.';
                            if (result.errors) {
                                for (const key in result.errors) {
                                    errorBox.innerHTML += `<br>${result.errors[key].join(', ')}`;
                                }
                            }
                        } else {
                            showCustomMessageBox(result.message || 'Review update failed.', 'danger');
                        }
                    }

                } catch (error) {
                    console.error('Error submitting reviewRejectForm:', error);
                    const errorBox = document.getElementById('reviewAmountErrors');
                    if (errorBox) {
                        errorBox.classList.remove('d-none');
                        errorBox.innerText = 'An unexpected error occurred. Please try again.';
                    } else {
                        showCustomMessageBox('An unexpected error occurred. Please try again.', 'danger');
                    }
                }
            });
        }

        window.approveFeeWaiver = function(guestId) {
            confirmProcessModal.hide();
            showAdjustPaymentModal(guestId);
        };

        window.showAdjustPaymentModal = function(guestId) {
            const guestIdInput = document.getElementById('edit_guest_id');
            const hostelFeeInput = document.getElementById('hostel_fee');
            const cautionMoneyInput = document.getElementById('caution_money');
            const monthsInput = document.getElementById('months');
            const daysInput = document.getElementById('days');
            const facilityInput = document.getElementById('facility');
            const remarksInput = document.getElementById('remarks');
            const approvedByInput = document.getElementById('approved_by');
            const currentDocumentInfo = document.getElementById('currentDocumentInfo');
            const editAmountErrors = document.getElementById('editAmountErrors');
            const calculatedTotalDisplay = document.getElementById('calculated_total_display');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (guestIdInput) guestIdInput.value = guestId;
            if (hostelFeeInput) hostelFeeInput.value = '';
            if (cautionMoneyInput) cautionMoneyInput.value = '';
            if (monthsInput) monthsInput.value = '';
            if (daysInput) daysInput.value = '';
            if (startDateInput) startDateInput.value = '';
            if (endDateInput) endDateInput.value = '';
            if (facilityInput) facilityInput.value = '';
            if (remarksInput) remarksInput.value = '';
            if (approvedByInput) approvedByInput.value = '';
            if (currentDocumentInfo) currentDocumentInfo.innerHTML = '';
            if (editAmountErrors) {
                editAmountErrors.textContent = '';
                editAmountErrors.classList.add('d-none');
            }
            if (calculatedTotalDisplay) calculatedTotalDisplay.textContent = '0.00';

            const updateCalculatedTotal = () => {
                const hFee = parseFloat(hostelFeeInput.value) || 0;
                const cMoney = parseFloat(cautionMoneyInput.value) || 0;
                if (calculatedTotalDisplay) {
                    calculatedTotalDisplay.textContent = (hFee + cMoney).toFixed(2);
                }
            };

            if (hostelFeeInput) hostelFeeInput.addEventListener('input', updateCalculatedTotal);
            if (cautionMoneyInput) cautionMoneyInput.addEventListener('input', updateCalculatedTotal);

            fetch(`/api/guest/${guestId}/fee-exception-details`)
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        if (hostelFeeInput) hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        if (cautionMoneyInput) cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        if (monthsInput) monthsInput.value = feeExceptionDetails.months || '';
                        if (daysInput) daysInput.value = feeExceptionDetails.days || '';
                        if (startDateInput) startDateInput.value = feeExceptionDetails.start_date || '';
                        if (endDateInput) endDateInput.value = feeExceptionDetails.end_date || '';
                        if (facilityInput) facilityInput.value = feeExceptionDetails.facility || '';
                        if (remarksInput) remarksInput.value = feeExceptionDetails.remarks || '';
                        if (approvedByInput) approvedByInput.value = feeExceptionDetails.approved_by || '';
                        updateCalculatedTotal();

                        if (feeExceptionDetails.document_url && currentDocumentInfo) {
                            // You might want to display the document here, e.g., an image or a link
                            // currentDocumentInfo.innerHTML = `<a href="${feeExceptionDetails.document_url}" target="_blank">View Document</a>`;
                        } else if (currentDocumentInfo) {
                            currentDocumentInfo.textContent = '';
                        }
                    } else if (!response.success && response.message && editAmountErrors) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = response.message || 'No existing fee exception details found for this guest.';
                    } else if (editAmountErrors) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details:', error);
                    if (editAmountErrors) {
                        editAmountErrors.classList.remove('d-none');
                        editAmountErrors.textContent = 'Error fetching fee exception details. Please try again.';
                    }
                });

            editAmountModal.show();
        };

        window.showReviewRejectModal = function(guestId) {
            const guestIdInput = document.getElementById('review_guest_id');
            const hostelFeeInput = document.getElementById('review_hostel_fee');
            const cautionMoneyInput = document.getElementById('review_caution_money');
            const monthsInput = document.getElementById('review_months');
            const daysInput = document.getElementById('review_days');
            const facilityInput = document.getElementById('review_facility');
            const remarksInput = document.getElementById('review_remarks');
            const approvedByInput = document.getElementById('review_approved_by');
            const currentDocumentInfo = document.getElementById('review_currentDocumentInfo');
            const reviewAmountErrors = document.getElementById('reviewAmountErrors');
            const calculatedTotalDisplay = document.getElementById('review_calculated_total_display');
            const reviewStartDateInput = document.getElementById('review_start_date');
            const reviewEndDateInput = document.getElementById('review_end_date');

            if (guestIdInput) guestIdInput.value = guestId;
            if (hostelFeeInput) hostelFeeInput.value = '';
            if (cautionMoneyInput) cautionMoneyInput.value = '';
            if (monthsInput) monthsInput.value = '';
            if (daysInput) daysInput.value = '';
            if (reviewStartDateInput) reviewStartDateInput.value = '';
            if (reviewEndDateInput) reviewEndDateInput.value = '';
            if (facilityInput) facilityInput.value = '';
            if (remarksInput) remarksInput.value = '';
            if (approvedByInput) approvedByInput.value = '';
            if (currentDocumentInfo) currentDocumentInfo.innerHTML = '';
            if (reviewAmountErrors) {
                reviewAmountErrors.textContent = '';
                reviewAmountErrors.classList.add('d-none');
            }
            if (calculatedTotalDisplay) calculatedTotalDisplay.textContent = '0.00';

            const updateCalculatedTotal = () => {
                const hFee = parseFloat(hostelFeeInput.value) || 0;
                const cMoney = parseFloat(cautionMoneyInput.value) || 0;
                if (calculatedTotalDisplay) {
                    calculatedTotalDisplay.textContent = (hFee + cMoney).toFixed(2);
                }
            };
            if (hostelFeeInput) hostelFeeInput.addEventListener('input', updateCalculatedTotal);
            if (cautionMoneyInput) cautionMoneyInput.addEventListener('input', updateCalculatedTotal);

            fetch(`/api/guest/${guestId}/fee-exception-details`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        if (hostelFeeInput) hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        if (cautionMoneyInput) cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        if (monthsInput) monthsInput.value = feeExceptionDetails.months || '';
                        if (daysInput) daysInput.value = feeExceptionDetails.days || '';
                        if (reviewStartDateInput) reviewStartDateInput.value = feeExceptionDetails.start_date || '';
                        if (reviewEndDateInput) reviewEndDateInput.value = feeExceptionDetails.end_date || '';
                        if (facilityInput) facilityInput.value = feeExceptionDetails.facility || '';
                        if (remarksInput) remarksInput.value = feeExceptionDetails.remarks || '';
                        if (approvedByInput) approvedByInput.value = feeExceptionDetails.approved_by || '';
                        updateCalculatedTotal();

                        if (feeExceptionDetails.document_url && currentDocumentInfo) {
                            // You might want to display the document here, e.g., an image or a link
                            // currentDocumentInfo.innerHTML = `<a href="${feeExceptionDetails.document_url}" target="_blank">View Document</a>`;
                        } else if (currentDocumentInfo) {
                            currentDocumentInfo.textContent = '';
                        }
                    } else if (!response.success && response.message && reviewAmountErrors) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = response.message || 'No existing fee exception details found for this guest. You can enter new values.';
                    } else if (reviewAmountErrors) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details for review:', error);
                    if (reviewAmountErrors) {
                        reviewAmountErrors.classList.remove('d-none');
                        reviewAmountErrors.textContent = 'Error fetching fee exception details. Please try again.';
                    }
                });

            reviewRejectModal.show();
        };

        window.hideEditAmountSection = function() {
            editAmountModal.hide();
        };

        window.showProcessGuestModal = function(guestId, hasFeeWaiver) {
            const processGuestButtonsContainer = document.getElementById('processGuestButtons');
            if (processGuestButtonsContainer) {
                processGuestButtonsContainer.innerHTML = '';

                if (hasFeeWaiver) {
                    processGuestButtonsContainer.innerHTML += `
                        <button class="btn btn-primary btn-lg mb-1" onclick="approveFeeWaiver(${guestId})">Approve Fee Waiver</button>
                        <button class="btn btn-danger btn-lg mb-1" onclick="rejectWaiver(${guestId})">Reject Waiver / Procced Without Waiver</button>
                    `;
                } else {
                    processGuestButtonsContainer.innerHTML += `
                        <button class="btn btn-success btn-lg mb-1" onclick="approveGuest(${guestId})">Approve Guest</button>
                        <button class="btn btn-danger btn-lg mb-1" onclick="rejectApplication(${guestId})">Reject Application</button>
                    `;
                }

            } else {
                console.error("Container for process guest buttons not found.");
                showCustomMessageBox("Could not open processing options. Please check console for errors.", 'danger');
                return;
            }
            confirmProcessModal.show();
        };
    });
</script>









<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
@extends('accountant.layout') {{-- Ensure this is your accountant's layout --}}

@section('content')
<div class="container mt-4">
    <h3>Guest Management & Payment Adjustments</h3> {{-- Updated title to reflect both functions --}}

    <div id="mainResponseMessage" class="mt-3"></div> {{-- Message container for the page --}}

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr>
                <th>Serial No</th> {{-- Changed from ID to Serial No --}}
                <th>Scholar No</th>
                <th>Name</th>
                <th>Fee Waiver</th>
                {{-- <th>Current Payable</th> --}} {{-- Removed Current Payable --}}
                <th>Remarks</th>
                <th>Guest Attachment</th>
                <th>Accessories</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="guestList">
            <tr>
                <td colspan="8" class="text-center">Loading guests...</td> {{-- Updated colspan --}}
            </tr>
        </tbody>
    </table>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



    <div class="modal fade" id="editAmountModal" tabindex="-1" aria-labelledby="editAmountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAmountModalLabel">Uploaded detail by Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Removed the form tag as fields are not editable here --}}
                    <input type="hidden" name="guest_id" id="edit_guest_id">
                    <input type="hidden" name="created_by" id="created_by" value="{{ auth()->user()->id ?? '' }}">

                    <div class="mb-3">
                        <label for="hostel_fee" class="form-label">Hostel Fee*</label>
                        {{-- Changed input to readonly --}}
                        <input type="number" class="form-control" id="hostel_fee" name="hostel_fee" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="caution_money" class="form-label">Caution Money*</label>
                        {{-- Changed input to readonly --}}
                        <input type="number" class="form-control" id="caution_money" name="caution_money" readonly>
                    </div>

                    <div class="mb-3 " style="display:none;">
                        <label for="total_amount" class="form-label">Total Amount</label>
                        <input type="number" class="form-control" id="total_amount" name="total_amount" readonly>
                        <small class="form-text text-muted">This field is calculated and cannot be directly edited.</small>
                    </div>

                    <div class="mb-3">
                        <label for="facility" class="form-label">Facility</label>
                        {{-- Changed input to readonly --}}
                        <input type="text" class="form-control" id="facility" name="facility" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="approved_by" class="form-label">Approved By</label>
                        {{-- Changed input to readonly --}}
                        <input type="text" class="form-control" id="approved_by" name="approved_by" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        {{-- Changed textarea to readonly --}}
                        <textarea class="form-control" id="remarks" name="remarks" readonly></textarea>
                    </div>

                    {{-- Added Start Date and End Date fields --}}
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" readonly>
                    </div>
 
                    <div class="mb-3">
                        <label for="document" class="form-label">Upload Document</label>
                        {{-- Removed input type="file" as it's not editable, display only --}}
                        <small class="form-text text-muted" id="currentDocumentInfo"></small>
                    </div>

                    <div class="mb-3 text-danger" id="editAmountErrors"></div>

                    {{-- Removed Save Changes and Cancel buttons --}}
                </div>
            </div>
        </div>
    </div>


    {{-- New Modal for Update Status and Remark --}}
    <div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateStatusModalLabel">Update Guest Status & Add Remark</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" name="guest_id" id="status_guest_id">
                        <div class="mb-3">
                            <label for="guest_status_select" class="form-label">Select Status:</label>
                            <select class="form-select" id="guest_status_select" name="status" >
                                <option value="">-- Select Status --</option>
                                <option value="accountant_reject">Reject By Accountant</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="account_remark" class="form-label">Account Remark:</label>
                            <textarea class="form-control" id="account_remark" name="account_remark" rows="3"></textarea>
                        </div>
                        <div id="updateStatusErrors" class="alert alert-danger d-none"></div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- Accessory Details Modal (Keeping existing for completeness) --}}
    <div class="modal fade" id="accessoryModal" tabindex="-1" role="dialog" aria-labelledby="accessoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Accessories</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="accessoryList">
                </div>
            </div>
        </div>
    </div>

</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Function to show a custom message box
    function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
        const messageContainer = document.getElementById(targetElementId);
        if (messageContainer) {
            messageContainer.innerHTML = ""; // Clear previous messages
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            messageContainer.appendChild(alertDiv);
            setTimeout(() => alertDiv.remove(), 3000); // Remove after 3 seconds
        } else {
            console.warn(`Message container #${targetElementId} not found.`);
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        fetchGuestsForAccountant();

        // Initialize both modals
        let editAmountModal = new bootstrap.Modal(document.getElementById('editAmountModal'), {
            backdrop: 'static',
            keyboard: false
        });
        let updateStatusModal = new bootstrap.Modal(document.getElementById('updateStatusModal'));

        function fetchGuestsForAccountant() {
            let guestList = document.getElementById("guestList");
            guestList.innerHTML = `<tr><td colspan="8" class="text-center">Loading guests...</td></tr>`; // Updated colspan

            // Adjust API endpoint if you have a specific one for accountants to fetch all relevant guests
            fetch("{{ url('/api/accountant/guests/pending') }}") // Consider an endpoint that fetches all relevant guests, e.g., /api/accountant/guests
                .then(response => response.json())
                .then(response => {
                    const guests = response.data;
                    guestList.innerHTML = "";

                    if (!response.success || !Array.isArray(guests) || guests.length === 0) {
                        guestList.innerHTML = `<tr><td colspan="8" class="text-center">No guests found.</td></tr>`; // Updated colspan
                        if (!response.success && response.message) {
                            showCustomMessageBox(response.message, 'danger');
                        }
                        return;
                    }

                    guests.forEach((guest, index) => { // Added index for Serial No
                        let accessoriesButton = `<button class="btn btn-info btn-sm" onclick='viewAccessories(${JSON.stringify(guest.accessories || [])})'>View</button>`;

                        const feeWaiverStatus = guest.fee_waiver ? 'Yes' : 'No';
                        const remarksContent = guest.remarks || 'N/A'; // General guest remarks

                        let attachmentLink = 'N/A';
                        if (guest.attachment_path) {
                            attachmentLink = `<a href="{{ asset('storage/') }}/${guest.attachment_path}" target="_blank" class="btn btn-sm btn-secondary">View</a>`;
                        }

                        guestList.innerHTML += `
                            <tr id="guest-${guest.id}">
                                <td>${index + 1}</td> {{-- Displaying Serial No --}}
                                <td>${guest.scholar_no || 'N/A'}</td>
                                <td>${guest.name || '-'}</td>
                                <td>${feeWaiverStatus}</td>
                                <td>${remarksContent}</td>
                                <td>${attachmentLink}</td>
                                <td>${accessoriesButton}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm mb-1" onclick="showAdjustPaymentModal(${guest.id})">View Details</button>
                                    <button class="btn btn-info btn-sm mb-1" onclick="showUpdateStatusModal(${guest.id}, '${guest.status || ''}', '${guest.fee_exception ? (guest.fee_exception.account_remark || '') : ''}')">Update Status</button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error fetching guests:', error);
                    guestList.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Failed to load guests.</td></tr>`; // Updated colspan
                    showCustomMessageBox('Failed to load guests.', 'danger');
                });
        }

        // --- Functions for Edit Amount Modal ---
        window.showAdjustPaymentModal = function(guestId) {
            const guestIdInput = document.getElementById('edit_guest_id');
            const hostelFeeInput = document.getElementById('hostel_fee');
            const cautionMoneyInput = document.getElementById('caution_money');
            const totalAmountInput = document.getElementById('total_amount');
            const facilityInput = document.getElementById('facility');
            const remarksInput = document.getElementById('remarks');
            const approvedByInput = document.getElementById('approved_by');
            const currentDocumentInfo = document.getElementById('currentDocumentInfo');
            const errorBox = document.getElementById('editAmountErrors');
            // NEW: Get references to the start_date and end_date input fields
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            // Reset form fields and error messages
            guestIdInput.value = guestId;
            hostelFeeInput.value = '';
            cautionMoneyInput.value = '';
            totalAmountInput.value = '';
            facilityInput.value = '';
            remarksInput.value = '';
            approvedByInput.value = '';
            currentDocumentInfo.textContent = '';
            startDateInput.value = ''; // Reset start_date
            endDateInput.value = ''; // Reset end_date
            errorBox.textContent = '';
            errorBox.classList.add('d-none');

            // Fetch current payment details for the guest using the new API
            fetch(`/api/guest/${guestId}/fee-exception`)
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data) {
                        const feeExceptionDetails = response.data;
                        hostelFeeInput.value = feeExceptionDetails.hostel_fee || '';
                        cautionMoneyInput.value = feeExceptionDetails.caution_money || '';
                        totalAmountInput.value = feeExceptionDetails.total_amount || '';
                        facilityInput.value = feeExceptionDetails.facility || '';
                        remarksInput.value = feeExceptionDetails.remarks || '';
                        approvedByInput.value = feeExceptionDetails.approved_by || '';
                        // NEW: Populate start_date and end_date fields
                        startDateInput.value = feeExceptionDetails.start_date || '';
                        endDateInput.value = feeExceptionDetails.end_date || '';

                        // Display existing document path/URL if available
                        if (feeExceptionDetails.document_url) {
                            currentDocumentInfo.innerHTML = `
        <button class="btn btn-secondary btn-sm" onclick="window.open('${feeExceptionDetails.document_url}', '_blank')">
            View Document
        </button>
    `;
                        } else {
                            currentDocumentInfo.textContent = 'No document uploaded.';
                        }

                    } else if (!response.success && response.message) {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = response.message || 'No existing fee exception details found for this guest.';
                    } else {
                        errorBox.classList.remove('d-none');
                        errorBox.textContent = 'Failed to load fee exception details for this guest.';
                    }
                })
                .catch(error => {
                    console.error('Error fetching fee exception details:', error);
                    errorBox.classList.remove('d-none');
                    errorBox.textContent = 'Error fetching fee exception details. Please try again.';
                });

            editAmountModal.show();
        };

        // The form submission listener for editAmountForm is no longer needed if fields are readonly
        // and buttons are removed. However, if you intend to re-enable saving via other means
        // or a different modal, keep this logic in mind.
        const editAmountForm = document.getElementById('editAmountForm');
        if (editAmountForm) {
            editAmountForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                // This block will no longer be reached if submit button is removed.
                // Keep it commented or remove it if not needed in the future.
            });
        }


        // --- Functions for Update Status Modal ---
        const updateStatusForm = document.getElementById('updateStatusForm');
        const statusGuestIdInput = document.getElementById('status_guest_id');
        const guestStatusSelect = document.getElementById('guest_status_select');
        const accountRemarkTextarea = document.getElementById('account_remark');
        const updateStatusErrors = document.getElementById('updateStatusErrors');

        window.showUpdateStatusModal = function(guestId, currentStatus, currentAccountRemark) {
            statusGuestIdInput.value = guestId;
            guestStatusSelect.value = currentStatus;
            accountRemarkTextarea.value = currentAccountRemark;
            updateStatusErrors.classList.add('d-none'); // Hide errors on open
            updateStatusErrors.innerHTML = '';
            updateStatusModal.show();
        };

        updateStatusForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(updateStatusForm);
            const guestId = formData.get('guest_id');
            const status = formData.get('status');
            const accountRemark = formData.get('account_remark');

            if (!status) {
                updateStatusErrors.classList.remove('d-none');
                updateStatusErrors.innerHTML = 'Please select a status.';
                return;
            }

            try {
                const response = await fetch("{{ url('/api/accountant/update-guest-status') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        guest_id: guestId,
                        status: status,
                        account_remark: accountRemark
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showCustomMessageBox(result.message, 'success');
                    updateStatusModal.hide(); // Hide the modal
                    fetchGuestsForAccountant(); // Refresh the table
                } else {
                    updateStatusErrors.classList.remove('d-none');
                    updateStatusErrors.innerHTML = result.message || 'Failed to update status.';
                    if (result.errors) {
                        for (const key in result.errors) {
                            updateStatusErrors.innerHTML += `<br>${result.errors[key].join(', ')}`;
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                updateStatusErrors.classList.remove('d-none');
                updateStatusErrors.innerHTML = 'An unexpected error occurred. Please try again.';
            }
        });

        // --- Accessory Modal (no changes) ---
        window.viewAccessories = function(accessories) {
            const accessoryList = document.getElementById("accessoryList");
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
                                    <td>${item.pivot ? item.pivot.to_date : 'N/A'}</td>
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
    });
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
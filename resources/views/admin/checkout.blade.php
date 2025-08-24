@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Admin Checkout Requests</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered" id="checkoutRequestsTable">
        <thead>
            <tr>
                <th>S.No</th>
                <th>Resident Name</th>
                <th>Checkout Date</th>
                <th>Reason</th>
                <th>Account Approval</th>
                <th>Admin Approval</th>
                <th>View Accessories</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <p id="noRequestsMessage" style="display: none;">No checkout requests found.</p>

    {{-- Removed the button to clear local storage for debugging --}}
</div>

<div class="modal fade" id="accessoryModal" tabindex="-1" aria-labelledby="accessoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accessoryModalLabel">Resident Accessories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="accessoryModalBody">
                {{-- Accessories will be loaded here --}}
                <div id="accessoryMessage" style="margin-top: 10px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitAccessoriesBtn">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
    // submittedAccessories will store resident IDs for which accessories have been submitted
    let submittedAccessories = new Set();
    // submittedData will store the actual submitted accessory data for each resident
    let submittedData = {};

    document.addEventListener('DOMContentLoaded', function() {
        // Load submitted data from localStorage on page load
        const storedSubmittedData = localStorage.getItem('submittedData');
        if (storedSubmittedData) {
            submittedData = JSON.parse(storedSubmittedData);
        }

        // Load submitted residents from localStorage
        const storedSubmittedAccessories = localStorage.getItem('submittedAccessories');
        if (storedSubmittedAccessories) {
            submittedAccessories = new Set(JSON.parse(storedSubmittedAccessories));
        }

        // Fetch and display checkout requests
        fetchAdminCheckoutRequests();

        // Removed event listener for the "Clear Stored Data" button
    });

    /**
     * Clears the accessory submission data from localStorage and refreshes the table.
     * This function is no longer called by a button, but kept for reference if needed elsewhere.
     */
    function clearLocalStorageData() {
        localStorage.removeItem('submittedData');
        localStorage.removeItem('submittedAccessories');
        submittedData = {};
        submittedAccessories = new Set();
        fetchAdminCheckoutRequests(); // Refresh the table to reflect the cleared state
        showCustomMessage('Stored accessory data cleared successfully!', 'info');
    }

    /**
     * Fetches admin checkout requests from the API and populates the table.
     */
    function fetchAdminCheckoutRequests() {
        fetch('/api/admin/resident/all-checkout-requests', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')  
            }
        })
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector('#checkoutRequestsTable tbody');
                const noRequestsMessage = document.getElementById('noRequestsMessage');

                // Check if data is available and not empty
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = ''; // Clear existing rows
                    noRequestsMessage.style.display = 'block'; // Show "No requests" message
                    return;
                }

                noRequestsMessage.style.display = 'none'; // Hide "No requests" message
                tbody.innerHTML = ''; // Clear existing rows before populating

                // Iterate through each request and create a table row
                data.data.forEach((request, index) => {
                    const row = tbody.insertRow();
                    row.insertCell().innerText = index + 1;
                    row.insertCell().innerText = request.resident_name ?? 'N/A';
                    row.insertCell().innerText = request.checkout_date;
                    row.insertCell().innerText = request.reason;

                    // Display account approval status
                    const accountStatusCell = row.insertCell();
                    const accountStatus = request.account_approval;
                    if (accountStatus === 'approved') {
                        accountStatusCell.innerHTML = '<span class="badge bg-success">Approved</span>';
                    } else if (accountStatus === 'denied') {
                        accountStatusCell.innerHTML = '<span class="badge bg-danger">Denied</span>';
                    } else {
                        accountStatusCell.innerHTML = '<span class="badge bg-secondary">Pending</span>';
                    }

                    // Display admin approval status or action buttons
                    const adminApprovalCell = row.insertCell();
                    if (request.admin_approval === 'approved') {
                        adminApprovalCell.innerHTML = '<span class="badge bg-success">Approved</span>';
                    } else if (request.admin_approval === 'denied') {
                        adminApprovalCell.innerHTML = '<span class="badge bg-danger">Denied</span>';
                    } else {
                        // Only show approve/deny buttons if account approval is approved
                        if (accountStatus === 'approved') {
                            const approveBtn = document.createElement('button');
                            approveBtn.className = 'btn btn-success btn-sm me-1';
                            approveBtn.textContent = 'Approve';
                            approveBtn.onclick = () => updateApproval(request.checkout_id, 'approved', adminApprovalCell);

                            const denyBtn = document.createElement('button');
                            denyBtn.className = 'btn btn-danger btn-sm';
                            denyBtn.textContent = 'Deny';
                            denyBtn.onclick = () => updateApproval(request.checkout_id, 'denied', adminApprovalCell);

                            adminApprovalCell.appendChild(approveBtn);
                            adminApprovalCell.appendChild(denyBtn);
                        } else {
                            adminApprovalCell.innerHTML = '<span class="text-muted">Wait for Account Approval</span>';
                        }
                    }

                    // Create "View Accessories" button
                    const viewCell = row.insertCell();
                    const viewButton = document.createElement('button');
                    viewButton.className = 'btn btn-primary btn-sm';
                    viewButton.textContent = 'View';
                    viewButton.onclick = () => viewAccessories(request.resident_id);

                    // The "View" button will always be clickable.
                    viewCell.appendChild(viewButton);
                });
            })
            .catch(error => {
                console.error('Error fetching checkout requests:', error);
                // Display a message to the user if there's an error
                const tbody = document.querySelector('#checkoutRequestsTable tbody');
                tbody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">Failed to load checkout requests. Please try again.</td></tr>`;
                document.getElementById('noRequestsMessage').style.display = 'none';
            });
    }

    /**
     * Updates the admin approval status for a checkout request.
     * @param {number} checkoutId - The ID of the checkout request.
     * @param {string} status - The new approval status ('approved' or 'denied').
     * @param {HTMLElement} actionCell - The table cell to update with the new status badge.
     */
    function updateApproval(checkoutId, status, actionCell) {
        fetch(`/api/admin/checkout/admin-approval/${checkoutId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Update the UI with the new status badge
                if (status === 'approved') {
                    actionCell.innerHTML = '<span class="badge bg-success">Approved</span>';
                } else if (status === 'denied') {
                    actionCell.innerHTML = '<span class="badge bg-danger">Denied</span>';
                }
                // Optionally, show a temporary success message
                // For this example, we're just updating the badge directly.
            } else {
                // Use a custom message box or alert for error
                showCustomMessage('Failed to update admin approval.', 'danger');
                console.error('API Error:', result.message || 'Unknown error');
            }
        })
        .catch(error => {
            showCustomMessage('Error updating admin approval. Please check console for details.', 'danger');
            console.error('Fetch Error:', error);
        });
    }

    /**
     * Displays a custom message in the modal or on the page.
     * @param {string} message - The message to display.
     * @param {string} type - The type of message ('success', 'danger', 'info').
     */
    function showCustomMessage(message, type) {
        const messageDiv = document.getElementById('accessoryMessage'); // Reusing this div for general messages
        if (messageDiv) {
            messageDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                        ${message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
        }
    }

    /**
     * Fetches and displays accessories for a given resident in a modal.
     * @param {number} residentId - The ID of the resident.
     */
    function viewAccessories(residentId) {
        // Clear previous messages
        document.getElementById('accessoryMessage').innerHTML = '';

        fetch(`/api/admin/accessories/${residentId}`, {
            method: 'GET',
            headers: {      
                'Content-Type': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => response.json())
            .then(apiResponse => {
                const modalBody = document.getElementById('accessoryModalBody');
                const submitButton = document.getElementById('submitAccessoriesBtn');

                // IMPORTANT: Access data.accessories as per the API response structure
                const accessories = apiResponse.data && apiResponse.data.accessories ? apiResponse.data.accessories : [];

                if (accessories.length > 0) {
                    let accessoriesHtml = `
                        <h5>Accessories</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Accessory Name</th>
                                    <th>Return</th>
                                    <th>Remark</th>
                                    <th>Debit Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    // Check if accessories for this resident have already been submitted
                    const isResidentSubmitted = submittedAccessories.has(residentId);
                    const residentSubmittedData = submittedData[residentId] || {};

                    accessories.forEach(acc => {
                        // Get previously submitted values for pre-filling
                        const prevSubmittedAcc = residentSubmittedData.accessories ? 
                                                 residentSubmittedData.accessories.find(a => a.accessory_head_id === acc.accessory_head.id) : 
                                                 null;
                        const isReturned = prevSubmittedAcc ? prevSubmittedAcc.is_returned : null;
                        const remark = prevSubmittedAcc ? prevSubmittedAcc.remark : '';
                        const debitAmount = prevSubmittedAcc ? prevSubmittedAcc.debit_amount : 0;

                        accessoriesHtml += `
                            <tr>
                                <td>${acc.accessory_head.name}</td>
                                <td>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input return-radio" type="radio" 
                                               id="return_yes_${acc.accessory_head.id}" 
                                               name="return_${acc.accessory_head.id}" value="yes" 
                                               ${isReturned === true ? 'checked' : ''} 
                                               ${isResidentSubmitted ? 'disabled' : ''}>
                                        <label class="form-check-label" for="return_yes_${acc.accessory_head.id}">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input return-radio" type="radio" 
                                               id="return_no_${acc.accessory_head.id}" 
                                               name="return_${acc.accessory_head.id}" value="no" 
                                               ${isReturned === false ? 'checked' : ''} 
                                               ${isResidentSubmitted ? 'disabled' : ''}>
                                        <label class="form-check-label" for="return_no_${acc.accessory_head.id}">No</label>
                                    </div>
                                </td>
                                <td><input type="text" class="form-control remark-input" 
                                           id="remark_${acc.accessory_head.id}" value="${remark || ''}" 
                                           ${isResidentSubmitted ? 'disabled' : ''}></td>
                                <td><input type="number" class="form-control debit-input" 
                                           id="debit_amount_${acc.accessory_head.id}" value="${debitAmount || 0}" 
                                           ${isResidentSubmitted ? 'disabled' : ''}></td>
                            </tr>
                        `;
                    });
                    accessoriesHtml += `</tbody></table>`;
                    
                    // Update modal body and ensure message div is present
                    modalBody.innerHTML = accessoriesHtml + `<div id="accessoryMessage" style="margin-top: 10px;"></div>`;

                    // Set submit button functionality and disable if already submitted
                    submitButton.onclick = () => submitAccessories(residentId);
                    if (isResidentSubmitted) {
                        submitButton.disabled = true;
                        submitButton.classList.add('disabled');
                    } else {
                        submitButton.disabled = false;
                        submitButton.classList.remove('disabled');
                    }

                } else {
                    // No accessories found for this resident
                    modalBody.innerHTML = '<p>No accessories found for this resident.</p><div id="accessoryMessage" style="margin-top: 10px;"></div>';
                    submitButton.disabled = true; // Disable submit button if no accessories to submit
                    submitButton.classList.add('disabled');
                }

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('accessoryModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error fetching accessories:', error);
                // Display error message in the modal body
                const modalBody = document.getElementById('accessoryModalBody');
                modalBody.innerHTML = '<div class="text-danger">Failed to fetch accessories for this resident. Please try again.</div><div id="accessoryMessage" style="margin-top: 10px;"></div>';
                const submitButton = document.getElementById('submitAccessoriesBtn');
                submitButton.disabled = true;
                submitButton.classList.add('disabled');
                const modal = new bootstrap.Modal(document.getElementById('accessoryModal'));
                modal.show();
            });
    }

    /**
     * Submits the accessory checking data for a resident.
     * @param {number} residentId - The ID of the resident.
     */
    function submitAccessories(residentId) {
        const accessories = [];
        // Use a Set to track processed accessory_head_ids to avoid duplicates
        const handledAccessoryIds = new Set(); 

        document.querySelectorAll('.return-radio:checked').forEach(radio => {
            const accessoryHeadId = parseInt(radio.name.split('_')[1]);
            
            // Only process if this accessory_head_id hasn't been handled yet
            if (handledAccessoryIds.has(accessoryHeadId)) {
                return;
            }
            handledAccessoryIds.add(accessoryHeadId);

            const isReturned = radio.value === 'yes';
            const remarkInput = document.getElementById(`remark_${accessoryHeadId}`);
            const debitAmountInput = document.getElementById(`debit_amount_${accessoryHeadId}`);

            const remark = remarkInput ? remarkInput.value : '';
            const debitAmount = debitAmountInput ? parseFloat(debitAmountInput.value) : 0;

            accessories.push({
                accessory_head_id: accessoryHeadId,
                is_returned: isReturned,
                remark: remark,
                debit_amount: debitAmount
            });
        });

        const messageDiv = document.getElementById('accessoryMessage');
        messageDiv.innerHTML = ''; // Clear previous messages

        fetch(`/api/admin/accessory/checking/${residentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify({ accessories })
        })
        .then(async response => {
            const result = await response.json();

            if (response.ok && result.message) {
                messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;

                // Add residentId to the set of submitted accessories
                submittedAccessories.add(residentId);
                // Store the submitted data for this resident
                submittedData[residentId] = { accessories: accessories };

                // Save updated state to localStorage
                localStorage.setItem('submittedData', JSON.stringify(submittedData));
                localStorage.setItem('submittedAccessories', JSON.stringify(Array.from(submittedAccessories)));

                // Disable inputs and submit button in the modal after successful submission
                document.querySelectorAll('#accessoryModalBody input').forEach(input => {
                    input.disabled = true;
                });
                const submitButton = document.getElementById('submitAccessoriesBtn');
                submitButton.disabled = true;
                submitButton.classList.add('disabled');

                // Refresh the main table to update the "View" button status
                // (Though in this version, the View button is always enabled, 
                // this call ensures consistency if other parts of the table change)
                fetchAdminCheckoutRequests();

                // Close modal after a short delay
                setTimeout(() => {
                    const modalEl = document.getElementById('accessoryModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                    messageDiv.innerHTML = ''; // Clear message after modal closes
                }, 2000);

            } else {
                messageDiv.innerHTML = `<div class="alert alert-danger">Failed to update accessories: ${result.message || 'Unknown error'}. Please try again.</div>`;
            }
        })
        .catch(error => {
            console.error('Error submitting accessories:', error);
            messageDiv.innerHTML = `<div class="alert alert-danger">Failed to update accessories. Please try again.</div>`;
        });
    }
</script>
@endsection

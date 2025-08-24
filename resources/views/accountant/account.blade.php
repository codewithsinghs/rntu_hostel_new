@extends('accountant.layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">

    <h2 class="mb-4">Checkout Requests</h2>

    <table class="table table-bordered" id="checkoutRequestsTable">
        <thead>
            <tr>
                <th>Serial No.</th>
                <th>Resident Name</th>
                <th>Checkout Date</th>
                <th>Reason</th>
                <th>Action</th>
                <th>Account Approval Status</th>
                <th>Accessories Check</th>
                <th>View Details</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="8">Loading checkout requests...</td></tr>
        </tbody>
    </table>

    <p id="noRequestsMessage" style="display: none;">No checkout requests found.</p>

    <div class="modal fade" id="checkoutDetailsModal" tabindex="-1" aria-labelledby="checkoutDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutDetailsModalLabel">Checkout Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="checkoutDetailsContent">
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetchCheckoutRequests();
    });

    async function fetchCheckoutRequests() {
        const tbody = document.querySelector('#checkoutRequestsTable tbody');
        const noRequestsMessage = document.getElementById('noRequestsMessage');

        tbody.innerHTML = '<tr><td colspan="8">Loading checkout requests...</td></tr>';

        try {
            const response = await fetch('http://127.0.0.1:8000/api/checkout-requests');
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error(`HTTP error! status: ${response.status}. Access Forbidden. Check CORS configuration on your Laravel backend.`);
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const apiResponse = await response.json();

            if (apiResponse.success === false) {
                console.error('API Error:', apiResponse.message || 'Failed to load checkout requests.');
                tbody.innerHTML = '<tr><td colspan="8">Failed to load checkout requests.</td></tr>';
                noRequestsMessage.style.display = 'none';
                return;
            }

            const requests = apiResponse.data;

            if (!requests || !Array.isArray(requests) || requests.length === 0) {
                tbody.innerHTML = '';
                noRequestsMessage.style.display = 'block';
                return;
            }

            noRequestsMessage.style.display = 'none';
            tbody.innerHTML = '';

            requests.forEach((request, index) => {
                const row = tbody.insertRow();
                row.insertCell().textContent = index + 1;
                row.insertCell().textContent = request.resident_name ?? 'N/A';
                row.insertCell().textContent = request.checkout_date ?? 'N/A';
                row.insertCell().textContent = request.reason ?? 'N/A';

                const actionCell = row.insertCell();
                const currentStatus = request.account_approval;

                if (currentStatus === 'approved') {
                    actionCell.innerHTML = `<span class="badge bg-success">Approved</span>`;
                } else if (currentStatus === 'denied') {
                    actionCell.innerHTML = `<span class="badge bg-danger">Denied</span>`;
                } else {
                    const approveBtn = document.createElement('button');
                    approveBtn.className = 'btn btn-success btn-sm me-1';
                    approveBtn.textContent = 'Approve';
                    approveBtn.onclick = () => handleApproval(request.checkout_id, 'approved', actionCell);

                    const denyBtn = document.createElement('button');
                    denyBtn.className = 'btn btn-danger btn-sm';
                    denyBtn.textContent = 'Deny';
                    denyBtn.onclick = () => handleApproval(request.checkout_id, 'denied', actionCell);

                    actionCell.appendChild(approveBtn);
                    actionCell.appendChild(denyBtn);
                }
                row.insertCell().textContent = request.account_approval ?? 'N/A';
                row.insertCell().textContent = request.action ?? 'N/A'; // Display 'action' in the Accessories Check column

                const detailsCell = row.insertCell();
                const viewDetailsBtn = document.createElement('button');
                viewDetailsBtn.className = 'btn btn-info btn-sm';
                viewDetailsBtn.textContent = 'View Details';
                // Pass the entire 'request' object to showCheckoutDetails
                viewDetailsBtn.onclick = () => showCheckoutDetails(request);
                detailsCell.appendChild(viewDetailsBtn);
            });
        } catch (error) {
            console.error('Error fetching checkout requests:', error);
            tbody.innerHTML = `<tr><td colspan="8">Error loading data: ${error.message}. Please check your server and network.</td></tr>`;
        }
    }

    async function handleApproval(checkoutId, status, actionCell) {
        try {
            const response = await fetch(`http://127.0.0.1:8000/api/checkout/account-approval/${checkoutId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ status: status })
            });

            const result = await response.json();

            if (response.ok && result.success === true) {
                if (status === 'approved') {
                    actionCell.innerHTML = `<span class="badge bg-success">Approved</span>`;
                } else if (status === 'denied') {
                    actionCell.innerHTML = `<span class="badge bg-danger">Denied</span>`;
                }
                console.log(result.message || `Checkout request ${status} successfully.`);
                fetchCheckoutRequests(); // Re-fetch to update the table
            } else {
                console.error('API Error:', result.message || `HTTP error: ${response.status} - Failed to update status.`);
            }
        } catch (error) {
            console.error('Error updating status:', error);
        }
    }

    /**
     * Fetches and displays detailed checkout information for a resident in a modal.
     * This function now calculates deposited, total debit, and remaining deposit amounts.
     * @param {object} requestData - The full checkout request object from the main table.
     */
    async function showCheckoutDetails(requestData) {
        const modalBody = document.getElementById('checkoutDetailsContent');
        modalBody.innerHTML = '<p>Loading details...</p>'; // Show loading message

        console.log("Displaying details for Checkout Request (from main table):", requestData);

        try {
            // Fetch accessory logs using the resident_id from the passed requestData
            const response = await fetch(`http://127.0.0.1:8000/api/resident-checkout-logs/${requestData.resident_id}`);
            if (!response.ok) {
                if (response.status === 403) {
                    throw new Error(`HTTP error! status: ${response.status}. Access Forbidden. Check CORS configuration on your Laravel backend.`);
                }
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const apiResponse = await response.json();

            console.log("API response for accessory logs:", apiResponse);

            // Accessory logs are expected to be in apiResponse.data based on your controller's current output
            const accessoryLogs = Array.isArray(apiResponse.data) ? apiResponse.data : [];

            console.log("Parsed Accessory Logs:", accessoryLogs);

            // --- Calculations for Checkout Details ---
            const depositedAmount = 10000; // Fixed deposited amount as requested
            let totalDebit = 0;

            accessoryLogs.forEach(log => {
                const debit = parseFloat(log.debit_amount);
                if (!isNaN(debit)) {
                    totalDebit += debit;
                }
            });

            const remainingDeposit = depositedAmount - totalDebit;
            // --- End Calculations ---

            let detailsHTML = '';

            // Display Resident Information using data from the main table's requestData
            detailsHTML += `
                <h4>Resident Information</h4>
                <p><strong>Name:</strong> ${requestData.resident_name ?? 'N/A'}</p>
                <hr>
            `;

            // Display Checkout Details using calculated values and data from requestData
            detailsHTML += `
                <h4>Checkout Details</h4>
                <p><strong>Checkout Date:</strong> ${requestData.checkout_date ?? 'N/A'}</p>
                <p><strong>Reason:</strong> ${requestData.reason ?? 'N/A'}</p>
                <p><strong>Deposited Amount:</strong> ${depositedAmount.toFixed(2)}</p> <p><strong>Total Debit:</strong> ${totalDebit.toFixed(2)}</p> <p><strong>Remaining Deposit:</strong> ${remainingDeposit.toFixed(2)}</p> <p><strong>Admin Approval:</strong> ${requestData.admin_approval ?? 'N/A'}</p>
                <p><strong>Account Approval:</strong> ${requestData.account_approval ?? 'N/A'}</p>
                <p><strong>Remarks:</strong> ${requestData.remark ?? 'N/A'}</p>
                <p><strong>Action:</strong> ${requestData.action ?? 'N/A'}</p>
                <hr>
            `;

            // Display Accessory Logs
            if (accessoryLogs.length > 0) {
                detailsHTML += `
                    <h4>Accessory Logs</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Accessory Name</th>
                                <th>Debit Amount</th>
                                <th>Returned</th>
                                <th>Remark</th>
                                <th>Logged At</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                // Fetch active accessories to map IDs to names
                const accessoriesResponse = await fetch('http://127.0.0.1:8000/api/accessories/active');
                if (!accessoriesResponse.ok) {
                    console.warn('Could not fetch active accessories. Accessory names might be "Unknown".');
                }
                const accessoriesData = await accessoriesResponse.json();
                const accessoryHeadNames = {};

                if (accessoriesData && accessoriesData.data && Array.isArray(accessoriesData.data)) {
                    accessoriesData.data.forEach(accessory => {
                        if (accessory.accessory_head && accessory.accessory_head.id) {
                            accessoryHeadNames[accessory.accessory_head.id] = accessory.accessory_head.name;
                        }
                    });
                }

                accessoryLogs.forEach(log => {
                    const accessoryName = accessoryHeadNames[log.accessory_head_id] || log.accessory_name || 'Unknown';
                    detailsHTML += `
                                <tr>
                                    <td>${accessoryName}</td>
                                    <td>${parseFloat(log.debit_amount).toFixed(2) ?? 'N/A'}</td>
                                    <td>${log.is_returned ? 'Yes' : 'No'}</td>
                                    <td>${log.remark ?? 'N/A'}</td>
                                    <td>${log.logged_at ?? 'N/A'}</td>
                                </tr>
                    `;
                });
                detailsHTML += `
                        </tbody>
                    </table>
                `;
            } else {
                detailsHTML += `<p>No accessory logs found for this checkout.</p>`;
            }

            modalBody.innerHTML = detailsHTML;

            const checkoutDetailsModal = new bootstrap.Modal(document.getElementById('checkoutDetailsModal'));
            checkoutDetailsModal.show();

        } catch (error) {
            console.error('Error fetching checkout details:', error);
            modalBody.innerHTML = `<p class="text-danger">Failed to fetch checkout details: ${error.message}. Please try again.</p>`;
            const checkoutDetailsModal = new bootstrap.Modal(document.getElementById('checkoutDetailsModal'));
            checkoutDetailsModal.show();
        }
    }
</script>
@endsection

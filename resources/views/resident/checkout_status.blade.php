@extends('resident.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Checkout Status</h2>

    <div id="checkoutTableContainer">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Reason</th>
                    <th>Account Approval</th>
                    <th>Admin Approval</th>
                    <th>Remarks</th>
                    <th>Acessory Check</th>
                    <th>View Details</th>
                </tr>
            </thead>
            <tbody id="checkoutTableBody">
            </tbody>
        </table>
        <p id="noData" style="display:none;">No checkout status found.</p>
        <p id="fetchError" class="text-danger text-center mt-3" style="display:none;"></p>
    </div>

    <div class="modal fade" id="checkoutDetailsModal" tabindex="-1" aria-labelledby="checkoutDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutDetailsModalLabel">Checkout Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="checkoutDetailsContent">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkoutTableBody = document.getElementById('checkoutTableBody');
    const noDataMsg = document.getElementById('noData');
    const fetchErrorMsg = document.getElementById('fetchError');
    const checkoutDetailsModalElement = document.getElementById('checkoutDetailsModal');
    let checkoutDetailsModal;

    // Load Bootstrap dynamically and initialize modal
    const bootstrapScript = document.createElement('script');
    bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js';
    bootstrapScript.onload = () => {
        initializeBootstrap();
    };
    bootstrapScript.onerror = () => {
        console.error("Failed to load Bootstrap from CDN.");
        fetchErrorMsg.textContent = "Error: Bootstrap is required but failed to load. The application may not function correctly.";
        fetchErrorMsg.style.display = 'block';
    };
    document.head.appendChild(bootstrapScript);

    function initializeBootstrap() {
        checkoutDetailsModal = new bootstrap.Modal(checkoutDetailsModalElement);
        fetchCheckoutStatus();
    }

    function fetchCheckoutStatus() {

        fetch(`http://127.0.0.1:8000/api/resident/checkout-status`, {
            method: 'GET',
            headers: {  
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok, status: ${response.status}`);
                }
                return response.json();
            })
            .then(apiResponse => {
                checkoutTableBody.innerHTML = '';
                fetchErrorMsg.style.display = 'none';

                const checkoutData = apiResponse.data;

                if (!checkoutData || Object.keys(checkoutData).length === 0) {
                    noDataMsg.style.display = 'block';
                    return;
                }

                noDataMsg.style.display = 'none';
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${checkoutData.date ?? 'N/A'}</td>
                    <td>${checkoutData.reason ?? 'N/A'}</td>
                    <td><span class="badge ${getBadgeClass(checkoutData.account_approval)}">${capitalize(checkoutData.account_approval)}</span></td>
                    <td><span class="badge ${getBadgeClass(checkoutData.admin_approval)}">${capitalize(checkoutData.admin_approval)}</span></td>
                    <td>${checkoutData.remarks ?? 'N/A'}</td>
                    <td>${formatAction(checkoutData.action)}</td>
                    <td>
                        <button class="btn btn-primary btn-sm view-details-btn">View Details</button>
                    </td>
                `;
                checkoutTableBody.appendChild(row);

                const viewDetailsButton = row.querySelector('.view-details-btn');
                viewDetailsButton.addEventListener('click', () => {
                    showCheckoutDetails(checkoutData);
                });
            })
            .catch(err => {
                console.error('Error fetching checkout status:', err);
                fetchErrorMsg.innerText = `Error fetching checkout status: ${err.message}. Please try again.`;
                fetchErrorMsg.style.display = 'block';
                noDataMsg.style.display = 'none';
            });
    }

    async function showCheckoutDetails(checkoutDetailsFromMainAPI) {
        const checkoutDetailsContent = document.getElementById('checkoutDetailsContent');
        checkoutDetailsContent.innerHTML = '<p>Loading details...</p>';

        try {
            const response = await fetch(`http://127.0.0.1:8000/api/resident/checkout-logs`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            });
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                const errorMessage = errorData.message || `Network response was not ok, status: ${response.status}`;
                throw new Error(errorMessage);
            }
            const apiResponse = await response.json();

            let detailsHTML = '';

            if (apiResponse.success === false) {
                detailsHTML += `<p class="text-danger">${apiResponse.message || 'Failed to fetch checkout details from API.'}</p>`;
            } else {
                // Display Checkout Details
                const depositedAmount = 10000;
                let totalDebit = 0;
                const accessoryLogs = Array.isArray(apiResponse.data) ? apiResponse.data : [];

                accessoryLogs.forEach(log => {
                    const debit = parseFloat(log.debit_amount);
                    if (!isNaN(debit)) {
                        totalDebit += debit;
                    }
                });
                const remainingDeposit = depositedAmount - totalDebit;

                detailsHTML += `
                    <h4>Checkout Details</h4>
                    <p><strong>Date:</strong> ${checkoutDetailsFromMainAPI.date ?? 'N/A'}</p>
                    <p><strong>Reason:</strong> ${checkoutDetailsFromMainAPI.reason ?? 'N/A'}</p>
                    <p><strong>Deposited Amount:</strong> ${depositedAmount.toFixed(2)}</p>
                    <p><strong>Total Debit:</strong> ${totalDebit.toFixed(2)}</p>
                    <p><strong>Remaining Deposit:</strong> ${remainingDeposit.toFixed(2)}</p>
                    <p><strong>Action:</strong> ${formatAction(checkoutDetailsFromMainAPI.action)}</p>
                    <p><strong>Admin Approval:</strong> <span class="badge ${getBadgeClass(checkoutDetailsFromMainAPI.admin_approval)}">${capitalize(checkoutDetailsFromMainAPI.admin_approval)}</span></p>
                    <p><strong>Account Approval:</strong> <span class="badge ${getBadgeClass(checkoutDetailsFromMainAPI.account_approval)}">${capitalize(checkoutDetailsFromMainAPI.account_approval)}</span></p>
                    <p><strong>Remark:</strong> ${checkoutDetailsFromMainAPI.remarks ?? 'N/A'}</p>
                    <hr>
                `;

                // Display Accessory Logs from apiResponse.data
                if (accessoryLogs.length > 0) {
                    detailsHTML += `
                        <h4>Accessory Logs</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Accessory Name</th>
                                    <th>Returned</th>
                                    <th>Debit Amount</th>
                                    <th>Remark</th>
                                    <th>Logged At</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    const accessoriesResponse = await fetch('http://127.0.0.1:8000/api/accessories/active');
                    if (!accessoriesResponse.ok) {
                        console.warn(`Failed to fetch active accessories, status: ${accessoriesResponse.status}. Accessory names might be "Unknown".`);
                    }
                    const accessoriesData = await accessoriesResponse.json();
                    const accessoryHeadNames = {};

                    if (accessoriesData && accessoriesData.data) {
                        accessoriesData.data.forEach(accessory => {
                            if (accessory.accessory_head && accessory.accessory_head.id) {
                                accessoryHeadNames[accessory.accessory_head.id] = accessory.accessory_head.name;
                            }
                        });
                    }

                    accessoryLogs.forEach(log => {
                        const accessoryName = log.accessory_name || accessoryHeadNames[log.accessory_head_id] || 'Unknown';
                        detailsHTML += `
                                <tr>
                                    <td>${accessoryName}</td>
                                    <td>${log.is_returned ? 'Yes' : 'No'}</td>
                                    <td>${parseFloat(log.debit_amount).toFixed(2) ?? '0.00'}</td>
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
            }

            checkoutDetailsContent.innerHTML = detailsHTML;
            checkoutDetailsModal.show();

        } catch (error) {
            console.error('Error fetching checkout details:', error);
            checkoutDetailsContent.innerHTML = `<p class="text-danger">Failed to fetch checkout details: ${error.message}. Please try again.</p>`;
            checkoutDetailsModal.show();
        }
    }

    function getBadgeClass(status) {
        switch (status) {
            case 'approved': return 'bg-success';
            case 'denied': return 'bg-danger';
            case 'pending': return 'bg-warning text-dark';
            default: return 'bg-secondary';
        }
    }

    function capitalize(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : 'Pending';
    }

    function formatAction(action) {
        if (!action) return 'N/A';
        return action.replace(/_/g, ' ').replace(/\b\w/g, char => char.toUpperCase());
    }
});
</script>
@endsection
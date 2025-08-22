@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">Pending Payments</h2>

            <div id="mainResponseMessage" class="mt-3"></div> {{-- Message container for the page --}}

            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Pending Payments List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="pendingPaymentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Serial No.</th>
                                <th>Resident Name</th>
                                <th>Total Amount</th>
                                <th>Paid</th>
                                <th>Remaining</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="9" class="text-center">Loading pending payments...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
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

document.addEventListener("DOMContentLoaded", function () {
    fetchPendingPayments();

    function fetchPendingPayments() {
        const apiUrl = "{{ url('/api/admin/allPendingPayments') }}";
        const tableBody = document.getElementById("pendingPaymentsTable").querySelector("tbody");

        fetch(apiUrl, {
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
                    // If the response is not OK, try to parse it as JSON to get the message
                    return response.json().then(errorData => {
                        throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(response => { // Changed 'data' to 'response' for consistency
                // Assuming the payments array is directly under the 'data' key
                const payments = response.data;
                tableBody.innerHTML = "";

                if (!response.success || !Array.isArray(payments) || payments.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="9" class="text-center">No pending payments found.</td></tr>`;
                    if (!response.success && response.message) {
                        showCustomMessageBox(response.message, 'info'); // Use info for "no payments found"
                    }
                    return;
                }

                payments.forEach((payment, index) => {
                    tableBody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td> <td>${payment.resident_name || 'N/A'}</td>
                            <td>${payment.total_amount || 'N/A'}</td>
                            <td>${payment.amount_paid || 'N/A'}</td>
                            <td>${payment.remaining_amount || 'N/A'}</td>
                            <td>${payment.payment_method || 'N/A'}</td>
                            <td>${payment.payment_status || 'N/A'}</td>
                            <td>${payment.due_date ?? 'N/A'}</td>
                            <td>${payment.created_at ? new Date(payment.created_at).toLocaleString() : 'N/A'}</td>
                        </tr>
                    `;
                });
                if (response.message) {
                    showCustomMessageBox(response.message, 'success');
                }
            })
            .catch(error => {
                console.error("Error fetching pending payments:", error);
                tableBody.innerHTML = `
                    <tr><td colspan="9" class="text-center text-danger">Failed to load pending payments.</td></tr>
                `;
                showCustomMessageBox(error.message || "Failed to load pending payments.", 'danger');
            });
    }
});
</script>
@endsection

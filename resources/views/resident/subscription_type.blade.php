@extends('resident.layout')

@section('content')
    @if (session('success'))
        <div class="alert alert-success text-center mt-3">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="text-center mt-3">Your Pending Hostel Fee Payments</h2>

    <div class="container mt-4">
        <table class="table table-bordered table-striped" id="payments-table" style="display: none;">
            <thead class="table-dark">
                <tr>
                    <th>Fee Type</th>
                    <th>Total Amount</th>
                    <th>Amount Paid</th>
                    <th>Remaining Amount</th>
                    <th>Payment Status</th>
                    <th>Payment Method</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="payments-body"></tbody>
        </table>

        <p id="no-payments" class="text-danger text-center mt-3" style="display: none;">No pending hostel payments found.</p>
        <p id="error-message" class="text-danger text-center mt-3" style="display: none;">Error loading data, please try again.</p>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const table = document.getElementById("payments-table");
        const tbody = document.getElementById("payments-body");
        const noData = document.getElementById("no-payments");
        const errorMsg = document.getElementById("error-message");


        tbody.innerHTML = "";
        table.style.display = "none";
        noData.style.display = "none";
        errorMsg.style.display = "none";

        fetch("{{'/api/resident/pending/subscription'}}", {
            method: "GET",
            headers: {
                "Accept": "application/json",       
                
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id') // Include auth-id for authorization     
            }
        })
        
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(apiResponse => {
                const subscriptions = (apiResponse.success && apiResponse.data && apiResponse.data.subscriptions)
                    ? apiResponse.data.subscriptions
                    : [];

                let hasPayments = false;

                subscriptions.forEach(subscription => {
                    // ✅ Skip if subscription type is NOT "Hostel fee"
                    if (subscription.subscription_type !== "Hostel fee") {
                        return;
                    }

                    const feeType = subscription.subscription_type;
                    const subscriptionId = subscription.subscription_id;

                    if (subscription.payments && subscription.payments.length > 0) {
                        const payment = subscription.payments[0]; // show only latest

                        if (payment.payment_status === 'Pending') {
                            hasPayments = true;

                            const row = `
                                <tr>
                                    <td>${feeType}</td>
                                    <td>₹${payment.total_amount ?? '0.00'}</td>
                                    <td>₹${payment.amount ?? '0.00'}</td>
                                    <td>₹${payment.remaining_amount ?? '0.00'}</td>
                                    <td><span class="badge bg-warning text-dark">${payment.payment_status}</span></td>
                                    <td>${payment.payment_method ?? 'N/A'}</td>
                                    <td>${payment.created_at ?? '-'}</td>
                                    <td>
                                        <a href="/resident/subscription_payment?subscription_id=${subscriptionId}&amount=${payment.remaining_amount}" class="btn btn-success btn-sm">Make Payment</a>
                                    </td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        }
                    }
                });

                if (hasPayments) {
                    table.style.display = "table";
                } else {
                    noData.style.display = "block";
                }
            })
            .catch(error => {
                errorMsg.innerText = "Error loading payments: " + error.message;
                errorMsg.style.display = "block";
            });
    });
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection

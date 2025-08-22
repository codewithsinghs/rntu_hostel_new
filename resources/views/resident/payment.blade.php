@extends('resident.layout')

@section('content')
    @if (session('success'))
        <div class="alert alert-success text-center mt-3">
            {{ session('success') }}
        </div>
    @endif

    <h2 class="text-center mt-3">Resident Pending Accessory Payments</h2>

            <hr>

            <div class="container mt-3">
                <table class="table table-bordered table-striped" id="payments-table" style="display: none;">
                    <thead class="table-dark">
                        <tr>
                            <th>Resident Name</th>
                            <th>Total Amount</th>
                            <th>Amount Paid</th>
                            <th>Remaining Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="payments-body">
                    </tbody>
                </table>

                <p id="no-payments" class="text-danger text-center mt-3" style="display: none;">No pending payments found.</p>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    let paymentsTable = document.getElementById("payments-table");
                    let paymentsBody = document.getElementById("payments-body");
                    let noPaymentsMsg = document.getElementById("no-payments");

                    // Get the resident_id of the logged-in user from the Blade context
                    // let residentIdString = "{{ $resident->id ?? '' }}";
                    // let residentId = parseInt(residentIdString);

                    // if (isNaN(residentId) || residentId <= 0) {
                    //     console.log("Resident ID is invalid or not found:", residentIdString);
                    //     noPaymentsMsg.innerText = "Resident ID not found or invalid. Please check your session or authentication.";
                    //     noPaymentsMsg.style.display = "block";
                    //     return;
                    // }

                    paymentsBody.innerHTML = "";
                    paymentsTable.style.display = "none";
                    noPaymentsMsg.style.display = "none";

                    fetch(`/api/resident/accessories`, {
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
                            if (apiResponse.success && apiResponse.data && apiResponse.data.length > 0) {
                                paymentsTable.style.display = "block";

                                apiResponse.data.forEach(payment => {
                                    let amount = parseFloat(payment.amount || 0);
                                    let remainingAmount = parseFloat(payment.remaining_amount || 0);
                                    let totalAmount = amount + remainingAmount;
                                    let paymentStatus = remainingAmount > 0 ? 'Pending' : 'Paid';
                                    let statusBadge = remainingAmount > 0 ? 'bg-warning text-dark' : 'bg-success';

                                    let row = `<tr>
                                        <td>${payment.resident_name || 'N/A'}</td>
                                        <td>${totalAmount.toFixed(2)}</td>
                                        <td>${amount.toFixed(2)}</td>
                                        <td>${remainingAmount.toFixed(2)}</td>
                                        <td><span class="badge ${statusBadge}">${paymentStatus}</span></td>
                                        <td>
                                            ${remainingAmount > 0 
                                                ? `<a href="/resident/payment/${payment.student_accessory_id}" class="btn btn-success btn-sm">Make Payment</a>` 
                                                : `<span class="text-muted">Paid</span>`}
                                        </td>
                                    </tr>`;

                                    paymentsBody.innerHTML += row;
                                });
                            } else {
                                noPaymentsMsg.style.display = "block";
                            }
                        })
                        .catch(error => {
                            console.error("❌ Error fetching payments:", error);
                            noPaymentsMsg.innerText = "Error loading payments. " + error.message;
                            noPaymentsMsg.style.display = "block";
                        });
                });
            </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection

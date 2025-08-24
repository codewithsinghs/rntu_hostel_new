@extends('guest.layout')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .payment-container {
            max-width: 600px;
            margin: auto;
            padding-top: 60px;
        }
        .accessory-total {
            margin-top: -10px;
            font-weight: 600;
        }
    </style>

<div class="container payment-container">
    <div class="card shadow p-4">
        <h3 class="mb-4 text-center">Guest Payment Summary</h3>

        <input type="hidden" id="guest_id">
        <input type="hidden" id="resident_id">
        <input type="hidden" id="accessory_ids">
        <input type="hidden" id="hidden_final_amount">

        <div id="paymentDetails" class="mb-4">
            <div class='alert alert-info text-center'>Loading payment details...</div>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Total Amount (Advance for <span id="monthDisplay"></span> Months) (₹)</label>
            <input type="number" id="amount" class="form-control" readonly>
        </div>

        <button id="payNowBtn" class="btn btn-primary w-100" disabled>Pay Now</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const guestInput = document.getElementById("guest_id");
    const residentInput = document.getElementById("resident_id");
    const accessoryInput = document.getElementById("accessory_ids");
    const amountInput = document.getElementById("amount");
    const hiddenFinalAmountInput = document.getElementById("hidden_final_amount");
    const paymentDetailsEl = document.getElementById("paymentDetails");
    const payNowBtn = document.getElementById("payNowBtn");
    const monthDisplay = document.getElementById("monthDisplay");

    payNowBtn.disabled = true;


    let accessoryIds = [];
    let numberOfMonths = 1;

    fetch(`/api/guest/total-amount`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'token': localStorage.getItem('token'), 
            'auth-id': localStorage.getItem('auth-id')
        }
        })
        .then(res => res.json())
        .then(response => {
            if (!response.success || !response.data) {
                paymentDetailsEl.innerHTML = `<div class='alert alert-warning'>${response.message || 'No guest payment details found.'}</div>`;
                throw new Error(response.message || 'No guest payment details found.');
            }

            const guestData = response.data;

            guestInput.value = guestData.guest_id;
            residentInput.value = guestData.resident_id || '';
            accessoryIds = guestData.accessory_head_ids || [];
            accessoryInput.value = accessoryIds.join(',');

            numberOfMonths = guestData.months || 1;
            const hostelFeeTotal = parseFloat(guestData.hostel_fee) || 0;
            const hostelFeePerMonth = hostelFeeTotal / numberOfMonths;
            const cautionMoney = parseFloat(guestData.caution_money) || 0;
            const accessoryAmount = parseFloat(guestData.total_accessory_amount) || 0;
            const finalTotal = parseFloat(guestData.final_total_amount) || 0;

            amountInput.value = finalTotal.toFixed(2);
            hiddenFinalAmountInput.value = finalTotal.toFixed(2);
            monthDisplay.textContent = numberOfMonths;

            return fetch('/api/guests/accessories/active', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',     
                    'Accept': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
                })
                .then(res => res.json())
                .then(accessoryResponse => {
                    const allAccessories = accessoryResponse.data || [];
                    const matchedAccessories = allAccessories.filter(acc =>
                        accessoryIds.includes(acc.accessory_head_id)
                    );

                    displayPaymentSummary(hostelFeeTotal, cautionMoney, accessoryAmount, numberOfMonths, matchedAccessories);
                    payNowBtn.disabled = false;
                });
        })
        .catch(error => {
            console.error('Error:', error);
            paymentDetailsEl.innerHTML = `<div class='alert alert-danger'>Error loading payment details: ${error.message}</div>`;
            payNowBtn.disabled = true;
        });

    function displayPaymentSummary(hostelFeeTotal, cautionMoney, accessoryAmount, numberOfMonths, matchedAccessories) {
        const accessoriesHtml = matchedAccessories.length > 0
            ? `
                <h5 class="mt-3">Accessory Breakdown</h5>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>S.No.</th>
                            <th>Name</th>
                            <th>Amount (per month)</th>
                            <th>Total (${numberOfMonths} Months)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${matchedAccessories.map((acc, index) => `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${acc.accessory_head?.name || 'N/A'}</td>
                                <td>₹${parseFloat(acc.price).toFixed(2)}</td>
                                <td>₹${(parseFloat(acc.price) * numberOfMonths).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <div class="accessory-total text-start">Total Accessory Charges: ₹${accessoryAmount.toFixed(2)}</div>
            ` : `<p>No optional accessories selected for this guest.</p>`;

        paymentDetailsEl.innerHTML = `
            <div class="bg-light p-3 rounded border">
                <p><strong>Hostel Fee (${numberOfMonths} Month${numberOfMonths > 1 ? 's' : ''}):</strong> ₹${hostelFeeTotal.toFixed(2)}</p>
                <p><strong>Caution Money:</strong> ₹${cautionMoney.toFixed(2)}</p>
                ${accessoriesHtml}
            </div>
        `;
    }

    payNowBtn.addEventListener("click", function () {
        const queryParams = new URLSearchParams({
            guest_id: guestInput.value,
            resident_id: residentInput.value,
            accessory_ids: accessoryInput.value,
            amount: hiddenFinalAmountInput.value
        });

        window.location.href = `/guest/makepayment?${queryParams.toString()}`;
    });
});
</script>

@endsection
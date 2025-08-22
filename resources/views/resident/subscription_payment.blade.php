@extends('resident.layout')

@section('content')
<div class="container mt-4">
    <h2 class="text-center">Make Payment</h2>

    <form id="paymentForm" class="mt-4">
        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" id="transaction_id" name="transaction_id" class="form-control" readonly>
        </div>

        <div class="mb-3" style="display: none;">
            <label for="subscription_id" class="form-label">Subscription ID</label>
            <input type="number" id="subscription_id" name="subscription_id" class="form-control" readonly required>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount (₹)</label>
            <input type="number" id="amount" name="amount" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select id="payment_method" name="payment_method" class="form-select" required>
                <option value="" disabled selected>Select Payment Method</option>
                <option value="Card">Card</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="UPI">UPI</option>
                <option value="Cash">Cash</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success w-100">Pay Now</button>
    </form>

    <p id="payment-message" class="text-center mt-3" style="display: none;"></p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("transaction_id").value = "TXN" + Math.floor(Math.random() * 1000000000);

    const params = new URLSearchParams(window.location.search);
    const subscriptionId = params.get('subscription_id');

    if (subscriptionId) {
        document.getElementById("subscription_id").value = subscriptionId;
    }
});

document.getElementById("paymentForm").addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = {
        transaction_id: document.getElementById("transaction_id").value,
        payment_method: document.getElementById("payment_method").value,
        subscription_id: document.getElementById("subscription_id").value,
        amount: document.getElementById("amount").value
    };

    if (!formData.transaction_id || !formData.payment_method || !formData.subscription_id || !formData.amount) {
        alert("Please fill all fields before proceeding.");
        return;
    }

    fetch("http://127.0.0.1:8000/api/subscription/pay", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) throw new Error("Network response was not ok");
        return response.json();
    })
    .then(data => {
        const messageElement = document.getElementById("payment-message");

        if (data.success || (data.message && data.message.toLowerCase().includes("success"))) {
            messageElement.innerText = data.message ?? "Payment successful! Transaction ID: " + formData.transaction_id;
            messageElement.style.color = "green";
            messageElement.style.display = "block";

            // 🔁 Refresh the page after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            messageElement.innerText = data.error ?? "Payment failed. Please try again.";
            messageElement.style.color = "red";
            messageElement.style.display = "block";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        const messageElement = document.getElementById("payment-message");
        messageElement.innerText = "An unexpected error occurred. Please try again.";
        messageElement.style.color = "red";
        messageElement.style.display = "block";
    });
});
</script>
@endsection
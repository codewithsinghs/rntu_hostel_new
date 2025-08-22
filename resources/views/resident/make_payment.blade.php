@extends('resident.layout')

@section('content')
    <div class="container mt-5">
        <!-- Success Alert -->
        <div id="successAlert" class="alert alert-success text-center" style="display: none;">
            Payment was successful!
        </div>

        <!-- Error Alert -->
        <div id="errorAlert" class="alert alert-danger text-center" style="display: none;">
            Payment failed!
        </div>

        <h2 class="text-center">Make Payment</h2>

        <form id="paymentForm" method="POST">
            @csrf

            <div class="mb-3">
                <label for="amount" class="form-label">Enter Payment Amount:</label>
                <input type="number" name="amount" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="payment_method" class="form-label">Select Payment Method:</label>
                <select name="payment_method" class="form-control" required>
                    <option value="Cash">Cash</option>
                    <option value="UPI">UPI</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Card">Card</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="transaction_id" class="form-label">Transaction ID (Optional):</label>
                <input type="text" name="transaction_id" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Submit Payment</button>
        </form>
    </div>

    <script>
        // Handle form submission with AJAX
        document.getElementById('paymentForm').addEventListener('submit', function(event) {
            event.preventDefault();  // Prevent the default form submission

            // Create a FormData object to send the form data
            let formData = new FormData(this);

            let accessory_id = window.location.pathname.replace(/\/$/, "").split("/").pop(); 
            // Send the data via AJAX using fetch
            fetch(`/api/resident/accessories/${accessory_id}/pay`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Show success alert
                    document.getElementById('successAlert').style.display = 'block';
                    document.getElementById('errorAlert').style.display = 'none';

                    // Optionally, add more logic like showing transaction ID or remaining balance
                    console.log('Payment was successful!', data.transaction_id);
                } else {
                    // Show error alert
                    document.getElementById('errorAlert').style.display = 'block';
                    document.getElementById('successAlert').style.display = 'none';

                    console.log('Payment failed:', data.message);
                }
            })
            .catch(error => {
                document.getElementById('errorAlert').style.display = 'block';
                document.getElementById('successAlert').style.display = 'none';
                console.error('Error:', error);
            });
        });
    </script>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

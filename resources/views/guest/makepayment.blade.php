<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Make Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            /* Light background for the page */
        }

        .container {
            max-width: 600px;
            /* Limit form width for better readability */
        }

        .card {
            border-radius: 0.75rem;
            /* Slightly rounded corners */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            /* Stronger shadow */
        }

        .success-message,
        .error-message,
        .resident-info {
            display: none;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .form-label {
            font-weight: 500;
            color: #343a40;
        }

        .form-control:read-only {
            background-color: #e9ecef;
            opacity: 1;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow p-4">

            <div id="successMsg" class="alert success-message" role="alert">
                🎉 Payment Successful! Thank you.
            </div>

            <div id="errorMsg" class="alert error-message" role="alert">
                ❌ Payment failed. Please check the form for errors.
            </div>

            <div id="paymentSection">
                <h3 class="mb-4 text-center text-primary">Guest Payment Form</h3>

                @php
                use App\Models\Accessory; // Ensure this model is correctly imported and exists

                $guest_id = request()->query('guest_id');
                $amount = request()->query('amount'); // Still needed here for display only
                $accessory_ids = request()->query('accessory_ids', []);

                // Ensure accessory_ids is an array, handle comma-separated string if passed
                if (!is_array($accessory_ids)) {
                $accessory_ids = explode(',', $accessory_ids);
                }

                $accessory_head_ids = [];

                // Fetch accessory_head_ids from Accessory model
                foreach ($accessory_ids as $accessory_id) {
                $accessory = Accessory::find($accessory_id);
                if ($accessory && $accessory->accessory_head_id) {
                $accessory_head_ids[] = $accessory->accessory_head_id;
                }
                }
                // Remove duplicates if any, as accessory_head_ids might repeat
                $accessory_head_ids = array_unique($accessory_head_ids);
                @endphp

                <form id="paymentForm">
                    @csrf {{-- Laravel's CSRF token for security --}}

                    <input type="hidden" name="guest_id" value="{{ $guest_id }}">
                    {{-- The 'amount' hidden input is REMOVED to prevent client-side manipulation --}}

                    {{-- Dynamically add hidden inputs for each accessory_head_id --}}
                    @foreach($accessory_head_ids as $head_id)
                    <input type="hidden" name="accessory_head_ids[]" value="{{ $head_id }}">
                    @endforeach

                    <div class="mb-3">
                        <label class="form-label">Total Amount (₹)</label>
                        {{-- The amount field is read-only and is for DISPLAY PURPOSES ONLY. It is NOT sent with the form. --}}
                        <input type="text" class="form-control" value="{{ $amount }}" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="" disabled selected>Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Card">Card</option>
                            <option value="Other">Other</option>
                        </select>
                        {{-- Validation feedback for payment_method --}}
                        <div class="invalid-feedback" id="payment_method_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID (optional, but must be unique)</label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control">
                        {{-- Validation feedback for transaction_id --}}
                        <div class="invalid-feedback" id="transaction_id_error"></div>
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <input type="text" name="remarks" id="remarks" class="form-control" value="Advance payment for hostel">
                        {{-- Validation feedback for remarks --}}
                        <div class="invalid-feedback" id="remarks_error"></div>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Confirm Payment</button>
                </form>
            </div>

            <div id="residentInfo" class="resident-info text-center">
                {{-- The password "12345678" is hardcoded here and will be displayed after successful payment.
                     In a real application, this should be dynamically generated and securely communicated (e.g., email, secure temporary link),
                     or the user should set their own password. --}}
                <h4>Your Password for Resident Panel: <strong id="guestPasswordDisplay">12345678</strong></h4>
                <div class="mt-4 d-flex justify-content-center gap-3">
                    <button onclick="window.location.href='/guest/dashboard'" class="btn btn-primary">Back to Guest Panel</button>
                    <button onclick="window.location.href='/login'" class="btn btn-success">Proceed to Resident Panel</button>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const form = document.getElementById('paymentForm');
        const successMsg = document.getElementById('successMsg');
        const errorMsg = document.getElementById('errorMsg');
        const paymentSection = document.getElementById('paymentSection');
        const residentInfo = document.getElementById('residentInfo');
        const guestPasswordDisplay = document.getElementById('guestPasswordDisplay');

        function resetFormErrors() {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');
            errorMsg.style.display = 'none';
            errorMsg.innerText = "❌ Payment failed. Please check the form for errors.";
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            resetFormErrors();

            const formData = new FormData(form);

            axios.post('/api/guests/guest-payments', formData, {
                    headers: {  
                        'Content-Type': 'multipart/form-data',
                        'Accept': 'application/json',
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
                    }
                })
                .then(response => {
                    if (response.success == true || response.status === 201) {
                        paymentSection.style.display = 'none';
                        successMsg.style.display = 'block';
                        guestPasswordDisplay.innerText = '12345678'; // Hardcoded password

                        setTimeout(() => {
                            successMsg.style.display = 'none';
                            residentInfo.style.display = 'block';
                        }, 2000);
                    }
                })
                .catch(error => {
                    errorMsg.style.display = 'block';

                    if (error.response) {
                        const {
                            status,
                            data
                        } = error.response;

                        if (status === 400 && data.message === 'Guest has already paid.') {
                            errorMsg.innerText = "❌ Guest has already paid for this accommodation.";
                        } else if (status === 422 && data.errors) {
                            const errors = data.errors;
                            for (const field in errors) {
                                const input = document.querySelector(`[name="${field}"]`);
                                const errorEl = document.getElementById(`${field}_error`);

                                if (input) {
                                    input.classList.add('is-invalid');
                                }
                                if (errorEl) {
                                    errorEl.innerText = errors[field][0];
                                }
                            }
                            errorMsg.innerText = "❌ Please correct the highlighted errors in the form.";
                        } else if (status === 500 && data.message) {
                            errorMsg.innerText = `❌ Server Error: ${data.message}`;
                            console.error("API Error Response:", error.response);
                        } else {
                            errorMsg.innerText = "❌ Something went wrong on the server. Please try again.";
                            console.error("API Error Response:", error.response);
                        }
                    } else if (error.request) {
                        errorMsg.innerText = "❌ No response from the server. Please check your network connection.";
                        console.error("API Error Request:", error.request);
                    } else {
                        errorMsg.innerText = "❌ An unexpected error occurred. Please try again.";
                        console.error("API Error Message:", error.message);
                    }
                });
        });
    </script>
</body>

</html>
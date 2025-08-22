@extends('accountant.layout')

@section('content')
<div class="container mt-5">
    <h2>Accessory Payment Form</h2>

    <form id="accessory-payment-form">
        {{-- Hidden fields for resident_id and student_accessory_id --}}
        <input type="hidden" name="resident_id" id="resident_id">
        <input type="hidden" name="student_accessory_id" id="student_accessory_id">

        <div class="mb-3">
            <label for="resident_name" class="form-label">Resident Name</label>
            <input type="text" id="resident_name" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="scholar_no" class="form-label">Scholar Number</label>
            <input type="text" id="scholar_no" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="accessory_name" class="form-label">Accessory Name</label>
            <input type="text" id="accessory_name" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="remaining_amount" class="form-label">Remaining Amount</label>
            <input type="number" step="0.01" id="remaining_amount" class="form-control" readonly />
        </div>

        {{-- Transaction ID field - hidden and auto-generated --}}
        <div class="mb-3" style="display: none;">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control" required readonly />
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Card">Card</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount to Pay</label>
            <input type="number" min="1" step="0.01" name="amount" id="amount" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-primary">Submit Accessory Payment</button>
    </form>

    <div id="message" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Function to get query parameters from the URL
        function getQueryParam(name) {
            const params = new URLSearchParams(window.location.search);
            return params.get(name);
        }

        // Function to generate a client-side transaction ID
        function generateTransactionId() {
            const timestamp = new Date().getTime();
            const random = Math.floor(Math.random() * 999999);
            return `ACC-TRX-${timestamp}-${random}`;
        }

        // Get resident_id and student_accessory_id from URL
        const residentIdFromUrl = getQueryParam('resident_id');
        const studentAccessoryIdFromUrl = getQueryParam('student_accessory_id');

        // Populate hidden IDs
        $('#resident_id').val(residentIdFromUrl);
        $('#student_accessory_id').val(studentAccessoryIdFromUrl);

        // Generate and set transaction ID
        $('#transaction_id').val(generateTransactionId());

        // Fetch resident details
        if (residentIdFromUrl) {
            $.ajax({
                url: `/api/residents?id=${residentIdFromUrl}`,
                method: 'GET',
                success: function(response) {
                    const resident = (response.data || []).find(r => r.id == residentIdFromUrl);
                    if (resident) {
                        $('#resident_name').val(resident.name);
                        $('#scholar_no').val(resident.scholar_no);
                    } else {
                        $('#resident_name').val('Resident Not Found');
                        $('#scholar_no').val('N/A');
                    }
                },
                error: function(xhr) {
                    console.error("Error fetching resident details:", xhr);
                    $('#resident_name').val('Error Loading');
                    $('#scholar_no').val('Error Loading');
                }
            });

            // Fetch accessory details for the resident and find the specific one
            if (studentAccessoryIdFromUrl) {
                $.ajax({
                    url: `/api/resident/${residentIdFromUrl}/accessories`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success && response.data && Array.isArray(response.data)) {
                            const accessory = response.data.find(acc => acc.student_accessory_id == studentAccessoryIdFromUrl);
                            if (accessory) {
                                $('#accessory_name').val(accessory.accessory_name ?? 'N/A');
                                $('#remaining_amount').val(accessory.remaining_amount ?? '0.00');
                                $('#amount').val(accessory.remaining_amount ?? '0.00'); // Pre-fill amount with remaining
                            } else {
                                $('#accessory_name').val('Accessory Not Found');
                                $('#remaining_amount').val('0.00');
                                $('#amount').val('0.00');
                            }
                        } else {
                             $('#accessory_name').val('No Accessories Found');
                             $('#remaining_amount').val('0.00');
                             $('#amount').val('0.00');
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching accessory details:", xhr);
                        $('#accessory_name').val('Error Loading');
                        $('#remaining_amount').val('Error Loading');
                        $('#amount').val('0.00');
                    }
                });
            } else {
                $('#accessory_name').val('Accessory ID Missing in URL');
                $('#remaining_amount').val('0.00');
                $('#amount').val('0.00');
            }
        } else {
            alert('Resident ID is missing in the URL. Cannot load payment form.');
        }


        // Handle form submission
        $('#accessory-payment-form').submit(function(e) {
            e.preventDefault();

            // Re-generate transaction ID just before submission in case page was left open for long
            $('#transaction_id').val(generateTransactionId());

            const formData = {
                transaction_id: $('#transaction_id').val(),
                payment_method: $('#payment_method').val(),
                amount: $('#amount').val()
            };

            $('#message').removeClass('alert alert-success alert-danger').text('');

            const residentId = $('#resident_id').val();
            const studentAccessoryId = $('#student_accessory_id').val();

            if (!residentId || !studentAccessoryId) {
                $('#message').addClass('alert alert-danger').text('Resident ID or Student Accessory ID is missing.');
                return;
            }

            $.ajax({
                url: `/api/residents/${residentId}/accessories/${studentAccessoryId}/pay`,
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#message').addClass('alert alert-success').text(response.message);
                    // Reset form fields except for auto-filled resident/accessory data
                    $('#transaction_id').val(generateTransactionId()); // Generate new ID
                    $('#payment_method').val(''); // Clear payment method
                    $('#amount').val(''); // Clear amount
                    // Optionally, update remaining_amount if payment was successful
                    // You might need to refetch accessory details or calculate remaining locally
                },
                error: function(xhr) {
                    let errMsg = 'An error occurred during payment submission.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.error) {
                            errMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON.message) {
                             errMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.messages) {
                            errMsg = Object.values(xhr.responseJSON.messages).flat().join(' ');
                        }
                    }
                    $('#message').addClass('alert alert-danger').text(errMsg);
                }
            });
        });
    });
</script>
@endsection

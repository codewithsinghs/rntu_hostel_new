@extends('resident.layout')

@section('title', 'Subscribe to Service')

@section('content')
<div class="container mt-4">
    <h2 class="text-center">Subscribe to Service</h2>

    <form id="subscriptionForm">
        @csrf

        <input type="hidden" id="resident_id" name="resident_id">

        <div class="form-group">
            <label for="fee_id">Select Fee Type</label>
            <select class="form-control" id="fee_id" name="fee_id" required>
                <option value="">Select Fee Type</option>
                </select>
        </div>

        <div class="form-group" id="food_preference_container" style="display: none;">
            <label for="food_preference">Food Preference</label>
            <select class="form-control" id="food_preference" name="food_preference">
                <option value="">Select Food Preference</option>
                <option value="veg">Veg</option>
                <option value="non_veg">Non-Veg</option>
            </select>
        </div>

        <div class="form-group">
            <label for="duration">Subscription Duration</label>
            <select class="form-control" id="duration" name="duration" required>
                <option value="1 Month">1 Month</option>
                <option value="3 Months">3 Months</option>
                <option value="6 Months">6 Months</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Subscribe</button>
        <div id="error-message" class="mt-3 text-danger" style="display: none;"></div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
    $(document).ready(function () {
        // Automatically set resident_id from the logged-in user
        const residentId = "{{ auth()->user()->resident->id ?? '' }}";
        if (residentId) {
            $('#resident_id').val(residentId);
        }

        // Load fees, filtered for Hostel fee
        $.ajax({
            url: '/api/fees',
            type: 'GET',
            success: function (fees) {
                let feeSelect = $('#fee_id');
                feeSelect.empty().append('<option value="">Select Fee Type</option>');

                fees.forEach(fee => {
                    if (fee.name.toLowerCase().includes('hostel')) { // Filter for "Hostel fee"
                        feeSelect.append(
                            `<option value="${fee.id}" data-fee-type="${fee.name}" data-fee-head-id="${fee.fee_head_id}">
                                ${fee.name} - ₹${fee.amount}
                            </option>`
                        );
                    }
                });
                if (feeSelect.find('option').length <= 1)
                {
                    feeSelect.append('<option value="" disabled>No Hostel Fee Available</option>');
                }

            },
            error: function () {
                $('#error-message').show().text('Failed to load fees.');
            }
        });

        // Show/hide food preference based on selection
        $('#fee_id').on('change', function () {
            let feeType = $(this).find(':selected').data('fee-type');
            if (feeType && feeType.toLowerCase().includes('mess')) {
                $('#food_preference_container').show();
            } else {
                $('#food_preference_container').hide();
                $('#food_preference').val('');
            }
        });

        // Submit form
        $('#subscriptionForm').on('submit', function (e) {
            e.preventDefault();

            const residentId = $('#resident_id').val();
            const feeId = $('#fee_id').val();
            const duration = $('#duration').val();
            const foodPreference = $('#food_preference').val();
            const subscriptionType = $('#fee_id').find(':selected').data('fee-type');
            const feeHeadId = $('#fee_id').find(':selected').data('fee-head-id'); // Get fee_head_id

            $('#error-message').hide().text('');

            if (!residentId || !feeId || !duration || !subscriptionType) {
                $('#error-message').show().text("Please fill in all required fields.");
                return;
            }

            $.ajax({
                url: '/api/residents/subscribe',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    resident_id: residentId,
                    fee_head_id: feeHeadId, // changed from fee_id to fee_head_id
                    subscription_type: subscriptionType,
                    duration: duration,
                    food_preference: foodPreference,
                },
                success: function (response) {
                    alert("✅ Subscription Created Successfully!\n" +
                        "Subscription ID: " + response.subscription_id + "\n" +
                        "Type: " + response.subscription.subscription_type + "\n" +
                        "Status: Pending Payment"
                    );

                    // Clear the form after successful submission
                    $('#subscriptionForm')[0].reset();
                    $('#food_preference_container').hide();
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = '';
                        for (const field in errors) {
                            errorMessages += `<p>${errors[field].join(', ')}</p>`;
                        }
                        $('#error-message').html(errorMessages).show();
                    } else {
                        $('#error-message').show().text('Failed to subscribe.');
                    }
                }
            });
        });
    });
</script>
@endsection

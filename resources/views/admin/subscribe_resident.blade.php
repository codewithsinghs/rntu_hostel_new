@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="text-xl font-semibold mb-4">Subscribe Resident</h2>

    <div id="success-message" class="alert alert-success d-none"></div>
    <div id="error-message" class="alert alert-danger d-none"></div>

    <form id="subscriptionForm">
        <div class="mb-3">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-control" required>
                <option value="">Loading residents...</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="fee_id">Hostel Fee</label>
            <select name="fee_id" id="fee_id" class="form-control" required>
                <option value="">Select Hostel Fee</option>
            </select>
        </div>

        <div class="mb-3" style="display: none;">
            <label for="subscription_type" class="form-label">Subscription Type</label>
            <input type="text" name="subscription_type" id="subscription_type_input" class="form-control" readonly>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <select name="duration" class="form-control" required>
                <option value="">Select Duration</option>
                <option value="1 Month">1 Month</option>
                <option value="3 Months">3 Months</option>
                <option value="6 Months">6 Months</option>
                <option value="1 Year">1 Year</option>
            </select>
        </div>

        <div class="mb-3" style="display: none;">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" class="form-control" required>
                <option value="">Select Method</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Card">Card</option>
                <option value="Other">Other</option>
                <option value="Null" selected>Null</option> {{-- Added 'selected' here --}}
            </select>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

{{-- Assets --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(async function() {
        const residentSelect = $('#resident_id');
        const feeSelect = $('#fee_id');
        const subscriptionTypeInput = $('#subscription_type_input');

        try {
            // Fetch residents
            const residentRes = await fetch("{{ url('/api/admin/residents') }}", {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            });
            const residentData = await residentRes.json();
            if (!Array.isArray(residentData.data)) throw new Error('Invalid residents data format.');

            residentSelect.html('<option value="">Select Resident</option>');
            residentData.data.forEach(res => {
                residentSelect.append(`<option value="${res.id}">${res.name} (${res.scholar_no})</option>`);
            });

            residentSelect.select2({
                placeholder: "Select Resident",
                width: '100%'
            });

            // Fetch fees
            const feeRes = await fetch("{{ url('/api/fees') }}");
            const feeData = await feeRes.json();
            if (!Array.isArray(feeData.data)) throw new Error('Invalid fees data format.');

            feeSelect.html('<option value="">Select Hostel Fee</option>');
            feeData.data.forEach(fee => {
                // Only show active hostel-related fees
                if (fee.is_active === 1 && fee.name.toLowerCase().includes('hostel')) {
                    feeSelect.append(`<option value="${fee.fee_head_id}" data-name="${fee.name}">${fee.name} - ₹${fee.amount}</option>`);
                }
            });

            feeSelect.on('change', function() {
                const selectedText = $('#fee_id option:selected').data('name');
                subscriptionTypeInput.val(selectedText || '');
            });

        } catch (err) {
            console.error(err);
            $('#error-message').removeClass('d-none').text('Failed to load dropdown data.');
        }

        // Form Submit
        $('#subscriptionForm').on('submit', async function(e) {
            e.preventDefault();

            const formData = {
                resident_id: $('#resident_id').val(),
                fee_head_id: $('#fee_id').val(),
                subscription_type: $('#subscription_type_input').val(),
                duration: $('select[name="duration"]').val(),
                payment_method: $('select[name="payment_method"]').val(),
                remarks: $('textarea[name="remarks"]').val()
            };

            try {
                const res = await fetch("{{ url('/api/admin/subscribe-resident') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
                    },
                    body: JSON.stringify(formData)
                });

                const result = await res.json();
                if (res.ok) {
                    $('#success-message').removeClass('d-none').text(result.message || 'Subscription successful.');
                    $('#error-message').addClass('d-none');
                    $('#subscriptionForm')[0].reset();
                    $('#resident_id').val(null).trigger('change');
                    $('#fee_id').val(null).trigger('change');
                    subscriptionTypeInput.val('');
                } else {
                    $('#error-message').removeClass('d-none').text(result.message || 'Subscription failed.');
                    $('#success-message').addClass('d-none');
                }
            } catch (err) {
                console.error('Submit error:', err);
                $('#error-message').removeClass('d-none').text('An error occurred. Please try again.');
                $('#success-message').addClass('d-none');
            }
        });
    });
</script>
@endsection
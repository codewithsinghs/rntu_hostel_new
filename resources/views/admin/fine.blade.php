@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="text-xl font-semibold mb-4">Assign Fine to Resident</h2>

    <div id="success-message" class="alert alert-success d-none"></div>
    <div id="error-message" class="alert alert-danger d-none"></div>

    <form id="fineAssignmentForm">
        <div class="mb-3">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-control" required>
                <option value="">Loading residents...</option>
            </select>
        </div>

        {{-- Hidden Inputs --}}
        <input type="hidden" name="subscription_type" value="Other">
        <input type="hidden" name="duration" value="">
        <input type="hidden" name="created_by" value="1">

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks (required for Other)</label>
            <textarea name="remarks" id="remarks" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Assign Fine</button>
    </form>
</div>

{{-- Assets --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(async function () {
        const residentSelect = $('#resident_id');

        try {
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

            residentSelect.html('<option value="">Select Resident</option>');
            residentData.data.forEach(res => {
                residentSelect.append(`<option value="${res.id}">${res.name} (${res.scholar_number})</option>`);
            });

            residentSelect.select2({ placeholder: "Select Resident", width: '100%' });

        } catch (err) {
            console.error(err);
            $('#error-message').removeClass('d-none').text('Failed to load resident data. Check API endpoint.');
        }

        $('#fineAssignmentForm').on('submit', async function (e) {
            e.preventDefault();

            $('#success-message').addClass('d-none').text('');
            $('#error-message').addClass('d-none').text('');

            const formData = {
                resident_id: $('#resident_id').val(),
                subscription_type: 'Other',
                duration: '', // Not required for 'Other'
                created_by: '{{ auth()->id() }}',
                remarks: $('#remarks').val()
            };

            if (!formData.resident_id  || !formData.remarks.trim()) {
                $('#error-message').removeClass('d-none').text('Please fill all required fields.');
                return;
            }

            try {
                const res = await fetch("{{ url('/api/admin/fine') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const result = await res.json();

                if (res.ok && result.success) {
                    $('#success-message').removeClass('d-none').text(result.message || 'Fine assigned successfully.');
                    $('#fineAssignmentForm')[0].reset();
                    $('#resident_id').val(null).trigger('change');
                } else {
                    let errorMessage = result.message || 'Fine assignment failed.';
                    if (result.errors) {
                        errorMessage += '<br>' + Object.values(result.errors).map(err => err.join(', ')).join('<br>');
                    }
                    $('#error-message').removeClass('d-none').html(errorMessage);
                }
            } catch (err) {
                console.error('Submit error:', err);
                $('#error-message').removeClass('d-none').text('An unexpected error occurred. Please try again.');
            }
        });
    });
</script>
@endsection

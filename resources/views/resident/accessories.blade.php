@extends('resident.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Add Accessories</h2>

    <form id="accessoryForm">
        @csrf
        <!-- Accessories Dropdown -->
        <div class="mb-3">
            <label for="accessory_head_id" class="form-label">Select Accessory</label>
            <select class="form-control" id="accessory_head_id" name="accessory_head_id" required>
                <option value="">Select an accessory</option>
            </select>
        </div>

        <!-- Duration -->
        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <select class="form-control" id="duration" name="duration" required>
                <option value="1 Month">1 Month</option>
                <option value="3 Months">3 Months</option>
                <option value="6 Months">6 Months</option>
                <option value="1 Year">1 Year</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Add Accessory</button>
    </form>

    <div id="responseMessage" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {

        $.ajaxSetup({
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        });
        // Fetch active accessories
        $.get('/api/resident/accessories/active', function (response) {
            let accessorySelect = $('#accessory_head_id');
            accessorySelect.html('<option value="">Select an accessory</option>');

            response.data.forEach(accessory => {
                const name = accessory.accessory_head?.name ?? 'Unnamed';
                accessorySelect.append(`<option value="${accessory.accessory_head_id}">${name} - ₹${accessory.price}</option>`);
            });
        }).fail(function () {
            $('#responseMessage').html('<div class="alert alert-danger">Error fetching accessories.</div>');
        });

        // Handle form submission
        $('#accessoryForm').submit(function (event) {
            event.preventDefault();

            // let residentId = $('#resident_id').val();
            let accessoryHeadId = $('#accessory_head_id').val();
            let duration = $('#duration').val();

            if (!accessoryHeadId) {
                $('#responseMessage').html('<div class="alert alert-danger">Please select an accessory.</div>');
                return;
            }

            $.ajax({
                url: `/api/resident/accessories`,
                type: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                },
                data: JSON.stringify({ accessory_head_id: accessoryHeadId, duration: duration }),
                success: function (response) {
                    $('#responseMessage').html(`<div class="alert alert-success">${response.message}</div>`);
                    $('#accessoryForm')[0].reset();
                },
                error: function (xhr) {
                    let errorMessage = "Error adding accessory.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    $('#responseMessage').html(`<div class="alert alert-danger">${errorMessage}</div>`);
                }
            });
        });
    });
</script>
@endsection

@extends('resident.layout')

@section('content')
<div class="container">
    <h2>Submit a Grievance</h2>

    <div id="alert" class="alert d-none"></div>

    <form id="grievanceForm">
        @csrf

        <!-- Hidden Resident ID input -->
        <input type="hidden" id="resident_id" name="resident_id">
        
        <!-- Hidden Created By input -->
        <input type="hidden" id="created_by" name="created_by">
        
        <!-- Hidden Token ID input -->
        <input type="hidden" id="token_id" name="token_id">

        <div class="form-group">
            <label for="type_of_complaint">Type of Complaint:</label>
            <select id="type_of_complaint" name="type_of_complaint" class="form-control" required>
                <option value="" disabled selected>Select Complaint Type</option>
                <option value="Maintenance">Maintenance</option>
                <option value="Behavior">Behavior</option>
                <option value="Cleanliness">Cleanliness</option>
                <option value="Noise">Noise</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div class="form-group" id="other_complaint_group" style="display: none;">
            <label for="other_complaint">Please specify:</label>
            <input type="text" id="other_complaint" name="other_complaint" class="form-control" placeholder="Type your complaint here">
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Submit Grievance</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function generateTokenId() {
        const randomNum = Math.floor(Math.random() * 1000000);
        return 'grievance-' + randomNum;
    }

    $('#type_of_complaint').change(function() {
        if ($(this).val() === 'Other') {
            $('#other_complaint_group').show();
        } else {
            $('#other_complaint_group').hide();
            $('#other_complaint').val('');
        }
    });

    $('#grievanceForm').submit(function(event) {
        event.preventDefault();

        $('#token_id').val(generateTokenId());

        let formData = new FormData(this);

        $.ajax({
            url: "{{ url('api/resident/grievances/submit') }}",
            type: "POST",
            headers: {
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#alert').removeClass('d-none alert-danger').addClass('alert-success')
                    .text('Grievance submitted successfully!').show();
                $('#grievanceForm')[0].reset();
                $('#other_complaint_group').hide();
            },
            error: function(xhr) {
                let errorMessage = xhr.responseJSON?.message || 'Error submitting grievance. Please try again.';
                $('#alert').removeClass('d-none alert-success').addClass('alert-danger')
                    .text(errorMessage).show();
            }
        });
    });
});
</script>
@endsection

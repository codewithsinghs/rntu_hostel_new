@extends('admin.layout')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<div class="container mt-5">
    <h2 class="mb-4">Edit Faculty</h2>

    <div id="alert-container"></div> {{-- Added alert container for consistent messaging --}}

    <form id="editFacultyForm" novalidate> {{-- Added novalidate to prevent browser's default validation --}}
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Faculty Name</label>
            <input type="text" class="form-control" id="name" name="name" value="" required>
            <div class="invalid-feedback" id="name_error"></div> {{-- Validation error display --}}
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="">-- Select Status --</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <div class="invalid-feedback" id="status_error"></div> {{-- Validation error display --}}
        </div>

        <button type="submit" class="btn btn-success">Update</button>
    </form>

</div>

<script>
    $(document).ready(function() {
        let id = window.location.pathname.replace(/\/$/, "").split("/").pop();
        //Show form data
        $.ajax({
            url: '/api/admin/faculties/'+id,
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id'),
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            
            success: function(response) {
                if (response.success) {
                    $('#name').val(response.data.name);
                    $('#status').val(response.data.status);
                } else {
                    showAlert("error", response.message || "Failed to load faculty data.");
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr);
            }
        });
        
        // Handle form submission
        $('#editFacultyForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            clearValidationErrors(); // Clear previous validation errors

            let formData = {
                id: $('#faculty_id').val(),
                name: $('#name').val(),
                status: $('#status').val(),
            };

            $.ajax({
                url: '/api/admin/faculties/' + id,
                type: 'PUT',
                headers: {
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id'),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        showAlert("success", response.message || "Faculty updated successfully!");
                        window.location.href = "{{ route('admin.faculties') }}"; // Redirect to faculties list
                    } else {
                        showAlert("error", response.message || "Failed to update faculty.");
                    }
                },
                error: function(xhr) {
                    handleAjaxError(xhr);
                }
            });
        });
    });

    function clearValidationErrors() {
        $('.invalid-feedback').text('');
        $('.form-control').removeClass('is-invalid');
    }
    function showAlert(type, message) {
        $('#alert-container').html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
    }   
    function handleAjaxError(xhr) {
        if (xhr.status === 422) { // Validation error
            const errors = xhr.responseJSON.errors;
            for (const field in errors) {
                $(`#${field}`).addClass('is-invalid');
                $(`#${field}_error`).text(errors[field][0]);
            }
        } else {
            showAlert("danger", "An error occurred while updating the faculty.");
        }
    }
    

</script>
@endsection

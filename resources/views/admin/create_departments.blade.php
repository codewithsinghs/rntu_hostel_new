@extends('admin.layout')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<div class="container mt-5">
    <h2 class="mb-4">Create Department</h2>

    <div id="alert-container"></div> {{-- Added alert container for consistent messaging --}}

    <form id="departmentsForm" novalidate> {{-- Added novalidate to prevent browser's default validation --}}
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Department Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <div class="invalid-feedback" id="name_error"></div> {{-- Validation error display --}}
        </div>
        <div class="mb-3">
            <label for="faculty_id" class="form-label">Select Faculty</label>
            <select class="form-control" id="faculty_id" name="faculty_id" required>
                <option value="">-- Select Faculty --</option>                
            </select>
            <div class="invalid-feedback" id="faculty_id_error"></div> {{-- Validation error display --}}
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

        <button type="submit" class="btn btn-success">Create</button>
    </form>

</div>

<script>
    $(document).ready(function() {
        $.ajax({
            url: '/api/admin/faculties',
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id'),
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    let options = '<option value="">-- Select Faculty --</option>';
                    response.data.forEach(function(faculty) {
                        options += `<option value="${faculty.id}">${faculty.name}</option>`;
                    });
                    $('#faculty_id').html(options);
                } else {
                    showAlert("danger", response.message || "Failed to load faculties.");
                }
            },
            error: function(xhr) {
                showAlert("danger", "An error occurred while fetching faculties.");
            }
        });
        // Handle form submission
        $('#departmentsForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            clearValidationErrors(); // Clear previous validation errors

            let formData = {
                name: $('#name').val(),  
                faculty_id: $('#faculty_id').val(),              
                status: $('#status').val(),
            };

            $.ajax({
                url: '/api/admin/departments/create',
                type: 'POST',
                headers: {
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id'),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        showAlert("success", response.message || "Department created successfully!");
                        window.location.href = "{{ route('admin.departments') }}"; // Redirect to faculties list
                    } else {
                        showAlert("danger", response.message || "Failed to create Department.");
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Validation error
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        showAlert("danger", "An error occurred while creating the department.");
                    }
                }
            });
        });
    });


    function showAlert(type, message) {
        $('#alert-container').html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
    }   
    function clearValidationErrors() {
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }   
    function displayValidationErrors(errors) {
        clearValidationErrors();
        for (const field in errors) {
            const input = $(`#${field}`);
            const errorDiv = $(`#${field}_error`);
            if (input.length) {
                input.addClass('is-invalid ');
            }
            if (errorDiv.length) {
                errorDiv.text(errors[field][0]); // Display the first error message
            }
        }   
    }
</script>
@endsection
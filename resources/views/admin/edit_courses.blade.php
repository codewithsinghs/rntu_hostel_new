@extends('admin.layout')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<div class="container mt-5">
    <h2 class="mb-4">Edit Course</h2>

    <div id="alert-container"></div> {{-- Added alert container for consistent messaging --}}

    <form id="coursesForm" novalidate> {{-- Added novalidate to prevent browser's default validation --}}
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Course Name</label>
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
            <label for="department_id" class="form-label">Select Department</label>
            <select class="form-control" id="department_id" name="department_id" required>
                <option value="">-- Select Department --</option>                
            </select>
            <div class="invalid-feedback" id="department_id_error"></div> {{-- Validation error display --}}
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
        // Load existing course data
        // Load Faculties on page load
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

        // On Faculty Change → Load Departments
        $('#faculty_id').on('change', function() {
            let facultyId = $(this).val();

            if (!facultyId) {
                $('#department_id').html('<option value="">-- Select Department --</option>');
                return;
            }

            $.ajax({
                url: '/api/admin/departments?faculty_id=' + facultyId,
                type: 'GET',
                headers: {
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id'),
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && Array.isArray(response.data)) {
                        let options = '<option value="">-- Select Department --</option>';
                        response.data.forEach(function(department) {
                            options += `<option value="${department.id}">${department.name}</option>`;
                        });
                        $('#department_id').html(options);
                    } else {
                        showAlert("danger", response.message || "No departments found for this faculty.");
                    }
                },
                error: function(xhr) {
                    showAlert("danger", "An error occurred while fetching departments.");
                }
            });
        });


        let id = window.location.pathname.replace(/\/$/, "").split("/").pop();
       $.ajax({
            url: '/api/admin/courses/'+id, // your API endpoint
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')            
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('#name').val(response.data.name);
                    $('#faculty_id').val(response.data.department.faculty.id);
                    let department_id = response.data.department_id;
                    $.ajax({
                        url: '/api/admin/departments?faculty_id=' + response.data.department.faculty.id,
                        type: 'GET',
                        headers: {
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id'),
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success && Array.isArray(response.data)) {
                                let options = '<option value="">-- Select Department --</option>';
                                response.data.forEach(function(department) {
                                    options += `<option value="${department.id}">${department.name}</option>`;
                                });
                                $('#department_id').html(options);
                                $('#department_id').val(department_id);
                            } else {
                                showAlert("danger", response.message || "No departments found for this faculty.");
                            }
                        },
                        error: function(xhr) {
                            showAlert("danger", "An error occurred while fetching departments.");
                        }
                    });

                    $('#status').val(response.data.status);
                } else {
                    showAlert("danger", response.message || "Failed to load course data.");
                }
            },
            error: function(xhr) {
                showAlert("danger", "An error occurred while fetching course data.");
            }
        });

    
        // Handle form submission
        $('#coursesForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            clearValidationErrors(); // Clear previous validation errors

            let formData = {
                name: $('#name').val(),  
                faculty_id: $('#faculty_id').val(),              
                department_id: $('#department_id').val(),              
                status: $('#status').val(),
            };

            $.ajax({
                url: '/api/admin/courses/' + id,
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
                        showAlert("success", response.message || "Courses updated successfully!");
                        window.location.href = "{{ route('admin.courses') }}"; // Redirect to faculties list
                    } else {
                        showAlert("danger", response.message || "Failed to update Course.");
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Validation error
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        showAlert("danger", "An error occurred while updating the courses.");
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
@extends('admin.layout')

@section('content')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<div class="container mt-5">
    <h2 class="mb-4">Create Building</h2>

    <div id="alert-container"></div> {{-- Added alert container for consistent messaging --}}

    <form id="buildingForm" novalidate> {{-- Added novalidate to prevent browser's default validation --}}
        @csrf
        {{-- <div class="mb-3">
            <label for="name" class="form-label">University Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <div class="invalid-feedback" id="name_error"></div> 
        </div> --}}
        <div class="mb-3">
            <label for="name" class="form-label">Building Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
            <div class="invalid-feedback" id="name_error"></div> {{-- Validation error display --}}
        </div>

        <div class="mb-3">
            <label for="building_code" class="form-label">Building Code</label>
            <input type="text" class="form-control" id="building_code" name="building_code" required>
            <div class="invalid-feedback" id="building_code_error"></div> {{-- Validation error display --}}
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="">-- Select Status --</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <div class="invalid-feedback" id="status_error"></div> {{-- Validation error display --}}
        </div>

        <div class="mb-3">
            <label for="floors" class="form-label">Number of Floors</label>
            <select class="form-control" id="floors" name="floors" required>
                <option value="">-- Select Floors --</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
            </select>
            <div class="invalid-feedback" id="floors_error"></div> {{-- Validation error display --}}
        </div>

        <button type="submit" class="btn btn-success">Create</button>
    </form>

</div>

<script>
    $(document).ready(function() {
        // Handle form submission
        $('#buildingForm').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            clearValidationErrors(); // Clear previous validation errors

            let formData = {
                name: $('#name').val(),
                building_code: $('#building_code').val(),
                floors: $('#floors').val(),
            };

            $.ajax({
                url: '/api/admin/buildings/create',
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
                        showAlert("success", response.message || "Building created successfully!");
                        $('#buildingForm')[0].reset(); // Reset the form
                    } else {
                        showAlert("danger", response.message || "Failed to create building.");
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Validation error
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        showAlert("danger", "An error occurred while creating the building.");
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
    // document.addEventListener("DOMContentLoaded", function() {

    //     // Helper function to display general alerts (success/error)
    //     function showAlert(type, message) {
    //         const alertContainer = document.getElementById("alert-container");
    //         if (alertContainer) {
    //             alertContainer.innerHTML = `
    //                 <div class="alert alert-${type} alert-dismissible fade show" role="alert">
    //                     ${message}
    //                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    //                 </div>
    //             `;
    //             // Automatically fade out the alert after 4 seconds
    //             setTimeout(() => {
    //                 const currentAlert = alertContainer.querySelector('.alert');
    //                 if (currentAlert) {
    //                     currentAlert.classList.remove('show');
    //                     currentAlert.classList.add('fade');
    //                     setTimeout(() => currentAlert.remove(), 500); // Remove after fade out
    //                 }
    //             }, 4000);
    //         }
    //     }

    //     // Helper function to clear all previous validation errors
    //     function clearValidationErrors() {
    //         document.querySelectorAll('.is-invalid').forEach(element => {
    //             element.classList.remove('is-invalid');
    //         });
    //         document.querySelectorAll('.invalid-feedback').forEach(element => {
    //             element.textContent = '';
    //         });
    //     }

    //     // Helper function to display specific validation errors
    //     function displayValidationErrors(errors) {
    //         clearValidationErrors(); // Clear existing errors first

    //         for (const field in errors) {
    //             const input = document.getElementById(field);
    //             const errorDiv = document.getElementById(`${field}_error`);

    //             if (input) {
    //                 input.classList.add('is-invalid');
    //             }
    //             if (errorDiv) {
    //                 errorDiv.textContent = errors[field][0]; // Display the first error message
    //             }
    //         }
    //     }

    //     // Function to fetch universities from API and populate the dropdown
    //     function fetchUniversities() {
    //         fetch("/api/universities")
    //             .then(response => {
    //                 if (!response.ok) {
    //                     return response.json().then(err => {
    //                         throw err;
    //                     });
    //                 }
    //                 return response.json();
    //             })
    //             .then(data => {
    //                 let universitySelect = document.getElementById("university_id");
    //                 universitySelect.innerHTML = '<option value="">-- Select University --</option>';

    //                 if (data.success && Array.isArray(data.data)) {
    //                     data.data.forEach(university => {
    //                         let option = document.createElement("option");
    //                         option.value = university.id;
    //                         option.textContent = university.name;
    //                         universitySelect.appendChild(option);
    //                     });
    //                 } else {
    //                     showAlert("danger", "Invalid response from server. Expected an array of universities in the 'data' field.");
    //                     console.error("Invalid university data structure:", data);
    //                 }
    //             })
    //             .catch(error => {
    //                 let errorMessage = "Failed to load universities.";
    //                 if (error.message) {
    //                     errorMessage = error.message;
    //                 } else if (error instanceof TypeError) {
    //                     errorMessage = "Could not connect to the server. Please check your network connection.";
    //                 } else {
    //                     errorMessage += " Error: " + error;
    //                 }
    //                 showAlert("danger", errorMessage);
    //                 console.error("Error fetching universities:", error);
    //             });
    //     }

    //     fetchUniversities(); // Call the function to fetch universities when the page loads

    //     // Handle form submission
    //     document.getElementById("buildingForm").addEventListener("submit", function(event) {
    //         event.preventDefault(); // Prevent default form submission
    //         clearValidationErrors(); // Clear errors on new submission

    //         let name = document.getElementById("name").value;
    //         let building_code = document.getElementById("building_code").value;
    //         let university_id = document.getElementById("university_id").value;
    //         let status = document.getElementById("status").value;
    //         let floors = document.getElementById("floors").value;
    //         let created_by = document.getElementById("created_by").value;

    //         let data = {
    //             name: name,
    //             building_code: building_code,
    //             university_id: university_id,
    //             status: status,
    //             floors: floors,
    //             created_by: created_by
    //         };

    //         fetch("/api/buildings", {
    //                 method: "POST",
    //                 headers: {
    //                     "Content-Type": "application/json",
    //                     "Accept": "application/json",
    //                     "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content, // Get CSRF token from meta tag
    //                     // "Authorization": `Bearer {{ Auth::user()->api_token ?? '' }}` // Uncomment if using API tokens for this endpoint
    //                 },
    //                 body: JSON.stringify(data)
    //             })
    //             .then(response => {
    //                 // Check if response is not OK (e.g., 4xx or 5xx status)
    //                 if (!response.ok) {
    //                     // If it's a validation error (422), the 'err' object will have an 'errors' key
    //                     return response.json().then(err => {
    //                         throw err; // Throw the error object including validation messages
    //                     });
    //                 }
    //                 return response.json(); // For successful 2xx responses
    //             })
    //             .then(apiResponse => {
    //                 if (apiResponse.success) {
    //                     showAlert("success", apiResponse.message || "Building Created Successfully!");
    //                     document.getElementById("buildingForm").reset(); // Reset form on success
    //                     fetchUniversities(); // Re-fetch universities if needed (e.g., if a new university could be created on another page)
    //                 } else {
    //                     // This block handles cases where the API returns success: false but with a 2xx status
    //                     showAlert("danger", apiResponse.message || "Failed to create building.");
    //                 }
    //             })
    //             .catch(error => {
    //                 // This catch block handles network errors and errors thrown from the .then blocks (including validation errors)
    //                 if (error.errors) { // Check if the error object contains validation errors
    //                     displayValidationErrors(error.errors);
    //                     showAlert("danger", error.message || "Please correct the form errors.");
    //                 } else {
    //                     let errorMessage = "Something went wrong!";
    //                     if (error.message) {
    //                         errorMessage = error.message; // Use message from API response if available
    //                     } else if (error instanceof TypeError) {
    //                         errorMessage = "Could not connect to the server. Please check your network connection.";
    //                     }
    //                     showAlert("danger", errorMessage);
    //                     console.error("Error creating building:", error);
    //                 }
    //             });
    //     });
    // });
</script>
@endsection
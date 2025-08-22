@extends('superadmin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Create Admin</h2>

    <div id="alert-container"></div>

    <form id="adminForm">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Admin Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="university_id" class="form-label">University</label>
            <select class="form-control" id="university_id" name="university_id" required>
                <option value="">Select University</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Create Admin</button>
    </form>
</div>

<script>
$(document).ready(function () {

    // Function to fetch universities and populate the dropdown
    function fetchUniversities() {
        $.ajax({
            url: "{{ url('/api/universities') }}", // Corrected URL
            type: "GET",
            success: function (response) {
                let universityDropdown = $('#university_id');
                universityDropdown.empty(); // Clear existing options
                universityDropdown.append('<option value="">Select University</option>'); // Add default option

                // Check if the response is successful and contains an array in the 'data' field
                if (response.success && Array.isArray(response.data)) {
                    $.each(response.data, function (key, university) {
                        universityDropdown.append('<option value="' + university.id + '">' + university.name + '</option>');
                    });
                } else {
                    // If 'data' is not an array or success is false, show an error
                    showAlert("danger", "Invalid response from server. Expected an array of universities in the 'data' field.");
                    console.error("Invalid university data structure:", response);
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = "Failed to load universities.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (status === 'timeout') {
                    errorMessage = "Request timed out. Please check your internet connection.";
                } else if (xhr.status === 0) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                } else {
                    errorMessage += " Error: " + error;
                }
                showAlert("danger", errorMessage);
                console.error("Error fetching universities:", xhr, status, error);
            }
        });
    }

    fetchUniversities(); // Call the function to fetch universities when the page loads

    $("#adminForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        let formData = $(this).serialize(); // Serialize form data

        $.ajax({
            url: "{{ url('/api/create-admin') }}",
            type: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            success: function (response) {
                if (response.message) {
                    showAlert("success", response.message); // Show success message
                    $("#adminForm")[0].reset(); // Reset form on success
                } else {
                    showAlert("danger", "Error creating admin. Please try again.");
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = "Something went wrong!";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (status === 'timeout') {
                    errorMessage = "Request timed out. Please check your internet connection.";
                } else if (xhr.status === 0) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                } else {
                    errorMessage += " Error: " + error;
                }
                showAlert("danger", errorMessage);
                console.error("Error creating admin:", xhr, status, error);
            }
        });
    });

    // Helper function to display alerts
    function showAlert(type, message) {
        $("#alert-container").html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
    }
});
</script>
@endsection

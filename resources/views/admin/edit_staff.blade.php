@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h2 class="mb-3">Update Staff</h2>

            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Staff Update</h4>
                </div>
                <div class="card-body">
                    <div id="messageContainer"></div>

                    <form id="updateStaffForm">
                        @csrf {{-- This is for standard form submission security --}}
                        {{-- Add a hidden input to easily access the CSRF token via JavaScript --}}
                        <input type="hidden" id="csrf_token_field" value="{{ csrf_token() }}">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="building_id" class="form-label">Building</label>
                            <select class="form-control" id="building_id" name="building_id" required>
                                <option value="">Select Building</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="warden">Warden</option>
                                <option value="security">Security</option>
                                <option value="mess_manager">Mess Manager</option>
                                <option value="gym_manager">Gym Manager</option>
                                <option value="hod">HOD</option>
                                <option value="accountant">Accountant</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success">Update Staff</button>
                        <a href="{{ route('admin.staff') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

$(document).ready(function() {
    // Fetch staff details and populate the form
    let staffId = window.location.pathname.replace(/\/$/, "").split("/").pop();
    fetch(`{{ url('/api/admin/staff') }}/${staffId}`, {
        method: "GET",
        headers: {
            "Accept": "application/json",
            "Content-Type": "application/json",
            'token': localStorage.getItem('token'), // Include token for authentication
            'auth-id': localStorage.getItem('auth-id') // Include auth-id for authorization
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const staff = data.data[0];
            document.getElementById("name").value = staff.name;
            document.getElementById("email").value = staff.email;
            document.getElementById("building_id").value = staff.building_id;
            document.getElementById("role").value = staff.roles[0].name; // Assuming one role per staff
        } else {
            showCustomMessageBox(data.message || "Failed to load staff details.", 'danger');
        }
    })
    .catch(error => {
        console.error("Error fetching staff details:", error);
        showCustomMessageBox("Failed to load staff details. Please try again.", 'danger');
    });
}); 


// Function to show a custom message box
function showCustomMessageBox(message, type = 'info', targetElementId = 'messageContainer') {
    const messageContainer = document.getElementById(targetElementId);
    if (messageContainer) {
        messageContainer.innerHTML = ""; // Clear previous messages
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        messageContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000); // Remove after 3 seconds
    } else {
        console.warn(`Message container #${targetElementId} not found.`);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    fetchBuildings();
    document.getElementById("updateStaffForm").addEventListener("submit", updateStaff);

    function getCsrfToken() {
        // Now getting the CSRF token from the hidden input field we added
        const csrfTokenField = document.getElementById('csrf_token_field');
        if (csrfTokenField) {
            return csrfTokenField.value;
        } else {
            console.error("CRITICAL ERROR: CSRF token hidden input field (id='csrf_token_field') not found. Ensure it's present in the form.");
            showCustomMessageBox("Security error: CSRF token missing. Please refresh the page or contact support.", 'danger');
            return null; // Return null to indicate failure
        }
    }

    function fetchBuildings() {
        fetch("{{ url('/api/admin/buildings') }}", {
            method: "GET",
            headers: {
                "Accept": "application/json",           
                "Content-Type": "application/json",
                'token': localStorage.getItem('token'), // Include token for authentication
                'auth-id': localStorage.getItem('auth-id') // Include auth-id for authorization
            }
        })
            .then(response => response.json())
            .then(response => {
                let buildingSelect = document.getElementById("building_id");
                buildingSelect.innerHTML = '<option value="">Select Building</option>';

                const buildings = response.data;

                if (response.success && Array.isArray(buildings)) {
                    buildings.forEach(building => {
                        buildingSelect.innerHTML += `<option value="${building.id}">${building.name}</option>`;
                    });
                } else {
                    console.error("API response for buildings was not successful or data is missing:", response);
                    showCustomMessageBox(response.message || "Error loading buildings.", 'danger');
                }
            })
            .catch(error => {
                console.error("Error loading buildings:", error);
                showCustomMessageBox("Failed to load buildings. Please try again.", 'danger');
            });
    }

    function updateStaff(event) {
        event.preventDefault();
    let staffId = window.location.pathname.replace(/\/$/, "").split("/").pop();
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            return; // Stop the form submission if token is not available
        }

        let formData = {
            name: document.getElementById("name").value.trim(),
            email: document.getElementById("email").value.trim(),
            building_id: document.getElementById("building_id").value,
            role: document.getElementById("role").value
        };

        fetch(`{{ url('/api/admin/staff/update') }}/${staffId}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken, // Now correctly uses the retrieved token
                'token': localStorage.getItem('token'), // Include token for authentication
                'auth-id': localStorage.getItem('auth-id') // Include auth-id for authorization 
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                // If the response is not OK (e.g., 4xx or 5xx status), parse the error
                return response.json().then(errorData => {
                    // Throw an error with the message from the API, or a generic one
                    throw new Error(errorData.message || `HTTP error! Status: ${response.status}`);
                });
            }
            return response.json(); // If OK, parse the successful response
        })
        .then(response => {
            if (response.success) {
                showCustomMessageBox(response.message || "Staff updated successfully!", 'success');
                document.getElementById("updateStaffForm").reset(); // Clear the form
            } else {
                showCustomMessageBox(response.message || "Something went wrong.", 'danger');
            }
        })
        .catch(error => {
            console.error("Error updating staff:", error);
            showCustomMessageBox(error.message || "Failed to update staff. Please try again.", 'danger');
        });
    }
});
</script>
@endsection
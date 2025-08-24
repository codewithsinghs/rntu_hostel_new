@extends('superadmin.layout')

@section('content')
<div class="container mt-4">
    <h2>Admins</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mt-3">Admin List</h4>
        <a href="{{ route('superadmin.create_admin') }}" class="btn btn-primary">Create Admin</a>
    </div>

    <div id="message-container" class="alert d-none" role="alert"></div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="admin-list">
            </tbody>
    </table>
</div>

<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmationModalBody">
                Are you sure you want to proceed?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmActionButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
        const messageContainer = document.getElementById("message-container");
        let currentAdminIdToDelete = null; // Variable to store the ID of the admin to be deleted

        // Initialize Bootstrap Modal (if using Bootstrap 5, otherwise adjust)
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        const confirmActionButton = document.getElementById('confirmActionButton');

        // Event listener for the confirmation button in the modal
        confirmActionButton.addEventListener('click', function() {
            if (currentAdminIdToDelete !== null) {
                proceedDeleteAdmin(currentAdminIdToDelete);
                currentAdminIdToDelete = null; // Reset after action
            }
            confirmationModal.hide();
        });

        /**
         * Fetches the list of administrators from the API and populates the table.
         */
        function fetchAdmins() {
            fetch("{{ url('/api/admins') }}")
                .then(response => response.json())
                .then(responseData => { // Changed 'data' to 'responseData' for clarity
                    const adminList = document.getElementById("admin-list");
                    adminList.innerHTML = ""; // Clear existing content

                    // Check if the API response is successful and contains an array of data
                    if (responseData.success && Array.isArray(responseData.data) && responseData.data.length > 0) {
                        responseData.data.forEach(admin => {
                            adminList.innerHTML += renderRow(admin);
                        });
                    } else {
                        adminList.innerHTML = `<tr><td colspan="4" class="text-center">No admins found.</td></tr>`;
                    }
                    // Re-attach event listeners after new rows are rendered
                    attachEditListeners();
                    attachSaveListeners();
                    attachCancelListeners();
                    attachDeleteListeners();
                })
                .catch(error => {
                    console.error('Error fetching admins:', error);
                    const adminList = document.getElementById("admin-list");
                    adminList.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error loading admins. Please try again.</td></tr>`;
                    showMessage("Error loading admins. Please try again.", "danger");
                });
        }

        /**
         * Renders a single admin row for the table.
         * @param {object} admin - The admin object.
         * @returns {string} The HTML string for the table row.
         */
        function renderRow(admin) {
            return `
                <tr id="admin-${admin.id}">
                    <td>${admin.id}</td>
                    <td>
                        <span class="admin-name">${admin.name}</span>
                        <input class="form-control d-none name-input" type="text" value="${admin.name}">
                    </td>
                    <td>
                        <span class="admin-email">${admin.email}</span>
                        <input class="form-control d-none email-input" type="email" value="${admin.email}">
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning edit-btn" data-id="${admin.id}">Edit</button>
                        <button class="btn btn-sm btn-success save-btn d-none" data-id="${admin.id}">Save</button>
                        <button class="btn btn-sm btn-secondary cancel-btn d-none" data-id="${admin.id}">Cancel</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${admin.id}">Delete</button>
                    </td>
                </tr>
            `;
        }

        /**
         * Attaches click listeners to all "Edit" buttons.
         */
        function attachEditListeners() {
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.removeEventListener("click", handleEditClick); // Prevent duplicate listeners
                button.addEventListener("click", handleEditClick);
            });
        }

        /**
         * Handles the click event for "Edit" buttons.
         * @param {Event} event - The click event.
         */
        function handleEditClick(event) {
            const row = document.getElementById(`admin-${this.dataset.id}`);
            row.querySelector(".admin-name").classList.add("d-none");
            row.querySelector(".admin-email").classList.add("d-none");
            row.querySelector(".name-input").classList.remove("d-none");
            row.querySelector(".email-input").classList.remove("d-none");

            row.querySelector(".edit-btn").classList.add("d-none");
            row.querySelector(".delete-btn").classList.add("d-none");
            row.querySelector(".save-btn").classList.remove("d-none");
            row.querySelector(".cancel-btn").classList.remove("d-none");
        }

        /**
         * Attaches click listeners to all "Save" buttons.
         */
        function attachSaveListeners() {
            document.querySelectorAll(".save-btn").forEach(button => {
                button.removeEventListener("click", handleSaveClick); // Prevent duplicate listeners
                button.addEventListener("click", handleSaveClick);
            });
        }

        /**
         * Handles the click event for "Save" buttons.
         * @param {Event} event - The click event.
         */
        function handleSaveClick(event) {
            const id = this.dataset.id;
            const row = document.getElementById(`admin-${id}`);
            const newName = row.querySelector(".name-input").value;
            const newEmail = row.querySelector(".email-input").value;

            fetch(`{{ url('/api/admins') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json' // Added Accept header
                },
                body: JSON.stringify({ name: newName, email: newEmail })
            })
            .then(res => res.json())
            .then(updatedData => {
                // Check if the update was successful and data is returned
                if (updatedData.success && updatedData.data) {
                    const admin = updatedData.data; // Use the updated data from the response
                    row.querySelector(".admin-name").textContent = admin.name;
                    row.querySelector(".admin-email").textContent = admin.email;

                    toggleViewMode(row);
                    showMessage("Admin updated successfully!", "success");
                } else {
                    console.error("Update failed or no data returned:", updatedData.message);
                    showMessage("Failed to update admin: " + (updatedData.message || "Unknown error"), "danger");
                }
            })
            .catch(error => {
                console.error("Error updating admin:", error);
                showMessage("Error updating admin.", "danger");
            });
        }

        /**
         * Attaches click listeners to all "Cancel" buttons.
         */
        function attachCancelListeners() {
            document.querySelectorAll(".cancel-btn").forEach(button => {
                button.removeEventListener("click", handleCancelClick); // Prevent duplicate listeners
                button.addEventListener("click", handleCancelClick);
            });
        }

        /**
         * Handles the click event for "Cancel" buttons.
         * @param {Event} event - The click event.
         */
        function handleCancelClick(event) {
            const id = this.dataset.id;
            const row = document.getElementById(`admin-${id}`);
            // Revert input values to original span text if needed (optional, but good for UX)
            row.querySelector(".name-input").value = row.querySelector(".admin-name").textContent;
            row.querySelector(".email-input").value = row.querySelector(".admin-email").textContent;
            toggleViewMode(row);
        }

        /**
         * Toggles the display mode between view and edit for a given row.
         * @param {HTMLElement} row - The table row element.
         */
        function toggleViewMode(row) {
            row.querySelector(".admin-name").classList.remove("d-none");
            row.querySelector(".admin-email").classList.remove("d-none");
            row.querySelector(".name-input").classList.add("d-none");
            row.querySelector(".email-input").classList.add("d-none");

            row.querySelector(".edit-btn").classList.remove("d-none");
            row.querySelector(".delete-btn").classList.remove("d-none");
            row.querySelector(".save-btn").classList.add("d-none");
            row.querySelector(".cancel-btn").classList.add("d-none");
        }

        /**
         * Attaches click listeners to all "Delete" buttons.
         */
        function attachDeleteListeners() {
            document.querySelectorAll(".delete-btn").forEach(button => {
                button.removeEventListener("click", handleDeleteClick); // Prevent duplicate listeners
                button.addEventListener("click", handleDeleteClick);
            });
        }

        /**
         * Handles the click event for "Delete" buttons, showing a confirmation modal.
         * @param {Event} event - The click event.
         */
        function handleDeleteClick(event) {
            currentAdminIdToDelete = this.dataset.id;
            document.getElementById('confirmationModalBody').textContent = `Are you sure you want to delete admin with ID: ${currentAdminIdToDelete}?`;
            confirmationModal.show();
        }

        /**
         * Proceeds with the deletion of an admin after confirmation.
         * @param {string} id - The ID of the admin to delete.
         */
        function proceedDeleteAdmin(id) {
            fetch(`{{ url('/api/admins') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json' // Added Accept header
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) { // Assuming API returns { success: true, message: "..." }
                    document.getElementById(`admin-${id}`).remove(); // Remove the row from the table
                    showMessage("Admin deleted successfully!", "success");
                } else {
                    console.error("Delete failed:", data.message);
                    showMessage("Failed to delete admin: " + (data.message || "Unknown error"), "danger");
                }
            })
            .catch(error => {
                console.error("Error deleting admin:", error);
                showMessage("Error deleting admin.", "danger");
            });
        }

        /**
         * Displays a temporary message to the user.
         * @param {string} message - The message to display.
         * @param {string} type - The type of alert (e.g., "success", "danger", "warning").
         */
        function showMessage(message, type) {
            messageContainer.textContent = message;
            messageContainer.className = `alert alert-${type}`; // Reset classes
            messageContainer.classList.remove("d-none");
            setTimeout(() => messageContainer.classList.add("d-none"), 5000); // Hide after 5 seconds
        }

        // Initial fetch of admins when the page loads
        fetchAdmins();
    });
</script>
@endsection

@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">Staff Management</h2>

            <div id="mainResponseMessage" class="mt-3"></div> {{-- Message container for the page --}}

            <div class="d-flex justify-content-end mb-3">
                <a href="/admin/staff/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Staff
                </a>
            </div>

            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Staff List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="staffList">
                        <thead class="table-dark">
                            <tr>
                                <th>S.No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Building Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6" class="text-center">Loading staff members...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
// Function to show a custom message box
function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
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
    fetchStaff();

    function fetchStaff() {
        fetch("{{ url('/api/admin/staff-list') }}", {
            method: "GET",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                'token': localStorage.getItem('token'), // Include token for authentication
                'auth-id': localStorage.getItem('auth-id') // Include auth-id for authorization
            }
        })
            .then(response => response.json())
            .then(response => { // Changed 'data' to 'response' for consistency
                const staffList = document.querySelector("#staffList tbody");
                // Assuming the staff data is directly under the 'data' key
                const staffMembers = response.data;
                const allRoles = ['warden', 'cleaner', 'caretaker', 'security']; // Define all possible roles

                staffList.innerHTML = ""; // Clear existing content

                if (!response.success || !Array.isArray(staffMembers) || staffMembers.length === 0) {
                    staffList.innerHTML = `<tr><td colspan="6" class="text-center">No staff found.</td></tr>`;
                    if (!response.success && response.message) {
                        showCustomMessageBox(response.message, 'info'); // Use info for "no staff found"
                    }
                    return;
                }

                staffMembers.forEach((staff, index) => {
                    let roles = staff.roles && staff.roles.length > 0 ? staff.roles.map(role => role.name).join(', ') : 'N/A';
                    let buildingName = staff.building&&staff.building.length > 0  ? staff.building.map(build => build.name).join(', ') : 'N/A';
                    // console.log(staff.building.name);
                    let row = `
                        <tr data-id="${staff.id}">
                            <td>${index + 1}</td>
                            <td class="name">${staff.name || 'N/A'}</td>
                            <td class="email">${staff.email || 'N/A'}</td>
                            <td class="roles">${roles}</td>
                            <td class="building-name">${staff.building.name}</td>
                            <td>
                                <a class="btn btn-sm btn-warning me-1" href="/admin/staff/edit/${staff.id}">Edit</a>
                            </td>
                        </tr>
                    `;
                    staffList.innerHTML += row;
                });

                if (response.message) {
                    showCustomMessageBox(response.message, 'success');
                }
            })
            .catch(error => {
                console.error('Error fetching staff:', error);
                document.querySelector("#staffList tbody").innerHTML = `
                    <tr><td colspan="6" class="text-center text-danger">Failed to load staff.</td></tr>
                `;
                showCustomMessageBox(error.message || 'Failed to load staff.', 'danger');
            });
    }

    // function attachEditListeners(allRoles) {
    //     document.querySelectorAll(".edit-btn").forEach(button => {
    //         button.addEventListener("click", function () {
    //             const row = this.closest("tr");
    //             const id = row.dataset.id;

    //             const nameCell = row.querySelector(".name");
    //             const emailCell = row.querySelector(".email");
    //             const rolesCell = row.querySelector(".roles");
    //             const buildingNameCell = row.querySelector(".building-name");

    //             if (this.textContent === "Edit") {
    //                 nameCell.innerHTML = `<input type="text" class="form-control form-control-sm" value="${nameCell.textContent.trim()}">`;
    //                 emailCell.innerHTML = `<input type="email" class="form-control form-control-sm" value="${emailCell.textContent.trim()}">`;

    //                 let currentRolesText = rolesCell.textContent.trim();
    //                 let currentRolesArray = currentRolesText === 'N/A' ? [] : currentRolesText.split(', ').map(role => role.trim());

    //                 let roleOptions = allRoles.map(role => {
    //                     return `<option value="${role}" ${currentRolesArray.includes(role) ? 'selected' : ''}>${role.charAt(0).toUpperCase() + role.slice(1)}</option>`;
    //                 }).join('');
    //                 let buildingOptions = allRoles.map(role => {
    //                     return `<option value="${role}" ${currentRolesArray.includes(role) ? 'selected' : ''}>${role.charAt(0).toUpperCase() + role.slice(1)}</option>`;
    //                 }).join('');

    //                 rolesCell.innerHTML = `<select class="form-select form-select-sm">${roleOptions}</select>`;
    //                 buildingNameCell.innerHTML = `<select class="form-control form-control-sm">${buildingNameCell}<select>`;
    //               //  buildingNameCell.innerHTML = `<input type="text" class="form-control form-control-sm" value="${buildingNameCell.textContent.trim()}">`;

    //                 this.textContent = "Save";
    //                 this.classList.remove("btn-warning");
    //                 this.classList.add("btn-success");
    //             } else {
    //                 const newName = nameCell.querySelector("input").value;
    //                 const newEmail = emailCell.querySelector("input").value;
    //                 const newRole = rolesCell.querySelector("select").value;
    //                 const newBuildingName = buildingNameCell.querySelector("select").value;

    //                 fetch(`/api/admin/staff/update/${id}`, {
    //                     method: "PUT",
    //                     headers: {
    //                         "Content-Type": "application/json",
    //                         "Accept": "application/json",
    //                         "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"), // Include CSRF token
    //                         'token': localStorage.getItem('token'),
    //                         'auth-id': localStorage.getItem('auth-id'),
    //                     },
    //                     body: JSON.stringify({
    //                         name: newName,
    //                         email: newEmail,
    //                         role: newRole, // Assuming the API expects a single role string
    //                         building_name: newBuildingName // Assuming this can be updated directly
    //                     })
    //                 })
    //                 .then(response => {
    //                     if (!response.ok) {
    //                         return response.json().then(errorData => {
    //                             throw new Error(errorData.message || "Update failed");
    //                         });
    //                     }
    //                     return response.json();
    //                 })
    //                 .then(response => { // Changed 'updated' to 'response'
    //                     if (response.success) {
    //                         // Update the displayed values with the new values
    //                         nameCell.textContent = newName;
    //                         emailCell.textContent = newEmail;
    //                         rolesCell.textContent = newRole.charAt(0).toUpperCase() + newRole.slice(1); // Display capitalized role
    //                         buildingNameCell.textContent = newBuildingName.charAt(0).toUpperCase() + newRole.slice(1); // Assuming API returns updated building name

    //                         this.textContent = "Edit";
    //                         this.classList.remove("btn-success");
    //                         this.classList.add("btn-warning");
    //                         showCustomMessageBox(response.message || "Staff details updated successfully.", 'success');
    //                     } else {
    //                         showCustomMessageBox(response.message || "Failed to update staff details.", 'danger');
    //                     }
    //                 })
    //                 .catch(error => {
    //                     console.error("Update failed:", error);
    //                     showCustomMessageBox(error.message || "Failed to update staff details.", 'danger');
    //                 });
    //             }
    //         });
    //     });
//     }
});
</script>
@endsection

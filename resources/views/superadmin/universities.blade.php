@extends('superadmin.layout')

@section('content')
<div class="container mt-4">
    <h2>Universities</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mt-3">University List</h4>
        <a href="{{ route('superadmin.create_university') }}" class="btn btn-primary">Create University</a>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Location</th>
                <th>State</th>
                <th>District</th>
                <th>Pincode</th>
                <th>Address</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="university-list">
            </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetchUniversities();
    });

    function fetchUniversities() {
        fetch("{{ url('/api/universities') }}")
            .then(response => response.json())
            .then(responseData => { // Changed 'data' to 'responseData' to avoid confusion
                const list = document.getElementById("university-list");
                list.innerHTML = ""; // Clear existing content

                // Check if responseData.data exists and is an array
                if (responseData.success && Array.isArray(responseData.data) && responseData.data.length > 0) {
                    responseData.data.forEach(u => {
                        list.innerHTML += generateRow(u);
                    });
                } else {
                    list.innerHTML = `<tr><td colspan="10" class="text-center">No universities found.</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error fetching universities:', error);
                const list = document.getElementById("university-list");
                list.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error loading universities. Please try again.</td></tr>`;
            });
    }

    function generateRow(u) {
        return `
            <tr id="row-${u.id}">
                <td>${u.id}</td>
                <td><span class="text" id="name-${u.id}">${u.name}</span><input class="form-control d-none" type="text" id="edit-name-${u.id}" value="${u.name}"></td>
                <td><span class="text" id="location-${u.id}">${u.location}</span><input class="form-control d-none" type="text" id="edit-location-${u.id}" value="${u.location}"></td>
                <td><span class="text" id="state-${u.id}">${u.state}</span><input class="form-control d-none" type="text" id="edit-state-${u.id}" value="${u.state}"></td>
                <td><span class="text" id="district-${u.id}">${u.district}</span><input class="form-control d-none" type="text" id="edit-district-${u.id}" value="${u.district}"></td>
                <td><span class="text" id="pincode-${u.id}">${u.pincode}</span><input class="form-control d-none" type="text" id="edit-pincode-${u.id}" value="${u.pincode}"></td>
                <td><span class="text" id="address-${u.id}">${u.address}</span><input class="form-control d-none" type="text" id="edit-address-${u.id}" value="${u.address}"></td>
                <td><span class="text" id="mobile-${u.id}">${u.mobile}</span><input class="form-control d-none" type="text" id="edit-mobile-${u.id}" value="${u.mobile}"></td>
                <td><span class="text" id="email-${u.id}">${u.email}</span><input class="form-control d-none" type="text" id="edit-email-${u.id}" value="${u.email}"></td>
                <td>
                    <button class="btn btn-warning btn-sm" id="edit-btn-${u.id}" onclick="enableEdit(${u.id})">Edit</button>
                    <button class="btn btn-success btn-sm d-none" id="save-btn-${u.id}" onclick="saveEdit(${u.id})">Save</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteUniversity(${u.id})">Delete</button>
                </td>
            </tr>
        `;
    }

    function enableEdit(id) {
        // Hide spans, show inputs
        document.querySelectorAll(`#row-${id} .text`).forEach(span => span.classList.add('d-none'));
        document.querySelectorAll(`#row-${id} input`).forEach(input => input.classList.remove('d-none'));

        // Toggle buttons
        document.getElementById(`edit-btn-${id}`).classList.add('d-none');
        document.getElementById(`save-btn-${id}`).classList.remove('d-none');
    }

    function saveEdit(id) {
        const data = {
            name: document.getElementById(`edit-name-${id}`).value,
            location: document.getElementById(`edit-location-${id}`).value,
            state: document.getElementById(`edit-state-${id}`).value,
            district: document.getElementById(`edit-district-${id}`).value,
            pincode: document.getElementById(`edit-pincode-${id}`).value,
            address: document.getElementById(`edit-address-${id}`).value,
            mobile: document.getElementById(`edit-mobile-${id}`).value,
            email: document.getElementById(`edit-email-${id}`).value,
        };

        fetch(`/api/universities/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(updated => {
            // Check if the update was successful and data is returned
            if (updated.success && updated.data) {
                const u = updated.data; // Use the updated data from the response
                // Update row content
                document.getElementById(`name-${u.id}`).textContent = u.name;
                document.getElementById(`location-${u.id}`).textContent = u.location;
                document.getElementById(`state-${u.id}`).textContent = u.state;
                document.getElementById(`district-${u.id}`).textContent = u.district;
                document.getElementById(`pincode-${u.id}`).textContent = u.pincode;
                document.getElementById(`address-${u.id}`).textContent = u.address;
                document.getElementById(`mobile-${u.id}`).textContent = u.mobile;
                document.getElementById(`email-${u.id}`).textContent = u.email;

                // Hide inputs, show spans
                document.querySelectorAll(`#row-${u.id} input`).forEach(input => input.classList.add('d-none'));
                document.querySelectorAll(`#row-${u.id} .text`).forEach(span => span.classList.remove('d-none'));

                // Toggle buttons
                document.getElementById(`save-btn-${u.id}`).classList.add('d-none');
                document.getElementById(`edit-btn-${u.id}`).classList.remove('d-none');
            } else {
                console.error("Update failed or no data returned:", updated.message);
                // Optionally revert changes or show an error message to the user
            }
        })
        .catch(err => console.error("Error updating:", err));
    }

    function deleteUniversity(id) {
        if (confirm("Are you sure you want to delete this university?")) {
            fetch(`/api/universities/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`row-${id}`).remove(); // Remove the row from the table
                    console.log(data.message);
                } else {
                    console.error("Delete failed:", data.message);
                }
            })
            .catch(error => console.error("Error deleting:", error));
        }
    }
</script>
@endsection

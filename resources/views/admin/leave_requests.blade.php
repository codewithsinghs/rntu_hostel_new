@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">Leave Request Management</h2>

            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Leave Request List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="leaveRequestList">
                        <thead class="table-dark">
                            <tr>
                                <th>S.No</th>
                                <th>Resident Name</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Reason</th>
                                <th>HOD Status</th>
                                <th>Admin Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-center">Loading leave requests...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchLeaveRequests();

    function fetchLeaveRequests() {
        let apiUrl = "{{ url('/api/admin/leave-requests') }}";

        fetch(apiUrl, {
            method: "GET",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                let leaveRequestList = document.getElementById("leaveRequestList").querySelector("tbody");
                leaveRequestList.innerHTML = "";

                if (!data.data || data.data.length === 0) {
                    leaveRequestList.innerHTML = `<tr><td colspan="8" class="text-center">No leave requests found.</td></tr>`;
                } else {
                    data.data.forEach((request, index) => {
                        let residentName = request?.resident?.user?.name || 'No Name Found';

                        leaveRequestList.innerHTML += `
                            <tr id="row-${request.id}">
                                <td>${index + 1}</td>
                                <td>${residentName}</td>
                                <td>${new Date(request.from_date).toLocaleDateString()}</td>
                                <td>${new Date(request.to_date).toLocaleDateString()}</td>
                                <td>${request.reason}</td>
                                <td id="hod-status-${request.id}">${request.hod_status}</td>
                                <td id="admin-status-${request.id}">${request.admin_status}</td>
                                <td>
                                    ${request.hod_status === 'pending' ? `
                                        <button class="btn btn-success btn-sm" onclick="updateStatus(${request.id}, 'hod', 'approve')">HOD Approve</button>
                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(${request.id}, 'hod', 'deny')">HOD Deny</button>
                                    ` : ''}
                                    
                                    ${request.hod_status === 'approved' && request.admin_status === 'pending' ? `
                                        <button class="btn btn-success btn-sm" onclick="updateStatus(${request.id}, 'admin', 'approve')">Admin Approve</button>
                                        <button class="btn btn-danger btn-sm" onclick="updateStatus(${request.id}, 'admin', 'deny')">Admin Deny</button>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });
                }
            })
            .catch(error => {
                console.error("Error fetching leave requests:", error);
                document.getElementById("leaveRequestList").querySelector("tbody").innerHTML = `
                    <tr><td colspan="8" class="text-center text-danger">Failed to load leave requests.</td></tr>
                `;
            });
    }

    window.updateStatus = function (id, role, action) {
        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let url = `{{ url('/api/admin/leave-requests') }}/${id}/${role}-${action}`;

        fetch(url, {
            method: "PATCH",
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json",
                "Content-Type": "application/json",
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
            fetchLeaveRequests(); // Refresh
        })
        .catch(error => console.error("Error updating leave request:", error));
    };
});
</script>

@endsection

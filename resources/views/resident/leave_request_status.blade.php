@extends('resident.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">Your Leave Requests</h2>

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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="8" class="text-center">Loading leave requests...</td></tr>
                        </tbody>
                    </table>
                    <p id="no-requests" class="text-danger text-center mt-3" style="display: none;">No leave requests found.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for View Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Leave Request Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receiptDetails"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printReceiptBtn">Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tableBody = document.querySelector("#leaveRequestList tbody");
    const noRequestsMsg = document.getElementById("no-requests");

    const apiUrl = `/api/resident/leave-requests`;

    fetch(apiUrl, {
        method: "GET",
        headers: {
            "Accept": "application/json",       
            'token': localStorage.getItem('token'),
            'auth-id': localStorage.getItem('auth-id')
        }
    })
        .then(response => response.json())
        .then(data => {
            tableBody.innerHTML = "";

            if (data.data && data.data.length > 0) {
                data.data.forEach((request, index) => {
                    tableBody.innerHTML += `
                        <tr data-request='${JSON.stringify(request).replace(/'/g, "&apos;")}'>
                            <td>${index + 1}</td>
                            <td>${request.resident?.user?.name ?? 'N/A'}</td>
                            <td>${new Date(request.from_date).toLocaleDateString()}</td>
                            <td>${new Date(request.to_date).toLocaleDateString()}</td>
                            <td>${request.reason}</td>
                            <td>${request.hod_status}</td>
                            <td>${request.admin_status}</td>
                            <td><button class="btn btn-primary" onclick="viewReceipt(this)">View Receipt</button></td>
                        </tr>
                    `;
                });
            } else {
                noRequestsMsg.style.display = "block";
            }
        })
        .catch(error => {
            console.error("Error fetching leave requests:", error);
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Failed to load leave requests.</td></tr>`;
        });
});

// View Receipt using row data
function viewReceipt(button) {
    const row = button.closest("tr");
    const request = JSON.parse(row.getAttribute("data-request").replace(/&apos;/g, "'"));

    const receiptDetails = `
        <div class="card border-primary">
            <div class="card-header">
                <h5>Leave Request Receipt</h5>
            </div>
            <div class="card-body">
                <p><strong>Resident Name:</strong> ${request.resident?.user?.name ?? 'N/A'}</p>
                <p><strong>From Date:</strong> ${new Date(request.from_date).toLocaleDateString()}</p>
                <p><strong>To Date:</strong> ${new Date(request.to_date).toLocaleDateString()}</p>
                <p><strong>Reason:</strong> ${request.reason}</p>
                <p><strong>HOD Status:</strong> ${request.hod_status}</p>
                <p><strong>Admin Status:</strong> ${request.admin_status}</p>
            </div>
        </div>
    `;
    document.getElementById('receiptDetails').innerHTML = receiptDetails;

    var receiptModal = new bootstrap.Modal(document.getElementById('receiptModal'));
    receiptModal.show();
}

// Print the receipt
document.getElementById('printReceiptBtn')?.addEventListener('click', function () {
    const printContent = document.getElementById('receiptDetails').innerHTML;
    const printWindow = window.open('', '', 'width=600,height=600');
    printWindow.document.write('<html><head><title>Print Receipt</title></head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
});
</script>

@endsection

<!-- Bootstrap JS (with Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

@extends('guest.layout')

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div class="container mt-4">
    <h3 class="mb-3">Guest Request Status</h3>

    <div id="mainResponseMessage"></div> {{-- Message container for general messages --}}

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>S.No.</th>
                <th>Scholar No</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="guestList">
            <tr><td colspan="4" class="text-center">Loading guest requests...</td></tr>
        </tbody>
    </table>
</div>

{{-- Modal for Waiver Rejected Information --}}
<div class="modal fade" id="waiverRejectedInfoModal" tabindex="-1" aria-labelledby="waiverRejectedInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="waiverRejectedInfoModalLabel">Waiver Rejected Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your fee waiver request has been rejected. You can still proceed with the normal payment process if you wish to continue your application.</p>
                <div class="text-center mt-3">
                    <button class="btn btn-success" id="proceedToNormalPaymentBtn">Pay as Normal & Continue</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchGuestStatus();

    // Initialize the modal once DOM is ready
    waiverRejectedInfoModal = new bootstrap.Modal(document.getElementById('waiverRejectedInfoModal'));
});

function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : null;
}

function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
    const messageContainer = document.getElementById(targetElementId);
    if (messageContainer) {
        messageContainer.innerHTML = "";
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        messageContainer.appendChild(alertDiv);
    } else {
        console.warn(`Message container #${targetElementId} not found.`);
    }
}

function fetchGuestStatus() {
    fetch("{{ url('/api/aproved/rejected/guest') }}")
        .then(response => {
            if (!response.ok) {
                throw new Error("Failed to load data");
            }
            return response.json();
        })
        .then(data => {
            let guestList = document.getElementById("guestList");
            guestList.innerHTML = "";

            if (!data.data || data.data.length === 0) {
                guestList.innerHTML = `<tr><td colspan="4" class="text-center">No approved, rejected, or pending guests found.</td></tr>`;
                return;
            }

            let serialNumber = 1;

            data.data.forEach(guest => {
                const normalizedStatus = guest.status.trim().toLowerCase();
                let statusClass = getStatusClass(normalizedStatus);
                let displayStatusText = guest.status; // Default display text for badge

                let actionColumn = '-'; // Default action

                switch (normalizedStatus) {
                    case 'approved':
                        displayStatusText = 'Application approved';
                        actionColumn = `<button class="btn btn-primary btn-sm" onclick="makePayment(${guest.id})"><i class="fa fa-credit-card"></i> Make Payment</button>`;
                        break;
                    case 'rejected':
                        displayStatusText = 'Application rejected';
                        actionColumn = '-';
                        break;
                    case 'waiver_approved':
                        displayStatusText = 'Waiver approved';
                        actionColumn = `<button class="btn btn-primary btn-sm" onclick="makePayment(${guest.id})"><i class="fa fa-credit-card"></i> Make Payment</button>`;
                        break;
                    case 'waiver_rejected':
                        displayStatusText = 'Waiver rejected';
                        actionColumn = `<button class="btn btn-warning btn-sm" onclick="showWaiverRejectedMessage(${guest.id})">Details / Pay</button>`;
                        break;
                    case 'pending':
                        displayStatusText = 'Pending';
                        actionColumn = '-';
                        break;
                    default:
                        // Use default guest.status for unknown statuses
                        break;
                }

                guestList.innerHTML += `
                    <tr>
                        <td>${serialNumber++}</td>
                        <td>${guest.scholar_no}</td>
                        <td><span class="badge ${statusClass}">${displayStatusText}</span></td>
                        <td>${actionColumn}</td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error fetching guest status:', error);
            document.getElementById("guestList").innerHTML = `
                <tr><td colspan="4" class="text-center text-danger">Failed to load guest requests. Please try again later.</td></tr>`;
            showCustomMessageBox('Failed to load guest requests. Please try again later.', 'danger');
        });
}

function getStatusClass(status) {
    switch (status) {
        case 'approved': return 'bg-success text-white';
        case 'waiver_approved': return 'bg-success text-white';
        case 'rejected': return 'bg-danger text-white';
        case 'waiver_rejected': return 'bg-warning text-dark';
        case 'pending': return 'bg-secondary text-white';
        default: return 'bg-secondary text-white';
    }
}

let waiverRejectedInfoModal; // Declare globally

// Function to show the waiver rejected message modal
window.showWaiverRejectedMessage = function(guestId) {
    const proceedBtn = document.getElementById('proceedToNormalPaymentBtn');
    if (proceedBtn) {
        // Ensure we remove any old event listeners before adding a new one
        // This prevents multiple calls if the button is clicked multiple times
        const oldProceedHandler = proceedBtn.onclick; // Get the existing handler if any
        if (oldProceedHandler) {
            proceedBtn.removeEventListener('click', oldProceedHandler);
        }
        // Attach a new event listener that calls makePayment with the correct guestId
        proceedBtn.onclick = () => {
            waiverRejectedInfoModal.hide(); // Hide this modal
            makePayment(guestId); // Proceed to the original makePayment function
        };
    }
    waiverRejectedInfoModal.show();
};

function makePayment(guestId) {
    showCustomMessageBox("Redirecting to payment page for Guest ID: " + guestId, 'info');
    window.location.href = "{{ url('/guest/payment') }}/" + guestId;
}
</script>

@endsection

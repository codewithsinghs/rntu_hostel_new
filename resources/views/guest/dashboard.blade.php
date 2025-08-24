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
                <tr>
                    <td colspan="4" class="text-center">Loading guest requests...</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Modal for Waiver Rejected Information --}}
    <div class="modal fade" id="waiverRejectedInfoModal" tabindex="-1" aria-labelledby="waiverRejectedInfoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="waiverRejectedInfoModalLabel">Waiver Rejected Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Your fee waiver request has been rejected. You can still proceed with the normal payment process if
                        you wish to continue your application.</p>
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
    {{-- <script>
        document.addEventListener("DOMContentLoaded", function() {
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
            fetch("{{ url('/api/guest/approved-rejected-guest') }}", {
                    method: "GET",
                    headers: {
                        "Accept": "application/json",
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
                    }
                })
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
                        guestList.innerHTML =
                            `<tr><td colspan="4" class="text-center">No approved, rejected, or pending guests found.</td></tr>`;
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
                                actionColumn =
                                    `<button class="btn btn-primary btn-sm" onclick="makePayment(${guest.id})"><i class="fa fa-credit-card"></i> Make Payment</button>`;
                                break;
                            case 'rejected':
                                displayStatusText = 'Application rejected';
                                actionColumn = '-';
                                break;
                            case 'waiver_approved':
                                displayStatusText = 'Waiver approved';
                                actionColumn =
                                    `<button class="btn btn-primary btn-sm" onclick="makePayment(${guest.id})"><i class="fa fa-credit-card"></i> Make Payment</button>`;
                                break;
                            case 'waiver_rejected':
                                displayStatusText = 'Waiver rejected';
                                actionColumn =
                                    `<button class="btn btn-warning btn-sm" onclick="showWaiverRejectedMessage(${guest.id})">Details / Pay</button>`;
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
                        <td>${guest.scholar_number}</td>
                        <td><span class="badge ${statusClass}">${displayStatusText}</span></td>
                        <td>${actionColumn}</td>
                    </tr>
                `;
                    });
                })
                .catch(error => {
                    console.error('Error fetching guest status:', error);
                    document.getElementById("guestList").innerHTML =
                        `
                <tr><td colspan="4" class="text-center text-danger">Failed to load guest requests. Please try again later.</td></tr>`;
                    showCustomMessageBox('Failed to load guest requests. Please try again later.', 'danger');
                });
        }

        function getStatusClass(status) {
            switch (status) {
                case 'approved':
                    return 'bg-success text-white';
                case 'waiver_approved':
                    return 'bg-success text-white';
                case 'rejected':
                    return 'bg-danger text-white';
                case 'waiver_rejected':
                    return 'bg-warning text-dark';
                case 'pending':
                    return 'bg-secondary text-white';
                default:
                    return 'bg-secondary text-white';
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
                    makePayment(); // Proceed to the original makePayment function
                };
            }
            waiverRejectedInfoModal.show();
        };

        function makePayment() {

            window.location.href = "{{ url('/guest/payment') }}";
        }
    </script> --}}
    <script>
        let waiverRejectedInfoModal; // Declare globally

        document.addEventListener("DOMContentLoaded", () => {
            console.log("DOM fully loaded and parsed.");
            initializeModal();
            fetchGuestStatus();
        });

        function initializeModal() {
            const modalElement = document.getElementById('waiverRejectedInfoModal');
            if (modalElement) {
                waiverRejectedInfoModal = new bootstrap.Modal(modalElement);
                console.log("Modal initialized.");
            } else {
                console.warn("Modal element #waiverRejectedInfoModal not found.");
            }
        }

        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log("CSRF Token:", token);
            return token || null;
        }

        function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
            const container = document.getElementById(targetElementId);
            if (container) {
                container.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
                console.log(`Displayed message: "${message}" with type "${type}"`);
            } else {
                console.warn(`Message container #${targetElementId} not found.`);
            }
        }

        function fetchGuestStatus() {
            console.log("Fetching guest status...");
            fetch("{{ url('/api/guest/approved-rejected-guest') }}", {
                    method: "GET",
                    headers: {
                        "Accept": "application/json",
                        "token": localStorage.getItem("token"),
                        "auth-id": localStorage.getItem("auth-id")
                    }
                })
                .then(response => {
                    console.log("Response received:", response);
                    if (!response.ok) throw new Error("Failed to load data");
                    return response.json();
                })
                .then(data => {
                    console.log("Guest data:", data);
                    const guestList = document.getElementById("guestList");
                    guestList.innerHTML = "";

                    if (!data.data || data.data.length === 0) {
                        guestList.innerHTML = `<tr><td colspan="4" class="text-center">No guests found.</td></tr>`;
                        return;
                    }

                    let serial = 1;
                    data.data.forEach(guest => {
                        const status = guest.status.trim().toLowerCase();
                        const statusClass = getStatusClass(status);
                        let displayText = guest.status;
                        let action = '-';

                        switch (status) {
                            case 'approved':
                            case 'waiver_approved':
                                displayText = status === 'approved' ? 'Application approved' :
                                'Waiver approved';
                                action =
                                    `<button class="btn btn-primary btn-sm" onclick="makePayment(${guest.id})"><i class="fa fa-credit-card"></i> Make Payment</button>`;
                                break;
                            case 'rejected':
                                displayText = 'Application rejected';
                                break;
                            case 'waiver_rejected':
                                displayText = 'Waiver rejected';
                                action =
                                    `<button class="btn btn-warning btn-sm" onclick="showWaiverRejectedMessage(${guest.id})">Details / Pay</button>`;
                                break;
                            case 'pending':
                                displayText = 'Pending';
                                break;
                        }

                        guestList.innerHTML += `
                    <tr>
                        <td>${serial++}</td>
                        <td>${guest.scholar_number}</td>
                        <td><span class="badge ${statusClass}">${displayText}</span></td>
                        <td>${action}</td>
                    </tr>
                `;
                    });
                })
                .catch(error => {
                    console.error("Error fetching guest status:", error);
                    document.getElementById("guestList").innerHTML = `
                <tr><td colspan="4" class="text-center text-danger">Failed to load guest requests.</td></tr>`;
                    showCustomMessageBox("Failed to load guest requests. Please try again later.", "danger");
                });
        }

        function getStatusClass(status) {
            const classes = {
                approved: 'bg-success text-white',
                waiver_approved: 'bg-success text-white',
                rejected: 'bg-danger text-white',
                waiver_rejected: 'bg-warning text-dark',
                pending: 'bg-secondary text-white'
            };
            return classes[status] || 'bg-secondary text-white';
        }

        window.showWaiverRejectedMessage = function(guestId) {
            console.log("Showing waiver rejected modal for guest ID:", guestId);
            const proceedBtn = document.getElementById('proceedToNormalPaymentBtn');
            if (proceedBtn) {
                proceedBtn.onclick = () => {
                    waiverRejectedInfoModal.hide();
                    makePayment(guestId);
                };
            } else {
                console.warn("Proceed button not found.");
            }
            waiverRejectedInfoModal.show();
        };

        function makePayment(guestId) {
            console.log("Redirecting to payment for guest ID:", guestId);
            window.location.href = "{{ url('/guest/payment') }}";
        }
    </script>
@endsection

@extends('admin.layout')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
    <h2 class="mb-4">All Room Change Requests</h2>

    <div id="mainResponseMessage" class="mt-3"></div> {{-- Main message container for the page --}}

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>S.No</th>
                <th>Resident Name</th>
                <th>Reason</th>
                <th>Preference</th>
                <th>Admin Action</th>
                <th>Resident Confirmation</th>
                <th>Chat</th>
                <th>Final Approval</th>
            </tr>
        </thead>
        <tbody id="admin-room-change-list">
            <tr><td colspan="9" class="text-center">Loading requests...</td></tr>
        </tbody>
    </table>

    <div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Room Change Request Chat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="chatMessages" class="mb-3" style="max-height: 400px; overflow-y: auto;"></div>
                    <div class="d-flex">
                        <textarea id="messageInput" class="form-control" rows="3" placeholder="Type a message..." style="resize: none;"></textarea>
                        <button class="btn btn-primary ms-2" onclick="sendMessage()">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> {{-- Ensure jQuery is loaded --}}

<script>
// Function to show a custom message box
function showCustomMessageBox(message, type = 'info', targetElementId = 'mainResponseMessage') {
    const messageContainer = document.getElementById(targetElementId);
    const modalBody = document.querySelector(`#${targetElementId}`); // Check if target is inside modal
    const actualContainer = modalBody || messageContainer;

    if (actualContainer) {
        actualContainer.innerHTML = ""; // Clear previous messages
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        actualContainer.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 3000); // Remove after 3 seconds
    } else {
        console.warn(`Message container #${targetElementId} not found.`);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    fetchRoomChangeRequests();

    function fetchRoomChangeRequests() {
        fetch(`{{ url('/api/admin/room-change/requests') }}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        }
        )
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        console.error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                        throw new Error(`Server error or invalid response when fetching all room change requests.`);
                    });
                }
                return res.json();
            })
            .then(response => {
                const requests = response.data;
                const list = document.getElementById('admin-room-change-list');
                list.innerHTML = ""; // Clear existing content

                if (!response.success || !Array.isArray(requests) || requests.length === 0) {
                    list.innerHTML = `<tr><td colspan="9" class="text-center">No requests found.</td></tr>`;
                    if (!response.success && response.message) {
                        showCustomMessageBox(response.message, 'danger');
                    }
                    return;
                }

                requests.forEach((request, i) => {
                    const residentName = request.resident_name || 'N/A';
                    const residentId = request.resident_id;
                    const reason = request.reason || 'N/A';
                    const preference = request.preference ?? 'N/A';
                    const adminAction = request.action ?? 'pending';

                    let residentConfirm = '';
                    // Convert to integer for robust comparison
                    const residentAgreeStatus = parseInt(request.resident_agree);

                    // Debugging: Log the actual value and type
                    console.log(`Request ID: ${request.id}, resident_agree: ${request.resident_agree} (Type: ${typeof request.resident_agree}), Parsed: ${residentAgreeStatus}`);

                    if (residentAgreeStatus === 1) { // Explicitly check for integer 1
                        residentConfirm = `<span class="badge bg-success">Confirmed</span>`;
                    } else if (residentAgreeStatus === 0) { // Explicitly check for integer 0
                        residentConfirm = `<span class="badge bg-danger">Denied</span>`;
                    } else { // Covers null, undefined, NaN (if parsing failed), or any other value
                        residentConfirm = `<span class="text-muted">Awaiting Response</span>`;
                    }

                    const isCompleted = adminAction.toLowerCase() === 'completed';
                    const isDeniedByAdmin = adminAction.toLowerCase() === 'not_available';

                    let finalApproveColumn = '';
                    if (isDeniedByAdmin) {
                        finalApproveColumn = `<button class="btn btn-sm btn-secondary" disabled>Denied by Admin</button>`;
                    } else if (isCompleted) {
                        finalApproveColumn = `<span class="badge bg-secondary">Approved</span>`;
                    } else {
                        const approveButton = `<button data-bs-toggle="modal" data-bs-target="#assignBedModal" class="btn btn-sm btn-warning me-1" onclick="finalApprove(${request.id}, ${residentId}, '${residentName.replace(/'/g, "\\'")}')">Final Approve</button>`;
                        const denyButton = `<button class="btn btn-sm btn-danger" onclick="denyRequest(${request.id})">Deny</button>`;
                        finalApproveColumn = `${approveButton}${denyButton}`;
                    }

                    list.innerHTML += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${residentName}</td>
                            <td>${reason}</td>
                            <td>${preference}</td>
                            <td>${formatAdminAction(adminAction)}</td>
                            <td>${residentConfirm}</td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="openChatModal(${request.id}, ${residentAgreeStatus}, '${adminAction}')">Chat</button>
                            </td>
                            <td>${finalApproveColumn}</td>
                        </tr>
                    `;
                });
                showCustomMessageBox("Room change requests loaded successfully.", 'success');
            })
            .catch(error => {
                console.error('Error fetching room change requests:', error);
                document.getElementById('admin-room-change-list').innerHTML =
                    `<tr><td colspan="9" class="text-danger text-center">Failed to load requests. ${error.message}</td></tr>`;
                showCustomMessageBox("Failed to load room change requests. " + error.message, 'danger');
            });
    }

    // Helper function for consistent display of admin actions
    function formatAdminAction(action) {
        switch(action) {
            case 'available': return `<span class="badge bg-info">Approved</span>`;
            case 'not_available': return `<span class="badge bg-danger">Denied</span>`;
            case 'pending': return `<span class="badge bg-warning">Pending</span>`;
            case 'completed': return `<span class="badge bg-primary">Completed</span>`;
            default: return `<span class="badge bg-secondary">Unknown</span>`;
        }
    }


    window.openChatModal = function (requestId, residentAgreeStatus, action) {
        fetchMessages(requestId);
        const modal = new bootstrap.Modal(document.getElementById('chatModal'));
        modal.show();

        const sendButton = document.querySelector("#chatModal button.btn-primary");
        const messageInput = document.getElementById("messageInput");

        // Disable chat if the action is 'not_available' or resident has responded
        if (action === 'not_available') {
            sendButton.disabled = true;
            messageInput.disabled = true;
            messageInput.placeholder = "Chat is locked. The request has been denied by admin.";
        } else if (residentAgreeStatus === 0 || residentAgreeStatus === 1) { // 0 for denied, 1 for confirmed
            sendButton.disabled = true;
            messageInput.disabled = true;
            messageInput.placeholder = "Resident has already responded. Chat is locked.";
        } else {
            sendButton.disabled = false;
            messageInput.disabled = false;
            messageInput.placeholder = "Type a message...";
        }

        document.getElementById("chatMessages").setAttribute("data-request-id", requestId);
    }

    function fetchMessages(requestId) {
        fetch(`{{ url('/api/admin/room-change/all-messages') }}/${requestId}`, {  
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        }
        )
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => {
                        console.error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                        throw new Error(`Server error or invalid response when fetching chat messages.`);
                    });
                }
                return res.json();
            })
            .then(response => {
                const container = document.getElementById("chatMessages");
                container.innerHTML = "";

                const messages = response.data; // Access messages from response.data

                if (Array.isArray(messages) && messages.length > 0) {
                    messages.forEach(msg => {
                        const senderClass = msg.sender === 'admin' ? 'text-success' : 'text-primary';
                        container.innerHTML += `
                            <div class="d-flex mb-2">
                                <div class="me-3">
                                    <strong class="${senderClass}">${msg.sender.charAt(0).toUpperCase() + msg.sender.slice(1)}</strong>:
                                    ${msg.message}
                                    <small class="text-muted d-block">${new Date(msg.created_at).toLocaleString()}</small>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = `<p>No messages yet for this request.</p>`;
                }

                container.scrollTop = container.scrollHeight;
            })
            .catch(err => {
                console.error("Error loading messages:", err);
                document.getElementById('chatMessages').innerHTML = `<p class="text-danger">Failed to load messages. ${err.message}</p>`;
                showCustomMessageBox("Failed to load chat messages. " + err.message, 'danger', 'chatModalMessage');
            });
    }

    window.sendMessage = function () {
        const requestId = document.getElementById("chatMessages").getAttribute("data-request-id");
        const message = document.getElementById("messageInput").value.trim();

        if (!message) {
            showCustomMessageBox("Please enter a message.", 'warning', 'chatModalMessage');
            return;
        }

        fetch(`{{ url('/api/admin/room-change/message') }}/${requestId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify({
                message: message,
                sender: 'admin'
            })
        })
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    console.error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                    throw new Error(`Server error or invalid response when sending message.`);
                });
            }
            return res.json();
        })
        .then(response => {
            if (response.success) {
                document.getElementById("messageInput").value = '';
                fetchMessages(requestId);
                showCustomMessageBox(response.message || "Message sent successfully!", 'success', 'chatModalMessage');
            } else {
                showCustomMessageBox(response.message || "Failed to send message.", 'danger', 'chatModalMessage');
            }
        })
        .catch(err => {
            console.error('Error sending message:', err);
            showCustomMessageBox('An error occurred while sending message. ' + err.message, 'danger', 'chatModalMessage');
        });
    };

    window.denyRequest = function (requestId) {
        const requestBody = {
            action: 'not_available',
            remark: 'Denied by admin'
        };

        fetch(`{{ url('/api/admin/room-change/deny') }}/${requestId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify(requestBody)
        })
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    console.error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                    throw new Error(`Server error or invalid response when denying request.`);
                });
            }
            return res.json();
        })
        .then(response => {
            if (response.success) {
                showCustomMessageBox(response.message || "Request denied successfully!", 'success');
                fetchRoomChangeRequests(); // Refresh the list
            } else {
                showCustomMessageBox(response.message || "Failed to deny request.", 'danger');
            }
        })
        .catch(err => {
            console.error("Deny error:", err);
            showCustomMessageBox("Something went wrong while denying the request. Try again. " + err.message, 'danger');
        });
    };
});
</script>


<div class="modal fade" id="assignBedModal" tabindex="-1" aria-labelledby="assignBedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Final approval: Assign new bed to resident </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="assignBedModalMessage" class="mt-3"></div> {{-- Message container for the modal --}}
                <form id="assignBedModalForm">
                    @csrf
                    <div class="mb-3">
                        <label for="modal_resident_id" class="form-label">Resident</label>
                        <select class="form-select" id="modal_resident_id" name="resident_id" required disabled>
                            <option value="">-- Resident Details --</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-4">
                            <label for="modal_building_id" class="form-label">Select Building</label>
                            <select class="form-select" id="modal_building_id" name="building_id" required>
                                <option value="">-- Select Building --</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="modal_room_id" class="form-label">Select Room</label>
                            <select class="form-select" id="modal_room_id" name="room_id" required>
                                <option value="">-- Select Room --</option>
                            </select>
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="modal_bed_id" class="form-label">Select Bed</label>
                            <select class="form-select" id="modal_bed_id" name="bed_id" required>
                                <option value="">-- Select Available Bed --</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">Assign Bed</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    let currentRoomChangeRequestId = null;
    let currentResidentId = null;

    window.finalApprove = function (requestId, residentId, residentName) {
        currentRoomChangeRequestId = requestId;
        currentResidentId = residentId;

        $('#assignBedModalMessage').empty();

        const residentSelect = $('#modal_resident_id');
        residentSelect.empty().append(`<option value="${residentId}" selected>${residentName}</option>`);

        fetchBuildingsForModal();

        new bootstrap.Modal(document.getElementById('assignBedModal')).show();
    };

    function fetchBuildingsForModal() {
        showCustomMessageBox("Loading buildings...", 'info', 'assignBedModalMessage');
        fetch('/api/admin/buildings', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                        throw new Error(`Server error or invalid response when fetching buildings.`);
                    });
                }
                return response.json();
            })
            .then(data => {
                const buildingSelect = $('#modal_building_id');
                buildingSelect.empty().append('<option value="">-- Select Building --</option>');
                const buildings = data.data;
                if (data.success && Array.isArray(buildings)) {
                    if (buildings.length > 0) {
                        buildings.forEach(building => {
                            buildingSelect.append(`<option value="${building.id}">${building.name}</option>`);
                        });
                        showCustomMessageBox("Buildings loaded successfully.", 'success', 'assignBedModalMessage');
                    } else {
                        buildingSelect.append('<option disabled>No buildings found</option>');
                        showCustomMessageBox("No buildings available.", 'warning', 'assignBedModalMessage');
                    }
                } else {
                    console.error("API response for modal buildings was not successful or data is missing/not an array:", data);
                    showCustomMessageBox(data.message || "Error loading buildings for modal: Invalid data format or no success.", 'danger', 'assignBedModalMessage');
                }
            })
            .catch(error => {
                console.error('Error fetching buildings for modal:', error);
                showCustomMessageBox('Failed to load buildings for bed assignment. ' + error.message, 'danger', 'assignBedModalMessage');
            });
    }

    $('#modal_building_id').on('change', function() {
        let buildingId = $(this).val();
        $('#modal_room_id').empty().append('<option value="">-- Select Room --</option>');
        $('#modal_bed_id').empty().append('<option value="">-- Select Available Bed --</option>');

        if (buildingId) {
            showCustomMessageBox("Loading rooms...", 'info', 'assignBedModalMessage');
            fetch(`/api/admin/buildings/${buildingId}/rooms`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',     
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            console.error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                            throw new Error(`Server error or invalid response when fetching rooms.`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    const roomSelect = $('#modal_room_id');
                    roomSelect.empty().append('<option value="">-- Select Room --</option>');
                    const rooms = data.data;
                    if (data.success && Array.isArray(rooms)) {
                        if (rooms.length > 0) {
                            rooms.forEach(room => {
                                roomSelect.append(`<option value="${room.id}">${room.room_number}</option>`);
                            });
                            showCustomMessageBox("Rooms loaded successfully.", 'success', 'assignBedModalMessage');
                        } else {
                            roomSelect.append('<option disabled>No rooms found for this building</option>');
                            showCustomMessageBox("No rooms available for the selected building.", 'warning', 'assignBedModalMessage');
                        }
                    } else {
                        console.error("API response for modal rooms was not successful or data is missing/not an array:", data);
                        showCustomMessageBox(data.message || "Error loading rooms for modal: Invalid data format or no success.", 'danger', 'assignBedModalMessage');
                    }
                })
                .catch(error => {
                    console.error('Error fetching rooms for modal:', error);
                    showCustomMessageBox('Failed to load rooms for bed assignment. ' + error.message, 'danger', 'assignBedModalMessage');
                });
        } else {
            showCustomMessageBox("Please select a building to load rooms.", 'info', 'assignBedModalMessage');
        }
    });

    $('#modal_room_id').on('change', function() {
        let roomId = $(this).val();
        $('#modal_bed_id').empty().append('<option value="">-- Select Available Bed --</option>');

        if (roomId) {
            showCustomMessageBox("Loading available beds...", 'info', 'assignBedModalMessage');
            fetch(`/api/admin/rooms/${roomId}/available-beds`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id'),
                }
            })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => {
                            console.error(`HTTP error! Status: ${res.status}, Response: ${text}`);
                            throw new Error(`Server error or invalid response when fetching available beds.`);
                        });
                    }
                    return res.json();
                })
                .then(data => {
                    const bedSelect = $('#modal_bed_id');
                    bedSelect.empty().append('<option value="">-- Select Available Bed --</option>');
                    const beds = data.data;
                    if (data.success && Array.isArray(beds)) {
                        if (beds.length > 0) {
                            beds.forEach(bed => {
                                bedSelect.append(`<option value="${bed.id}">Bed Number ${bed.bed_number}</option>`);
                            });
                            showCustomMessageBox("Available beds loaded successfully.", 'success', 'assignBedModalMessage');
                        } else {
                            bedSelect.append('<option disabled>No available beds in this room</option>');
                            showCustomMessageBox("No available beds found for the selected room.", 'warning', 'assignBedModalMessage');
                        }
                    } else {
                        console.error("API response for modal beds was not successful or data is missing/not an array:", data);
                        showCustomMessageBox(data.message || "Error loading available beds for modal: Invalid data format or no success.", 'danger', 'assignBedModalMessage');
                    }
                })
                .catch(error => {
                    console.error('Error fetching available beds for modal:', error);
                    showCustomMessageBox('Failed to load available beds for bed assignment. ' + error.message, 'danger', 'assignBedModalMessage');
                });
        } else {
            showCustomMessageBox("Please select a room to load available beds.", 'info', 'assignBedModalMessage');
        }
    });

    // Handle form submission inside the modal
    $('#assignBedModalForm').submit(function(e) {
        e.preventDefault();

        const newBedId = $('#modal_bed_id').val();

        if (!currentRoomChangeRequestId || !newBedId) {
            showCustomMessageBox("Missing room change request ID or new bed ID. Please try again.", 'warning', 'assignBedModalMessage');
            return;
        }

        $.ajax({
            url: `{{ url('/api/admin/room-change/final-approval') }}/${currentRoomChangeRequestId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')  
            },
            data: JSON.stringify({
                new_bed_id: newBedId,
            }),
            success: function(response) {
                if (response.success) {
                    showCustomMessageBox(response.message || "Room change finalized and bed assigned successfully!", 'success');
                    bootstrap.Modal.getInstance(document.getElementById('assignBedModal')).hide();
                    fetchRoomChangeRequests(); // Refresh the main list
                } else {
                    showCustomMessageBox(response.message || "Failed to finalize room change and assign bed.", 'danger', 'assignBedModalMessage');
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || 'Something went wrong during final approval.';
                showCustomMessageBox(errorMsg, 'danger', 'assignBedModalMessage');
                console.error("Error finalizing room change:", xhr);
            }
        });
    });
});
</script>
@endsection
@extends('resident.layout')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
    <h2 class="mb-4">My Room Change Requests</h2>

    <div id="mainResponseMessage" class="mt-3"></div> {{-- Main message container for the page --}}

    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>S.No</th>
                <th>Reason</th>
                <th>Preference</th>
                <th>Admin Action</th>
                <th>Remark</th>
                <th>Resident Confirmation</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="room-change-list">
            <tr><td colspan="7" class="text-center">Loading requests...</td></tr>
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
                    <div id="chatMessages" class="mb-3" style="max-height: 400px; overflow-y: auto;">
                        </div>
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
    
    // const currentResidentId = "{{ auth()->user()->resident->id ?? '' }}";

    // if (!currentResidentId) {
    //     showCustomMessageBox('Resident not found or not logged in. Cannot fetch requests.', 'danger');
    //     document.getElementById('room-change-list').innerHTML = `<tr><td colspan="7" class="text-center text-danger">Resident ID not available. Please log in.</td></tr>`;
    //     return;
    // }

    fetchRoomChangeRequests();

    function fetchRoomChangeRequests() {
        fetch(`{{ url('/api/resident/room-change/requests') }}`, {
            method: "GET",
            headers: {
                "Accept": "application/json",
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                        throw new Error(`Server error or invalid response when fetching room change requests.`);
                    });
                }
                return response.json();
            })
            .then(response => {
                const requests = response.data;
                const roomChangeList = document.getElementById('room-change-list');
                roomChangeList.innerHTML = "";

                if (!response.success || !Array.isArray(requests) || requests.length === 0) {
                    roomChangeList.innerHTML = `<tr><td colspan="7" class="text-center">No room change requests found.</td></tr>`;
                    if (!response.success && response.message) {
                        showCustomMessageBox(response.message, 'warning');
                    }
                    return;
                }

                requests.forEach((request, index) => {
                    let remark = request.remark?.trim() || 'No remark yet';
                    let status = request.action;
                    let isConfirmed = request.resident_agree === true || request.resident_agree === 1;

                    let residentAgree = '';
                    if (isConfirmed) {
                        residentAgree = `<span class="badge bg-success">Confirmed</span>`;
                    } else if (status === 'available' || status === 'pending') {
                        residentAgree = `<button class="btn btn-primary btn-sm" onclick="confirmRequest(${request.id})">Confirm</button>`;
                    } else if (status === 'not_available') {
                        residentAgree = `<span class="badge bg-danger">Denied</span>`;
                    } else if (status === 'completed') {
                        residentAgree = `<span class="badge bg-success">Assigned</span>`;
                    } else {
                        residentAgree = `<span class="text-muted">Awaiting Admin Approval</span>`;
                    }

                    roomChangeList.innerHTML += `
                        <tr id="row-${request.id}">
                            <td>${index + 1}</td>
                            <td>${request.reason}</td>
                            <td>${request.preference ?? 'N/A'}</td>
                            <td>${formatAction(status)}</td>
                            <td>${remark}</td>
                            <td id="confirm-${request.id}">${residentAgree}</td>
                            <td>
                                <button class="btn btn-sm btn-success mb-1" onclick="openChatModal(${request.id}, '${status}', ${isConfirmed})">Chat</button>
                            </td>
                        </tr>
                    `;
                });
                showCustomMessageBox("Room change requests loaded successfully.", 'success');
            })
            .catch(error => {
                console.error('Error fetching room change requests:', error);
                document.getElementById("room-change-list").innerHTML = `
                    <tr><td colspan="7" class="text-center text-danger">Failed to load requests. ${error.message}</td></tr>`;
                showCustomMessageBox("Failed to load room change requests. " + error.message, 'danger');
            });
    }

    function formatAction(action) {
        switch(action) {
            case 'available': return `<span class="badge bg-info">Approved by Admin</span>`;
            case 'not_available': return `<span class="badge bg-danger">Denied by Admin</span>`;
            case 'pending': return `<span class="badge bg-warning">Pending</span>`;
            case 'completed': return `<span class="badge bg-primary">Assigned & Completed</span>`;
            default: return `<span class="badge bg-secondary">Unknown</span>`;
        }
    }

    window.confirmRequest = function (requestId) {
        fetch(`{{ url('/api/resident/room-change/confirm-by-resident') }}/${requestId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify({ resident_agree: true })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                    throw new Error(`Server error or invalid response when confirming request: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // *** IMPORTANT CHANGE HERE: Directly update the cell instead of re-fetching the whole table ***
                document.getElementById(`confirm-${requestId}`).innerHTML = `<span class="badge bg-success">Confirmed</span>`;

                // Optionally, update the "Admin Action" column if confirmation changes it to 'completed' on the backend
                // This requires sending the new 'action' status back from the backend.
                // Assuming your backend might return the updated request object, you could do:
                // if (data.data && data.data.action === 'completed') {
                //     const rowElement = document.getElementById(`row-${requestId}`);
                //     if (rowElement) {
                //         // Assuming 'Admin Action' is the 4th column (index 3)
                //         rowElement.children[3].innerHTML = formatAction('completed');
                //     }
                // }

                showCustomMessageBox(data.message || "Room change confirmed by resident successfully.", 'success');

                // *** REMOVED: No need to call fetchRoomChangeRequests here anymore ***
                // fetchRoomChangeRequests(currentResidentId);
            } else {
                showCustomMessageBox(data.message || "Failed to confirm request.", 'danger');
            }
        })
        .catch(error => {
            console.error('Error confirming request:', error);
            showCustomMessageBox('An error occurred while confirming the request. ' + error.message, 'danger');
        });
    };

    window.openChatModal = function (requestId, status, isConfirmed) {
        fetchMessages(requestId);
        const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
        chatModal.show();

        document.getElementById("chatMessages").setAttribute("data-request-id", requestId);

        const messageInput = document.getElementById("messageInput");
        const sendButton = document.querySelector("#chatModal button.btn-primary");

        // Disable chat input if the request is denied or completed
        if (status === 'not_available' || status === 'completed') {
            messageInput.disabled = true;
            sendButton.disabled = true;
            if (status === 'not_available') {
                messageInput.placeholder = "Chat is locked. This request was denied by admin.";
            } else if (status === 'completed') {
                messageInput.placeholder = "Chat is locked. This request has been completed.";
            }
        } else {
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.placeholder = "Type a message...";
        }
    };

    function fetchMessages(requestId) {
        fetch(`{{ url('/api/resident/room-change/all-messages') }}/${requestId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'token': localStorage.getItem('token'), 
                'auth-id': localStorage.getItem('auth-id')
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                        throw new Error(`Server error or invalid response when fetching chat messages.`);
                    });
                }
                return response.json();
            })
            .then(data => {
                let chatMessages = document.getElementById('chatMessages');
                chatMessages.innerHTML = "";

                if (data.data && Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach(msg => {
                        const senderClass = msg.sender === 'resident' ? 'text-primary' : 'text-success';
                        const messageHtml = `
                            <div class="d-flex mb-2">
                                <div class="me-3">
                                    <strong class="${senderClass}">${msg.sender.charAt(0).toUpperCase() + msg.sender.slice(1)}</strong>:
                                    ${msg.message}
                                    <small class="text-muted d-block">${new Date(msg.created_at).toLocaleString()}</small>
                                </div>
                            </div>
                        `;
                        chatMessages.innerHTML += messageHtml;
                    });
                } else {
                    chatMessages.innerHTML = `<p>No messages found for this request.</p>`;
                }
                chatMessages.scrollTop = chatMessages.scrollHeight;
            })
            .catch(error => {
                console.error("Error loading messages:", error);
                document.getElementById('chatMessages').innerHTML = `<p class="text-danger">Failed to load messages. ${error.message}</p>`;
            });
    }

    window.sendMessage = function () {
        const requestId = document.getElementById("chatMessages").getAttribute("data-request-id");
        const message = document.getElementById("messageInput").value.trim();
        const sender = 'resident';

        if (!message) {
            showCustomMessageBox("Please enter a message.", 'warning', 'chatModal');
            return;
        }

        fetch(`{{ url('/api/resident/room-change/message') }}/${requestId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            body: JSON.stringify({
                message: message,
                sender: sender
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error(`HTTP error! Status: ${response.status}, Response: ${text}`);
                    throw new Error(`Server error or invalid response when sending message.`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.getElementById("messageInput").value = '';
                fetchMessages(requestId);
                showCustomMessageBox("Message sent!", 'success', 'chatModal');
            } else {
                showCustomMessageBox('Failed to send message: ' + (data.message || 'Unknown error.'), 'danger', 'chatModal');
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            showCustomMessageBox('An error occurred while sending the message: ' + error.message, 'danger', 'chatModal');
        });
    };
});
</script>

@endsection
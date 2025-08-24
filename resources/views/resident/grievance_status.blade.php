@extends('resident.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Your Grievances</h2>

            <div class="card">
                <div class="card-header">Submitted Grievances</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>S.No.</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="grievanceList">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="viewGrievanceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.5); z-index: 999; align-items: center; justify-content: center;">
                <div style="background: #fff; padding: 20px; border-radius: 10px; width: 400px; max-height: 600px;
                    display: flex; flex-direction: column; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                    <h5 class="text-center mb-3">Grievance Chat</h5>

                    <div id="chatContainer" style="flex-grow: 1; overflow-y: auto; padding: 10px;
                        border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; max-height: 350px;">
                        </div>

                    <form id="chatForm" style="display: flex;">
                        <input type="text" id="chatMessage" class="form-control" placeholder="Type your message..."
                               style="flex-grow: 1; margin-right: 5px;">
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>

                    <button type="button" class="btn btn-secondary mt-2" id="closeViewModalBtn">Close</button>
                </div>
            </div>

            <div id="customModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
                <div style="background: #fff; padding: 20px; border-radius: 10px; width: 300px; text-align: center;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                    <p id="modalMessage" style="margin-bottom: 20px;"></p>
                    <button id="modalConfirmBtn" class="btn btn-primary" style="display: none; margin-right: 10px;">Yes</button>
                    <button id="modalCloseBtn" class="btn btn-secondary">OK</button>
                </div>
            </div>

            <script>
                // Get current user details from Laravel Blade
                const currentUserRole = 'resident';
                let currentGrievanceId = null;
                let currentGrievanceStatus = '';
                let currentResidentId =null;

                // --- Custom Modal Functions ---
                const customModal = document.getElementById('customModal');
                const modalMessage = document.getElementById('modalMessage');
                const modalConfirmBtn = document.getElementById('modalConfirmBtn');
                const modalCloseBtn = document.getElementById('modalCloseBtn');

                let resolveModalPromise; // To handle confirmation callback

                /**
                 * Displays a custom alert message.
                 * @param {string} message The message to display.
                 */
                function showAlert(message) {
                    modalMessage.textContent = message;
                    modalConfirmBtn.style.display = 'none'; // Hide confirm button for alerts
                    modalCloseBtn.textContent = 'OK'; // Set close button text to OK
                    customModal.style.display = 'flex';
                    // Set up listener for closing the alert
                    modalCloseBtn.onclick = () => {
                        customModal.style.display = 'none';
                    };
                }

                /**
                 * Displays a custom confirmation dialog.
                 * @param {string} message The confirmation message.
                 * @returns {Promise<boolean>} A promise that resolves to true if confirmed, false otherwise.
                 */
                function showConfirm(message) {
                    return new Promise((resolve) => {
                        resolveModalPromise = resolve; // Store resolve function for later use
                        modalMessage.textContent = message;
                        modalConfirmBtn.style.display = 'inline-block'; // Show confirm button for confirmations
                        modalCloseBtn.textContent = 'Cancel'; // Set close button text to Cancel
                        customModal.style.display = 'flex';

                        // Set up listeners for confirmation
                        modalConfirmBtn.onclick = () => {
                            customModal.style.display = 'none';
                            resolveModalPromise(true);
                        };
                        modalCloseBtn.onclick = () => {
                            customModal.style.display = 'none';
                            resolveModalPromise(false);
                        };
                    });
                }
                // --- End Custom Modal Functions ---


                /**
                 * Fetches and displays the list of grievances for the current resident.
                 */
                function fetchGrievances() {
                    fetch(`/api/resident/grievances`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            const grievanceList = document.getElementById("grievanceList");
                            grievanceList.innerHTML = ""; // Clear existing list

                            // Check if data.data exists and has grievances
                            if (!data.data || data.data.length === 0) {
                                grievanceList.innerHTML = `<tr><td colspan="4" class="text-center">No grievances found.</td></tr>`;
                                return;
                            }

                            // Iterate over the grievances and append to the table
                            data.data.forEach((grievance, index) => {
                                const status = grievance.status.toLowerCase();
                                currentResidentId = grievance.resident_id; // Store current resident ID for chat responses
                                grievanceList.innerHTML += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${grievance.type_of_complaint}</td>
                                        <td>${grievance.status}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewGrievance(${grievance.id})">View</button>
                                            ${status !== 'closed' ? `<button class="btn btn-sm btn-danger" onclick="closeGrievance(${grievance.id})">Close</button>` : ''}
                                        </td>
                                    </tr>
                                `;
                            });
                        })
                        .catch(error => {
                            console.error("Error fetching grievances:", error);
                            document.getElementById("grievanceList").innerHTML =
                                `<tr><td colspan="4" class="text-danger text-center">Failed to load grievances.</td></tr>`;
                        });
                }

                /**
                 * Displays the chat modal for a specific grievance and fetches its details.
                 * @param {number} grievanceId The ID of the grievance to view.
                 */
                function viewGrievance(grievanceId) {
                    currentGrievanceId = grievanceId;

                    fetch(`/api/resident/grievances/${grievanceId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')  
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            const grievance = data.data; // Access the grievance object directly from data.data
                            if (!grievance) {
                                showAlert("Grievance details not found.");
                                return;
                            }
                            currentGrievanceStatus = grievance.status;

                            const chatContainer = document.getElementById("chatContainer");
                            chatContainer.innerHTML = ""; // Clear existing chat messages

                            // Display initial grievance description as the first message
                            const initialMessageDiv = document.createElement('div');
                            initialMessageDiv.style.textAlign = 'right';
                            initialMessageDiv.style.marginBottom = '10px';
                            initialMessageDiv.innerHTML = `
                                <div style="display: inline-block; background: #e9ecef; padding: 10px; border-radius: 10px; max-width: 75%; word-wrap: break-word;">
                                    <small><strong>Grievance Submitted</strong></small><br>
                                    ${grievance.description}
                                </div>
                            `;
                            chatContainer.appendChild(initialMessageDiv);


                            // Display responses
                            grievance.responses.forEach(response => {
                                // Determine if the response is from the current resident or an admin
                                // Assuming 'responded_by' for residents refers to 'resident_id' and for admins refers to 'user_id'
                                // This logic might need refinement based on how your API distinguishes between resident and admin responses.
                                const isFromCurrentResident = response.responded_by == localStorage.getItem('auth-id');
                                const sender = isFromCurrentResident ? 'You' : 'Admin'; // Simplified for demonstration
                                console.log(isFromCurrentResident);

                                const align = sender === 'You' ? 'right' : 'left';
                                const color = sender === 'You' ? '#d4edda' : '#f8d7da'; // Green for 'You', Red for 'Admin'
                                const maxWidth = '75%';

                                const messageDiv = document.createElement('div');
                                messageDiv.style.textAlign = align;
                                messageDiv.style.marginBottom = '10px';

                                messageDiv.innerHTML = `
                                    <div style="display: inline-block; background: ${color}; padding: 10px; border-radius: 10px; max-width: ${maxWidth}; word-wrap: break-word;">
                                        <small><strong>${sender}</strong></small><br>
                                        ${response.description}
                                    </div>
                                `;
                                chatContainer.appendChild(messageDiv);
                            });

                            // Disable chat input if grievance is closed
                            const chatForm = document.getElementById("chatForm");
                            if (grievance.status.toLowerCase() === "closed") {
                                chatForm.style.display = "none";
                            } else {
                                chatForm.style.display = "flex";
                            }

                            chatContainer.scrollTop = chatContainer.scrollHeight; // Scroll to bottom of chat
                            document.getElementById("viewGrievanceModal").style.display = "flex"; // Show the modal
                        })
                        .catch(error => {
                            console.error("Error fetching grievance details:", error);
                            showAlert("Failed to fetch grievance details.");
                        });
                }

                /**
                 * Closes a specific grievance after user confirmation.
                 * @param {number} grievanceId The ID of the grievance to close.
                 */
                async function closeGrievance(grievanceId) {
                    const confirmed = await showConfirm("Are you sure you want to close this grievance?");
                    if (!confirmed) return;

                    fetch(`/api/resident/grievances/close/${grievanceId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            fetchGrievances(); // Reload the list to reflect the status change
                        } else {
                            showAlert("Failed to close grievance.");
                        }
                    })
                    .catch(error => {
                        console.error("Error closing grievance:", error);
                        showAlert("Failed to close grievance.");
                    });
                }

                // Event listener for sending chat messages
                document.getElementById("chatForm").addEventListener("submit", function(event) {
                    event.preventDefault(); // Prevent default form submission
                    const message = document.getElementById("chatMessage").value.trim();

                    if (message === "") {
                        showAlert("Please type a message.");
                        return;
                    }

                    fetch(`/api/resident/grievances/respond/${currentGrievanceId}`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')
                        },
                        body: JSON.stringify({
                            description: message
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById("chatMessage").value = ""; // Clear message input
                        viewGrievance(currentGrievanceId); // Reload messages to show the new one
                    })
                    .catch(error => {
                        console.error("Error sending message:", error);
                        showAlert("Failed to send message.");
                    });
                });

                // Event listener for closing the view grievance modal
                document.getElementById("closeViewModalBtn").onclick = function () {
                    document.getElementById("viewGrievanceModal").style.display = "none";
                };

                // Fetch grievances when the DOM is fully loaded
                document.addEventListener("DOMContentLoaded", fetchGrievances);
            </script>
</div>
@endsection

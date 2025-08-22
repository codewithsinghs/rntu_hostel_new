@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2>Create Room</h2>

    <div id="responseMessage" class="alert" style="display: none;"></div>

    <form id="createRoomForm">
        @csrf
        <div class="mb-3">
            <label for="room_number" class="form-label">Room Number</label>
            <input type="text" class="form-control" id="room_number" name="room_number" required>
        </div>

        <div class="mb-3">
            <label for="building_id" class="form-label">Select Building</label>
            <select class="form-control" id="building_id" name="building_id" required>
                <option value="">-- Select Building --</option>
                </select>
        </div>

        <div class="mb-3">
            <label for="floorSelect" class="form-label">Select Floor</label>
            <select class="form-control" id="floorSelect" name="floor_no" required>
                <option value="">-- Select Floor --</option>
                </select>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-control" id="status" name="status" required>
                <option value="available">Available</option>
                <option value="occupied">Occupied</option>
                <option value="maintenance">Maintenance</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Create Room</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        // Fetch buildings and populate the dropdown
        fetchBuildings();

        // Event listener for form submission
        $("#createRoomForm").on("submit", function(event) {
            event.preventDefault();
            createRoom();
        });

        // Event listener for building selection change
        $("#building_id").on("change", function() {
            const selectedBuildingId = $(this).val();
            const selectedBuildingFloors = getBuildingFloorsById(selectedBuildingId);
            updateFloorDropdown(selectedBuildingFloors);
        });

        

    });
    // Function to fetch buildings and populate the dropdown
    function fetchBuildings() {
        $.ajax({
            url: "/api/admin/buildings",
            type: "GET",                    
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            success: function(response) {
                const buildingSelect = $("#building_id");
                buildingSelect.empty();
                buildingSelect.append('<option value="">-- Select Building --</option>');
                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(building) {
                        const option = $("<option></option>")

                            .val(building.id)
                            .text(building.name)
                            .data("floors", building.floors); // Store the number of floors in the option
                        buildingSelect.append(option);
                    }); 
                } else {
                    showMessage("danger", "Invalid response from server for buildings. Expected an array in 'data' field.");
                    console.error("Invalid building data structure:", response);
                }
            },
            error: function(xhr) {
                let errorMessage = "Failed to load buildings.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                }
                showMessage("danger", `Error: ${errorMessage}`);
                console.error("Error fetching buildings:", xhr);        
            }
        });
    }
    // Function to get the number of floors for the selected building
    function getBuildingFloorsById(buildingId) {
        const buildingSelect = $("#building_id");
        const selectedBuilding = buildingSelect.find(`option[value="${buildingId}"]`);
        return selectedBuilding.length ? parseInt(selectedBuilding.data("floors")) : 0;
    }
    // Function to update the floor dropdown based on the selected building's floors
    function updateFloorDropdown(floors) {
        const floorSelect = $("#floorSelect");
        floorSelect.empty();
        floorSelect.append('<option value="">-- Select Floor --</option>');
        if (floors > 0) {
            for (let i = 1; i <= floors; i++) {
                const option = $("<option></option>")
                    .val(i)
                    .text(`Floor ${i}`);
                floorSelect.append(option);
            }
        } else {                
            floorSelect.append('<option value="">No floors available</option>');
        }
    }
    // Function to create a room
    function createRoom() {
        const roomData = {
            room_number: $("#room_number").val(),
            building_id: $("#building_id").val(),       
            floor_no: $("#floorSelect").val(),
            status: $("#status").val()
        };
        $.ajax({
            url: "/api/admin/rooms/create", // Adjust the URL as per your API endpoint
            type: "POST",       
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id'),
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Get CSRF token
            },
            contentType: "application/json",
            data: JSON.stringify(roomData),
            success: function(response) {
                if (response.success) {
                    showMessage("success", response.message || "Room created successfully!");
                    $("#createRoomForm")[0].reset(); // Reset the form
                    $("#floorSelect").empty().append('<option value="">-- Select Floor --</option>'); // Reset floor dropdown

                } else {
                    showMessage("danger", response.message || "Error creating room. Please try again.");
                }
            },
            error: function(xhr) {
                let errorMessage = "An unexpected error occurred.";
                if (xhr.responseJSON && xhr.responseJSON.message) {     
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 422) { // Handle Laravel validation errors
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }   
                showMessage("danger", `Error: ${errorMessage}`);
                console.error("Error creating room:", xhr);
            }
        });
    }   
    // Function to show messages
    function showMessage(type, message) {
        const responseMessage = $("#responseMessage");  
        responseMessage.removeClass("alert-success alert-danger").addClass(`alert-${type}`);
        responseMessage.text(message).show();
        setTimeout(() => {
            responseMessage.hide();
        }, 4000); // Hide message after 4 seconds
    }
</script>
@endsection


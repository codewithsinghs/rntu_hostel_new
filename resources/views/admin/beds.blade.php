@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Manage Beds</h2>

    
    <div class="card mb-4">
        <div class="card-header">Create New Bed</div>
        <div class="card-body">
            <form id="createBedForm">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label for="buildingSelect" class="form-label">Select Building</label>
                        <select class="form-select" id="buildingSelect" name="building_id" required>
                            <option value="">-- Select Building --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="roomSelect" class="form-label">Select Room</label>
                        <select class="form-select" id="roomSelect" name="room_id" required>
                            <option value="">-- Select Room --</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="bedNumber" class="form-label">Bed Number</label>
                        <input type="text" class="form-control" id="bedNumber" name="bed_number" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Create Bed</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Beds List</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>S. No</th>
                        <th>Bed Number</th>
                        <th>Room Number</th>
                        <th>Building Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="bedList">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Bed</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editId">
            <div class="mb-3">
                <label for="editBedNumber" class="form-label">Bed Number</label>
                <input type="text" class="form-control" id="editBedNumber" required>
            </div>
            <div class="mb-3">
                <label for="editRoomId" class="form-label">Room ID</label>
                <input type="number" class="form-control" id="editRoomId" required>
            </div>
            <div class="mb-3">
                <label for="editStatus" class="form-label">Status</label>
                <select id="editStatus" class="form-select">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
    $(document).ready(function() {
        fetchBuildings();
        fetchBeds();

        $("#buildingSelect").on("change", function() {
            const buildingId = $(this).val();
            fetchRoomsByBuilding(buildingId);
        });

        $("#createBedForm").on("submit", function(event) {
            event.preventDefault();
            createBed();
        });

        $("#editForm").on("submit", function(event) {
            event.preventDefault();
            updateBed();
        });
    });

    function fetchBuildings() {
        $.ajax({
            url: '/api/admin/buildings',
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    const select = $('#buildingSelect');
                    select.empty().append('<option value="">-- Select Building --</option>');
                    response.data.forEach(function(building) {
                        select.append(`<option value="${building.id}">${building.name}</option>`);
                    });
                } else {
                    console.error("Failed to load buildings:", response);
                }
            },
            error: function(error) {
                console.error("Error fetching buildings:", error);
            }
        });
    }

    function fetchRoomsByBuilding(buildingId) {
        const roomSelect = $('#roomSelect');
        roomSelect.empty().append('<option value="">-- Select Room --</option>');
        if (!buildingId) return;
        $.ajax({
            url: `/api/admin/buildings/${buildingId}/rooms`,
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(room) {
                        roomSelect.append(`<option value="${room.id}">${room.room_number}</option>`);
                    });
                } else {
                    console.error("Failed to load rooms:", response);
                }
            },
            error: function(error) {
                console.error("Error fetching rooms:", error);
            }
        });
    }

    function fetchBeds() {
        $.ajax({
            url: '/api/admin/beds',
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            success: function(response) {
                const bedList = $('#bedList');
                bedList.empty();
                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(function(bed, index) {
                        bedList.append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${bed.bed_number}</td>
                                <td>${bed.room ? bed.room.room_number : 'N/A'}</td>
                                <td>${bed.room ? bed.room.building.name : 'N/A'}</td>
                                <td><span class="badge bg-${bed.status === 'available' ? 'success' : 'danger'}">${bed.status.charAt(0).toUpperCase() + bed.status.slice(1)}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="openEditModal(${bed.id}, '${bed.bed_number}', ${bed.room_id}, '${bed.status}')">Edit</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteBed(${bed.id})">Delete</button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    bedList.append('<tr><td colspan="6" class="text-center">No beds found.</td></tr>');
                }
            },
            error: function(error) {
                console.error("Error fetching beds:", error);
                $('#bedList').html('<tr><td colspan="6" class="text-danger text-center">Failed to load beds.</td></tr>');
            }
        });
    }

    function createBed() {
        const formData = {
            bed_number: $('#bedNumber').val(),
            room_id: $('#roomSelect').val(),
            status: 'available'
        };

        $.ajax({
            url: '/api/admin/beds/create',
            type: 'POST',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert("success", response.message || "Bed created successfully!");
                    fetchBeds();
                    $('#createBedForm')[0].reset();
                } else {
                    showAlert("danger", response.message || "Failed to create bed.");
                }
            },
            error: function(error) {
                console.error("Error creating bed:", error);
                showAlert("danger", "An error occurred while creating the bed.");
            }
        });
    }   

    function openEditModal(id, bedNumber, roomId, status) {
        $('#editId').val(id);
        $('#editBedNumber').val(bedNumber);
        $('#editRoomId').val(roomId);
        $('#editStatus').val(status);
        $('#editModal').modal('show');
    }

    function updateBed() {
        const id = $('#editId').val();
        const formData = {
            bed_number: $('#editBedNumber').val(),
            room_id: $('#editRoomId').val(),
            status: $('#editStatus').val()
        };

        $.ajax({
            url: `/api/admin/beds/update/${id}`,
            type: 'PUT',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert("success", response.message || "Bed updated successfully!");
                    fetchBeds();
                    $('#editModal').modal('hide');
                } else {
                    showAlert("danger", response.message || "Failed to update bed.");
                }
            },
            error: function(error) {
                console.error("Error updating bed:", error);
                showAlert("danger", "An error occurred while updating the bed.");
            }
        });
    }   


    function deleteBed(id) {
        if (confirm("Are you sure you want to delete this bed?")) {
            $.ajax({
                url: `/api/admin/beds/${id}`,
                type: 'DELETE',
                headers: {
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert("success", response.message || "Bed deleted successfully!");
                        fetchBeds();
                    } else {
                        showAlert("danger", response.message || "Failed to delete bed.");
                    }
                },
                error: function(error) {
                    console.error("Error deleting bed:", error);
                    showAlert("danger", "An error occurred while deleting the bed.");
                }
            });
        }
    }   

    function showAlert(type, message) {
        const alert = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        $('.container').prepend(alert);
    }   

    // Function to reset the floor dropdown 
    function resetFloorDropdown(floors) {
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

    // Function to update the floor dropdown based on selected building
    function updateFloorDropdown(buildingId) {
        const floors = getBuildingFloorsById(buildingId);
        resetFloorDropdown(floors);
    }
    // Function to get the number of floors for the selected building
    function getBuildingFloorsById(buildingId) {
        const buildingSelect = $("#buildingSelect");
        const selectedBuilding = buildingSelect.find(`option[value="${buildingId}"]`);
        return selectedBuilding.length ? parseInt(selectedBuilding.data("floors")) : 0;
    }



    </script>
@endsection

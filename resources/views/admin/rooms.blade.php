@extends('admin.layout')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-3">Rooms Management</h2>

            <div class="d-flex justify-content-between mb-3">
                <a href="{{ route('admin.create_rooms') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Room
                </a>
            </div>

            <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>

            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">Room List</h4>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="roomList">
                        <thead class="table-dark">
                            <tr>
                                <th>Serial No.</th>
                                <th>Room Number</th>
                                <th>Building Name</th>
                                <th>Floor No</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center">Loading rooms...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this room? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    $.ajax({
        url: '/api/admin/rooms', // your API endpoint
        type: 'GET',
        headers: {
            'token': localStorage.getItem('token'),
            'auth-id': localStorage.getItem('auth-id')
        },
        success: function (response) {
            if (response.success && Array.isArray(response.data)) {
                let rows = '';              
                response.data.forEach(function (room, index) {
                    rows += `
                        <tr data-id="${room.id}">
                            <td>${index + 1}</td>
                            <td class="room_number">${room.room_number}</td>
                            <td class="building_name">${room.building_name || 'Unknown'}</td>
                            <td class="building_name">${room.floor_no || 'Unknown'}</td>
                            <td class="status">
                                <span class="badge ${room.status === 'available' ? 'bg-success' : 'bg-danger'}">
                                    ${room.status.charAt(0).toUpperCase() + room.status.slice(1)}
                                </span>
                            </td>
                            <td class="actions">
                                <a class="btn btn-sm btn-warning me-1" href="/admin/rooms/edit/${room.id}">Edit</a>
                                <button class="btn btn-sm btn-danger" onclick="deleteRoom(${room.id})">Delete</button>
                            </td>
                        </tr>
                    `;  

                });
                $('#roomList tbody').html(rows);
            } else {
                $('#roomList tbody').html('<tr><td colspan="4">No data found</td></tr>');
            }
            },
            error: function (xhr) {
                console.error(xhr);
                $('#roomList tbody').html('<tr><td colspan="4">Error loading data</td></tr>');
            }
    });

    

});

function deleteRoom(id) {
    // Show the confirmation modal
    $('#deleteConfirmationModal').modal('show');

    // Set up the confirm delete button
    $('#confirmDeleteBtn').off('click').on('click', function() {
        $.ajax({
            url: `/api/admin/rooms/${id}`,
            type: 'DELETE',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteConfirmationModal').modal('hide');
                    showAlert('success', 'Room deleted successfully.');
                    fetchRooms(); // Refresh the room list
                } else {
                    showAlert('danger', response.message || 'Failed to delete room.');
                }
            },
            error: function(xhr) {
                let errorMessage = "Failed to delete room.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('danger', errorMessage);
            }
        });
    });
}   
</script>
{{-- Bootstrap CSS and JS are typically included in admin.layout or a common layout file.
     If not, ensure these are present for Bootstrap functionality. --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
@endsection

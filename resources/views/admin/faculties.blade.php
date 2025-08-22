@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2>Faculties</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="{{ route('admin.create_faculties') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Faculty
        </a>
    </div>

    {{-- Alert for errors --}}
    <div id="errorAlert" class="alert alert-danger d-none" role="alert"></div>
    {{-- Alert for success messages --}}
    <div id="successAlert" class="alert alert-success d-none" role="alert"></div>

    <table class="table table-bordered" id="facultiesList">
        <thead class="table-dark">
            <tr>
                <th>S.No.</th>
                <th>Name</th>
                <td>University Name</td>                
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this building? This action cannot be undone.
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
        url: '/api/admin/faculties', // your API endpoint
        type: 'GET',
        headers: {
            'token': localStorage.getItem('token'),
            'Auth-ID': localStorage.getItem('auth-id')
        },
        success: function (response) {
            if (response.success && Array.isArray(response.data)) {
                let rows = '';
                response.data.forEach(function (faculty, index) {
                    rows += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${faculty.name}</td>
                            <td>${faculty.university.name}</td>                            
                            <td>${faculty.status==1?"Active":"Inactive"}</td>
                            <td>                                
                            <a href="/admin/faculties/edit/${faculty.id}" class="btn btn-sm btn-primary edit-btn" data-id="${faculty.id}">Edit</a>
                            <a href="javascript:void(0);" class="btn btn-sm btn-danger delete-btn" data-id="${faculty.id}">Delete</a>
                            </td>
                        </tr>
                    `;
                });
                $('#facultiesList tbody').html(rows);
            } else {
                $('#facultiesList tbody').html('<tr><td colspan="4">No data found</td></tr>');
            }
            },
            error: function (xhr) {
                console.error(xhr);
                $('#facultiesList tbody').html('<tr><td colspan="4">Error loading data</td></tr>');
            }
    });
});


function showAlert(type, message) {
    let alertBox = type === 'success' ? $('#successAlert') : $('#errorAlert');
    alertBox.text(message).removeClass('d-none');
    setTimeout(() => {
        alertBox.addClass('d-none');
    }, 4000);
}
$(document).on('click', '.delete-btn', function () {
    let facultyId = $(this).data('id'); // Get the ID from button
    $('#deleteConfirmationModal').modal('show');
    $('#confirmDeleteBtn').off('click').on('click', function () {
        $.ajax({
            url: `/api/admin/faculties/${facultyId}`,              
            type: 'DELETE',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id'),
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')        
            },
            success: function (response) {
                if (response.success) {
                    showAlert("success", response.message || "Faculty deleted successfully!");
                    $('#deleteConfirmationModal').modal('hide');
                    // Refresh the building list
                    $.ajax({
                        url: '/api/admin/faculties',
                        type: 'GET',
                        headers: {
                            'token': localStorage.getItem('token'),         
                            'auth-id': localStorage.getItem('auth-id')
                        },
                        success: function (response) {
                            if (response.success && Array.isArray(response.data)) {
                                let rows = '';
                                response.data.forEach(function (faculty, index) {
                                    rows += `
                                        <tr>        
                                            <td>${index + 1}</td>
                                            <td>${faculty.name}</td>
                                            <td>${faculty.status}</td>
                                            <td>                                
                                            <a href="/admin/faculties/edit/${faculty.id}" class="btn btn-sm btn-primary edit-btn" data-id="${faculty.id}">Edit</a>
                                            <a href="javascript:void(0);" class="btn btn-sm btn-danger delete-btn" data-id="${faculty.id}">Delete</a>
                                            </td>
                                        </tr>
                                    `;
                                });
                                $('#facultiesList tbody').html(rows);
                            } else {
                                $('#facultiesList tbody').html('<tr><td colspan="6">No data found</td></tr>');
                            }
                        },
                        error: function (xhr) {
                            console.error(xhr);
                            $('#facultiesList tbody').html('<tr><td colspan="6">Error loading data</td></tr>');
                        }
                    });
                } else {
                    showAlert("danger", response.message || "Failed to delete building.");
                    $('#deleteConfirmationModal').modal('hide');
                }
            },
            error: function (xhr) {
                console.error(xhr);
                showAlert("danger", "An error occurred while deleting the building.");
                $('#deleteConfirmationModal').modal('hide');
            }
        });
    });
}); 

</Script>
@endsection
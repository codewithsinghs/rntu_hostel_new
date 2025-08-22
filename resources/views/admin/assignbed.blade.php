@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Assign Bed to Resident</h2>

    <div class="card p-4 shadow">
        <form id="assignBedForm">

            <div class="mb-3">
                <label for="resident_id" class="form-label">Select Resident</label>
                <select class="form-select" id="resident_id" name="resident_id" required>
                    <option value="">-- Select Resident --</option>
                </select>
            </div>

            <div class="row">
                <div class="mb-3 col-md-4">
                    <label for="building_id" class="form-label">Select Building</label>
                    <select class="form-select" id="building_id" name="building_id" required>
                        <option value="">-- Select Building --</option>
                    </select>
                </div>

                <div class="mb-3 col-md-4">
                    <label for="room_id" class="form-label">Select Room</label>
                    <select class="form-select" id="room_id" name="room_id" required>
                        <option value="">-- Select Room --</option>
                    </select>
                </div>

                <div class="mb-3 col-md-4">
                    <label for="bed_id" class="form-label">Select Bed</label>
                    <select class="form-select" id="bed_id" name="bed_id" required>
                        <option value="">-- Select Available Bed --</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Assign Bed</button>
        </form>

        <div id="responseMessage" class="mt-3"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {

        // Function to show a custom message box
        function showCustomMessageBox(message, type = 'info') {
            const messageContainer = $('#responseMessage');
            messageContainer.empty(); // Clear previous messages
            const alertDiv = `<div class="alert alert-${type}">${message}</div>`;
            messageContainer.html(alertDiv);
            setTimeout(() => messageContainer.empty(), 3000); // Remove after 3 seconds
        }

        $.ajax({
            url:'/api/admin/buildings',
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')      
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data)) {
                    const buildingSelect = $('#building_id');
                    buildingSelect.empty().append('<option value="">-- Select Building --</option>');
                    response.data.forEach(building => {
                        buildingSelect.append(`<option value="${building.id}">${building.name}</option>`);
                    });
                } else {
                    console.error("API response for buildings was not an array or data is missing:", response);
                    showCustomMessageBox("Error loading buildings.", 'danger');     
                }
            },
            error: function(xhr) {
                let errorMessage = "Failed to load buildings.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                }
                showCustomMessageBox(errorMessage, 'danger');           
                console.error("Error fetching buildings:", xhr);
            }
        });

        //select resident
        $.ajax({
            url: '/api/admin/residents/unassigned',
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            success: function(response) {
                let residentSelect = $('#resident_id');
                residentSelect.empty().append('<option value="">-- Select Resident --</option>');
                if (response.success && Array.isArray(response.data)) {
                    response.data.forEach(resident => {             
                        const name = resident.name ?? 'N/A';
                        const scholar = resident.scholar_number ?? 'N/A';   
                        const gender = resident.gender ?? 'N/A';
                        const label = `${name} | Scholar: ${scholar} | Gender: ${gender}`;
                        residentSelect.append(
                            `<option value="${resident.id}">${label}</option>`
                        );
                    });
                } else {
                    console.error("API response for unassigned residents was not an array or data is missing:", response);
                    showCustomMessageBox("Error loading residents.", 'danger');
                }
            },
            error: function(xhr) {
                let errorMessage = "Failed to load residents.";
                if (xhr.responseJSON && xhr.responseJSON.message) {     
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMessage = "Could not connect to the server. Please check your network connection.";
                }
                showCustomMessageBox(errorMessage, 'danger');
                console.error("Error fetching unassigned residents:", xhr);
            }

        });
                        
        // When building changes, fetch rooms
        $('#building_id').on('change', function() {
            let buildingId = $(this).val();
            $('#room_id').empty().append('<option value="">-- Select Room --</option>');
            $('#bed_id').empty().append('<option value="">-- Select Available Bed --</option>');        
            if (buildingId) {
                $.ajax({
                    url: `/api/admin/buildings/${buildingId}/rooms`,
                    type: 'GET',
                    headers: {
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
                    },
                    success: function(response) {
                        const roomSelect = $('#room_id');
                        roomSelect.empty().append('<option value="">-- Select Room --</option>');
                        if (response.success && Array.isArray(response.data)) {
                            response.data.forEach(room => {
                                roomSelect.append(`<option value="${room.id}">${room.room_number}</option>`);
                            });
                        } else {
                            console.error("API response for rooms was not an array or data is missing:", response);
                            showCustomMessageBox("Error loading rooms for the selected building.", 'danger');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "Failed to load rooms.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 0) {
                            errorMessage = "Could not connect to the server. Please check your network connection.";
                        }
                        showCustomMessageBox(errorMessage, 'danger');
                        console.error("Error fetching rooms by building:", xhr);
                    }
                });
            }
        });

        // When room changes, fetch available beds
        $('#room_id').on('change', function() {
            let roomId = $(this).val();
            $('#bed_id').empty().append('<option value="">-- Select Available Bed --</option>');
            if (roomId) {
                $.ajax({
                    url: `/api/admin/rooms/${roomId}/available-beds`,
                    type: 'GET',
                    headers: {
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')      
                    },
                    success: function(response) {
                        const bedSelect = $('#bed_id');
                        bedSelect.empty().append('<option value="">-- Select Available Bed --</option>');
                        if (response.success && Array.isArray(response.data)) {
                            if (response.data.length === 0) {
                                bedSelect.append('<option disabled>No available beds</option>');
                            } else {        
                                response.data.forEach(bed => {
                                    bedSelect.append(`<option value="${bed.id}">Bed Number ${bed.bed_number}</option>`);
                                });
                            }
                        } else {
                            console.error("API response for available beds was not an array or data is missing:", response);
                            showCustomMessageBox("Error loading available beds for the selected room.", 'danger');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "Failed to load available beds.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 0) {
                            errorMessage = "Could not connect to the server. Please check your network connection.";
                        }
                        showCustomMessageBox(errorMessage, 'danger');
                        console.error("Error fetching available beds:", xhr);
                    }   

                });
            }   
        });

        // Submit form
        $('#assignBedForm').submit(function(e) {
            e.preventDefault(); 
            const formData = {
                resident_id: $('#resident_id').val(),
                bed_id: $('#bed_id').val()
            };
            $.ajax({
                url: "{{ url('/api/admin/assign-bed') }}",
                type: 'POST',
                data: formData,
                headers: {
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                },
                success: function(response) {
                    showCustomMessageBox(response.message, 'success');
                    $('#assignBedForm')[0].reset();
                    $('#room_id').html('<option value="">-- Select Room --</option>');
                    $('#bed_id').html('<option value="">-- Select Available Bed --</option>');
                    // Re-fetch unassigned residents to update the list
                    $.ajax({
                        url: '/api/admin/residents/unassigned',
                        type: 'GET',
                        headers: {
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')
                        },
                        success: function(response) {
                            const residentSelect = $('#resident_id');
                            residentSelect.empty().append('<option value="">-- Select Resident --</option>');
                            if (response.success && Array.isArray(response.data)) {
                                response.data.forEach(resident => {
                                    const name = resident.name ?? 'N/A';
                                    const scholar = resident.scholar_number ?? 'N/A';
                                    const gender = resident.gender ?? 'N/A';
                                    const label = `${name} | Scholar: ${scholar} | Gender: ${gender}`;
                                    residentSelect.append(
                                        `<option value="${resident.id}">${label}</option>`
                                    );
                                });
                            } else {
                                console.error("API response for re-fetching unassigned residents was not an array or data is missing:", response);
                                showCustomMessageBox('Failed to refresh residents list.', 'danger');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "Failed to refresh residents.";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.status === 0) {
                                errorMessage = "Could not connect to the server. Please check your network connection.";
                            }
                            showCustomMessageBox(errorMessage, 'danger');
                            console.error("Error re-fetching unassigned residents:", xhr);
                        }

                    });
                },
            });
        });








    });
        // // Function to show a custom message box
        // function showCustomMessageBox(message, type = 'info') {
        //     const messageContainer = $('#responseMessage');
        //     messageContainer.empty(); // Clear previous messages
        //     const alertDiv = `<div class="alert alert-${type}">${message}</div>`;
        //     messageContainer.html(alertDiv);
        //     setTimeout(() => messageContainer.empty(), 3000); // Remove after 3 seconds
        // }

        // // Load unassigned residents
        // fetch('/api/admin/residents/unassigned')
        //     .then(response => response.json())
        //     .then(data => {
        //         const residentsSelect = $('#resident_id');
        //         residentsSelect.empty().append('<option value="">-- Select Resident --</option>');

        //         // Correctly access residents from data.data as per the API response structure
        //         const residents = data.data;

        //         if (Array.isArray(residents) && residents.length === 0) {
        //             residentsSelect.append('<option disabled>No residents available</option>');
        //         } else if (Array.isArray(residents)) {
        //             residents.forEach(resident => {
        //                 const name = resident.name ?? 'N/A';
        //                 const scholar = resident.scholar_number ?? 'N/A';
        //                 const gender = resident.gender ?? 'N/A';
        //                 const label = `${name} | Scholar: ${scholar} | Gender: ${gender}`;
        //                 residentsSelect.append(
        //                     `<option value="${resident.id}">${label}</option>`
        //                 );
        //             });
        //         } else {
        //             console.error("API response for unassigned residents was not an array or data is missing:", data);
        //             showCustomMessageBox("Error loading residents.", 'danger');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error fetching unassigned residents:', error);
        //         showCustomMessageBox('Failed to load residents.', 'danger');
        //     });

        // // Load buildings
        // fetch('/api/buildings')
        //     .then(response => response.json())
        //     .then(data => {
        //         const buildingSelect = $('#building_id');
        //         buildingSelect.empty().append('<option value="">-- Select Building --</option>');

        //         const buildings = data.data;

        //         if (Array.isArray(buildings)) {
        //             buildings.forEach(building => {
        //                 buildingSelect.append(`<option value="${building.id}">${building.name}</option>`);
        //             });
        //         } else {
        //             console.error("API response for buildings was not an array or data is missing:", data);
        //             showCustomMessageBox("Error loading buildings.", 'danger');
        //         }
        //     })
        //     .catch(error => {
        //         console.error('Error fetching buildings:', error);
        //         showCustomMessageBox('Failed to load buildings.', 'danger');
        //     });

        // // When building changes, fetch rooms
        // $('#building_id').on('change', function() {
        //     let buildingId = $(this).val();
        //     $('#room_id').empty().append('<option value="">-- Select Room --</option>');
        //     $('#bed_id').empty().append('<option value="">-- Select Available Bed --</option>');

        //     if (buildingId) {
        //         fetch(`/api/buildings/${buildingId}/rooms`)
        //             .then(res => res.json())
        //             .then(data => {
        //                 const roomSelect = $('#room_id');
        //                 roomSelect.empty().append('<option value="">-- Select Room --</option>');

        //                 const rooms = data.data;

        //                 if (Array.isArray(rooms)) {
        //                     rooms.forEach(room => {
        //                         roomSelect.append(`<option value="${room.id}">${room.room_number}</option>`);
        //                     });
        //                 } else {
        //                     console.error("API response for rooms was not an array or data is missing:", data);
        //                     showCustomMessageBox("Error loading rooms for the selected building.", 'danger');
        //                 }
        //             })
        //             .catch(error => {
        //                 console.error('Error fetching rooms by building:', error);
        //                 showCustomMessageBox('Failed to load rooms.', 'danger');
        //             });
        //     }
        // });

        // // When room changes, fetch available beds
        // $('#room_id').on('change', function() {
        //     let roomId = $(this).val();
        //     $('#bed_id').empty().append('<option value="">-- Select Available Bed --</option>');

        //     if (roomId) {
        //         fetch(`/api/rooms/${roomId}/available-beds`)
        //             .then(res => res.json())
        //             .then(data => {
        //                 const bedSelect = $('#bed_id');
        //                 bedSelect.empty().append('<option value="">-- Select Available Bed --</option>');

        //                 const beds = data.data;

        //                 if (Array.isArray(beds) && beds.length === 0) {
        //                     bedSelect.append('<option disabled>No available beds</option>');
        //                 } else if (Array.isArray(beds)) {
        //                     beds.forEach(bed => {
        //                         bedSelect.append(`<option value="${bed.id}">Bed Number ${bed.bed_number}</option>`);
        //                     });
        //                 } else {
        //                     console.error("API response for available beds was not an array or data is missing:", data);
        //                     showCustomMessageBox("Error loading available beds for the selected room.", 'danger');
        //                 }
        //             })
        //             .catch(error => {
        //                 console.error('Error fetching available beds:', error);
        //                 showCustomMessageBox('Failed to load available beds.', 'danger');
        //             });
        //     }
        // });

        // Submit form
    //     $('#assignBedForm').submit(function(e) {
    //         e.preventDefault();

    //         const formData = {
    //             resident_id: $('#resident_id').val(),
    //             bed_id: $('#bed_id').val()
    //         };

    //         $.ajax({
    //             url: "{{ url('/api/admin/assign-bed') }}",
    //             type: 'POST',
    //             data: formData,
    //             success: function(response) {
    //                 showCustomMessageBox(response.message, 'success');
    //                 $('#assignBedForm')[0].reset();
    //                 $('#room_id').html('<option value="">-- Select Room --</option>');
    //                 $('#bed_id').html('<option value="">-- Select Available Bed --</option>');
    //                 // Re-fetch unassigned residents to update the list
    //                 fetch('/api/admin/residents/unassigned')
    //                     .then(response => response.json())
    //                     .then(data => {
    //                         const residentsSelect = $('#resident_id');
    //                         residentsSelect.empty().append('<option value="">-- Select Resident --</option>');
    //                         const residents = data.data; // Corrected access
    //                         if (Array.isArray(residents) && residents.length === 0) {
    //                             residentsSelect.append('<option disabled>No residents available</option>');
    //                         } else if (Array.isArray(residents)) {
    //                             residents.forEach(resident => {
    //                                 const name = resident.name ?? 'N/A';
    //                                 const scholar = resident.scholar_number ?? 'N/A';
    //                                 const gender = resident.gender ?? 'N/A';
    //                                 const label = `${name} | Scholar: ${scholar} | Gender: ${gender}`;
    //                                 residentsSelect.append(
    //                                     `<option value="${resident.id}">${label}</option>`
    //                                 );
    //                             });
    //                         } else {
    //                             console.error("API response for re-fetching unassigned residents was not an array or data is missing:", data);
    //                             showCustomMessageBox('Failed to refresh residents list.', 'danger');
    //                         }
    //                     })
    //                     .catch(error => {
    //                         console.error('Error re-fetching unassigned residents:', error);
    //                         showCustomMessageBox('Failed to refresh residents list.', 'danger');
    //                     });
    //             },
    //             error: function(xhr) {
    //                 let errorMsg = xhr.responseJSON?.message || 'Something went wrong.';
    //                 showCustomMessageBox(errorMsg, 'danger');
    //             }
    //         });
    //     });

    // });

    </script>
@endsection
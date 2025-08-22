@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h3>List of Residents</h3>
    <hr>

    <div id="responseMessage" class="mt-3"></div> {{-- Added message container here --}}

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>S. No</th>
                <th>Scholar No</th>
                <th>Resident Name</th>
                <th>Email</th>
                <th>Gender</th>
                <th>Bed Number</th>
                <th>Room Number</th>
                <th>Building Name</th>
                <th>Room Preference</th>
                <th>Food Preference</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody id="residentList">
            </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        // Fetch residents when the document is ready
        fetchResidents();

        // Function to show a custom message box
        function showCustomMessageBox(message, type = 'info') {
            const messageContainer = $('#responseMessage');
            messageContainer.html(`<div class="alert alert-${type}">${message}</div>`);
            setTimeout(() => messageContainer.empty(), 3000); // Clear after 3 seconds
        }
    });

    // Function to fetch residents
    function fetchResidents() {
        $.ajax({
            url: "{{ url('/api/admin/residents') }}",
            type: 'GET',
            headers: {
                'token': localStorage.getItem('token'),
                'Auth-ID': localStorage.getItem('auth-id')
            },
            success: function(response) {
                const residents = response.data;
                const residentList = $("#residentList");
                residentList.empty();

                if (!Array.isArray(residents) || residents.length === 0) {
                    residentList.append(`<tr><td colspan="10" class="text-center">No residents found.</td></tr>`);
                    return;
                }

                residents.forEach((resident, index) => {
                    const guest = resident.guest || {};
                    const bed = resident.bed || {};

                    residentList.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${resident.scholar_no || 'N/A'}</td>
                            <td>${resident.name || 'N/A'}</td>
                            <td>${resident.email || 'N/A'}</td>
                            <td>${resident.gender || 'N/A'}</td> {{-- Changed from guest.gender to resident.gender based on API --}}
                            <td>${bed.bed_number || 'Not Assigned'}</td>
                            <td>${bed.room.room_number || 'N/A'}</td>
                            <td>${bed.room.building.name || 'N/A'}</td>
                            <td>${guest.room_preference || 'N/A'}</td>
                            <td>${guest.food_preference || 'N/A'}</td>
                            <td>${resident.status || 'N/A'}</td>
                            <td>${new Date(resident.created_at).toLocaleString()}</td>
                        </tr>
                    `);
                });
            },
            error: function(xhr) {
                console.error("Error fetching residents:", xhr);
                $("#residentList").html(`<tr><td colspan="10" class="text-danger text-center">Error loading residents.</td></tr>`);
                showCustomMessageBox("Failed to load residents.", 'danger'); // Display error message
            }
        });
    }
</script>
@endsection


@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <div class="d-flex">
        <!-- Admin Panel (Left Sidebar) -->
        <div class="col-md-3">
            <div class="list-group">
                <!-- Removed Buildings and Rooms buttons -->
                <!-- <button class="list-group-item list-group-item-action" id="showBuildings">Buildings</button> -->
                <!-- <button class="list-group-item list-group-item-action" id="showRooms">Rooms</button> -->
                <!-- Add more buttons for other sections here -->
            </div>
        </div>

        <!-- Right Section for Content Display -->
        <div class="col-md-9">
            <div id="adminContent">
                <!-- Initially, show this message -->
                <h3>Select an option from the admin panel</h3>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Initially set content to 'Select an option' message
    document.getElementById("adminContent").innerHTML = `
        <h3>Select an option from the admin panel</h3>
    `;

    // Optionally, add more sections and functionality here
});
</script>
@endsection

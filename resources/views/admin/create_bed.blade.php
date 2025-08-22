@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Add New Bed</h2>

    <!-- Dynamic Success/Error Message Box -->
    <div id="formMessage"></div>

    <!-- Bed Creation Form -->
    <div class="card mb-4">
        <div class="card-header">Create Bed</div>
        <div class="card-body">
            <form id="createBedForm">
                @csrf
                <div class="mb-3">
                    <label for="bed_number" class="form-label">Bed Number</label>
                    <input type="text" class="form-control" id="bed_number" name="bed_number" required>
                </div>

                <div class="mb-3">
                    <label for="room_id" class="form-label">Room ID</label>
                    <input type="text" class="form-control" id="room_id" name="room_id" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status" required>
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Add Bed</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("createBedForm");
    const formMessage = document.getElementById("formMessage");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const bedNumber = document.getElementById("bed_number").value;
        const roomId = document.getElementById("room_id").value;
        const status = document.getElementById("status").value;

        fetch("{{ url('/api/beds') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                bed_number: bedNumber,
                room_id: roomId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.bed) {
                formMessage.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ✅ Bed created successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;

                // Optional: Clear the form
                form.reset();
            } else {
                formMessage.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ❌ Error creating bed. Please check the input.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error("Error:", error);
            formMessage.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    ❌ An unexpected error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        });
    });
});
</script>
@endsection

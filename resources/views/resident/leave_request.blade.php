@extends('resident.layout')

@section('content')
<div class="container mt-5">
    <h3 class="h3 text-center p-5 text-success">Leave Request Form</h3>

    <div id="message" class="alert d-none"></div>
            <form id="leaveRequestForm" enctype="multipart/form-data">
                @csrf <!-- CSRF Token -->

                <div class="mb-3">
                    <label for="from_date" class="form-label">From Date:</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="to_date" class="form-label">To Date:</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason:</label>
                    <textarea id="reason" name="reason" class="form-control" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="photo" class="form-label">Upload Supporting Photo/Document (Optional):</label>
                    <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Submit Request</button>
            </form>
</div>

<!-- CSRF Meta -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.getElementById("leaveRequestForm")?.addEventListener("submit", function(event) {
    event.preventDefault();

    const residentId = localStorage.getItem('auth-id');
    const form = this;

    const formData = new FormData(form); // Automatically includes the file input

    fetch(`/api/resident/leave`, {
        method: "POST",
        headers: {
            "Accept": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            'token': localStorage.getItem('token'),
            'auth-id': localStorage.getItem('auth-id')
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        const messageDiv = document.getElementById("message");

        if (response.ok) {
            messageDiv.className = "alert alert-success";
            messageDiv.textContent = data.message || "Leave request submitted successfully!";
            messageDiv.classList.remove("d-none");
            form.reset();
        } else {
            messageDiv.className = "alert alert-danger";
            messageDiv.textContent = data.error || "Error submitting request.";
            messageDiv.classList.remove("d-none");

            if (data.messages) {
                for (const field in data.messages) {
                    console.error(`${field}: ${data.messages[field].join(', ')}`);
                }
            }
        }
    })
    .catch(error => {
        console.error("Unexpected error:", error);
        const messageDiv = document.getElementById("message");
        messageDiv.className = "alert alert-danger";
        messageDiv.textContent = "An unexpected error occurred.";
        messageDiv.classList.remove("d-none");
    });
});
</script>
@endsection

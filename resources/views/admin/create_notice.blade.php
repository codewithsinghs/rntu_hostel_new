@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2>Create Notice</h2>

    <div class="card">
        <div class="card-body">
            <form id="createNoticeForm">
                @csrf  <!-- Not required for API but kept for consistency -->

                <div class="mb-3">
                    <label for="message_from" class="form-label">Message From</label>
                    <input type="text" class="form-control" id="message_from" name="message_from" required>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" required>
                </div>

                <div class="mb-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Notice</button>
                <a href="{{ url('/admin/notices') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById("createNoticeForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent default form submission

    let formData = {
        message_from: document.getElementById("message_from").value,
        message: document.getElementById("message").value,
        from_date: document.getElementById("from_date").value,
        to_date: document.getElementById("to_date").value
    };

    fetch("{{ url('/api/notices') }}", {  // Directly calling the API
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            window.location.href = "{{ url('/admin/notices') }}"; // Redirect to Notices List
        } else {
            alert("Failed to create notice.");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while creating the notice.");
    });
});
</script>
@endsection

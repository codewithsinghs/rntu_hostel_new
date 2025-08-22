@extends('resident.layout')

@section('content')
<div class="container mt-5">
    <h3>Submit Feedback</h3>
    <hr>

    <form id="feedbackForm">
        @csrf

        <!-- Hidden Resident ID field -->
        <input type="hidden" id="resident_id" name="resident_id">

        <div class="mb-3">
            <label for="facility_name" class="form-label">Facility Name</label>
            <input type="text" class="form-control" id="facility_name" name="facility_name" required>
        </div>
        <div class="mb-3">
            <label for="feedback" class="form-label">Feedback</label>
            <textarea class="form-control" id="feedback" name="feedback" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="suggestion" class="form-label">Suggestion (Optional)</label>
            <textarea class="form-control" id="suggestion" name="suggestion" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>

    <div id="message" class="mt-3"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Automatically set resident_id from the logged-in user
    const residentId = "{{ auth()->user()->resident->id ?? '' }}";
    if (residentId) {
        document.getElementById("resident_id").value = residentId;
    }

    document.getElementById("feedbackForm").addEventListener("submit", function(event) {
        event.preventDefault();


        let formData = new FormData();
        formData.append('facility_name', document.getElementById("facility_name").value);
        formData.append('feedback', document.getElementById("feedback").value);
        formData.append('suggestion', document.getElementById("suggestion").value);

        fetch("{{ url('/api/resident/feedbacks') }}", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "token": localStorage.getItem('token'),
                "auth-id": localStorage.getItem('auth-id')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                document.getElementById("message").innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                document.getElementById("feedbackForm").reset();
            }
        })
        .catch(error => {
            console.error("Error submitting feedback:", error);
            document.getElementById("message").innerHTML = `<div class="alert alert-danger">Error submitting feedback.</div>`;
        });
    });
});
</script>
@endsection

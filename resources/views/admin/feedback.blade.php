@extends('admin.layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
    <h2 class="mb-4">Resident Feedback</h2>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th>S. No.</th>
                <th>Resident Name</th>
                <th>User Email</th>
                <th>Facility Name</th>
                <th>Feedback</th>
                <th>Suggestion</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody id="feedback-list">
            <tr><td colspan="7" class="text-center">Loading feedbacks...</td></tr>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        fetch('/api/feedbacks') // API endpoint from your controller
            .then(response => response.json())
            .then(data => {
                let feedbackList = document.getElementById('feedback-list');
                feedbackList.innerHTML = ""; // Clear the loading message

                // Access the feedback array from the 'data' property of the API response
                const feedbacks = data.data; 

                let count = 1;

                if (!feedbacks || feedbacks.length === 0) {
                    feedbackList.innerHTML = `<tr><td colspan="7" class="text-center">No feedbacks available.</td></tr>`;
                    return;
                }

                feedbacks.forEach(feedback => {
                    let residentName = feedback.resident ? feedback.resident.user.name : 'N/A';
                    let userEmail = feedback.resident && feedback.resident.user ? feedback.resident.user.email : 'N/A';

                    feedbackList.innerHTML += `
                        <tr>
                            <td>${count++}</td>
                            <td>${residentName}</td>
                            <td>${userEmail}</td>
                            <td>${feedback.facility_name}</td>
                            <td>${feedback.feedback}</td>
                            <td>${feedback.suggestion ? feedback.suggestion : 'N/A'}</td>
                            <td>${new Date(feedback.created_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                console.error('Error fetching feedback:', error);
                let feedbackList = document.getElementById('feedback-list');
                feedbackList.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error loading feedbacks. Please try again.</td></tr>`;
            });
    });
</script>
@endsection

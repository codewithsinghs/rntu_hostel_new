@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Notices</h2>

    <!-- Create Notice Button -->
    <a href="{{ route('admin.create_notice') }}" class="btn btn-primary mb-3">Create Notice</a>

    <!-- Notices List -->
    <div class="card">
        <div class="card-header">Notices List</div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>S.No.</th>
                        <th>Message From</th>
                        <th>Message</th>
                        <th>From Date</th>
                        <th>To Date</th>
                    </tr>
                </thead>
                <tbody id="noticesTable">
                    <tr>
                        <td colspan="5" class="text-center">Loading notices...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchNotices(); // Fetch notices when page loads

    function fetchNotices() {
        fetch("{{ url('/api/notices') }}")
            .then(response => response.json())
            .then(response => {
                console.log("Raw response:", response); // Debug line (optional)
                const notices = response.data || []; // Safely extract notices array

                let tableBody = document.getElementById("noticesTable");
                tableBody.innerHTML = ""; // Clear previous content

                if (!Array.isArray(notices) || notices.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center">No notices found.</td></tr>`;
                    return;
                }

                // Populate the table with notices
                notices.forEach((notice, index) => {
                    tableBody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${notice.message_from}</td>
                            <td>${notice.message}</td>
                            <td>${notice.from_date}</td>
                            <td>${notice.to_date}</td>
                        </tr>
                    `;
                });
            })
            .catch(error => {
                console.error("Error fetching notices:", error);
                document.getElementById("noticesTable").innerHTML = `
                    <tr><td colspan="5" class="text-center text-danger">Failed to load notices.</td></tr>`;
            });
    }
});
</script>
@endsection

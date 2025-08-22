@extends('resident.layout')

@section('content')
<div class="container mt-5">
    <h3>Notices</h3>
    <hr>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>S.No</th>
                <th>Message From</th>
                <th>Message</th>
                <th>From Date</th>
                <th>To Date</th>
            </tr>
        </thead>
        <tbody id="notice-list">
            <tr>
                <td colspan="5" class="text-center">Loading notices...</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetch(`/api/resident/notices`, {    

        method: "GET",
        headers: {
            "Accept": "application/json",
            'token': localStorage.getItem('token'),
            'auth-id': localStorage.getItem('auth-id') // Include auth-id for authorization
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            let tableBody = document.getElementById("notice-list");
            tableBody.innerHTML = ""; // Clear initial loading message.

            // Check if the response has the expected structure
            if (typeof data !== 'object' || data === null || !Array.isArray(data.data)) {
                console.error("Unexpected data format:", data);
                tableBody.innerHTML = "<tr><td colspan='5' class='text-center text-danger'>Failed to load notices: Unexpected data format from server. Expected an object with a 'data' array.</td></tr>";
                return;
            }
            
            const notices = data.data;
            const currentDate = new Date(); // Get current date

            // Filter out expired notices
            const unexpiredNotices = notices.filter(notice => {
                const toDate = new Date(notice.to_date);
                // Set both dates to the start of the day to compare only dates, ignoring time
                currentDate.setHours(0, 0, 0, 0);
                toDate.setHours(0, 0, 0, 0);
                return toDate >= currentDate;
            });

            if (unexpiredNotices.length === 0) {
                tableBody.innerHTML = "<tr><td colspan='5' class='text-center'>No active notices available</td></tr>";
            } else {
                unexpiredNotices.forEach((notice, index) => {
                    let row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${notice.message_from}</td>
                            <td>${notice.message}</td>
                            <td>${notice.from_date}</td>
                            <td>${notice.to_date}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            }
        })
        .catch(error => {
            console.error("Error fetching notices:", error);
            let tableBody = document.getElementById("notice-list");
            tableBody.innerHTML = "<tr><td colspan='5' class='text-center text-danger'>Failed to load notices: " + error.message + "</td></tr>";
        });
});
</script>
@endsection
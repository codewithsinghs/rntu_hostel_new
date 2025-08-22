@extends('admin.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Guests Who Have Paid</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="paidGuestsTable">
            <thead class="table-dark">
                <tr>
                    <th>Guest ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Created Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- Rows will be inserted via JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('/api/guests/paid')
            .then(response => response.json())
            .then(data => {
                console.log('API Response:', data); // Debug API response structure

                const tableBody = document.querySelector('#paidGuestsTable tbody');
                tableBody.innerHTML = '';

                const guests = Array.isArray(data) ? data : (data.data || data.guests || []);

                if (guests.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4" class="text-center">No guests have paid yet.</td></tr>';
                    return;
                }

                guests.forEach(guest => {
                    const status = 'Paid';  // Status can be adjusted if needed from API data
                    const name = guest.name || 'N/A';
                    const id = guest.id || 'N/A';
                    const createdDate = guest.created_at ? new Date(guest.created_at).toLocaleString() : 'N/A'; // Format created_at date

                    const row = `
                        <tr>
                            <td>${id}</td>
                            <td>${name}</td>
                            <td>${status}</td>
                            <td>${createdDate}</td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(error => {
                console.error('Error fetching paid guests:', error);
                const tableBody = document.querySelector('#paidGuestsTable tbody');
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading data.</td></tr>';
            });
    });
</script>
@endsection

@extends('Mess.layout')

@section('content')
<div class="container">
    <h1 class="mb-4">Mess Records</h1>

    <div id="mess-records" class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Serial No.</th>
                    <th>Name</th>
                    <th>Food Preference</th>
                    <th>From Date</th>
                    <th>To Date</th>
                </tr>
            </thead>
            <tbody id="mess-table-body">
                {{-- Records will be populated by JS --}}
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        fetch('{{ url("/api/messes") }}')
            .then(res => res.json())
            .then(data => {
                console.log('API Response:', data); // 👈 Log for debugging
                const tbody = document.getElementById('mess-table-body');

                if (data.success) {
                    tbody.innerHTML = '';
                    data.data.forEach((mess, index) => {
                        const name = mess.guest?.name || mess.user?.name || 'N/A';
                        const row = `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${name}</td>
                                <td>${mess.food_preference || 'N/A'}</td>
                                <td>${mess.from_date || 'N/A'}</td>
                                <td>${mess.to_date || 'N/A'}</td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                } else {
                    alert(data.message || 'Failed to fetch mess records!');
                }
            })
            .catch(error => {
                console.error('Error fetching mess data:', error);
                alert('Something went wrong!');
            });
    });
</script>
@endsection

@extends('admin.layout')

@section('content')
<div class="container mt-5">
    <h2 class="text-xl font-semibold mb-4">Assign Accessory to Resident</h2>

    <div id="success-message" class="alert alert-success d-none"></div>
    <div id="error-message" class="alert alert-danger d-none"></div>

    <form id="sendAccessoryForm">
        @csrf

        <div class="mb-3">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-control" required>
                <option value="">Select Resident</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="accessory_id" class="form-label">Accessory</label>
            <select name="accessory_id" id="accessory_id" class="form-control" required>
                <option value="">Select Accessory</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="duration" class="form-label">Duration</label>
            <select name="duration" class="form-control" required>
                <option value="">Select Duration</option>
                <option value="1 Month">1 Month</option>
                <option value="3 Months">3 Months</option>
                <option value="6 Months">6 Months</option>
                <option value="1 Year">1 Year</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async function () {
        const residentDropdown = document.getElementById('resident_id');
        const accessoryDropdown = document.getElementById('accessory_id');
        // This line assumes 'auth()->user()->id' is accessible in the Blade template.
        // If this is a pure HTML file, you'll need to pass this value from your backend.
        const loggedInUserId = localStorage.getItem('auth-id'); 

        try {
            // Fetch residents
            const residentResponse = await fetch("{{ url('/api/admin/residents') }}", {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            });
            if (!residentResponse.ok) {
                throw new Error(`HTTP error! status: ${residentResponse.status}`);
            }
            const residentData = await residentResponse.json();
            // Corrected: Access 'data' property as per API response structure
            if (!Array.isArray(residentData.data)) {
                throw new Error('Invalid residents data format: Expected an array in "data" property.');
            }
            const residents = residentData.data; // Changed from residentData.residents to residentData.data

            // Populate resident dropdown
            residents.forEach(resident => {
                const option = document.createElement('option');
                option.value = resident.id;
                option.textContent = `${resident.name} (${resident.scholar_no})`;
                residentDropdown.appendChild(option);
            });

            // Fetch accessories
            const accessoryResponse = await fetch("{{ url('/api/admin/accessories/active') }}", {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'token': localStorage.getItem('token'),
                    'auth-id': localStorage.getItem('auth-id')
                }
            });
            if (!accessoryResponse.ok) {
                throw new Error(`HTTP error! status: ${accessoryResponse.status}`);
            }
            const accessoryData = await accessoryResponse.json();
            // Added check for accessoryData.data
            if (!Array.isArray(accessoryData.data)) {
                throw new Error('Invalid accessories data format: Expected an array in "data" property.');
            }
            const accessories = accessoryData.data;

            // Populate accessory dropdown
            accessories.forEach(accessory => {
                const option = document.createElement('option');
                option.value = accessory.id;
                option.textContent = `${accessory.accessory_head.name}`;
                option.setAttribute('data-accessory-head-id', accessory.accessory_head_id); // Store accessory_head_id
                accessoryDropdown.appendChild(option);
            });

        } catch (error) {
            console.error("Could not fetch data:", error);
            document.getElementById('error-message').classList.remove('d-none');
            document.getElementById('error-message').innerText = 'Failed to load data.';
        }

        document.getElementById('sendAccessoryForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.created_by = loggedInUserId; // Add the created_by field

            const accessoryId = document.getElementById('accessory_id').value;
            const selectedOption = document.querySelector(`#accessory_id option[value="${accessoryId}"]`);
            const accessoryHeadId = selectedOption.getAttribute('data-accessory-head-id');  // Get accessory_head_id
            data.accessory_head_id = accessoryHeadId; // Add accessory_head_id to the data

            try {
                const response = await fetch("{{ url('/api/admin/assign-accessories') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                const successMessage = document.getElementById('success-message');
                const errorMessage = document.getElementById('error-message');

                if (response.ok) {
                    successMessage.classList.remove('d-none');
                    errorMessage.classList.add('d-none');
                    successMessage.innerText = result.message;
                    form.reset();
                } else {
                    successMessage.classList.add('d-none');
                    errorMessage.classList.remove('d-none');
                    errorMessage.innerText = result.error || 'Failed to assign accessory.';
                }
            } catch (err) {
                console.error("Error sending data", err);
                document.getElementById('error-message').classList.remove('d-none');
                document.getElementById('error-message').innerText = 'Request failed. Please try again.';
            }
        });
    });
</script>
@endsection

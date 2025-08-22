@extends('accountant.layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Add or Update Fee
        </div>
        <div class="card-body">
            <form id="feeForm" class="row g-3">
                <div class="col-md-6">
                    <label for="fee_head_id" class="form-label">Fee Head</label>
                    <div class="input-group">
                        <select name="fee_head_id" id="fee_head_id" class="form-select form-select-lg" required>
                            <option value="">Select Fee Head</option>
                        </select>
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="addFeeHeadBtn">
                            +
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" name="amount" id="amount" class="form-control form-control-lg" placeholder="Enter Fee Amount" required>
                    <input type="hidden" name="created_by" value="{{ auth()->user()->id }}">
                </div>
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-success btn-lg">Submit</button>
                </div>
            </form>
            <div id="feeFormMessage" class="mt-3"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-success text-white">
            All Active Fees
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>S.No</th>
                            <th>Fee Head Name</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="activeFeesTableBody">
                        <tr><td colspan="3">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addFeeHeadModal" tabindex="-1" aria-labelledby="addFeeHeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFeeHeadModalLabel">Add Fee Head</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="feeHeadFormModal" class="g-3">
                    <div class="col-12">
                        <label for="name" class="form-label">Fee Head Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-lg" placeholder="Enter Fee Head Name" required>
                        <input type="hidden" name="created_by" id="created_by" value="{{ auth()->user()->id }}">
                    </div>
                </form>
                <div id="feeHeadMessageModal" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" id="createFeeHeadBtnModal">Create</button>
            </div>
        </div>
    </div>
</div>

<script>
    let feeHeadMap = {}; // Global map to store fee head IDs and names

    document.addEventListener('DOMContentLoaded', function() {
        // Load fee heads first, then load active fees
        loadFeeHeads();

        const addFeeHeadBtn = document.getElementById('addFeeHeadBtn');
        const addFeeHeadModalEl = document.getElementById('addFeeHeadModal');
        const feeHeadFormModal = document.getElementById('feeHeadFormModal');
        const createFeeHeadBtnModal = document.getElementById('createFeeHeadBtnModal');
        // Initialize Bootstrap Modal instance
        const modalInstance = new bootstrap.Modal(addFeeHeadModalEl);

        // Event listener to show the modal when the '+' button is clicked
        addFeeHeadBtn.addEventListener('click', () => {
            modalInstance.show();
        });

        // Event listener for creating a new fee head from the modal
        createFeeHeadBtnModal.addEventListener('click', async (e) => {
            e.preventDefault(); // Prevent default form submission
            const name = document.getElementById('name').value;
            const createdBy = document.getElementById('created_by').value;

            try {
                const response = await fetch('/api/fee-heads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}" // Laravel CSRF token
                    },
                    body: JSON.stringify({ name, created_by: createdBy })
                });

                const data = await response.json();
                const messageDiv = document.getElementById('feeHeadMessageModal');

                // Check for 'success' property in the API response and HTTP status
                if (response.ok && data.success === true) { 
                    messageDiv.innerHTML = `<div class="alert alert-success">${data.message || 'Fee head added successfully!'}</div>`;
                    feeHeadFormModal.reset(); // Clear the form
                    loadFeeHeads(); // Reload fee heads to update dropdown and then the active fees table
                } else {
                    // Handle errors from the API response
                    let errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message || 'An unknown error occurred.';
                    messageDiv.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                }
            } catch (error) {
                // Catch network or parsing errors
                document.getElementById('feeHeadMessageModal').innerHTML = `<div class="alert alert-danger">Something went wrong. Please try again.</div>`;
                console.error('Error adding fee head:', error);
            }
        });

        // Prevent default submission for the modal form (button handles submission)
        feeHeadFormModal.addEventListener('submit', function (e) {
            e.preventDefault();
        });
    });

    /**
     * Fetches fee heads from the API, populates the dropdown, and then loads active fees.
     */
    async function loadFeeHeads() {
        try {
            const response = await fetch('/api/fee-heads');
            const data = await response.json();
            // Ensure 'data' property exists and is an array, otherwise default to empty array
            const feeHeads = Array.isArray(data) ? data : data.data || [];

            const feeHeadSelect = document.getElementById('fee_head_id');
            feeHeadSelect.innerHTML = '<option value="">Select Fee Head</option>'; // Clear existing options
            feeHeadMap = {}; // Reset the map

            feeHeads.forEach((feeHead) => {
                const option = document.createElement('option');
                option.value = feeHead.id;
                option.textContent = feeHead.name;
                feeHeadSelect.appendChild(option);
                feeHeadMap[feeHead.id] = feeHead.name; // Populate the map
            });

            // IMPORTANT: Call loadActiveFees ONLY AFTER feeHeadMap is populated
            loadActiveFees();
        } catch (error) {
            console.error('Failed to fetch fee heads:', error);
            // Optionally, display an error message to the user
            document.getElementById('fee_head_id').innerHTML = '<option value="">Error loading fee heads</option>';
        }
    }

    /**
     * Fetches active fees from the API and populates the table.
     * Relies on feeHeadMap being populated by loadFeeHeads().
     */
    async function loadActiveFees() {
        try {
            const response = await fetch('/api/activeFees'); // Assuming this API returns the active fees
            const result = await response.json(); // Renamed 'data' to 'result' to avoid confusion with result.data
            const activeFees = Array.isArray(result) ? result : result.data || []; // Extract the array from 'data' property if it exists

            const tbody = document.getElementById('activeFeesTableBody');
            tbody.innerHTML = ''; // Clear existing table rows

            if (activeFees.length === 0) { // Check if the extracted array is empty
                tbody.innerHTML = '<tr><td colspan="3">No active fees found.</td></tr>';
                return;
            }

            activeFees.forEach((fee, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${feeHeadMap[fee.fee_head_id] || 'Unknown Fee Head'}</td>
                    <td>${fee.amount}</td>
                `;
                tbody.appendChild(row);
            });
        } catch (error) {
            console.error('Failed to fetch active fees:', error);
            document.getElementById('activeFeesTableBody').innerHTML = '<tr><td colspan="3">Error loading active fees.</td></tr>';
        }
    }

    // Event listener for the main fee form submission
    document.getElementById('feeForm').addEventListener('submit', async function (e) {
        e.preventDefault(); // Prevent default form submission
        const feeHeadId = document.getElementById('fee_head_id').value;
        const amount = document.getElementById('amount').value;
        const createdBy = document.querySelector('#feeForm input[name="created_by"]').value;

        try {
            const response = await fetch('/api/admin/addOrUpdateFees', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    fee_head_id: feeHeadId,
                    amount: amount,
                    created_by: createdBy
                })
            });

            const data = await response.json();
            const messageDiv = document.getElementById('feeFormMessage');
            
            // Clear previous messages
            messageDiv.innerHTML = '';

            // Updated condition to check for both HTTP success and API's 'success' flag
            if (response.ok && data.success === true) { 
                messageDiv.innerHTML = `<div class="alert alert-success">${data.message || 'Fee added/updated successfully!'}</div>`;
                document.getElementById('feeForm').reset(); // Reset the form fields
                loadActiveFees(); // Reload active fees to show the newly added/updated fee
            } else {
                // Display error messages from the API
                let errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message || 'An unknown error occurred.';
                messageDiv.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
            }
        } catch (error) {
            // Catch network or parsing errors
            document.getElementById('feeFormMessage').innerHTML = `<div class="alert alert-danger">Something went wrong. Please try again.</div>`;
            console.error('Error submitting fee form:', error);
        }
    });
</script>

@endsection

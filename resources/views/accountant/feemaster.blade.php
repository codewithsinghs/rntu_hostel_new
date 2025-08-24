@extends('accountant.layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
{{-- Optional: Add Bootstrap Icons if you haven't already for the edit icon --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container mt-5">

    {{-- Section for Adding/Updating Fees (Amount related to a Fee Head) --}}
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Add or Update Fee Amount for a Fee Head
        </div>
        <div class="card-body">
            <form id="feeForm" class="row g-3">
                <div class="col-md-6">
                    <label for="fee_head_id" class="form-label">Fee Head</label>
                    <div class="input-group">
                        <select name="fee_head_id" id="fee_head_id" class="form-select form-select-lg" required>
                            <option value="">Select Fee Head</option>
                            {{-- Fee heads will be loaded here by JavaScript --}}
                        </select>
                        <button type="button" class="btn btn-outline-secondary btn-lg" id="addFeeHeadBtn">
                            + Add New Fee Head
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="amount" class="form-label">Amount</label>
                    <input type="number" name="amount" id="amount" class="form-control form-control-lg" placeholder="Enter Fee Amount" required>
                    {{-- Null-safe access for created_by --}}
                    @if (auth()->check())
                        <input type="hidden" name="created_by" value="{{ auth()->user()->id }}">
                    @else
                        <input type="hidden" name="created_by" value="">
                    @endif
                </div>
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-success btn-lg">Submit Fee Amount</button>
                </div>
            </form>
            <div id="feeFormMessage" class="mt-3"></div>
        </div>
    </div>

    {{-- Section for displaying and managing Fee Heads --}}
    <div class="card">
        <div class="card-header bg-success text-white">
            All Fee Heads
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>S.No</th>
                            <th>Fee Head Name</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="feeHeadsTableBody">
                        <tr><td colspan="4">Loading Fee Heads...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal for Adding/Editing Fee Heads --}}
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
                        <label for="fee_head_name_modal" class="form-label">Fee Head Name</label>
                        <input type="text" name="name" id="fee_head_name_modal" class="form-control form-control-lg" placeholder="Enter Fee Head Name" required>
                        <input type="hidden" name="fee_head_id_modal" id="fee_head_id_modal" value="">
                        {{-- Null-safe access for created_by in modal --}}
                        @if (auth()->check())
                            <input type="hidden" name="created_by_modal" id="created_by_modal" value="{{ auth()->user()->id }}">
                        @else
                            <input type="hidden" name="created_by_modal" id="created_by_modal" value="">
                        @endif
                    </div>
                </form>
                <div id="feeHeadMessageModal" class="mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelEditBtn">Close</button>
                <button type="submit" class="btn btn-success" id="createFeeHeadBtnModal">Create</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let feeHeadMap = {}; // Global map to store fee head IDs and names
    let currentEditingFeeHeadId = null; // To track which fee head is being edited

    document.addEventListener('DOMContentLoaded', function() {
        // Initial load of fee heads (populates dropdown and the new table)
        loadFeeHeads();

        const addFeeHeadBtn = document.getElementById('addFeeHeadBtn');
        const addFeeHeadModalEl = document.getElementById('addFeeHeadModal');
        const feeHeadFormModal = document.getElementById('feeHeadFormModal');
        const createFeeHeadBtnModal = document.getElementById('createFeeHeadBtnModal');
        const feeHeadModalLabel = document.getElementById('addFeeHeadModalLabel');
        const feeHeadNameInputModal = document.getElementById('fee_head_name_modal');
        const feeHeadIdInputModal = document.getElementById('fee_head_id_modal');
        const cancelEditBtn = document.getElementById('cancelEditBtn');

        // Initialize Bootstrap Modal instance
        const modalInstance = new bootstrap.Modal(addFeeHeadModalEl);

        // Event listener to show the modal for adding a new fee head
        addFeeHeadBtn.addEventListener('click', () => {
            resetModalForm(); // Ensure modal is in 'add' mode
            modalInstance.show();
        });

        // Event listener for creating/updating a fee head from the modal
        createFeeHeadBtnModal.addEventListener('click', async (e) => {
            e.preventDefault();
            const name = feeHeadNameInputModal.value;
            const createdBy = document.getElementById('created_by_modal').value; // Get value from the null-safe input

            const url = currentEditingFeeHeadId
                ? `/api/fee-heads/${currentEditingFeeHeadId}`
                : '/api/fee-heads';
            const method = currentEditingFeeHeadId ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ name, created_by: createdBy })
                });

                const data = await response.json();
                const messageDiv = document.getElementById('feeHeadMessageModal');

                if (response.ok && data.success === true) {
                    messageDiv.innerHTML = `<div class="alert alert-success">${data.message || 'Fee head saved successfully!'}</div>`;
                    feeHeadFormModal.reset();
                    currentEditingFeeHeadId = null; // Reset edit state
                    loadFeeHeads(); // Reload fee heads to update dropdown and table
                    // Optionally close modal after a short delay
                    setTimeout(() => modalInstance.hide(), 1500);
                } else {
                    let errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message || 'An unknown error occurred.';
                    messageDiv.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
                }
            } catch (error) {
                document.getElementById('feeHeadMessageModal').innerHTML = `<div class="alert alert-danger">Something went wrong. Please try again.</div>`;
                console.error('Error saving fee head:', error);
            }
        });

        // Event listener for canceling edit mode in the modal
        cancelEditBtn.addEventListener('click', () => {
            resetModalForm();
            modalInstance.hide();
        });

        // Prevent default submission for the modal form (button handles submission)
        feeHeadFormModal.addEventListener('submit', function (e) {
            e.preventDefault();
        });

        /**
         * Resets the modal form to 'Add Fee Head' mode.
         */
        function resetModalForm() {
            currentEditingFeeHeadId = null;
            feeHeadModalLabel.textContent = 'Add Fee Head';
            createFeeHeadBtnModal.textContent = 'Create';
            feeHeadNameInputModal.value = '';
            feeHeadIdInputModal.value = '';
            document.getElementById('feeHeadMessageModal').innerHTML = ''; // Clear messages
        }
    });

    /**
     * Fetches fee heads from the API, populates the dropdown, and the main table.
     */
    async function loadFeeHeads() {
        try {
            const response = await fetch('/api/fee-heads');
            const data = await response.json();
            const feeHeads = Array.isArray(data) ? data : data.data || [];

            const feeHeadSelect = document.getElementById('fee_head_id');
            const feeHeadsTableBody = document.getElementById('feeHeadsTableBody');

            feeHeadSelect.innerHTML = '<option value="">Select Fee Head</option>'; // Clear existing dropdown options
            feeHeadsTableBody.innerHTML = ''; // Clear existing table rows
            feeHeadMap = {}; // Reset the map

            if (feeHeads.length === 0) {
                feeHeadsTableBody.innerHTML = '<tr><td colspan="4">No fee heads found.</td></tr>';
                return;
            }

            feeHeads.forEach((feeHead, index) => {
                // Populate dropdown
                const option = document.createElement('option');
                option.value = feeHead.id;
                option.textContent = feeHead.name;
                feeHeadSelect.appendChild(option);
                feeHeadMap[feeHead.id] = feeHead.name; // Populate the map

                // Populate main table
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${feeHead.name}</td>
                    <td>${new Date(feeHead.created_at).toLocaleDateString()}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info edit-fee-head-btn" data-id="${feeHead.id}" data-name="${feeHead.name}">
                            <i class="bi bi-pencil-square"></i> Edit
                        </button>
                    </td>
                `;
                feeHeadsTableBody.appendChild(row);
            });

            // Add event listeners for edit buttons after they are rendered
            document.querySelectorAll('.edit-fee-head-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    editFeeHead(id, name);
                });
            });

        } catch (error) {
            console.error('Failed to fetch fee heads:', error);
            document.getElementById('fee_head_id').innerHTML = '<option value="">Error loading fee heads</option>';
            document.getElementById('feeHeadsTableBody').innerHTML = '<tr><td colspan="4">Error loading fee heads.</td></tr>';
        }
    }

    /**
     * Sets the modal to edit mode and populates it with fee head data.
     * @param {string} id - The ID of the fee head to edit.
     * @param {string} name - The current name of the fee head.
     */
    function editFeeHead(id, name) {
        currentEditingFeeHeadId = id;
        document.getElementById('addFeeHeadModalLabel').textContent = 'Edit Fee Head';
        document.getElementById('createFeeHeadBtnModal').textContent = 'Update';
        document.getElementById('fee_head_name_modal').value = name;
        document.getElementById('fee_head_id_modal').value = id; // Store ID in hidden input
        document.getElementById('feeHeadMessageModal').innerHTML = ''; // Clear any previous messages
        new bootstrap.Modal(document.getElementById('addFeeHeadModal')).show();
    }

    // Event listener for the main fee form submission (for fee amounts)
    document.getElementById('feeForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const feeHeadId = document.getElementById('fee_head_id').value;
        const amount = document.getElementById('amount').value;
        const createdBy = document.querySelector('#feeForm input[name="created_by"]').value;

        try {
            const response = await fetch('/api/admin/addOrUpdateFees', { // Assuming this is the correct API for fees
                method: 'POST', // Or PUT if updating an existing fee amount
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

            messageDiv.innerHTML = ''; // Clear previous messages

            if (response.ok && data.success === true) {
                messageDiv.innerHTML = `<div class="alert alert-success">${data.message || 'Fee amount saved successfully!'}</div>`;
                document.getElementById('feeForm').reset();
                // If you have a table for 'active fees' (amounts), you'd reload it here.
                // Since we removed loadActiveFees, this might need a new function if you want to display fee amounts.
            } else {
                let errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message || 'An unknown error occurred.';
                messageDiv.innerHTML = `<div class="alert alert-danger">${errors}</div>`;
            }
        } catch (error) {
            document.getElementById('feeFormMessage').innerHTML = `<div class="alert alert-danger">Something went wrong. Please try again.</div>`;
            console.error('Error submitting fee form:', error);
        }
    });
</script>

@endsection
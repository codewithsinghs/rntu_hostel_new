@extends('accountant.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">Fine List</h2>

    <ul id="residentList" class="list-group">
        <li class="list-group-item text-muted">Loading fines...</li>
    </ul>
</div>

<!-- Modal -->
<div class="modal fade" id="fineModal" tabindex="-1" aria-labelledby="fineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="fineForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="fineModalLabel">Fine Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Resident:</strong> <span id="modalResidentName"></span></p>
                    <p><strong>Scholar No:</strong> <span id="modalScholarNo"></span></p>
                    <p><strong>Admin Remarks:</strong> <span id="modalAdminRemarks"></span></p>

                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" name="amount_paid" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks (Optional)</label>
                        <input type="text" name="payment_remarks" class="form-control">
                    </div>
                    <input type="hidden" name="subscription_id">
                    <input type="hidden" name="payment_method" value="Null">
                    <input type="hidden" name="created_by" value="{{ auth()->id() }}">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Submit Fine</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Styles & Scripts -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const residentList = document.getElementById("residentList");
    const fineModal = new bootstrap.Modal(document.getElementById('fineModal'));

    try {
        const res = await fetch("/api/accountant/view-fine-details");
        const result = await res.json();

        if (result.success && Array.isArray(result.data)) {
            residentList.innerHTML = "";

            result.data.forEach(fine => {
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";

                li.innerHTML = `
                    <div>
                        <strong>${fine.resident_name}</strong> <br>
                        <small>${fine.resident_scholar_number}</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary view-btn">Set Amount</button>
                `;

                li.querySelector('.view-btn').addEventListener("click", () => {
                    // Fill modal fields
                    document.getElementById("modalResidentName").textContent = fine.resident_name;
                    document.getElementById("modalScholarNo").textContent = fine.resident_scholar_number;
                    document.getElementById("modalAdminRemarks").textContent = fine.admin_remarks || "N/A";
                    document.querySelector("#fineForm [name='subscription_id']").value = fine.subscription_id;

                    fineModal.show();
                });

                residentList.appendChild(li);
            });
        } else {
            residentList.innerHTML = `<li class="list-group-item text-danger">No fines found.</li>`;
        }
    } catch (err) {
        console.error(err);
        residentList.innerHTML = `<li class="list-group-item text-danger">Failed to load data.</li>`;
    }

    // Handle fine form submit
    document.getElementById("fineForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const form = e.target;
        const data = {
            subscription_id: parseInt(form.subscription_id.value),
            amount_paid: parseFloat(form.amount_paid.value),
            payment_method: "Null",
            created_by: parseInt(form.created_by.value),
            payment_remarks: form.payment_remarks.value
        };

        try {
            const res = await fetch("/api/accountant/set-fine-amount", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                body: JSON.stringify(data)
            });

            const result = await res.json();
            if (result.success) {
                alert("Fine submitted successfully.");
                form.reset();
                fineModal.hide();
            } else {
                alert("Error: " + (result.message || "Submission failed."));
            }
        } catch (err) {
            console.error(err);
            alert("An unexpected error occurred.");
        }
    });
});
</script>
@endsection

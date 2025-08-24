@extends('accountant.layout')

@section('content')
<div class="container">
    <h1>Resident Payment History</h1>

    <div class="mb-3">
        <input type="text" id="resident_search_input" class="form-control" placeholder="Search by Scholar No, Name, Email, or Number" style="max-width: 400px; display: inline-block;">
        <button id="resident_search_btn" class="btn btn-primary">Search</button>
        <button id="resident_search_reset_btn" class="btn btn-secondary">Reset</button>
    </div>

    <h5>Resident List:</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>S. No.</th>
                    <th>Scholar No</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="resident_table_body">
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center align-items-center gap-2">
        <button class="btn btn-outline-primary" id="prev_page_btn">Previous</button>
        <div id="current_page_display" class="border rounded px-2 py-1">1</div>
        <button class="btn btn-outline-primary" id="next_page_btn">Next</button>
    </div>

    {{-- Modal for Subscriptions --}}
    <div class="modal fade" id="viewSubscriptionsModal" tabindex="-1" aria-labelledby="viewSubscriptionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewSubscriptionsModalLabel">Resident Pending Subscriptions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Type</th>
                                    <th>Latest Paid Amount</th>
                                    <th>Total Amount</th>
                                    <th>Remaining Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pending_subscriptions_body">
                            </tbody>
                        </table>
                    </div>
                    <p id="no_pending_subscriptions_message" class="mt-3 text-center text-info" style="display: none;">No pending subscriptions found for this resident.</p>
                    <p id="subscription_error_message" class="mt-3 text-center text-danger" style="display: none;">Error loading subscriptions. Please try again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for Accessories --}}
    <div class="modal fade" id="viewAccessoriesModal" tabindex="-1" aria-labelledby="viewAccessoriesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAccessoriesModalLabel">Resident Pending Accessory Payments</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Accessory Name</th>
                                    <th>Latest Paid Amount</th>
                                    <th>Remaining Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="pending_accessories_body">
                            </tbody>
                        </table>
                    </div>
                    <p id="no_pending_accessories_message" class="mt-3 text-center text-info" style="display: none;">No pending accessory payments found for this resident.</p>
                    <p id="accessory_error_message" class="mt-3 text-center text-danger" style="display: none;">Error loading accessory payments. Please try again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- New Modal for All Payments --}}
    <div class="modal fade" id="viewAllPaymentsModal" tabindex="-1" aria-labelledby="viewAllPaymentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAllPaymentsModalLabel">Resident All Payments History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Transaction ID</th>
                                    <th>Description</th> {{-- Changed from Remarks to Description to be more generic --}}
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody id="all_payments_body">
                            </tbody>
                        </table>
                    </div>
                    <p id="no_all_payments_message" class="mt-3 text-center text-info" style="display: none;">No payment history found for this resident.</p>
                    <p id="all_payments_error_message" class="mt-3 text-center text-danger" style="display: none;">Error loading all payments history. Please try again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const $residentTableBody = $('#resident_table_body');
        const $searchInput = $('#resident_search_input');
        const $prevPageBtn = $('#prev_page_btn');
        const $nextPageBtn = $('#next_page_btn');
        const $currentPageDisplay = $('#current_page_display');
        const $residentSearchResetBtn = $('#resident_search_reset_btn');

        const $viewSubscriptionsModal = $('#viewSubscriptionsModal');
        const $pendingSubscriptionsBody = $('#pending_subscriptions_body');
        const $noPendingSubscriptionsMessage = $('#no_pending_subscriptions_message');
        const $subscriptionErrorMessage = $('#subscription_error_message');

        const $viewAccessoriesModal = $('#viewAccessoriesModal');
        const $pendingAccessoriesBody = $('#pending_accessories_body');
        const $noPendingAccessoriesMessage = $('#no_pending_accessories_message');
        const $accessoryErrorMessage = $('#accessory_error_message');

        const $viewAllPaymentsModal = $('#viewAllPaymentsModal');
        const $allPaymentsBody = $('#all_payments_body');
        const $noAllPaymentsMessage = $('#no_all_payments_message');
        const $allPaymentsErrorMessage = $('#all_payments_error_message');

        const RESIDENTS_PER_PAGE = 10;
        let residentsData = [];
        let currentPage = 1;
        let totalPages = 0;
        let currentSearchQuery = '';

        function renderResidents(residents, page) {
            $residentTableBody.empty();
            const start = (page - 1) * RESIDENTS_PER_PAGE;
            const paginatedResidents = residents.slice(start, start + RESIDENTS_PER_PAGE);

            if (paginatedResidents.length > 0) {
                paginatedResidents.forEach((resident, index) => {
                    const phoneNumber = resident.guest ? resident.guest.emergency_no : 'N/A';

                    const row = `
                    <tr data-scholar="${resident.scholar_number ? resident.scholar_number.toLowerCase() : ''}"
                        data-name="${resident.name ? resident.name.toLowerCase() : ''}"
                        data-email="${resident.email ? resident.email.toLowerCase() : ''}"
                        data-number="${phoneNumber.toLowerCase()}">
                        <td>${start + index + 1}</td>
                        <td>${resident.scholar_number ?? 'N/A'}</td>
                        <td>${resident.name ?? 'N/A'}</td>
                        <td>${resident.email ?? 'N/A'}</td>
                        <td>${resident.gender ?? 'N/A'}</td>
                        <td>${phoneNumber}</td>
                        <td>
                            <button class="btn btn-sm btn-warning view-subscriptions-btn" data-id="${resident.id}">Pending Subscriptions</button>
                            <button class="btn btn-sm btn-info view-accessories-btn" data-id="${resident.id}">Pending Accessories</button>
                            <button class="btn btn-sm btn-success view-all-payments-btn" data-id="${resident.id}">View All Payments</button>
                        </td>
                    </tr>
                    `;
                    $residentTableBody.append(row);
                });
            } else {
                $residentTableBody.append('<tr><td colspan="7" class="text-center">No residents found on this page.</td></tr>');
            }

            $currentPageDisplay.text(currentPage);
            $prevPageBtn.prop('disabled', currentPage <= 1);
            $nextPageBtn.prop('disabled', currentPage >= totalPages);
        }

        function loadInitialData() {
            $.ajax({
                url: '/api/residents',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.data && Array.isArray(data.data) && data.data.length > 0) {
                        residentsData = data.data;
                        totalPages = Math.ceil(residentsData.length / RESIDENTS_PER_PAGE);
                        currentPage = 1;
                        renderResidents(residentsData, currentPage);
                    } else {
                        residentsData = [];
                        totalPages = 0;
                        currentPage = 0;
                        renderResidents(residentsData, currentPage);
                        $prevPageBtn.prop('disabled', true);
                        $nextPageBtn.prop('disabled', true);
                        $currentPageDisplay.text(0);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error loading residents:", status, error);
                    $residentTableBody.empty().append('<tr><td colspan="7" class="text-danger text-center">Error loading residents. Please try again.</td></tr>');
                    $prevPageBtn.prop('disabled', true);
                    $nextPageBtn.prop('disabled', true);
                    $currentPageDisplay.text(0);
                }
            });
        }

        function applySearchAndRender() {
            let filteredResidents;
            if (currentSearchQuery === '') {
                filteredResidents = residentsData;
            } else {
                filteredResidents = residentsData.filter(resident => {
                    const scholarNo = resident.scholar_number ? resident.scholar_number.toLowerCase() : '';
                    const name = resident.name ? resident.name.toLowerCase() : '';
                    const email = resident.email ? resident.email.toLowerCase() : '';
                    const phoneNumber = resident.guest && resident.guest.emergency_no ? resident.guest.emergency_no.toLowerCase() : '';

                    return scholarNo.includes(currentSearchQuery) ||
                        name.includes(currentSearchQuery) ||
                        email.includes(currentSearchQuery) ||
                        phoneNumber.includes(currentSearchQuery);
                });
            }
            totalPages = Math.ceil(filteredResidents.length / RESIDENTS_PER_PAGE);
            currentPage = 1;
            renderResidents(filteredResidents, currentPage);
        }

        $('#resident_search_btn').on('click', function() {
            currentSearchQuery = $searchInput.val().trim().toLowerCase();
            applySearchAndRender();
        });

        $residentSearchResetBtn.on('click', function() {
            $searchInput.val('');
            currentSearchQuery = '';
            currentPage = 1;
            totalPages = Math.ceil(residentsData.length / RESIDENTS_PER_PAGE);
            renderResidents(residentsData, currentPage);
        });

        $prevPageBtn.on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                applySearchAndRender();
            }
        });

        $nextPageBtn.on('click', function() {
            if (currentPage < totalPages) {
                currentPage++;
                applySearchAndRender();
            }
        });

        $(document).on('click', '.view-subscriptions-btn', function() {
            const residentId = $(this).data('id');

            $viewSubscriptionsModal.data('resident-id', residentId);

            $pendingSubscriptionsBody.empty();
            $noPendingSubscriptionsMessage.hide();
            $subscriptionErrorMessage.hide();

            $pendingSubscriptionsBody.append('<tr><td colspan="5" class="text-center text-muted">Loading subscriptions...</td></tr>');
            $viewSubscriptionsModal.modal('show');

            $.ajax({
                url: `/api/pending/${residentId}/subscription`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $pendingSubscriptionsBody.empty();

                    if (response.success && response.data && response.data.subscriptions && response.data.subscriptions.length > 0) {
                        response.data.subscriptions.forEach((subscription, index) => {
                            const firstPayment = subscription.payments && subscription.payments.length > 0 ? subscription.payments[0] : null;

                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${subscription.subscription_type ?? 'N/A'}</td>
                                    <td>${firstPayment ? (firstPayment.amount ?? 'N/A') : 'N/A'}</td>
                                    <td>${firstPayment ? (firstPayment.total_amount ?? 'N/A') : 'N/A'}</td>
                                    <td>${firstPayment ? (firstPayment.remaining_amount ?? 'N/A') : 'N/A'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info make-subscription-payment-btn"
                                                    data-subscription-id="${subscription.subscription_id}"
                                                    data-resident-id="${residentId}">Pay</button>
                                    </td>
                                </tr>
                                `;
                            $pendingSubscriptionsBody.append(row);
                        });
                    } else {
                        $noPendingSubscriptionsMessage.show();
                    }
                },
                error: function(xhr, status, error) {
                    $pendingSubscriptionsBody.empty();
                    $subscriptionErrorMessage.text('Error fetching subscriptions: ' + (xhr.responseJSON?.message || error)).show();
                    console.error("Error fetching subscriptions:", status, error, xhr.responseJSON);
                }
            });
        });

        $(document).on('click', '.view-accessories-btn', function() {
            const residentId = $(this).data('id');

            $viewAccessoriesModal.data('resident-id', residentId);

            $pendingAccessoriesBody.empty();
            $noPendingAccessoriesMessage.hide();
            $accessoryErrorMessage.hide();

            $pendingAccessoriesBody.append('<tr><td colspan="5" class="text-center text-muted">Loading accessory payments...</td></tr>');
            $viewAccessoriesModal.modal('show');

            $.ajax({
                url: `/api/resident/${residentId}/accessories`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $pendingAccessoriesBody.empty();

                    if (response.success && response.data && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach((accessory, index) => {
                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${accessory.accessory_name ?? 'N/A'}</td>
                                    <td>${accessory.amount ?? 'N/A'}</td>
                                    <td>${accessory.remaining_amount ?? 'N/A'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info make-accessory-payment-btn"
                                                    data-accessory-payment-id="${accessory.payment_id}"
                                                    data-resident-id="${residentId}"
                                                    data-student-accessory-id="${accessory.student_accessory_id}">Pay</button>
                                    </td>
                                </tr>
                                `;
                            $pendingAccessoriesBody.append(row);
                        });
                    } else {
                        $noPendingAccessoriesMessage.show();
                    }
                },
                error: function(xhr, status, error) {
                    $pendingAccessoriesBody.empty();
                    $accessoryErrorMessage.text('Error fetching accessory payments: ' + (xhr.responseJSON?.message || error)).show();
                    console.error("Error fetching accessories:", status, error, xhr.responseJSON);
                }
            });
        });

        $(document).on('click', '.view-all-payments-btn', function() {
            const residentId = $(this).data('id');

            $allPaymentsBody.empty();
            $noAllPaymentsMessage.hide();
            $allPaymentsErrorMessage.hide();

            $allPaymentsBody.append('<tr><td colspan="9" class="text-center text-muted">Loading all payments...</td></tr>');
            $viewAllPaymentsModal.modal('show');

            $.ajax({
                url: `/api/payments/resident/${residentId}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $allPaymentsBody.empty();
                    if (response.success && response.data && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach((payment, index) => {
                            let description = payment.remarks ?? 'N/A'; // Default to existing remarks

                            if (payment.subscription_name && payment.subscription_name !== 'N/A') {
                                description = `Subscription: ${payment.subscription_name}`;
                            } else if (payment.accessory_name && payment.accessory_name !== 'N/A') {
                                description = `Accessory: ${payment.accessory_name}`;
                            } else if (payment.fee_head_name && payment.fee_head_name !== 'N/A') {
                                description = `Fee Head: ${payment.fee_head_name}`;
                            }


                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${payment.transaction_id ?? 'N/A'}</td>
                                    <td>${description}</td>
                                    <td>${payment.total_amount ?? 'N/A'}</td>
                                    <td>${payment.amount ?? 'N/A'}</td>
                                    <td>${payment.remaining_amount ?? 'N/A'}</td>
                                    <td>${payment.payment_method ?? 'N/A'}</td>
                                    <td>${payment.payment_status ?? 'N/A'}</td>
                                    <td>${payment.created_at ?? 'N/A'}</td>
                                    <td>${payment.due_date ?? 'N/A'}</td>
                                </tr>
                            `;
                            $allPaymentsBody.append(row);
                        });
                    } else {
                        $noAllPaymentsMessage.show();
                    }
                },
                error: function(xhr, status, error) {
                    $allPaymentsBody.empty();
                    $allPaymentsErrorMessage.text('Error fetching all payments: ' + (xhr.responseJSON?.message || error)).show();
                    console.error("Error fetching all payments:", status, error, xhr.responseJSON);
                }
            });
        });

        $(document).on('click', '.make-subscription-payment-btn', function() {
            const residentId = $(this).data('resident-id');
            const subscriptionId = $(this).data('subscription-id');

            if (residentId && subscriptionId) {
                $viewSubscriptionsModal.modal('hide');
                window.location.href = `/accountant/resident/pay?resident_id=${residentId}&subscription_id=${subscriptionId}`;
            } else {
                console.warn("Missing resident ID or subscription ID for payment.");
                alert("Could not determine payment details. Please try again.");
            }
        });

        $(document).on('click', '.make-accessory-payment-btn', function() {
            const residentId = $(this).data('resident-id');
            const paymentId = $(this).data('accessory-payment-id');
            const studentAccessoryId = $(this).data('student-accessory-id');

            if (residentId && paymentId) {
                $viewAccessoriesModal.modal('hide');
                window.location.href = `/accountant/resident/accessory-pay?resident_id=${residentId}&accessory_payment_id=${paymentId}&student_accessory_id=${studentAccessoryId}`;
            } else {
                console.warn("Missing resident ID or accessory payment ID for payment.");
                alert("Could not determine accessory payment details. Please try again.");
            }
        });

        loadInitialData();
    });
</script>
@endsection

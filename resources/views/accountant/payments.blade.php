@extends('accountant.layout')

@section('content')
<div class="container mt-5">
    <h2>Resident Payment Form</h2>

    <form id="payment-form">
        <div class="mb-3" style="display: none;">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-select" required>
                <option value="">-- Select Resident --</option>
            </select>
        </div>

        <div class="mb-3 ">
            <label for="resident_name" class="form-label">Name</label>
            <input type="text" id="resident_name" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="scholar_no" class="form-label">Scholar Number</label>
            <input type="text" id="scholar_no" class="form-control" readonly />
        </div>

        <div class="mb-3">
            <label for="subscription_id" class="form-label">Subscription ID</label>
            <select name="subscription_id" id="subscription_id" class="form-select" required>
                <option value="">-- Select Subscription --</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control" required />
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
                <option value="">-- Select Payment Method --</option>
                <option value="Cash">Cash</option>
                <option value="UPI">UPI</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Card">Card</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" min="1" step="0.01" name="amount" id="amount" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-primary">Submit Payment</button>
    </form>

    <div id="message" class="mt-3"></div>
</div>

<!-- jQuery and Select2 CSS/JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
    function getQueryParam(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    }

    // Initialize Select2 for resident select
    $('#resident_id').select2({
        placeholder: 'Search Resident...',
        allowClear: true,
        ajax: {
            url: '/api/residents',
            dataType: 'json',
            delay: 250,
            data: params => ({ search: params.term }),
            processResults: function(data) {
                return {
                    results: (data.residents || []).map(r => ({
                        id: r.id,
                        text: `${r.name} (${r.scholar_no})`,
                        extra: r
                    }))
                };
            },
            cache: true
        },
        minimumInputLength: 1
    });

    function loadSubscriptions(residentId) {
        if (!residentId) {
            $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
            return;
        }
        $.ajax({
            url: `/api/pending/${residentId}/subscription`,
            method: 'GET',
            success: function(data) {
                $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
                if (data.subscriptions && data.subscriptions.length > 0) {
                    data.subscriptions.forEach(function(sub) {
                        const optionText = `Subscription ID: ${sub.subscription_id} - Status: ${sub.status}`;
                        $('#subscription_id').append(`<option value="${sub.subscription_id}">${optionText}</option>`);
                    });
                    $('#subscription_id').val(data.subscriptions[0].subscription_id).trigger('change');
                } else {
                    $('#subscription_id').append('<option value="">No subscriptions found</option>');
                }
            },
            error: function() {
                $('#subscription_id').empty().append('<option value="">Error loading subscriptions</option>');
            }
        });
    }

    // On resident select
    $('#resident_id').on('select2:select', function(e) {
        const selectedData = e.params.data;
        $('#resident_name').val(selectedData.extra.name || '');
        $('#scholar_no').val(selectedData.extra.scholar_no || '');
        loadSubscriptions(selectedData.id);
    });

    // Clear name, scholar_no, and subscriptions on clear
    $('#resident_id').on('select2:clear', function() {
        $('#resident_name').val('');
        $('#scholar_no').val('');
        $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
    });

    // Preselect resident if resident_id in URL
    const residentIdFromUrl = getQueryParam('resident_id');
    if (residentIdFromUrl) {
        $.ajax({
            url: `/api/residents?search=${residentIdFromUrl}`,
            success: function(data) {
                const resident = data.residents.find(r => r.id == residentIdFromUrl);
                if (resident) {
                    const option = new Option(`${resident.name} (${resident.scholar_no})`, resident.id, true, true);
                    $('#resident_id').append(option).trigger('change');

                    $('#resident_name').val(resident.name);
                    $('#scholar_no').val(resident.scholar_no);
                    loadSubscriptions(resident.id);
                }
            }
        });
    }

    // Handle form submission
    $('#payment-form').submit(function(e) {
        e.preventDefault();

        const formData = {
            resident_id: $('#resident_id').val(),
            subscription_id: $('#subscription_id').val(),
            transaction_id: $('#transaction_id').val(),
            payment_method: $('#payment_method').val(),
            amount: $('#amount').val()
        };

        $('#message').removeClass('alert alert-success alert-danger').text('');

        $.ajax({
            url: '/api/account/subscribe/pay',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#message').addClass('alert alert-success').text(response.message);
                $('#payment-form')[0].reset();
                $('#resident_id').val(null).trigger('change');
                $('#resident_name').val('');
                $('#scholar_no').val('');
                $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
            },
            error: function(xhr) {
                let errMsg = 'An error occurred.';
                if(xhr.responseJSON) {
                    if(xhr.responseJSON.error) {
                        errMsg = xhr.responseJSON.error;
                    }
                    else if(xhr.responseJSON.messages) {
                        errMsg = Object.values(xhr.responseJSON.messages).flat().join(' ');
                    }
                }
                $('#message').addClass('alert alert-danger').text(errMsg);
            }
        });
    });
});
</script>
@endsection

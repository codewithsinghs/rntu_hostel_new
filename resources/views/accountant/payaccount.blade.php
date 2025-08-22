@extends('accountant.layout')

@section('content')
<div class="container mt-5">
    <h2>Subscription Payments Form</h2>

    <form id="payment-form">
        {{-- Resident ID field - still hidden, but essential for data --}}
        <div class="mb-3" style="display: none;">
            <label for="resident_id" class="form-label">Resident</label>
            <select name="resident_id" id="resident_id" class="form-select" required>
                <option value="">-- Select Resident --</option>
            </select>
        </div>

        {{-- Resident Name (visible) --}}
        <div class="mb-3 ">
            <label for="resident_name" class="form-label">Name</label>
            <input type="text" id="resident_name" class="form-control" readonly />
        </div>

        {{-- Scholar Number (visible) --}}
        <div class="mb-3">
            <label for="scholar_no" class="form-label">Scholar Number</label>
            <input type="text" id="scholar_no" class="form-control" readonly />
        </div>

        {{-- Subscription ID field - now hidden --}}
        <div class="mb-3" style="display: none;">
            <label for="subscription_id" class="form-label">Subscription ID</label>
            <select name="subscription_id" id="subscription_id" class="form-select" required>
                <option value="">-- Select Subscription --</option>
            </select>
        </div>

        {{-- Transaction ID field - now hidden and readonly --}}
        <div class="mb-3" style="display: none;">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" name="transaction_id" id="transaction_id" class="form-control" required readonly />
        </div>

        {{-- Payment Method (visible) --}}
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

        {{-- Amount (visible) --}}
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" min="1" step="0.01" name="amount" id="amount" class="form-control" required />
        </div>

        <button type="submit" class="btn btn-primary">Submit Payment</button>
    </form>

    <div id="message" class="mt-3"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        /**
         * Retrieves a query parameter from the URL.
         * @param {string} name - The name of the query parameter.
         * @returns {string|null} The value of the query parameter, or null if not found.
         */
        function getQueryParam(name) {
            const params = new URLSearchParams(window.location.search);
            return params.get(name);
        }

        /**
         * Generates a simple client-side transaction ID.
         * For production, consider generating this on the backend to guarantee uniqueness.
         * @returns {string} A generated transaction ID.
         */
        function generateTransactionId() {
            const timestamp = new Date().getTime();
            const random = Math.floor(Math.random() * 999999); // Increased random range
            return `TRX-${timestamp}-${random}`;
        }

        // Initialize Select2 for resident select dropdown
        $('#resident_id').select2({
            placeholder: 'Search Resident...',
            allowClear: true,
            ajax: {
                url: '/api/residents', // API endpoint for searching residents
                dataType: 'json',
                delay: 250, // Delay in milliseconds before sending the request
                data: params => ({ search: params.term }), // Data to send with the request
                processResults: function(response) { // Changed 'data' to 'response'
                    // Process the results from the API response
                    // Assuming resident data is in `response.data` array
                    return {
                        results: (response.data || []).map(r => ({ // Access 'response.data' here
                            id: r.id,
                            text: `${r.name} (${r.scholar_no})`,
                            extra: r // Store the full resident object for later use
                        }))
                    };
                },
                cache: true // Cache the results
            },
            minimumInputLength: 1 // Minimum characters to type before search
        });

        /**
         * Loads subscriptions for a given resident ID into the subscription_id select.
         * @param {number} residentId - The ID of the resident.
         * @param {string|null} preselectSubscriptionId - Optional ID to pre-select.
         */
        function loadSubscriptions(residentId, preselectSubscriptionId = null) {
            if (!residentId) {
                $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
                return;
            }
            $.ajax({
                url: `/api/pending/${residentId}/subscription`, // API endpoint for resident subscriptions
                method: 'GET',
                success: function(response) {
                    $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');

                    // Access the subscriptions array correctly from the nested 'data.subscriptions'
                    let subscriptions = response.data && response.data.subscriptions ? response.data.subscriptions : [];

                    if (subscriptions && Array.isArray(subscriptions) && subscriptions.length > 0) {
                        subscriptions.forEach(function(sub) {
                            // Store the subscription fee as a data attribute on the option
                            const optionText = `Subscription ID: ${sub.subscription_id} - Type: ${sub.subscription_type} (Fee: ${sub.subscription_fee})`;
                            $('#subscription_id').append(`<option value="${sub.subscription_id}" data-fee="${sub.subscription_fee}">${optionText}</option>`);
                        });

                        // Attempt to pre-select subscription from URL after loading options
                        if (preselectSubscriptionId) {
                            const $optionToSelect = $('#subscription_id option[value="' + preselectSubscriptionId + '"]');
                            if ($optionToSelect.length > 0) {
                                $('#subscription_id').val(preselectSubscriptionId).trigger('change');
                            } else {
                                console.warn(`Subscription ID ${preselectSubscriptionId} from URL not found in loaded subscriptions. Showing first available.`);
                                // If URL ID not found, fall back to auto-selecting the first one if available
                                $('#subscription_id').val(subscriptions[0].subscription_id).trigger('change');
                            }
                        } else if (subscriptions.length === 1) {
                            // If only one subscription is found and no URL ID, auto-select it
                            $('#subscription_id').val(subscriptions[0].subscription_id).trigger('change');
                        } else {
                             // No URL ID, multiple subscriptions: leave default '-- Select Subscription --' or auto-select first
                             // For now, it will default to '-- Select Subscription --'
                        }

                    } else {
                        $('#subscription_id').append('<option value="">No pending subscriptions found</option>');
                        $('#amount').val(''); // Clear amount if no subscriptions
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`Error loading subscriptions for resident ${residentId}:`, status, error);
                    $('#subscription_id').empty().append('<option value="">Error loading subscriptions</option>');
                    $('#amount').val(''); // Clear amount on error
                }
            });
        }

        // Event listener for when a resident is selected from the Select2 dropdown
        $('#resident_id').on('select2:select', function(e) {
            const selectedData = e.params.data;
            // Populate name and scholar number fields
            $('#resident_name').val(selectedData.extra.name || '');
            $('#scholar_no').val(selectedData.extra.scholar_no || '');
            // Load subscriptions for the selected resident. Pass URL subscription ID.
            loadSubscriptions(selectedData.id, getQueryParam('subscription_id'));
        });

        // Event listener for when the resident selection is cleared
        $('#resident_id').on('select2:clear', function() {
            $('#resident_name').val('');
            $('#scholar_no').val('');
            $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
            $('#amount').val(''); // Clear amount when resident is cleared
        });

        // Event listener for subscription selection to auto-fill amount
        $('#subscription_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const fee = selectedOption.data('fee'); // Get fee from data attribute
            if (fee !== undefined) {
                $('#amount').val(fee);
            } else {
                $('#amount').val(''); // Clear amount if no fee is associated or option is '-- Select --'
            }
        });

        // --- Initial Load Logic ---
        // Get query parameters from URL
        const residentIdFromUrl = getQueryParam('resident_id');
        const subscriptionIdFromUrl = getQueryParam('subscription_id');

        // Generate transaction ID on page load
        $('#transaction_id').val(generateTransactionId());

        // Preselect resident and then subscriptions if IDs are in URL on page load
        if (residentIdFromUrl) {
            $.ajax({
                url: `/api/residents?id=${residentIdFromUrl}`, // Using 'id' if your API supports direct lookup by ID
                // Alternatively, if your API only supports 'search' for name/scholar_no:
                // url: `/api/residents?search=${residentIdFromUrl}`,
                success: function(response) { // Changed 'data' to 'response'
                    const resident = (response.data || []).find(r => r.id == residentIdFromUrl); // Access 'response.data'
                    if (resident) {
                        const option = new Option(`${resident.name} (${resident.scholar_no})`, resident.id, true, true);
                        $('#resident_id').append(option).trigger('change'); // Add option and trigger change for Select2

                        $('#resident_name').val(resident.name);
                        $('#scholar_no').val(resident.scholar_no);
                        // Load subscriptions for the preselected resident, and attempt to pre-select from URL
                        loadSubscriptions(resident.id, subscriptionIdFromUrl);
                    } else {
                         console.warn(`Resident with ID ${residentIdFromUrl} from URL not found.`);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(`Error fetching resident with ID ${residentIdFromUrl}:`, status, error);
                }
            });
        }

        // --- Form Submission Logic ---
        $('#payment-form').submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            // Ensure transaction ID is set before submission (in case it wasn't on initial load)
            if(!$('#transaction_id').val()) {
                $('#transaction_id').val(generateTransactionId());
            }

            const formData = {
                resident_id: $('#resident_id').val(),
                // subscription_id will be auto-selected or manually chosen.
                // We ensure it has a value as it's required.
                subscription_id: $('#subscription_id').val(),
                transaction_id: $('#transaction_id').val(), // Use the auto-generated ID
                payment_method: $('#payment_method').val(),
                amount: $('#amount').val()
            };

            $('#message').removeClass('alert alert-success alert-danger').text(''); // Clear previous messages

            // Basic validation for hidden fields
            if (!formData.resident_id) {
                $('#message').addClass('alert alert-danger').text('Resident is required.');
                return;
            }
            if (!formData.subscription_id) {
                $('#message').addClass('alert alert-danger').text('Subscription is required.');
                return;
            }
            if (!formData.transaction_id) {
                $('#message').addClass('alert alert-danger').text('Transaction ID could not be generated.');
                return;
            }


            $.ajax({
                url: '/api/account/subscribe/pay', // API endpoint for submitting payment
                method: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for Laravel protection
                },
                success: function(response) {
                    $('#message').addClass('alert alert-success').text(response.message);
                    // Reset the form after successful submission
                    $('#payment-form')[0].reset();
                    $('#resident_id').val(null).trigger('change'); // Clear Select2 selection
                    $('#resident_name').val('');
                    $('#scholar_no').val('');
                    $('#subscription_id').empty().append('<option value="">-- Select Subscription --</option>');
                    $('#amount').val(''); // Reset amount field
                    // Generate a new transaction ID for the next potential payment
                    $('#transaction_id').val(generateTransactionId());
                },
                error: function(xhr) {
                    let errMsg = 'An error occurred. Please try again.';
                    if(xhr.responseJSON) {
                        if(xhr.responseJSON.error) {
                            errMsg = xhr.responseJSON.error;
                        } else if(xhr.responseJSON.messages) {
                            // Concatenate validation error messages
                            errMsg = Object.values(xhr.responseJSON.messages).flat().join(' ');
                        } else if (xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message; // General message field
                        }
                    }
                    $('#message').addClass('alert alert-danger').text(errMsg);
                }
            });
        });
    });
</script>
@endsection
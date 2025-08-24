@extends('layout')

@section('head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            /* Light gray background */
        }

        /* Custom styles for invalid feedback to ensure it's always visible when needed */
        .form-control.is-invalid+.invalid-feedback,
        .form-select.is-invalid+.invalid-feedback,
        .form-check-input.is-invalid+.form-check-label+.invalid-feedback,
        /* Added for general invalid states for form elements */
        .form-control.is-invalid,
        .form-select.is-invalid,
        .form-check-input.is-invalid,
        .form-control.is-invalid+.invalid-feedback {
            /* Added for textarea specific error */
            border-color: #dc3545;
            /* Red border for invalid fields */
        }

        /* Hide default browser validation messages for more control */
        input:invalid:not(:focus):not(:placeholder-shown),
        select:invalid:not(:focus):not(:placeholder-shown),
        textarea:invalid:not(:focus):not(:placeholder-shown) {
            border-color: #dc3545;
            /* Red border for invalid fields */
        }

        */

        /* Style for required asterisk */
        .required {
            color: #ef4444;
            /* Red color for required indicator */
        }

        button.accordion-button.collapsed {
            color: white;
        }
    </style>
@endsection
@section('content')
    <div class="container mt-5 mb-5">
        <h2 class="text-center bg-dark text-white p-3">Guest Registration</h2>

        <div id="errorMessage" class="alert alert-danger d-none" role="alert">
            <strong>Error!</strong> <span id="errorMessageText">Something went wrong. Please try again.</span>
        </div>

        <div id="registrationSuccessContainer" class="alert alert-success d-none text-center">
            <h4 class="alert-heading">Guest registered successfully!</h4>
            <p>Thank you!</p>
        </div>

        <div id="approvalMessageContainer" class="alert alert-info d-none text-center">
            Your registration is awaiting admin approval. Keep checking for updates.
        </div>

        <form id="registrationForm" class="card p-4 shadow-sm" novalidate>
            @csrf
            <h5 class="card-header bg-primary text-white">Personal Details</h5>
            <div class="card-body">
                <div class="row">
                    <!-- Scholar Number -->
                    <div class="col-md-6 mb-3">
                        <label for="scholar_number" class="form-label">Scholar Number <span
                                class="text-danger">*</span></label>
                        <input type="text" name="scholar_number" id="scholar_number" class="form-control"
                            pattern="[a-zA-Z0-9]+" title="Only letters and digits allowed" required
                            aria-describedby="scholarNoError">
                        <div id="scholarNoError" class="invalid-feedback"></div>
                    </div>

                    <!-- Full Name -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control"
                            aria-describedby="nameError" required>
                        <div id="nameError" class="invalid-feedback"></div>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control"
                            aria-describedby="emailError" required>
                        <div id="emailError" class="invalid-feedback"></div>
                    </div>

                    <!-- Mobile Number -->
                    <div class="col-md-6">
                        <label for="mobile" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="text" name="mobile" id="mobile" class="form-control" pattern="[0-9]{10}"
                            aria-describedby="mobileError" required>
                        <div id="mobileError" class="invalid-feedback"></div>
                    </div>

                    <!-- Faculty -->
                    <div class="col-md-6 mb-3">
                        <label for="faculty" class="form-label">Select Faculty <span class="text-danger">*</span></label>
                        <select name="faculty_id" id="faculty" class="form-select" required
                            aria-describedby="facultyError">
                            <option value="">Select Faculty</option>
                            <option value="1">Faculty of Science</option>
                            <option value="2">Faculty of Arts</option>
                            <option value="3">Faculty of Engineering</option>
                        </select>
                        <div id="facultyError" class="invalid-feedback"></div>
                    </div>

                    <!-- Department -->
                    <div class="col-md-6 mb-3">
                        <label for="department" class="form-label">Select Department <span
                                class="text-danger">*</span></label>
                        <select name="department_id" id="department" class="form-select" required
                            aria-describedby="departmentError">
                            <option value="">Select Department</option>
                        </select>
                        <div id="departmentError" class="invalid-feedback"></div>
                    </div>

                    <!-- Course -->
                    <div class="col-md-6 mb-3">
                        <label for="course" class="form-label">Select Course <span class="text-danger">*</span></label>
                        <select name="course_id" id="course" class="form-select" required aria-describedby="courseError">
                            <option value="">Select Course</option>
                        </select>
                        <div id="courseError" class="invalid-feedback"></div>
                    </div>

                    <!-- Gender -->
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select name="gender" id="gender" class="form-select" required
                            aria-describedby="genderError">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <div id="genderError" class="invalid-feedback"></div>
                    </div>

                </div>
                {{-- <div class="row mb-3">
                    <!-- Password -->
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" minlength="8"
                            required autocomplete="new-password" aria-describedby="passwordError"
                            title="Password must be at least 8 characters long.">
                        <div id="passwordError" class="invalid-feedback"></div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm Password <span
                                class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" minlength="8" required autocomplete="new-password"
                            aria-describedby="confirmPasswordError" title="Please re-enter the same password.">
                        <div id="confirmPasswordError" class="invalid-feedback"></div>
                    </div>
                </div> --}}

                <!-- Fee Waiver -->
                <div class="accordion mb-4" id="feeWaiverAccordion">
                    <div class="accordion-item border-0 shadow-sm">
                        <h2 class="accordion-header" id="feeWaiverHeading">
                            <button class="accordion-button collapsed fw-semibold " type="button"
                                data-bs-toggle="collapse" data-bs-target="#feeWaiverCollapse" aria-expanded="false"
                                aria-controls="feeWaiverCollapse">
                                Fee Waiver Request (Optional)
                            </button>
                        </h2>
                        <div id="feeWaiverCollapse" class="accordion-collapse collapse"
                            aria-labelledby="feeWaiverHeading" data-bs-parent="#feeWaiverAccordion">
                            <div class="accordion-body">

                                <!-- Introductory Message -->
                                <div class="alert alert-info mb-4" role="alert">
                                    If you already have documentation or eligibility for a fee waiver, you may submit your
                                    request here. This section is optional and intended to support students with verified
                                    Channel.
                                </div>

                                <!-- Apply Fee Waiver Checkbox -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="fee_waiver" id="fee_waiver"
                                        value="1">
                                    <label class="form-check-label fw-medium" for="fee_waiver">
                                        I would like to request a fee waiver based on existing eligibility or documentation.
                                    </label>
                                    <div id="feeWaiverError" class="invalid-feedback"></div>
                                </div>

                                <!-- Upload Supporting Document -->
                                <div class="mb-3">
                                    <label for="waiver_document" class="form-label">Supporting Document</label>
                                    <input type="file" name="attachment" id="waiver_document" class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">Upload relevant documentation (e.g., scholarship letter,
                                        income certificate).</small>
                                    <div id="waiverDocumentError" class="invalid-feedback"></div>
                                </div>

                                <!-- Remarks -->
                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Additional Remarks</label>
                                    <textarea name="remarks" id="remarks" rows="3" class="form-control"
                                        placeholder="Feel free to share any context or notes that may support your request."
                                        aria-describedby="remarksError"></textarea>
                                    <div id="remarksError" class="invalid-feedback"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Family Details -->
                <h5 class="bg-primary text-white p-2">Family Details</h5>
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label for="fathers_name" class="form-label">Father's Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="fathers_name" id="fathers_name" class="form-control"
                            aria-describedby="fathersNameError" required>
                        <div id="fatherNameError" class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mothers_name" class="form-label">Mother's Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="mothers_name" id="mothers_name" class="form-control"
                            aria-describedby="mothersNameError" required>
                        <div id="motherNameError" class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="parent_contact" class="form-label">Parent's Contact Number</label>
                        <input type="text" name="parent_contact" id="parent_contact" class="form-control"
                            pattern="[0-9]{10}" aria-describedby="parent_contactError">
                        <div id="parent_contactError" class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact Number <span
                                class="text-danger">*</span></label>
                        <input type="text" name="emergency_contact" id="emergency_contact" class="form-control"
                            pattern="[0-9]{10}" aria-describedby="emergency_contactError" required>
                        <div id="emergency_contactError" class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="local_guardian_name" class="form-label">Local Guardian Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="local_guardian_name" id="local_guardian_name" class="form-control"
                            aria-describedby="localGuardianNameError" required>
                        <div id="localGuardianNameError" class="invalid-feedback"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="guardian_contact" class="form-label">Local Guardian's Contact Number</label>
                        <input type="text" name="guardian_contact" id="guardian_contact" class="form-control"
                            pattern="[0-9]{10}" aria-describedby="guardian_contactError" required>
                        <div id="guardian_contactError" class="invalid-feedback"></div>
                    </div>



                    <!-- Preferences -->
                    <h5 class="bg-primary text-white p-2">Preferences</h5>
                    <div class="mb-3">
                        <label class="form-label">Food Preference <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food_preference" id="food_veg"
                                    value="Veg" required>
                                <label class="form-check-label" for="food_veg">Veg</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="food_preference" id="food_nonveg"
                                    value="Non-Veg">
                                <label class="form-check-label" for="food_nonveg">Non-Veg</label>
                            </div>
                        </div>
                        <div id="foodPreferenceError" class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bed Preference <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="room_preference" id="room_single"
                                    value="Single" required>
                                <label class="form-check-label" for="room_single">Single</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="room_preference" id="room_double"
                                    value="Double">
                                <label class="form-check-label" for="room_double">Double</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="room_preference" id="room_triple"
                                    value="Triple">
                                <label class="form-check-label" for="room_triple">Triple</label>
                            </div>
                        </div>
                        <div id="bedPreferenceError" class="invalid-feedback"></div>
                    </div>

                    <!-- Stay Duration -->
                    <div class="mb-3">
                        <label for="months" class="form-label">Stay Duration <span class="text-danger">*</span></label>
                        <select name="months" id="months" class="form-select" required
                            aria-describedby="stayDurationError">
                            <option value="">Select Type</option>
                            <option value="1">Temporary (1 Month)</option>
                            <option value="3">Regular (3 Months)</option>
                        </select>
                        <div id="stayDurationError" class="invalid-feedback"></div>
                    </div>

                    {{-- <!-- Accessories -->
                    <div class="mb-3">
                        <label class="form-label">Free Accessories</label>
                        <div class="border border-gray-300 p-4 rounded bg-light" id="default-accessories">
                            <p class="text-muted">Loading free accessories...</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Accessories (Optional)</label>
                        <div class="border p-3" id="additional-accessories">
                            <p class="text-muted">Loading additional accessories...</p>
                        </div>
                    </div> --}}

                    <!-- Complimentary Accessories -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Complimentary Accessories</label>
                        <div class="border rounded p-3 bg-light d-flex" id="default-accessories">
                            <p class="text-muted mb-0">Fetching complimentary accessories...</p>
                        </div>
                    </div>

                    <!-- Optional Add-ons -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Optional Add-on Accessories</label>
                        <div class="border rounded p-3 bg-light d-flex flex-wrap gap-3" id="additional-accessories">
                            di
                            <p class="text-muted mb-0">Fetching add-on accessories...</p>
                        </div>
                    </div>


                    <!-- Agreement -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agree" required>
                        <label class="form-check-label" for="agree">
                            I agree to the <a href="/terms-and-conditions" target="_blank">terms and conditions</a> <span
                                class="text-danger">*</span>
                        </label>
                        <div id="agreeError" class="invalid-feedback"></div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                        Register
                    </button>
                    <div id="loading" class="mt-3 text-center text-muted d-none">Submitting...</div>
                </div>
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- <script type="text/javascript">
        $(document).ready(function() {
            let defaultAccessoryHeadIds = [];
            let allAccessories = [];

            function displayError(field, message) {
                $('#' + field + 'Error').text(message);
                $('[name="' + field + '"]').addClass('is-invalid');
            }

            function clearError(field) {
                $('#' + field + 'Error').text('');
                $('[name="' + field + '"]').removeClass('is-invalid');
            }

            function toggleConditionalFields() {
                if ($('#fee_waiver').is(':checked')) {
                    $('#remarksFieldGroup').show();
                    $('#remarks').prop('required', true);
                    $('#remarksRequiredAsterisk').text('*');

                    $('#waiverDocumentFieldGroup').show();
                } else {
                    $('#remarksFieldGroup').hide();
                    $('#remarks').prop('required', false).val('');
                    clearError('remarks');
                    $('#remarksRequiredAsterisk').text('');

                    $('#waiverDocumentFieldGroup').hide();
                    $('#waiver_document').val('');
                    clearError('waiver_document');
                }
            }

            function checkFormValidity() {
                let valid = true;

                $('#registrationForm [required]').each(function() {
                    if (!this.checkValidity()) {
                        valid = false;
                    }
                });

                if (!$('input[name="food_preference"]:checked').length) valid = false;
                if (!$('input[name="room_preference"]:checked').length) valid = false;
                if (!$('#agree').is(':checked')) valid = false;

                $('#submitBtn').prop('disabled', !valid);
            }

            $('#fee_waiver').change(toggleConditionalFields);

            $('#registrationForm input, #registrationForm select, #registrationForm textarea').on(
                'input blur change',
                function() {
                    clearError(this.name);
                    checkFormValidity();
                });

            $('#agree').change(checkFormValidity);

            checkFormValidity();
            toggleConditionalFields();
            // Load accessories
            $.getJSON('/api/accessories/active', function(data) {
                allAccessories = data.data;
                let defaultHTML = '',
                    additionalHTML = '';

                if (allAccessories.length === 0) {
                    defaultHTML = '<p>No free accessories available.</p>';
                    additionalHTML = '<p>No additional accessories available.</p>';
                } else {
                    $.each(allAccessories, function(i, acc) {
                        if (parseFloat(acc.price) === 0) {
                            defaultAccessoryHeadIds.push(acc.accessory_head.id);
                            defaultHTML +=
                                `<div  class="text-gray-700 py-1">${acc.accessory_head.name}</div>`;
                        } else {
                            additionalHTML += `<div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${acc.accessory_head.id}" name="accessories[]" id="accessory-${acc.accessory_head.id}">
                        <label class="form-check-label" for="accessory-${acc.accessory_head.id}">
                            ${acc.accessory_head.name} (${parseFloat(acc.price).toFixed(2)} INR)
                        </label>
                    </div>`;
                        }
                    });
                }

                $('#default-accessories').html(defaultHTML);
                $('#additional-accessories').html(additionalHTML);
            }).fail(function() {
                $('#errorMessageText').text('Error loading accessories.');
                $('#errorMessage').removeClass('hidden');
            });

            $('#registrationForm').submit(function(e) {
                e.preventDefault();
                $('#errorMessage').addClass('hidden');
                $('.is-invalid').removeClass('is-invalid');

                let valid = true;

                $('#registrationForm [required]').each(function() {
                    if (!this.checkValidity()) {
                        displayError(this.name, this.validationMessage);
                        valid = false;
                    }
                });

                if (!$('input[name="food_preference"]:checked').length) {
                    displayError('food_preference', 'Select food preference.');
                    valid = false;
                }

                if (!$('input[name="room_preference"]:checked').length) {
                    displayError('room_preference', 'Select bed preference.');
                    valid = false;
                }

                if (!$('#agree').is(':checked')) {
                    displayError('agree', 'You must agree to the terms.');
                    valid = false;
                }

                if (!valid) return;

                let formData = new FormData(this);

                $.each(defaultAccessoryHeadIds, function(i, id) {
                    formData.append('accessory_head_ids[]', id);
                });

                $('input[name="accessories[]"]:checked').each(function() {
                    formData.append('accessory_head_ids[]', $(this).val());
                });

                formData.set('fee_waiver', $('#fee_waiver').is(':checked') ? '1' : '0');
                formData.set('remarks', $('#remarks').val().trim());

                if ($('#fee_waiver').is(':checked') && $('#waiver_document')[0].files.length > 0) {
                    formData.append('attachment', $('#waiver_document')[0].files[0]);
                }

                $('#submitBtn').prop('disabled', true);
                $('#loading').removeClass('hidden');

                $.ajax({
                    url: '/api/guests',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#loading').addClass('hidden');
                        if (response.success) {
                            $('#registrationForm').hide();
                            $('#registrationSuccessContainer').removeClass('hidden');
                            setTimeout(() => {
                                window.location.href = '/guest?status=success';
                            }, 3000);
                        } else {
                            $('#errorMessageText').text(response.message ||
                                'Registration failed.');
                            $('#errorMessage').removeClass('hidden');
                        }
                    },
                    error: function(xhr) {
                        $('#loading').addClass('hidden');
                        $('#errorMessage').removeClass('hidden');
                        let response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function(field, msgs) {
                                displayError(field, msgs[0]);
                            });
                        } else {
                            $('#errorMessageText').text('An error occurred. Try again.');
                        }
                    }
                });
            });



        });
    </script> --}}

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            const defaultAccessoryHeadIds = [];
            let allAccessories = [];

            // Utility: Show error message
            function displayError(field, message) {
                const $error = $('#' + field + 'Error');
                const $input = $('[name="' + field + '"]');
                $error.text(message).show();
                $input.addClass('is-invalid');
            }

            // Utility: Clear error message
            function clearError(field) {
                const $error = $('#' + field + 'Error');
                const $input = $('[name="' + field + '"]');
                $error.text('').hide();
                $input.removeClass('is-invalid');
            }

            // Toggle conditional fields based on fee waiver
            function toggleConditionalFields() {
                const isWaiverChecked = $('#fee_waiver').is(':checked');
                $('#remarksFieldGroup, #waiverDocumentFieldGroup').toggle(isWaiverChecked);
                $('#remarks').prop('required', isWaiverChecked);
                $('#remarksRequiredAsterisk').text(isWaiverChecked ? '*' : '');
                if (!isWaiverChecked) {
                    $('#remarks').val('');
                    $('#waiver_document').val('');
                    clearError('remarks');
                    clearError('waiver_document');
                }
            }

            // Validate form inputs
            function checkFormValidity() {
                let valid = true;

                $('#registrationForm [required]').each(function() {
                    if (!this.checkValidity()) valid = false;
                });

                if (!$('input[name="food_preference"]:checked').length) valid = false;
                if (!$('input[name="room_preference"]:checked').length) valid = false;
                if (!$('#agree').is(':checked')) valid = false;

                $('#submitBtn').prop('disabled', !valid);
            }

            // Load accessories from API
            // function loadAccessories() {
            //     $.getJSON('/api/accessories/active')
            //         .done(function(data) {
            //             allAccessories = data.data || [];
            //             let defaultHTML = '',
            //                 additionalHTML = '';

            //             if (allAccessories.length === 0) {
            //                 defaultHTML = '<p>No free accessories available.</p>';
            //                 additionalHTML = '<p>No additional accessories available.</p>';
            //             } else {
            //                 allAccessories.forEach(acc => {
            //                     const headName = acc.accessory_head.name;
            //                     const headId = acc.accessory_head.id;
            //                     const price = parseFloat(acc.price);

            //                     if (price === 0) {
            //                         defaultAccessoryHeadIds.push(headId);
            //                         defaultHTML += `<div class="text-gray-700 py-1">${headName}</div>`;
            //                     } else {
            //                         additionalHTML += `
        //                     <div class="form-check">
        //                         <input class="form-check-input" type="checkbox" value="${headId}" name="accessories[]" id="accessory-${headId}">
        //                         <label class="form-check-label" for="accessory-${headId}">
        //                             ${headName} (${price.toFixed(2)} INR)
        //                         </label>
        //                     </div>`;
            //                     }
            //                 });
            //             }

            //             $('#default-accessories').html(defaultHTML);
            //             $('#additional-accessories').html(additionalHTML);
            //         })
            //         .fail(function() {
            //             $('#errorMessageText').text('Unable to load accessories. Please try again later.');
            //             $('#errorMessage').removeClass('hidden');
            //         });
            // }

            // function loadAccessories() {
            //     $.getJSON('/api/accessories/active')
            //         .done(function(data) {
            //             const allAccessories = data.data || [];
            //             let defaultHTML = '',
            //                 additionalHTML = '';

            //             if (allAccessories.length === 0) {
            //                 defaultHTML = '<p>No free accessories available.</p>';
            //                 additionalHTML = '<p>No additional accessories available.</p>';
            //             } else {
            //                 allAccessories.forEach(acc => {
            //                     const headName = acc.name;
            //                     const headId = acc.id;
            //                     const price = parseFloat(acc.default_price);
            //                     const isPaid = acc.is_paid;

            //                     if (!isPaid || price === 0) {
            //                         defaultAccessoryHeadIds.push(headId);
            //                         defaultHTML += `<div class="text-gray-700 py-1">${headName}</div>`;
            //                     } else {
            //                         additionalHTML += `
        //             <div class="form-check">
        //                 <input class="form-check-input" type="checkbox" value="${headId}" name="accessories[]" id="accessory-${headId}">
        //                 <label class="form-check-label" for="accessory-${headId}">
        //                     ${headName} (${price.toFixed(2)} INR)
        //                 </label>
        //             </div>`;
            //                     }
            //                 });
            //             }

            //             $('#default-accessories').html(defaultHTML);
            //             $('#additional-accessories').html(additionalHTML);
            //         })
            //         .fail(function() {
            //             $('#errorMessageText').text('Unable to load accessories. Please try again later.');
            //             $('#errorMessage').removeClass('hidden');
            //         });
            // }

            function loadAccessories() {
                $.getJSON('/api/accessories/active')
                    .done(function(data) {
                        const allAccessories = data.data || [];
                        let defaultHTML = '',
                            additionalHTML = '';

                        // if (allAccessories.length === 0) {
                        //     defaultHTML = '<p class="text-muted">No free accessories available.</p>';
                        //     additionalHTML = '<p class="text-muted">No additional accessories available.</p>';
                        // } else {
                        //     allAccessories.forEach(acc => {
                        //         const headName = acc.name;
                        //         const headId = acc.id;
                        //         const price = parseFloat(acc.default_price);
                        //         const isPaid = acc.is_paid;
                        //         const billingCycle = acc.billing_cycle;

                        //         // Format billing cycle label
                        //         const cycleLabel = billingCycle === 'monthly' ? 'Per Month' :
                        //             billingCycle === 'yearly' ? 'Per Year' :
                        //             billingCycle === 'one_time' ? 'One-Time' :
                        //             billingCycle;

                        //         const badgeColor = billingCycle === 'monthly' ? 'primary' :
                        //             billingCycle === 'yearly' ? 'warning' :
                        //             billingCycle === 'one_time' ? 'success' : 'secondary';

                        //         const billingBadge =
                        //             `<span class="badge bg-${badgeColor} ms-2">${cycleLabel}</span>`;

                        //         if (!isPaid || price === 0) {
                        //             defaultAccessoryHeadIds.push(headId);
                        //             defaultHTML += `
                    //     <div class="border rounded p-2 mb-2 bg-white shadow-sm">
                    //         <strong>${headName}</strong> ${billingBadge}
                    //     </div>`;
                        //         } else {
                        //             additionalHTML += `
                    //     <div class="form-check border rounded p-3 mb-2 bg-white shadow-sm">
                    //         <input class="form-check-input" type="checkbox" value="${headId}" name="accessories[]" id="accessory-${headId}">
                    //         <label class="form-check-label" for="accessory-${headId}">
                    //             <strong>${headName}</strong> (${price.toFixed(2)} INR) ${billingBadge}
                    //         </label>
                    //     </div>`;
                        //         }
                        //     });
                        // }
                        if (allAccessories.length === 0) {
                            defaultHTML = '<p>No free accessories available.</p>';
                            additionalHTML = '<p>No additional accessories available.</p>';
                        } else {
                            $.each(allAccessories, function(i, acc) {
                                if (parseFloat(acc.price) === 0) {
                                    defaultAccessoryHeadIds.push(acc.accessory_head.id);

                                    defaultHTML += `
                                        <div class="border rounded p-2 mb-2 bg-white shadow-sm">
                                            <strong>${acc.accessory_head.name}</strong>
                                        </div>`;
                                } else {

                                    additionalHTML += `
                                    <div class="form-check border rounded p-3 mb-1 bg-white shadow-sm flex-grow-1" style=margin-right:20px;>
                                        <input class="form-check-input" type="checkbox" value="${acc.accessory_head.id}" name="accessories[]" id="accessory-${acc.accessory_head.id}">
                                        <label class="form-check-label" for="accessory-${acc.accessory_head.id}">
                                            <strong>${acc.accessory_head.name}</strong> (${parseFloat(acc.price).toFixed(2)} INR)
                                        </label>
                                    </div>`;
                                }
                            });
                        }

                        $('#default-accessories').html(defaultHTML);
                        $('#additional-accessories').html(additionalHTML);
                    })
                    .fail(function() {
                        $('#errorMessageText').text('Unable to load accessories. Please try again later.');
                        $('#errorMessage').removeClass('hidden');
                    });
            }


            // function loadAccessories() {
            //     $.getJSON('/api/accessories/active')
            //         .done(function(data) {
            //             const allAccessories = data.data || [];
            //             let defaultHTML = '',
            //                 additionalHTML = '';

            //             if (allAccessories.length === 0) {
            //                 defaultHTML = '<p class="text-muted">No free accessories available.</p>';
            //                 additionalHTML = '<p class="text-muted">No additional accessories available.</p>';
            //             } else {
            //                 allAccessories.forEach(acc => {
            //                     const headName = acc.name;
            //                     const headId = acc.id;
            //                     const price = parseFloat(acc.default_price);
            //                     const isPaid = acc.is_paid;
            //                     const billingCycle = acc.billing_cycle;

            //                     const cycleLabel = billingCycle === 'monthly' ? 'Per Month' :
            //                         billingCycle === 'yearly' ? 'Per Year' :
            //                         billingCycle === 'one_time' ? 'One-Time' :
            //                         billingCycle;

            //                     const badgeColor = billingCycle === 'monthly' ? 'primary' :
            //                         billingCycle === 'yearly' ? 'warning' :
            //                         billingCycle === 'one_time' ? 'success' : 'secondary';

            //                     const billingBadge =
            //                         `<span class="badge bg-${badgeColor} ms-2">${cycleLabel}</span>`;

            //                     if (!isPaid || price === 0) {
            //                         defaultAccessoryHeadIds.push(headId);
            //                         defaultHTML += `
        //                             <div class="border rounded p-2 mb-2 ml-4 bg-white shadow-sm d-flex align-items-center gap-2 flex-wrap">
        //                                 ${headName} ${billingBadge}
        //                             </div>`;

            //                     } else {
            //                         additionalHTML = '<div class="d-flex flex-wrap gap-3">';
            //                         allAccessories.forEach(acc => {
            //                             if (acc.is_paid && parseFloat(acc.default_price) > 0) {
            //                                 additionalHTML += `
        //                                     <div style="flex: 1 1 calc(33.333% - 1rem); min-width: 250px;">
        //                                         <div class="form-check border rounded p-3 shadow-sm d-flex flex-column gap-2">
        //                                             <div class="d-inline-flex align-items-center gap-3 flex-wrap">
        //                                                 <input class="form-check-input" type="checkbox" value="${acc.id}" name="accessories[]" id="accessory-${acc.id}">
        //                                                 <label class="form-check-label mb-0" for="accessory-${acc.id}" style="flex: 1;">
        //                                                     ${acc.name} (${parseFloat(acc.default_price).toFixed(2)} INR) ${billingBadge}
        //                                                 </label>
        //                                             </div>
        //                                         </div>
        //                                     </div>`;
            //                             }
            //                         });
            //                         additionalHTML += '</div>';
            //                     }
            //                 });
            //             }

            //             $('#default-accessories').html(defaultHTML);
            //             $('#additional-accessories').html(additionalHTML);
            //         })
            //         .fail(function() {
            //             $('#errorMessageText').text('Unable to load accessories. Please try again later.');
            //             $('#errorMessage').removeClass('hidden');
            //         });
            // }




            // Submit form with validation and AJAX
            $('#registrationForm').submit(function(e) {
                e.preventDefault();
                $('#errorMessage').addClass('hidden');
                $('.is-invalid').removeClass('is-invalid');

                let valid = true;

                $('#registrationForm [required]').each(function() {
                    if (!this.checkValidity()) {
                        displayError(this.name, this.validationMessage);
                        valid = false;
                    }
                });

                if (!$('input[name="food_preference"]:checked').length) {
                    displayError('food_preference', 'Please select your food preference.');
                    valid = false;
                }

                if (!$('input[name="room_preference"]:checked').length) {
                    displayError('room_preference', 'Please select your room preference.');
                    valid = false;
                }

                if (!$('#agree').is(':checked')) {
                    displayError('agree', 'You must agree to the terms and conditions.');
                    valid = false;
                }

                if (!valid) return;

                const formData = new FormData(this);

                defaultAccessoryHeadIds.forEach(id => {
                    formData.append('accessory_head_ids[]', id);
                });

                $('input[name="accessories[]"]:checked').each(function() {
                    formData.append('accessory_head_ids[]', $(this).val());
                });

                formData.set('fee_waiver', $('#fee_waiver').is(':checked') ? '1' : '0');
                formData.set('remarks', $('#remarks').val().trim());

                const waiverFile = $('#waiver_document')[0].files[0];
                if ($('#fee_waiver').is(':checked') && waiverFile) {
                    formData.append('attachment', waiverFile);
                }

                $('#submitBtn').prop('disabled', true);
                $('#loading').removeClass('hidden');

                $.ajax({
                    url: '/api/guests',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    // success: function(response) {
                    //     $('#loading').addClass('hidden');
                    //     if (response.success) {
                    //         $('#registrationForm').hide();
                    //         $('#registrationSuccessContainer').removeClass('hidden');
                    //         setTimeout(() => {
                    //             window.location.href = '/guest?status=success';
                    //         }, 3000);
                    //     } else {
                    //         $('#errorMessageText').text(response.message ||
                    //             'Registration failed.');
                    //         $('#errorMessage').removeClass('hidden');
                    //     }
                    // },
                    // error: function(xhr) {
                    //     $('#loading').addClass('hidden');
                    //     $('#errorMessage').removeClass('hidden');
                    //     const response = xhr.responseJSON;
                    //     if (response && response.errors) {
                    //         Object.entries(response.errors).forEach(([field, msgs]) => {
                    //             displayError(field, msgs[0]);
                    //         });
                    //     } else {
                    //         $('#errorMessageText').text(
                    //             'An unexpected error occurred. Please try again.');
                    //     }
                    // }

                    success: function(response) {
                        $('#loading').addClass('hidden');
                        console.log('Success Response:', response); // Log to console

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Registration Successful',
                                text: 'You will be redirected shortly.',
                                timer: 3000,
                                showConfirmButton: false
                            });

                            $('#registrationForm').hide();
                            $('#registrationSuccessContainer').removeClass('hidden');

                            setTimeout(() => {
                                window.location.href = '/guest/registration-status';
                            }, 3000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Registration Failed',
                                text: response.message ||
                                    'Something went wrong. Please try again.'
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#loading').addClass('hidden');
                        console.log('Error Response:', xhr); // Log to console

                        const response = xhr.responseJSON;
                        let errorMessage = 'An unexpected error occurred. Please try again.';

                        if (response && response.errors) {
                            errorMessage = Object.values(response.errors).map(msgs => msgs[0])
                                .join('\n');
                            Object.entries(response.errors).forEach(([field, msgs]) => {
                                displayError(field, msgs[0]);
                            });
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Submission Error',
                            text: errorMessage
                        });
                    }

                });
            });

            // Initial setup
            $('#fee_waiver').change(toggleConditionalFields);
            $('#registrationForm input, #registrationForm select, #registrationForm textarea').on(
                'input blur change',
                function() {
                    clearError(this.name);
                    checkFormValidity();
                });
            $('#agree').change(checkFormValidity);

            checkFormValidity();
            toggleConditionalFields();
            loadAccessories();
        });
    </script>


    <script>
        $('#faculty').on('change', function() {
            const facultyId = $(this).val();
            $('#department').html('<option value="">Select Department </option>');
            $('#course').html('<option value="">Select Course</option>');

            if (facultyId) {
                $.get(`/departments/${facultyId}`, function(departments) {
                    if (departments.length > 0) {
                        departments.forEach(dept => {
                            $('#department').append(
                                `<option value="${dept.id}">${dept.name}</option>`);
                        });
                    } else {
                        // No departments — load courses directly under faculty
                        $.get(`/courses/faculty/${facultyId}`, function(courses) {
                            if (courses.length > 0) {
                                courses.forEach(course => {
                                    $('#course').append(
                                        `<option value="${course.id}">${course.name}</option>`
                                    );
                                });
                            } else {
                                $('#course').append(
                                    `<option value="">No courses available</option>`);
                            }
                        });
                    }
                });
            }
        });

        $('#department').on('change', function() {
            const departmentId = $(this).val();
            $('#course').html('<option value="">Select Course</option>');

            if (departmentId) {
                $.get(`/courses/department/${departmentId}`, function(courses) {
                    if (courses.length > 0) {
                        courses.forEach(course => {
                            $('#course').append(
                                `<option value="${course.id}">${course.name}</option>`);
                        });
                    } else {
                        $('#course').append(`<option value="">No courses available</option>`);
                    }
                });
            }
        });
    </script>
@endsection

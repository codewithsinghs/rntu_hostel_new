<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        /* Style for required asterisk */
        .required {
            color: #ef4444;
            /* Red color for required indicator */
        }
    </style>
</head>

<body class="flex items-center justify-center min-h-screen p-4">
    <div class="container mx-auto max-w-3xl">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Guest Registration</h2>

        <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline" id="errorMessageText">Something went wrong. Please try again.</span>
        </div>

        <div id="registrationSuccessContainer" class="hidden bg-white p-8 rounded-lg shadow-xl text-center">
            <div class="text-green-600 text-2xl font-semibold mb-4">Guest registered successfully!</div>
            <div class="text-green-700 text-3xl font-bold">Thank you!</div>
        </div>

        <div id="approvalMessageContainer" class="hidden bg-blue-100 border border-blue-400 text-blue-700 px-6 py-4 rounded-lg text-center text-xl font-medium shadow-md">
            Your registration is awaiting admin approval. Keep checking for updates.
        </div>

        <form id="registrationForm" class="bg-white p-8 rounded-lg shadow-xl">
            @csrf

            <div class="bg-blue-600 text-white p-3 rounded-t-lg text-center font-semibold text-lg mb-6 shadow-md">
                Personal Details
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                <div>
                    <label for="name" class="block text-gray-700 text-sm font-medium mb-1">Full Name <span class="required">*</span></label>
                    <input type="text" name="name" id="name" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required>
                    <div id="nameError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-1">Email <span class="required">*</span></label>
                    <input type="email" name="email" id="email" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required>
                    <div id="emailError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="scholar_no" class="block text-gray-700 text-sm font-medium mb-1">Scholar Number <span class="required">*</span></label>
                    <input type="text" name="scholar_no" id="scholar_no" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required pattern="[a-zA-Z0-9]+" title="Scholar number must contain only letters and digits (e.g., SCHO123).">
                    <div id="scholarNoError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="gender" class="block text-gray-700 text-sm font-medium mb-1">Gender <span class="required">*</span></label>
                    <select name="gender" id="gender" class="form-select w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <div id="genderError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                <div class="col-span-1">
                    <label for="fee_waiver" class="block text-gray-700 text-sm font-medium mb-1">Fee Waiver</label>
                    <div class="flex items-center">
                        <input type="checkbox" name="fee_waiver" id="fee_waiver" value="1" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="fee_waiver" class="ml-2 text-gray-700">Apply Fee Waiver</label>
                    </div>
                    <div id="feeWaiverError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div class="col-span-1" id="waiverDocumentFieldGroup">
                    <label for="waiver_document" class="block text-gray-700 text-sm font-medium mb-1">Fee Waiver Document <span class="" id="waiverDocumentRequiredAsterisk"></span></label>
                    <input type="file" name="attachment" id="waiver_document" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500">
                    <div id="waiverDocumentError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div class="col-span-1" id="remarksFieldGroup">
                    <label for="remarks" class="block text-gray-700 text-sm font-medium mb-1">Remarks <span class="required" id="remarksRequiredAsterisk"></span></label>
                    <textarea name="remarks" id="remarks" rows="3" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500"></textarea>
                    <div id="remarksError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
            </div>
            <div class="bg-blue-600 text-white p-3 rounded-t-lg text-center font-semibold text-lg mb-6 shadow-md">
                Family Details
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mb-6">
                <div>
                    <label for="fathers_name" class="block text-gray-700 text-sm font-medium mb-1">Father's Name <span class="required">*</span></label>
                    <input type="text" name="fathers_name" id="fathers_name" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required>
                    <div id="fathersNameError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="mothers_name" class="block text-gray-700 text-sm font-medium mb-1">Mother's Name <span class="required">*</span></label>
                    <input type="text" name="mothers_name" id="mothers_name" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required>
                    <div id="mothersNameError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div class="md:col-span-2">
                    <label for="local_guardian_name" class="block text-gray-700 text-sm font-medium mb-1">Local Guardian Name (Optional)</label>
                    <input type="text" name="local_guardian_name" id="local_guardian_name" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500">
                    <div id="localGuardianNameError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="emergency_no" class="block text-gray-700 text-sm font-medium mb-1">Emergency Contact Number <span class="required">*</span></label>
                    <input type="text" name="emergency_no" id="emergency_no" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required pattern="[0-9]{10}" title="Emergency contact number must be 10 digits.">
                    <div id="emergencyNoError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="number" class="block text-gray-700 text-sm font-medium mb-1">Your Contact Number (Optional)</label>
                    <input type="text" name="number" id="number" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" pattern="[0-9]{10}" title="Contact number must be 10 digits.">
                    <div id="numberError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="parent_no" class="block text-gray-700 text-sm font-medium mb-1">Parent's Contact Number (Optional)</label>
                    <input type="text" name="parent_no" id="parent_no" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" pattern="[0-9]{10}" title="Parent's contact number must be 10 digits.">
                    <div id="parentNoError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
                <div>
                    <label for="guardian_no" class="block text-gray-700 text-sm font-medium mb-1">Local Guardian's Contact Number (Optional)</label>
                    <input type="text" name="guardian_no" id="guardian_no" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" pattern="[0-9]{10}" title="Local Guardian's contact number must be 10 digits.">
                    <div id="guardianNoError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
                </div>
            </div>

            <div class="bg-blue-600 text-white p-3 rounded-t-lg text-center font-semibold text-lg mb-6 shadow-md">
                Preferences
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Food Preference <span class="required">*</span></label>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center">
                        <input type="radio" name="food_preference" value="Veg" id="food_veg" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" required>
                        <label for="food_veg" class="ml-2 text-gray-700">Veg</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="food_preference" value="Non-Veg" id="food_nonveg" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="food_nonveg" class="ml-2 text-gray-700">Non-Veg</label>
                    </div>
                </div>
                <div id="foodPreferenceError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Bed Preference <span class="required">*</span></label>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center">
                        <input type="radio" name="room_preference" value="Single" id="room_single" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" required>
                        <label for="room_single" class="ml-2 text-gray-700">Single</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="room_preference" value="Double" id="room_double" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="room_double" class="ml-2 text-gray-700">Double</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" name="room_preference" value="Triple" id="room_triple" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="room_triple" class="ml-2 text-gray-700">Triple</label>
                    </div>
                </div>
                <div id="bedPreferenceError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
            </div>
            <div class="mb-6">
                <label for="months" class="block text-gray-700 text-sm font-medium mb-1">Stay Duration <span class="required">*</span></label>
                <select name="months" id="months" class="form-select w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200 focus:border-blue-500" required>
                    <option value="">-- Select Type --</option>
                    <option value="1">Temporary (1 Month)</option>
                    <option value="3">Regular (3 Months)</option>
                </select>
                <div id="stayDurationError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
            </div>

            <div class="bg-blue-600 text-white p-3 rounded-t-lg text-center font-semibold text-lg mb-6 shadow-md">
                Accessories
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Free Accessories</label>
                <div class="border border-gray-300 p-4 rounded-md bg-gray-50" id="default-accessories">
                    <p class="text-gray-500">Loading free accessories...</p>
                </div>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-medium mb-2">Additional Accessories (Optional)</label>
                <div class="border border-gray-300 p-4 rounded-md bg-gray-50" id="additional-accessories">
                    <p class="text-gray-500">Loading additional accessories...</p>
                </div>
            </div>

            <div class="flex items-start mb-6">
                <input type="checkbox" class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" id="agree" required>
                <label for="agree" class="ml-2 text-gray-700 text-sm">
                    I agree to the <a href="/terms-and-conditions" target="_blank" class="text-blue-600 hover:underline">terms and conditions</a> <span class="required">*</span>
                </label>
                <div id="agreeError" class="invalid-feedback text-red-500 text-xs mt-1"></div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-300 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed" id="submitBtn" disabled>
                Register
            </button>
            <div id="loading" class="mt-4 text-center text-gray-500 hidden">Submitting...</div>
        </form>
    </div>

    <script>
        let defaultAccessoryHeadIds = [];
        let allAccessories = [];
        const errorMessageDiv = document.getElementById("errorMessage");
        const errorMessageText = document.getElementById("errorMessageText");
        const registrationForm = document.getElementById("registrationForm");
        const approvalMessageContainer = document.getElementById("approvalMessageContainer");
        const submitBtn = document.getElementById("submitBtn");
        const loadingDiv = document.getElementById("loading");
        const registrationSuccessContainer = document.getElementById("registrationSuccessContainer");
        const defaultAccessoriesDiv = document.getElementById("default-accessories");
        const additionalAccessoriesDiv = document.getElementById("additional-accessories");
        const agreeCheckbox = document.getElementById("agree");

        const feeWaiverCheckbox = document.getElementById('fee_waiver');
        const remarksFieldGroup = document.getElementById('remarksFieldGroup');
        const remarksTextarea = document.getElementById('remarks');
        const remarksRequiredAsterisk = document.getElementById('remarksRequiredAsterisk');
        const waiverDocumentFieldGroup = document.getElementById('waiverDocumentFieldGroup');
        const waiverDocumentInput = document.getElementById('waiver_document');
        const waiverDocumentRequiredAsterisk = document.getElementById('waiverDocumentRequiredAsterisk');

        // New error message references for optional phone numbers
        const errorMessages = {
            name: document.getElementById('nameError'),
            email: document.getElementById('emailError'),
            scholar_no: document.getElementById('scholarNoError'),
            gender: document.getElementById('genderError'),
            fathers_name: document.getElementById('fathersNameError'),
            mothers_name: document.getElementById('mothersNameError'),
            local_guardian_name: document.getElementById('localGuardianNameError'),
            emergency_no: document.getElementById('emergencyNoError'),
            number: document.getElementById('numberError'), // New
            parent_no: document.getElementById('parentNoError'), // New
            guardian_no: document.getElementById('guardianNoError'), // New
            food_preference: document.getElementById('foodPreferenceError'),
            room_preference: document.getElementById('bedPreferenceError'),
            months: document.getElementById('stayDurationError'),
            agree: document.getElementById('agreeError'),
            fee_waiver: document.getElementById('feeWaiverError'),
            remarks: document.getElementById('remarksError'),
            waiver_document: document.getElementById('waiverDocumentError')
        };

        function displayErrorMessage(fieldName, message) {
            const errorDiv = errorMessages[fieldName];
            if (errorDiv) {
                errorDiv.textContent = message;
                const inputElements = registrationForm.querySelectorAll(`[name="${fieldName}"]`);
                if (inputElements.length > 0) {
                    if (inputElements[0].type === 'radio' || inputElements[0].type === 'checkbox' || inputElements[0].type === 'file') {
                        inputElements.forEach(input => input.classList.add('is-invalid'));
                    } else {
                        inputElements[0].classList.add('is-invalid');
                    }
                } else {
                    const specificElement = document.getElementById(fieldName);
                    if (specificElement) specificElement.classList.add('is-invalid');
                }
            }
        }

        function clearErrorMessage(fieldName) {
            const errorDiv = errorMessages[fieldName];
            if (errorDiv) {
                errorDiv.textContent = '';
                const inputElements = registrationForm.querySelectorAll(`[name="${fieldName}"]`);
                if (inputElements.length > 0) {
                    if (inputElements[0].type === 'radio' || inputElements[0].type === 'checkbox' || inputElements[0].type === 'file') {
                        inputElements.forEach(input => input.classList.remove('is-invalid'));
                    } else {
                        inputElements[0].classList.remove('is-invalid');
                    }
                } else {
                    const specificElement = document.getElementById(fieldName);
                    if (specificElement) specificElement.classList.remove('is-invalid');
                }
            }
        }

        function toggleConditionalFields() {
            if (feeWaiverCheckbox.checked) {
                remarksFieldGroup.classList.remove('hidden');
                remarksTextarea.required = true;
                remarksRequiredAsterisk.textContent = '*';

                waiverDocumentFieldGroup.classList.remove('hidden');
                waiverDocumentInput.required = false; // Waiver document is not strictly required here
                waiverDocumentRequiredAsterisk.textContent = '';
            } else {
                remarksFieldGroup.classList.add('hidden');
                remarksTextarea.required = false;
                remarksTextarea.value = '';
                clearErrorMessage('remarks');
                remarksRequiredAsterisk.textContent = '';

                waiverDocumentFieldGroup.classList.add('hidden');
                waiverDocumentInput.required = false;
                waiverDocumentInput.value = '';
                clearErrorMessage('waiver_document');
                waiverDocumentRequiredAsterisk.textContent = '';
            }
            checkFormValidity();
        }

        function checkFormValidity() {
            let allValid = true;

            const requiredElements = registrationForm.querySelectorAll('input[required]:not([type="radio"]):not([type="checkbox"]):not([type="file"]), select[required], textarea[required]');
            requiredElements.forEach(element => {
                if (!element.checkValidity()) allValid = false;
            });

            // Check validity for optional number fields if they have values
            const optionalNumberFields = ['number', 'parent_no', 'guardian_no'];
            optionalNumberFields.forEach(fieldName => {
                const inputElement = document.getElementById(fieldName);
                if (inputElement && inputElement.value.trim() !== '' && !inputElement.checkValidity()) {
                    allValid = false;
                }
            });


            const foodPreferenceChecked = document.querySelector('input[name="food_preference"]:checked');
            if (!foodPreferenceChecked) allValid = false;

            const roomPreferenceChecked = document.querySelector('input[name="room_preference"]:checked');
            if (!roomPreferenceChecked) allValid = false;

            if (!agreeCheckbox.checked) allValid = false;

            if (feeWaiverCheckbox.checked) {
                if (remarksTextarea.required && remarksTextarea.value.trim() === '') allValid = false;
            }

            submitBtn.disabled = !allValid;
        }

        document.addEventListener("DOMContentLoaded", function() {
            remarksFieldGroup.classList.add('hidden');
            remarksTextarea.required = false;
            remarksRequiredAsterisk.textContent = '';

            waiverDocumentFieldGroup.classList.add('hidden');
            waiverDocumentInput.required = false;
            waiverDocumentRequiredAsterisk.textContent = '';

            // Ensure optional fields are not marked as required by default
            const localGuardianNameInput = document.getElementById('local_guardian_name');
            if (localGuardianNameInput) localGuardianNameInput.removeAttribute('required');
            // No asterisk for localGuardianName, as it's not required in HTML

            const numberInput = document.getElementById('number');
            if (numberInput) numberInput.removeAttribute('required');

            const parentNoInput = document.getElementById('parent_no');
            if (parentNoInput) parentNoInput.removeAttribute('required');

            const guardianNoInput = document.getElementById('guardian_no');
            if (guardianNoInput) guardianNoInput.removeAttribute('required');

            feeWaiverCheckbox.addEventListener('change', toggleConditionalFields);

            const generalFormElements = registrationForm.querySelectorAll('input:not([type="radio"]):not([type="checkbox"]):not([type="file"]), select, textarea');
            generalFormElements.forEach(element => {
                element.addEventListener('input', () => {
                    // For optional number fields, only validate if they have a value
                    if (['number', 'parent_no', 'guardian_no'].includes(element.name) && element.value.trim() === '') {
                        clearErrorMessage(element.name);
                    } else if (element.checkValidity()) {
                        clearErrorMessage(element.name);
                    }
                    checkFormValidity();
                });
                element.addEventListener('blur', () => {
                    // For optional number fields, only validate if they have a value
                    if (['number', 'parent_no', 'guardian_no'].includes(element.name) && element.value.trim() === '') {
                        clearErrorMessage(element.name);
                    } else if (!element.checkValidity()) {
                        displayErrorMessage(element.name, element.validationMessage || 'This field is required.');
                    } else {
                        clearErrorMessage(element.name);
                    }
                    checkFormValidity();
                });
            });

            remarksTextarea.addEventListener('input', () => {
                if (remarksTextarea.required) {
                    if (remarksTextarea.value.trim() === '') {
                        displayErrorMessage('remarks', 'Remarks are required when fee waiver is applied.');
                    } else {
                        clearErrorMessage('remarks');
                    }
                } else {
                    clearErrorMessage('remarks');
                }
                checkFormValidity();
            });

            remarksTextarea.addEventListener('blur', () => {
                if (remarksTextarea.required) {
                    if (remarksTextarea.value.trim() === '') {
                        displayErrorMessage('remarks', 'Remarks are required when fee waiver is applied.');
                    } else {
                        clearErrorMessage('remarks');
                    }
                } else {
                    clearErrorMessage('remarks');
                }
                checkFormValidity();
            });

            waiverDocumentInput.addEventListener('change', () => {
                if (waiverDocumentInput.required) {
                    if (waiverDocumentInput.files.length === 0) {
                        displayErrorMessage('waiver_document', 'A document is required when fee waiver is applied.');
                    } else {
                        clearErrorMessage('waiver_document');
                    }
                } else {
                    clearErrorMessage('waiver_document');
                }
                checkFormValidity();
            });

            waiverDocumentInput.addEventListener('blur', () => {
                if (waiverDocumentInput.required) {
                    if (waiverDocumentInput.files.length === 0) {
                        displayErrorMessage('waiver_document', 'A document is required when fee waiver is applied.');
                    } else {
                        clearErrorMessage('waiver_document');
                    }
                } else {
                    clearErrorMessage('waiver_document');
                }
                checkFormValidity();
            });

            const foodRadios = document.querySelectorAll('input[name="food_preference"]');
            foodRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    clearErrorMessage('food_preference');
                    checkFormValidity();
                });
                radio.addEventListener('blur', () => {
                    if (!document.querySelector('input[name="food_preference"]:checked')) {
                        displayErrorMessage('food_preference', 'Please select your food preference.');
                    }
                    checkFormValidity();
                });
            });

            const roomRadios = document.querySelectorAll('input[name="room_preference"]');
            roomRadios.forEach(radio => {
                radio.addEventListener('change', () => {
                    clearErrorMessage('room_preference');
                    checkFormValidity();
                });
                radio.addEventListener('blur', () => {
                    if (!document.querySelector('input[name="room_preference"]:checked')) {
                        displayErrorMessage('room_preference', 'Please select your bed preference.');
                    }
                    checkFormValidity();
                });
            });

            agreeCheckbox.addEventListener("change", () => {
                if (agreeCheckbox.checked) {
                    clearErrorMessage('agree');
                } else {
                    displayErrorMessage('agree', 'You must agree to the terms and conditions.');
                }
                checkFormValidity();
            });

            agreeCheckbox.addEventListener("blur", () => {
                if (!agreeCheckbox.checked) {
                    displayErrorMessage('agree', 'You must agree to the terms and conditions.');
                }
                checkFormValidity();
            });

            checkFormValidity();

            const urlParams = new URLSearchParams(window.location.search);
            const showApprovalMessage = urlParams.get('status') === 'success';

            if (showApprovalMessage) {
                registrationForm.classList.add('hidden');
                registrationSuccessContainer.classList.add('hidden');
                approvalMessageContainer.classList.remove('hidden');
                setTimeout(() => {
                    approvalMessageContainer.classList.add('hidden');
                }, 6000);
            } else {
                fetch("{{ url('api/accessories/active') }}")
                    .then(res => {
                        if (!res.ok) {
                            console.error(`Error fetching accessories: ${res.status} - ${res.statusText}`);
                            errorMessageText.textContent = `Failed to load accessories: ${res.statusText}`;
                            errorMessageDiv.classList.remove("hidden");
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        allAccessories = data.data;
                        let defaultAccessoriesHTML = '';
                        let additionalAccessoriesHTML = '';

                        if (allAccessories.length === 0) {
                            defaultAccessoriesHTML = '<p class="text-gray-500">No free accessories available.</p>';
                            additionalAccessoriesHTML = '<p class="text-gray-500">No additional accessories available.</p>';
                        } else {
                            allAccessories.forEach(accessory => {
                                const price = parseFloat(accessory.price);
                                if (price === 0) {
                                    defaultAccessoryHeadIds.push(accessory.accessory_head.id);
                                    defaultAccessoriesHTML += `
                                <div class="text-gray-700 py-1">${accessory.accessory_head.name}</div>
                            `;
                                } else {
                                    additionalAccessoriesHTML += `
                                <div class="flex items-center mb-2">
                                    <input class="form-check-input h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" value="${accessory.accessory_head.id}" name="accessories[]" id="accessory-${accessory.accessory_head.id}">
                                    <label class="ml-2 text-gray-700" for="accessory-${accessory.accessory_head.id}">${accessory.accessory_head.name} (${price.toFixed(2)} INR)</label>
                                </div>
                            `;
                                }
                            });
                        }

                        defaultAccessoriesDiv.innerHTML = defaultAccessoriesHTML;
                        additionalAccessoriesDiv.innerHTML = additionalAccessoriesHTML;
                    })
                    .catch(error => {
                        console.error("Error loading accessories:", error);
                        errorMessageText.textContent = "Error loading accessories. Please try again.";
                        errorMessageDiv.classList.remove("hidden");
                    });
            }

            registrationForm.addEventListener("submit", function(event) {
                event.preventDefault();

                Object.keys(errorMessages).forEach(clearErrorMessage);
                errorMessageDiv.classList.add("hidden");

                let isValid = true;
                const requiredFieldsOnSubmit = registrationForm.querySelectorAll('input[required]:not([type="radio"]):not([type="checkbox"]):not([type="file"]), select[required], textarea[required]');
                requiredFieldsOnSubmit.forEach(field => {
                    if (!field.checkValidity()) {
                        isValid = false;
                        displayErrorMessage(field.name, field.validationMessage || 'This field is required.');
                    }
                });

                // Validate optional number fields on submit if they have values
                const optionalNumberFields = ['number', 'parent_no', 'guardian_no'];
                optionalNumberFields.forEach(fieldName => {
                    const inputElement = document.getElementById(fieldName);
                    if (inputElement && inputElement.value.trim() !== '' && !inputElement.checkValidity()) {
                        isValid = false;
                        displayErrorMessage(fieldName, inputElement.validationMessage || 'Please enter a valid 10-digit number.');
                    }
                });

                const foodPreference = document.querySelector('input[name="food_preference"]:checked');
                if (!foodPreference) {
                    isValid = false;
                    displayErrorMessage('food_preference', 'Please select your food preference.');
                }

                const bedPreference = document.querySelector('input[name="room_preference"]:checked');
                if (!bedPreference) {
                    isValid = false;
                    displayErrorMessage('room_preference', 'Please select your bed preference.');
                }

                if (!agreeCheckbox.checked) {
                    isValid = false;
                    displayErrorMessage('agree', 'You must agree to the terms and conditions.');
                }

                if (feeWaiverCheckbox.checked) {
                    if (remarksTextarea.value.trim() === '') {
                        isValid = false;
                        displayErrorMessage('remarks', 'Remarks are required when fee waiver is applied.');
                    }
                }

                if (!isValid) {
                    submitBtn.disabled = false;
                    loadingDiv.classList.add("hidden");
                    return;
                }

                submitBtn.disabled = true;
                loadingDiv.classList.remove("hidden");
                registrationForm.classList.remove('hidden');
                registrationSuccessContainer.classList.add('hidden');
                approvalMessageContainer.classList.add('hidden');

                const formData = new FormData(this);

                const allAccessoryIds = [...defaultAccessoryHeadIds, ...Array.from(this.querySelectorAll('input[name="accessories[]"]:checked')).map(cb => cb.value)];

                if (formData.has('accessory_head_ids')) {
                    formData.delete('accessory_head_ids');
                }

                allAccessoryIds.forEach(id => {
                    formData.append('accessory_head_ids[]', id);
                });

                formData.set('fee_waiver', feeWaiverCheckbox.checked ? '1' : '0');
                formData.set('remarks', remarksTextarea.value.trim());

                if (feeWaiverCheckbox.checked && waiverDocumentInput.files.length > 0) {
                    formData.append('attachment', waiverDocumentInput.files[0]);
                } else if (!feeWaiverCheckbox.checked) {
                    formData.delete('attachment');
                }

                fetch("{{ url('/api/guests') }}", {
                        method: "POST",
                        headers: {
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => {
                        if (!res.ok) {
                            console.error(`Error submitting form: ${res.status} - ${res.statusText}`);
                            return res.json().then(errData => {
                                throw new Error(JSON.stringify(errData));
                            });
                        }
                        return res.json();
                    })
                    .then(data => {
                        loadingDiv.classList.add("hidden");
                        if (data.success) {
                            registrationForm.classList.add('hidden');
                            registrationSuccessContainer.classList.remove('hidden');
                            setTimeout(() => {
                                window.location.href = "{{ url('/guest?status=success') }}";
                            }, 3000);
                        } else {
                            console.error("Registration failed:", data);
                            errorMessageText.textContent = data.message || "Registration failed. Please check your inputs.";
                            errorMessageDiv.classList.remove("hidden");
                            submitBtn.disabled = false;
                            if (data.errors) {
                                for (const key in data.errors) {
                                    displayErrorMessage(key, data.errors[key][0]);
                                }
                            }
                        }
                    })
                    .catch(error => {
                        loadingDiv.classList.add("hidden");
                        errorMessageDiv.classList.remove("hidden");
                        console.error("Error during registration (catch block):", error);
                        submitBtn.disabled = false;

                        try {
                            const errorData = JSON.parse(error.message);
                            console.error("Server error details (parsed):", errorData);
                            if (errorData.errors) {
                                for (const key in errorData.errors) {
                                    displayErrorMessage(key, errorData.errors[key][0]);
                                }
                            } else {
                                errorMessageText.textContent = errorData.message || "An unexpected error occurred. Please try again.";
                            }
                        } catch (e) {
                            errorMessageText.textContent = "An unexpected error occurred. Please try again.";
                            console.error("Could not parse error response in catch:", error);
                        }
                    });
            });
        });
    </script>

</body>

</html>
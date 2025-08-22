@extends('superadmin.layout')

@section('content')

<head>

<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<h2 class="mb-4">Create University</h2>

<div id="message" class="mt-3"></div>

<form id="createUniversityForm">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">University Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
        <div id="name-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location" required>
        <div id="location-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="state" class="form-label">State</label>
        <input type="text" class="form-control" id="state" name="state" required>
        <div id="state-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="district" class="form-label">District</label>
        <input type="text" class="form-control" id="district" name="district" required>
        <div id="district-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="pincode" class="form-label">Pincode</label>
        <input type="text" class="form-control" id="pincode" name="pincode" required>
        <div id="pincode-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address" required></textarea>
        <div id="address-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="mobile" class="form-label">Mobile</label>
        <input type="text" class="form-control" id="mobile" name="mobile" required pattern="[0-9]{10}" title="Please enter a 10-digit mobile number">
        <div id="mobile-error" class="invalid-feedback"></div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
        <div id="email-error" class="invalid-feedback"></div>
    </div>

    <button type="submit" class="btn btn-primary">Create University</button>
</form>

<script>
document.getElementById("createUniversityForm").addEventListener("submit", function(event) {
    event.preventDefault();
    const messageBox = document.getElementById("message");
    messageBox.innerHTML = ''; // Clear previous messages
    resetValidationErrors(); // Clear any previous error messages

    let formData = {
        name: document.getElementById("name").value,
        location: document.getElementById("location").value,
        state: document.getElementById("state").value,
        district: document.getElementById("district").value,
        pincode: document.getElementById("pincode").value,
        address: document.getElementById("address").value,
        mobile: document.getElementById("mobile").value,
        email: document.getElementById("email").value
    };

    fetch("{{ url('/api/universities') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(formData)
    })
    .then(async response => {
        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (error) {
            throw new Error("Invalid JSON response: " + text);
        }

        if (!response.ok) {
            throw data;
        }

        return data;
    })
    .then(data => {
        messageBox.innerHTML = `<div class="alert alert-success">✅ University created successfully!</div>`;
        document.getElementById("createUniversityForm").reset();
    })
    .catch(error => {
        console.error("Error:", error);
        if (error.errors) {
            // Laravel validation errors - enhanced to target specific fields
            displayValidationErrors(error.errors);
        } else {
            // General or server-side error
            messageBox.innerHTML = `<div class="alert alert-danger">⚠️ ${error.message || 'Unable to create university. Please try again later.'}</div>`;
        }
    });
});

function displayValidationErrors(errors) {
    for (const field in errors) {
        const errorDivId = `${field}-error`;
        const inputField = document.getElementById(field);
        const errorDiv = document.getElementById(errorDivId);

        if (inputField && errorDiv) {
            inputField.classList.add('is-invalid');
            errorDiv.textContent = errors[field].join('<br>');
        }
    }
}

function resetValidationErrors() {
    const inputFields = document.querySelectorAll('.form-control.is-invalid');
    inputFields.forEach(inputField => {
        inputField.classList.remove('is-invalid');
    });

    const errorDivs = document.querySelectorAll('.invalid-feedback');
    errorDivs.forEach(errorDiv => {
        errorDiv.textContent = '';
    });
}
</script>
@endsection

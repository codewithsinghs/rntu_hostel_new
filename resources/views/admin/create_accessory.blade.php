@extends('admin.layout')

@section('content')
<div class="container">
    <h2>Create Accessory</h2>

    <!-- Success Message Box -->
    <div id="successMessage" class="alert alert-success d-none" style="background-color: #28a745; color: white;" role="alert"></div>

    <!-- Error Message Box -->
    <div id="errorMessage" class="alert alert-danger d-none" role="alert" style="background-color: #dc3545; color: white;"></div>

    <form id="createAccessoryForm">
        <div class="mb-3">
            <label for="name" class="form-label">Accessory Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" class="form-control" id="price" name="price" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_default" name="is_default">
            <label class="form-check-label" for="is_default">Is Default</label>
        </div>

        <button type="submit" class="btn btn-success">Create Accessory</button>
    </form>
</div>

<script>
document.getElementById("is_default").addEventListener("change", function () {
    const priceInput = document.getElementById("price");
    if (this.checked) {
        priceInput.value = 0;
        priceInput.setAttribute("readonly", true);
    } else {
        priceInput.removeAttribute("readonly");
        priceInput.value = ""; // Clear value so user can type
    }
});

document.getElementById("createAccessoryForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const name = document.getElementById("name").value;
    const price = document.getElementById("price").value;
    const isDefaultChecked = document.getElementById("is_default").checked;

    const accessoryData = {
        name: name,
        price: price,
        is_default: isDefaultChecked
    };

    const apiUrl = "http://localhost:8000/api/accessories";
    const token = localStorage.getItem("auth_token");

    fetch(apiUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            ...(token && { "Authorization": `Bearer ${token}` })
        },
        body: JSON.stringify(accessoryData)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Failed to create accessory. Please try again.");
        }
        return response.json();
    })
    .then(data => {
        if (data.id) {
            document.getElementById("createAccessoryForm").reset();
            hideErrorMessage();
            showSuccessMessage("Accessory created successfully!");

            // Uncheck default and enable price input again
            document.getElementById("is_default").checked = false;
            document.getElementById("price").removeAttribute("readonly");
            document.getElementById("price").value = "";

            setTimeout(() => {
                window.location.href = "{{ route('admin.accessories') }}";
            }, 2000);
        } else {
            showErrorMessage(data.message || "Something went wrong.");
        }
    })
    .catch(error => {
        showErrorMessage("Error: " + error.message);
    });
});

function showSuccessMessage(message) {
    const box = document.getElementById("successMessage");
    box.classList.remove("d-none");
    box.textContent = message;
}

function showErrorMessage(message) {
    const box = document.getElementById("errorMessage");
    box.classList.remove("d-none");
    box.textContent = message;
}

function hideErrorMessage() {
    const box = document.getElementById("errorMessage");
    box.classList.add("d-none");
}
</script>
@endsection

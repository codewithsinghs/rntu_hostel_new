@extends('resident.layout')

@section('content')
<div class="container mt-5">
    <h2 class="mb-4">Resident Checkout Request</h2>

    <div id="response-message"></div>


            <form id="checkoutForm">
                @csrf

                <div class="mb-3">
                    <label for="date" class="form-label">Checkout Date</label>
                    <input type="date" class="form-control" id="date" name="date" required>
                </div>

                <div class="mb-3">
                    <label for="reason" class="form-label">Reason</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Checkout Request</button>
            </form>
</div>

<script>
    document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        document.getElementById('response-message').innerHTML = '<div class="alert alert-info">Submitting...</div>';

        fetch('/api/resident/checkout/request', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'token': localStorage.getItem('token'),
                'auth-id': localStorage.getItem('auth-id')
            },
            credentials: 'include'
        })
        .then(async response => {
            const contentType = response.headers.get("content-type");

            if (!contentType || !contentType.includes("application/json")) {
                throw new Error("Unexpected response format");
            }

            const data = await response.json();

            if (response.ok) {
                document.getElementById('response-message').innerHTML =
                    '<div class="alert alert-success">' + data.message + '</div>';
                form.reset();
            } else {
                document.getElementById('response-message').innerHTML =
                    '<div class="alert alert-danger">Error: ' + (data.message || 'Something went wrong') + '</div>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('response-message').innerHTML =
                '<div class="alert alert-danger">There was an error: ' + error.message + '</div>';
        });
    });
</script>
@endsection

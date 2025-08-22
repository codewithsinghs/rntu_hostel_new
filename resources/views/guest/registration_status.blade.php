@extends('layout')

@section('head')
<style>
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

</style>
@endsection
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card-header text-center">
                <h3>Registration Status</h3>
            </div>
            <div class="card-body">
                <div id="loginMessage"></div>                
                <form id="guest-login">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile Number :</label>
                        <input type="mobile" class="form-control" name="mobile" required>
                    </div>

                    <div class="d-grid w-50">
                        <button type="submit" class="btn btn-primary">Check Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-scripts')        
<script type="text/javascript">
$(document).ready(function () {
    $('#guest-login').on('submit', function (event) {
        event.preventDefault();

        const formData = $(this).serialize();

        $('#loginMessage').html('<span style="color: blue;">Checking registration status...</span>');

        $.ajax({
            url: '/api/guest/login',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // only if route is in web.php
            },
            success: function (response) {
                $('#loginMessage').html('<span style="color: green;">' + response.data.message + '</span>');

                // Store token & auth-id in localStorage
                localStorage.setItem('token', response.data.token);
                localStorage.setItem('auth-id', response.data.user.id);

                // Now check guest authentication
                $.ajax({
                    url: '/api/guest-authentication',
                    type: 'POST',
                    headers: {
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            window.location.href = "/guest/dashboard";
                        } else {
                            $('#loginMessage').append('<div class="alert alert-danger">Role not found or not authorized!</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#loginMessage').append('<div class="alert alert-danger">Error during guest authentication.</div>');
                        console.error(xhr.responseText);
                    }
                });
            },
            error: function (xhr, status, error) {
                $('#loginMessage').html('<div class="alert alert-danger">Login failed. Please check your credentials.</div>');
                console.error(xhr.responseText);
            }
        });
    });
});

</script>
@endsection
$(document).ready(function () {
    $('#loginForm').on('submit', function (e) {
        e.preventDefault(); // Stop default form submit

        let loginUrl = $('#loginBtn').data('login-url');
        $.ajax({
            url: loginUrl,
            type: "POST",
            data: {
                email: $('#email').val(),
                password: $('#password').val(),
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Only if using web routes
            },
            success: function (response) {
                if (response.success) {  // boolean check
                    $('#loginMessage').html('<span style="color: green;">' + response.data.message + '</span>');

                    // Store token in localStorage
                    localStorage.setItem('token', response.data.token);
                    localStorage.setItem('auth-id', response.data.user.id);

                    $.ajax({
                        url: '/api/authenticate-users', // your API endpoint
                        type: 'POST',
                        headers: {  
                            'token': localStorage.getItem('token'),
                            'auth-id': localStorage.getItem('auth-id')
                        },  
                    success: function (response) {
                    if (response.success && response.data && response.data.role_name) {
                        switch (response.data.role_name) {
                            case "admin":
                                window.location.href = "/admin/dashboard";
                                break;
                            case "super_admin":
                                window.location.href = "/super-admin/dashboard";
                                break;
                            case "accountant":
                                window.location.href = "/accountant/dashboard";
                                break;
                            case "warden":
                                window.location.href = "/warden/dashboard";
                                break;
                            case "security":
                                window.location.href = "/security/dashboard";
                                break;
                            case "mess_manager":
                                window.location.href = "/mess-manager/dashboard";
                                break;
                            case "gym_manager":
                                window.location.href = "/gym-manager/dashboard";
                                break;
                            case "hod":
                                window.location.href = "/hod/dashboard";
                                break;
                            case "resident":
                                window.location.href = "/resident/dashboard";
                                break;
                            default:
                                callLogoutAPI();
                        }
                    } else {
                        $('#loginMessage').append('<div class="alert alert-danger">Role not found or not authorized!</div>');   
                    }
                    }

                    });

                    // url: '/api/authenticate-users', // your API endpoint
                    // // Redirect
                    // window.location.href = "/admin/dashboard";
                } else {
                    $('#loginMessage').append( '<div class="alert alert-danger">'+
response.message || "Login failed!"+'</div>');
                }
            },
            error: function (xhr) {
                $('#loginMessage').html('<span style="color: red;">' + (xhr.responseJSON?.message || "An error occurred") + '</span>');
            }
        });

    });
});

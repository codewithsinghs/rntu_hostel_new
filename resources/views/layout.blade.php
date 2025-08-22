<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
         <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">        <!-- Styles -->
         <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        @yield('head')
    </head>
    <body class="antialiased">

        <nav class="navbar sticky-top navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Hostel Management</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="{{ url('/') }}" class="nav-link active font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('guest/registration') }}" class="nav-link ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Registration</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('guest/registration-status') }}" class="nav-link ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Registration Status</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>
                    </li>
                    <!-- <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Dropdown link
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Action</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                    </li> -->
                </ul>
                </div>
            </div>
        </nav>

        @yield('content')

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        @yield('page-scripts')
        <script type="text/javascript">
            $(document).ready(function() {
            if(localStorage.getItem('token') && localStorage.getItem('token'))
            {
                $.ajax({
                    url: '/api/authenticate-users', // your API endpoint
                    type: 'POST',
                    headers: {
                        'token': localStorage.getItem('token'),
                        'Auth-ID': localStorage.getItem('auth-id')
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
                    callLogoutAPI();
                }                    
            },
                    error: function (xhr, status, error) {
                        console.error("Error authenticating user:", error);
                        callLogoutAPI();
                    }
                    
                });


            }
            

    function callLogoutAPI() {
        $.ajax({
            url: '/api/logout',
            type: 'POST',
            headers: {
                'token': localStorage.getItem('token'),
                'Auth-ID': localStorage.getItem('auth-id')
            },
            complete: function () {
                localStorage.removeItem('token');
                localStorage.removeItem('auth-id');
                window.location.href = "/login";
            }
        });
        }
    });
        </script>

    </body>
</html>

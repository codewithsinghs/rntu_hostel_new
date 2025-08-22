<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- ✅ CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Bootstrap CSS & JS -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- {{-- You can add your custom CSS links here if any --}} -->
    <!-- {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}} -->


    <!-- Your CSS -->
    <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
    <style>
        body {
            display: flex;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: #343a40;
            color: #fff;
            padding: 15px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }

        .sidebar h4 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .sidebar hr {
            margin: 10px 15px;
            border-top: 1px solid #555;
        }

        .sidebar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            font-size: 16px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            border-radius: 5px;
        }

        .sidebar .nav-link:hover {
            background-color: #e74c3c;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: #fff;
            padding: 10px 20px;
            margin: 0 15px 20px 15px;
            width: calc(100% - 30px);
            border: none;
            border-radius: 25px;
            font-size: 16px;
            text-align: center;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            overflow-y: auto;
        }

        .topbar {
            background: #343a40;
            color: #fff;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notify{
            display: block;
            /* position: absolute; */
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>Resident Panel</h4>
        <hr>

        

        <button type="button" onClick="callLogoutAPI()" class="btn btn-danger w-100">Logout</button>

        <!-- Navigation Links -->
        <a href="{{ url('/resident/dashboard') }}" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
        <a href="{{ url('/resident/leave-request') }}" class="nav-link"><i class="fas fa-calendar-alt"></i> Leave Request</a>
        <a href="{{ url('/resident/leave_request_status') }}" class="nav-link"><i class="fas fa-hourglass-half"></i> Leave Status</a>
        <a href="{{ url('/resident/room-change') }}" class="nav-link"><i class="fas fa-exchange-alt"></i> Room Change</a>
        <a href="{{ url('/resident/room_change_status') }}" class="nav-link"><i class="fas fa-home text-warning"></i> Room Change Status</a>
        <a href="{{ route('resident.submit_grievance') }}" class="nav-link"><i class="fas fa-exclamation-circle"></i> Grievances</a>
        <a href="{{ url('/resident/grievance_status') }}" class="nav-link"><i class="fas fa-file-alt"></i> Grievance Status</a>
        <a href="{{ url('/resident/accessories') }}" class="nav-link"><i class="fas fa-tools"></i> Accessories</a>
        <a href="{{ url('/resident/payment') }}" class="nav-link"><i class="fas fa-money-bill"></i> Accessory Payments</a>
        <a href="{{ url('/resident/feedback') }}" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a>
        <a href="{{ url('/resident/notices') }}" class="nav-link"><i class="fas fa-bell"></i> Notices</a>
        <a href="{{ url('/resident/fine') }}" class="nav-link"><i class="fas fa-gavel text-danger"></i> Fine Payments</a>
      <!-- <a href="{{ url('/resident/subscription') }}" class="nav-link"><i class="fas fa-clipboard-list"></i> Subscription</a> -->
        <a href="{{ url('/resident/subscription_type') }}" class="nav-link"><i class="fas fa-list-alt"></i> Subscription List</a>
        <a href="{{ route('resident.checkout') }}" class="nav-link"><i class="fas fa-door-open"></i> Checkout</a>
        <a href="{{ route('resident.checkout.status') }}" class="nav-link"><i class="fas fa-clipboard-check"></i> Checkout Status</a>
        <a href="#" class="nav-link"><i class="fas fa-sign-out-alt"></i> Exit</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="topbar">
            <h5>Resident Dashboard</h5>
            
            <span>Welcome, Resident</span>

            <div class="notify">
                <x-notification-icon />
            </div>
        </div>

        @yield('content') <!-- Dynamic content goes here -->
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
           if(!localStorage.getItem('token') && !localStorage.getItem('token'))
           {
            callLogoutAPI();
           }
           else if(localStorage.getItem('token') && !localStorage.getItem('auth-id'))
           {
            callLogoutAPI();
           }
           else if(!localStorage.getItem('token') && localStorage.getItem('auth-id'))
           {
            callLogoutAPI();
           }
            else
            {
                $.ajax({
                    url: '/api/resident/profile', // your API endpoint
                    type: 'GET',
                    headers: {
                        'token': localStorage.getItem('token'),
                        'Auth-ID': localStorage.getItem('auth-id')
                    },
                    success: function (response) {
                        if (!response.success) {
                            callLogoutAPI();
                        }
                    },
                });
            }


        });
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

    </script>
    <!-- Scripts -->
    <!-- <script>
        document.addEventListener("DOMContentLoaded", function () {
            let currentUrl = window.location.href;
            let links = document.querySelectorAll(".nav-link");

            links.forEach(link => {
                if (link.href === currentUrl || currentUrl.includes(link.getAttribute('href'))) {
                    link.classList.add("active");
                }
            });
        });
    </script> -->

</body>

</html>

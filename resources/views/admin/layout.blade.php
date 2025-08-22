<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #2c3e50;
            color: white;
            padding-top: 20px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
            /* Allows scrolling in sidebar if content overflows */
            display: flex;
            flex-direction: column;
        }

        .sidebar h4 {
            font-size: 20px;
            color: #ecf0f1;
            text-align: left;
            /* Align text to the left */
            margin-left: 15px;
            /* Move text a little left */
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-left: 20px;
            padding-right: 20px;
        }

        .sidebar h4 .logout-btn {
            background-color: #e74c3c;
            padding: 0;
            text-align: center;
            width: 70px;
            border-radius: 30px;
            border: none;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            padding: 12px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: #34495e;
        }

        .sidebar .active {
            background-color: #1abc9c;
        }

        .content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
            overflow-y: auto;
            /* Allows scrolling in the content section */
            min-height: 100vh;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Admin Panel Section -->
        <h4>
            Admin Panel
            <!-- Logout Button -->
                <button type="button" onClick="callLogoutAPI()" class="btn btn-danger w-100">Logout</button>
        </h4>

        <!-- Navigation Links -->
        <a href="{{ route('admin.dashboard') }}" id="dashboardTab">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

    <!-- Master Management (Dropdown) -->
    <a class="d-flex justify-content-between align-items-center" 
       data-bs-toggle="collapse" href="#masterMenu" role="button" aria-expanded="false" aria-controls="masterMenu">
        <span><i class="bi bi-gear"></i> Master Management</span>
        <i class="bi bi-chevron-down"></i>
    </a>
    <div class="collapse ps-4" id="masterMenu">
        <a href="{{ route('admin.faculties') }}" id="facultiesTab">
            <i class="bi bi-building"></i> Faculties
        </a>
        <a href="{{ route('admin.departments') }}" id="roomsTab">
            <i class="bi bi-door-open"></i> Departments
        </a>
        <a href="{{ route('admin.courses') }}" id="bedsTab">
            <i class="bi bi-folder"></i> Courses
        </a>
    </div>

        
        <div class="collapse ps-4" id="requestsMenu">
            <a href="{{ route('admin.leave_requests') }}" id="leaveRequestsTab">
                <i class="bi bi-calendar-x"></i> Leave Requests
            </a>
            <a href="{{ route('admin.room_change') }}" id="roomChangesTab">
                <i class="bi bi-arrow-repeat"></i> Room Change Requests
            </a>
            <a href="{{ route('guest.pending') }}" id="pendingGuestsTab">
                <i class="bi bi-person-exclamation"></i> Pending Guests
            </a>
            <a href="{{ route('admin.pendingpayments') }}" id="pendingpayments">
                <i class="bi bi-cash-coin"></i> Pending Payments
            </a>
        </div>
        <!-- Building & Room Management -->
        <a href="{{ route('admin.building') }}" id="buildingsTab">
            <i class="bi bi-building"></i> Buildings
        </a>
        <a href="{{ route('admin.rooms') }}" id="roomsTab">
            <i class="bi bi-door-open"></i> Rooms
        </a>
        <a href="{{ route('admin.beds') }}" id="bedsTab">
            <i class="bi-bed-fill"></i> Beds
        </a>
        <a href="{{ route('admin.assignbed') }}" id="assignbedTab">
            <i class="bi bi-arrow-left-right"></i> Assign Beds
        </a>

        <!-- Resident Management -->
        <a href="{{ route('admin.residents') }}" id="residentsTab">
            <i class="bi bi-people"></i> Residents
        </a>



        <!-- Request Management -->
        <a href="{{ route('admin.leave_requests') }}" id="leaveRequestsTab">
            <i class="bi bi-calendar-x"></i> Leave Requests
        </a>
        <a href="{{ route('admin.room_change') }}" id="roomChangesTab">
            <i class="bi bi-arrow-repeat"></i> Room Change Requests
        </a>
        <a href="{{ route('guest.pending') }}" id="pendingGuestsTab">
            <i class="bi bi-person-exclamation"></i> Pending Guests
        </a>
        <a href="{{ route('admin.pendingpayments') }}" id="pendingpayments">
            <i class="bi bi-cash-coin"></i> Pending Payments
        </a>

        <!-- Assets & Services -->
        <a href="{{ route('admin.accessories') }}" id="accessoriesTab">
            <i class="bi bi-box-seam"></i> Accessories
        </a>
        <a href="{{ route('admin.staff') }}" id="staffTab">
            <i class="bi bi-person-badge"></i> Staff
        </a>

        <!-- Communication -->
        <a href="{{ route('admin.grievances') }}" id="grievancesTab">
            <i class="bi bi-exclamation-circle"></i> Grievances
        </a>
        <a href="{{ route('admin.feedbacks') }}" id="feedbackTab">
            <i class="bi bi-chat-dots"></i> Feedback
        </a>
        <a href="{{ route('admin.notices') }}" id="noticesTab">
            <i class="bi bi-megaphone"></i> Notices
        </a>
        <a href="{{ route('admin.checkout') }}" id="checkoutRequestsTab">
            <i class="bi bi-box-arrow-right"></i> Checkout Requests
        </a>
        <a href="{{ route('admin.paid.guests') }}" id="paidGuestsTab">
            <i class="bi bi-currency-rupee"></i> Paid Guests
        </a>
        <!-- Subscribe Resident -->
        <a href="{{ url('/admin/subscribe-resident') }}" id="subscribeResidentTab">
            <i class="bi bi-person-plus"></i> Subscribe Resident
        </a>
        <a href="{{ url('/admin/add-accessory') }}" id="adminSendAccessoryToResident">
            <i class="bi bi-person-plus"></i> Send Aceessory
        </a>

        <a class="nav-link" href="{{ url('/admin/fine') }}" id="adminAssignFine">
            <i class="bi bi-cash-coin"></i> Assign Fine
        </a>



    </div>


    <!-- Main Content -->
    <div class="content">
        @yield('content')
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
                    url: '/api/admin/profile', // your API endpoint
                    type: 'GET',
                    headers: {
                        'token': localStorage.getItem('token'),
                        'auth-id': localStorage.getItem('auth-id')
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
</body>

</html>
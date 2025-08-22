<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Panel</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3" style="width: 250px; height: 100vh;">
            <!-- <h4>Guest Panel</h4> -->
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
            <ul class="nav flex-column">
                <!-- <li class="nav-item">
                    <a href="{{ url('/guest') }}" class="nav-link text-white">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('guest/register') }}" class="nav-link text-white">
                        <i class="fas fa-user-plus"></i> Apply for Registration
                    </a>
                </li> -->
                <!-- <li class="nav-item">
                    <a href="{{ url('/guest/status') }}" class="nav-link text-white">
                        <i class="fas fa-tasks"></i> Application Status
                    </a>
                </li> -->
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container p-4">
            @yield('content')
        </div>
    </div>

    <!-- FontAwesome Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @yield('page-scripts')
</body>
</html>

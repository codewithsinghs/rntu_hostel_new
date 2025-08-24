@extends('layout')

@section('content')

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3>Login</h3>
                    </div>
                    <div class="card-body">
                        <div id="loginMessage"></div>
                        <!-- <form method="POST" action="{{ route('login') }}"> -->
                            <form id="loginForm" >
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" id="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" class="form-control" name="password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" id="loginBtn" data-login-url="{{ url('/api/admin/login') }}" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

{{-- page scripts --}}
@section('page-scripts')
<script src="{{asset('js/scripts/pages/login.js')}}"></script>
@endsection
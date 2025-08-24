@extends('resident.layout')

@section('content')

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Panel</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- You can add your custom CSS links here if any --}}
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> --}}

    <style>
        .notification{
            display: flex;
            align-items: center;
            justify-content: end;

        }
    </style>
</head>
<div class="container text-center mt-5 ">
    <h3>Welcome to the Resident Dashboard</h3>
    <!-- <x-notification-icon /> -->
    <p>Select an option from the resident panel to proceed.</p>
    
</div>

<div class="notification">
        
</div>


@endsection
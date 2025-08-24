@extends('layouts.app')

@section('title')
@section('content')
    <div class="contailer">
        <div class="row mx-auto">
            <div class="col-md-6">
                Test
            </div>

            {{-- <div>
                @foreach ($faculties as $faculty)
                    <h2>{{ $faculty->name }}</h2>

                    @foreach ($faculty->departments as $department)
                        <h3>Department: {{ $department->name }}</h3>

                        @foreach ($department->courses as $course)
                            <p>Course: {{ $course->title }}</p>
                        @endforeach
                    @endforeach

                    <h3>Courses directly under Faculty:</h3>
                    @foreach ($faculty->courses->whereNull('department_id') as $course)
                        <p>Course: {{ $course->title }}</p>
                    @endforeach
                @endforeach

            </div> --}}
        </div>
    </div>
@endsection

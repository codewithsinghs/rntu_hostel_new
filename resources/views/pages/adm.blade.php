@extends('layouts.app')

@section('title')
@section('content')
    <div class="contailer">
        <div class="row mx-auto">
            <div class="col-md-6">
                Test
            </div>

            <form action="{{ route('adm.store') }}" method="POST">
                @csrf

                <h2>Apply for Admission</h2>

                {{-- <label>Full Name *</label>
                <input type="text" name="full_name" required>

                <label>Email Address *</label>
                <input type="email" name="email" required>

                <label>Phone Number *</label>
                <input type="text" name="phone" required>

                <label>Faculty *</label>
                <select name="faculty_id" id="faculty" required>
                    <option value="">-- Select Faculty --</option>
                    @foreach ($faculties as $faculty)
                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                    @endforeach
                </select>

                <label>Department (optional)</label>
                <select name="department_id" id="department">
                    <option value="">-- Select Department --</option>
                    @foreach ($faculties as $faculty)
                        @foreach ($faculty->departments as $department)
                            <option value="{{ $department->id }}" data-faculty="{{ $faculty->id }}">
                                {{ $department->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>

                <label>Desired Course (optional)</label>
                <select name="course_id" id="course">
                    <option value="">-- Select Course --</option>
                    @foreach ($faculties as $faculty)
                        @foreach ($faculty->courses as $course)
                            <option value="{{ $course->id }}" data-faculty="{{ $faculty->id }}">
                                {{ $course->title }}
                            </option>
                        @endforeach
                    @endforeach
                </select> --}}
                <select id="faculty">
                    <option value="">-- Select Faculty --</option>
                    @foreach ($faculties as $faculty)
                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                    @endforeach
                </select>

                <select id="department">
                    <option value="">-- Select Department --</option>
                </select>

                <select id="course">
                    <option value="">-- Select Course --</option>
                </select>


                <label>Additional Notes</label>
                <textarea name="notes" rows="4"></textarea>

                <button type="submit">Submit Application</button>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script>
    const faculties = @json($faculties);
</script> --}}

    {{-- <script>
        const faculties = @json($faculties);
        console.log(faculties);
        const facultySelect = document.getElementById('faculty');
        const departmentWrapper = document.getElementById('department-wrapper');
        const departmentSelect = document.getElementById('department');
        const courseWrapper = document.getElementById('course-wrapper');
        const courseSelect = document.getElementById('course');

        facultySelect.addEventListener('change', function() {
            const facultyId = parseInt(this.value);
            const selectedFaculty = faculties.find(f => f.id === facultyId);

            // Reset dropdowns
            departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
            courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
            departmentWrapper.style.display = 'none';
            courseWrapper.style.display = 'none';

            if (!selectedFaculty) return;

            if (selectedFaculty.departments.length > 0) {
                // Show departments
                departmentWrapper.style.display = 'block';
                selectedFaculty.departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.name;
                    departmentSelect.appendChild(option);
                });

                // Listen for department change to show courses
                departmentSelect.addEventListener('change', function() {
                    const deptId = parseInt(this.value);
                    const department = selectedFaculty.departments.find(d => d.id === deptId);

                    courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
                    courseWrapper.style.display = 'block';

                    if (department && department.courses.length > 0) {
                        department.courses.forEach(course => {
                            const option = document.createElement('option');
                            option.value = course.id;
                            option.textContent = course.title;
                            courseSelect.appendChild(option);
                        });
                    }
                });

            } else {
                // No departments — show faculty-level courses
                courseWrapper.style.display = 'block';
                selectedFaculty.courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course.id;
                    option.textContent = course.title;
                    courseSelect.appendChild(option);
                });
            }
        });
    </script> --}}
    {{-- <script>
        $('#faculty').on('change', function() {
            const facultyId = $(this).val();
            $('#department').html('<option value="">-- Select Department --</option>');
            $('#course').html('<option value="">-- Select Course --</option>');

            if (facultyId) {
                $.get(`/departments/${facultyId}`, function(departments) {
                    departments.forEach(dept => {
                        $('#department').append(`<option value="${dept.id}">${dept.name}</option>`);
                    });
                });
            }
        });

        $('#department').on('change', function() {
            const departmentId = $(this).val();
            $('#course').html('<option value="">-- Select Course --</option>');

            if (departmentId) {
                $.get(`/courses/${departmentId}`, function(courses) {
                    courses.forEach(course => {
                        $('#course').append(`<option value="${course.id}">${course.name}</option>`);
                    });
                });
            }
        });
    </script> --}}
    <script>
        $('#faculty').on('change', function() {
            const facultyId = $(this).val();
            $('#department').html('<option value="">-- Select Department --</option>');
            $('#course').html('<option value="">-- Select Course --</option>');

            if (facultyId) {
                $.get(`/departments/${facultyId}`, function(departments) {
                    if (departments.length > 0) {
                        departments.forEach(dept => {
                            $('#department').append(
                                `<option value="${dept.id}">${dept.name}</option>`);
                        });
                    } else {
                        // No departments — load courses directly under faculty
                        $.get(`/courses/faculty/${facultyId}`, function(courses) {
                            if (courses.length > 0) {
                                courses.forEach(course => {
                                    $('#course').append(
                                        `<option value="${course.id}">${course.name}</option>`
                                        );
                                });
                            } else {
                                $('#course').append(
                                    `<option value="">No courses available</option>`);
                            }
                        });
                    }
                });
            }
        });

        $('#department').on('change', function() {
            const departmentId = $(this).val();
            $('#course').html('<option value="">-- Select Course --</option>');

            if (departmentId) {
                $.get(`/courses/department/${departmentId}`, function(courses) {
                    if (courses.length > 0) {
                        courses.forEach(course => {
                            $('#course').append(
                                `<option value="${course.id}">${course.name}</option>`);
                        });
                    } else {
                        $('#course').append(`<option value="">No courses available</option>`);
                    }
                });
            }
        });
    </script>
@endpush

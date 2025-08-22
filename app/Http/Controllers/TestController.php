<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Faculty;
use App\Models\Department;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //
    public function test()
    {
        return view('pages.test');
    }

    public function adm()
    {
        // $faculties = Faculty::with('departments', 'courses')->get();
        // dd($faculties);


        $faculties = Faculty::with('departments.courses', 'courses')->get();
        return view('pages.adm', compact('faculties'));
    }

    public function getByDepartment($departmentId)
{
    $courses = Course::where('department_id', $departmentId)->get(['id', 'name']);
    return response()->json($courses);
}

// public function getByFaculty($facultyId)
// {
//     $courses = Course::where('faculty_id', $facultyId)
//                      ->whereNull('department_id')
//                      ->get(['id', 'name']);
//     return response()->json($courses);
// }

public function getByFaculty($facultyId)
{
    $departments = Department::where('faculty_id', $facultyId)->get(['id', 'name']);
    return response()->json($departments);
}

}

<?php

namespace App\Http\Controllers;

use App\Models\Mess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'guest_id' => 'nullable|exists:guests,id',
                'resident_id' => 'nullable|exists:residents,id',
                'user_id' => 'nullable|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error.',
                    'data' => null,
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Mess::with([
                'guest',
                'resident',
                'user',
                'building',
                'university',
                'creator' => function ($query) {
                    $query->select('id', 'name', 'email');
                }
            ]);

            if ($request->filled('guest_id')) {
                $query->where('guest_id', $request->guest_id);
            }

            if ($request->filled('resident_id')) {
                $query->where('resident_id', $request->resident_id);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $messes = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Mess records fetched successfully.',
                'data' => $messes,
                'errors' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: Unable to fetch mess records.',
                'data' => null,
                'errors' => ['exception' => $e->getMessage()]
            ], 500);
        }
    }

    public function indexBlade()
    {
        $messes = Mess::with(['guest', 'resident.user'])->get();
        return view('mess.index', compact('messes'));
    }

    public function create()
    {
        return response()->json([
            'success' => true,
            'message' => 'Create form loaded (if implemented).',
            'data' => null,
            'errors' => null
        ], 200);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Store functionality not yet implemented.',
            'data' => null,
            'errors' => null
        ], 501);
    }

    public function show(Mess $mess)
    {
        return response()->json([
            'success' => true,
            'message' => 'Mess record retrieved successfully.',
            'data' => $mess->load(['guest', 'resident', 'user', 'building', 'university']),
            'errors' => null
        ], 200);
    }

    public function edit(Mess $mess)
    {
        return response()->json([
            'success' => true,
            'message' => 'Edit form data retrieved successfully.',
            'data' => $mess,
            'errors' => null
        ], 200);
    }

    public function update(Request $request, Mess $mess)
    {
        return response()->json([
            'success' => false,
            'message' => 'Update functionality not yet implemented.',
            'data' => null,
            'errors' => null
        ], 501);
    }

    public function destroy(Mess $mess)
    {
        return response()->json([
            'success' => false,
            'message' => 'Delete functionality not yet implemented.',
            'data' => null,
            'errors' => null
        ], 501);
    }
}

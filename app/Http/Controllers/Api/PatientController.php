<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    // Get list
    public function index()
    {
        $patients = Patient::with('user')->get();
        return response()->json($patients);
    }

    // Get detail
    public function show($id)
    {
        $patient = Patient::with('user')->find($id);
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }
        return response()->json($patient);
    }

    // Create
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'id_type' => 'required',
            'id_no' => 'required',
            'gender' => 'required|in:male,female',
            'dob' => 'required|date',
            'address' => 'required',
            'medium_acquisition' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'id_type' => $request->id_type,
            'id_no' => $request->id_no,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'address' => $request->address,
            'email' => fake()->unique()->safeEmail(), // Dummy email
            'password' => bcrypt('password'), // Dummy password
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'medium_acquisition' => $request->medium_acquisition,
        ]);

        return response()->json(['message' => 'Patient created', 'data' => $patient], 201);
    }

    // Update
    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $user = $patient->user;

        $user->update($request->only(['name', 'id_type', 'id_no', 'gender', 'dob', 'address']));
        $patient->update($request->only(['medium_acquisition']));

        return response()->json(['message' => 'Patient updated']);
    }

    // Delete
    public function destroy($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        $patient->user()->delete(); // delete user & cascade ke patient
        return response()->json(['message' => 'Patient deleted']);
    }
}

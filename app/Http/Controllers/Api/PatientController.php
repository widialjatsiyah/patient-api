<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    // Store new patient
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'               => 'required|string|max:255',
            'id_type'            => 'required|string|max:50',
            'id_no'              => 'required|string|max:50',
            'gender'             => 'required|in:male,female',
            'dob'                => 'required|date',
            'address'            => 'required|string',
            'medium_acquisition' => 'required|string',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ];
            return response()->json($response, 422);
        }
        
        // Create user first
        $user = User::create([
            'name'    => $request->name,
            'id_type' => $request->id_type,
            'id_no'   => $request->id_no,
            'gender'  => $request->gender,
            'dob'     => $request->dob,
            'address' => $request->address,
            'password' => bcrypt($request->dob)
        ]);

        // Create patient
        $patient = Patient::create([
            'user_id'           => $user->id,
            'medium_acquisition'=> $request->medium_acquisition,
        ]);

        $response = [
            'success' => true,
            'message' => 'Patient created successfully',
            'data'    => $patient->load('user') // include related user
        ];
        return response()->json($response, 201);
    }

    // Update patient
    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            $response = [
                'success' => false,
                'message' => 'Patient not found'
            ];
            return response()->json($response, 404);
        }

        $validator = Validator::make($request->all(), [
            'name'               => 'sometimes|string|max:255',
            'id_type'            => 'sometimes|string|max:50',
            'id_no'              => 'sometimes|string|max:50',
            'gender'             => 'sometimes|in:male,female',
            'dob'                => 'sometimes|date',
            'address'            => 'sometimes|string',
            'medium_acquisition' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ];
            return response()->json($response, 422);
        }

        // Update user
        $user = $patient->user;
        $user->update($request->only('name', 'id_type', 'id_no', 'gender', 'dob', 'address'));

        // Update patient
        $patient->update($request->only('medium_acquisition'));

        $response = [
            'success' => true,
            'message' => 'Patient updated successfully',
            'data'    => $patient->load('user')
        ];
        return response()->json($response);
    }

    // Delete patient
    public function destroy($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            $response = [
                'success' => false,
                'message' => 'Patient not found'
            ];
            return response()->json($response, 404);
        }

        $user = $patient->user;

        $patient->delete();
        $user->delete();

        $response = [
            'success' => true,
            'message' => 'Patient deleted successfully'
        ];
        return response()->json($response, 200);
    }

    // Get list of patients
    public function index()
    {
        $patients = Patient::with('user')->get();

        $response = [
            'success' => true,
            'message' => 'Patient list retrieved successfully',
            'data'    => $patients
        ];
        return response()->json($response);
    }

    // Get patient detail
    public function show($id)
    {
        $patient = Patient::with('user')->find($id);
        if (!$patient) {
            $response = [
                'success' => false,
                'message' => 'Patient not found'
            ];
            return response()->json($response, 404);
        }

        $response = [
            'success' => true,
            'message' => 'Patient details retrieved successfully',
            'data'    => $patient
        ];
        return response()->json($response);
    }
}

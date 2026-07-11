<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponse; 
use App\Models\Wallet;

class ManagerController extends Controller {
    use ApiResponse;

    public function getEmployees(Business $business) {
        $user = auth()->user();

        if (!$user->isManager() || $user->id !== $business->user_id) {
            return $this->apiResponse(null, 'This action is unauthorized.', 403);
        }

        $employees = $business->employees;
        return $this->apiResponse($employees, 'Employees fetched successfully', 200);
    }

    public function addEmployee(Request $request) {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'business_id' => 'required|exists:businesses,id',
        ]);
        $business = Business::find($data['business_id']);
       if (!$user->isManager() || $user->id !== $business->user_id) {
            return $this->apiResponse(null, 'This action is unauthorized.', 403);
        }
        $employee = User::create([
            ...$data,
            'role' => 'employee',
        ]);
        Wallet::create(['user_id' => $employee->id, 'balance' => 1000]);
        return $this->apiResponse($employee, 'Employee added successfully', 201);
    }
    public function removeEmployee(Request $request, Business $business) {
        $user = $request->user();

        if (!$user->isManager() || $user->id !== $business->user_id) {
            return $this->apiResponse(null, 'This action is unauthorized.', 403);
        }

        $employee = User::find($request->id);

        if (!$employee) {
            return $this->apiResponse(null, 'Employee not found.', 404);
        }

        $employee->delete();
        return $this->apiResponse($employee, 'Employee removed successfully', 200);
    }
}

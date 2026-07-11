<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Process;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            $users = User::all();
        } elseif ($user->isManager() ) {
            $businessIds = $user->managedBusiness()->pluck('id');
            $users = User::where('role', 'employee')
                ->whereIn('business_id', $businessIds)
                ->get();
        } else {
            $users = collect();
        }

        return $this->apiResponse($users, 'Users fetched successfully', 200);
    }
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return $this->apiResponse($user, 'User fetched successfully', 200);
    }
    public function addManager(Request $request)
    {
        $this->authorize('addManager', User::class);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);
        $user = User::create($data);
        $user->role = 'manager';
        $user->save();
        Wallet::create(['user_id' => $user->id, 'balance' => 1000]);
        return $this->apiResponse($user, 'Manager added successfully', 201);
    }

    public function diposit(Request $request)
    {
        $this->authorize('can_deposit', User::class);
        
        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'user_id' => 'required|exists:users,id',
        ]);
    
        $result = DB::transaction(function () use ($data) {
            $wallet = Wallet::where('user_id', $data['user_id'])
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $data['user_id'],
                    'balance' => 0,
                ]);
                $wallet->refresh();
            }

            $wallet->balance = (float) $wallet->balance + (float) $data['amount'];
            $wallet->save();

            $process = Process::create([
                'wallet_id' => $wallet->id,
                'type'      => 'deposit',
                'amount'    => $data['amount'],
            ]);

            return [
                'wallet'  => $wallet->fresh(),
                'process' => $process,
            ];
        });

        return $this->apiResponse($result, 'Deposited successfully', 201);
    }
    
}
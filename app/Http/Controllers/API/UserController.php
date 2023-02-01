<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // laravel foritify password validation rules
    use PasswordValidationRules;

    // Register user
    public function register(Request $request)
    {
        try {
            // validate request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users,username'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone_number' => ['nullable', 'string', 'max:255'],
                'password' => $this->passwordRules(),
            ]);

            // Create new user
            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ]);

            // get user
            $user = User::where('email', $request->email)->firstOrFail();

            // // create user token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // response json success
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered');
        } catch (\Exception $e) {

            // response json error
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 'Authentication Failed', 500);
        }
    }

    // login user
    public function login(Request $request)
    {
        try {
            // validasi request untuk login
            $request->validate([
                'email' => ['required', 'email', 'max:255'],
                'password' => $this->passwordRules()
            ]);

            // check user ada atau tidak
            if (!Auth::attempt($request->only(['email', 'password']))) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized',
                ], 'Authentication Failed', 500);
            }

            // Mengambil data user
            $user = User::firstWhere('email', $request->email);

            // check password user dengan pssword dari request
            if (!Hash::check($request->password, $user->password)) {
                throw new \Exception('Invalid Credentials');
            }

            // create user token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            // response json error
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Login Success');
        } catch (\Exception $e) {
            // response json error
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 'Authentication Failed', 500);
        }
    }

    // Get data current user
    public function fetch(Request $request)
    {
        // response user success
        return ResponseFormatter::success($request->user(), 'Data user berhasil di ambil');
    }

    // Update profile user
    public function update(Request $request)
    {
        try {
            // Validasi update user
            $request->validate([
                'name' => ['string', 'max:255'],
                'username' => ['string', 'max:255', 'unique:users,username,' . $request->user()->id],
                'email' => ['email', 'max:255', 'unique:users,email,' . $request->user()->id],
                'phone_number' => ['string', 'max:15']
            ]);

            // Update data current user
            $request->user()->update($request->all());

            // Response update user success
            return ResponseFormatter::success($request->user(), 'User berhasil di Update');
        } catch (\Exception $e) {
            // Response update user error
            return ResponseFormatter::error([
                'message' => 'Update gagal',
                'error' => $e->getMessage()
            ], 'User gagal di update');
        }
    }

    // Logout user
    public function logout(Request $request)
    {
        // delete token current user
        $request->user()->currentAccessToken()->delete();

        // response user logout success
        return ResponseFormatter::success(message: 'User berhasil logout');
    }
}

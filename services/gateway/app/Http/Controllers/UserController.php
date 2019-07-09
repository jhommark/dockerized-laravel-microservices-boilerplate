<?php
namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\User;

use Validator;

class UserController extends Controller
{
    /**
     * Login
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request) {
        $input = $request->only('email', 'password');

        $validator = Validator::make($input, [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        if (!Auth::attempt($input)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $user['access_token'] = $user->createToken(config('app.name'))->accessToken;

        return new UserResource($user);
    }

    /**
     * Register
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $input = $request->only('name', 'email', 'password', 'password_confirmation');

        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $user = User::create($input);
        $user['access_token'] = $user->createToken(config('app.name'))->accessToken;

        return new UserResource($user);
    }

    /**
     * Authenticated User
     *
     * @return \Illuminate\Http\Response
     */
    public function me()
    {
        return new UserResource(Auth::user());
    }
}

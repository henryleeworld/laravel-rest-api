<?php
   
namespace App\Http\Controllers\Api;
   
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
   
class AuthController extends BaseController
{
    /**
     * Register api
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
            'c_password' => 'required|same:password',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->string('password'),
        ]);

        return $this->sendResponse(null, __('The user registered successfully.'));
    }
   
    /**
     * Login api
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) { 
            $user = Auth::user(); 
            $success['access_token'] = $user->createToken($user->name.'-AuthToken')->plainTextToken;
            $success['name']         = $user->name;

            return $this->sendResponse($success, __('User login successfully.'));
        } 
        return $this->sendError(['error' => __('Unauthorised.')]);
    }

    /**
     * Logout api
     */
    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return $this->sendResponse(null, __('You\'re logged out!'));
    }
}
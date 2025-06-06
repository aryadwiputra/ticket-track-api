<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Validator;

class LoginController extends BaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "email" => "required",
                "password" => "required",
                'password_confirmation' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                return $this->sendError(422, 'ERROR_VALIDATION', $validator->errors());
            }

            $user = User::where("email", $request->email)->first();

            if (!$user) {
                return $this->sendError(404, 'USER_NOT_FOUND', "User not found");
            } elseif (!$user && !Hash::check($request->password, $user->password)) {
                return $this->sendError(400, 'LOGIN_FAILED', "Login failed");
            } elseif ($user && !Hash::check($request->password, $user->password)) {
                return $this->sendError(422, 'CREDENTIALS_NOT_MATCH', "Credentials doesn't match");
            } else {
                $data = [
                    'user' => UserResource::make($user),
                    'token' => $user->createToken('MyApp')->accessToken
                ];
                return $this->sendSuccess(200, 'LOGIN_SUCCESS', $data);
            }

        } catch (\Throwable $th) {
            return $this->sendError(500, "Error", $th->getMessage());
        }
    }
}

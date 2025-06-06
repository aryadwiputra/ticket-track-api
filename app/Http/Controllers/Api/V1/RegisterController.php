<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends BaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required',
                'password_confirmation' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                return $this->sendError(422, 'ERROR_VALIDATION', $validator->errors());
            }

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            User::create($input)->sendEmailVerificationNotification();

            return $this->sendSuccess(201, 'REGISTER_SUCCESS', []);
        } catch (\Exception $e) {
            return $this->sendError(500, 'SERVER_ERROR', $e->getMessage());
        }
    }
}

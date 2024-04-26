<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'email' => 'required|email:rfc,dns|max:255',
            'password' => [
                'required', 'string', 'min:8',
                // 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
        ];
    }

    public function failedValidation(Validator $validated)
    {
        throw new HttpResponseException(response()->json([
            "success" => "false",
            "message" => "Validation Error",
            "errors" => $validated->errors(),

        ]));
    }
}

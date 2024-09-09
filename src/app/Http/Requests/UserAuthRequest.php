<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserAuthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'package' => ['required', 'string', 'max:255'],
            'user_name' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'contact_no' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'email_verified_at' => ['nullable', 'date'],
            'employee_code' => ['nullable', 'integer'],
            'security_question' => ['nullable', 'string', 'max:255'],
            'security_answer' => ['nullable', 'string', 'max:255'],
            'activation_code' => ['nullable', 'string', 'max:255'],
            'is_user_blocked' => ['nullable', 'boolean'],
            'is_trial_account' => ['nullable', 'boolean'],
            'first_login' => ['nullable', 'date'],
            'user_description' => ['nullable', 'string'],
            'is_deleted' => ['nullable', 'boolean'],
            'created_user' => ['nullable', 'string', 'max:255'],
            'tenant_db_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'app_user_email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required','string','min:8',
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
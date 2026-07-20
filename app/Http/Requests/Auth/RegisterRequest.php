<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\RealEmailDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = strtolower(trim((string) $this->input('email')));
        $studentId = strtoupper(preg_replace('/\s+/', '', (string) $this->input('student_id')));
        $phone = preg_replace('/[^\d+]/', '', (string) $this->input('phone'));

        // Normalize BD mobiles: 017XXXXXXXX / +88017XXXXXXXX → +88017XXXXXXXX
        if (preg_match('/^01[3-9]\d{8}$/', $phone)) {
            $phone = '+880' . substr($phone, 1);
        } elseif (preg_match('/^8801[3-9]\d{8}$/', $phone)) {
            $phone = '+' . $phone;
        }

        $this->merge([
            'email' => $email,
            'student_id' => $studentId,
            'phone' => $phone,
            'first_name' => trim((string) $this->input('first_name')),
            'last_name' => trim((string) $this->input('last_name')),
            'university' => trim((string) $this->input('university')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\pL\s\'\-\.]+$/u'],
            'last_name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\pL\s\'\-\.]+$/u'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                'unique:users,email',
                new RealEmailDomain(),
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^\+8801[3-9]\d{8}$/',
                'unique:users,phone',
            ],
            'university' => ['required', 'string', 'min:2', 'max:255'],
            'student_id' => [
                'required',
                'string',
                'min:4',
                'max:40',
                'regex:/^[A-Z0-9\-\/]+$/',
                'unique:users,student_id',
            ],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => 'First name may only contain letters, spaces, hyphens, and apostrophes.',
            'last_name.regex' => 'Last name may only contain letters, spaces, hyphens, and apostrophes.',
            'email.unique' => 'This email is already registered. Sign in instead.',
            'phone.regex' => 'Enter a valid Bangladesh mobile number (e.g. 017XXXXXXXX).',
            'phone.unique' => 'This phone number is already linked to another account.',
            'student_id.regex' => 'Student ID may only contain letters, numbers, hyphens, and slashes.',
            'student_id.unique' => 'This student ID is already registered. One ID = one account.',
            'password.confirmed' => 'Password confirmation does not match.',
            'terms.accepted' => 'You must accept the Terms of Service to continue.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $email = $this->input('email');
            $studentId = $this->input('student_id');
            $phone = $this->input('phone');

            // Case-insensitive / normalized duplicate guards (belt + suspenders with unique rules)
            if (User::whereRaw('LOWER(email) = ?', [strtolower($email)])->exists()) {
                $validator->errors()->add('email', 'This email is already registered. Sign in instead.');
            }

            if (User::whereRaw('UPPER(REPLACE(student_id, ?, ?)) = ?', [' ', '', $studentId])->exists()) {
                $validator->errors()->add('student_id', 'This student ID is already registered. One ID = one account.');
            }

            if (User::where('phone', $phone)->exists()) {
                $validator->errors()->add('phone', 'This phone number is already linked to another account.');
            }
        });
    }
}

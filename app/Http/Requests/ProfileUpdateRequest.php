<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password; // Import Password rules

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:30', Rule::unique(User::class, 'username')->ignore($this->user()->id)],
            'email' => [
                'required',
                'string',
                'email',
                'max:256',
                Rule::unique(User::class, 'email')->ignore($this->user()->id),
            ],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,png', 'max:2048'], // 2MB max, E-016
            'remove_profile_image' => ['boolean'], // For removing the image
        ];

        // Password change rules, only if new_password is provided
        if ($this->filled('new_password')) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['new_password'] = [
                'required',
                'string',
                Password::min(8)->letters()->numbers(), // E-005
                'confirmed', // new_password_confirmation must match, E-004
            ];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // E-001: Obligātajiem laukiem jābūt aizpildītiem
            'username.required' => 'E-001: Lietotājvārds ir obligāts lauks.',
            'email.required' => 'E-001: E-pasts ir obligāts lauks.',
            'current_password.required' => 'E-001: Esošā parole ir obligāts lauks.',
            'new_password.required' => 'E-001: Jaunā parole ir obligāts lauks.',
            'new_password_confirmation.required' => 'E-001: Jaunās paroles apstiprinājums ir obligāts lauks.',
            
            // E-002: Garuma ierobežojums
            'name.max' => 'E-002: Vārds nedrīkst pārsniegt 256 simbolus.',
            'surname.max' => 'E-002: Uzvārds nedrīkst pārsniegt 256 simbolus.',
            'username.max' => 'E-002: Lietotājvārds nedrīkst pārsniegt 30 simbolus.',
            'email.max' => 'E-002: E-pasts nedrīkst pārsniegt 256 simbolus.',
            'new_password.min' => 'E-002: Parolei jābūt vismaz 8 simbolus garai.',

            // E-003: E-pasta formāts
            'email.email' => 'E-003: E-pasta formāts nav korekts.',

            // E-004: Paroles nesakrīt
            'new_password.confirmed' => 'E-004: Jaunā parole un tās apstiprinājums nesakrīt.',

            // E-005: Paroles sarežģītība
            'new_password.letters' => 'E-005: Parolei jāsatur vismaz viens burts.',
            'new_password.numbers' => 'E-005: Parolei jāsatur vismaz viens cipars.',

            // E-006: Unikalitāte
            'username.unique' => 'E-006: Lietotājvārds jau tiek izmantots.',
            'email.unique' => 'E-006: E-pasts jau tiek izmantots.',

            // E-010: Esošās paroles nepareizība (apstrādājam kontrolierī)
            'current_password.current_password' => 'E-010: Ievadītā esošā parole ir nepareiza.',

            // E-016: Attēla formāts/lielums
            'profile_image.image' => 'E-016: Profila attēlam jābūt attēla failam (JPG, PNG).',
            'profile_image.mimes' => 'E-016: Profila attēlam jābūt JPG vai PNG formātā.',
            'profile_image.max' => 'E-016: Profila attēla izmērs nedrīkst pārsniegt 2 MB.',
        ];
    }
}

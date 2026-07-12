<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveGameServerSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'server_name' => ['required', 'string', 'max:100'],
            'server_rates' => ['nullable', 'string', 'max:100'],
            'server_chronicle' => ['nullable', 'string', 'max:100'],
            'server_mode' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'server_name' => 'имя сервера',
            'server_rates' => 'рейты сервера',
            'server_chronicle' => 'хроники',
            'server_mode' => 'режим сервера',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'server_name' => trim((string) $this->input('server_name')),
            'server_rates' => trim((string) $this->input('server_rates')),
            'server_chronicle' => trim((string) $this->input('server_chronicle')),
            'server_mode' => trim((string) $this->input('server_mode')),
        ]);
    }
}

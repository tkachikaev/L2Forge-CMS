<?php

namespace App\Http\Requests\Admin;

use App\Services\Settings\SettingsImageStorage;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class SaveGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:100'],
            'site_description' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'admin_email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'logo' => [
                'nullable',
                'file',
                'max:5120',
                $this->imageRule('logo'),
            ],
            'favicon' => [
                'nullable',
                'file',
                'max:1024',
                $this->imageRule('favicon'),
            ],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'site_name' => 'название сайта',
            'site_description' => 'краткое описание',
            'timezone' => 'часовой пояс',
            'admin_email' => 'email администрации',
            'footer_text' => 'текст в подвале',
            'logo' => 'логотип',
            'favicon' => 'favicon',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'site_name' => trim((string) $this->input('site_name')),
            'site_description' => trim((string) $this->input('site_description')),
            'timezone' => trim((string) $this->input('timezone')),
            'admin_email' => trim((string) $this->input('admin_email')),
            'footer_text' => trim((string) $this->input('footer_text')),
            'remove_logo' => $this->boolean('remove_logo'),
            'remove_favicon' => $this->boolean('remove_favicon'),
        ]);
    }

    private function imageRule(string $kind): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail) use ($kind): void {
            if (! $value instanceof UploadedFile) {
                return;
            }

            $error = app(SettingsImageStorage::class)->validateUpload($value, $kind);
            if ($error !== null) {
                $fail($error);
            }
        };
    }
}

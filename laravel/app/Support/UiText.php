<?php

namespace App\Support;

use Illuminate\Support\Str;

class UiText
{
    public static function permission(?string $value): string
    {
        if (blank($value)) {
            return __('N/A');
        }

        $key = 'permission.' . Str::of($value)->lower()->replace(' ', '-');
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return __($value);
    }

    public static function role(?string $value): string
    {
        if (blank($value)) {
            return __('No Role');
        }

        if (app()->getLocale() === 'en') {
            $roleMap = [
                'Contador' => 'Accountant',
                'Gerencia' => 'Management',
            ];

            if (isset($roleMap[$value])) {
                return $roleMap[$value];
            }
        }

        $key = 'role.' . Str::of($value)->lower()->replace(' ', '-');
        $translated = __($key);

        if ($translated !== $key) {
            return $translated;
        }

        return __($value);
    }

    public static function value(?string $value, ?string $fallback = null): string
    {
        if (blank($value)) {
            return $fallback !== null ? __($fallback) : __('N/A');
        }

        return __($value);
    }
}

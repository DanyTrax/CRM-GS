<?php

namespace App\Filament\Pages\Concerns;

use App\Models\Setting;

trait PersistsSystemSettings
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $keyTypes  key => type (string|decimal|boolean|integer|json)
     */
    protected function persistSettings(array $data, array $keyTypes): void
    {
        foreach ($data as $key => $value) {
            $type = $keyTypes[$key] ?? 'string';
            Setting::set($key, $value, $type);
        }
    }
}

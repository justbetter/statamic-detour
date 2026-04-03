<?php

namespace JustBetter\Detour\Support;

use Illuminate\Support\Facades\File;
use JustBetter\Detour\Enums\QueryStringHandling;
use JustBetter\Detour\Models\DetourSetting;
use Statamic\Facades\YAML;

class DetourSettings
{
    public function defaultQueryStringHandling(): QueryStringHandling
    {
        $rawValue = $this->all()['query_string_default_handling']
            ?? config()->string('justbetter.statamic-detour.query_string_default_handling');

        return QueryStringHandling::tryFrom((string) $rawValue)
            ?? QueryStringHandling::StripCompletely;
    }

    public function defaultQueryStringStripKeys(): string
    {
        $rawValue = $this->all()['query_string_default_strip_keys']
            ?? config()->string('justbetter.statamic-detour.query_string_default_strip_keys');

        return trim((string) $rawValue);
    }

    public function update(QueryStringHandling $handling, string $stripKeys): void
    {
        if ($this->usesEloquentDriver()) {
            DetourSetting::updateOrCreate(
                ['key' => 'query_string_default_handling'],
                ['value' => $handling->value]
            );
            DetourSetting::updateOrCreate(
                ['key' => 'query_string_default_strip_keys'],
                ['value' => trim($stripKeys)]
            );

            return;
        }

        $data = $this->all();
        $data['query_string_default_handling'] = $handling->value;
        $data['query_string_default_strip_keys'] = trim($stripKeys);

        $path = config()->string('justbetter.statamic-detour.settings_path');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, YAML::dump($data));
    }

    /**
     * @return array<string, mixed>
     */
    protected function all(): array
    {
        if ($this->usesEloquentDriver()) {
            return DetourSetting::all()
                ->mapWithKeys(fn (DetourSetting $setting): array => [$setting->key => $setting->value])
                ->all();
        }

        $path = config()->string('justbetter.statamic-detour.settings_path');

        if (! File::exists($path)) {
            return [];
        }

        $data = YAML::parse(File::get($path));

        return is_array($data) ? $data : [];
    }

    protected function usesEloquentDriver(): bool
    {
        return config()->string('justbetter.statamic-detour.driver') === 'eloquent';
    }
}

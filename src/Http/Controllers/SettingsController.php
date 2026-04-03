<?php

namespace JustBetter\Detour\Http\Controllers;

use JustBetter\Detour\Enums\QueryStringHandling;
use JustBetter\Detour\Http\Requests\SettingsRequest;
use JustBetter\Detour\Support\DetourSettings;
use Statamic\Facades\CP\Toast;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SettingsController
{
    public function index(DetourSettings $settings): mixed
    {
        /** @var view-string $view */
        $view = 'statamic-detour::detours.settings.index';

        return view($view, [
            'settingsAction' => cp_route('justbetter.detours.settings.update'),
            'handlingOptions' => [
                ['label' => __('Pass through'), 'value' => 'pass_through'],
                ['label' => __('Strip completely'), 'value' => 'strip_completely'],
                ['label' => __('Strip specific keys'), 'value' => 'strip_specific_keys'],
            ],
            'queryStringDefaultHandling' => $settings->defaultQueryStringHandling()->value,
            'queryStringDefaultStripKeys' => $settings->defaultQueryStringStripKeys(),
        ]);
    }

    public function update(SettingsRequest $request, DetourSettings $settings): RedirectResponse
    {
        $settings->update(
            QueryStringHandling::from($request->string('query_string_default_handling')->value()),
            $request->string('query_string_default_strip_keys')->value()
        );

        Toast::success(__('Settings saved.'));

        return redirect()->route('statamic.cp.justbetter.detours.settings.index');
    }
}

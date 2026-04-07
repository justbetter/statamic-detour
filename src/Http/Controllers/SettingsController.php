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
            'handlingOptions' => array_map(
                fn (QueryStringHandling $handling): array => [
                    'label' => $this->labelForHandling($handling),
                    'value' => $handling->value,
                ],
                QueryStringHandling::cases()
            ),
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

    protected function labelForHandling(QueryStringHandling $handling): string
    {
        return match ($handling) {
            QueryStringHandling::PassThrough => __('Pass through'),
            QueryStringHandling::StripCompletely => __('Strip completely'),
            QueryStringHandling::StripSpecificKeys => __('Strip specific keys'),
        };
    }
}

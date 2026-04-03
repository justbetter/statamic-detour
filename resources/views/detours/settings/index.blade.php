@extends('statamic::layout')

@section('title', 'Detours - Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <ui-heading size="2xl">{{ __('Detours settings') }}</ui-heading>

    <ui-card-panel
        heading="{{ __('Default query behavior') }}"
        subheading="{{ __('Used when a detour has no query string handling mode set.') }}"
    >
        @if (isset($errors) && $errors->any())
            <ui-alert
                variant="error"
                class="mt-4"
            >
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </ui-alert>
        @endif

        @php
            $selectedHandling = old('query_string_default_handling', $queryStringDefaultHandling) ?? 'strip_completely';
            $stripKeysValue = old('query_string_default_strip_keys', $queryStringDefaultStripKeys) ?? '';
        @endphp

        <detour-settings-form
            action="{{ $settingsAction }}"
            csrf-token="{{ csrf_token() }}"
            :handling-options='@json($handlingOptions)'
            selected-handling="{{ $selectedHandling }}"
            :strip-keys='@json($stripKeysValue)'
        />
    </ui-card-panel>
</div>
@endsection

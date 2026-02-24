@extends('statamic::layout')

@section('title', __('Detours - Actions'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <ui-heading size="2xl">{{ __('Import & Export Detours') }}</ui-heading>

    <ui-card-panel
        heading="{{ __('Upload file') }}"
        subheading="{{ __('Upload a CSV to be imported.') }}"
    >
        @if (session('status'))
            <ui-alert variant="success" class="mt-4">
                {{ session('status') }}
            </ui-alert>
        @endif

        @if ($errors->any())
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

        <form method="POST" action="{{ cp_route('justbetter.detours.actions.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf

            <ui-field>
                <ui-label for="file">{{ __('Choose file') }}</ui-label>
                <ui-input
                    id="file"
                    name="file"
                    type="file"
                    required
                    class="mt-2"
                />
            </ui-field>

            <div class="flex items-center gap-3">
                <ui-button
                    type="submit"
                    variant="primary"
                    text="{{ __('Upload') }}"
                />
            </div>
        </form>
    </ui-card-panel>

    <ui-card-panel
        heading="{{ __('Export') }}"
        subheading="{{ __('Generate and download an export file.') }}"
    >
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <ui-button
                href="{{ cp_route('justbetter.detours.actions.export') }}"
                variant="primary"
                text="{{ __('Download CSV') }}"
            />
        </div>
    </ui-card-panel>
</div>
@endsection

@extends('statamic::layout')

@section('title', __('Detours - Actions'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <h1>
        {{ __('Import & Export Detours') }}
    </h1>

    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Upload file') }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Upload a CSV to be imported.')}}
        </p>
        
        @if (session('status'))
            <div class="mt-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">
                {{ session('status') }}
            </div>
        @endif
        
        @if ($errors->any())
            <div class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form method="POST" action="{{ cp_route('justbetter.detours.actions.import') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            
            <div>
                <label for="file" class="block text-sm font-medium text-gray-900">{{ __('Choose file') }}</label>
                <div class="mt-2">
                    <input
                        id="file"
                        name="file"
                        type="file"
                        class="block w-full text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-800 hover:file:bg-gray-200 rounded-md border border-gray-300 bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    {{ __('Upload') }}
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('Export') }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Generate and download an export file.') }}
        </p>
        
        <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a
                href="{{ cp_route('justbetter.detours.actions.export') }}"
                class="btn btn-primary"
            >
                {{ __('Download CSV') }}
            </a>
        </div>
</div>

</div>
@endsection

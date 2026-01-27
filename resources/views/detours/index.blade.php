@extends('statamic::layout')

@section('content')
    <div>
        <Detours
            action="{{ $action }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
            :items='@json($data)'
        ></Detours>
        <div class="mt-4">
            {{ $paginator->links('pagination::simple-tailwind') }}
        </div>
    </div>
@endsection

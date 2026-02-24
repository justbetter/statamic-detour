@extends('statamic::layout')

@section('title', 'Detours - Overview')

@section('content')
    @php
        $paginatorMeta = [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    @endphp
    <div>
        <Detours
            action="{{ $action }}"
            :blueprint='@json($blueprint)'
            :meta='@json($meta)'
            :values='@json($values)'
            :items='@json($data)'
            :paginator-meta='@json($paginatorMeta)'
            :per-page="{{ $paginator->perPage() }}"
            index-url="{{ request()->url() }}"
        ></Detours>
    </div>
@endsection

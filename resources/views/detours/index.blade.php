@extends('statamic::layout')

@section('content')
    <detours
        action="{{ $action }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        :items='@json($data->items())'
        :page="{{ $data->currentPage() }}"
        :total-pages="{{ $data->lastPage() }}"
    />
@endsection

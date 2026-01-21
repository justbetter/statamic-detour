@extends('statamic::layout')

@section('content')
    <detours
        action="{{ $action }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        :items="@js($data)"
    />
@endsection
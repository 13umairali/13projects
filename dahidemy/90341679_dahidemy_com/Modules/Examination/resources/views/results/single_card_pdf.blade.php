@extends('examination::results.print_layout')

@php $hideToolbar = true; @endphp

@section('content')
    @include('examination::results.partials.result_card_body')
@endsection

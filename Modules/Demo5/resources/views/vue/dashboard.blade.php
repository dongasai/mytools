@extends('module_dcatadmin::layouts.vue-app')

@section('title', 'Vue 仪表盘')

@push('styles')
    @vite(['Modules/Demo5/resources/js/dashboard.js'])
@endpush

@section('content')
    <div id="demo5-dashboard"></div>
@endsection
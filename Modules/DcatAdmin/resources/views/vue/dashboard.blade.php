@extends('module_dcatadmin::layouts.vue-app')

@section('title', 'Vue 仪表盘')

@push('styles')
    @vite(['Modules/DcatAdmin/resources/js/dashboard.js'])
@endpush

@section('content')
    <div id="vue-dashboard"></div>
@endsection
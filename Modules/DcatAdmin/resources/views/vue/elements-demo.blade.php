@extends('module_dcatadmin::layouts.vue-app')

@section('title', 'Element Plus 组件演示')

@push('styles')
    @vite(['Modules/DcatAdmin/resources/js/elements-demo.js'])
@endpush

@section('content')
    <div id="elements-demo"></div>
@endsection
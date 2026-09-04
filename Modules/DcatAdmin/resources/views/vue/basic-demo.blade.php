@extends('module_dcatadmin::layouts.vue-app')

@section('title', '基础组件演示')

@push('styles')
    @vite(['Modules/DcatAdmin/resources/js/basic-demo.js'])
@endpush

@section('content')
    <div id="basic-demo"></div>
@endsection
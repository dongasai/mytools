@extends('module_dcatadmin::layouts.vue-app')

@section('title', '表单组件演示')

@push('styles')
    @vite(['Modules/DcatAdmin/resources/js/form-demo.js'])
@endpush

@section('content')
    <div id="form-demo"></div>
@endsection
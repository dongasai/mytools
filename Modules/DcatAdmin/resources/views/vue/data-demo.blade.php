@extends('module_dcatadmin::layouts.vue-app')

@section('title', '数据展示演示')

@push('styles')
    @vite(['Modules/DcatAdmin/resources/js/data-demo.js'])
@endpush

@section('content')
    <div id="data-demo"></div>
@endsection
@extends('module_dcatadmin::layouts.vue-app')

@section('title', '数据库管理员工具')

@push('styles')
    @vite(['Modules/FeatureDbadmin/resources/js/app.js'])
@endpush

@section('content')
    <div id="featuredbadmin-app"></div>
@endsection
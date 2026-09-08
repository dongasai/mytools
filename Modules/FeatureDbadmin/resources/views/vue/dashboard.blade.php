@extends('module_dcatadmin::layouts.vue-app')

@section('title', '数据库管理仪表盘')

@push('styles')
    @vite(['Modules/FeatureDbadmin/resources/js/dashboard.js'])
@endpush

@section('content')
    <div id="featuredbadmin-dashboard"></div>
@endsection

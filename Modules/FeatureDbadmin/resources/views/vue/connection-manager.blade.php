@extends('module_dcatadmin::layouts.vue-app')

@section('title', '数据库连接管理')

@push('styles')
    @vite(['Modules/FeatureDbadmin/resources/js/connection-manager.js'])
@endpush

@section('content')
    <div id="featuredbadmin-connection-manager"></div>
@endsection

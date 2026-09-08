@extends('module_dcatadmin::layouts.vue-app')

@section('title', 'SQL 查询工具')

@push('styles')
    @vite(['Modules/FeatureDbadmin/resources/js/query-tool.js'])
@endpush

@section('content')
    <div id="featuredbadmin-query-tool"></div>
@endsection

@extends('module_dcatadmin::layouts.vue-app')

@section('title', '数据浏览')

@push('styles')
    @vite(['Modules/FeatureDbadmin/resources/js/data-browser.js'])
@endpush

@section('content')
    <div id="featuredbadmin-data-browser"></div>
@endsection

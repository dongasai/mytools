@extends('module_dcatadmin::layouts.vue-app')

@section('title', '表管理')

@push('styles')
    @vite(['Modules/FeatureDbadmin/resources/js/table-manager.js'])
@endpush

@section('content')
    <div id="featuredbadmin-table-manager"></div>
@endsection

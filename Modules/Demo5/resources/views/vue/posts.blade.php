@extends('module_dcatadmin::layouts.vue-app')

@section('title', 'Vue 文章管理')

@push('styles')
    @vite(['Modules/Demo5/resources/js/post-list.js'])
@endpush

@section('content')
    <div id="demo5-post-list"></div>
@endsection
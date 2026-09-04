@extends('errors::minimal')

@section('title', '请求过多')
@section('code', '429')
@section('message', '您的请求过于频繁，请稍后再试。')
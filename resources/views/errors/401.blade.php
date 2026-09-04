@extends('errors::minimal')

@section('title', '未授权')
@section('code', '401')
@section('message', '访问此资源需要身份验证。请先登录后再试。')
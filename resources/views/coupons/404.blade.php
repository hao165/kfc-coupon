@extends('layouts.master')
@section('title')
找不到 {{$slug}} 優惠券@endsection
@section('description','404 not found')
@section('keywords')
{{$slug}},404,not found @endsection

@section('content')
<div class="container">
    @php
        $nowtitle = '代碼：'.$slug;
    @endphp
    @include('frontpart.breadcrumbs', ['nowtitle' => $nowtitle])
    <div class="alert alert-success mb-5">找不到該優惠券 > _ <　！</div>
</div>
@endsection

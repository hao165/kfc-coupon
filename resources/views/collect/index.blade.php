@extends('layouts.master')
@section('title','我的收藏')
@section('description','個人收藏！')
@section('keywords','我的收藏')

@section('content')
<div class="container">
    @include('frontpart.breadcrumbs', ['nowtitle' => '我的收藏'])
    @if ($message)
        <div class="alert alert-success mb-5">{{$message}}</div>
    @endif
    <div class="row d-flex mt-4 pe-2 card-list">
        @include('coupons.partials.coupon')
    </div>
</div>

@include('coupons.partials.comment')

@endsection

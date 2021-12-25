@extends('layouts.master')
@section('title','熱門收藏榜')
@section('description','個人收藏排行榜！')
@section('keywords','會員,收藏,排行榜')

@section('content')
<div class="container">
    @include('frontpart.breadcrumbs', ['nowtitle' => '收藏榜'])
    @if ($message)
        <div class="alert alert-success mb-5">{{$message}}</div>
    @endif
    <div class="row d-flex mt-4 pe-2 card-list">
        @include('coupons.partials.coupon')
    </div>
</div>

@include('coupons.partials.comment')

@endsection

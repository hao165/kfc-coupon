@extends('layouts.master')
@section('title')
待審核 {{$coupon->title}}@endsection
@section('description')
肯德基 最新優惠券 {{$coupon->title}} ， {{$coupon->discount_percent}} 只賣 {{$coupon->new_price_name}} !! 內容物 .. @endsection
@section('image'){{asset($coupon->image_name)}}@endsection
@section('keywords')
{{$coupon->title}} @endsection

@section('content')
<div class="container">
    @php
        $nowtitle = '代碼：'.$coupon->title;
    @endphp
    @include('frontpart.breadcrumbs', ['nowtitle' => $nowtitle])
    @if (1 != $coupon->status)
    <div class="alert alert-success">
        等待審核中
        @if ($isAdmin)
        ｜ <a href="{{route('coupons.verify',['id'=>$coupon->id,'type'=>'pass'])}}">通過</a> ｜ <a href="{{route('coupons.verify',['id'=>$coupon->id,'type'=>'fail'])}}">不通過</a> ｜
        @endif
    </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-6 col-sm-12 pb-3 border rounded">
            <div class="card-body">
                <div class="card-header rounded bg-secondary bg-gradient mb-3" style="--bs-bg-opacity: .5;">
                <h5 class="mt-1 mb-2">
                    @if ($coupon->hot_level=='lv3')
                        <i class="bi bi-star-fill text-warning"></i>
                    @endif

                    <span class="mx-2">{{$coupon->new_price_name}} ({{$coupon->old_price}})</span>
                    <span class="mx-2 fw-bold text-decoration-underline">{{$coupon->title}}</span>

                    @if ($coupon->hot_level=='lv3')
                        <span class="rounded float-end bg-danger text-light p-1">
                    @elseif ($coupon->hot_level=='lv2')
                        <span class="rounded float-end bg-success text-light p-1" style="--bs-bg-opacity: .7;">
                    @else
                        <span class="rounded float-end bg-white p-1">
                    @endif
                        {{$coupon->discount_percent}}
                    </span>
                </h5>
                </div>

                <div class="card-text mb-3">
                    @if ($coupon->sub_title)
                        ({{$coupon->sub_title}})
                    @endif
                    {{$coupon->content}}</div>
                <div class="card-text">
                    <small class="text-muted">
                        {{$coupon->end_at_name}} {{$coupon->status_name}}
                    </small>
                </div>
            </div>
            <div class="sharethis-inline-reaction-buttons"></div>
            <div class="w-100 p-3" style="background-color: #eee;">
                <img src="{{asset($coupon->image_name)}}" class="card-img-bottom mx-auto d-block" alt="...">
            </div>
        </div>
    </div>
</div>
@endsection

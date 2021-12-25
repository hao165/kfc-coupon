@extends('layouts.master')
@section('title','待審核列表 < 後台管理')

@section('content')
<div class="container">
    <div class="title mb-5">
        後台管理 / 待審核列表
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">slug</th>
                <th scope="col">備註</th>
                <th scope="col">內容物</th>
                <th scope="col">售價 / 原價 / 折數</th>
                <th scope="col">時間</th>
                <th scope="col">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list as $coupon)
            <tr>
                <th scope="row">{{$coupon->slug}}</th>
                <td>{{$coupon->sub_title}}</td>
                <td>{{$coupon->content}}</td>
                <td>{{$coupon->new_price_name}} / {{$coupon->old_price}} / {{$coupon->discount_percent}}</td>
                <td>{{$coupon->start_at}}<br>{{$coupon->end_at}}</td>
                <td><a href="{{route('coupons.show',$coupon->slug)}}" target="_blank">詳情</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


@endsection

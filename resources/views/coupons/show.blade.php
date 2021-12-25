@extends('layouts.master')
@section('title')
優惠券代碼： {{$coupon->title}}@endsection
@section('description')
肯德基 最新優惠券 {{$coupon->title}} ， {{$coupon->discount_percent}} 只賣 {{$coupon->new_price_name}} !! 內容物 .. @endsection
@section('image'){{asset($coupon->image_name)}}@endsection
@section('keywords')
{{$coupon->title}} @endsection

@push('meta')
<meta name="finish-type" content="refresh" />
@endpush

@section('content')
<div id="viewDiv">
<div class="container">
    @php
        $nowtitle = '代碼：'.$coupon->title;
    @endphp
    @include('frontpart.breadcrumbs', ['nowtitle' => $nowtitle])
    <div class="row justify-content-center">
        <div class="col-md-6 col-sm-12 pb-3 border rounded">
            <div class="card-body">
                <div class="card-header rounded bg-secondary bg-gradient mb-3" style="--bs-bg-opacity: .5;">
                <h5 class="mt-1 mb-2">
                    @if ($coupon->is_hot=='lv3')
                        <i class="bi bi-star-fill text-warning"></i>
                    @endif

                    <span class="mx-2">{{$coupon->new_price_name}}</span>
                    <span class="fs-6 text-decoration-line-through">原價${{$coupon->old_price}}</span>
                    <span class="mx-4 fw-bold text-decoration-underline">{{$coupon->title}}</span>

                    @if ($coupon->is_hot=='lv3')
                        <span class="rounded float-end bg-danger text-light p-1">
                    @elseif ($coupon->is_hot=='lv2')
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
                        ({!!$coupon->sub_title!!})
                    @endif
                    {{$coupon->content}}</div>
                <div class="card-text">
                    <small class="text-muted">
                        {{$coupon->end_at_name}} {{$coupon->status_name}}
                    </small>
                </div>
            </div>
            @php
            if (in_array($coupon->slug,$collects)){
                $collect_css='bi-archive-fill';
            }else{
                $collect_css='bi-archive';
            }
            @endphp
            <div class="row my-2 mx-2">
                <a class="btn btn-outline-primary showMask" href="javascript:void(0)" onclick="collect('{{$coupon->slug}}','false')"><i class="bi {{$collect_css}} collectItem" data-collect-slug="{{$coupon->slug}}"></i>　收 藏</a>
                <a class="btn btn-outline-primary d-md-none d-sm-block mt-2" href="#commentDiv"><i class="bi "></i><i class="bi bi-chat-square-text"></i>　留 言　({{$coupon->comment_cou}})</a>
            </div>
            <div class="sharethis-inline-reaction-buttons"></div>
            <div class="w-100 p-3" style="background-color: #eee;">
                <img data-src="{{asset($coupon->image_name)}}" class="lazyload card-img-bottom mx-auto d-block" alt="...">
            </div>
        </div>
        <div id="commentDiv" class="col-md-6 col-sm-12">
            <div class="row my-4 mx-2">
                <a class="btn btn-outline-primary showMask" href="#commentDiv" onclick="reply('{{$coupon->slug}}','false')">發 表 留 言</a>
            </div>
            @foreach ($comments as $comment)
            <div class="row justify-content-center my-4 mx-2">
                <div class="col py-3 px-4 border border-4 rounded">
                    <a class="text-decoration-none btn btn-outline-dark">{{ $comment->name }}</a>
                    @if($comment->tag != 'reply')
                    <a class="text-decoration-none btn {{$comment->tag_css}}">{{$comment->tag_name}}</a>
                    @endif
                    {{ date('Y-m-d', strtotime($comment->created_at)) }}
                    <div class="mt-2">
                        {!! $comment->content !!}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@include('coupons.partials.comment')
</div>
@endsection

@push('scripts')
{{-- lazyload --}}
<script src="https://unpkg.com/lazyload@2.0.0-rc.2/lazyload.js"></script>
<script src="/js/jquery.mobile.custom.min.js?v=1"></script>
<script>
$(document).ready(function() {
    $('a[href^="#"]').on('click', function () {
        var target = $(this.hash)
        $('html,body').animate({
            scrollTop: target.offset().top - 200
        });
    });
    $('#viewDiv').one('vmouseover',
        function(){
            $.ajax({
                url: "{{ route('coupons.view_count') }}",
                type: 'get',
                data:{
                    slug : '{{$coupon->slug}}',
                    ip : '{{request()->ip()}}',
                },
            });
        });
});
function collect(slug){
    @unless (Auth::check())
    $(window).attr('location','/login');
    @else
    $.ajax({
        url: "{{ route('collect.update') }}",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        cache: false,
        type: 'post',
        data:{
            slug : slug,
        },
        success: function(data) {
            if(data.action=="add"){
                $('.collectItem[data-collect-slug=' + slug + ']').addClass('bi-archive-fill').removeClass('bi-archive');
            }
            if(data.action=="del"){
                $('.collectItem[data-collect-slug=' + slug + ']').addClass('bi-archive').removeClass('bi-archive-fill');
            }
            Swal.fire('',data.message,'success');
            hideLoading();
        },
    });
    @endunless
}
</script>
@endpush

@push('scripts-footer')
<script>$("img.lazyload").lazyload();</script>
@endpush

@extends('layouts.master')
@if($pageWord=="過期優惠券")
@section('title')
{{$pageWord}}@endsection
@section('description','過期優惠券/肯德基史上最強排行榜．熱門討論/一探究竟．一鍵篩選/上百張優惠券')
@section('keywords',date('Y年m月').',過期優惠券,排行榜')
@else
@section('title')
{{$pageWord}}@endsection
@section('description','超高折數．熱門討論．一鍵篩選。'.date('Y年m月').'肯德基最新優惠券，上百張任君挑選')
@section('keywords',date('Y年,m月').',最新優惠券,首頁')
@endif
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
@endpush

@push('scripts-footer')
<script>
    AOS.init();
</script>
@endpush

@push('scripts')
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    var tags=[];
    $(document).ready(function() {
        //選擇排序方式
        $('#sortHot').trigger('click');
        //隱藏少用btn
        $('.less-tag').hide();
        //展開更多btn
        $('#more-btn').click(function() {
            $('.less-tag').show();
            $('#more-btn').hide();
        });
        //搜尋
        $('#search').keyup(function() {
            //清除篩選按鈕
            $('.btn-check').prop("checked",false);
            $('.card-tag').hide();
            let keyword=$('#search').val();
            if (keyword) {
                $('.card-tag[data-slug^='+keyword+']').show();
            }else{
                $('.card-tag').show();
            }
            AOS.refresh();
        });
        $('.btn-check').click(function(e){
            let tag=e.target.id;
            let tag_val='';
            //清除搜尋欄
            $('#search').val('');
            let tags=$('.btn-check:checked');
            if(0 == tags.length){
                $('.card-tag').show();
            }else{
                $('.card-tag').hide();
                tags.each(function( index, target ) {
                    //點心盒轉換
                    value=target.id;
                    if(value == 'DES1'){
                        tag_val+='.FRS.CRS';
                    }else if(value == 'DES2'){
                        tag_val+='.FRS.MCN3';
                    }else{
                        tag_val+='.'+value;
                    }
                });
                $('.card-tag'+tag_val).show();
            }
            AOS.refresh();
        })
        // 取得tag hash
        if(window.location.hash) {
            let hash = window.location.hash.substring(1);
            $('#sortDiscount').trigger('click');
            setTimeout(function () {
                $('#more-btn').trigger('click');
            }, 500);
            setTimeout(function () {
                $('#'+hash).trigger('click');
            }, 1000);
            window.location.hash = '';
        }
    });
    function sortList(type,order) {
        $('.card-tag').sort(function(a,b){
            if(order=='asc') {
                return ($(b).data(type)) < ($(a).data(type)) ? 1 : -1;
            } else {
                return ($(b).data(type)) > ($(a).data(type)) ? 1 : -1;
            }
        }).appendTo('.card-list');
        AOS.refresh();
    }
</script>
@endpush

@section('content')
<div class="container px-4">
    @if($pageWord=="過期優惠券")
    @include('frontpart.breadcrumbs', ['nowtitle' => '過期優惠券'])
    @endif
    @isset($food_array_common)
    <div class="row">
        <div class="col-lg-12 mb-sm-2">
            <div class="input-group flex-nowrap mb-2">
                <span class="input-group-text" id="addon-wrapping"><i class="bi bi-search"></i></span>
                <input type="number" class="form-control col-3" id="search" min="0" max="99999" placeholder="代碼" autocomplete="off" aria-describedby="addon-wrapping">
            </div>
            @foreach ($food_array_common as $tag => $value)
                <input type="checkbox" class="btn-check" id="{{$tag}}">
                <label class="btn btn-outline-primary me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($food_array_drink as $tag => $value)
                <input type="checkbox" class="btn-check" id="{{$tag}}">
                <label class="btn btn-outline-primary me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($food_array_less as $tag => $value)
                <input type="checkbox" class="btn-check" id="{{$tag}}">
                <label class="btn btn-outline-dark less-tag me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($food_array_more as $tag => $value)
            <input type="checkbox" class="btn-check" id="{{$tag}}">
            <label class="btn btn-outline-success less-tag me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            <a id="more-btn" class="btn btn-outline-success mb-2">..更多</a>
            <a class="btn btn-outline-secondary mb-2" id="sortPrice" onclick="sortList('price','asc');">金額 低->高</a>
            <a class="btn btn-outline-secondary mb-2" id="sortDiscount" onclick="sortList('discount','asc');">折扣 高->低</a>
            <a class="btn btn-outline-secondary mb-2" id="sortHot" onclick="sortList('hot','des');">熱門 高->低</a>
            <a class="btn btn-outline-secondary mb-2" id="sortTime" onclick="sortList('datetime','des');">時間 新->舊</a>
        </div>
    </div>
    @endisset

    <div class="row d-flex mt-4 card-list">
        @include('coupons.partials.coupon')
    </div>
</div>

@include('coupons.partials.comment')

@endsection

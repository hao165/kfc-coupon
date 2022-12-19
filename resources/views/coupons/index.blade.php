@extends('layouts.master')
@if($pageTitle=="已失效優惠券")
@section('title')
{{$pageTitle}}@endsection
@section('description','已失效優惠券/肯德基史上最強排行榜．熱門討論/一探究竟．一鍵篩選/上百張優惠券')
@section('keywords',date('Y年m月').',已失效優惠券,排行榜')
@else
@section('title')
{{$pageTitle}}@endsection
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
            let tagShow='';
            let tagHide='';
            //清除搜尋欄
            $('#search').val('');
            let tags=$('.btn-check:checked');
            if(0 == tags.length){
                $('.card-tag').show();
            }else{
                $('.card-tag').hide();
                tags.each(function( index, target ) {
                    //點心盒轉換
                    value = target.id;
                    let temp  = value.split('-');
                    if(temp[1]){
                        return tagHide+=',.'+temp[1];
                    }
                    if(value == 'DES2'){
                        tagShow+='.FRS.MCN3';
                    }else if(value == 'DES1'){ // 已停用
                        tagShow+='.FRS.CRS';
                    }else{
                        tagShow+='.'+value;
                    }
                });
                console.log('show',tagShow);
                console.log('hide',tagHide);
                $('.card-tag'+tagShow).show();
                $('.hide'+tagHide).hide();
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
    @if($pageTitle=="已失效優惠券")
    @include('frontpart.breadcrumbs', ['nowtitle' => '已失效優惠券'])
    @endif
    <div class="row">
        <div class="col-lg-12 mb-sm-2">
            <div class="input-group flex-nowrap mb-2">
                <span class="input-group-text" id="addon-wrapping"><i class="bi bi-search"></i></span>
                <input type="number" class="form-control col-3" id="search" min="0" max="99999" placeholder="肯德基 {{ $pageTitle }} 代碼快速搜尋" autocomplete="off" aria-describedby="addon-wrapping">
            </div>
            @isset($foodCommon)
            @foreach ($foodCommon as $tag => $value)
                <input type="checkbox" class="btn-check" id="{{$tag}}">
                <label class="btn btn-outline-primary me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($foodDrink as $tag => $value)
                <input type="checkbox" class="btn-check" id="{{$tag}}">
                <label class="btn btn-outline-primary me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($foodLess as $tag => $value)
                <input type="checkbox" class="btn-check" id="{{$tag}}">
                <label class="btn btn-outline-dark less-tag me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($foodMore as $tag => $value)
            <input type="checkbox" class="btn-check" id="{{$tag}}">
            <label class="btn btn-outline-success less-tag me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            @foreach ($foodHide as $tag => $value)
            <input type="checkbox" class="btn-check" id="{{$tag}}">
            <label class="btn btn-outline-warning opacity-75 less-tag me-1 mb-2" for="{{$tag}}">{{$value[0]}}</label>
            @endforeach
            <a id="more-btn" class="btn btn-outline-success mb-2">..更多</a>
            @endisset

            @if($pageTitle=="已失效優惠券")
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortDiscount" onclick="sortList('discount','asc');">折扣 高->低</a>
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortHot" onclick="sortList('hot','des');">熱門 高->低</a>
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortTime" onclick="sortList('datetime','asc');">時間 舊->新</a>
            @else
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortPrice" onclick="sortList('price','asc');">金額 低->高</a>
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortDiscount" onclick="sortList('discount','asc');">折扣 高->低</a>
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortHot" onclick="sortList('hot','des');">熱門 高->低</a>
            <a class="btn btn-outline-danger opacity-75 mb-2" id="sortTime" onclick="sortList('datetime','des');">時間 新->舊</a>
            <input type="checkbox" class="btn-check" id="validity">
            <label class="btn btn-outline-danger opacity-75 mb-2" for="validity">只顯示期限內</label>
            @endif
        </div>
    </div>

    <div class="row d-flex mt-4 card-list">
        @include('coupons.partials.coupon')
    </div>

    @if($pageTitle!="已失效優惠券")
    <div class="row d-flex justify-content-center mt-4">
        <div class="col-lg-8 col-md-12">
            <div class="card mb-4">
                <div class="card-body text-center bg-success bg-opacity-25">
                    找不到原本的優惠券？　可以到 <a href="{{ route('coupons.expired') }}">已失效優惠券列表</a> 找找！
                </div>
            </div>
        </div>
    </div>
    @endif

@include('coupons.partials.comment')

@endsection

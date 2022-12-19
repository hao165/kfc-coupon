@extends('layouts.master')
@section('title','新增優惠券')
@section('description','新增優惠券')
@section('keywords','新增優惠券')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        $('#img_file').on('change', function() {
            var file_data = $('#img_file').prop('files')[0];
            var form_data = new FormData();
            form_data.append('img_file', file_data);
            $('#imageTxt').val("上傳中..");

            $.ajax({
                url: '{{route('img.imgur')}}',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                success: function(data) {
                    if (data) {
                        $('#image').val(data);
                        $('#imageTxt').val("上傳成功！");
                    } else {
                        $('#imageTxt').val("上傳失敗..");
                    }
                }
            });
        });
        $('#end_at_check').on('change', function() {
            if ($('#end_at_check').prop('checked')) {
                $('#end_at').val('2000-01-01').hide();
            }
            else{
                $('#end_at').show();
            }
        });
        $('#start_at_check').on('change', function() {
            if ($('#start_at_check').prop('checked')) {
                $('#start_at').val('2000-01-01').hide();
            }
            else{
                $('#start_at').show();
            }
        });
        $('#switch').on('change', function() {
            var inputNow='';
            if($('#switch').val()=='原價'){
                inputNow='<input type="number" class="form-control" id="old_price" name="old_price" required max="9999">';
            }else if($('#switch').val()=='折數'){
                inputNow='<input type="number" class="form-control" id="discount" name="discount" required min="10" max="99" placeholder="原價優先填寫">';
            }
            $('#switch_input').html(inputNow);
        });
    });
</script>
@endpush

@section('content')
    <div class="container">
        @include('frontpart.breadcrumbs', ['nowtitle' => '新增優惠券'])
        @if((session()->has('message'))||($errors->any()))
            <div class="alert alert-success my-2">
                {!!session('message')!!}
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </div>
        @endif
        <form class="row my-3" action="{{route('coupons.store')}}" method="post" autocomplete="off">
            {{ csrf_field() }}
            <div class="mb-3 col-md-2 col-6">
                <label for="title" class="form-label">優惠券代號</label>
                <input type="number" class="form-control" id="title" name="title" required min="10000" max="99999" value="{{ old('title') }}">
            </div>
            <div class="mb-3 col-md-2 col-6">
                <label for="sub_title" class="form-label">備註/說明</label>
                <input type="text" class="form-control" id="sub_title" name="sub_title" value="{{ old('sub_title') }}" placeholder="可空白">
            </div>
            <div class="mb-3 col-md-2">
                <label for="image" class="form-label me-4">圖片</label>
                <input id="img_file" name="img_file" type="file" hidden>
                <label class="btn btn-sm  btn-outline-secondary" for="img_file">上傳</label>
                <input type="text" class="form-control" id="image" name="image" hidden require value="{{ old('image') }}">
                <input type="text" class="form-control" id="imageTxt" require>
            </div>
            <div class="mb-3 col-md-2 col-6">
                <label for="new_price" class="form-label">售價</label>
                <input type="number" class="form-control" id="new_price" name="new_price" required max="9999" value="{{ old('new_price') }}">
            </div>
            <div class="mb-3 col-md-2 col-6">
                <select id="switch">
                    <option>請選擇</option>
                    <option>原價</option>
                    <option>折數</option>
                </select>
                <div class="mt-2" id="switch_input"></div>
            </div>
            <div class="mb-3 col-md-3 col-6">
                <label for="start_at" class="form-label">開始時間</label>　<label><input type="checkbox" id="start_at_check"> 不填寫</label>
                <input type="date" class="form-control" id="start_at" name="start_at" value="{{ old('start_at') }}">
            </div>
            <div class="mb-3 col-md-3 col-6">
                <label for="end_at" class="form-label">結束時間</label>　<label><input type="checkbox" id="end_at_check"> 無限期</label>
                <input type="date" class="form-control" id="end_at" name="end_at" value="{{ old('end_at') }}">
                {{-- min="{{date('Y-m-d')}}" --}}
            </div>
            <div class="mb-3 col-md-6">
            </div>

            @foreach ($foodArray as $key => $value)
            <div class="mb-3 col">
                <label for="{{$key}}" class="form-label" style="white-space:nowrap;">{{$value[0]}}</label><br>
                <select id="{{$key}}" name="tag[{{$key}}]" style="padding:4px;" value="{{ old('tag['.$key.']') }}">
                    @foreach (range(0,10) as $num)
                        <option>{{$num}}</option>
                    @endforeach
                </select>
            </div>
            @endforeach
            <button class="btn btn-primary">新增</button>
        </form>
    </div>
@endsection

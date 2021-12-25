@extends('layouts.master')
@section('title','爬蟲文章列表')
@section('description','爬蟲文章列表')
@section('keywords','爬蟲文章列表')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@section('content')
<div class="container">
    <div class="title mb-4">
        後台管理 / 爬蟲文章 / 列表
    </div>
    <div class="input-group flex-nowrap mb-4">
        <span class="input-group-text"><i class="bi bi-clipboard-plus"></i></span>
        <input type="text" class="form-control col-3" id="url" placeholder="Ptt網址" autocomplete="off">
        <a class="btn btn-outline-secondary showLoading" href="javascript:void(0)" onclick="addUrl()">新增</a>
    </div>
    <table class="table table-striped">
        <thead>
            <tr>
                <th scope="col">編號</th>
                <th scope="col">看板</th>
                <th scope="col">標題</th>
                <th scope="col">最後回覆</th>
                <th scope="col">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list as $item)
            <tr>
                <th scope="row">{{$item->id}}</th>
                <td>{{$item->cls}}</td>
                <td><a href="{{$item->url}}" target="_blank">{{$item->title}}</a></td>
                <td>{{$item->last_at}}</td>
                <td>
                    @if ($item->status == 1)
                    <a href="javascript:void(0)" onclick="switchStatus({{$item->id}},2)" class="btn btn-sm btn-outline-warning showLoading">停用</a>
                    @else
                    <a href="javascript:void(0)" onclick="switchStatus({{$item->id}},1)" class="btn btn-sm btn-outline-success showLoading">啟用</a>
                    @endif
                    <a href="javascript:void(0)" onclick="switchStatus({{$item->id}},3)" class="btn btn-sm btn-outline-danger showLoading">刪除</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
function addUrl(){
    let url = $('#url').val();
    $.ajax({
        url: "{{ route('admin.crawler.store') }}",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        cache: false,
        type: 'post',
        data:{
            url : url,
        },
        success: function(data) {
            hideLoading();
            if(data.success){
                $('#url').val('');
                Swal.fire({
                    icon: 'success',
                    title: '',
                    html: data.message,
                    preConfirm: () => {
                        showLoading();
                        return location.reload();
                    }
                })
                return;
            }
            return Swal.fire('',data.message,'error');
        },
    });
}

function switchStatus(id,status){
    $.ajax({
        url: "{{ route('admin.crawler.switch_status') }}",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        cache: false,
        type: 'post',
        data:{
            id : id,
            status : status,
        },
        success: function(data) {
            hideLoading();
            if(data.success){
                $('#url').val('');
                Swal.fire(
                    '',
                    data.message,
                    'success'
                );
                Swal.fire({
                    title: '',
                    html: data.message,
                    preConfirm: () => {
                        showLoading();
                        return location.reload();
                    }
                })
                return;
            }
            return Swal.fire(
                '',
                data.message,
                'error'
            );
        },
    });
}
</script>
@endpush

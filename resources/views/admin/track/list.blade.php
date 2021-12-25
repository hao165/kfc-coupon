@extends('layouts.master')
@section('title','追蹤看板列表')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@section('content')
<div class="container">
    <div class="title mb-4">
        後台管理 / 追蹤看板 / 列表
    </div>
    <div class="input-group flex-nowrap mb-4">
        <span class="input-group-text"><i class="bi bi-clipboard-plus"></i></span>
        <input type="text" class="form-control col-3" id="command" placeholder="新增 lifeismoney 50 / 刪除 lifeismoney 肯德基 (功能 看板 關鍵字/推數)" autocomplete="off">
        <a class="btn btn-outline-secondary showLoading" href="javascript:void(0)" onclick="doCls()">執行</a>
    </div>
    <table class="table table-striped">
        <thead>
            <tr class="d-flex">
                <th class="col-2">看板</th>
                <th class="col-1">頁數</th>
                <th class="col">關鍵字</th>
                <th class="col-1">推數</th>
                <th class="col-2 d-none d-sm-block">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($list as $item)
            <tr class="d-flex" id="item{{$item->id}}">
                <td class="col-2"><a href="{{$item->url}}" target="_blank">{{$item->cls}}</a></td>
                <td class="col-1"><input type="number" class="col-6 d-none d-sm-block" id="page-{{$item->cls}}" value="{{$item->page}}" max="10"><span class="d-block d-sm-none">{{$item->page}}</span></td>
                <td class="col">{{ implode(', ', $item->keyword) }}</td>
                <td class="col-1"><input type="number" class="col-8 d-none d-sm-block" id="push-{{$item->cls}}" value="{{$item->push}}" max="100"><span class="d-block d-sm-none">{{$item->push}}</span></td>
                <td class="col-2 d-none d-sm-block">
                    <a href="javascript:void(0)" onclick="editCls('{{$item->cls}}')" class="btn btn-sm btn-outline-primary showMask">編輯</a>
                    <a href="javascript:void(0)" onclick="handleCls('delAll','{{$item->cls}}')" class="btn btn-sm btn-outline-danger showMask">刪除</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
function doCls(){
    let command = $('#command').val();
    let arr = command.split(" ");
    if(arr.length!=3){
        return Swal.fire('','指令錯誤','error');
    }
    let action='';
    if(arr[0]=='新增'){
        action='add';
    }else if(arr[0]=='刪除'){
        action='del';
    }
    handleCls(action,arr[1],arr[2]);
}
function editCls(cls){
    let data = $('#page-'+cls).val();
    let data2 = $('#push-'+cls).val();
    handleCls('edit',cls,data,data2)
}

function handleCls(action,cls,data=null,data2=null){
    $.ajax({
        url: "{{ route('admin.track.store') }}",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        cache: false,
        type: 'post',
        data:{
            action : action,
            cls : cls,
            data : data,
            data2 : data2,
        },
        success: function(data) {
            hideLoading();
            if(data.success){
                $('#command').val('');
                let message = data.message || '執行成功';

                Swal.fire({
                    icon: 'success',
                    title: '',
                    html: message,
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
</script>
@endpush

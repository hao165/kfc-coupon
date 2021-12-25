@extends('layouts.master')
@section('title','爬蟲回覆列表')
@section('description','爬蟲回覆列表')
@section('keywords','爬蟲回覆列表')

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@section('content')
<div class="container">
    <div class="title mb-3">
        後台管理 / 爬蟲回覆 / 列表 <a href="javascript:void(0)" onclick="itemCheck()" class="btn btn-sm btn-outline-primary showMask">刷新</a>
    </div>
    @include('partials.pagination')
    <table class="table table-striped">
        <thead>
            <tr class="d-flex">
                <th class="col-1">編號</th>
                <th class="col-1">看板</th>
                <th class="col-2">標題</th>
                <th class="col-5">回覆</th>
                <th class="col-2">回覆時間</th>
                <th class="col-1">操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paginate['list'] as $item)
            <tr class="d-flex" id="item{{$item->id}}">
                <th class="col-1 item-id">{{$item->id}}</th>
                <td class="col-1">{{$item->crawler->cls}}</td>
                <td class="col-2"><a href="{{$item->crawler->url}}" target="_blank">{{$item->crawler->title}}</a></td>
                <td class="col-5 item-content">{{$item->ptt_id}}: {{$item->content}}</td>
                <td class="col-2 item-time">{{$item->created_at}}</td>
                <td class="col-1">
                    @if ($item->status == 0)
                    <a href="javascript:void(0)" onclick="itemPush({{$item->id}})" class="btn btn-sm btn-outline-primary showMask">編輯</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @include('partials.pagination')
</div>

<div id="commentForm" class="container position-fixed top-50 start-50 translate-middle" style="z-index:2">
    <div class="row border mx-sm-1 mx-md-3 p-3 bg-light">
        <div class="col-12 mb-2">
            <i class="bi bi-x-circle float-end" id="commentFormCls"></i>
            <div class="mb-2 fw-bold" id="oldContent"></div>
            <input type="text" name="crawlerItemId" id="crawlerItemId" class="d-none">
            <div class="mb-2 fw-bold">Slug　<label><input type="checkbox" id="commentSlug_99999"> 討論區</label> <input type="text" name="commentSlug" id="commentSlug" class="form-control"> </div>
            <div class="mb-2 fw-bold">時間　<input type="text" name="commentTime" id="commentTime" class="form-control"> </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="useTag" id="reply" value="reply" checked>
                <label class="form-check-label" for="reply">
                    一般留言
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="useTag" id="use_false" value="use_false">
                <label class="form-check-label" for="use_false">
                    @不可使用
                </label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="useTag" id="use_true" value="use_true">
                <label class="form-check-label" for="use_true">
                    @可以使用
                </label>
            </div>
        </div>
        <textarea class="form-control mb-2" id="commentContent" rows="2"></textarea>
        <a class="btn btn-primary" id="commentSubmit">送出</a>
    </div>
</div>

@push('scripts')
<script>
function itemCheck(){
    $.ajax({
        url: "{{ route('admin.crawler.item_check') }}",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        cache: false,
        type: 'post',
        success: function(data) {
            hideLoading();
            if(data.success){
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

function itemPush(id){
    let getContent = $('#item'+id+' .item-content').text();
    let slug = getContent.split(': ')[1].match(/[0-9]{5}/);
    let newContent = getContent.replace(slug,"");
    let getTime = $('#item'+id+' .item-time').text();
    $('#oldContent').text(getContent);
    $('#crawlerItemId').val(id);
    $('#commentSlug_99999').prop('checked', false);
    $('#commentSlug').val(slug);
    $('#commentTime').val(getTime);
    $('#commentContent').val(newContent);
    if(newContent.includes('不')){
        $('#use_false').prop('checked', true);
    }else if(newContent.includes('可')){
        $('#use_true').prop('checked', true);
    }
    $('#commentForm').show('fast');
}

$(document).ready(function() {
    $('#commentForm').hide();

    $('#commentFormCls').click(function(){
        commentFormHide();
    });

    $('#mask').click(function(){
        commentFormHide();
    });

    $('#commentSlug_99999').on('change', function() {
        if ($('#commentSlug_99999').prop('checked')) {
            $('#commentSlug').val('99999');
            $('#reply').prop('checked', true);
        }else{
            $('#commentSlug').val($('#oldContent').text().split(': ')[1].match(/[0-9]{5}/));
        }
    });

    $('#commentSubmit').click(function(e){
        let itemId=$("#crawlerItemId").val();
        let slug=$("#commentSlug").val();
        let time=$('#commentTime').val();
        let tag=$('input[name=useTag]:checked').val();
        let content=$('#commentContent').val();
        if((!itemId)||(!slug)||(!time)||(!content)){
            return Swal.fire('','請填寫完整','error');
        }

        showLoading();
        $.ajax({
            url: "{{ route('admin.crawler.item_push') }}",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            cache: false,
            type: 'post',
            data:{
                slug : slug,
                time : time,
                tag : tag,
                content : content,
                itemId : itemId,
            },
            success: function(data) {
                hideLoading();
                commentFormHide();
                if(data.success){
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
    })
});

function commentFormHide(){
    $('#commentForm').slideUp('fast',function(){
        $('#mask').hide();
        $('#commentForm').hide();
        $("#reply").prop("checked", true);
    });
}
</script>
@endpush

@endsection

{{-- csrf-token --}}
<div id="commentForm" class="container position-fixed top-50 start-50 translate-middle" style="z-index:2; display:none;">
    <div class="row border mx-sm-1 mx-md-3 p-3 bg-light">
        <div class="alert alert-success" id="commentMsg"></div>
        <div id="comments"> </div>
        <div class="col-12 mb-2">
            <i class="bi bi-x-circle float-end" id="commentFormCls"></i>
            <input class="d-none" type="text" name="commentSlug" id="commentSlug" disabled readonly>
            <div class="mb-2 fw-bold">在　優惠券<span id="commentSlugTxt"></span>　發表</div>
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
        <textarea class="form-control mb-2" id="content" rows="2" maxlength="40" placeholder="台中文心可用 (最多30字)"></textarea>
        @unless (Auth::check())
        <a class="btn btn-primary showLoading" href="/login">登入</a>
        @else
        <a class="btn btn-primary" id="commentSubmit">送出</a>
        @endunless
    </div>
</div>

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@push('scripts')
<script>
    function reply(slug , show='true'){
        $('#commentSlug').val(slug);
        $('#commentSlugTxt').text(slug);
        $('#commentSubmit').show();

        if(show=='true'){
            $.ajax({
                url: "{{ route('api.comments.index') }}",
                cache: false,
                type: 'get',
                data:{
                    slug : slug,
                },
                success: function(data) {
                    if(data.length){
                        $.each(data,function( key, value ) {
                            $('#comments').append(commentItem(value));
                        })
                        $('#comments').append("<hr>");
                    }
                    $('#loading').hide('fast');
                    $('#commentForm').show('fast');
                },
                complete: function(XMLHttpRequest, textStatus) {
                    // if(XMLHttpRequest.status == 401){
                    //     $(window).attr('location','/login');
                    // }
                },
            });
        }else{
            $('#loading').hide();
            $('#commentForm').show('fast');
        }
    }

    function commentItem(data){
        let newItem =
        `<div class="comment row mb-2 mb-sm-1">
            <div class="col" style="word-break: break-all;">[ ${data.name} ]　${data.content}</div><div class="col-3"><a class="text-decoration-none ${data.tag_css}">${data.tag_name}</a>　${data.created_at}</div>
        </div>`;
        return newItem;
    }

    $(document).ready(function() {
        $('#mask').hide();
        $('#commentMsg').hide();

        $('#commentFormCls').click(function(){
            commentFormHide();
        });

        $('#mask').click(function(){
            commentFormHide();
        });

        $("#content").keyup(function(){
            if($('#content').val().includes('不')){
                $('#use_false').prop('checked', true);
            }else if($('#content').val().includes('可')){
                $('#use_true').prop('checked', true);
            }
        });

        $('#commentSubmit').click(function(e){
            $('#commentSubmit').hide();
            let content=$('#content').val();
            let tag=$('input[name=useTag]:checked').val();
            let slug=$("#commentSlug").val();
            if((content.length<3)||(content.length>40)){
                $('#commentMsg').show().text('內容異常！');
                $('#commentSubmit').show();
                return;
            }

            $.ajax({
                url: "{{ route('api.comments.store') }}",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                cache: false,
                type: 'post',
                data:{
                    slug : slug,
                    tag : tag,
                    content : content,
                },
                success: function(data) {
                    if(data.success){
                        $('#commentMsg').show().text('留言成功！');
                        if($('meta[name="finish-type"]').attr('content')=='refresh'){
                            $('#loading').show();
                            location.reload();
                        }
                        setTimeout(function(){
                            $('#commentMsg').fadeOut('fast');
                        },3000);
                    }
                },
                complete: function(XMLHttpRequest, textStatus) {
                    //$('#commentSubmit').hide();
                },
            });
        })
    });

    function commentFormHide(){
        $('#commentForm').slideUp('fast',function(){
            $('#mask').hide();
            $("#commentSlug").val('');
            $("#reply").prop("checked", true);
            $("#content").val('');
            $('#commentMsg').hide();
            $('#comments').text("");
        });
    }

</script>
@endpush

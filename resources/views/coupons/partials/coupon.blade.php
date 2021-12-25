@foreach ($list as $coupon)
<div class="col-lg-4 col-md-12 card-tag {{$coupon->tag}}" data-aos="fade-up" data-aos-offset="50" data-slug="{{$coupon->slug}}" data-hot="{{$coupon->hot_cou}}"
    data-price="{{$coupon->new_price}}" data-discount="{{$coupon->discount}}"
    data-datetime="{{$coupon->start_at_timestamp}}">
    <div class="card mb-4 bg-light">
        <div class="card-body">
            <div class="card-header rounded bg-secondary bg-gradient mb-3" style="--bs-bg-opacity: .5;">
                <h5 class="my-1">
                    @if ($coupon->is_hot=='lv3')
                    <i class="bi bi-star-fill text-warning"></i>
                    @endif

                    <span class="me-2">{{$coupon->new_price_name}}</span>
                    <span class="me-1 fw-bold text-decoration-underline">{{$coupon->title}}</span>

                    @if ($coupon->is_hot=='lv3')
                    <span class="rounded float-end fs-6 bg-danger text-light p-1">
                    @elseif ($coupon->is_hot=='lv2')
                    <span class="rounded float-end fs-6 bg-success text-light p-1" style="--bs-bg-opacity: .7;">
                    @else
                    <span class="rounded float-end fs-6 bg-white p-1">
                    @endif
                    {{$coupon->discount_percent}}
                    </span>
                </h5>
            </div>

            <div class="card-text" style="height: 50px;">
                @if ($coupon->sub_title)
                ({!!$coupon->sub_title!!})
                @endif
                {{$coupon->content}}</div>
            <div class="card-text mb-3">
                <small class="text-muted">
                    {{$coupon->end_at_name}} {{$coupon->status_name}}
                </small>
            </div>
            @php
            if (in_array($coupon->slug,$collects)){
                $collect_css='bi-archive-fill';
            }else{
                $collect_css='bi-archive';
            }
            @endphp
            <div class="card-text float-end">
                <a class="text-decoration-none showLoading" href="javascript:void(0)" onclick="collect('{{$coupon->slug}}')" data-bs-toggle="tooltip" data-bs-placement="top" title="已被{{$coupon->collect_cou}}人收藏">
                    <i class="bi {{$collect_css}} collectItem" data-collect-slug="{{$coupon->slug}}"></i></a> ｜
                <a class="text-decoration-none showLoading" href="javascript:void(0)" onclick="reply('{{$coupon->slug}}')" data-bs-toggle="tooltip" data-bs-placement="top" title="有{{$coupon->comment_cou}}則留言">
                    <i class="bi bi-chat-square-text"></i> ({{$coupon->comment_cou}})</a> ｜
                <a class="text-decoration-none" href="{{route('coupons.show',$coupon->slug)}}" target="_blank">
                    <i class="bi bi-arrow-down-right-square" data-bs-toggle="tooltip" data-bs-placement="top" title="查看詳情"></i></a>
            </div>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">{{$coupon->hot_cou}}</span>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
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

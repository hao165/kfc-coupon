@extends('layouts.master')
@section('title')
優惠券討論區 第{{$id}}頁@endsection
@section('description','最熱門肯德基優惠券，大家都在這裡討論')
@section('keywords','討論區,聊天室')

@section('content')
<div class="container">
    @include('frontpart.breadcrumbs', ['nowtitle' => '討論區 第'.$id.'頁'])
    @include('partials.pagination')
    <div class="container">
        @foreach ($paginate['list'] as $comment)
        <div class="row justify-content-center my-4 mx-2">
            <div class="col py-3 px-4 border border-4 rounded">
                <a class="text-decoration-none btn btn-outline-dark">{{ $comment->name }}</a>
                @if ($comment->slug == 99999)
                {{-- 討論區slug-99999 --}}
                <a class="text-decoration-none btn btn-outline-secondary">{{$comment->title}}</a>
                @else
                <a class="text-decoration-none btn btn-outline-primary" href="{{route('coupons.show', $comment->slug)}}">{{$comment->title}}</a>
                @endif
                @if($comment->tag != 'reply')
                <a class="text-decoration-none btn {{$comment->tag_css}}">{{$comment->tag_name}}</a>
                @endif
                {{ date('Y-m-d', strtotime($comment->created_at)) }}
                <div class="mt-2">
                    {!! $comment->content !!}
                </div>
            </div>
        </div>
        @if($loop->iteration=='3')
        <div class="row justify-content-center my-4 mx-2">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8078981033820348" crossorigin="anonymous"></script> <!-- KFC-TOP --> <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-8078981033820348" data-ad-slot="9274099060" data-ad-format="auto" data-full-width-responsive="true"></ins> <script> (adsbygoogle = window.adsbygoogle || []).push({}); </script>
        </div>
        @endif
        @endforeach
    </div>
    @include('partials.pagination')
</div>
@endsection

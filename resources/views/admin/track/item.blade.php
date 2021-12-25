@extends('layouts.master')
@section('title','追蹤看板通知')

@section('content')
<div class="container">
    <div class="title mb-4">
        後台管理 / 追蹤看板 / 通知
    </div>
    @include('partials.pagination')
    <table class="table table-striped">
        <thead>
            <tr class="d-flex">
                <th class="col-2">類型</th>
                <th class="col">標題</th>
                <th class="col-3">時間</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($paginate['list'] as $item)
            <tr class="d-flex" id="item{{$item->id}}">
                <td class="col-2">{{$item->type}}</td>
                <td class="col"><a href="{{$item->url}}" target="_blank">[{{$item->track->cls}}]{{$item->title}}</a></td>
                <td class="col-3">{{$item->created_at}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @include('partials.pagination')
</div>
@endsection

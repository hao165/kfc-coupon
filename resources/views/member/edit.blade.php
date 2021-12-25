@extends('layouts.master')
@section('title','個人設定')
@section('description','個人設定')

@section('content')
<div class="container">
    @include('frontpart.breadcrumbs', ['nowtitle' => '個人設定'])
    @if((session()->has('message'))||($errors->any()))
        <div class="alert alert-success my-2">
            {!!session('message')!!}
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </div>
    @endif
    <form class="row col-6 mx-auto" action="{{route('member.update')}}" method="post" autocomplete="off">
    {{ method_field('PUT') }}
    {{ csrf_field() }}
    <div class="row mb-2">
        <label for="staticEmail" class="col-sm-2 col-form-label">信箱</label>
        <div class="col">
            <input type="text"class="form-control-plaintext" disabled id="staticEmail" value="{{$user->email}}">
        </div>
    </div>
    <div class="row mb-2">
        <label for="name" class="col-sm-2 col-form-label">暱稱</label>
        <div class="col">
            <input type="text" class="form-control" id="name" name="name" value="{{$user->name}}" maxlength="10" required>
        </div>
    </div>
    {{-- <div class="row mb-2">
        <label for="password" class="col-sm-2 col-form-label">密碼</label>
        <div class="col">
            <input type="password" class="form-control" id="password" name="password" name="password">
        </div>
    </div> --}}
    @if ($canEdit)
        <button class="btn btn-primary col my-2 showLoading">更新</button>
    @else
        <span class="btn btn-secondary col my-2">權限不足</span>
    @endif
    </form>
</div>


@endsection

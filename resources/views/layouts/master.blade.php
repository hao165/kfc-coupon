<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
//是否為role-admin
$isMember = false;
$isAdmin = false;
if(Auth::check()){
    $isMember = true;
    $user = Auth::user();
    $isAdmin = $user->hasTeamRole($user->currentTeam, 'admin');
}
@endphp
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title') ｜ {{ config('app.site') }}</title>
<meta http-equiv="Content-Language" content="zh-tw" />
<meta name="author" content="iZO" />
<meta name="description" content="@yield('description')。 KFC Coupon 優惠券 最低37折起！ 過期券報給大家知，能不能用來這裡就知道！肯德基優惠選 Coupon優惠選 KFC優惠選 ptt鄉民推薦">
<meta name="keywords" content="肯德基優惠券,KFC優惠選,肯德基優惠選,Coupon優惠選,KFC,肯德基,Coupon,優惠券,優惠選,快選,QuickPon,ptt,鄉民,討論區,推薦,@yield('keywords')" />
<meta property="og:title" content="@yield('title') ｜ {{ config('app.site') }}" />
<meta property="og:description" content="@yield('description')。 KFC Coupon 優惠券 最低37折起！ 過期券報給大家知，能不能用來這裡就知道！肯德基優惠選 Coupon優惠選 KFC優惠選 ptt鄉民推薦" />
<meta property="og:image" content="@yield('image', asset('icon.png'))" />
<meta property="og:site_name" content="{{ config('app.site') }}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ url()->current() }}" />
@stack('meta')
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
@if((config('app.env')=='production')&&(!$isAdmin))
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-FVMVNTDF76"></script>
<script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-FVMVNTDF76'); </script>
@endif
@if(!$isMember)
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8078981033820348" crossorigin="anonymous"></script>
@endif
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" integrity="sha512-1fPmaHba3v4A7PaUsComSM4TBsrrRGs+/fv0vrzafQ+Rw+siILTiJa0NtFfvGeyY5E182SDTaF5PqP+XOHgJag==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" integrity="sha512-GQGU0fMMi238uA+a/bdWJfpUGKUkBdgfFdgBm72SUQ6BeyWjoY/ton0tEjH+OSH9iP4Dfh+7HM0I9f5eR0L/4w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js" integrity="sha512-pax4MlgXjHEPfCwcJLQhigY7+N8rt6bVvWLFyUMuxShv170X53TRzGPmPkZmGBhk+jikR8WBM4yl7A9WMHHqvg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.3.0/sweetalert2.all.min.js" integrity="sha512-HL3ljb5fSrMMfAiRxSP+EdI/KC1kl6gVIzCV7GaJ4yFWCpnrcGxPAmytnKk9KNigFhyICxOlgD+uuaS07t7RdQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=61753eed6fc5810019e086a2&product=sticky-share-buttons" async="async"></script>
<script>
    $(document).ready(function() {
        $('.showLoading').click(function(){
            $('#mask').show();
            $('#loading').show();
        });
        $('.showMask').click(function(){
            $('#mask').show();
        });
    });
    function hideLoading(){
        $('#mask').hide('fast');
        $('#loading').hide('fast');
    }
    function showLoading(){
        $('#mask').show();
        $('#loading').show();
    }
</script>
@stack('scripts')
@stack('styles')
<style>
    .dropdown-submenu {
        position: relative;
    }

    .dropdown-submenu .dropdown-menu {
        top: 0;
        left: 100%;
        margin-top: -1px;
    }

    .navbar-nav li:hover>ul.dropdown-menu {
        display: block;
    }
</style>
</head>

<body style="padding-top: 70px;">
    @include("frontpart.header")
    @yield("content")
    @include("frontpart.footer")
    <div id="mask" class="position-fixed top-0 start-0 vw-100 vh-100 bg-secondary" style="--bs-bg-opacity: .6; z-index:1; display: none;"></div>
    <div id="loading" class="position-fixed top-50 start-50 translate-middle" style="z-index:2; display: none;"><div class="spinner-border text-primary p-2" style="width: 3rem; height: 3rem; "><span class="sr-only">Loading..</span></div></div>
    <div class="sharethis-sticky-share-buttons"></div>
    @stack('scripts-footer')
</body>

</html>

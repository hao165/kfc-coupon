<nav class="navbar navbar-expand-lg navbar-light fixed-top shadow" style="background-color: #318cd7;">
    <div class="container-fluid">
        <a class="navbar-brand showLoading text-white" href="/"><img src="{{ asset('icon.png') }}" width="20px" class="me-2 mb-1">KFC優惠選</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-white showLoading" href="{{route('coupons.create')}}">新增優惠券</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white showLoading" href="{{route('coupons.expired')}}">失效券</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white showLoading" href="{{route('chat.index',1)}}">討論區</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link text-white dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        收藏
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item showLoading" href="{{route('collect.rank')}}">收藏榜</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item showLoading" href="{{route('collect.index')}}">我的收藏</a></li>
                    </ul>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link text-white showLoading" href="/qa">常見問題</a>
                </li> --}}
                @if ($isMember)
                <li class="nav-item">
                    <a class="nav-link text-white showLoading" href="{{route('member.edit')}}">個人設定</a>
                </li>
                @endif
                @if ($isAdmin)
                <li class="nav-item dropdown">
                    <a class="nav-link text-white dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        後台管理
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item showLoading" href="{{route('admin.pending_list')}}">待審核券</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item showLoading" href="{{route('admin.crawler.post_list')}}">爬蟲文章列表</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item showLoading" href="{{route('admin.crawler.item_list',1)}}">爬蟲回覆列表</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-submenu">
                            <a href="#" data-bs-toggle="dropdown" class="dropdown-item dropdown-toggle">Line Notify</a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="{{route('admin.track.list')}}" class="dropdown-item">列表</a>
                                </li>
                                <li>
                                    <a href="{{route('admin.track.item',1)}}" class="dropdown-item">通知</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                @endif
                @if ($isMember)
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="nav-link text-white showLoading" href="{{ route('logout') }}"
                            onclick="event.preventDefault();this.closest('form').submit();">
                            登出
                        </a>
                    </form>
                </li>
                @else
                <li class="nav-item">
                    <a class="nav-link text-white showLoading" href="/login">登入</a>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

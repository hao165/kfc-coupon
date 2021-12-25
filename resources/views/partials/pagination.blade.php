<div class="pagination text-center d-flex justify-content-center">
    @if($paginate['prevUrl'])
    <li class="page-item showLoading">
        <a class="page-link" href="{{$paginate['prevUrl']}}" rel="prev"> &lsaquo; 上一頁 </a>
    </li>
    @else
    <li class="page-item disabled" aria-disabled="true">
        <span class="page-link" aria-hidden="true"> &lsaquo; 上一頁 </span>
    </li>
    @endif

    <li class="page-item"><span class="page-link" aria-hidden="true">列表</span></li>

    @if($paginate['nextUrl'])
    <li class="page-item showLoading">
        <a class="page-link" href="{{$paginate['nextUrl']}}" rel="next"> 下一頁 &rsaquo; </a>
    </li>
    @else
    <li class="page-item disabled" aria-disabled="true">
        <span class="page-link" aria-hidden="true"> 下一頁 &rsaquo; </span>
    </li>
    @endif
</div>

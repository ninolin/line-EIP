<link href="{{ asset('css/header.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">        
<header class="navbar navbar-expand navbar-dark flex-column flex-md-row">
    <div class="navbar-nav-scroll menu">
        <ul class="navbar-nav bd-navbar-nav flex-row">
            <li class="nav-item px-3">
                <a class="angle-down nav-link">工時管理</a>
                <ul>
                    <div>
                        <li><a class="@if (Request::is('webpo_applyleave')) active @endif" href="{{ route('webpo_applyleave') }}">個人工時</a></li>
                        @if (\Session::get('eip_level') == 'admin')
                            <li><a class="@if (Request::is('calendar')) active @endif" href="{{ route('calendar') }}">工時日曆</a></li>
                            <li><a class="@if (Request::is('userlist')) active @endif" href="{{ route('userlist') }}">員工設定</a></li>
                            <li><a class="@if (Request::is('work/setting/title')) active @endif" href="{{ route('ws_title') }}">工時主檔</a></li>
                            <li><a class="@if (Request::is('work/manage/applyleave')) active @endif" href="{{ route('wm_applyleave') }}">工時管理</a></li>
                            <li><a class="@if (Request::is('messagelog')) active @endif" href="{{ route('messagelog') }}">訊息記錄</a></li>
                        @endif
                    </div>
                </ul>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link @if (Request::is('formmanage')) active @endif" href="{{ route('formmanage') }}">表單管理</a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link " href="/docs/4.0/examples/">ERPTools</a>
            </li>
            <li class="nav-item px-3">
                <a class="nav-link" href="https://themes.getbootstrap.com/">公用資源</a>
            </li>
        </ul>
    </div>
    <div class="ml-md-auto">
        <form method="POST" action="{{ route('doLogout') }}">
            {{ csrf_field() }}
            <button class="btn-c">登出</button>
        </form>
    </div>
</header>
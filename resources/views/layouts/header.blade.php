<link href="{{ asset('css/header.css') }}" rel="stylesheet">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">        
<header class="navbar navbar-expand navbar-dark flex-column flex-md-row">
    <div class="navbar-nav-scroll menu">
        <ul class="navbar-nav bd-navbar-nav flex-row">
            <li class="nav-item px-3">
                <a class="angle-down nav-link @if (Request::is('whmanage')) active @endif" href="{{ route('whmanage') }}">工時管理</a>
                <ul>
                    <div>
                        <li><a class="@if (Request::is('userlist')) active @endif" href="{{ route('userlist') }}">員工清單</a></li>
                        <li><a class="@if (Request::is('titlelist')) active @endif" href="{{ route('titlelist') }}">職等設定</a></li>
                        <li><a class="@if (Request::is('formmanage')) active @endif" href="{{ route('formmanage') }}">查看最近請假</a></li>
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
    <div class="ml-md-auto"><button class="btn-c">登出</button></div>
</header>
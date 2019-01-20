<link href="{{ asset('css/header.css') }}" rel="stylesheet">

<header class="navbar navbar-expand navbar-dark flex-column flex-md-row">
    <div class="navbar-nav-scroll">
        <ul class="navbar-nav bd-navbar-nav flex-row">
            <li class="nav-item px-3">
                <a class="nav-link @if (Request::is('whmanage')) active @endif" href="{{ route('whmanage') }}">工時管理</a>
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
    <div class="ml-md-auto"><button class="btn">登出</button></div>
</header>
@extends('layouts.master')
@section('content')
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #f1f3f5">
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">
      <a class="nav-link @if ($tab == 'dashboard') active @endif" href="{{ route('ws_title') }}">工時總表</a>
      <a class="nav-link @if ($tab == 'last') active @endif" href="{{ route('ws_leavetype') }}">最近工時</a>
      <a class="nav-link @if ($tab == 'individual') active @endif" href="{{ route('ws_overworktype') }}">員工工時</a>
    </div>
  </div>
</nav>
@yield('content2')
@endsection

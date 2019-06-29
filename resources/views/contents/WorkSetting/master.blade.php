@extends('layouts.master')
@section('content')
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #f1f3f5">
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">
      <a class="nav-link @if ($tab == 'title') active @endif" href="{{ route('ws_title') }}">職等設定</a>
      <a class="nav-link @if ($tab == 'leavetype') active @endif" href="{{ route('ws_leavetype') }}">假別設定</a>
      <a class="nav-link @if ($tab == 'overworktype') active @endif" href="{{ route('ws_overworktype') }}">加班設定</a>
      <a class="nav-link @if ($tab == 'workclass') active @endif" href="{{ route('ws_class') }}">班表設定</a>
    </div>
  </div>
</nav>
@yield('content2')
@endsection

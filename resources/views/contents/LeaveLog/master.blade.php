@extends('layouts.master')
@section('content')
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #f1f3f5">
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">
      <a class="nav-link @if ($tab == 'last') active @endif" href="{{ route('ll_last') }}">簽核中紀錄</a>
      <a class="nav-link @if ($tab == 'individual') active @endif" href="{{ route('ll_individual') }}">員工紀錄查詢</a>
    </div>
  </div>
</nav>
@yield('content2')
@endsection

@extends('layouts.master')
@section('content')
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #f1f3f5">
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">
      <a class="nav-link @if ($tab == 'applyleave') active @endif" href="{{ route('wm_applyleave') }}">申請休假</a>
      <a class="nav-link @if ($tab == 'applyoverwork') active @endif" href="{{ route('wm_applyoverwork') }}">申請加班</a>
      <!-- <a class="nav-link @if ($tab == 'last') active @endif" href="{{ route('wm_last') }}">簽核中紀錄</a> -->
      <a class="nav-link @if ($tab == 'individual') active @endif" href="{{ route('wm_individual') }}">員工紀錄查詢</a>
    </div>
  </div>
</nav>
@yield('content2')
@endsection

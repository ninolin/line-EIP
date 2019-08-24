@extends('layouts.master')
@section('content')
<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #f1f3f5">
  <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
    <div class="navbar-nav">
      <a class="nav-link @if ($tab == 'applyleave') active @endif" href="{{ route('po_applyleave') }}">申請休假</a>
      <a class="nav-link @if ($tab == 'applyoverwork') active @endif" href="{{ route('po_applyoverwork') }}">申請加班</a>
      <a class="nav-link @if ($tab == 'validate') active @endif" href="{{ route('po_validate') }}">簽核申請</a>
      <a class="nav-link @if ($tab == 'log') active @endif" href="{{ route('po_log') }}">工時查詢</a>
    </div>
  </div>
</nav>
@yield('content3')
@endsection

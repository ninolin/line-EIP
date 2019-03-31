@extends('layouts.master')
@section('title', 'Home')
@section('content')
<div class="container-fluid pt-lg-4">
  <form>
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="帳號或Email">
        </div>
        <div class="col-auto">
          <button type="button" class="btn-c">搜尋</button>
        </div>
      </div>
    </div>
  </form>
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          <th scope="col">帳號</th>
          <th scope="col">Email</th>
          <th scope="col">名稱</th>
          <th scope="col">等級</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
          <tr>
            <td> {{$user->username}} </td>
            <td> {{$user->email}} </td>
            <td> {{$user->cname}} </td>
            <td> {{$user->level}} </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <nav aria-label="Page navigation example">
      <ul class="pagination justify-content-center">
        <li class="page-item @if ($page == 1) disabled @endif ">
          <a class="page-link" href="./userlist?page={{ $page-1 }}" >Previous</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./userlist?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./userlist?page={{ $page+1 }}">Next</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>
@endsection

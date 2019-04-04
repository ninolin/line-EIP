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
          <th scope="col">職等</th>
          <th scope="col">第一簽核人</th>
          <th scope="col">操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
          <tr>
            <td> {{$user->username}} </td>
            <td> {{$user->email}} </td>
            <td> {{$user->cname}} </td>
            <td> {{$user->title}} </td>
            <td> {{$user->upper_cname}} </td>
            <td>  
             <button type="button" class="btn btn-outline-primary btn-sm" onclick="showSetModal({{$user->NO}}, {{$user->title_id}}, {{$user->upper_user_no}})">設定</button>
            </td>
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
          <a class="page-link" href="./userlist?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./userlist?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./userlist?page={{ $page+1 }}">下一頁</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>
<!-- Modal -->
<div class="modal fade" id="setModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">設定職等和第一簽核人</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row">
              <label for="title-name" class="col-form-label w-25">職等:</label>
              <div class="col-form-label w-75">
                <select id="title_set_select"></select>
              </div>
            </div>
            <div class="row">
              <label for="title-name" class="col-form-label w-25">第一簽核人:</label>
              <div class="col-form-label w-75">
                <select id="upper_user_set_select" class="w-75"></select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo">新增</button>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('js/views/userlist.js') }}"></script>
@endsection

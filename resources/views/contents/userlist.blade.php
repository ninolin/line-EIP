@extends('layouts.master')
@section('title', 'Home')
@section('content')
<div class="container-fluid pt-lg-4">
  <form id="search_form" method="POST" action="{{ route('userlist') }}">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <input type="text" name="search" class="form-control" placeholder="帳號或Email或名稱" value="{{ $search }}">
        </div>
        <div class="col-auto">
          <button type="button" class="btn-c"  onclick="reload_page(1, '{{$order_col}}', '{{$order_type}}', 'search')">搜尋</button>
        </div>
      </div>
    </div>
  </form>
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          <th scope="col" onclick="reload_page({{$page}}, 'username', '{{$order_type}}', 'col')">帳號
            @if ($order_col == 'username' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'username' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'email', '{{$order_type}}', 'col')">Email
            @if ($order_col == 'email' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'email' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'cname', '{{$order_type}}', 'col')">名稱
            @if ($order_col == 'cname' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'cname' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'title_id', '{{$order_type}}', 'col')">職等
            @if ($order_col == 'title_id' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'title_id' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'upper_user_no', '{{$order_type}}', 'col')">第一簽核人
            @if ($order_col == 'upper_user_no' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'upper_user_no' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
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
        <li class="page-item @if ($page == 1) disabled @endif " onclick="reload_page({{$page-1}}, '{{$order_col}}', '{{$order_type}}', 'page')">
          <a class="page-link">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif" onclick="reload_page({{$i}}, '{{$order_col}}', '{{$order_type}}', 'page')">
            <a class="page-link">{{$i}}</a>
          </li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif" onclick="reload_page({{$page+1}}, '{{$order_col}}', '{{$order_type}}', 'page')">
          <a class="page-link">下一頁</a>
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

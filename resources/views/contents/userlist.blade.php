@extends('layouts.master')
@section('title', 'Home')
@section('content')
<div class="container-fluid pt-lg-4">
  <form id="search_form" method="POST" action="{{ route('userlist') }}">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <input type="text" name="search" class="form-control" placeholder="帳號或名稱" value="{{ $search }}">
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
          <!-- <th scope="col" onclick="reload_page({{$page}}, 'username', '{{$order_type}}', 'col')">帳號
            @if ($order_col == 'username' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'username' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th> -->
          <th scope="col" onclick="reload_page({{$page}}, 'cname', '{{$order_type}}', 'col')">名稱
            @if ($order_col == 'cname' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'cname' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'eip_level', '{{$order_type}}', 'col')">權限
            @if ($order_col == 'eip_level' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'eip_level' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'onboard_date', '{{$order_type}}', 'col')" style="width: 115px;">到職日
            @if ($order_col == 'onboard_date' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'onboard_date' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'title_id', '{{$order_type}}', 'col')" style="width: 70px;">職等
            @if ($order_col == 'title_id' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'title_id' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'default_agent_user_no', '{{$order_type}}', 'col')">預設代理人
            @if ($order_col == 'default_agent_user_no' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'default_agent_user_no' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'upper_user_no', '{{$order_type}}', 'col')">第一簽核人
            @if ($order_col == 'upper_user_no' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'upper_user_no' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'work_class_id', '{{$order_type}}', 'col')">班別
            @if ($order_col == 'upper_user_no' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'upper_user_no' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" onclick="reload_page({{$page}}, 'line_id', '{{$order_type}}', 'col')" style="width: 300px;" >lineId
            @if ($order_col == 'line_id' && $order_type == 'DESC') <div class="angle-down"></div> @endif
            @if ($order_col == 'line_id' && $order_type == 'ASC') <div class="angle-up"></div> @endif
          </th>
          <th scope="col" style="width: 75px;">操作</th>
        </tr>
      </thead>
      <tbody>
        @foreach($users as $user)
          <tr>
            <td> {{$user->cname}} </td>
            <td> 
              @if ($user->eip_level == 'admin')
                管理用戶
              @else 
                一般用戶
              @endif
            </td>
            <td> {{$user->onboard_date}} </td>
            <td> {{$user->title}} </td>
            <td> {{$user->default_agent_cname}} </td>
            <td> {{$user->upper_cname}} </td>
            <td> {{$user->work_class_name}} </td>
            <td> {{$user->line_id}} </td>
            <td>
              <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  操作
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="#" onclick="showSetModal({{$user->NO}}, {{$user->title_id}}, {{$user->default_agent_user_no}}, {{$user->upper_user_no}}, {{$user->work_class_id}}, '{{$user->eip_level}}')">主檔設定</a>
                  <a class="dropdown-item" href="#" onclick="showLeaveDayModal('{{$user->NO}}', '{{$user->onboard_date}}')">休假設定</a>
                  <div class="dropdown-divider"></div>
                  @if ($user->line_id == '') 
                    <a class="dropdown-item text-success" href="#" onclick="showBindLineId({{$user->NO}}, '{{$user->cname}}')">綁定lineId</a>
                  @else
                    <a class="dropdown-item text-danger" href="#" onclick="showUnbindLineId({{$user->NO}}, '{{$user->cname}}', '{{$user->line_id}}')">解除lineId</a>
                  @endif
                  </div>
              </div>
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
          <a class="page-link" onclick="reload_page({{$page-1}}, '{{$order_col}}', '{{$order_type}}', 'page')">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif" onclick="reload_page({{$i}}, '{{$order_col}}', '{{$order_type}}', 'page')">
            <a class="page-link">{{$i}}</a>
          </li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif" >
          <a class="page-link" onclick="reload_page({{$page+1}}, '{{$order_col}}', '{{$order_type}}', 'page')">下一頁</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>
<!-- Modal -->
<div class="modal fade" id="setModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">設定</h5>
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
              <label for="title-name" class="col-form-label w-25">預設代理人:</label>
              <div class="col-form-label w-75">
                <select id="default_agent_user_set_select"></select>
              </div>
            </div>
            <div class="row">
              <label for="title-name" class="col-form-label w-25">第一簽核人:</label>
              <div class="col-form-label w-75">
                <select id="upper_user_set_select"></select>
              </div>
            </div>
            <div class="row">
              <label for="title-name" class="col-form-label w-25">班別:</label>
              <div class="col-form-label w-75">
                <select id="work_class_set_select"></select>
              </div>
            </div>
            <div class="row">
              <label for="title-name" class="col-form-label w-25">權限</label>
              <div class="col-form-label w-75">
                <select id="eip_level_set_select" class="form-control">
                  <option value="user">一般用戶</option>
                  <option value="admin">管理用戶</option>
                </select>
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
<div class="modal fade" id="setLeaveDayModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">設定休假</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row">
              <label class="col-form-label w-25">到職日:</label>
              <div class="col-form-label w-75">
                <input id="onboard_date" type="date" class="form-control" onchange="cal_laborannualleave()">
              </div>
            </div>
            <div class="row">
              <label class="col-form-label w-25">建議給予年休:</label>
              <label class="col-form-label w-75" id="labor_annual_leaves">勞基法年休:</label>
            </div>
            <div class="row">
              <label class="col-form-label w-25">實際給予年休:</label>
              <input type="text" class="form-control w-75" id="annual_leaves">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo">設定</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="bindLineModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">綁定LineId</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row">
              <label for="title-name" class="col-form-label col-md-4">LineId:</label>
              <input type="text" class="form-control col-md-8" id="line_id_input">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo">綁定</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="unbindLineModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">解除綁定LineId</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row">
              確定要解除綁定LineId嗎，解除綁定該用戶無法透過Line使用EIP功能
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-danger todo">解除</button>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('js/views/userlist.js') }}"></script>
@endsection

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
          <th scope="col">申請人</th>
          <th scope="col">代理人</th>
          <th scope="col">假別/加班</th>
          <th scope="col">起</th>
          <th scope="col">迄</th>
          <th scope="col">備註</th>
          <th scope="col">申請日</th>
          <th scope="col">狀態</th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $log)
          <tr>
            <td> {{$log->cname}} </td>
            <td> 
              @if ($log->apply_type == 'L') 
                {{$log->agent_cname}}
              @else
                -
              @endif
            </td>
            <td> 
              @if ($log->apply_type == 'L')
                {{$log->leave_name}}
              @else
                加班
              @endif
            </td>
            <td> 
              @if ($log->apply_type == 'L')
                {{$log->start_date}}
              @else
                {{$log->over_work_date}} ({{$log->over_work_hours}}小時)
              @endif
            </td>
            <td>
              @if ($log->apply_type == 'L') 
                {{$log->end_date}}
              @else
                -
              @endif
            </td>
            <td> {{$log->comment}} </td>
            <td> {{$log->apply_time}} </td>
            <td> 
              @if ($log->apply_status == 'Y')
                  已通過
              @elseif ($log->apply_status == 'N')
                  已拒絕
              @else
                  簽核中
              @endif
            </td>
            <td>  
             <button type="button" class="btn btn-outline-primary btn-sm" onclick="showDetailModal({{$log->id}})">詳細</button>
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
          <a class="page-link" href="./leavelog?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./leavelog?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./leavelog?page={{ $page+1 }}">下一頁</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>
<!-- Modal -->
<div class="modal fade" id="logModal" tabindex="-1" role="dialog"aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">簽核紀錄</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row">
              <table class="table table-bordered table-striped">
                <thead class="table-thead">
                  <tr>
                      <th scope="col">簽核順位</th>
                      <th scope="col">簽核人</th>
                      <th scope="col">簽核狀態</th>
                      <th scope="col">拒絕原因</th>
                      <th scope="col">簽核時間</th>
                  </tr>
                </thead>
                <tbody id="log_data">
                  <tr><td colspan="5">無資料</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('js/views/leavelog.js') }}"></script>
@endsection

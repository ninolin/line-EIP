@extends('contents.PersonalOperate.master')
@section('content3')
<style>
  .nav-item {
    line-height: 35px !important;
  }
  .date-input {
    padding: 1px !important;
    width: 235px !important;
  }
  .blade_select2 {
    width: 150px !important;
  }
  .overwork-date {
    width: 65% !important;
    display: inline;
  }
</style>
<div class="container-fluid pt-lg-4">
  <div class="row p-lg-3" @if ($user_no == 0) style="display:none" @endif>
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          <th scope="col">員工</th>
          <th scope="col">到職日:</th>
          @foreach($types as $type)
            <th scope="col">{{$type->name}}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        <tr>
          <td scope="col">{{$cname}}</td>
          <td scope="col">{{$onboard_date}}</td>
          @foreach($types as $type)
            <td> {{$type->hours}}小時 </td>
          @endforeach
        </tr>
      </tbody>
    </table>
  </div>
  <nav @if ($user_no == 0) style="display:none" @endif>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
      <a class="nav-item nav-link active" id="nav-leave-tab" data-toggle="tab" href="#nav-leave" role="tab" aria-controls="nav-leave" aria-selected="true">休假</a>
      <a class="nav-item nav-link" id="nav-overwork-tab" data-toggle="tab" href="#nav-overwork" role="tab" aria-controls="nav-overwork" aria-selected="false">加班</a>
      <a class="nav-item nav-link" id="nav-agent-tab" data-toggle="tab" href="#nav-agent" role="tab" aria-controls="nav-agent" aria-selected="false">代理人</a>
    </div>
  </nav>
  <div class="tab-content" id="nav-tabContent" @if ($user_no == 0) style="display:none" @endif>
    <div class="tab-pane fade show active" id="nav-leave" role="tabpanel" aria-labelledby="nav-leave-tab">
      <div class="row p-lg-3">
        <table class="table table-bordered table-striped">
          <thead class="table-thead">
              <tr>
                <th scope="col">申請人</th>
                <th scope="col">代理人</th>
                <th scope="col">假別</th>
                <th scope="col">起</th>
                <th scope="col">迄</th>
                <th scope="col">備註</th>
                <th scope="col" style="width: 160px;">申請日</th>
                <th scope="col" style="width: 75px;">狀態</th>
                <th scope="col"></th>
              </tr>
          </thead>
          <tbody>
              @foreach($leaves as $leave)
                <tr>
                  <td> {{$leave->cname}} </td>
                  <td> {{$leave->agent_cname}} </td>
                  <td> {{$leave->leave_name}} ({{$leave->leave_hours}}小時) </td>
                  <td> {{strftime('%Y-%m-%d %H:%M', strtotime($leave->start_date))}} </td>
                  <td> {{strftime('%Y-%m-%d %H:%M', strtotime($leave->end_date))}} </td>
                  <td> {{$leave->comment}} </td>
                  <td> {{strftime('%Y-%m-%d %H:%M', strtotime($leave->apply_time))}} </td>
                  <td> 
                    @if ($leave->apply_status == 'Y')
                        已通過
                    @elseif ($leave->apply_status == 'N')
                        已拒絕
                    @elseif ($leave->apply_status == 'C')
                        已取消
                    @else
                        簽核中
                    @endif
                  </td>
                  <td>  
                    <div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        操作
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="showDetailModal({{$leave->id}}, {{$login_user_no}})">簽核紀錄</a>
                        <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$leave->id}})">更新紀錄</a>
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
            <li class="page-item @if ($leaves_page == 1) disabled @endif ">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page-1 }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $leaves_t_pages; $i++)
              <li class="page-item @if ($i == $leaves_page) active @endif"><a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $i }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($leaves_page == $leaves_t_pages) disabled @endif">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page+1 }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page }}">下一頁</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="nav-overwork" role="tabpanel" aria-labelledby="nav-overwork-tab">
      <div class="row p-lg-3">
        <table class="table table-bordered table-striped">
            <thead class="table-thead">
              <tr>
                <th scope="col">申請人</th>
                <th scope="col">加班日期</th>
                <th scope="col">加班小時</th>
                <th scope="col">備註</th>
                <th scope="col">申請日</th>
                <th scope="col">狀態</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              @foreach($overworks as $overwork)
                <tr>
                  <td> {{$overwork->cname}} </td>
                  <td> {{$overwork->over_work_date}} </td>
                  <td> {{$overwork->over_work_hours}}小時 </td>
                  <td> {{$overwork->comment}} </td>
                  <td> {{$overwork->apply_time}} </td>
                  <td> 
                    @if ($overwork->apply_status == 'Y')
                        已通過
                    @elseif ($overwork->apply_status == 'N')
                        已拒絕
                    @elseif ($overwork->apply_status == 'C')
                        已取消
                    @else
                        簽核中
                    @endif
                  </td>
                  <td>  
                    <div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        操作
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="showDetailModal({{$overwork->id}}, {{$login_user_no}})">簽核紀錄</a>
                        <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$overwork->id}})">更新紀錄</a>
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
            <li class="page-item @if ($overworks_page == 1) disabled @endif ">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page-1 }}&agents_page={{ $agents_page }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $overworks_t_pages; $i++)
              <li class="page-item @if ($i == $overworks_page) active @endif"><a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page }}&overworks_page={{ $i }}&agents_page={{ $agents_page }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($overworks_page == $overworks_t_pages) disabled @endif">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page+1 }}&agents_page={{ $agents_page }}">下一頁</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="nav-agent" role="tabpanel" aria-labelledby="nav-agent-tab">
      <div class="row p-lg-3">
        <table class="table table-bordered table-striped">
          <thead class="table-thead">
              <tr>
                <th scope="col">申請人</th>
                <th scope="col">代理人</th>
                <th scope="col">假別</th>
                <th scope="col">起</th>
                <th scope="col">迄</th>
                <th scope="col">備註</th>
                <th scope="col">申請日</th>
                <th scope="col">狀態</th>
                <th scope="col"></th>
              </tr>
          </thead>
          <tbody>
              @foreach($agents as $agent)
                <tr>
                  <td> {{$agent->cname}} </td>
                  <td> {{$agent->agent_cname}} </td>
                  <td> 
                    @if ($agent->apply_type == 'L')
                      {{$agent->leave_name}} ({{$agent->leave_hours}}小時)
                    @else
                      加班 ({{$agent->over_work_hours}}小時)
                    @endif
                  </td>
                  <td> 
                    @if ($agent->apply_type == 'L')
                      {{$agent->start_date}}
                    @else
                      {{$agent->over_work_date}} 
                    @endif
                  </td>
                  <td>
                    @if ($agent->apply_type == 'L') 
                      {{$agent->end_date}}
                    @else
                      -
                    @endif
                  </td>
                  <td> {{$agent->comment}} </td>
                  <td> {{$agent->apply_time}} </td>
                  <td> 
                    @if ($agent->apply_status == 'Y')
                        已通過
                    @elseif ($agent->apply_status == 'N')
                        已拒絕
                    @elseif ($agent->apply_status == 'C')
                        已取消
                    @else
                        簽核中
                    @endif
                  </td>
                  <td>  
                    <div class="btn-group">
                      <button type="button" class="btn btn-primary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        操作
                      </button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="showDetailModal({{$agent->id}}, {{$login_user_no}})">簽核紀錄</a>
                        <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$agent->id}})">更新紀錄</a>
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
            <li class="page-item @if ($agents_page == 1) disabled @endif ">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page-1 }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $agents_t_pages; $i++)
              <li class="page-item @if ($i == $agents_page) active @endif"><a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page }}&agents_page={{ $i }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($agents_page == $agents_t_pages) disabled @endif">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page+1 }}">下一頁</a>
            </li>
          </ul>
        </div>
      </div>
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
                  <tr><td colspan="5" class="text-center">無資料</td></tr>
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
<div class="modal fade" id="changeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-leave">確認</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="container-fluid form-group msg"></div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary tocancel" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo">確認</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="changelogModal" tabindex="-1" role="dialog"aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">修改紀錄</h5>
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
                      <th>修改日期</th>
                      <th>修改內容</th>
                      <th>修改人</th>
                  </tr>
                </thead>
                <tbody id="changelog_data">
                  <tr><td colspan="3" class="text-center">無資料</td></tr>
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
<script>

  const showDetailModal = async (apply_id, login_user_no) => {
      const users_res = await promise_call({
        url: "../api/userlist", 
        method: "get"
      });
      let all_users = [];
      if(users_res.status == "successful") {
        all_users = users_res.data.map(item => {
          item.id = item.NO;
          item.text = item.cname;
          return item;
        })
      }

      const res = await promise_call({
          url: "../api/leavelog/process/"+apply_id, 
          method: "get"
      });

      if(res.status == "successful") {
          if(res.data.length > 0) $("#log_data").html("");
          res.data.map( (item, index) => {
              let html = "<tr>";
              html += "<td>"+(index+1)+"</td>";
              
              if(item.is_validate === 1) {
                html += "<td>"+item.cname+"</td>";
                html += "<td>同意</td>";
              } else if(item.is_validate === 0){
                html += "<td>"+item.cname+"</td>";
                html += "<td>拒絕</td>";
              } else {
                html += "<td>"+item.cname+"</td>";
                html += "<td>未簽核</td>";
              }
              if(item.reject_reason) {
                  html += "<td>"+item.reject_reason+"</td>";
              } else {
                  html += "<td>-</td>";
              }
              if(item.validate_time) {
                  html += "<td>"+item.validate_time+"</td>";
              } else {
                  html += "<td>-</td>";
              }
              html += "</tr>";
              $("#log_data").append(html);
          })
          $('#logModal').modal('toggle');
      }
  }

  const showChangeLogModal = async (apply_id) => {
    const res = await promise_call({
          url: "../api/leavelog/changelog/"+apply_id, 
          method: "get"
    });
    if(res.status == "successful") {
        if(res.data.length > 0) $("#changelog_data").html("");
        res.data.map( (item, index) => {
            let html = "<tr>";
            html += "<td>"+item.change_time+"</td>";
            html += "<td>"+item.change_desc+"</td>";
            html += "<td>"+item.cname+"</td>";
            html += "</tr>";
            $("#changelog_data").append(html);
        })
        $('#changelogModal').modal('toggle');
    }
  }

  const get_applyleave = (apply_id) => {
    return promise_call({
        url: "../api/applyleave/"+apply_id, 
        method: "get"
    })
  }

  window.onload = function() {
    $('.blade_select2').select2();
  };
</script>
@endsection

@extends('contents.LeaveLog.master')
@section('content2')
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
  <form id="search_form" method="GET" action="{{ route('ll_last') }}">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-sm-12 form-row">
        <div class="col-auto">
          <input name="search" type="text" class="form-control" placeholder="帳號或Email" value="{{ $search }}">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn-c">搜尋</button>
        </div>
      </div>
    </div>
  </form>

  <nav style="padding-top: 1rem;">
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
      <a class="nav-item nav-link active" id="nav-leave-tab" data-toggle="tab" href="#nav-leave" role="tab" aria-controls="nav-leave" aria-selected="true">休假</a>
      <a class="nav-item nav-link" id="nav-overwork-tab" data-toggle="tab" href="#nav-overwork" role="tab" aria-controls="nav-overwork" aria-selected="false">加班</a>
    </div>
  </nav>
  <div class="tab-content" id="nav-tabContent">
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
                <th scope="col" style="width: 220px;">備註</th>
                <th scope="col">申請日</th>
                <th scope="col" style="width: 75px;">狀態</th>
                <th scope="col" style="width: 90px;"></th>
              </tr>
          </thead>
          <tbody>
              @foreach($leaves as $leave)
                <tr>
                  <td> {{$leave->cname}} </td>
                  <td> 
                    <select class="blade_select2" id='leave_agent_user_select_{{$leave->id}}' onchange='confirm_change_agent_user("leave_agent_user_select_{{$leave->id}}", {{$leave->id}}, {{$leave->agent_user_no}}, "{{$leave->agent_cname}}", {{$login_user_no}})'>
                      @foreach($users as $u)
                        <option value='{{$u->NO}}' @if ($u->cname == $leave->agent_cname) selected @endif> {{$u->cname}}</option>
                      @endforeach
                    </select>
                  </td>
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
                        @if ($leave->apply_status != 'N' && $leave->apply_status != 'C') 
                          <a class="dropdown-item" href="#" onclick="showChangeLeaveDateModal({{$leave->id}}, {{$login_user_no}})">更新起迄</a>
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
            <li class="page-item @if ($leaves_page == 1) disabled @endif ">
              <a class="page-link" href="./last?&search={{$search}}&leaves_page={{ $leaves_page-1 }}&overworks_page={{ $overworks_page }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $leaves_t_pages; $i++)
              <li class="page-item @if ($i == $leaves_page) active @endif"><a class="page-link" href="./last?search={{$search}}&leaves_page={{ $i }}&overworks_page={{ $overworks_page }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($leaves_page == $leaves_t_pages) disabled @endif">
              <a class="page-link" href="./last?search={{$search}}&leaves_page={{ $leaves_page+1 }}&overworks_page={{ $overworks_page }}">下一頁</a>
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
                <th scope="col" style="width: 350px;">備註</th>
                <th scope="col">申請日</th>
                <th scope="col" style="width: 75px;">狀態</th>
                <th scope="col" style="width: 90px;"></th>
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
                        @if ($overwork->apply_status != 'N' && $overwork->apply_status != 'C') 
                          <a class="dropdown-item" href="#" onclick="showChangeOverworkDateModal({{$overwork->id}}, {{$login_user_no}})">更新起迄</a>
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
            <li class="page-item @if ($overworks_page == 1) disabled @endif ">
              <a class="page-link" href="./last?search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page-1 }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $overworks_t_pages; $i++)
              <li class="page-item @if ($i == $overworks_page) active @endif"><a class="page-link" href="./last?search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $i }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($overworks_page == $overworks_t_pages) disabled @endif">
              <a class="page-link" href="./last?search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page+1 }}">下一頁</a>
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
<div class="modal fade" id="changeLeaveDateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-leave">更新休假起訖</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group row">
            <label class="col-5 col-form-label">休假的目前開始日期</label>
            <label class="col-7 col-form-label now_start_date"></label>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">休假的目前結束日期</label>
            <label class="col-7 col-form-label now_end_date"></label>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">休假的新開始日期</label>
            <div class="col-7">
              <input type="datetime-local" class="form-control date-input new_leave_start_date" value="">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">休假的新結束日期</label>
            <div class="col-7">
              <input type="datetime-local" class="form-control date-input new_leave_end_date" value="">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">更新原因</label>
            <div class="col-7">
              <textarea class="form-control rounded-0 reason" rows="3"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary tocancel" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo">確認</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="changeOverworkDateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-overwork">更新加班時間</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group row">
            <label class="col-5 col-form-label">目前的加班日期</label>
            <label class="col-7 col-form-label now_overwork_date"></label>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">目前的加班小時</label>
            <label class="col-7 col-form-label now_overwork_hours"></label>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">新的加班日期</label>
            <div class="col-7">
              <input type="date" class="form-control date-input new_overwork_date" value="">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">新的加班小時</label>
            <div class="col-7">
              <select class="form-control new_overwork_hours">
                <option value="1">1小時</option>
                <option value="2">2小時</option>
                <option value="3">3小時</option>
                <option value="4">4小時</option>
                <option value="5">5小時</option>
                <option value="6">6小時</option>
                <option value="7">7小時</option>
                <option value="8">8小時</option>
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-5 col-form-label">更新原因</label>
            <div class="col-7">
              <textarea class="form-control rounded-0 reason" rows="3"></textarea>
            </div>
          </div>
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

  const reload_page = () => {
    $("#search_form").attr("action", "./userlist");
    $("#search_form").submit();
  }

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
                html += "<td><select id='upper_user_select_"+item.id+"' onchange='confirm_change_upper_user("+item.apply_id+", "+item.id+", "+item.upper_user_no+", \""+item.cname+"\", "+login_user_no+")'></select></td>";
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
              $("#upper_user_select_"+item.id).select2({
                dropdownParent: $("#logModal"),
                data: all_users,
                dropdownAutoWidth : false,
                width: '100%'
              })
              $("#upper_user_select_"+item.id).val(item.upper_user_no).trigger("change");
          })
          $('#logModal').modal('toggle');
      }
  }

  const showChangeLeaveDateModal = async (apply_id, login_user_no) => {
    const res = await get_applyleave(apply_id);
    if(res.status == "successful" && res.data.length == 1) {
      $('#changeLeaveDateModal').find('.now_start_date').html(res.data[0].start_date_f1);
      $('#changeLeaveDateModal').find('.now_end_date').html(res.data[0].end_date_f1);
      $('#changeLeaveDateModal').find('.new_leave_start_date').val(res.data[0].start_date_f2);
      $('#changeLeaveDateModal').find('.new_leave_end_date').val(res.data[0].end_date_f2);
      $("#changeLeaveDateModal").find(".todo").attr("onclick", "change_leave_date('"+apply_id+"', '"+login_user_no+"')");
      $('#changeLeaveDateModal').modal('toggle');
    } else {
      alert(v.message);
    }
  }

  const change_leave_date = (apply_id, login_user_no) => {
    promise_call({
      url: "../api/leavelog/change_leave_date", 
      data: {
        "apply_id": apply_id,
        "new_leave_start_date": $('#changeLeaveDateModal').find('.new_leave_start_date').val(),
        "new_leave_end_date": $('#changeLeaveDateModal').find('.new_leave_end_date').val(),
        "reason": $('#changeLeaveDateModal').find('.reason').val(),
        "login_user_no": login_user_no
      }, 
      method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
          $('#changeLeaveDateModal').modal('toggle');
        } else {
          alert(v.message);
        }
    })
  }

  const showChangeOverworkDateModal = async (apply_id, login_user_no) => {
    const res = await get_applyleave(apply_id);
    if(res.status == "successful" && res.data.length == 1) {
      $('#changeOverworkDateModal').find('.now_overwork_date').html(res.data[0].over_work_date);
      $('#changeOverworkDateModal').find('.now_overwork_hours').html(res.data[0].over_work_hours);
      $('#changeOverworkDateModal').find('.new_overwork_date').val(res.data[0].over_work_date);
      $('#changeOverworkDateModal').find('.new_overwork_hours').val(res.data[0].over_work_hours);
      $("#changeOverworkDateModal").find(".todo").attr("onclick", "change_overwork_date('"+apply_id+"', '"+login_user_no+"')");
      $('#changeOverworkDateModal').modal('toggle');
    } else {
      alert(v.message);
    }
  }

  const change_overwork_date = (apply_id, login_user_no) => {
    promise_call({
      url: "../api/leavelog/change_overwork_date", 
      data: {
        "apply_id": apply_id,
        "new_overwork_date": $('#changeOverworkDateModal').find('.new_overwork_date').val(),
        "new_overwork_hours": $('#changeOverworkDateModal').find('.new_overwork_hours').val(),
        "reason": $('#changeOverworkDateModal').find('.reason').val(),
        "login_user_no": login_user_no
      }, 
      method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
          $('#changeOverworkDateModal').modal('toggle');
        } else {
          alert(v.message);
        }
    })
  }

  const confirm_change_upper_user = (apply_id, apply_process_id, old_upper_user_no, old_upper_user_cname, login_user_no) => {
    //因為用select2要先trigger change一次，所以這邊會要檢查新的簽核人是否跟舊的簽核人不同人，才會去執行換簽核人的程式
    if(old_upper_user_no != $("#upper_user_select_"+apply_process_id).val()) {
      const new_cname = $("#upper_user_select_"+apply_process_id).select2('data')[0].cname;
      $('#changeModal').modal('toggle');
      let html = "<div class='row'>";
        html += "<label class='col-form-label col-md-3'>訊息:</label>";
        html += "<label class='col-form-label' style='width: 72%;'>";
        html += "確定要將<strong class='text-success'>簽核人</strong>";
        html += "從<strong class='text-success'>"+old_upper_user_cname+"</strong>";
        html += "換成<strong class='text-success'>"+new_cname+"</strong>嗎";
        html += "</label>";
        html += "</div>";
        html += "<div class='row'>";
        html += "<label class='col-form-label col-md-3'>說明:</label>";
        html += "<input type='text' class='col-md-9 form-control confirm_reason'>";
        html += "</div>";
      $('#changeModal').find('.msg').html(html);
      $('#changeModal').css('z-index', '1060');
      $($('.modal-backdrop')[1]).css('z-index', '1051');

      $("#changeModal").find(".todo").attr("onclick", "change_upper_user('"+apply_id+"', '"+apply_process_id+"', '"+login_user_no+"')");
      $("#changeModal").find(".tocancel").attr("onclick", "cancel_change_upper_user('"+apply_process_id+"', '"+old_upper_user_no+"')");
    }
  }

  const cancel_change_upper_user = (apply_process_id, old_upper_user_no) => {
    $("#upper_user_select_"+apply_process_id).val(old_upper_user_no).trigger("change");
    $('#changeModal').modal('toggle');
  }

  const change_upper_user = (apply_id, apply_process_id, login_user_no) => {
    const user_NO = $("#upper_user_select_"+apply_process_id).val();
    promise_call({
      url: "../api/leavelog/change_upper_user", 
      data: {
        "apply_id": apply_id,
        "apply_process_id": apply_process_id,
        "user_NO": user_NO,
        "reason": $(".confirm_reason").val(),
        "login_user_no": login_user_no
      }, 
      method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
          $("#upper_user_select_"+apply_process_id).val(user_NO).trigger("change");
          $('#changeModal').modal('toggle');
        } else {
          alert(v.message);
        }
    })
    //console.log(apply_process_id, $("#upper_user_select_"+apply_process_id).val());
  }

  const confirm_change_agent_user = (tag_name, apply_id, old_agent_user_no, old_agent_user_cname, login_user_no) => {
    const new_cname = $("#"+tag_name).select2('data')[0].text;
    $('#changeModal').modal('toggle');
    let html = "<div class='row'>";
        html += "<label class='col-form-label col-md-3'>訊息:</label>";
        html += "<label class='col-form-label' style='width: 72%;'>";
        html += "確定要將<strong class='text-success'>代理人</strong>";
        html += "從<strong class='text-success'>"+old_agent_user_cname+"</strong>";
        html += "換成<strong class='text-success'>"+new_cname+"</strong>嗎";
        html += "</label>";
        html += "</div>";
        html += "<div class='row'>";
        html += "<label class='col-form-label col-md-3'>說明:</label>";
        html += "<input type='text' class='col-md-9 form-control confirm_reason'>";
        html += "</div>";
    $('#changeModal').find('.msg').html(html);
    $("#changeModal").find(".todo").attr("onclick", "change_agent_user('"+tag_name+"', '"+apply_id+"', '"+old_agent_user_no+"', '"+login_user_no+"')");
    $("#changeModal").find(".tocancel").attr("onclick", "cancel_change_agent_user('"+apply_id+"', '"+old_agent_user_no+"')");
  }

  const cancel_change_agent_user = (apply_id, old_agent_user_no) => {
    $("#agent_user_select_"+apply_id).val(old_agent_user_no).trigger("change");
    $('#changeModal').modal('toggle');
  }

  const change_agent_user = (tag_name, apply_id, old_agent_user_no, login_user_no) => {
    const user_no = $("#"+tag_name).val();
    promise_call({
      url: "../api/leavelog/change_agent_user", 
      data: {
        "apply_id": apply_id,
        "user_NO": user_no,
        "reason": $(".confirm_reason").val(),
        "login_user_no": login_user_no
      }, 
      method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
          $("#agent_user_select_"+apply_id).val(user_no).trigger("change");
          $('#changeModal').modal('toggle');
        } else {
          $("#agent_user_select_"+apply_id).val(old_agent_user_no).trigger("change");
          $('#changeModal').modal('toggle');
          alert(v.message);
        }
    })
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

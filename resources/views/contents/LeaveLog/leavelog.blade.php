@extends('contents.LeaveLog.master')
@section('content2')
<style>
  .date-input {
    padding: 1px !important;
    width: 235px !important;
  }
  .overwork-date {
    width: 65% !important;
    display: inline;
  }
  .overwork-hour {
    width: 31% !important;
    display: inline;
    margin-top: 1px;
    height: 30px !important;
  }
</style>
<div class="container-fluid pt-lg-4">
  <form>
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="帳號或Email">
        </div>
        <div class="col-auto">
          <button type="button" class="btn-c" value="{{ $search }}">搜尋</button>
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
                <select class="blade_select2" id='agent_user_select_{{$log->id}}' onchange='confirm_change_agent_user({{$log->id}}, {{$log->agent_user_no}}, "{{$log->agent_cname}}", {{$login_user_no}})'>
                  @foreach($agents as $a)
                    @if ($a->cname == $log->agent_cname) 
                      <option value='{{$a->NO}}' selected> {{$a->cname}}</option>
                    @else
                      <option value='{{$a->NO}}'> {{$a->cname}}</option>
                    @endif
                  @endforeach
                </select>
              @else
                -
              @endif
            </td>
            <td> 
              @if ($log->apply_type == 'L')
                {{$log->leave_name}} ({{$log->leave_hours}}小時)
              @else
                加班 ({{$log->over_work_hours}}小時)
              @endif
            </td>
            <td> 
              @if ($log->apply_type == 'L')
                <input type="datetime-local" id='leave_start_date_{{$log->id}}' class="form-control date-input" value="{{$log->start_date}}" onfocusout='confirm_change_date("leave_start_date", {{$log->id}}, "{{$log->start_date}}", "{{$log->cname}}", {{$login_user_no}})'>
              @else
                <input type="date" id='overwork_date_{{$log->id}}' class="form-control date-input overwork-date" value="{{$log->over_work_date}}" onfocusout='confirm_change_date("overwork_date", {{$log->id}}, "{{$log->over_work_date}}", "{{$log->cname}}", {{$login_user_no}})'>
                <select class="form-control overwork-hour" id='overwork_hour_{{$log->id}}' onchange='confirm_change_date("overwork_hour", {{$log->id}}, "{{$log->over_work_hours}}", "{{$log->cname}}", {{$login_user_no}})'>
                  <option value="1" @if ($log->over_work_hours == '1') selected @endif>1小時</option>
                  <option value="2" @if ($log->over_work_hours == '2') selected @endif>2小時</option>
                  <option value="3" @if ($log->over_work_hours == '3') selected @endif>3小時</option>
                  <option value="4" @if ($log->over_work_hours == '4') selected @endif>4小時</option>
                  <option value="5" @if ($log->over_work_hours == '5') selected @endif>5小時</option>
                  <option value="6" @if ($log->over_work_hours == '6') selected @endif>6小時</option>
                  <option value="7" @if ($log->over_work_hours == '7') selected @endif>7小時</option>
                  <option value="8" @if ($log->over_work_hours == '8') selected @endif>8小時</option>
                </select>
              @endif
            </td>
            <td>
              @if ($log->apply_type == 'L') 
                <input type="datetime-local" id='leave_end_date_{{$log->id}}' class="form-control date-input" value="{{$log->end_date}}" onfocusout='confirm_change_date("leave_end_date", {{$log->id}}, "{{$log->end_date}}", "{{$log->cname}}", {{$login_user_no}})'>
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
              @elseif ($log->apply_status == 'C')
                  已取消
              @else
                  簽核中
              @endif
            </td>
            <td>  
             <button type="button" class="btn btn-outline-primary btn-sm" onclick="showDetailModal({{$log->id}}, {{$login_user_no}})">詳細</button>
             <button type="button" class="btn btn-outline-primary btn-sm" onclick="showChangeLogModal({{$log->id}})">修改紀錄</button>
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
          <a class="page-link" href="./last?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./last?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./last?page={{ $page+1 }}">下一頁</a>
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
                      <th style="width: 13%">簽核順位</th>
                      <th style="width: 29%">簽核人</th>
                      <th style="width: 13%">簽核狀態</th>
                      <th style="width: 20%">拒絕原因</th>
                      <th style="width: 25%">簽核時間</th>
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
                  <tr><td colspan="3">無資料</td></tr>
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

<script>

const showChangeLogModal = async (apply_id) => {
    const res = await get_apply_changelog(apply_id);
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

const showDetailModal = async (apply_id, login_user_no) => {
    const users_res = await get_all_user();
    let all_users = [];
    if(users_res.status == "successful") {
      all_users = users_res.data.map(item => {
        item.id = item.NO;
        item.text = item.cname;
        return item;
      })
    }
    
    const res = await get_apply_path(apply_id);
    if(res.status == "successful") {
        if(res.data.length > 0) $("#log_data").html("");
        res.data.map( (item, index) => {
            let html = "<tr>";
            html += "<td>"+(index+1)+"</td>";
            //html += "<td><select id='upper_user_select_"+item.id+"'></select></td>";
            if(item.is_validate === 1) {
                html += "<td>"+item.cname+"</td>";
                html += "<td>同意</td>";
            } else if(item.is_validate === 0){
                html += "<td>"+item.cname+"</td>";
                html += "<td>拒絕</td>";
            } else {
                html += "<td><select id='upper_user_select_"+item.id+"' onchange='confirm_change_upper_user("+item.apply_id+", "+item.id+", "+item.upper_user_no+", \""+item.cname+"\", "+login_user_no+")'></select></td>";
                html += "<td>待簽核</td>";
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

const get_apply_path = (apply_id) => {
    return promise_call({
        url: "../api/leavelog/"+apply_id, 
        method: "get"
    })
}

const get_apply_changelog = (apply_id) => {
    return promise_call({
        url: "../api/leavelog/changelog/"+apply_id, 
        method: "get"
    })
}

const get_all_user = () => {
    return promise_call({
        url: "../api/userlist", 
        method: "get"
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

const confirm_change_agent_user = (apply_id, old_agent_user_no, old_agent_user_cname, login_user_no) => {
  const new_cname = $("#agent_user_select_"+apply_id).select2('data')[0].text;
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
  $("#changeModal").find(".todo").attr("onclick", "change_agent_user('"+apply_id+"', '"+old_agent_user_no+"', '"+login_user_no+"')");
  $("#changeModal").find(".tocancel").attr("onclick", "cancel_change_agent_user('"+apply_id+"', '"+old_agent_user_no+"')");
}

const cancel_change_agent_user = (apply_id, old_agent_user_no) => {
  $("#agent_user_select_"+apply_id).val(old_agent_user_no).trigger("change");
  $('#changeModal').modal('toggle');
}

const change_agent_user = (apply_id, old_agent_user_no, login_user_no) => {
  const user_NO = $("#agent_user_select_"+apply_id).val();
  promise_call({
    url: "../api/leavelog/change_agent_user", 
    data: {
      "apply_id": apply_id,
      "user_NO": user_NO,
      "reason": $(".confirm_reason").val(),
      "login_user_no": login_user_no
    }, 
    method: "put"
  })
  .then(v => {
      if(v.status == "successful") {
        $("#agent_user_select_"+apply_id).val(user_NO).trigger("change");
        $('#changeModal').modal('toggle');
      } else {
        $("#agent_user_select_"+apply_id).val(old_agent_user_no).trigger("change");
        $('#changeModal').modal('toggle');
        alert(v.message);
      }
  })
}

const confirm_change_date = (type, apply_id, old_date, cname, login_user_no) => {
  const new_date = $("#"+type+"_"+apply_id).val().replace('T',' ');
  let html = "<div class='row'>";
      html += "<label class='col-form-label col-md-3'>訊息:</label>";
      html += "<label class='col-form-label' style='width: 72%;'>";
  if(type == "leave_start_date") html += "確定要將<strong class='text-success'>"+cname+"</strong>的休假開始時間從 ";
  if(type == "leave_end_date") html += "確定要將<strong class='text-success'>"+cname+"</strong>的休假結束時間從 ";
  if(type == "overwork_date") html += "確定要將<strong class='text-success'>"+cname+"</strong>的加班日期從 ";
  if(type == "overwork_hour") html += "確定要將<strong class='text-success'>"+cname+"</strong>的加班小時從 ";
  html += "</br>"+old_date.replace('T',' ')+"換成</br>"+new_date+"嗎?";
  html += "</label>";
  html += "</div>";
  html += "<div class='row'>";
  html += "<label class='col-form-label col-md-3'>說明:</label>";
  html += "<input type='text' class='col-md-9 form-control confirm_reason'>";
  html += "</div>";
  $('#changeModal').modal('toggle');
  $('#changeModal').find('.msg').html(html);
  $("#changeModal").find(".todo").attr("onclick", "change_date('"+type+"', '"+apply_id+"', '"+old_date+"', '"+login_user_no+"')");
  $("#changeModal").find(".tocancel").attr("onclick", "cancel_change_date('"+type+"', '"+apply_id+"', '"+old_date+"')");
}

const cancel_change_date = (type, apply_id, old_date) => {
  $("#"+type+"_"+apply_id).val(old_date);
  $('#changeModal').modal('toggle');
}

const change_date = (type, apply_id, old_date, login_user_no) => {
  const new_date = $("#"+type+"_"+apply_id).val();
  promise_call({
    url: "../api/leavelog/change_date", 
    data: {
      "apply_id": apply_id,
      "type": type,
      "new_date": new_date,
      "reason": $(".confirm_reason").val(),
      "login_user_no": login_user_no
    }, 
    method: "put"
  })
  .then(v => {
      if(v.status == "successful") {
        $('#changeModal').modal('toggle');
      } else {
        $("#"+type+"_"+apply_id).val(old_date);
        $('#changeModal').modal('toggle');
        alert(v.message);
      }
  })
}

window.onload = function() {
  $('.blade_select2').select2();
};

</script>
@endsection

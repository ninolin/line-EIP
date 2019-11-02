@extends('contents.WebPersonalOperate.master')
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
  <nav>
    <div class="nav nav-tabs" id="nav-tab" role="tablist">
      <a class="nav-item nav-link @if ($show_tab === 'unvalidate_apply') active @endif" id="nav-unvalidate-tab" data-toggle="tab" href="#nav-unvalidate" role="tab" aria-controls="nav-unvalidate" aria-selected="false">待簽核</a>
      <a class="nav-item nav-link @if ($show_tab === 'validate_apply') active @endif" id="nav-validate-tab" data-toggle="tab" href="#nav-validate" role="tab" aria-controls="nav-validate" aria-selected="true">已簽核</a>
    </div>
  </nav>
  <div class="tab-content" id="nav-tabContent">
    <div class="tab-pane fade @if ($show_tab === 'unvalidate_apply') show active @endif" id="nav-unvalidate" role="tabpanel" aria-labelledby="nav-unvalidate-tab">
        <div class="row p-lg-3">
          <table class="table table-bordered table-striped">
            <thead class="table-thead">
                <tr>
                  <th scope="col" style="width: 150px;">申請人</th>
                  <th scope="col" style="width: 150px;">代理人</th>
                  <th scope="col" style="width: 170px;">假別</th>
                  <th scope="col" style="width: 170px;">起</th>
                  <th scope="col" style="width: 170px;">迄</th>
                  <th scope="col">備註</th>
                  <th scope="col" style="width: 170px;">申請日</th>
                  <th scope="col" style="width: 75px;">狀態</th>
                  <th scope="col" style="width: 75px;"></th>
                </tr>
            </thead>
            <tbody>
                @if (count($unvalidate_apply) > 0) 
                  @foreach($unvalidate_apply as $leave)
                    <tr>
                      <td> {{$leave->cname}} </td>
                      <td> 
                        @if ($leave->apply_type === 'L')
                          {{$leave->agent_cname}} 
                        @else
                          -
                        @endif
                      </td>
                      <td> 
                        @if ($leave->apply_type === 'L')
                          {{$leave->leave_name}} ({{$leave->leave_hours}}小時)
                        @else
                          加班 ({{$leave->over_work_hours}}小時)
                        @endif
                      </td>
                      <td> 
                        @if ($leave->apply_type === 'L')
                          {{strftime('%Y-%m-%d %H:%M', strtotime($leave->start_date))}}
                        @else
                          {{strftime('%Y-%m-%d', strtotime($leave->over_work_date))}}
                        @endif
                      </td>
                      <td> 
                        @if ($leave->apply_type === 'L')
                          {{strftime('%Y-%m-%d %H:%M', strtotime($leave->end_date))}} 
                        @else
                          -
                        @endif
                      </td>
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
                            <a class="dropdown-item" href="#" onclick="showDetailModal({{$leave->id}})">簽核紀錄</a>
                            <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$leave->id}})">更新紀錄</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-success" href="#" onclick="showValidateModal({{$leave->id}}, {{$leave->process_id}}, 'accept')">同意簽核</a>
                            <a class="dropdown-item text-danger" href="#" onclick="showValidateModal({{$leave->id}}, {{$leave->process_id}}, 'reject')">拒絕簽核</a>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                @else
                  <tr><td class="text-center" colspan="9">目前無資料</td></tr>
                @endif
            </tbody>
          </table>
        </div>
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
              <li class="page-item @if ($unvalidate_apply_page == 1) disabled @endif ">
                <a class="page-link" href="./validate?show_tab=unvalidate_apply&validate_apply_page={{ $validate_apply_page }}&unvalidate_apply_page={{ $unvalidate_apply_page-1 }}">上一頁</a>
              </li>
              @for ($i = 1; $i <= $unvalidate_apply_t_pages; $i++)
                <li class="page-item @if ($i == $unvalidate_apply_page) active @endif"><a class="page-link" href="./validate?show_tab=unvalidate_apply&validate_apply_page={{ $validate_apply_page }}&unvalidate_apply_page={{ $i }}">{{$i}}</a></li>
              @endfor
              <li class="page-item @if ($unvalidate_apply_page == $unvalidate_apply_t_pages) disabled @endif">
                <a class="page-link" href="./validate?show_tab=unvalidate_apply&validate_apply_page={{ $validate_apply_page }}&unvalidate_apply_page={{ $unvalidate_apply_page+1 }}">下一頁</a>
              </li>
            </ul>
          </div>
        </div>
    </div>
    <div class="tab-pane fade @if ($show_tab === 'validate_apply') show active @endif" id="nav-validate" role="tabpanel" aria-labelledby="nav-validate-tab">
        <div class="row p-lg-3">
          <table class="table table-bordered table-striped">
            <thead class="table-thead">
                <tr>
                  <th scope="col" style="width: 150px;">申請人</th>
                  <th scope="col" style="width: 150px;">代理人</th>
                  <th scope="col" style="width: 170px;">假別</th>
                  <th scope="col" style="width: 170px;">起</th>
                  <th scope="col" style="width: 170px;">迄</th>
                  <th scope="col">備註</th>
                  <th scope="col" style="width: 170px;">申請日</th>
                  <th scope="col" style="width: 75px;">狀態</th>
                  <th scope="col" style="width: 75px;"></th>
                </tr>
            </thead>
            <tbody>
              @if (count($validate_apply) > 0) 
                @foreach($validate_apply as $leave)
                  <tr>
                    <td> {{$leave->cname}} </td>
                    <td> 
                      @if ($leave->apply_type === 'L')
                        {{$leave->agent_cname}} 
                      @else
                        -
                      @endif
                    </td>
                    <td> 
                      @if ($leave->apply_type === 'L')
                        {{$leave->leave_name}} ({{$leave->leave_hours}}小時)
                      @else
                        加班 ({{$leave->over_work_hours}}小時)
                      @endif
                    </td>
                    <td> 
                      @if ($leave->apply_type === 'L')
                        {{strftime('%Y-%m-%d %H:%M', strtotime($leave->start_date))}}
                      @else
                        {{strftime('%Y-%m-%d', strtotime($leave->over_work_date))}}
                      @endif
                    </td>
                    <td> 
                      @if ($leave->apply_type === 'L')
                        {{strftime('%Y-%m-%d %H:%M', strtotime($leave->end_date))}} 
                      @else
                        -
                      @endif
                    </td>
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
                          <a class="dropdown-item" href="#" onclick="showDetailModal({{$leave->id}})">簽核紀錄</a>
                          <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$leave->id}})">更新紀錄</a>
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr><td class="text-center" colspan="9">目前無資料</td></tr>
              @endif
            </tbody>
          </table>
        </div>
        <div class="row">
          <div class="col-md-6 offset-md-3">
            <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
              <li class="page-item @if ($validate_apply_page == 1) disabled @endif ">
                <a class="page-link" href="./validate?show_tab=validate_apply&validate_apply_page={{ $validate_apply_page-1 }}&unvalidate_apply_page={{ $unvalidate_apply_page }}">上一頁</a>
              </li>
              @for ($i = 1; $i <= $validate_apply_t_pages; $i++)
                <li class="page-item @if ($i == $validate_apply_page) active @endif"><a class="page-link" href="./validate?show_tab=validate_apply&validate_apply_page={{ $i }}&unvalidate_apply_page={{ $unvalidate_apply_page }}">{{$i}}</a></li>
              @endfor
              <li class="page-item @if ($validate_apply_page == $validate_apply_t_pages) disabled @endif">
                <a class="page-link" href="./validate?show_tab=validate_apply&validate_apply_page={{ $validate_apply_page+1 }}&unvalidate_apply_page={{ $unvalidate_apply_page }}">下一頁</a>
              </li>
            </ul>
          </div>
        </div>
    </div>
  </div>  
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
<div class="modal fade" id="validateModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-leave">簽核</h5>
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
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo">同意</button>
      </div>
    </div>
  </div>
</div>
<div id="useridfield" style="display:none">{{$login_user_no}}</div>
<script>

  const showDetailModal = async (apply_id) => {
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

  const showValidateModal = async (apply_id, process_id, type) => {
    const res = await promise_call({
          url: "../api/applyleave/"+apply_id, 
          method: "get"
    });
    if(res.status == "successful") {
        if(res.data.length > 0) $("#validateModal").find(".msg").html("");
        const apply = res.data[0];
        let html = '<table class="table table-bordered table-striped">';
            html += '   <tr><td>申請人</td><td>'+apply.cname+'</td></tr>';
            if(apply.apply_type == 'L') {
              html += '   <tr><td>代理人</td><td>'+apply.agent_cname+'</td></tr>';
              html += '   <tr><td>假別</td><td>'+apply.leave_name+'('+apply.leave_hours+'小時)</td></tr>';
              html += '   <tr><td>起</td><td>'+apply.start_date+'</td></tr>';
              html += '   <tr><td>迄</td><td>'+apply.end_date+'</td></tr>';
            } else {
              html += '   <tr><td>假別</td><td>加班('+apply.over_work_hours+'小時)</td></tr>';
              html += '   <tr><td>日期</td><td>'+apply.over_work_date+')</td></tr>';
            }
            html += '   <tr><td>備註</td><td>'+apply.comment+'</td></tr>';
            html += '   <tr><td>申請日</td><td>'+apply.apply_time+'</td></tr>';
            if(type == 'reject') {
              html += '   <tr><td>拒絕原因</td><td><textarea class="form-control rounded-0 reject_reason" rows="3"></textarea></td></tr>';
              $("#validateModal").find(".todo").attr("onclick", "validate_leave('"+process_id+"', '"+apply_id+"','"+apply.apply_type+"', 0)");
              $("#validateModal").find(".todo").addClass("btn-danger");
              $("#validateModal").find(".todo").html("拒絕");
            } else {
              $("#validateModal").find(".todo").attr("onclick", "validate_leave('"+process_id+"', '"+apply_id+"','"+apply.apply_type+"', 1)");
              $("#validateModal").find(".todo").addClass("btn-primary");
              $("#validateModal").find(".todo").html("同意");
            }
            html += "</table>";
            $("#validateModal").find(".msg").html(html);
            
        $('#validateModal').modal('toggle');
    } else {
      alert("找不到申請紀錄");
    }
  }

  const validate_leave = (process_id, apply_id, apply_type, is_validate) => {

    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "is_validate": is_validate, //0=reject or 1=agree
        "apply_type": apply_type,   //L or O
        "process_id": process_id,   //apply_process_id
        "use_mode": 'web'
    }
    if(is_validate == 0) {
      post_data.reject_reason = $("#validateModal").find(".reject_reason").val();
    } 

    promise_call({
        url: "../api/validateleave/"+apply_id, 
        data: post_data, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
          window.location.reload();
          //$('#validateModal').modal('toggle');
        } 
    })
  }
</script>
@endsection

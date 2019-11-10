@extends('contents.WorkManage.master')
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
  <form id="search_form" method="GET" action="{{ route('wm_individual') }}">
    {{ csrf_field() }}
    <div class="row">
      <div class="col-sm-12 form-row">
        <div class="col-auto">
          <select name="leave_year" class="browser-default custom-select">
            <option value="2019" @if ($leave_year == '2019') selected @endif>2019</option>
            <option value="2020" @if ($leave_year == '2020') selected @endif>2020</option>
            <option value="2021" @if ($leave_year == '2021') selected @endif>2021</option>
            <option value="2022" @if ($leave_year == '2022') selected @endif>2022</option>
          </select>
        </div>
        <div class="col-auto">
          <input name="search" type="text" class="form-control" placeholder="帳號或Email" value="{{ $search }}">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn-c">搜尋</button>
        </div>
        <div class="col-auto" @if ($user_no == 0) style="display:none" @endif>
          <button type="button" class="btn-c" onclick="showExportModal()">匯出{{$cname}}的工時</button>
        </div>
        <div class="col-auto" >
          <button type="button" class="btn-c" onclick="javascript:location.href='{{route('exportLastMonthExcel')}}'">匯出上月全部工時</button>
        </div>
      </div>
    </div>
  </form>
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
      <a class="nav-item nav-link @if ($show_tab === 'leave') active @endif" id="nav-leave-tab" data-toggle="tab" href="#nav-leave" role="tab" aria-controls="nav-leave" aria-selected="true">休假</a>
      <a class="nav-item nav-link @if ($show_tab === 'overwork') active @endif" id="nav-overwork-tab" data-toggle="tab" href="#nav-overwork" role="tab" aria-controls="nav-overwork" aria-selected="false">加班</a>
      <a class="nav-item nav-link @if ($show_tab === 'agent') active @endif" id="nav-agent-tab" data-toggle="tab" href="#nav-agent" role="tab" aria-controls="nav-agent" aria-selected="false">代理人</a>
    </div>
  </nav>
  <div class="tab-content" id="nav-tabContent" @if ($user_no == 0) style="display:none" @endif>
    <div class="tab-pane fade @if ($show_tab === 'leave') show active @endif" id="nav-leave" role="tabpanel" aria-labelledby="nav-leave-tab">
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
            @if (count($leaves) > 0)
              @foreach($leaves as $leave)
                <tr>
                  <td> {{$leave->cname}} </td>
                  <td> 
                    @if (strtotime($leave->start_date) >= strtotime(date('Y-m-01')))
                      <select class="blade_select2" id='leave_agent_user_select_{{$leave->id}}' onchange='confirm_change_agent_user("leave_agent_user_select_{{$leave->id}}", {{$leave->id}}, {{$leave->agent_user_no}}, "{{$leave->agent_cname}}", {{$login_user_no}})'>
                        @foreach($users as $u)
                          <option value='{{$u->NO}}' @if ($u->cname == $leave->agent_cname) selected @endif> {{$u->cname}}</option>
                        @endforeach
                      </select>
                    @else
                      {{$leave->agent_cname}}
                    @endif
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
                        @if (strtotime($leave->start_date) >= strtotime(date('Y-m-01')))
                          <a class="dropdown-item" href="#" onclick="showDetailModal({{$leave->id}}, {{$login_user_no}}, 'L', true)">簽核紀錄</a>
                        @else
                          <a class="dropdown-item" href="#" onclick="showDetailModal({{$leave->id}}, {{$login_user_no}}, 'L', false)">簽核紀錄</a>
                        @endif
                        <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$leave->id}})">更新紀錄</a>
                        @if ($leave->apply_status != 'N' && $leave->apply_status != 'C' && strtotime($leave->start_date) >= strtotime(date('Y-m-01'))) 
                          <a class="dropdown-item" href="#" onclick="showChangeLeaveDateModal({{$leave->id}}, {{$login_user_no}})">更新起迄</a>
                        @endif
                      </div>
                    </div>
                  </td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan=9 class="text-center">目前無資料</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
      <div class="row">
        <div class="col-md-6 offset-md-3">
          <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center">
            <li class="page-item @if ($leaves_page == 1) disabled @endif ">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=leave&search={{$search}}&leaves_page={{ $leaves_page-1 }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $leaves_t_pages; $i++)
              <li class="page-item @if ($i == $leaves_page) active @endif"><a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=leave&search={{$search}}&leaves_page={{ $i }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($leaves_page == $leaves_t_pages) disabled @endif">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=leave&search={{$search}}&leaves_page={{ $leaves_page+1 }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page }}">下一頁</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="tab-pane fade @if ($show_tab === 'overwork') show active @endif" id="nav-overwork" role="tabpanel" aria-labelledby="nav-overwork-tab">
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
              @if (count($overworks) > 0)
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
                          @if (strtotime($overwork->over_work_date) >= strtotime(date('Y-m-01')))
                            <a class="dropdown-item" href="#" onclick="showDetailModal({{$overwork->id}}, {{$login_user_no}}, 'O', true)">簽核紀錄</a>
                          @else
                            <a class="dropdown-item" href="#" onclick="showDetailModal({{$overwork->id}}, {{$login_user_no}}, 'O', false)">簽核紀錄</a>
                          @endif
                          <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$overwork->id}})">更新紀錄</a>
                          @if ($overwork->apply_status != 'N' && $overwork->apply_status != 'C' && strtotime($overwork->over_work_date) >= strtotime(date('Y-m-01'))) 
                            <a class="dropdown-item" href="#" onclick="showChangeOverworkDateModal({{$overwork->id}}, {{$login_user_no}})">更新起迄</a>
                          @endif
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              @else
                <tr>
                  <td colspan=7 class="text-center">目前無資料</td>
                </tr>
              @endif
            </tbody>
          </table>
      </div>
      <div class="row">
        <div class="col-md-6 offset-md-3">
          <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center">
            <li class="page-item @if ($overworks_page == 1) disabled @endif ">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=overwork&search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page-1 }}&agents_page={{ $agents_page }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $overworks_t_pages; $i++)
              <li class="page-item @if ($i == $overworks_page) active @endif"><a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=overwork&search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $i }}&agents_page={{ $agents_page }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($overworks_page == $overworks_t_pages) disabled @endif">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=overwork&search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page+1 }}&agents_page={{ $agents_page }}">下一頁</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="tab-pane fade @if ($show_tab === 'agent') show active @endif" id="nav-agent" role="tabpanel" aria-labelledby="nav-agent-tab">
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
            @if (count($agents) > 0)
              @foreach($agents as $agent)
                <tr>
                  <td> {{$agent->cname}} </td>
                  <td> 
                    @if (strtotime($agent->start_date) >= strtotime(date('Y-m-01')))
                      <select class="blade_select2" id='agent_agent_user_select_{{$agent->id}}' onchange='confirm_change_agent_user("agent_agent_user_select_{{$agent->id}}", {{$agent->id}}, {{$agent->agent_user_no}}, "{{$agent->agent_cname}}", {{$login_user_no}})'>
                        @foreach($users as $u)
                          @if ($u->cname == $agent->agent_cname) 
                            <option value='{{$u->NO}}' selected> {{$u->cname}}</option>
                          @else
                            <option value='{{$u->NO}}'> {{$u->cname}}</option>
                          @endif
                        @endforeach
                      </select>
                    @else
                      {{ $agent->agent_cname }}
                    @endif
                  </td>
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
                        @if (strtotime($agent->start_date) >= strtotime(date('Y-m-01')))
                          <a class="dropdown-item" href="#" onclick="showDetailModal({{$agent->id}}, {{$login_user_no}}, '{{$agent->apply_type}}', true)">簽核紀錄</a>
                        @else
                          <a class="dropdown-item" href="#" onclick="showDetailModal({{$agent->id}}, {{$login_user_no}}, '{{$agent->apply_type}}', false)">簽核紀錄</a>
                        @endif  
                        <a class="dropdown-item" href="#" onclick="showChangeLogModal({{$agent->id}})">更新紀錄</a>
                      </div>
                    </div>
                  </td>
                </tr>
              @endforeach
            @else
              <tr>
                <td colspan=9 class="text-center">目前無資料</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
      <div class="row">
        <div class="col-md-6 offset-md-3">
          <nav aria-label="Page navigation example">
          <ul class="pagination justify-content-center">
            <li class="page-item @if ($agents_page == 1) disabled @endif ">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=agent&search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page-1 }}">上一頁</a>
            </li>
            @for ($i = 1; $i <= $agents_t_pages; $i++)
              <li class="page-item @if ($i == $agents_page) active @endif"><a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=agent&search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page }}&agents_page={{ $i }}">{{$i}}</a></li>
            @endfor
            <li class="page-item @if ($agents_page == $agents_t_pages) disabled @endif">
              <a class="page-link" href="./individual?leave_year={{$leave_year}}&show_tab=agent&search={{$search}}&leaves_page={{ $leaves_page }}&overworks_page={{ $overworks_page }}&agents_page={{ $agents_page+1 }}">下一頁</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="row p-lg-3" @if ($user_no != 0 || $search == '') style="display:none" @endif>查無此用戶</div>
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
<div class="modal fade" id="changelogModal" tabindex="-1" role="dialog" aria-hidden="true">
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
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">匯出工時紀錄</h5>
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
                      <th class="text-center">匯出條件</th>
                      <th class="text-center">條件值</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-center">工時起</td>
                    <td>
                      <input type="date" style="width:300px" class="form-control date-input export_startdate" value="">
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center">工時迄</td>
                    <td>
                      <input type="date" style="width:300px" class="form-control date-input export_enddate" value="">
                    </td>
                  </tr>
                  <tr>
                    <td class="text-center">匯出假別</td>
                    <td>
                      <select id="levae_type_select" style="width:300px" name="states[]" multiple="multiple"></select>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
        <button type="button" class="btn btn-primary" onclick="exportExcel({{$user_no}})">匯出</button>
      </div>
    </div>
  </div>
</div>
<script>

  const reload_page = () => {
    $("#search_form").attr("action", "./userlist");
    $("#search_form").submit();
  }

  const showDetailModal = async (apply_id, login_user_no, apply_type, can_update) => {
      const users_res = await promise_call({
        url: "../../api/userlist", 
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
          url: "../../api/leavelog/process/"+apply_id, 
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
                if(can_update) {
                  html += "<td><select id='upper_user_select_"+item.id+"' onchange='confirm_change_upper_user("+item.apply_id+", "+item.id+", "+item.upper_user_no+", \""+item.cname+"\", "+login_user_no+", \""+apply_type+"\")'></select></td>";
                } else {
                  html += "<td>"+item.cname+"</td>";
                }
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
      url: "../../api/leavelog/change_leave_date", 
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
          let url_str = location.href.toString();
          location.href = url_str.substring(0,url_str.length-1)+ "&show_tab=leave";
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
      url: "../../api/leavelog/change_overwork_date", 
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
          let url_str = location.href.toString();
          location.href = url_str.substring(0,url_str.length-1)+ "&show_tab=overwork";
          $('#changeOverworkDateModal').modal('toggle');
        } else {
          alert(v.message);
        }
    })
  }

  const confirm_change_upper_user = (apply_id, apply_process_id, old_upper_user_no, old_upper_user_cname, login_user_no, apply_type) => {
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

      $("#changeModal").find(".todo").attr("onclick", "change_upper_user('"+apply_id+"', '"+apply_process_id+"', '"+login_user_no+"', '"+apply_type+"')");
      $("#changeModal").find(".tocancel").attr("onclick", "cancel_change_upper_user('"+apply_process_id+"', '"+old_upper_user_no+"')");
    }
  }

  const cancel_change_upper_user = (apply_process_id, old_upper_user_no) => {
    $("#upper_user_select_"+apply_process_id).val(old_upper_user_no).trigger("change");
    $('#changeModal').modal('toggle');
  }

  const change_upper_user = (apply_id, apply_process_id, login_user_no, apply_type) => {
    const user_NO = $("#upper_user_select_"+apply_process_id).val();
    promise_call({
      url: "../../api/leavelog/change_upper_user", 
      data: {
        "apply_id": apply_id,
        "apply_type": apply_type, 
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
      url: "../../api/leavelog/change_agent_user", 
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
          url: "../../api/leavelog/changelog/"+apply_id, 
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
        url: "../../api/applyleave/"+apply_id, 
        method: "get"
    })
  }

  const showExportModal = () => {
    promise_call({
      url: "../../api/individuallog/leavetype", 
      method: "get"
    })
    .then(v => {
      if(v.status != 'successful') {
        alert("get data error");
      } else {
        if(v.data.length > 0) $("#levae_type_select").html("");
        v.data.map(item => {     
          $("#levae_type_select").append("<option value='"+item.name+"'>"+item.name+"</option>");
        });
        $("#levae_type_select").append("<option value='加班'>加班</option>");
        $('#levae_type_select').select2();
        const today = new Date();
        let dd = today.getDate();
        let mm = today.getMonth() + 1;
        let yyyy = today.getFullYear();
        if(dd < 10) dd = '0'+dd
        if(mm < 10) mm = '0'+mm
        $('#exportModal').find('.export_startdate').val(yyyy+"-"+mm+"-"+dd);
        if(mm == 12) {
          mm = 01;
          yyyy = yyyy+1;
        } else {
          mm++;
        }
        $('#exportModal').find('.export_enddate').val(yyyy+"-"+mm+"-"+dd);
        $('#exportModal').modal('toggle');     
      }
    })
    
  }

  const exportExcel = (user_no) => {
    const export_startdate = $(".export_startdate").val();
    const export_enddate = $(".export_enddate").val();
    const export_leaves = $('#levae_type_select').val();
    const url_parameter = "?user_no="+user_no+"&export_startdate="+export_startdate+"&export_enddate="+export_enddate+"&export_leaves="+export_leaves;
    location.href='{{route('exportExcel')}}'+url_parameter;
  }
  window.onload = function() {
    $('.blade_select2').select2();
    $('#levae_type_select').select2();
  };
</script>
@endsection

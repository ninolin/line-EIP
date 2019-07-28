@extends('contents.LeaveLog.master')
@section('content2')
<div class="container-fluid pt-lg-4">
  <form id="search_form" method="GET" action="{{ route('ll_individual') }}">
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
      </div>
    </div>
  </form>
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          @foreach($types as $type)
            <th scope="col">{{$type->name}} (小時/天)</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        <tr>
        @foreach($types as $type)
          <td> {{$type->hours}} /  {{$type->days}} </td>
        @endforeach
        </tr>
      </tbody>
    </table>
  </div>
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
                {{$log->leave_name}} ({{$log->leave_hours}}小時)
              @else
                加班 ({{$log->over_work_hours}}小時)
              @endif
            </td>
            <td> 
              @if ($log->apply_type == 'L')
                {{$log->start_date}}
              @else
                {{$log->over_work_date}} 
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
              @elseif ($log->apply_status == 'C')
                  已取消
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
<script>

  const reload_page = () => {
    $("#search_form").attr("action", "./userlist");
    $("#search_form").submit();
  }

const showDetailModal = async (apply_id) => {
    const res = await get_apply_path(apply_id);
    if(res.status == "successful") {
        if(res.data.length > 0) $("#log_data").html("");
        res.data.map( (item, index) => {
            let html = "<tr>";
            html += "<td>"+(index+1)+"</td>";
            html += "<td>"+item.cname+"</td>";
            if(item.is_validate === 1) {
                html += "<td>同意</td>";
            } else if(item.is_validate === 0){
                html += "<td>拒絕</td>";
            } else {
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

const get_apply_path = (apply_id) => {
    return promise_call({
        url: "../api/leavelog/"+apply_id, 
        method: "get"
    })
}
</script>
@endsection

@extends('contents.WorkSetting.master')
@section('content2')
<div class="container-fluid pt-lg-4">
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped table-hover">
      <thead class="table-thead">
        <tr>
          <th scope="col" style="width:20%">事件名稱</th>
          <th scope="col" style="width:20%">通知對象</th>
          <th scope="col" style="width:50%">通知訊息</th>
          <th scope="col" style="width:10%">操作</th>
        </tr>
      </thead>
      <tbody>
        @if(count($messages) === 0) 
          <tr>
            <td class="text-center"> 目前無資料 </td>
          </tr>
        @else
          @foreach($messages as $m)
            <tr>
              <td> {{$m->event_name}} </td>
              <td> 
                @if ($m->hook_user_type == 'apply_user')
                  <p class="text-danger">申請人</p>
                @elseif ($m->hook_user_type == 'validate_user')
                  <p class="text-success">簽核人</p>
                @else
                  <p class="text-info">代理人</p>
                @endif
              </td>
              <td> {{$m->message}} </td>
              <td>  
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showModal('{{$m->id}}', '{{$m->event_name}}', '{{$m->hook_user_type}}', '{{$m->message}}')">修改</button>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  <div class="row">
    <div class="col-md-6 offset-md-3">
      <nav aria-label="Page navigation example">
      <ul class="pagination justify-content-center">
        <li class="page-item @if ($page == 1) disabled @endif ">
          <a class="page-link" href="./linedefaultmsg?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./linedefaultmsg?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./linedefaultmsg?page={{ $page+1 }}">下一頁</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>

<!-- Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">修改訊息</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row form-group">
              <label for="class-name" class="col-form-label col-md-5">事件名稱:</label>
              <label for="class-name" class="col-form-label" id="event-name"></label>
            </div>
            <div class="row form-group">
              <label for="work_start" class="col-form-label col-md-5">通知對象:</label>
              <label for="class-name" class="col-form-label" id="hook-user-type"></label>
            </div>
            <div class="row form-group">
              <label for="lunch_start" class="col-form-label col-md-5">通知訊息:</label>
              <div class="col-7" style="padding: 0px">
                <textarea class="form-control rounded-0" id="message" rows="3"></textarea>
              </div>
            </div>
            <div class="row form-group">
              <label for="lunch_start" class="col-form-label col-md-5">可用變數:</label>
              <div class="col-7" style="padding: 0px">
                <span class="badge badge-primary">申請人(%apply_cname%)</span>
                <span class="badge badge-primary">拒絕原因(%reject_reason%)</span>
                <span class="badge badge-primary">代理人(%agent_cname%)</span>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo" onclick="update_message()">修改</button>
      </div>
    </div>
  </div>
</div>

<script>
  const showModal = (id, event_name, hook_user_type, message) => {
    if(hook_user_type == 'apply_user') {
      hook_user_type = '申請人';
    } else if(hook_user_type == 'validate_user') {
      hook_user_type = '簽核人';
    } else {
      hook_user_type = '代理人';
    }
    $('#messageModal').find("#event-name").html(event_name);
    $('#messageModal').find("#hook-user-type").html(hook_user_type);
    $('#messageModal').find("#message").val(message);
    $('#messageModal').find(".todo").attr("onclick", "update_message('"+id+"')").html("修改");
    $('#messageModal').modal('toggle');
  }

  const update_message = (id) => {
    const data = {
      'message': $('#messageModal').find("#message").val()
    }
    promise_call({
        url: "/api/lineDefaultMsg/"+id,
        data: data,
        method: "put"
      })
      .then(v => {
          if(v.status == "successful") {
            window.location.reload();
          } else {
            alert(v.message);
          }
      })
  }
</script>
@endsection

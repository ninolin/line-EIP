@extends('contents.WorkSetting.master')
@section('content2')
<div class="container-fluid pt-lg-4">
  <form>
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <button type="button" class="btn-c" onclick="showLeaveModal('add')">新增假別</button>
        </div>
      </div>
    </div>
  </form>
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          <th scope="col">假別</th>
          <th scope="col">天數</th>
          <th scope="col">最小請假分鐘</th>
          <th scope="col">簽核職等</th>
          <th scope="col" class="w-25">操作</th>
        </tr>
      </thead>
      <tbody>
        @if(count($types) === 0) 
          <tr>
            <td colspan="4" class="text-center"> 目前無資料 </td>
          </tr>
        @else
          @foreach($types as $type)
            <tr>
              <td> {{$type->name}} </td>
              <td> {{$type->day}} </td>
              <td> {{$type->min_time}} 分鐘</td>
              <td> {{$type->title_name}} </td>
              <td>  
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showLeaveModal('update', '{{$type->id}}', '{{$type->name}}', '{{$type->day}}', '{{$type->title_id}}', '{{$type->min_time}}')">修改</button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="showDeleteModal('{{$type->id}}', '{{$type->name}}', '{{$type->day}}')">刪除</button>
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
          <a class="page-link" href="./titlelist?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./titlelist?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./titlelist?page={{ $page+1 }}">下一頁</a>
        </li>
      </ul>
    </div>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="leaveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-leave">新增假別</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="container-fluid">
            <div class="row form-group">
              <label for="leave-name" class="col-form-label w-25">假別:</label>
              <input type="text" class="form-control w-75 leave-name">
            </div>
            <div class="row form-group">
              <label for="leave-day" class="col-form-label w-25">天數:</label>
              <input type="text" class="form-control w-75 leave-day">
            </div>
            <div class="row form-group">
              <label for="leave-day" class="col-form-label w-25">請假單位:</label>
              <select class="form-control w-75 leave-min-time" id="sel1">
                <option value="30">30分鐘</option>
                <option value="60">60分鐘</option>
                <option value="90">90分鐘</option>
                <option value="120">120分鐘</option>
                <option value="150">150分鐘</option>
                <option value="180">180分鐘</option>
              </select>
            </div>
            <div class="row form-group">
              <label for="title-name" class="col-form-label w-25">簽核職等:</label>
              <div class="col-form-label w-75">
                <select id="title_set_select" class="form-control-lg"></select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo" onclick="add_leave()">新增</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-leave">刪除假別</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="container-fluid">
            <div class="row form-group delete_msg"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-danger todo" onclick="delete_leave()">刪除</button>
      </div>
    </div>
  </div>
</div>

<script>
  const showLeaveModal = async (type, leave_id, leave_name, leave_day, title_id, min_time) => {
    const titles_res = await get_all_title();
    if(titles_res.status == "successful") {
        const all_titles = titles_res.data.map(item => {
            item.text = item.name;
            return item;
        })
        $("#title_set_select").select2({
            dropdownParent: $("#leaveModal"),
            data: all_titles,
            dropdownAutoWidth : false,
            width: '100%'
        })
        if(type == 'add') {
            $("#leaveModal").find(".modal-header h5").html("新增假別");
            $("#leaveModal").find(".leave-name").val("");
            $("#leaveModal").find(".leave-day").val("");
            $("#leaveModal").find(".todo").attr("onclick", "add_leave()").html("新增");
            $('#leaveModal').modal('toggle');
        } else {
            $("#leaveModal").find(".modal-header h5").html("修改假別");
            $("#leaveModal").find(".leave-name").val(leave_name);
            $("#leaveModal").find(".leave-day").val(leave_day);
            $("#leaveModal").find(".leave-min-time").val(min_time);
            $("#title_set_select").val(title_id).trigger("change");
            $("#leaveModal").find(".todo").attr("onclick", "update_leave('"+leave_id+"')").html("修改");
            $('#leaveModal').modal('toggle');
        }
    } else {
        alert("get data error");
    }
  }

  const get_all_title = () => {
      return promise_call({
          url: "/api/title", 
          method: "get"
      })
  }

  const add_leave = () => {
      promise_call({
          url: "/api/leavetype", 
          data: {
              "name": $("#leaveModal").find(".leave-name").val(),
              "day": $("#leaveModal").find(".leave-day").val(),
              "min_time": $("#leaveModal").find(".leave-min-time").val(),
              "approved_title_id": parseInt($("#title_set_select").val())
          }, 
          method: "post"
      })
      .then(v => {
          if(v.status == "successful") {
              window.location.reload();
          } else {
              alert(v.message);
          }
      })
  }

  const update_leave = (leave_id) => {
      promise_call({
          url: "/api/leavetype/"+leave_id, 
          data: {
              "name": $("#leaveModal").find(".leave-name").val(),
              "day": $("#leaveModal").find(".leave-day").val(),
              "min_time": $("#leaveModal").find(".leave-min-time").val(),
              "approved_title_id": parseInt($("#title_set_select").val())
          }, 
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

  const showDeleteModal = async (leave_id, leave_name, leave_day) => {
      $("#deleteModal").find(".todo").attr("onclick", "delete_leave('"+leave_id+"')").html("刪除");
      $("#deleteModal").find(".delete_msg").html("確認要刪除「"+leave_name+leave_day+"天」該假別嗎?");
      $('#deleteModal').modal('toggle');
  }

  const delete_leave = (leave_id) => {
      promise_call({
          url: "/api/leavetype/"+leave_id,
          method: "delete"
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

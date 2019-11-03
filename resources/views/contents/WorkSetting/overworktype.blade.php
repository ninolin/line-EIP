@extends('contents.WorkSetting.master')
@section('content2')
<div class="container-fluid pt-lg-4">
  <form>
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <button type="button" class="btn-c" onclick="showOverworkModal('add')">新增加班</button>
        </div>
      </div>
    </div>
  </form>
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          <th scope="col">小時</th>
          <th scope="col">簽核職等</th>
          <th scope="col" class="w-25">操作</th>
        </tr>
      </thead>
      <tbody>
        @if(count($types) === 0) 
          <tr>
            <td colspan="3" class="text-center"> 目前無資料 </td>
          </tr>
        @else
          @foreach($types as $type)
            <tr>
              <td> {{$type->hour}} </td>
              <td> {{$type->title_name}} </td>
              <td>  
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showOverworkModal('update', '{{$type->id}}', '{{$type->hour}}', '{{$type->title_id}}')">修改</button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="showDeleteModal('{{$type->id}}', '{{$type->hour}}')">刪除</button>
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
          <a class="page-link" href="./overworktype?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./overworktype?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./overworktype?page={{ $page+1 }}">下一頁</a>
        </li>
      </ul>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="overworkModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5>新增加班</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="container-fluid">
            <div class="row form-group">
              <label for="overwork-hour" class="col-form-label w-25">小時:</label>
              <input type="text" class="form-control w-75 overwork-hour">
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
        <button type="button" class="btn btn-primary todo" onclick="add_overwork()">新增</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5>刪除加班</h5>
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
        <button type="button" class="btn btn-danger todo" onclick="delete_overwork()">刪除</button>
      </div>
    </div>
  </div>
</div>

<script>
  const showOverworkModal = async (type, overwork_id, overwork_hour, title_id) => {
      const titles_res = await get_all_title();
      if(titles_res.status == "successful") {
          const all_titles = titles_res.data.map(item => {
              item.text = item.name;
              return item;
          })
          $("#title_set_select").select2({
              dropdownParent: $("#overworkModal"),
              data: all_titles,
              dropdownAutoWidth : false,
              width: '100%'
          })
          if(type == 'add') {
              $("#overworkModal").find(".modal-header h5").html("新增加班");
              $("#overworkModal").find(".overwork-hour").val("");
              $("#overworkModal").find(".todo").attr("onclick", "add_overwork()").html("新增");
              $('#overworkModal').modal('toggle');
          } else {
              $("#overworkModal").find(".modal-header h5").html("修改加班");
              $("#overworkModal").find(".overwork-hour").val(overwork_hour);
              $("#title_set_select").val(title_id).trigger("change");
              $("#overworkModal").find(".todo").attr("onclick", "update_overwork('"+overwork_id+"')").html("修改");
              $('#overworkModal').modal('toggle');
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

  const add_overwork = () => {
      promise_call({
          url: "/api/overworktype", 
          data: {
              "hour": $("#overworkModal").find(".overwork-hour").val(),
              "approved_title_id": $("#title_set_select").val()
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

  const update_overwork = (overwork_id) => {
      promise_call({
          url: "/api/overworktype/"+overwork_id, 
          data: {
              "hour": $("#overworkModal").find(".overwork-hour").val(),
              "approved_title_id": $("#title_set_select").val()
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

  const showDeleteModal = async (overwork_id, overwork_hour) => {
      $("#deleteModal").find(".todo").attr("onclick", "delete_overwork('"+overwork_id+"')").html("刪除");
      $("#deleteModal").find(".delete_msg").html("確認要刪除「"+overwork_hour+"小時」該加班嗎?");
      $('#deleteModal').modal('toggle');
  }

  const delete_overwork = (overwork_id) => {
      promise_call({
          url: "/api/overworktype/"+overwork_id,
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

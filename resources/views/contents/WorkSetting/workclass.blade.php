@extends('contents.WorkSetting.master')
@section('content2')
<div class="container-fluid pt-lg-4">
  <form>
    <div class="row">
      <div class="col-sm-4 form-row">
        <div class="col-auto">
          <button type="button" class="btn-c" onclick="showClassModal('add')">新增班別</button>
        </div>
      </div>
    </div>
  </form>
  <div class="row p-lg-3">
    <table class="table table-bordered table-striped">
      <thead class="table-thead">
        <tr>
          <th scope="col">名稱</th>
          <th scope="col">上班開始時間</th>
          <th scope="col">午休開始時間</th>
          <th scope="col">午休結束時間</th>
          <th scope="col">上班結束時間</th>
          <th scope="col" class="w-15">操作</th>
        </tr>
      </thead>
      <tbody>
        @if(count($classes) === 0) 
          <tr>
            <td class="text-center"> 目前無資料 </td>
          </tr>
        @else
          @foreach($classes as $c)
            <tr>
              <td> {{$c->name}} </td>
              <td> {{$c->work_start}} </td>
              <td> {{$c->lunch_start}} </td>
              <td> {{$c->lunch_end}} </td>
              <td> {{$c->work_end}} </td>
              <td>  
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showClassModal('update', '{{$c->id}}', '{{$c->name}}', '{{$c->work_start}}', '{{$c->lunch_start}}', '{{$c->lunch_end}}', '{{$c->work_end}}')">修改</button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="showDeleteModal({{$c->id}}, '{{$c->name}}')">刪除</button>
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
          <a class="page-link" href="./class?page={{ $page-1 }}">上一頁</a>
        </li>
        @for ($i = 1; $i <= $total_pages; $i++)
          <li class="page-item @if ($i == $page) active @endif"><a class="page-link" href="./class?page={{ $i }}">{{$i}}</a></li>
        @endfor
        <li class="page-item @if ($page == $total_pages) disabled @endif">
          <a class="page-link" href="./class?page={{ $page+1 }}">下一頁</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
</div>

<!-- Modal -->
<div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">新增班別</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          <div class="form-group container-fluid">
            <div class="row form-group">
              <label for="class-name" class="col-form-label col-md-5">名稱:</label>
              <input type="text" class="form-control col-md-7 class-name">
            </div>
            <div class="row form-group">
              <label for="work_start" class="col-form-label col-md-5">上班開始時間:</label>
              <input type="time" class="form-control col-md-7 work_start">
            </div>
            <div class="row form-group">
              <label for="lunch_start" class="col-form-label col-md-5">午休開始時間:</label>
              <input type="time" class="form-control col-md-7 lunch_start">
            </div>
            <div class="row form-group">
              <label for="lunch_end" class="col-form-label col-md-5">午休結束時間:</label>
              <input type="time" class="form-control col-md-7 lunch_end">
            </div>
            <div class="row form-group">
              <label for="work_end" class="col-form-label col-md-5">上班結束時間:</label>
              <input type="time" class="form-control col-md-7 work_end">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button type="button" class="btn btn-primary todo" onclick="add_class()">新增</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-leave">刪除班別</h5>
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
        <button type="button" class="btn btn-danger todo" onclick="delete()">刪除</button>
      </div>
    </div>
  </div>
</div>

<script>
  const showClassModal = (type, id, name, work_start, lunch_start, lunch_end, work_end) => {
    if(type == 'add') {
      $('#classModal').find(".modal-title").html("新增班別");
      $("#classModal").find("form")[0].reset();
      $("#classModal").find(".todo").attr("onclick", "add_class()").html("新增");
    } else {
      $('#classModal').find(".modal-title").html("修改班別");
      $(".class-name").val(name);
      $(".work_start").val(work_start);
      $(".lunch_start").val(lunch_start);
      $(".lunch_end").val(lunch_end);
      $(".work_end").val(work_end);
      $("#classModal").find(".todo").attr("onclick", "update_class('"+id+"')").html("修改");
    }
    $('#classModal').modal('toggle');
  }

  const showDeleteModal = (id, name) => {
    $("#deleteModal").find(".todo").attr("onclick", "delete_class('"+id+"')");
    $("#deleteModal").find(".delete_msg").html("確認要刪除「"+name+"」該班別嗎?");
    $('#deleteModal').modal('toggle');
  }

  const add_class = () => {
    const data = {
      'name': $(".class-name").val(), 
      'work_start': $(".work_start").val(),
      'lunch_start': $(".lunch_start").val(),
      'lunch_end': $(".lunch_end").val(),
      'work_end': $(".work_end").val()
    }
    if(data.lunch_start <= data.work_start) {
      alert("午休開始時間 需大於 上班開始時間");
    } else if(data.lunch_end <= data.lunch_start){
      alert("午休結束時間 需大於 午休開始時間");
    }　else if(data.work_end <= data.lunch_end){
      alert("上班結束時間 需大於 午休結束時間");
    } else {
      promise_call({
        url: "/api/workclass/",
        data: data,
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
  }

  const update_class = (id) => {
    const data = {
      'name': $(".class-name").val(), 
      'work_start': $(".work_start").val(),
      'lunch_start': $(".lunch_start").val(),
      'lunch_end': $(".lunch_end").val(),
      'work_end': $(".work_end").val()
    }
    if(data.lunch_start <= data.work_start) {
      alert("午休開始時間 需大於 上班開始時間");
    } else if(data.lunch_end <= data.lunch_start){
      alert("午休結束時間 需大於 午休開始時間");
    }　else if(data.work_end <= data.lunch_end){
      alert("上班結束時間 需大於 午休結束時間");
    } else {
      promise_call({
        url: "/api/workclass/"+id,
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
  }

  const delete_class = (id) => {
    promise_call({
        url: "/api/workclass/"+id,
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

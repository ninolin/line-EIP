@extends('contents.WorkManage.master')
@section('title', 'Home')
@section('content2')
<div class="container-fluid pt-lg-4">
    <form>
        <div class="form-group">
            <label>申請人</label>
            <select class="form-control" id="applyUser" onchange="get_user_date()">
                @foreach($users as $user)
                    <option value="{{$user->NO}}">{{$user->cname}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>假別</label>
            <select class="form-control" id="leaveType">
                @foreach($leavetypes as $type)
                    <option value="{{$type->name}}">{{$type->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>代理人</label>
            <select class="form-control" id="leaveAgent">
                @foreach($users as $user)
                    <option value="{{$user->NO}}" @if($user->NO == $agent_user_no) selected @endif >{{$user->cname}}</option>
                @endforeach
            </select>
        </div>
        <div><label>休假開始時間</label></div>
        <div class="row form-group">
            <div class="col-6">
                <input class="form-control" id="startDate" type="date" value="{{$nowdate}}"/>
            </div>
            <div class="col-3">
                <select class="form-control" id="startHour">
                    @foreach($hours_select as $h)
                        <option value="{{$h}}" @if($h == $startHour) selected @endif>{{$h}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-3">
                <select class="form-control" id="startMin">
                    <option value="00" @if('00' == $startMin) selected @endif>00</option>
                    <option value="30" @if('30' == $startMin) selected @endif>30</option>
                </select>
            </div>
        </div>
        <div><label>休假結束時間</label></div>
        <div class="row form-group">
            <div class="col-6">
                <input class="form-control" id="endDate" type="date" value="{{$nowdate}}"/>
            </div>
            <div class="col-3">
                <select class="form-control" id="endHour">
                    @foreach($hours_select as $h)
                        <option value="{{$h}}" @if($h == $endHour) selected @endif>{{$h}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-3">
                <select class="form-control" id="endMin">
                    <option value="00" @if('00' == $endMin) selected @endif>00</option>
                    <option value="30" @if('30' == $endMin) selected @endif>30</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>休假事由</label>
            <textarea class="form-control rounded-0" id="comment" rows="3"></textarea>
        </div>
        <button type="button" class="btn btn-primary" onclick="apply_leave()">休假申請</button>
    </form>
</div>
<script>
    window.onload = function() {
        $('#applyUser').select2();
        $('#leaveAgent').select2();
    };

    const get_user_date = () => {
        promise_call({
            url: "/api/userlist/"+$("#applyUser").val(),
            method: "get"
        })
        .then(v => {
            if(v.status == "successful") {
                const res = v.data;
                $("#leaveAgent").val(res.default_agent_user_no).trigger("change");
                $("#startHour").val(res.work_start.split(":")[0]);
                $("#startMin").val(res.work_start.split(":")[1]);
                $("#endHour").val(res.work_end.split(":")[0]);
                $("#endMin").val(res.work_end.split(":")[1]);
            } else {
                alert(v.message);
            }
        })
    }
    const apply_leave = () => {
        const post_data = {
            "userId": $("#applyUser").val(),
            "leaveType": $("#leaveType").val(),
            "leaveAgent": $("#leaveAgent").val(),
            "startDate": $("#startDate").val()+"T"+$("#startHour").val()+":"+$("#startMin").val(),
            "endDate": $("#endDate").val()+"T"+$("#endHour").val()+":"+$("#endMin").val(),
            "use_mode": 'web'
        }

        for (k in post_data) {
            if(post_data[k] == "") {
                alert("資料不正確");
                return;
            }
        }
        post_data.comment = $("#comment").val();
        promise_call({
            url: "/api/applyleave", 
            data: post_data, 
            method: "post"
        })
        .then(v => {
            if(v.status == "successful") {
                alert("申請成功");
            } else {
                alert(v.message);
            }
        })
    }
</script>
@endsection
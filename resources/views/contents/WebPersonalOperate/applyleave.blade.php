@extends('contents.WebPersonalOperate.master')
@section('title', 'Home')
@section('content3')
<div class="container-fluid pt-lg-4">
    <form id="apply_from">
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
                    <option value="{{$user->NO}}" @if($user->NO == $default_agent_user_no) selected @endif>{{$user->cname}}</option>
                @endforeach
            </select>
        </div>
        <div><label>休假開始時間</label></div>
        <div class="row form-group">
            <div class="col-6">
                <input class="form-control" id="startDate" type="date" value="{{$nowdate}}" onchange="change_startdate()"/>
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
<div id="useridfield" style="display:none">{{$user_id}}</div>
<script>
    window.onload = function() {
        $('#leaveAgent').select2();
    };

    const apply_leave = () => {
        const post_data = {
            "userId": document.getElementById('useridfield').textContent,
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

        const start_time = new Date(post_data.startDate);
        const end_time = new Date(post_data.endDate);
        if(start_time >= end_time) {
            alert("結束時間需大於開始時間");
            return;
        } 

        post_data.comment = $("#comment").val();
        promise_call({
            url: "/api/applyleave", 
            data: post_data, 
            method: "post"
        })
        .then(v => {
            if(v.status == "successful") {
                $("#apply_from")[0].reset()
                alert("送簽中");
            } else {
                alert(v.message);
            }
        })
    }

    const change_startdate = () => {
        $("#endDate").val($("#startDate").val())
    }
</script>
@endsection
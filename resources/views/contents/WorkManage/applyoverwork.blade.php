@extends('contents.WorkManage.master')
@section('title', 'Home')
@section('content2')
<div class="container-fluid pt-lg-4">
    <form>
        <div class="form-group">
            <label>申請人</label>
            <select class="form-control" id="applyUser">
                @foreach($users as $user)
                    <option value="{{$user->NO}}">{{$user->cname}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail1">加班日</label>
            <input class="form-control" id="overworkDate"  type="date" value="{{$nowdate}}"/>
        </div>
        <div class="form-group">
            <label for="overworkHour">加班小時</label>
            <select class="form-control" id="overworkHour">
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
                <option>7</option>
                <option>8</option>
                <option>9</option>
                <option>10</option>
                <option>11</option>
                <option>12</option>
            </select>
        </div>
        <div class="form-group">
            <label for="comment">加班事由</label>
            <textarea class="form-control rounded-0" id="comment" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" onclick="apply_overwork()">加班申請</button>
    </form>
</div>
<script>
    window.onload = function() {
        $('#applyUser').select2();
    };
    const apply_overwork = () => {
        const post_data = {
            "userId": $("#applyUser").val(),
            "overworkDate": $("#overworkDate").val(),
            "overworkHour": $("#overworkHour").val(),
            "use_mode"    : 'web',
        }
        for (k in post_data) {
            if(post_data[k] == "") {
                alert("資料不正確");
                return;
            }
        }
        post_data.comment = $("#comment").val();
        promise_call({
            url: "/api/applyoverwork", 
            data: post_data, 
            method: "post"
        })
        .then(v => {
            if(v.status == "successful") {
                alert("送簽中");
            } else {
                alert(v.message);
            }
        })
    }
</script>
@endsection
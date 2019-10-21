var apply_time = {'start_hour': 8, 'start_min': '00', 'end_hour': 17, 'end_min': '00'};
window.onload = function (e) {
    $("#toast").show();
    liff.init(function (data) {
        initializeApp(data);
    }, function (err) {
        alert(err);
    });
    //initializeApp({context: {userId: "U8d41dfb18097f57080858e39b929ce39"}});
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    Promise.all([
        promise_call({url: "./api/userlist/checklineid/"+data.context.userId, method: "get"}),
        promise_call({url: "./api/applyleave/user/"+data.context.userId, method: "get"}),
    ]).then(v => {
        if(v[0].status == "successful" && v[0].data.length == 0) {
            $("#toast").hide();
            $("#no_bind_alert").show();
            return;
        } 
        if(v[1].status != "successful" ||  v[1].data.length == 0) {
            $("#toast").hide();
            alert("取得員工資料錯誤");
            return;
        } else {
            const user = v[1].data[0];
            if(user.default_agent_user_no != 0) $("#leaveAgent").val(user.default_agent_user_no);
            user.work_start = user.work_start || '08:00:00';
            user.work_end = user.work_end || '17:00:00';
            apply_time = {
                'start_hour': user.work_start.split(':')[0], 
                'start_min': user.work_start.split(':')[1], 
                'end_hour': user.work_end.split(':')[0], 
                'end_min': user.work_end.split(':')[1]
            };
            $("#startTime").html(apply_time.start_hour+':'+apply_time.start_min);
            $("#endTime").html(apply_time.end_hour+':'+apply_time.end_min);
            $("#toast").hide();
        }
        
    })
}

const setTime = (type) => {
    if(type == 'startTime') {
        $("#add_hour").attr("onclick", "time_calculate('startTime', 'add_hour')");
        $("#add_min").attr("onclick", "time_calculate('startTime', 'add_min')");
        $("#sub_hour").attr("onclick", "time_calculate('startTime', 'sub_hour')");
        $("#sub_min").attr("onclick", "time_calculate('startTime', 'sub_min')");
        $("#apply_time_hour").html(apply_time.start_hour);
        $("#apply_time_min").html(apply_time.start_min);
    } else {
        $("#add_hour").attr("onclick", "time_calculate('endTime', 'add_hour')");
        $("#add_min").attr("onclick", "time_calculate('endTime', 'add_min')");
        $("#sub_hour").attr("onclick", "time_calculate('endTime', 'sub_hour')");
        $("#sub_min").attr("onclick", "time_calculate('endTime', 'sub_min')");
        $("#apply_time_hour").html(apply_time.end_hour);
        $("#apply_time_min").html(apply_time.end_min);
    }
    $("#time_select").show();
}

const time_calculate = (time_type, action_type) => {
    if(time_type == 'startTime') {
        if(action_type == 'add_hour') {
            apply_time.start_hour++;
            if(apply_time.start_hour == 24) apply_time.start_hour = 0;
        } else if(action_type == 'sub_hour') {
            apply_time.start_hour--;
            if(apply_time.start_hour == -1) apply_time.start_hour = 23;
        } else {
            if(apply_time.start_min == '00') {
                apply_time.start_min = '30';
            } else {
                apply_time.start_min = '00';
            }
        }
        $("#apply_time_hour").html(apply_time.start_hour);
        $("#apply_time_min").html(apply_time.start_min);
        $("#startTime").html(apply_time.start_hour+':'+apply_time.start_min);
    } else {
        if(action_type == 'add_hour') {
            apply_time.end_hour++;
            if(apply_time.end_hour == 24) apply_time.end_hour = 0;
        } else if(action_type == 'sub_hour') {
            apply_time.end_hour--;
            if(apply_time.end_hour == -1) apply_time.end_hour = 23;
        } else {
            if(apply_time.end_min == '00') {
                apply_time.end_min = '30';
            } else {
                apply_time.end_min = '00';
            }
        }
        $("#apply_time_hour").html(apply_time.end_hour);
        $("#apply_time_min").html(apply_time.end_min);
        $("#endTime").html(apply_time.end_hour+':'+apply_time.end_min);
    }
}

const apply_leave = () => {
    
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "leaveType": $("#leaveType").val(),
        "leaveAgent": $("#leaveAgent").val(),
        "startDate": $("#startDate").val()+"T"+apply_time.start_hour+":"+apply_time.start_min,
        "endDate": $("#endDate").val()+"T"+apply_time.end_hour+":"+apply_time.end_min,
        "use_mode": 'line'
    }
    const start_time = new Date(post_data.startDate);
    const end_time = new Date(post_data.endDate);
    if(start_time >= end_time) {
        $("#error_alert").find(".weui-dialog__bd").html("結束時間需大於開始時間");
        $("#error_alert").show();
        return;
    } 
    //alert(JSON.stringify(post_data));
    for (k in post_data) {
        if(post_data[k] == "") {
            $("#error_alert").find(".weui-dialog__bd").html("欄位填寫錯誤");
            $("#error_alert").show();
            return;
        }
    }
    post_data.comment = $("#comment").val();
    $("#toast").show();
    promise_call({
        url: "./api/applyleave", 
        data: post_data, 
        method: "post"
    })
    .then(v => {
        $("#toast").hide();
        if(v.status == "successful") {
            liff.closeWindow();
        } else {
            $("#error_alert").find(".weui-dialog__bd").html(v.message);
            $("#error_alert").show();
        }
    })
}

const change_tab = (p) => {
    alert(p);
    $(".weui-navbar__item").removeClass("weui-bar__item_on");
    $($(".weui-navbar__item").get(p)).addClass("weui-bar__item_on");
    $(".weui-navbar__item").get().map((item, index) => {
        if(index == p) {
            $($(".weui-tab__panel").children('div')[index]).show()
        } else {
            $($(".weui-tab__panel").children('div')[index]).hide();
        }
    })
}

const close_dialog = () => {
    $("#error_alert").hide();
}

const close_no_bind_alert = () => {
    $("#no_bind_alert").hide();
    liff.closeWindow();
}

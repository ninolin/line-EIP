var isMobile = false;
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
            $("#startTime").html(user.work_start.split(':')[0]+':'+user.work_start.split(':')[1]);
            $("#endTime").html(user.work_end.split(':')[0]+':'+user.work_end.split(':')[1]);
            $("#toast").hide();
        }
        
    })
}

const setTime = (id) => {
    const hour = $("#"+id).html().split(':')[0];
    const min = $("#"+id).html().split(':')[1];
    let defaultValue = [hour, min];
    if(id == "endTime") defaultValue = [hour, min];
    weui.picker(
        [
            {label: '01', value: '01'}, 
            {label: '02', value: '02'}, 
            {label: '03', value: '03'},
            {label: '04', value: '04'},
            {label: '05', value: '05'},
            {label: '06', value: '06'},
            {label: '07', value: '07'},
            {label: '08', value: '08'},
            {label: '09', value: '09'},
            {label: '10', value: '10'},
            {label: '11', value: '11'},
            {label: '12', value: '12'},
            {label: '13', value: '13'},
            {label: '14', value: '14'},
            {label: '15', value: '15'},
            {label: '16', value: '16'},
            {label: '17', value: '17'},
            {label: '18', value: '18'},
            {label: '19', value: '19'},
            {label: '20', value: '20'},
            {label: '21', value: '21'},
            {label: '22', value: '22'},
            {label: '23', value: '23'}
        ], 
        [
            {label: '00', value: '00'}, {label: '30',value: '30'}
        ], 
        {
        defaultValue: defaultValue,
        onConfirm: function (result) {
            $("#"+id).html(result[0].value + ":" + result[1].value);
        },
        id: id
        }
    );
}

const apply_leave = () => {
    
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "leaveType": $("#leaveType").val(),
        "leaveAgent": $("#leaveAgent").val(),
        "startDate": $("#startDate").val()+"T"+$("#startTime").html(),
        "endDate": $("#endDate").val()+"T"+$("#endTime").html(),
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

var isMobile = false;
window.onload = function (e) {
    $("#startTime").html("08:00");
    $("#endTime").html("17:00");
    
    liff.init(function (data) {
        initializeApp(data);
    });
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/userlist/checklineid/"+data.context.userId, 
        method: "get"
    })
    .then(v => {
        if(v.status == "successful" && v.data.length == 0) {
            $("#no_bind_alert").show();
            return;
        } 
    })
}

const setTime = (id) => {
    let defaultValue = ['08', '00'];
    if(id == "endTime") defaultValue = ['17', '00']
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
        //"userId": "U8d41dfb18097f57080858e39b929ce39",
        "leaveType": $("#leaveType").val(),
        "leaveAgent": $("#leaveAgent").val(),
        "startDate": $("#startDate").val()+"T"+$("#startTime").html(),
        "endDate": $("#endDate").val()+"T"+$("#endTime").html()
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
            alert(v.message);
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

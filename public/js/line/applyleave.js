var isMobile = false;
window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/checklineid/"+data.context.userId, 
        method: "get"
    })
    .then(v => {
        if(v.status == "successful" && v.data.length == 0) {
            alert('目前未完成綁定，無法使用Everplast員工服務系統');
        } 
    })
}

const apply_leave = () => {
    
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "leaveType": $("#leaveType").val(),
        "leaveAgent": $("#leaveAgent").val(),
        "startDate": $("#startDate").val(),
        "endDate": $("#endDate").val()
    }
    const start_time = new Date($("#startDate").val());
    const end_time = new Date($("#endDate").val());
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

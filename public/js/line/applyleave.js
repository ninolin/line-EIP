var isMobile = false;
window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
}

const apply_leave = () => {
    alert("yyy");
    // const post_data = {
    //     "userId": document.getElementById('useridfield').textContent,
    //     "leaveType": $("#leaveType").val(),
    //     "leaveAgent": $("#leaveAgent").val(),
    //     "startDate": $("#startDate").val(),
    //     "startTime": $("#startTime").val(),
    //     "endDate": $("#endDate").val(),
    //     "endTime": $("#endTime").val()
    // }
    
    // for (k in post_data) {
    //     if(post_data[k] == "") {
    //         alert("資料不正確");
    //         return;
    //     }
    // }
    // post_data.comment = $("#comment").val();
    // promise_call({
    //     url: "./api/applyleave", 
    //     data: post_data, 
    //     method: "post"
    // })
    // .then(v => {
    //     if(v.status == "successful") {
    //         liff.closeWindow();
    //     } else {
    //         alert(v.message);
    //     }
    // })
}
document.getElementById('test').addEventListener('click', function () {
    alert("xxxxx");
});
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

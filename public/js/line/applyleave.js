window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
};

function initializeApp(data) {
    $("#userdata").attr("userId", data.context.userId);
    //document.getElementById('useridfield').textContent = data.context.userId;
}

const apply_leave = () => {
    promise_call({
        url: "./api/applyleave", 
        data: {
            "userId": $("#userdata").attr("userId"),
            "leaveType": $("#leaveType").val(),
            "leaveAgent": $("#leaveAgent").val(),
            "startDate": $("#startDate").val(),
            "startTime": $("#startTime").val(),
            "endDate": $("#endDate").val(),
            "endTime": $("#endTime").val(),
            "comment": $("#comment").val(),
        }, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            liff.closeWindow();
        } 
    })
}
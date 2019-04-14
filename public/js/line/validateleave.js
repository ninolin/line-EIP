window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/validateleave/"+data.context.userId, 
        method: "get"
    })
    .then(v => {
        document.getElementById('result').textContent = data.context.userId;
        alert(JSON.stringify(v));
        // if(v.status == "successful") {
        //     liff.closeWindow();
        // } 
    })
}

const apply_leave = () => {
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "leaveType": $("#leaveType").val(),
        "leaveAgent": $("#leaveAgent").val(),
        "startDate": $("#startDate").val(),
        "startTime": $("#startTime").val(),
        "endDate": $("#endDate").val(),
        "endTime": $("#endTime").val()
    }
    for (k in post_data) {
        if(post_data[k] == "") {
            alert("資料不正確");
            return;
        }
    }
    post_data.comment = $("#comment").val();
    promise_call({
        url: "./api/applyleave", 
        data: post_data, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            liff.closeWindow();
        } 
    })
}
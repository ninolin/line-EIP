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
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            alert(JSON.stringify(v));
            $("#leave_data").html("");
            v.data.map(item => {
                $html =  "<tr>";
                $html += "<td>"+item.cname+"</td>";
                $html += "<td>"+item.agent_cname+"</td>";
                $html += "<td>"+item.leave_name+"</td>";
                $html += "<td>"+item.start_date+" "+item.start_time+"</td>";
                $html += "<td>"+item.end_date+" "+item.end_time+"</td>";
                $html +=  "</tr>";
                $("#leave_data").append($html);
            })
        }
        
    })
}

const validate_leave = () => {
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
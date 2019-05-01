window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
    //initializeApp({})
};

function initializeApp(data) {
    //document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/individualleavelog/"+data.context.userId, 
        //url: "./api/individuallog/U8d41dfb18097f57080858e39b929ce39", 
        method: "get"
    })
    .then(v => {
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            //alert(JSON.stringify(v));
            if(v.data.length > 0)  $("#leave_data").html("");
            v.data.map(item => {
                $html =  "<tr>";             
                if(item.apply_type == 'L') {
                    $html += "<td>"+item.agent_cname+"</td>";
                    $html += "<td>"+item.leave_name+"</td>";
                    $html += "<td>"+item.start_date+" "+item.start_time+"</td>";
                    $html += "<td>"+item.end_date+" "+item.end_time+"</td>";
                } else {
                    $html += "<td>-</td>";
                    $html += "<td>加班</td>";
                    $html += "<td>"+item.over_work_date+"("+item.over_work_hours+"小時)</td>";
                    $html += "<td>-</td>";
                }
                if(item.apply_status == 'P') {
                    $html += "<td>簽核中</td>";
                } else if(item.apply_status == 'Y') {
                    $html += "<td>已通過</td>";
                } else {
                    $html += "<td>已拒絕</td>";
                }
                
                $html += "</tr>";
                $("#leave_data").append($html);
            })
        }
    })
}
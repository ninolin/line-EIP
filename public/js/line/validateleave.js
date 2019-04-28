window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
    //initializeApp({})
};

function initializeApp(data) {
    //document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/validateleave/"+data.context.userId, 
        //url: "./api/validateleave/U8d41dfb18097f57080858e39b929ce39", 
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
                $html += "<td>"+item.cname+"</td>";
               
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
                $html += "<td><button type='button' class='btn btn-primary btn-sm' onclick='show_leave("+item.id+")'>查</button></td>";
                $html += "</tr>";
                $("#leave_data").append($html);
            })
        }
    })
}

const show_leave = (apply_id) => {
    promise_call({
        url: "./api/applyleave/"+apply_id, 
        method: "get"
    })
    .then(v => {
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            $("#leave_data_in_modal").html("");
            v.data.map(item => {
                $html =  "<tr>";
                $html += "<td>"+item.cname+"</td>";
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
                $html += "</tr>";
                if(item.comment) {
                    $html += "<tr>";
                    $html += "<td colspan='5'>備住:"+item.comment+"</td>";
                    $html += "</tr>";
                }
                $("#leave_data_in_modal").append($html);
            })
            $("#validateModal").find(".agree").attr("onclick", "validate_leave('agree', "+apply_id+")");
            $("#validateModal").find(".reject").attr("onclick", "validate_leave('reject', "+apply_id+")");
            $('#validateModal').modal('toggle');
        }
    })
}

const validate_leave = (type, apply_id) => {

    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        //"userId": "U8d41dfb18097f57080858e39b929ce39", 
        "validate": type,
        "reject_reason": $("#reject_reason").val() || "null"
    }
    promise_call({
        url: "./api/validateleave/"+apply_id, 
        data: post_data, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            liff.closeWindow();
        } 
    })
}
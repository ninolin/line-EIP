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
            //alert(JSON.stringify(v));
            if(v.data.length > 0)  $("#leave_data").html("");
            v.data.map(item => {
                $html =  "<tr>";
                $html += "<td>"+item.cname+"</td>";
                $html += "<td>"+item.agent_cname+"</td>";
                $html += "<td>"+item.leave_name+"</td>";
                $html += "<td>"+item.start_date+" "+item.start_time+"</td>";
                $html += "<td>"+item.end_date+" "+item.end_time+"</td>";
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
            v.data.map(item => {
                $html =  "<tr>";
                $html += "<td>"+item.cname+"</td>";
                $html += "<td>"+item.agent_cname+"</td>";
                $html += "<td>"+item.leave_name+"</td>";
                $html += "<td>"+item.start_date+" "+item.start_time+"</td>";
                $html += "<td>"+item.end_date+" "+item.end_time+"</td>";
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

    //alert(type+apply_id+document.getElementById('useridfield').textContent);
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
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
const showDetailModal = async (apply_id) => {
    const res = await get_apply_path(apply_id);
    if(res.status == "successful") {
        if(res.data.length > 0) $("#log_data").html("");
        res.data.map( (item, index) => {
            let html = "<tr>";
            html += "<td>"+(index+1)+"</td>";
            html += "<td>"+item.cname+"</td>";
            if(item.is_validate === 1) {
                html += "<td>同意</td>";
            } else if(item.is_validate === 0){
                html += "<td>拒絕</td>";
            } else {
                html += "<td>未簽核</td>";
            }
            if(item.reject_reason) {
                html += "<td>"+item.reject_reason+"</td>";
            } else {
                html += "<td>-</td>";
            }
            if(item.validate_time) {
                html += "<td>"+item.validate_time+"</td>";
            } else {
                html += "<td>-</td>";
            }
            html += "</tr>";
            $("#log_data").append(html);
        })
        $('#logModal').modal('toggle');
    }
}

const get_apply_path = (apply_id) => {
    return promise_call({
        url: "./api/leavelog/"+apply_id, 
        method: "get"
    })
}
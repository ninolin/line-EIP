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
    
    // const users_res = await get_all_user();
    // if(titles_res.status == "successful" && users_res.status == "successful") {
    //     const all_titles = titles_res.data.map(item => {
    //         item.text = item.name;
    //         return item;
    //     })
    //     const all_users = users_res.data.map(item => {
    //         item.id = item.NO;
    //         item.text = item.cname;
    //         return item;
    //     })
       
    //     $("#title_set_select").select2({
    //         dropdownParent: $("#setModal"),
    //         data: all_titles,
    //         dropdownAutoWidth : false,
    //         width: '100%',
    //     })
    //     $("#title_set_select").val(title_id).trigger("change");
    //     $("#upper_user_set_select").select2({
    //         dropdownParent: $("#setModal"),
    //         data: all_users,
    //         dropdownAutoWidth : false,
	// 		width: '100%'
    //     })
    //     $("#upper_user_set_select").val(upper_user_no).trigger("change");
    //     $("#setModal").find(".todo").attr("onclick", "update_set('"+user_no+"')").html("修改");
    //     $('#setModal').modal('toggle');
    // } else {
    //     alert("get data error");
    // }
}

const get_apply_path = (apply_id) => {
    return promise_call({
        url: "./api/leavelog/"+apply_id, 
        method: "get"
    })
}
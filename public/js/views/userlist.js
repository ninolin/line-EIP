
const showSetModal = async (user_no, title_id, upper_user_no, work_class_id, onboard_date) => {
    $("#onboard_date").val(onboard_date);
    const titles_res = await get_all_title();
    const users_res = await get_all_user();
    const class_res = await get_all_class();
    if(titles_res.status == "successful" && users_res.status == "successful") {
        let all_titles = titles_res.data.map(item => {
            item.text = item.name;
            return item;
        })
        all_titles = [{id: 0, text: "不設定"}, ...all_titles];

        let all_users = users_res.data.map(item => {
            item.id = item.NO;
            item.text = item.cname;
            return item;
        })
        all_users = [{id: 0, text: "不設定"}, ...all_users];

        let all_classes = class_res.data.map(item => {
            item.id = item.id;
            item.text = item.name;
            return item;
        })
        all_classes = [{id: 0, text: "不設定"}, ...all_classes];

        $("#title_set_select").select2({
            dropdownParent: $("#setModal"),
            data: all_titles,
            dropdownAutoWidth : false,
            width: '100%',
        })
        $("#title_set_select").val(title_id).trigger("change");
        $("#upper_user_set_select").select2({
            dropdownParent: $("#setModal"),
            data: all_users,
            dropdownAutoWidth : false,
			width: '100%'
        })
        $("#upper_user_set_select").val(upper_user_no).trigger("change");
        $("#work_class_set_select").select2({
            dropdownParent: $("#setModal"),
            data: all_classes,
            dropdownAutoWidth : false,
			width: '100%'
        })
        $("#work_class_set_select").val(work_class_id).trigger("change");
        $("#setModal").find(".todo").attr("onclick", "update_set('"+user_no+"')").html("修改");
        $('#setModal').modal('toggle');
    } else {
        alert("get data error");
    }
}

const showLeaveDayModal = (user_no) => {
    $("#setLeaveDayModal").find(".todo").attr("onclick", "updateLeaveDay('"+user_no+"')")
    promise_call({
        url: "./api/userlist/leaveday/"+user_no, 
        method: "get"
    })
    .then(v => {
        if(v.status == "successful") {
            $("#calc_leave_day").html(v.leave_day);
            $("#year_useleave").html(v.year_useleave);
            $("#year_totalleave").val(v.year_totalleave);
        }
        $('#setLeaveDayModal').modal('toggle');
    })
}

const updateLeaveDay = (user_no) => {
    promise_call({
        url: "./api/userlist/leaveday/"+user_no, 
        data: {
            "leave_day": $("#year_totalleave").val()
        }, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            $('#setLeaveDayModal').modal('toggle');
        }
    })
}

const showBindLineId = (user_no, usercname) => {
    $("#bindLineModal").find(".modal-title").html("綁定"+usercname.trim()+"的LineId");
    $("#bindLineModal").find(".todo").attr("onclick", "bindLineId('"+user_no+"')");
    $('#bindLineModal').modal('toggle');
}

const showUnbindLineId = (user_no, usercname) => {
    $("#unbindLineModal").find(".modal-title").html("解除綁定"+usercname.trim()+"的LineId");
    $("#unbindLineModal").find(".todo").attr("onclick", "unbindLineId('"+user_no+"')");
    $('#unbindLineModal').modal('toggle');
}

const bindLineId = (user_no) => {
    promise_call({
        url: "./api/userlist/bindlineid/"+user_no, 
        data: {
            "line_id": $("#line_id_input").val()
        }, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            $('#bindLineModal').modal('toggle');
        }
    })
}

const unbindLineId = (user_no) => {
    promise_call({
        url: "./api/userlist/unbindlineid/"+user_no, 
        data: {}, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            $('#unbindLineModal').modal('toggle');
        }
    })
}

const get_all_title = () => {
    return promise_call({
        url: "./api/title", 
         method: "get"
    })
}

const get_all_user = () => {
    return promise_call({
        url: "./api/userlist", 
        method: "get"
    })
}

const get_all_class = () => {
    return promise_call({
        url: "/api/workclass/", 
        method: "get"
    })
}

const update_set = (user_no) => {
    if(user_no == $("#upper_user_set_select").val()) {
        alert("請勿設定自己為第一簽核人");
        return;
    }
    promise_call({
        url: "./api/userlist/"+user_no, 
        data: {
            "title_id": $("#title_set_select").val(),
            "upper_user_no": $("#upper_user_set_select").val(),
            "work_class_id": $("#work_class_set_select").val(),
            "onboard_date": $("#onboard_date").val()
        }, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            $('#setModal').modal('toggle');
        }
    })
}

const delete_title = (title_id) => {
    promise_call({
        url: "./api/titlelist/"+title_id,
        method: "delete"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            //$('#addTitleModal').modal('toggle');
        }
    })
}

const reload_page = (page, order_col, order_type, source) => {
    if(source == 'col' && order_type == 'DESC') {
        order_type = 'ASC';
    } else if(source == 'col' && order_type == 'ASC') {
        order_type = 'DESC';
    }
    $("#search_form").attr("action", "./userlist?page="+page+"&order_col="+order_col+"&order_type="+order_type);
    $("#search_form").submit();
}

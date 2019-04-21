const showLeaveModal = async (type, leave_id, leave_name, leave_day, title_id) => {
    const titles_res = await get_all_title();
    if(titles_res.status == "successful") {
        const all_titles = titles_res.data.map(item => {
            item.text = item.name;
            return item;
        })
        $("#title_set_select").select2({
            dropdownParent: $("#leaveModal"),
            data: all_titles,
            dropdownAutoWidth : false,
            width: '100%'
        })
        if(type == 'add') {
            $("#leaveModal").find(".modal-header h5").html("新增假別");
            $("#leaveModal").find(".leave-name").val("");
            $("#leaveModal").find(".leave-day").val("");
            $("#leaveModal").find(".todo").attr("onclick", "add_leave()").html("新增");
            $('#leaveModal').modal('toggle');
        } else {
            $("#leaveModal").find(".modal-header h5").html("修改假別");
            $("#leaveModal").find(".leave-name").val(leave_name);
            $("#leaveModal").find(".leave-day").val(leave_day);
            $("#title_set_select").val(title_id).trigger("change");
            $("#leaveModal").find(".todo").attr("onclick", "update_leave('"+leave_id+"')").html("修改");
            $('#leaveModal').modal('toggle');
        }
    } else {
        alert("get data error");
    }
}

const get_all_title = () => {
    return promise_call({
        url: "./api/titlelist", 
         method: "get"
    })
}

const add_leave = () => {
    promise_call({
        url: "./api/leavetypelist", 
        data: {
            "name": $("#leaveModal").find(".leave-name").val(),
            "day": $("#leaveModal").find(".leave-day").val(),
            "approved_title_id": $("#title_set_select").val()
        }, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            alert(v.message);
        }
    })
}

const update_leave = (leave_id) => {
    promise_call({
        url: "./api/leavetypelist/"+leave_id, 
        data: {
            "name": $("#leaveModal").find(".leave-name").val(),
            "day": $("#leaveModal").find(".leave-day").val(),
            "approved_title_id": $("#title_set_select").val()
        }, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            alert(v.message);
        }
    })
}

const showDeleteModal = async (leave_id, leave_name, leave_day) => {
    $("#deleteModal").find(".todo").attr("onclick", "delete_leave('"+leave_id+"')").html("刪除");
    $("#deleteModal").find(".delete_msg").html("確認要刪除「"+leave_name+leave_day+"天」該假別嗎?");
    $('#deleteModal').modal('toggle');
}

const delete_leave = (leave_id) => {
    promise_call({
        url: "./api/leavetypelist/"+leave_id,
        method: "delete"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            alert(v.message);
        }
    })
}
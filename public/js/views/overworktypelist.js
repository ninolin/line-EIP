const showOverworkModal = async (type, overwork_id, overwork_hour, title_id) => {
    const titles_res = await get_all_title();
    if(titles_res.status == "successful") {
        const all_titles = titles_res.data.map(item => {
            item.text = item.name;
            return item;
        })
        $("#title_set_select").select2({
            dropdownParent: $("#overworkModal"),
            data: all_titles,
            dropdownAutoWidth : false,
            width: '100%'
        })
        if(type == 'add') {
            $("#overworkModal").find(".modal-header h5").html("新增加班");
            $("#overworkModal").find(".overwork-hour").val("");
            $("#overworkModal").find(".todo").attr("onclick", "add_overwork()").html("新增");
            $('#overworkModal').modal('toggle');
        } else {
            $("#overworkModal").find(".modal-header h5").html("修改加班");
            $("#overworkModal").find(".overwork-hour").val(overwork_hour);
            $("#title_set_select").val(title_id).trigger("change");
            $("#overworkModal").find(".todo").attr("onclick", "update_overwork('"+overwork_id+"')").html("修改");
            $('#overworkModal').modal('toggle');
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

const add_overwork = () => {
    promise_call({
        url: "./api/overworktypelist", 
        data: {
            "hour": $("#overworkModal").find(".overwork-hour").val(),
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

const update_overwork = (overwork_id) => {
    promise_call({
        url: "./api/overworktypelist/"+overwork_id, 
        data: {
            "hour": $("#overworkModal").find(".overwork-hour").val(),
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

const showDeleteModal = async (overwork_id, overwork_hour) => {
    $("#deleteModal").find(".todo").attr("onclick", "delete_overwork('"+overwork_id+"')").html("刪除");
    $("#deleteModal").find(".delete_msg").html("確認要刪除「"+overwork_hour+"小時」該加班嗎?");
    $('#deleteModal').modal('toggle');
}

const delete_overwork = (overwork_id) => {
    promise_call({
        url: "./api/overworktypelist/"+overwork_id,
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
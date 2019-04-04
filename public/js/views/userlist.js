const showSetModal = async (user_no, title_id, upper_user_no) => {
    const titles_res = await get_all_title();
    const users_res = await get_all_user();
    if(titles_res.status == "successful" && users_res.status == "successful") {
        const all_titles = titles_res.data.map(item => {
            item.text = item.name;
            return item;
        })
        const all_users = users_res.data.map(item => {
            item.id = item.NO;
            item.text = item.cname;
            return item;
        })
       
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
        $("#setModal").find(".todo").attr("onclick", "update_set('"+user_no+"')").html("修改");
        $('#setModal').modal('toggle');
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
const get_all_user = () => {
    return promise_call({
        url: "./api/userlist", 
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
            "upper_user_no": $("#upper_user_set_select").val()
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
const showTitleModal = (type, title_id, title_name) => {
    if(type == 'update') {
        $('#titleModal').modal('toggle');
        $("#titleModal").find(".modal-title").html("修改職等");
        $("#titleModal").find(".title-name").val(title_name);
        $("#titleModal").find(".todo").attr("onclick", "update_title('"+title_id+"')").html("修改");
    } else if(type == 'add') {
        $('#titleModal').modal('toggle');
        $("#titleModal").find(".modal-title").html("新增職等");
        $("#titleModal").find(".title-name").val('');
        $("#titleModal").find(".todo").attr('onclick', "add_title()").html("新增");
    }
}

const add_title = () => {
    promise_call({
        url: "./api/titlelist", 
        data: {"name": $("#titleModal").find(".title-name").val()}, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            //$('#addTitleModal').modal('toggle');
            alert(v.message);
        }
    })
}

const update_title = (title_id) => {
    promise_call({
        url: "./api/titlelist/"+title_id, 
        data: {"name": $("#titleModal").find(".title-name").val()}, 
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

const showDeleteModal = async (title_id, title_name) => {
    $("#deleteModal").find(".todo").attr("onclick", "delete_title('"+title_id+"')").html("刪除");
    $("#deleteModal").find(".delete_msg").html("確認要刪除「"+title_name+"」該職等嗎?");
    $('#deleteModal').modal('toggle');
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
            alert(v.message);
        }
    })
}
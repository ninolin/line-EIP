const showTitleModal = (type, title_id, title_name) => {
    console.log(type);
    if(type == 'update') {
        console.log('aaa');
        $('#titleModal').modal('toggle');
        $("#titleModal").find(".modal-title").html("修改職等");
        $("#titleModal").find(".title-name").val(title_name);
        $("#titleModal").find(".todo").attr("onclick", "update_title('"+title_id+"')").html("修改");
    } else if(type == 'add') {
        console.log('bbb');
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
            $('#addTitleModal').modal('toggle');
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
            $('#addTitleModal').modal('toggle');
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
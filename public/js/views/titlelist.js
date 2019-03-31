const add_title = () => {
    promise_call({
        url: "./api/titlelist", 
        data: {"name": $("#title-name").val()}, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            window.location.reload();
        } else {
            console.log(v.message);
            $('#addTitleModal').modal('toggle');
        }
    })
}
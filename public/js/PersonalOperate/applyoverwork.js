const apply_overwork = () => {
    //alert(document.getElementById('useridfield').textContent);
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "overworkDate": $("#overworkDate").val(),
        "overworkHour": $("#overworkHour").val(),
        "use_mode"    : 'web',
    }
    //alert(JSON.stringify(post_data));
    for (k in post_data) {
        if(post_data[k] == "") {
            alert("資料不正確");
            return;
        }
    }
    post_data.comment = $("#comment").val();

    console.log(post_data);
    $("#toast").show();
    promise_call({
        url: "/api/applyoverwork", 
        data: post_data, 
        method: "post"
    })
    .then(v => {
        $("#toast").hide();
        if(v.status == "successful") {
            liff.closeWindow();
        } else {
            alert(v.message);
        }
    })
}

const close_no_bind_alert = () => {
    $("#no_bind_alert").hide();
    liff.closeWindow();
}
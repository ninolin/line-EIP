window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    }, function (err) {
        alert(err);
    });
    //initializeApp({context: {userId: "U8d41dfb18097f57080858e39b929ce39"}});
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/userlist/checklineid/"+data.context.userId, 
        method: "get"
    })
    .then(v => {
        if(v.status == "successful" && v.data.length == 0) {
            $("#no_bind_alert").show();
            return;
        } 
    })
}

const apply_overwork = () => {
    //alert(document.getElementById('useridfield').textContent);
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "overworkDate": $("#overworkDate").val(),
        "overworkHour": $("#overworkHour").val()
    }
    //alert(JSON.stringify(post_data));
    for (k in post_data) {
        if(post_data[k] == "") {
            alert("資料不正確");
            return;
        }
    }
    post_data.comment = $("#comment").val();
    $("#toast").show();
    promise_call({
        url: "./api/applyoverwork", 
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
window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
}

const apply_overwork = () => {
    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        "overworkDate": $("#overworkDate").val(),
        "overworkHour": $("#overworkHour").val()
    }

    for (k in post_data) {
        if(post_data[k] == "") {
            alert("資料不正確");
            return;
        }
    }
    post_data.comment = $("#comment").val();
    promise_call({
        url: "./api/applyoverwork", 
        data: post_data, 
        method: "post"
    })
    .then(v => {
        if(v.status == "successful") {
            liff.closeWindow();
        } else {
            alert(v.message);
        }
    })
}
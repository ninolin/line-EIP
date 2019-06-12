window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
    //initializeApp({})
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/validateleave/"+data.context.userId, 
        //url: "./api/validateleave/U8d41dfb18097f57080858e39b929ce39", 
        method: "get"
    })
    .then(v => {
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            v.data.map(item => {
                $html =  '<div class="weui-form-preview mb-3" id="apply_'+item.id+'">';
                $html += '<div class="weui-form-preview__hd" style="padding: 5px 16px;">';
                if(item.apply_type == 'L') {
                    $html += '    <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;text-align:left;">'+item.leave_name+'</em>';
                } else {
                    $html += '    <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;text-align:left;">加班</em>';
                }
                $html += '</div>';
                $html += '<div class="weui-form-preview__bd" style="padding: 5px 16px;">';
                $html += '    <div class="weui-form-preview__item">';
                $html += '        <span class="weui-form-preview__label">申請人</span>';
                $html += '         <span class="weui-form-preview__value">'+item.cname+'</span>';
                $html += '    </div>';
                if(item.apply_type == 'L') {
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">代理人</span>';
                    $html += '         <span class="weui-form-preview__value">'+item.agent_cname+'</span>';
                    $html += '    </div>';
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">開始時間</span>';
                    $html += '        <span class="weui-form-preview__value">'+item.start_date+'</span>';
                    $html += '    </div>';
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">結束時間</span>';
                    $html += '        <span class="weui-form-preview__value">'+item.end_date+'</span>';
                    $html += '    </div>';
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">請假事由</span>';
                    $html += '        <span class="weui-form-preview__value">'+item.comment+'</span>';
                    $html += '    </div>';
                } else {
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">加班時間</span>';
                    $html += '        <span class="weui-form-preview__value">'+item.over_work_date+'</span>';
                    $html += '    </div>';
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">加班小時</span>';
                    $html += '        <span class="weui-form-preview__value">'+item.over_work_hours+'</span>';
                    $html += '    </div>';
                    $html += '    <div class="weui-form-preview__item">';
                    $html += '        <span class="weui-form-preview__label">加班事由</span>';
                    $html += '        <span class="weui-form-preview__value">'+item.comment+'</span>';
                    $html += '    </div>';
                }
                $html += '<div class="weui-form-preview__ft">';
                $html += '  <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="validate_leave('+item.id+', \''+item.apply_type+'\', 1)"><i class="weui-icon-success"></i>同意</button>';
                $html += '  <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_reject_dialog('+item.id+', \''+item.apply_type+'\', \''+item.cname+'\', \''+item.leave_name+'\')"><i class="weui-icon-cancel"></i>拒絕</button>';
                $html += '</div>';
                $html += '</div>';
                $html += '</div>';
                $("#leave_data").append($html);
            })
        }
    })
}

const show_leave = (apply_id, apply_type, action) => {
    //console.log(apply_type);
    promise_call({
        url: "./api/applyleave/"+apply_id, 
        method: "get"
    })
    .then(v => {
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            $("#leave_data_in_modal").html("");
            v.data.map(item => {
                $html =  "<tr>";
                $html += "<td>"+item.cname+"</td>";
                if(item.apply_type == 'L') {
                    $html += "<td>"+item.agent_cname+"</td>";
                    $html += "<td>"+item.leave_name+"</td>";
                    $html += "<td>"+item.start_date+" "+item.start_time+"</td>";
                    $html += "<td>"+item.end_date+" "+item.end_time+"</td>";
                } else {
                    $html += "<td>-</td>";
                    $html += "<td>加班</td>";
                    $html += "<td>"+item.over_work_date+"("+item.over_work_hours+"小時)</td>";
                    $html += "<td>-</td>";
                }
                $html += "</tr>";
                if(item.comment) {
                    $html += "<tr>";
                    $html += "<td colspan='5'>備住:"+item.comment+"</td>";
                    $html += "</tr>";
                }
                $("#leave_data_in_modal").append($html);
            })
            $("#validateModal").find(".agree").attr("onclick", "validate_leave('agree', "+apply_id+", \""+apply_type+"\")");
            $("#validateModal").find(".reject").attr("onclick", "validate_leave('reject', "+apply_id+", \""+apply_type+"\")");
            $('#validateModal').modal('toggle');
        }
    })
}

const show_reject_dialog = (apply_id, apply_type, cname, leave_name) => {
    $("#reject_dialog").find(".weui-dialog__title").html(cname+"的"+leave_name);
    $("#reject_dialog").show();
    $("#reject_dialog").find(".todo").attr("onclick", "validate_leave('"+apply_id+"','"+apply_type+"', 0)");
}

const validate_leave = (apply_id, apply_type, is_validate) => {

    const post_data = {
        "userId": document.getElementById('useridfield').textContent,
        //"userId": "U8d41dfb18097f57080858e39b929ce39", 
        "is_validate": is_validate,    // 0=reject or 1=agree
        "apply_type": apply_type //L or O
    }
    if(is_validate == 0) {
        post_data.reject_reason = $("#reject_reason").val();
        $("#toast").find(".weui-toast__content").html("已拒絕簽核");
    } else {
        $("#toast").find(".weui-toast__content").html("已同意簽核");
    }

    promise_call({
        url: "./api/validateleave/"+apply_id, 
        data: post_data, 
        method: "put"
    })
    .then(v => {
        if(v.status == "successful") {
            $("#toast").show();
            $("#reject_dialog").hide();
            $("#apply_"+apply_id).hide();
            setTimeout('$("#toast").hide();',1000);
            //liff.closeWindow();
        } 
    })
}
import { resolve } from "path";

window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
    //initializeApp({})
};

function initializeApp(data) {
    //document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/individuallog/"+data.context.userId, 
        //url: "./api/individuallog/U8d41dfb18097f57080858e39b929ce39", 
        method: "get"
    })
    .then(v => {
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            v.data.map(item => {     
                $html =  '<div class="weui-form-preview mb-3" onclick="show_process_history('+item.id+')">';
                $html += '<div class="weui-form-preview__hd" style="padding: 5px 16px;">';
                if(item.apply_type == 'L') {
                    $html += '    <label class="weui-form-preview__label" style="color: black;">'+item.leave_name+'</label>';
                } else {
                    $html += '    <label class="weui-form-preview__label" style="color: black;">加班</label>';
                }
                if(item.apply_status == 'P') {
                    $html += '    <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;">簽核中</em>';
                } else if(item.apply_status == 'Y') {
                    $html += '    <em class="weui-form-preview__value" style="color: green;font-size: 1.2em;">已簽核</em>';
                } else {
                    $html += '    <em class="weui-form-preview__value" style="color: red;font-size: 1.2em;">已拒絕</em>';
                }
                $html += '</div>';
                $html += '<div class="weui-form-preview__bd" style="padding: 5px 16px;">';
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
                $html += '</div>';
                $html += '</div>';
                $("#leave_data").append($html);
            })
        }
    })
}

function show_process_history(apply_id) {
    promise_call({
        url: "./api/leavelog/"+apply_id, 
        method: "get"
    }).then(v => {
        if(v.data.length > 0) $("#logs_history").find(".weui-dialog__bd").html("");
        $html = "<table style='width: 100%;'><tr><th>簽核人</th><th>簽核時間</th><th>簽核狀態</th></tr>";
        v.data.map(item => {
            if(!item.is_validate) item.is_validate = "-";
            if(item.is_validate == 0) item.is_validate = "拒絕("+item.reject_reason+")";
            if(item.is_validate == 1) item.is_validate = "同意";
            if(!item.validate_time) item.validate_time = "-";
            $html += "<tr><td>"+item.cname+"</td><td>"+item.validate_time+"</td><td>"+item.is_validate+"</td></tr>"
        });
        $html +="</table>"
        $("#logs_history").find(".weui-dialog__bd").html($html);
        $("#logs_history").show();
    })
}
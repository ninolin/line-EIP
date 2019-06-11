window.onload = function (e) {
    liff.init(function (data) {
        initializeApp(data);
    });
    //initializeApp({})
};

function initializeApp(data) {
    document.getElementById('useridfield').textContent = data.context.userId;
    promise_call({
        url: "./api/individuallog/"+data.context.userId, 
        //url: "./api/individuallog/U8d41dfb18097f57080858e39b929ce39", 
        method: "get"
    })
    .then(v => {
        if(v.status != 'successful') {
            alert("get data error");
        } else {
            //alert(JSON.stringify(v));
            if(v.data.length > 0)  $("#leave_data").html("");
            v.data.map(item => {     
                $html =  '<div class="weui-form-preview mb-3">';
                $html += '<div class="weui-form-preview__hd" style="padding: 5px 16px;">';
                if(item.apply_type == 'L') {
                    $html += '    <label class="weui-form-preview__label" style="color: black;">'+item.leave_name+'</label>';
                } else {
                    $html += '    <label class="weui-form-preview__label" style="color: black;">加班</label>';
                }
                $html += '    <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;">';
                if(item.apply_status == 'P') {
                    $html += '簽核中';
                } else if(item.apply_status == 'Y') {
                    $html += '已簽核';
                } else {
                    $html += '已拒絕';
                }
                $html += '</em>';
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
                // $html =  "<tr>";             
                // if(item.apply_type == 'L') {
                //     $html += "<td>"+item.agent_cname+"</td>";
                //     $html += "<td>"+item.leave_name+"</td>";
                //     $html += "<td>"+item.start_date+" "+item.start_time+"</td>";
                //     $html += "<td>"+item.end_date+" "+item.end_time+"</td>";
                // } else {
                //     $html += "<td>-</td>";
                //     $html += "<td>加班</td>";
                //     $html += "<td>"+item.over_work_date+"("+item.over_work_hours+"小時)</td>";
                //     $html += "<td>-</td>";
                // }
                // if(item.apply_status == 'P') {
                //     $html += "<td>簽核中</td>";
                // } else if(item.apply_status == 'Y') {
                //     $html += "<td>已通過</td>";
                // } else {
                //     $html += "<td>已拒絕</td>";
                // }
                
                // $html += "</tr>";
                $("#leave_data").append($html);
            })
        }
    })
}
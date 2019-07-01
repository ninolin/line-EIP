<!doctype html>
<html lang="zh-TW">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EIP</title>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/solid.css" integrity="sha384-+0VIRx+yz1WBcCTXBkVQYIBVNEFH1eP6Zknm16roZCyeNg2maWEpk/l/KsyFKs7G" crossorigin="anonymous">
        <link href="{{ asset('js/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/weui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/public.css') }}" rel="stylesheet">
    </head>
    <body style="background-color: #f1f1f1;">
        <div class="weui-flex"><div class="weui-flex__item mobile_topbar">工時紀錄</div></div>
        <div class="text-center">
            <div class="main-section" id="leave_data">
                <img src="./image/folder.png" style="margin-top:30px;">
                <div>目前無資料</div>
            </div>
            <div id="useridfield" style="display:none"></div>
        </div>
        <div style="display: none;" id="logs_history">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">簽核歷程</strong></div>
                <div class="weui-dialog__bd"></div>
                <div class="weui-dialog__ft">
                    <a onclick="javascript:$('#logs_history').hide();" href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary">確定</a>
                </div>
            </div>
        </div>
        <div id="cancel_dialog" style="display: none;">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">取消</strong></div>
                <div class="weui-dialog__bd">
                    確定要取消嗎?
                </div>
                <div class="weui-dialog__ft">
                    <a href="javascript:$('#cancel_dialog').hide();" class="weui-dialog__btn weui-dialog__btn_default">關閉視窗</a>
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary todo">確定取消</a>
                </div>
            </div>
        </div>
    </body>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
    <!-- <script src="{{ asset('js/line/individuallog.js') }}"></script> -->
    <script>
        window.onload = function (e) {
            alert("aaa");
            liff.init(function (data) {
                alert("bbb");
                alert(JSON.stringify(data));
                initializeApp(data);
            }, function (err) {
                alert(err);
            });
            //initializeApp({})
        };

        function initializeApp(data) {
            alert(data.context.userId);
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
                    if(v.data.length > 0) $("#leave_data").html("");
                    v.data.map(item => {     
                        $html =  '<div class="weui-form-preview mb-3">';
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
                        $html += '<div class="weui-form-preview__bd" style="padding: 5px 16px;" onclick="show_process_history('+item.id+')">';
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
                        if(item.apply_status == 'P') {
                            $html += '<div class="weui-form-preview__ft">';
                            $html += '  <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_cancel_dialog('+item.id+')"><i class="weui-icon-cancel"></i>取消</button>';
                            $html += '</div>';
                        } else if(item.apply_status == 'Y' && item.apply_type == 'L') {
                            const start_date_timestamp = new Date(item.start_date.split('T')[0]).getTime()-28800;
                            const today_timestamp = new Date().getTime();
                            //最後取消請假的時間是當天
                            if(today_timestamp < start_date_timestamp) {
                                $html += '<div class="weui-form-preview__ft">';
                                $html += '  <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_cancel_dialog('+item.id+')"><i class="weui-icon-cancel"></i>取消</button>';
                                $html += '</div>';
                            }
                        } else if(item.apply_status == 'Y' && item.apply_type == 'O') {
                            const start_date_timestamp = new Date(item.over_work_date).getTime()-28800;
                            const today_timestamp = new Date().getTime();
                            //最後取消請假的時間是當天
                            if(today_timestamp < start_date_timestamp) {
                                $html += '<div class="weui-form-preview__ft">';
                                $html += '  <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_cancel_dialog('+item.id+')"><i class="weui-icon-cancel"></i>取消</button>';
                                $html += '</div>';
                            }
                        }
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

        function show_cancel_dialog(apply_id) {
            $("#cancel_dialog").show();
            $("#cancel_dialog").find(".todo").attr("onclick", "cancel_leave('"+apply_id+"')");
        }

        function cancel_leave(apply_id) {
            promise_call({
                url: "./api/individuallog/"+apply_id,
                method: "put"
            }).then(v => {
                window.location.reload();
            })
        }
    </script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
</html>
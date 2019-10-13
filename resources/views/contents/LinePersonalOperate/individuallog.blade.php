<!doctype html>
<html lang="zh-TW">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EIP</title>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/all.min.css') }}" rel="stylesheet">
        <link href="{{ asset('js/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/weui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/public.css') }}" rel="stylesheet">
        <style>
            .logs_history_table tbody tr:nth-child(even){
                background: #e7e7e7
            }
            .logs_history_table thead {
                background: #53b4b0;
                color: white;
            }
            .logs_history_table {
                width: 100%;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body style="background-color: #f1f1f1;">
        <div class="weui-flex">
            <div class="placeholder mobile_topbar" style="padding-left: 5px;">
                <i class="fas fa-arrow-left" onclick="javascript:{history.go(-1)}"></i>
            </div>
            <div class="weui-flex__item mobile_topbar">工時紀錄a</div>
        </div>
        <div>
            <div class="weui-cells__title"></div>
            <div class="weui-cells">
                @if ($next_y_annual_hours >= 0)
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>明年可使用</p>
                        </div>
                        <div class="weui-cell__ft">{{$next_y_annual_hours}}小時</div>
                    </div>
                @endif
                @if ($pre_y_annual_use_hours >= 0)
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>去年未使用</p>
                        </div>
                        <div class="weui-cell__ft">{{$pre_y_annual_use_hours}}小時</div>
                    </div>
                @endif
                @if ($is_annual)
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>今年可使用</p>
                        </div>
                        <div class="weui-cell__ft">{{$annual_hours}}小時</div>
                    </div>
                @endif
                @if ($show_success_hours)
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>今年已簽核</p>
                        </div>
                        <div class="weui-cell__ft">{{$success_hours}}小時</div>
                    </div>
                @endif
                @if ($show_process_hours)
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>今年簽核中</p>
                        </div>
                        <div class="weui-cell__ft">{{$process_hours}}小時</div>
                    </div>
                @endif
            </div>
            <div class="weui-cells__title"></div>
            <div class="main-section" id="leave_data">
                @if (count($leaves) > 0)
                    @foreach($leaves as $leave)
                        <div class="weui-form-preview mb-3"> 
                            <div class="weui-form-preview__hd" style="padding: 5px 16px;">
                                @if ($leave->apply_type == 'L')
                                    <label class="weui-form-preview__label" style="color: black;">{{$leave->leave_name}}</label>
                                @else
                                    <label class="weui-form-preview__label" style="color: black;">加班</label>
                                @endif
                                <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;">
                                    @if ($leave->apply_status == 'P')
                                        <p style="color: #007bff;font-size: 1.2em;">簽核中</p>
                                    @elseif ($leave->apply_status == 'Y')
                                        <p style="color: green;font-size: 1.2em;">已簽核</p>
                                    @elseif ($leave->apply_status == 'N')
                                        <p style="color: red;font-size: 1.2em;">已拒絕</p>
                                    @else
                                        <p style="color: #6c757d;font-size: 1.2em;">已取消</p>
                                    @endif
                                </em>
                            </div>
                            <div class="weui-form-preview__bd" style="padding: 5px 16px;">
                            @if ($leave->apply_type == 'L')
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">代理人</span>
                                    <span class="weui-form-preview__value">{{$leave->agent_cname}}</span>
                                </div>
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">開始時間</span>
                                    <span class="weui-form-preview__value">{{$leave->start_date}}</span>
                                </div>
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">結束時間</span>
                                    <span class="weui-form-preview__value">{{$leave->end_date}}</span>
                                </div>
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">請假事由</span>
                                    <span class="weui-form-preview__value">{{$leave->comment}}</span>
                                </div>
                            @else
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">加班時間</span>
                                    <span class="weui-form-preview__value">{{$leave->over_work_date}}</span>
                                </div>
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">加班小時</span>
                                    <span class="weui-form-preview__value">{{$leave->over_work_hours}}</span>
                                </div>
                                <div class="weui-form-preview__item">
                                    <span class="weui-form-preview__label">加班事由</span>
                                    <span class="weui-form-preview__value">{{$leave->comment}}</span>
                                </div>
                            @endif
                                <ul class="weui-media-box__info" style="color:#2c66bc">
                                    <li class="weui-media-box__info__meta" onclick="show_process_history('{{$leave->id}}')">簽核歷程</li>
                                </ul>
                            </div>
                            @if ($leave->apply_status == 'P')
                                <div class="weui-form-preview__ft">
                                    <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_cancel_dialog('{{$leave->id}}')"><i class="weui-icon-cancel"></i>取消</button>
                                </div>
                            @elseif ($leave->apply_status == 'Y' && $leave->apply_type == 'L' && $today < $leave->start_date)
                                <!--最後取消請假的時間是當天-->
                                <div class="weui-form-preview__ft">
                                    <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_cancel_dialog('{{$leave->id}}')"><i class="weui-icon-cancel"></i>取消</button>';
                                </div>
                            @elseif ($leave->apply_status == 'Y' && $leave->apply_type == 'O' && $today < $leave->start_date)
                                <div class="weui-form-preview__ft">
                                    <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_cancel_dialog('{{$leave->id}}')"><i class="weui-icon-cancel"></i>取消</button>';
                                </div>
                            @endif
                        </div>
                        <br>
                    @endforeach
                @else
                    <div class="weui-cells">
                        <div class="weui-cell">
                            <div class="weui-cell__bd">
                                <p>目前無資料</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div id="useridfield" style="display:none"></div>
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
        <div class="js_dialog" id="logs_history" style="display: none;">
            <div class="weui-mask"></div>
            <div class="weui-half-screen-dialog">
                <div class="weui-half-screen-dialog__hd">
                    <div class="weui-half-screen-dialog__hd__side">
                        <button class="weui-icon-btn weui-icon-btn_close" onclick="javascript:$('#logs_history').hide();">關閉</button>
                    </div>
                    <div class="weui-half-screen-dialog__hd__main">
                        <strong class="weui-half-screen-dialog__title">簽核歷程</strong>
                    </div>
                </div>
                <div class="weui-half-screen-dialog__bd"></div>
            </div>
        </div>
    </body>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script>
        function show_process_history(apply_id) {
            promise_call({
                url: "/api/leavelog/process/"+apply_id, 
                method: "get"
            }).then(v => {
                if(v.data.length > 0) $("#logs_history").find(".weui-half-screen-dialog__bd").html("");
                $html = "<table class='logs_history_table text-center'>";
                $html +="<thead><tr><th>簽核人</th><th>簽核時間</th><th>簽核狀態</th></tr></thead>";
                $html +="<tbody>"
                v.data.map(item => {
                    if(!item.is_validate) item.is_validate = "-";
                    if(item.is_validate == 0) item.is_validate = "拒絕("+item.reject_reason+")";
                    if(item.is_validate == 1) item.is_validate = "同意";
                    if(!item.validate_time) item.validate_time = "-";
                    $html += "<tr><td>"+item.cname+"</td><td>"+item.validate_time+"</td><td>"+item.is_validate+"</td></tr>"
                });
                $html +="</tbody></table>"
                $("#logs_history").find(".weui-half-screen-dialog__bd").html($html);
                $("#logs_history").show();
            })
        }

        function show_cancel_dialog(apply_id) {
            $("#cancel_dialog").show();
            $("#cancel_dialog").find(".todo").attr("onclick", "cancel_leave('"+apply_id+"')");
        }

        function cancel_leave(apply_id) {
            promise_call({
                url: "../../../api/individuallog/"+apply_id,
                method: "put"
            }).then(v => {
                window.location.reload();
            })
        }
    </script>
</html>
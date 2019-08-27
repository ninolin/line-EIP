<!doctype html>
<html lang="zh-TW">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EIP</title>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/solid.css" integrity="sha384-+0VIRx+yz1WBcCTXBkVQYIBVNEFH1eP6Zknm16roZCyeNg2maWEpk/l/KsyFKs7G" crossorigin="anonymous">
        <link href="{{ asset('css/weui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/public.css') }}" rel="stylesheet">
    </head>
    <body style="background-color: #f1f1f1;">
        <div class="weui-flex"><div class="weui-flex__item mobile_topbar">工時紀錄</div></div>
        <div class="text-center">
            <div class="weui-cells__title"></div>
            <div class="weui-cells">
                @if ($is_annual)
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <p>可使用</p>
                        </div>
                        <div class="weui-cell__ft">{{$annual_hours}}小時</div>
                    </div>
                @endif
                <div class="weui-cell">
                    <div class="weui-cell__bd">
                        <p>已簽核</p>
                    </div>
                    <div class="weui-cell__ft">{{$success_hours}}小時</div>
                </div>
                <div class="weui-cell">
                    <div class="weui-cell__bd">
                        <p>簽核中</p>
                    </div>
                    <div class="weui-cell__ft">{{$process_hours}}小時</div>
                </div>
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
                            <div class="weui-form-preview__bd" style="padding: 5px 16px;" onclick="show_process_history('{{$leave->id}}')">
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
    <!-- <script src="{{ asset('js/line/individuallog.js') }}"></script> -->
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script>
        function show_process_history(apply_id) {
            promise_call({
                url: "/api/leavelog/"+apply_id, 
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
                url: "../../../api/individuallog/"+apply_id,
                method: "put"
            }).then(v => {
                window.location.reload();
            })
        }
    </script>
</html>
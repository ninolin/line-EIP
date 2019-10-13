<!doctype html>
<html lang="zh-TW">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EIP</title>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link href="{{ asset('css/all.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/weui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/public.css') }}" rel="stylesheet">
        <link href="{{ asset('css/validateLeave.css') }}" rel="stylesheet">
        <style>
            .other_leaves_table tbody tr:nth-child(even){
                background: #e7e7e7
            }
            .other_leaves_table thead {
                background: #53b4b0;
                color: white;
            }
            .other_leaves_table {
                width: 100%;
                margin-bottom: 20px;
            }
            .weui-form-preview {
                margin-bottom: 15px;
            }
            .prev_page {

            }
        </style>
    </head>
    <body style="background-color: #f1f1f1;">
        <div class="weui-flex">
            <div class="placeholder mobile_topbar" style="padding-left: 5px;">
                <i class="fas fa-arrow-left" onclick="javascript:{history.go(-1)}"></i>
            </div>
            <div class="weui-flex__item mobile_topbar">
                @if ($type == 'unvalidate')
                    未簽核工時
                @else
                    已簽核工時
                @endif
            </div>
        </div>
        <div class="text-center">
            <div class="main-section">
                <div id="leave_data">
                    @if (count($leaves) > 0) 
                        @foreach($leaves as $leave)
                            <div class="weui-form-preview" id="apply_{{$leave->id}}">
                                <div class="weui-form-preview__hd" style="padding: 5px 16px;">
                                @if ($leave->apply_type === 'L')
                                    <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;text-align:left;">{{$leave->leave_name}}</em>
                                @else
                                    <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;text-align:left;">加班</em>
                                @endif
                                </div>
                                <div class="weui-form-preview__bd" style="padding: 5px 16px;">
                                    <div class="weui-form-preview__item">
                                        <span class="weui-form-preview__label">申請人</span>
                                        <span class="weui-form-preview__value">{{$leave->cname}}</span>
                                    </div>
                                @if ($leave->apply_type === 'L')
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
                                    @if ($leave->apply_type === 'L')
                                    <ul class="weui-media-box__info" style="color:#bc2c2c">
                                        <li class="weui-media-box__info__meta" onclick="show_other_leaves('{{$leave->id}}', '{{$leave->apply_user_no}}')">申請人未發生休假</li>
                                    </ul>
                                    @endif
                                    @if ($type == 'unvalidate')
                                        <div class="weui-form-preview__ft">
                                            <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="validate_leave('{{$leave->process_id}}', '{{$leave->id}}', '{{$leave->apply_type}}', 1)"><i class="weui-icon-success"></i>同意</button>
                                            <button type="button" class="weui-form-preview__btn weui-form-preview__btn_primary" onclick="show_reject_dialog('{{$leave->process_id}}', '{{$leave->id}}', '{{$leave->apply_type}}', '{{$leave->cname}}', '{{$leave->leave_name}}')"><i class="weui-icon-cancel"></i>拒絕</button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <img src="./image/folder.png" style="margin-top:30px;">
                    @endif
                </div>
                <div id="useridfield" style="display:none">{{$login_user_no}}</div>
                <div id="result"></div>
            </div>
        </div>
        <div class="js_dialog" id="other_leaves" style="display: none;">
            <div class="weui-mask"></div>
            <div class="weui-half-screen-dialog">
                <div class="weui-half-screen-dialog__hd">
                    <div class="weui-half-screen-dialog__hd__side">
                        <button class="weui-icon-btn weui-icon-btn_close" onclick="javascript:$('#other_leaves').hide();">關閉</button>
                    </div>
                    <div class="weui-half-screen-dialog__hd__main">
                        <strong class="weui-half-screen-dialog__title">申請人未發生請假</strong>
                    </div>
                </div>
                <div class="weui-half-screen-dialog__bd"></div>
            </div>
        </div>
    </body>
    <!-- Reject Dialog -->
    <div id="reject_dialog" style="display: none;">
        <div class="weui-mask"></div>
        <div class="weui-dialog">
            <div class="weui-dialog__hd"><strong class="weui-dialog__title">林佳誼的事假</strong></div>
            <div class="weui-dialog__bd">
                <input id="reject_reason" class="weui-input" type="text" placeholder="請輸入拒絕原因"/>
            </div>
            <div class="weui-dialog__ft">
                <a href="javascript:$('#reject_dialog').hide();" class="weui-dialog__btn weui-dialog__btn_default">取消</a>
                <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary todo">拒絕</a>
            </div>
        </div>
    </div>
    <!-- Toast -->
    <div id="toast" style="display: none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast">
            <i class="weui-icon-success-no-circle weui-icon_toast"></i>
            <p class="weui-toast__content">審核</p>
        </div>
    </div>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script>
        const validate_leave = (process_id, apply_id, apply_type, is_validate) => {

            const post_data = {
                "userId": document.getElementById('useridfield').textContent,
                "is_validate": is_validate, // 0=reject or 1=agree
                "apply_type": apply_type,   //L or O
                "process_id": process_id    //apply_process_id
            }
            if(is_validate == 0) {
                post_data.reject_reason = $("#reject_reason").val();
                $("#toast").find(".weui-toast__content").html("已拒絕簽核");
            } else {
                $("#toast").find(".weui-toast__content").html("已同意簽核");
            }

            promise_call({
                url: "../../api/validateleave/"+apply_id, 
                data: post_data, 
                method: "put"
            })
            .then(v => {
                if(v.status == "successful") {
                    $("#toast").show();
                    $("#reject_dialog").hide();
                    $("#apply_"+apply_id).hide();
                    setTimeout('$("#toast").hide();',1000);
                } 
            })
        }
        const show_reject_dialog = (process_id, apply_id, apply_type, cname, leave_name) => {
            if(apply_type == 'O') leave_name = '加班';
            $("#reject_dialog").find(".weui-dialog__title").html(cname+"的"+leave_name);
            $("#reject_dialog").show();
            $("#reject_dialog").find(".todo").attr("onclick", "validate_leave('"+process_id+"', '"+apply_id+"','"+apply_type+"', 0)");
        }
        const show_other_leaves = (apply_id, apply_user_no) => {
            promise_call({
                url: "../../api/validateleave/show_other_leaves/"+apply_user_no, 
                method: "get"
            })
            .then(v => {
                if(v.status == "successful") {
                    if(v.data.length > 0) $("#other_leaves").find(".weui-half-screen-dialog__bd").html("");
                    $html = "<table class='other_leaves_table text-center'>";
                    $html += "<thead><tr><th>假別</th><th>起</th><th>迄</th><th>狀態</th></tr></thead>";
                    $html += "<tbody>"
                    v.data.map(item => {
                        $html += "<tr>";
                        if(apply_id == item.id) {
                            $html += "  <td>"+item.leave_name+"(本次)</td>";
                        } else {
                            $html += "  <td>"+item.leave_name+"</td>";
                        }
                        $html += "  <td>"+item.start_date_f1+"</td>";
                        $html += "  <td>"+item.end_date_f1+"</td>";
                        if(item.apply_status == 'Y') {
                            $html += "  <td>已通過</td>";
                        } else if(item.apply_status == 'P') {
                            $html += "  <td>簽核中</td>";
                        } else if(item.apply_status == 'N') {
                            $html += "  <td>已拒絕</td>";
                        } else {
                            $html += "  <td>已取消</td>";
                        }
                        $html += "</tr>";
                    });
                    $html += "</tbody></table>";
                    $("#other_leaves").find(".weui-half-screen-dialog__bd").html($html);
                    $("#other_leaves").show();
                } 
            })
        }
    </script>
</html>
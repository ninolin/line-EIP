<!doctype html>
<html lang="zh-TW">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>EIP</title>
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
        <link href="{{ asset('js/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/all.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/weui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/applyLeave.css') }}" rel="stylesheet">
        <link href="{{ asset('css/public.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="weui-flex"><div class="weui-flex__item mobile_topbar">請假申請</div></div>
        <div id="useridfield" style="display:none"></div>
        <div class="weui-cells weui-cells_form">
            <div class="weui-cell weui-cell_vcode">
                <div class="weui-cell__hd"><label class="weui-label">假別</label></div>
                <div class="weui-cell__bd">
                    <select id="leaveType" class="weui-select">
                        @foreach($leavetypes as $type)
                            <option value="{{$type->name}}">{{$type->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="weui-cell weui-cell_vcode">
                <div class="weui-cell__hd"><label class="weui-label">代理人</label></div>
                <div class="weui-cell__bd">
                    <select id="leaveAgent" class="weui-select">
                        @foreach($users as $user)
                            <option value="{{$user->NO}}">{{$user->cname}}-{{$user->username}}</option>
                        @endforeach
                    </select>
                 </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__hd"><label for="" class="weui-label">開始時間</label></div>
                <div class="weui-cell__bd weui-flex">
                    <input class="weui-input weui-flex__item" type="date" id="startDate" min="{{$nowdate}}" value="{{$nowdate}}" placeholder=""/>
                    <div class="weui-input weui-flex__item" id="startTime" onclick="setTime('startTime')"></div>
                </div>
            </div>
            <div class="weui-cell">
                <div class="weui-cell__hd"><label for="" class="weui-label">結束時間</label></div>
                <div class="weui-cell__bd weui-flex">
                    <input class="weui-input weui-flex__item" type="date" id="endDate"  min="{{$nowdate}}" value="{{$nowdate}}" placeholder=""/>
                    <div class="weui-input weui-flex__item" id="endTime" onclick="setTime('endTime')"></div>
                </div>
            </div>
            <div class="weui-cells weui-cells_form">
                <div class="weui-cell">
                    <div class="weui-cell__bd">
                        <textarea class="weui-textarea" placeholder="請假事由" rows="3" id="comment"></textarea>
                    </div>
                </div>
            </div>
            <div class="weui-btn-area">
                <a class="weui-btn weui-btn_primary mobile_btn" href="javascript:" id="showTooltips" onclick="apply_leave()">送出請假</a>
            </div>
        </div>
        <div style="display: none;" id="error_alert">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">錯誤</strong></div>
                <div class="weui-dialog__bd">申請失敗訊息</div>
                <div class="weui-dialog__ft">
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" onclick="close_dialog()">確定</a>
                </div>
            </div>
        </div>
        <div style="display: none;" id="no_bind_alert">
            <div class="weui-mask"></div>
            <div class="weui-dialog">
                <div class="weui-dialog__hd"><strong class="weui-dialog__title">錯誤</strong></div>
                <div class="weui-dialog__bd">目前未完成綁定，無法使用Everplast員工服務</div>
                <div class="weui-dialog__ft">
                    <a href="javascript:;" class="weui-dialog__btn weui-dialog__btn_primary" onclick="close_no_bind_alert()">確定</a>
                </div>
            </div>
        </div>
        <div id="toast" style="display: none;">
            <div class="weui-mask_transparent"></div>
            <div class="weui-mask"></div>
            <div class="weui-toast">
                <i class="weui-loading weui-icon_toast"></i>
                <p class="weui-toast__content">送出中...</p>
            </div>
        </div>
        <div class="js_dialog" id="time_select" style="display: none;">
            <div class="weui-mask"></div>
            <div class="weui-half-screen-dialog">
                <div class="weui-half-screen-dialog__hd" style="height:40px;">
                    <div class="weui-half-screen-dialog__hd__side">
                        <button class="weui-icon-btn weui-icon-btn_close" onclick="javascript:$('#time_select').hide();">關閉</button>
                    </div>
                </div>
                <div class="weui-half-screen-dialog__bd" style="padding-bottom: 25px;">
                    <div class="weui-flex" style="padding: 5px;">
                        <div class="weui-flex__item" style="text-align: center" id="add_hour" onclick="time_calculate('add_hour')"><i class="fas fa-angle-up"></i></div>
                        <div></div>
                        <div class="weui-flex__item" style="text-align: center" id="add_min" onclick="time_calculate('add_min')"><i class="fas fa-angle-up"></i></div>
                    </div>
                    <div class="weui-flex" style="padding: 5px;">
                        <div class="weui-flex__item" style="text-align: center" id="apply_time_hour"></div>
                        <div>:</div>
                        <div class="weui-flex__item" style="text-align: center" id="apply_time_min"></div>
                    </div>
                    <div class="weui-flex" style="padding: 5px;">
                        <div class="weui-flex__item" style="text-align: center" id="sub_hour" onclick="time_calculate('sub_hour')"><i class="fas fa-angle-down"></i></div>
                        <div></div>
                        <div class="weui-flex__item" style="text-align: center" id="sub_min" onclick="time_calculate('sub_min')"><i class="fas fa-angle-down"></i></div>
                    </div>
                    <div class="weui-flex" style="padding: 5px;">
                        <div class="weui-flex__item" style="text-align: center">
                            <a class="weui-btn weui-btn_primary mobile_btn" href="javascript:" id="showTooltips" onclick="javascript:$('#time_select').hide();">確認</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/line/applyleave.js') }}"></script>
        <script src="{{ asset('js/restcall.js') }}"></script>
        <script type="text/javascript" src="https://res.wx.qq.com/open/libs/weuijs/1.1.4/weui.min.js"></script>
        <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    </body>
</html>

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
        <link href="https://unpkg.com/gijgo@1.9.13/css/gijgo.min.css" rel="stylesheet" type="text/css" />
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
                        <input class="weui-input weui-flex__item" id="startTime" onclick="setTime('startTime')" value=""/>
                    </div>
                </div>
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label for="" class="weui-label">結束時間</label></div>
                    <div class="weui-cell__bd weui-flex">
                        <input class="weui-input weui-flex__item" type="date" id="endDate"  min="{{$nowdate}}" value="{{$nowdate}}" placeholder=""/>
                        <input class="weui-input weui-flex__item" id="endTime" onclick="setTime('endTime')" value=""/>
                    </div>
                </div>
                <div class="weui-cell">
                    <div class="weui-cell__hd"><label for="" class="weui-label">結束時間</label></div>
                    <div class="weui-cell__bd">
                        <input class="weui-input" id="testDate" placeholder=""/>
                    </div>
                </div>
                <div class="weui-cells weui-cells_form">
                    <div class="weui-cell">
                        <div class="weui-cell__bd">
                            <textarea class="weui-textarea" placeholder="請假事由" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="weui-btn-area">
                    <a class="weui-btn weui-btn_primary mobile_btn" href="javascript:" id="showTooltips" onclick="apply_leave()">送出請假</a>
                </div>
                <!-- <div class="weui-btn-area">
                    <button type="button" class="btn-c" onclick="apply_leave()"><i class="fas fa-sign-in-alt"></i>申請</button>
                </div> -->
            </div>
        <!--</div>-->
                <!-- <div style="display:none">
                    <div class="weui-cells weui-cells_form">
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">加班日</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" type="date" value=""/>
                            </div>
                        </div>
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">加班小時</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" type="datetime-local" value="" placeholder=""/>
                            </div>
                        </div>
                        <div class="weui-cells weui-cells_form">
                            <div class="weui-cell">
                                <div class="weui-cell__bd">
                                    <textarea class="weui-textarea" placeholder="加班事由" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="weui-btn-area">
                            <a href="javascript:;" class="weui-btn weui-btn_primary">加班申請</a>
                        </div>
                    </div>
                </div> -->
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
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/line/applyleave.js') }}"></script>
        <script src="{{ asset('js/restcall.js') }}"></script>
        <script type="text/javascript" src="https://res.wx.qq.com/open/libs/weuijs/1.1.4/weui.min.js"></script>
        <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    </body>
</html>

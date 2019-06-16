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
        <div class="weui-flex">
            <div class="weui-flex__item mobile_topbar">加班申請</div>
        </div>
        <div class="text-center">
            <div class="main-section">
                <div id="useridfield" style="display:none"></div>
                    <div class="weui-cells weui-cells_form">
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">加班日</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" id="overworkDate"  type="date" value="{{$nowdate}}"/>
                            </div>
                        </div>
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">加班小時</label></div>
                            <div class="weui-cell__bd">
                                <select class="weui-select" name="select2" id="overworkHour">
                                    <option>1</option>
                                    <option>2</option>
                                    <option>3</option>
                                    <option>4</option>
                                    <option>5</option>
                                    <option>6</option>
                                    <option>7</option>
                                    <option>8</option>
                                    <option>9</option>
                                    <option>10</option>
                                    <option>11</option>
                                    <option>12</option>
                                </select>
                            </div>
                        </div>
                        <div class="weui-cells weui-cells_form">
                            <div class="weui-cell">
                                <div class="weui-cell__bd">
                                    <textarea class="weui-textarea" placeholder="加班事由" rows="3" id="comment"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="weui-btn-area">
                            <a href="javascript:;" class="weui-btn weui-btn_primary mobile_btn" onclick="apply_overwork()">加班申請</a>
                        </div>
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
        <script src="{{ asset('js/line/applyoverwork.js') }}"></script>
        <script src="{{ asset('js/restcall.js') }}"></script>
        <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    </body>
</html>

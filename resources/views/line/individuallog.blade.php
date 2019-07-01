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
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script src="{{ asset('js/line/individuallog.js') }}"></script>
</html>
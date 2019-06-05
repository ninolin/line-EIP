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
                <!-- <div class="weui-form-preview mb-3">
                    <div class="weui-form-preview__hd" style="padding: 5px 16px;">
                        <label class="weui-form-preview__label" style="color: black;">事假</label>
                        <em class="weui-form-preview__value" style="color: black;font-size: 1.2em;">已審核</em>
                    </div>
                    <div class="weui-form-preview__bd" style="padding: 5px 16px;">
                        <div class="weui-form-preview__item">
                            <span class="weui-form-preview__label">代理人</span>
                            <span class="weui-form-preview__value">nino</span>
                        </div>
                        <div class="weui-form-preview__item">
                            <span class="weui-form-preview__label">開始時間</span>
                            <span class="weui-form-preview__value">2019-09-11</span>
                        </div>
                        <div class="weui-form-preview__item">
                            <span class="weui-form-preview__label">結束時間</span>
                            <span class="weui-form-preview__value">2019-09-11</span>
                        </div>
                        <div class="weui-form-preview__item">
                            <span class="weui-form-preview__label">請假事由</span>
                            <span class="weui-form-preview__value">2019-09-11</span>
                        </div>
                    </div>
                </div> -->
                <!-- <div>
                    <table class="table table-bordered table-striped">
                        <thead class="table-thead">
                            <tr>
                                <th scope="col">代</th>
                                <th scope="col">假</th>
                                <th scope="col">起</th>
                                <th scope="col">迄</th>
                                <th scope="col">狀</th>
                            </tr>
                        </thead>
                        <tbody id="leave_data">
                           <tr><td colspan="5">無資料</td></tr>
                        </tbody>
                    </table>
                </div> -->
                <div id="useridfield" style="display:none"></div>
                <div id="result"></div>
            </div>
        </div>
    </body>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/line/individuallog.js') }}"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
</html>
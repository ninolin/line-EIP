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
            <div id="useridfield" style="display:none"></div>
            <div class="weui-cells" id="leave_type_data"></div>
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
    </body>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script>
        window.onload = function (e) {
            liff.init(function (data) {
                initializeApp(data);
            }, function (err) {
                alert(err);
            });
            //initializeApp({context: {userId: "U8d41dfb18097f57080858e39b929ce39"}});
        };

        function initializeApp(data) {
            document.getElementById('useridfield').textContent = data.context.userId;
            Promise.all([
                promise_call({url: "./api/userlist/checklineid/"+data.context.userId, method: "get"}),
                promise_call({url: "./api/individuallog/leavetype", method: "get"}),
            ])
            .then(v => {
                if(v[0].status == "successful" && v[0].data.length == 0) {
                    $("#no_bind_alert").show();
                    return;
                } 
                if(v[1].status != 'successful') {
                    alert("get data error");
                } else {
                    if(v[1].data.length > 0) $("#leave_type_data").html("");
                    $html = "";
                    v[1].data.unshift({name: '簽核中'});
                    v[1].data.map(item => {     
                        $html +=  '<a class="weui-cell weui-cell_access" href="./individuallog/leavetype/'+item.name+'/'+data.context.userId+'">';
                        $html +=  ' <div class="weui-cell__bd"><p>'+item.name+'</p></div>';
                        $html +=  ' <div class="weui-cell__ft"></div>';
                        $html +=  '</a>';
                    });
                    $html +=  '<a class="weui-cell weui-cell_access" href="./individuallog/leavetype/加班/'+data.context.userId+'">';
                    $html +=  ' <div class="weui-cell__bd"><p>加班</p></div>';
                    $html +=  ' <div class="weui-cell__ft"></div>';
                    $html +=  '</a>';
                    $("#leave_type_data").append($html);
                }
            })
        }

    </script>
</html>
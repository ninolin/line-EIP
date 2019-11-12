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
        <div class="weui-flex"><div class="weui-flex__item mobile_topbar">簽核操作</div></div>
        <div class="text-center">
            <div id="useridfield" style="display:none"></div>
            <div class="weui-cells" id="validate_type_data">
            </div>
        </div>
    </body>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
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
            $html =  '<a class="weui-cell weui-cell_access" href="../validateleave/unvalidate/'+data.context.userId+'">';
            $html +=  ' <div class="weui-cell__bd"><p>未簽核</p></div>';
            $html +=  ' <div class="weui-cell__ft"></div>';
            $html +=  '</a>';
            $html +=  '<a class="weui-cell weui-cell_access" href="../validateleave/validate/'+data.context.userId+'">';
            $html +=  ' <div class="weui-cell__bd"><p>已簽核</p></div>';
            $html +=  ' <div class="weui-cell__ft"></div>';
            $html +=  '</a>';
            $("#validate_type_data").append($html);
        }

    </script>
</html>
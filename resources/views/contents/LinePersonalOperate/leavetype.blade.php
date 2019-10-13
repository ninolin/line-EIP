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
            <div class="weui-cells" id="leave_type_data">
               
            </div>
        </div>
    </body>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    <script src="{{ asset('js/restcall.js') }}"></script>
    <script>
        window.onload = function (e) {
            // liff.init(function (data) {
            //     initializeApp(data);
            // }, function (err) {
            //     alert(err);
            // });
            initializeApp({context: {userId: "U8d41dfb18097f57080858e39b929ce39"}});
        };

        function initializeApp(data) {
            document.getElementById('useridfield').textContent = data.context.userId;
            promise_call({
                url: "./api/individuallog/leavetype", 
                method: "get"
            })
            .then(v => {
                if(v.status != 'successful') {
                    alert("get data error");
                } else {
                    if(v.data.length > 0) $("#leave_type_data").html("");
                    $html = "";
                    v.data.unshift({name: '簽核中'});
                    v.data.map(item => {     
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
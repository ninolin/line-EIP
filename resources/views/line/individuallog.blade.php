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
    </head>
    <body>
        <div class="text-center">
            <div class="main-section">
                <div>
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
                </div>
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
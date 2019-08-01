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
        <link href="{{ asset('css/login.css') }}" rel="stylesheet">
        <!-- <link href="{{ secure_asset('css/login.css') }}" rel="stylesheet"> -->
    </head>
    <body>
        <div class="modal-dialog text-center">
            <div class="main-section">
                <div class="modal-content">
                    <form id="loginForm" class="col-12" method="POST" action="{{ route('doLogin') }}">
                        {{ csrf_field() }}
                        <div class="form-group acc-input">    
                            <input type="text" name="account" class="form-control" placeholder="Enter Username">
                        </div>
                        <div class="form-group pwd-input ">    
                            <input type="password" name="password" class="form-control" placeholder="Enter Password">
                        </div>
                        <div class="form-group" style="display:none">    
                            <input type="text" name="gmail" value=""> 
                            <input type="text" name="token" value="">
                        </div>
                        <div class="container">
                            <div class="row">
                                <div class="col"><button type="button" class="btn-c" onclick="login()"><i class="fas fa-sign-in-alt"></i>一般登入</button></div>
                                <div class="col"><button type="button" class="btn-c" onclick="login('google')"><i class="fas fa-sign-in-alt"></i>Google登入</button></div>                         
                            </div>
                        </div>
                        @if (session('login_status'))
                            <div class="alert alert-danger">
                                {{ session('login_status') }}
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/restcall.js') }}"></script>
        <script src="https://apis.google.com/js/platform.js" async defer></script>
        <script type="text/javascript">
            function login(type) {
                if(type == 'google') {
                    $('#loginForm').attr('action', '{{ route('doGLogin') }}');
                    $('#loginForm').submit();
                } else {
                    $('#loginForm').attr('action', '{{ route('doLogin') }}');
                    $('#loginForm').submit();
                }
            }
        </script>
    </body>
</html>

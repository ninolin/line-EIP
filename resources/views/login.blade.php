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
    </head>
    <body>
        <div class="modal-dialog text-center">
            <div class="main-section">
                <div class="modal-content">
                    <form class="col-12" method="POST" action="{{ route('doLogin') }}">
                        {{ csrf_field() }}
                        <div class="form-group">    
                            <input type="text" name="account" class="form-control" placeholder="Enter Username">
                        </div>
                        <div class="form-group">    
                            <input type="password" name="password" class="form-control" placeholder="Enter Password">
                        </div>
                        <button type="submit" class="btn-c"><i class="fas fa-sign-in-alt"></i>Login</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
    </body>
</html>

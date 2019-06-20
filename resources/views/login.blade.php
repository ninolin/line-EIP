<!doctype html>
<html lang="zh-TW">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="google-signin-client_id" content="675194260883-t0567hq0tr1gvvd1o4e8enqbq39qmupn.apps.googleusercontent.com">
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
                        <div class="form-group acc-input">    
                            <input type="text" name="account" class="form-control" placeholder="Enter Username">
                        </div>
                        <div class="form-group pwd-input ">    
                            <input type="password" name="password" class="form-control" placeholder="Enter Password">
                        </div>
                        <div class="form-group" style="display:none">    
                            <input type="text" name="gmail" value="{{ csrf_token() }}"> 
                            <input type="text" name="token" value="{{ csrf_token() }}">
                        </div>
                        <div class="container">
                            <div class="row">
                                <div class="col"><button type="submit" class="btn-c"><i class="fas fa-sign-in-alt"></i>一般登入</button></div>
                                <div class="col"><div class="g-signin2 col" data-onsuccess="onSignIn" data-width="170" data-height="48"></div></div>
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
            function onSignIn(googleUser) {     
                //document.getElementsByName('gmail').value = googleUser.getBasicProfile().getEmail();
                //document.getElementsByName('token').value = googleUser.getAuthResponse().id_token;
                //$('form').attr('action', '{{ route('doGLogin') }}');
                //$('form').submit();
                //var profile = googleUser.getBasicProfile();
                // console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
                // console.log('Name: ' + profile.getName());
                // console.log('Image URL: ' + profile.getImageUrl());
                // console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
                //var id_token = googleUser.getAuthResponse().id_token;
                //console.log('Token: ' + id_token);
                promise_call({
                    url: "./api/glogin/", 
                    method: "post",
                    data: {
                        gmail: googleUser.getBasicProfile().getEmail(),
                        token: googleUser.getAuthResponse().id_token
                    }
                })
            }
        </script>
    </body>
</html>

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
        <link href="{{ asset('css/applyLeave.css') }}" rel="stylesheet">
    </head>
    <body>
        <div class="text-center">
            <div class="main-section">
                <div>
                    <form class="col-12" method="POST" action="{{ route('doLogin') }}">
                        {{ csrf_field() }}
                        <div class="form-group form-inline">    
                            <label class="col-3">申請人</label>
                            <div class="col-9">
                                Tim
                            </div>
                        </div>
                        <div class="form-group form-inline">    
                            <label class="col-3">假別</label>
                            <select class="form-control col-9">
                                <option>休假</option>
                                <option>病假</option>
                                <option>事假</option>
                            </select>
                        </div>
                        <div class="form-group form-inline">    
                            <label class="col-3">代理人</label>
                            <select class="form-control col-9">
                                <option>休假</option>
                                <option>病假</option>
                                <option>事假</option>
                            </select>
                        </div>
                        <div class="form-group form-inline">  
                            <label class="col-3">起日</label>  
                            <input class="col-6" id="startDate" type="date"/>
                            <select class="form-control col-3" id="startTime">
                                <option>休假</option>
                                <option>病假</option>
                                <option>事假</option>
                            </select>
                        </div>
                        <div class="form-group form-inline">  
                            <label class="col-3">迄日</label>  
                            <input class="col-6" id="endDate" type="date"/>
                            <select class="form-control col-3" id="endTime">
                                <option>休假</option>
                                <option>病假</option>
                                <option>事假</option>
                            </select>
                        </div>
                        <div class="form-group form-inline">
                            <label class="col-3">備註</label>  
                            <textarea class=" col-9 form-control" rows="3" id="comment"></textarea>
                        </div>
                        <button type="submit" class="btn-c"><i class="fas fa-sign-in-alt"></i>申請</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/gijgo/gijgo.js') }}"></script>
        <script src="{{ asset('js/line/applyLeave.js') }}"></script>
    </body>
</html>

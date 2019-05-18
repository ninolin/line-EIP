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
        <link href="{{ asset('css/weui.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/applyLeave.css') }}" rel="stylesheet">
    </head>
    <body>
        
        <div class="weui-tab">
            <div class="weui-navbar">
                <div class="weui-navbar__item weui-bar__item_on" onclick="change_tab(0)">請假</div>
                <div class="weui-navbar__item" id="test" onclick="change_tab(1)">加班</div>
            </div>
            <div class="weui-tab__panel">
                <div>
                    <!-- <div class="weui-cells weui-cells_form">
                        <div class="weui-cell weui-cell_vcode">
                            <div class="weui-cell__hd"><label class="weui-label">假別</label></div>
                            <div class="weui-cell__bd">
                                <select id="leaveType" class="weui-select">
                                    @foreach($leavetypes as $type)
                                        <option value="{{$type->name}}">{{$type->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="weui-cell weui-cell_vcode">
                            <div class="weui-cell__hd">
                                <label class="weui-label">代理人</label>
                            </div>
                            <div class="weui-cell__bd">
                                <select id="leaveAgent" class="weui-select">
                                    @foreach($users as $user)
                                        <option value="{{$user->NO}}">{{$user->cname}}-{{$user->username}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">開始時間</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" type="datetime-local" value="" placeholder=""/>
                            </div>
                        </div>
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">結束時間</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" type="datetime-local" value="" placeholder=""/>
                            </div>
                        </div>
                        <div class="weui-cells weui-cells_form">
                            <div class="weui-cell">
                                <div class="weui-cell__bd">
                                    <textarea class="weui-textarea" placeholder="請假事由" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="weui-btn-area">
                        <button type="button" class="btn-c" onclick="apply_leave()"><i class="fas fa-sign-in-alt"></i>申請</button>
                    </div> -->
                    <!-- <div class="weui-btn-area">
                    <button type="button" class="btn-c" onclick="apply_leave()"><i class="fas fa-sign-in-alt"></i>請假申請</button>
                    </div> -->
                    <div class="text-center">
                        <div class="main-section">
                            <div>
                                <form class="col-12" method="POST">
                                    {{ csrf_field() }}
                                    <div id="useridfield" style="display:none"></div>
                                    <!-- <div class="form-group form-inline">    
                                        <label class="col-3">假別</label>
                                        <select id="leaveType" class="form-control col-9">
                                            @foreach($leavetypes as $type)
                                                <option value="{{$type->name}}">{{$type->name}}</option>
                                            @endforeach
                                        </select>
                                    </div> -->
                                    <div class="weui-cell weui-cell_vcode">
                                        <div class="weui-cell__hd"><label class="weui-label">假別</label></div>
                                        <div class="weui-cell__bd">
                                            <select id="leaveType" class="weui-select">
                                                @foreach($leavetypes as $type)
                                                    <option value="{{$type->name}}">{{$type->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group form-inline">    
                                        <label class="col-3">代理人</label>
                                        <select id="leaveAgent" class="form-control col-9">
                                            @foreach($users as $user)
                                                <option value="{{$user->NO}}">{{$user->cname}}-{{$user->username}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group form-inline">  
                                        <label class="col-3">起日</label>  
                                        <input class="col-6" id="startDate" type="date"/>
                                        <select class="form-control col-3" id="startTime">
                                            <option>00:00</option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                        </select>
                                    </div>
                                    <div class="form-group form-inline">  
                                        <label class="col-3">迄日</label>  
                                        <input class="col-6" id="endDate" type="date"/>
                                        <select class="form-control col-3" id="endTime">
                                            <option>00:00</option>
                                            <option>01:00</option>
                                            <option>02:00</option>
                                            <option>03:00</option>
                                            <option>04:00</option>
                                            <option>05:00</option>
                                            <option>06:00</option>
                                            <option>07:00</option>
                                            <option>08:00</option>
                                            <option>09:00</option>
                                            <option>10:00</option>
                                            <option>11:00</option>
                                            <option>12:00</option>
                                            <option>13:00</option>
                                            <option>14:00</option>
                                            <option>15:00</option>
                                            <option>16:00</option>
                                            <option>17:00</option>
                                            <option>18:00</option>
                                            <option>19:00</option>
                                            <option>20:00</option>
                                            <option>21:00</option>
                                            <option>22:00</option>
                                            <option>23:00</option>
                                        </select>
                                    </div>
                                    <div class="form-group form-inline">
                                        <label class="col-3">備註</label>  
                                        <textarea class=" col-9 form-control" rows="3" id="comment"></textarea>
                                    </div>
                                    <button type="button" class="btn-c" onclick="apply_leave()"><i class="fas fa-sign-in-alt"></i>申請</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="display:none">
                    <div class="weui-cells weui-cells_form">
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">加班日</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" type="date" value=""/>
                            </div>
                        </div>
                        <div class="weui-cell">
                            <div class="weui-cell__hd"><label for="" class="weui-label">加班小時</label></div>
                            <div class="weui-cell__bd">
                                <input class="weui-input" type="datetime-local" value="" placeholder=""/>
                            </div>
                        </div>
                        <div class="weui-cells weui-cells_form">
                            <div class="weui-cell">
                                <div class="weui-cell__bd">
                                    <textarea class="weui-textarea" placeholder="加班事由" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="weui-btn-area">
                            <a href="javascript:;" class="weui-btn weui-btn_primary">加班申請</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/line/applyleave.js') }}"></script>
        <script src="{{ asset('js/restcall.js') }}"></script>
        <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    </body>
</html>

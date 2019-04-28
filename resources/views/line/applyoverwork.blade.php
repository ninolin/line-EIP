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
                    <form class="col-12" method="POST">
                        {{ csrf_field() }}
                        <div id="useridfield" style="display:none"></div>
                        <div class="form-group form-inline">  
                            <label class="col-3">加班日</label>  
                            <input class="col-9" id="overworkDate" type="date"/>
                        </div>
                        <div class="form-group form-inline">  
                            <label class="col-3">加班小時</label>  
                            <select class="form-control col-9" id="overworkHour">
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                                <option>6</option>
                                <option>7</option>
                                <option>8</option>
                                <option>9</option>
                                <option>10</option>
                                <option>11</option>
                                <option>12</option>
                                <option>13</option>
                                <option>14</option>
                                <option>15</option>
                                <option>16</option>
                                <option>17</option>
                                <option>18</option>
                                <option>19</option>
                                <option>20</option>
                                <option>21</option>
                                <option>22</option>
                                <option>23</option>
                                <option>24</option>
                            </select>
                        </div>
                        <div class="form-group form-inline">
                            <label class="col-3">備註</label>  
                            <textarea class=" col-9 form-control" rows="3" id="comment"></textarea>
                        </div>
                        <button type="button" class="btn-c" onclick="apply_overwork()"><i class="fas fa-sign-in-alt"></i>申請</button>
                    </form>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/line/applyoverwork.js') }}"></script>
        <script src="{{ asset('js/restcall.js') }}"></script>
        <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
    </body>
</html>

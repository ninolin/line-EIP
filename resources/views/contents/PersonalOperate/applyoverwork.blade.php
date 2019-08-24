@extends('contents.PersonalOperate.master')
@section('title', 'Home')
@section('content3')
<div class="container-fluid pt-lg-4">
    <form>
        <div class="form-group">
            <label for="exampleInputEmail1">加班日</label>
            <input class="form-control" id="overworkDate"  type="date" value="{{$nowdate}}"/>
        </div>
        <div class="form-group">
            <label for="overworkHour">加班小時</label>
            <select class="form-control" id="overworkHour">
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
            </select>
        </div>
        <div class="form-group">
            <label for="comment">加班事由</label>
            <textarea class="form-control rounded-0" id="comment" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary" onclick="apply_overwork()">加班申請</button>
    </form>
</div>
<div id="useridfield" style="display:none">{{$user_id}}</div>
<!-- <div class="weui-cell">
                        <div class="weui-cell__hd"><label for="" class="weui-label">加班日</label></div>
                        <div class="weui-cell__bd">
                            <input class="weui-input" id="overworkDate"  type="date" value="{{$nowdate}}"/>
                        </div>
                    </div>


                    <div class="weui-cell">
                        <div class="weui-cell__hd"><label for="" class="weui-label">加班小時</label></div>
                        <div class="weui-cell__bd">
                            <select class="weui-select" name="select2" id="overworkHour">
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
                            </select>
                        </div>
                    </div>

    <div class="weui-flex">
        <div class="weui-flex__item mobile_topbar">加班申請</div>
    </div>
    <div class="text-center">
        <div class="main-section">
            <div id="useridfield" style="display:none">{{$user_id}}</div>
                <div class="weui-cells weui-cells_form">
                    <div class="weui-cell">
                        <div class="weui-cell__hd"><label for="" class="weui-label">加班日</label></div>
                        <div class="weui-cell__bd">
                            <input class="weui-input" id="overworkDate"  type="date" value="{{$nowdate}}"/>
                        </div>
                    </div>
                    <div class="weui-cell">
                        <div class="weui-cell__hd"><label for="" class="weui-label">加班小時</label></div>
                        <div class="weui-cell__bd">
                            <select class="weui-select" name="select2" id="overworkHour">
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
                            </select>
                        </div>
                    </div>
                    <div class="weui-cells weui-cells_form">
                        <div class="weui-cell">
                            <div class="weui-cell__bd">
                                <textarea class="weui-textarea" placeholder="加班事由" rows="3" id="comment"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="weui-btn-area">
                        <a href="javascript:;" class="weui-btn weui-btn_primary mobile_btn" onclick="apply_overwork()">加班申請</a>
                    </div>
                </div>
        </div>
    </div>

    <div id="toast" style="display: none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-mask"></div>
        <div class="weui-toast">
            <i class="weui-loading weui-icon_toast"></i>
            <p class="weui-toast__content">送出中...</p>
        </div>
    </div> -->
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/PersonalOperate/applyoverwork.js') }}"></script>
    <script src="https://d.line-scdn.net/liff/1.0/sdk.js"></script>
@endsection
@extends('Layout.app')
@section('css')
    <style type="text/css">
        .mui-card-footer:before, .mui-card-header:after{
            background-color: #ffffff;
        }
        .mui-card-footer a{
            width: 47%;
        }
        .mui-card-content-inner{
            padding: 30px 15px;
        }
        .log a{
            color: #2274ee;
            font-size: .5rem;
        }
        .mui-card-content{
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            height: 4.5rem;
        }
        .num-item{
            width: 50%;
            text-align: center;
            font-size: 2rem;
        }
        .num-item p{
            margin-bottom: 0;
        }
        .num-tip{
            font-size: .8rem;
        }
        .num{
            color: #333333;
            font-size: 1.8rem;
            margin-top: .5rem;
        }
    </style>
@stop
@section('content')

    <header class="mui-bar mui-bar-nav app-header">
        <span class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></span>
        <h1 class="mui-title">资产账户</h1>
    </header>

    <div class="mui-content">
        @foreach($balance_arr as $key => $value)
            <div class="mui-card" style="box-shadow: none !important;">
                <div class="mui-card-header">
                    <div>{{ strtoupper($key) }}</div>
                    <div class="log"><a href="/manage-assets/assets-logs?assets_id={{ $value->assets_id }}">查看明细</a></div>
                </div>
                <div class="mui-card-content">
                    <div class="num-item">
                        <p class="num-tip">可用</p>
                        <p class="num">{{ float_format($value->amount ?? 0) }}</p>
                    </div>
                    <div class="num-item">
                        <p class="num-tip">托管</p>
                        <p class="num">{{ float_format($value->freeze_amount ?? 0) }}</p>
                    </div>
                </div>
                <div class="mui-card-footer">
                    <a href="/manage-assets/charge-page" class="mui-btn" style="background: #2274ee;border: none;color: #ffffff;">充值</a>
                    <a href="/manage-assets/withdraw-page?assets_id={{$value->assets_id}}" class="mui-btn" style="background: #2274ee;border: none;color: #ffffff;">提现</a>
                </div>
            </div>
        @endforeach

    </div>
    <script type="text/javascript">

        mui.init();

    </script>
@stop

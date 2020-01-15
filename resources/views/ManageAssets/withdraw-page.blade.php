@extends('Layout.app')
@section('css')
    <style type="text/css">
        .tip{font-size: 0.7rem;padding: 0.5rem;margin: 0;}
        .publish-box{}
        .publish-box:before{background:none !important;}
        .publish-box:after{background:none !important;}
        .publish-box .mui-input-row:last-child:after{background:none !important;}
        .publish-box .mui-input-row:after{background:#ececec !important;}
        .publish-box .mui-input-row label{font-size: 0.9rem;color: #6b6b6b;}
        .publish-box .mui-input-row input{font-size: 0.9rem;text-align: right;}
        .publish-box .mui-input-row select{width: 65%; direction: rtl;font-size: 0.9rem;}
        .save-btn{padding: 0.6rem 0 !important;font-size: 1.0rem !important;}
        .payment-list{background: #ffffff;padding: 0.6rem;margin-bottom: 0.6rem;}
        .payment-list h2{font-size: 0.9rem;font-weight: normal;margin-bottom: 0.6rem;}
        .payment-list .remark{background: #f5f5f5;overflow: hidden;padding: 0.3rem 0.7rem;font-size: 0.8rem;margin: 0.6rem 0;border-radius: 3px;border: 1px solid #efefef;color: #929292;}
        .payment-list .remark .number{font-size: .7rem;color: #333333;}
        .publish-tip {
            margin-top: 1rem !important;
        }

        .publish-tip p {
            margin: 0;
            font-size: 0.8rem;
        }

        .mui-content>.mui-table-view:first-child{
            margin-top: 0;
        }
        .item{
            display: flex;
            justify-content: space-between;
        }
        .s-area p{
            text-align: right;
        }
        .num{
            font-size: 1.5rem;
        }
        .plus{
            color: red;
        }
        .reduce{
            color: green;
        }
    </style>
@stop
@section('content')

    <header class="mui-bar mui-bar-nav app-header">
        <span class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left" ></span>
        <h1 class="mui-title">{{$title}}</h1>
    </header>

    <div class="mui-content">

        <form class="mui-input-group publish-box">

            <div class="payment-list">
                <h2>数字资产将会转入以下地址</h2>
                <div class="remark copy-btn" id="showUserPicker">
                    <span id="address">{{$userAddress}}</span>
                </div>
            </div>

            <div class="mui-input-row">
                <label>当前余额</label>
                <input type="number" readonly placeholder="{{ float_format($balance)." ".strtoupper($assets) }}">
            </div>

            <div class="mui-input-row">
                <label>数量</label>
                <input type="number" id="num" placeholder="请输入数量（最小1 {{strtoupper($assets)}}）">
            </div>

            <div class="mui-input-row">
                <label>密码</label>
                <input type="password" id="password" placeholder="请输入密码">
            </div>

        </form>
        <div class="mui-content-padded">
            <button type="button" class="mui-btn mui-btn-primary mui-btn-block save-btn" id="withdraw-btn">提交</button>
        </div>

        <!--数据列表-->
        <ul class="mui-table-view">
            @foreach($withdrawLogList as $withdrawLog)
                @if(empty($withdrawLog['tx_hash']))
                <li class="mui-table-view-cell item">
                    @else
                <li class="mui-table-view-cell item" onclick="location.href = 'https://qkiscan.cn/tx/{{ $withdrawLog['tx_hash'] }}'">
                    @endif
                    <div class="num-div">
                    <span class="num reduce">{{ float_format($withdrawLog['amount']) }}</span>
                </div>
                <div class="s-area">
                    <p class="status-p">(ID:{{ $withdrawLog['id'] }}) {{ substr($withdrawLog['tx_hash'], 0, 20) }}...</p>
                    <p class="time-div">{{ $withdrawLog['created_at'] }}</p>
                    @if(empty($withdrawLog['tx_hash']))
                    <p class="time-div">审核中，请等待</p>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    <script type="text/javascript">
        var assetId = '{{\Request::input("assets_id")}}';

        document.getElementById('withdraw-btn').addEventListener('tap',function () {
            var amount = document.getElementById('num').value,
                password = document.getElementById('password').value,
                username = "{{$username}}";

            if(amount <= 0){
                mui.toast('提现数量错误');
                return ;
            }

            if(!password){
                mui.toast('请输入密码');
                return ;
            }

            var p = 'token' + username + password;
            var encrypt_password = sha256(p);

            mui.ajax('/manage-assets/withdraw', {
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: {
                    assets_id: assetId,
                    password: encrypt_password,
                    num: amount
                },
                dataType: 'json',//服务器返回json格式数据
                type: 'post',//HTTP请求类型
                success: function (data) {
                    if (data.code === 0) {
                        mui.alert('提现成功',function () {
                            window.location.reload();
                        });
                    }else{
                        mui.toast(data.msg);
                    }
                }
            });
        });
    </script>
@stop

@extends('Layout.app')
@section('css')
    <style type="text/css">
        .publish-box .mui-input-row label{font-size: 0.9rem;color: #6b6b6b;}
        .publish-box .mui-input-row input{font-size: 0.9rem;text-align: right;}
        .publish-box .mui-input-row select{width: 65%; direction: rtl;font-size: 0.9rem;}
        .payment-list{background: #ffffff;padding: 0.6rem;margin-bottom: 0.6rem;}
        .payment-list h2{font-size: 0.9rem;font-weight: normal;margin-bottom: 0.6rem;}
        .payment-list .remark{background: #f5f5f5;overflow: hidden;padding: 0.3rem 0.7rem;font-size: 0.8rem;margin: 0.6rem 0;border-radius: 3px;border: 1px solid #efefef;color: #929292;}
        .payment-list .remark .number{font-size: .7rem;color: #333333;}
        .publish-tip p {
            margin: 0;
            font-size: 0.8rem;
        }
        .img-area img{
            width: 95%;
            margin: 1rem auto;
        }
    </style>
@stop
@section('content')

    <header class="mui-bar mui-bar-nav app-header">
        <span class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left" ></span>
        <h1 class="mui-title">{{$title}}</h1>
    </header>

    <div class="mui-content">

            <div class="payment-list">
                @if($userAddress)
                <h2>1、复制下列地址转入数资产 <span style="color: red;"></span></h2>
                <div class="remark copy-btn" data-clipboard-text="{{ $officialAddress }}">
                    <span class="mui-pull-left" id="address-text">
                        {{ $officialAddress }}
                    </span>
                    <div class="mui-pull-right number">
                        点击复制
                    </div>
                </div>
                @else
                    <h2 style="text-align: center"><a href="/manage-assets/addre">请先绑定地址</a></h2>
                @endif
                <h2 style="color: red">充值前请确认已经绑定地址，并且使用绑定地址进行充值，避免造成不可挽回的资产损失。</h2>

            </div>

    </div>
    <script src="/js/clipboard.min.js"></script>
    <script type="text/javascript">

        mui.init();

        clipboard = new Clipboard('.copy-btn');

        clipboard.on('success', function (e) {

            mui.toast('地址复制成功');
            e.clearSelection();
        });

        clipboard.on('error', function (e) {
            mui.toast('地址复制失败，请长按复制');
        });

    </script>
@stop

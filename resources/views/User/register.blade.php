@extends('Layout.app')
@section('css')
    <style>
        body{background: #ffffff !important;}
        .captcha-div .mui-input-clear~.mui-icon-clear{
            right: 30%!important;
            top: 20px;
        }
        .tip{background: #ffffff;padding: 0.6rem;}
        .tip p{font-size: 0.8rem;}
    </style>
@stop
@section('content')
    <header class="mui-bar mui-bar-nav app-header">
        <span class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></span>
        <h1 class="mui-title">注册</h1>
    </header>
    <div class="mui-content">
        <form class="mui-input-group login-box">

            <div class="mui-input-row">
                <input type="text" class="mui-input-clear login-input" placeholder="请输入用户名" data-input-clear="5" id="username"><span class="mui-icon mui-icon-clear mui-hidden"></span>
            </div>

            <div class="mui-input-row">
                <input type="password" class="mui-input-clear login-input" placeholder="请输入密码" data-input-clear="5" id="password"><span class="mui-icon mui-icon-clear mui-hidden"></span>
            </div>

            <div class="mui-input-row">
                <input type="password" class="mui-input-clear login-input" placeholder="请再次输入密码" data-input-clear="5" id="password2"><span class="mui-icon mui-icon-clear mui-hidden"></span>
            </div>

            <button type="button" class="mui-btn mui-btn-primary mui-btn-block login-btn" id="register-btn">注册</button>

        </form>
    </div>

    <script type="text/javascript" charset="utf-8">

        var registerBtn = document.getElementById("register-btn");
        registerBtn.addEventListener("tap",function () {

            var username = mui("#username")[0].value;
            var password = mui("#password")[0].value;
            var password2 = mui("#password2")[0].value;
            var invite_uid = "{{ \Request::input('invite_uid') }}";

            if (username == '') {
                mui.toast('请输入用户名');
                return false;
            }
            if (password == '') {
                mui.toast('请输入密码');
                return false;
            }
            if (password2 == '') {
                mui.toast('请再次输入密码');
                return false;
            }
            if (password != password2) {
                mui.toast('两次密码不一致');
                return false;
            }
            var p = 'token' + username + password;
            var encrypt_password = sha256(p);

            mui.ajax("register-submit",
                {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data:
                        {
                            username:username,
                            password:password,
                            password2:password2,
                            encrypt_password:encrypt_password,
                            invite_uid:invite_uid
                        },
                    dataType: 'json',//服务器返回json格式数据
                    type: 'post',//HTTP请求类型
                    timeout:10000,//超时时间设置为10秒；
                    success: function (data)
                    {
                        if (data.code === 0)
                        {
                            mui.alert(data.msg,function () {
                                window.location.href="login";
                            });
                        }
                        else
                        {
                            mui.toast(data.msg);
                        }
                    },
                    error:function(xhr,type,errorThrown)
                    {
                        mui.toast("网络错误");
                    }
                }
            );
        });

    </script>

@stop

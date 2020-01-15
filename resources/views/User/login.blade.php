@extends('Layout.app')
@section('css')
    <style>
        body{background: #ffffff !important;}
        .login-link{font-size: 0.9rem;padding: 0.5rem 0;}
        .register-link{color: #007aff;}
        .mui-input-row .mui-input-clear~.mui-icon-clear{top:20px;}
    </style>
@stop
@section('content')

    <header class="mui-bar mui-bar-nav app-header">
        <h1 class="mui-title">登录</h1>
    </header>
    <div class="mui-content">
        <form class="mui-input-group login-box">
            <div class="mui-input-row">
                <input type="text" class="mui-input-clear login-input" placeholder="请输入用户名" id="username"><span class="mui-icon mui-icon-clear mui-hidden"></span>
            </div>
            <div class="mui-input-row">
                <input type="password" class="mui-input-clear login-input" placeholder="请输入密码" id="password"><span class="mui-icon mui-icon-clear mui-hidden"></span>
            </div>
            <button type="button" class="mui-btn mui-btn-primary mui-btn-block login-btn" id="login-btn">登录</button>
            <div class="login-link">
                <a href="register" class="register-link mui-pull-right">立即注册</a>
            </div>
        </form>
    </div>

    <script type="text/javascript" charset="utf-8">
        var loginBtn = document.getElementById("login-btn");
        loginBtn.addEventListener("tap",function () {

            var username = mui("#username")[0].value;
            var password = mui("#password")[0].value;

            var p = 'token' + username + password;
            var encrypt_password = sha256(p);

            mui.ajax("login-submit",
            {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data:
                    {
                        username:username,
                        encrypt_password:encrypt_password,
                    },
                dataType: 'json',//服务器返回json格式数据
                type: 'post',//HTTP请求类型
                timeout:10000,//超时时间设置为10秒；
                success: function (data)
                {
                    if (data.code === 0)
                    {
                        mui.alert(data.msg,function () {
                            window.location.href="/";
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
            });

        });

    </script>

@stop

@extends('Layout.app')
@section('css')
    <style type="text/css">
        .user-bar {
            position: relative;
            background: #ffffff;
            padding: 0 1rem;
            -webkit-box-shadow: 0 1px 8px #eef2fd;
            box-shadow: 0 1px 8px #eef2fd;
        }

        .user-bar .user-img img {
            height: 100%;
            border-radius: 50%;
        }

        .user-bar .user-info {
            padding: 0 0 0.8rem 0;
        }

        .user-bar .user-info p {
            margin: 0;
            color: #333333;
            font-style: 0.8rem;
        }

        .user-bar .user-info .username {
            height: 2rem;
            line-height: 2rem;
            font-size: 1.3rem;
        }


        .menu-list .item a {
            font-size: 0.9rem;
            line-height: 2rem;
            color: #545454 !important;
        }


        .logout-box {

            padding:0 0.6rem;
        }

        .logout-box a {
            font-size: 0.9rem;
            border:1px solid #e5e5e5;
            border-radius: 3rem;
            background:#fbfbfb;
            color:#666666;
            padding:10px 0;
        }

        .mui-badge {
            padding: 5px !important;
        }


        .user-menu ul {
            background-color: #ffffff !important;
            border-top: none !important;
            border-left: none !important;
        }
        .user-menu ul li {
            padding: 10px 5px !important;
            border-right: none !important;
            border-bottom: none !important;
            text-align: center !important;
        }
        .user-menu ul li span{
            font-size: 1.8rem !important;
        }

        .version{
            color:#666;
            text-align: center;
            font-size:14px;
        }
        .user-bar-cancel{
            margin-left:15px;
        }

        .games-list{
            background:#fff;

        }
        .games-list dl{
            padding:0.8rem;
            overflow: hidden;
            margin:0;
            border-top:4px solid #f6f9fb;
        }
        .games-list dl dt{
            color:#333333;
            font-size:16px;
            margin-bottom:10px;
        }
        .games-list dl dd{
            width:25%;
            float:left;
            margin:0;
            position: relative;
            text-align: center;
        }
        .games-list dl dd img{
            width:50%;
        }
        .mui-badge-danger {
            position: absolute;
            top: 0;
            border-radius: 20px;
            padding: 5px !important;
        }
        .logout-box{
            margin-top:1rem;

        }
        .menu-item{
            background: #ffffff;
            padding: 0 1rem;
            margin-top: 0.3rem;
        }
        .menu-item .item{
            overflow: hidden;
            font-size: 1rem;
            border-bottom: 1px solid #f0f0f0;
            padding: 0.6rem 0;
            position: relative;
        }
        .menu-item .item:last-child{
            border: none;
        }
        .menu-item .item a{
            color: #333333;
            display: block;
            overflow: hidden;
        }
        .menu-item .item img{
            height: 2rem;
            margin-right: 0.3rem;
        }
        .menu-item .item span{
            margin-top: 0.4rem;
            font-size: 0.9rem;
        }
    </style>
@stop
@section('content')
    <header class="mui-bar mui-bar-nav app-header">
        <h1 class="mui-title" style="left:85px;right:85px;">我的</h1>
    </header>

    <div class="mui-content" id="data">
        <div class="user-bar">
            <div class="user-info">
                <p class="username">
                   {{mb_strlen($user['username']) > 20 ? (mb_substr($user['username'], 0, 20).'...') : $user['username']}}
                </p>
                <p>
                    ID: {{$user['id']}} &nbsp;
                </p>
            </div>
        </div>
        <div class="menu-item">
            <div class="item">
                <a href="/manage-assets/addre">
                    <img class="mui-pull-left" src="/images/bind-adress-ico.png?v=1" alt="">
                    <span class="mui-pull-left">绑定地址</span>
                </a>
            </div>
            <div class="item">
                <a href="/manage-assets">
                    <img class="mui-pull-left" src="/images/my-assets-ico.png?v=1" alt="">
                    <span class="mui-pull-left">资产账户</span>
                </a>
            </div>
            <div class="item">
                <a href="/box">
                    <img class="mui-pull-left" src="/images/my-assets-ico.png?v=1" alt="">
                    <span class="mui-pull-left">开箱子</span>
                </a>
            </div>
        </div>
        <div class="logout-box">
            <a href="/user/logout" class="mui-btn mui-btn-outlined mui-btn-block">退出</a>
        </div>
    </div>
@stop

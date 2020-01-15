@extends('Layout.app')
@section('css')
    <style type="text/css">
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
        <span class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></span>
        <h1 class="mui-title">{{ $title }}</h1>
    </header>

    <!--下拉刷新容器-->
    <div id="pullrefresh" class="mui-content mui-scroll-wrapper">
        <div class="mui-scroll">
            <!--数据列表-->
            <ul class="mui-table-view">
                <li class="mui-table-view-cell item" v-cloak v-for="item in list">
                    <div class="num-div">
                        <span v-if="item.amount > 0" class="num reduce">@{{ item.amount }} @{{ item.operate_type_label }}</span>
                        <span v-else-if="item.amount < 0" class="num plus">@{{ item.amount }} @{{ item.operate_type_label }}</span>
                    </div>
                    <div class="s-area">
                        <p class="status-p">@{{ item.remark }}</p>
                        <p class="time-div">@{{ item.created_at }}</p>
                    </div>
                </li>
            </ul>
        </div>

    </div>

    <script type="text/javascript">

        mui.init({
            pullRefresh: {
                container: '#pullrefresh',
                up: {
                    auto: true,
                    contentrefresh: '正在加载...',
                    contentnomore:'没有更多数据了',
                    callback: pullupRefresh
                }
            }
        });

        vm = new Vue({
            el: '#pullrefresh',
            data: {
                list: [],
            },
            methods: {
            },
        });

        var page = 1;
        var assets_id = '{{$_GET['assets_id']}}';

        function pullupRefresh() {
            setTimeout(function () {

                mui.ajax("/manage-assets/balance-log", {
                    data: {
                        assets_id: assets_id,
                        page: page
                    },
                    dataType: 'json',//服务器返回json格式数据
                    type: 'get',//HTTP请求类型
                    success: function (data) {

                        if (data.code === 0) {
                            var list = data.data;
                            if (list.length > 0) {
                                for (var i = 0; i < list.length; i++) {
                                    vm.list.push(list[i]);
                                }
                                mui('#pullrefresh').pullRefresh().endPullupToRefresh(false); //参数为true代表没有更多数据了。
                                page++;
                            }
                            else {
                                mui('#pullrefresh').pullRefresh().endPullupToRefresh(true); //参数为true代表没有更多数据了。
                            }
                        }
                        else {
                            mui('#pullrefresh').pullRefresh().endPullupToRefresh(true); //参数为true代表没有更多数据了。
                        }
                    }
                });

            }, 0);
        }

    </script>
@stop

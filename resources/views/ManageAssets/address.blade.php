@extends('Layout.app')
@section('content')
    <style type="text/css">
        .tip{font-size: 0.7rem;padding: 0.5rem;margin: 0;}
        .auth-box{}
        .auth-box:before{background:none !important;}
        .auth-box:after{background:none !important;}
        .auth-box .mui-input-row:last-child:after{background:none !important;}
        .auth-box .mui-input-row:after{background:#ececec !important;}
        .auth-box .mui-input-row label{font-size: 0.9rem;color: #6b6b6b;}
        .auth-box .mui-input-row input{font-size: 0.9rem;text-align: right;}
        .save-btn{padding: 0.6rem 0 !important;font-size: 1.0rem !important;}
        .tip{background: #ffffff;padding: 0.6rem;}
        .tip p{font-size: 0.8rem;}
        .tip{font-size: 0.7rem;padding: 0.5rem;margin: 0;}
        .address-list,.add-address-list{padding: 0.8rem;}
        .add-btn{border:1px dashed #a5a5a5;text-align: center;padding: 1.2rem 0;border-radius: 3px;color: #9c9898;font-size: 0.9rem;display: block;margin-bottom: 0.5rem;}
        .address-list{}
        .address-list .item{background: #ffffff;padding: 0.6rem;margin: 0;border-radius: 0.3rem;}
        .address-list .item p{margin: 0;}
        .address-list .item .type{color: #333333;font-size: 1rem;;line-height: 2rem;}
    </style>
    <header id="header" class="mui-bar mui-bar-nav app-header-1">
        <a class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
        <h1 class="mui-title">{{$title}}</h1>
    </header>

    <div class="mui-content" id="content">

        <div v-if="can_bind">
            <p class="tip">
                请正确填写你的地址。
            </p>

            <form class="mui-input-group auth-box">
                <div class="mui-input-row">
                    <label>地址</label>
                    <input type="text" placeholder="请输入您的地址" id="address" >
                </div>

                <div class="mui-input-row">
                    <label>备注</label>
                    <input type="text" placeholder="请输入备注" id="remark" >
                </div>
            </form>

            <div class="mui-content-padded">
                <button type="button" class="mui-btn mui-btn-primary mui-btn-block save-btn" v-on:tap="bind()">保存</button>
            </div>
        </div>

        <div class="address-list" v-if="!can_bind">
            <div class="item">
                <p class="type">
                    备注：@{{ remark }}
                </p>
                <button type="button"  class="mui-btn mui-btn-primary" style="float: right;margin-top: -20px;" v-on:tap="unbind()">删除</button>
                <p class="address">
                    @{{ address }}
                </p>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        vm = new Vue({
            el: '#content',
            data: {
                address: '加载中...',
                remark: '',
                can_bind: true
            },
            methods: {
                bind: function(){
                    var addre = document.getElementById('address').value;
                    var desc  = document.getElementById('remark').value;

                    mui.ajax("/manage-assets/addre-bind", {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            address: addre,
                            remark: desc
                        },
                        dataType: 'json',//服务器返回json格式数据
                        type: 'post',//HTTP请求类型
                        success: function (data) {
                            if (data.code === 0) {
                                vm.address = addre;
                                vm.remark = desc;
                                vm.can_bind = false;

                                alert('绑定成功');
                            }else{
                                alert('绑定失败：' + (data.msg || ''));
                            }
                        }
                    });
                },
                unbind: function(){
                    mui.ajax("/manage-assets/addre-del", {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                        },
                        dataType: 'json',//服务器返回json格式数据
                        type: 'post',//HTTP请求类型
                        success: function (data) {
                            if (data.code === 0) {
                                vm.address = '未绑定';
                                vm.remark = '';
                                vm.can_bind = true;
                                alert('解绑成功');
                            }else{
                                alert('解绑失败：' + (data.msg || ''));
                            }
                        }
                    });
                },
            }
        });

        mui.ajax("/manage-assets/addre-my", {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: {
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            success: function (data) {
                if (data.code === 0) {
                    if(data.data){
                        vm.address = data.data.address;
                        vm.remark = data.data.remark;
                        vm.can_bind = false;
                    }else{
                        vm.address = '未绑定';
                        vm.can_bind = true;
                    }
                }
            }
        });

    </script>
@stop

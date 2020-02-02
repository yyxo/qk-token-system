<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>开箱子趣味游戏-dddotc</title>
    <meta name="viewport" content="width=device-width,user-scalable=no,initial-scale=1,viewport-fit=cover,maximum-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="https://cdn.staticfile.org/mui/3.7.1/css/mui.min.css">
    <link rel="stylesheet" href="https://airdrop.qkfile.org/css/app.css?v=11">
    <style>
        .user-menu .item{
            width: 50%;
        }
    </style>
</head>
<body>
<header class="mui-bar mui-bar-nav app-header" id="header" style=" text-align: center; margin-top: 10px; ">
LUCK BOX
</header>
<div class="mui-content" id="content" v-lock>


    <div class="balance-box">
        <p class="title">
            余额
        </p>
        <p class="amount">
            <a href="/user/assets-logs?t=cct">@{{user.cct}} <span>CCT</span></a>
        </p>
        <p class="amount">
            <a href="/user/assets-logs?t=qki">@{{user.qki}} <span>QKI</span></a>
        </p>
    </div>

    <div class="user-menu">
        <p>1.箱子有两种，蓝色和红色，取区块hash最后一位字符，01234567为红色，89abcdef为蓝色。</p>
        <p>2.每一个箱子有一个区块高度(当前区块高度以后的)，该区块的颜色和你选择的颜色相同，你可以获得token奖励，不相同token消失。</p>

        <div class="mui-input-row">
            <label for="goodsType">类型</label>
            <select class="mui-btn mui-btn-block" id="goodsType">
                <option value="cct">
                    cct
                </option>
            </select>
        </div>

        <div class="mui-input-row mui-input-range">
            <input type="range" v-model="amount" min="1" max="20">
            <span>@{{ amount }}个</span>
        </div>

        <div class="item" v-on:tap="red()">
            <button type="button" class="mui-btn mui-btn-danger">红色</button>
        </div>

        <div class="item" v-on:tap="blue()" style=" float: right; ">
            <button type="button" class="mui-btn mui-btn-primary">蓝色</button>
        </div>

    </div>

    <div class="user-data-list">
        <div class="item" v-for="box in boxes">
            <span class="title mui-pull-left">
                @{{ box.color_name }}箱子 高度@{{box.height  }}
            </span>
            <span class="amount mui-pull-right" v-if="box.status==0">
                 打开中
            </span>
            <span class="amount mui-pull-right" v-if="box.status==1">
                 @{{ box.qkf_amount }} @{{ box.token_name }}
            </span>
            <span class="amount mui-pull-right" v-if="box.status==2">
                 空
            </span>
        </div>
    </div>

</div>

</body>
<script src="https://cdn.staticfile.org/mui/3.7.1/js/mui.min.js"></script>
<script src="/js/vue.min.js"></script>
<script type="text/javascript">

    vm = new Vue({
        el: '#content',
        data: {
           user:[],
            boxes:[],
            amount:1
        },
        methods: {
            red: function () {
                mui.ajax("/box/open", {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',//服务器返回json格式数据
                    type: 'post',//HTTP请求类型
                    data: {
                        amount:vm.amount,
                        color:1,
                        token:mui('#goodsType')[0].value
                    },
                    success: function (data) {

                        mui.toast(data.msg);
                        getUser();
                        getBoxes();
                    }
                });
            },
            blue: function () {
                mui.ajax("/box/open", {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',//服务器返回json格式数据
                    type: 'post',//HTTP请求类型
                    data: {
                        amount:vm.amount,
                        color:2,
                        token:mui('#goodsType')[0].value
                    },
                    success: function (data) {

                        mui.toast(data.msg);
                        getUser();
                        getBoxes();
                    }
                });
            },
        }
    });
    getUser();
    getBoxes();
    /**
     * 获取会员信息
     * @param {Object} address
     */
    function getUser()
    {
        mui.ajax('/box/userinfo',{
            dataType:'json',//服务器返回json格式数据
            type:'get',//HTTP请求类型
            timeout:10000,//超时时间设置为10秒；
            success:function(data){
                if(data.code == 0)
                {
                    vm.user = data.data;
                }
            },
            error:function(xhr,type,errorThrown){

                mui.toast("网络错误");
            }
        });
    }

    /**
     * 获取会员信息
     * @param {Object} address
     */
    function getBoxes()
    {
        mui.ajax('/box/boxes',{
            dataType:'json',//服务器返回json格式数据
            type:'get',//HTTP请求类型
            timeout:10000,//超时时间设置为10秒；
            success:function(data){
                if(data.code == 0)
                {
                    vm.boxes = data.data.boxes;
                }
            },
            error:function(xhr,type,errorThrown){

                mui.toast("网络错误");
            }
        });
    }


    /*
		时间倒计时插件
		TimeDown.js
		*/
    function TimeDown(id, endDateStr) {
        //相差的总秒数
        var totalSeconds = parseInt(endDateStr);
        //取模（余数）
        var modulo = totalSeconds % (60 * 60 * 24);
        //小时数
        var hours = Math.floor(modulo / (60 * 60));
        modulo = modulo % (60 * 60);
        //分钟
        var minutes = Math.floor(modulo / 60);
        //秒
        var seconds = modulo % 60;

        if(totalSeconds<=0)
        {
            document.getElementById(id).innerHTML = "解锁";
            document.getElementById(id).disabled=false;
            return false;
        }
        else
        {
            //输出到页面
            document.getElementById(id).innerHTML = hours + ":" + minutes + ":" + seconds+"后可解锁";
        }
        //延迟一秒执行自己
        if(totalSeconds > 0)
        {
            totalSeconds--;
            setTimeout(function () {
                TimeDown(id, totalSeconds);
            }, 1000)
        }

    }
</script>
</html>

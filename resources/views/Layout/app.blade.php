<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <title>{{$title}}</title>
    <meta name="description" content=""/>
    <link href="/css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/css/app.css"/>
    <link rel="stylesheet" type="text/css" href="/icon/iconfont.css"/>
    @yield('css')
    <style>
        html, body {
            height: 100%;
        }
    </style>
</head>

<body>

<script src="/js/mui.min.js"></script>
<script src="/js/vue.min.js"></script>
<script src="/js/math.min.js"></script>
<script src="/js/sha256.min.js"></script>
@yield('content')

</body>

<script type="text/javascript">
    mui('body').on('tap', 'a', function () {
        document.location.href = this.href;
    });
</script>
</html>

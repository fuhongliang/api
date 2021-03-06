<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>layui</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="https://res.layui.com/layui/dist/css/modules/layer/default/layer.css?v=3.1.1"  media="all">
    <!-- 注意：如果你直接复制所有代码到本地，上述css路径需要改成你本地的 -->
</head>
<body>
<fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
    <legend>经营数据</legend>
</fieldset>

<div style="padding: 20px; background-color: #F2F2F2;">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs6">
            <div class="layui-card">
                <div class="layui-card-header">访问顾客</div>
                <div class="layui-card-body">
                    {{$data['today_click']}}
                </div>
            </div>
        </div>
        <div class="layui-col-xs6">
            <div class="layui-card">
                <div class="layui-card-header">比较前日</div>
                <div class="layui-card-body">
                    {{$data['today_click_comp']}}
                </div>
            </div>
        </div>
        <div class="layui-col-xs6">
            <div class="layui-card">
                <div class="layui-card-header">下单顾客</div>
                <div class="layui-card-body">
                    {{$data['today_ordernum']}}
                </div>
            </div>
        </div>
        <div class="layui-col-xs6">
            <div class="layui-card">
                <div class="layui-card-header">比较前日</div>
                <div class="layui-card-body">
                    {{$data['today_ordernum_comp']}}
                </div>
            </div>
        </div>
        <div class="layui-col-xs6">
            <div class="layui-card">
                <div class="layui-card-header">下单转换率</div>
                <div class="layui-card-body">
                    {{$data['today_change']}}
                </div>
            </div>
        </div>
        <div class="layui-col-xs6">
            <div class="layui-card">
                <div class="layui-card-header">比较前日</div>
                <div class="layui-card-body">
                    {{$data['today_change_comp']}}
                </div>
            </div>
        </div>
    </div>
</div>
<div style="padding: 20px; background-color: #F2F2F2;">
    <div class="layui-row">
        <div class="layui-col-xs12">
            <div id="ordernum" style="width: 100%;height:300px;"></div>
        </div>
    </div>
</div>
<div style="padding: 20px; background-color: #F2F2F2;">
    <div class="layui-row">
        <div class="layui-col-xs12">
            <div id="orderamont" style="width: 100%;height:300px;"></div>
        </div>
    </div>
</div>
<input type="hidden" name="store_id" value="{{$data['store_id']}}" id="store_id">

<script src="https://cdn.bootcss.com/echarts/4.2.1-rc1/echarts.js"></script>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="//layui.hcwl520.com.cn/layui/layui.js?v=201811010202" charset="utf-8"></script>
<script>
    $(document).ready(function () {
        getData();
        getData_();
    });
</script>
<script type="text/javascript">
    //第一个图表
    var myChart1 = echarts.init(document.getElementById('ordernum'));
    var store_id = $(" #store_id ").val();
    var app = {
        xday:[],
        yvalue:[]
    };
    function getData() {
        $.ajax({
            url:'http://47.92.244.60:88/v1/get_echarts',
            data:{'store_id':store_id},
            type:'post',
            dataType:'json',
            success:function(result) {
                console.log(result)
                app.xday=result.xday;
                app.yvalue = result.ydata;
                myChart1.setOption({
                    title: {
                        text: '7日成交量'
                    },
                    tooltip: {},
                    legend: {
                    },
                    xAxis: {
                        data: app.xday
                    },
                    yAxis: {},
                    series: [{
                        type: 'line',
                        data: app.yvalue
                    }]
                })
            },
            error:function (msg) {
                console.log(msg);
                alert('系统发生错误');
            }
        })
    };
</script>
<script type="text/javascript">
    //第一个图表
    var myChart2 = echarts.init(document.getElementById('orderamont'));
    var store_id = $(" #store_id ").val();
    var app = {
        xday_:[],
        yvalue_:[]
    };
    // 发送ajax请求，从后台获取json数据

    function getData_() {
        $.ajax({
            url:'http://47.92.244.60:88/v1/get_echarts_',
            data:{'store_id':store_id},
            type:'post',
            dataType:'json',
            success:function(result) {
                console.log(result)
                app.xday_=result.xday;
                app.yvalue_ = result.ydata;
                myChart2.setOption({
                    title: {
                        text: '7日成交金额'
                    },
                    tooltip: {},
                    legend: {
                    },
                    xAxis: {
                        data: app.xday
                    },
                    yAxis: {},
                    series: [{
                        type: 'line',
                        data: app.yvalue
                    }]
                })
            },
            error:function (msg) {
                console.log(msg);
                alert('系统发生错误');
            }
        })
    };
</script>
</body>
</html>
<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> 2.4.0
    </div>
    <strong>Copyright &copy; 2014-2016 <a href="https://adminlte.io">Almsaeed Studio</a>.</strong> All rights
    reserved.
</footer>
</div>
<!-- ./wrapper -->

<!-- jQuery 3 -->
<script src="{{asset('AdminLTE/bower_components/jquery/dist/jquery.min.js')}}"></script>
<!-- Bootstrap 3.3.7 -->
<script src="{{asset('AdminLTE/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('AdminLTE/dist/js/adminlte.min.js')}}"></script>
<!-- JavaScripts -->
{{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.pjax/1.9.6/jquery.pjax.min.js"></script>
<script>
    $(document).pjax('a', '#pjax-container');
    $(document).on("pjax:timeout", function(event) {
        // 阻止超时导致链接跳转事件发生
        event.preventDefault()
    });
</script>
<script>
    $(document).ready(function(){
        var wsServer = 'ws://47.111.27.189:9501';
        var websocket = new WebSocket(wsServer);

        websocket.onopen = function (evt) {
            console.log("连接成功");
        };
        websocket.onclose = function (evt) {
            console.log("关闭成功");
        };
        websocket.onmessage = function (evt) {
            console.log('收到服务器信息: ' + evt.data);
        };
        websocket.onerror = function (evt, e) {
            console.log('错误: ' + evt.data);
        };
        $("#send").click(function(){
            var message=$('input[name="message"]').val();

            websocket.send( makeJson(message));
        });
    });
    function makeJson(data)
    {
        var mini= {
            'username':'',
            'id':1000,
            'type':'',//1对个人
            'from':'admin',
            'avatar':'',
            'data':data
        };
       return  JSON.stringify(mini);
    }
</script>
</body>
</html>
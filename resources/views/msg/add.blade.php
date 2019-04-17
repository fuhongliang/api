@extends('layout.template')
@section("content")


    <div class="form-group has-success">
        <label class="control-label" for="inputSuccess">内容</label>
        <input type="text" class="form-control" id="inputSuccess" placeholder="Enter ...">
        <span class="help-block">Help block with success</span>
    </div>
    <div class="box-footer">
            <span class="input-group-btn">
                        <button type="submit" class="btn btn-success btn-flat" id="send">Send</button>
            </span>
    </div>
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            var token=$('input[name="token"]').val();
            var wsServer = 'ws://47.111.27.189:9501?token=6666';
            var websocket = new WebSocket(wsServer);
            websocket.onopen = function (evt) {
                console.log("连接成功");
            };
            websocket.onclose = function (evt) {
                console.log("关闭成功");
            };
            websocket.onmessage = function (evt) {
                var jsonData = eval("("+evt.data+")");
                console.log(evt);
            };
            websocket.onerror = function (evt, e) {
                console.log('错误: ' + evt.data);
            };
            $("#send").click(function(){
                var json = {
                    'target':2,
                    'msg':$('input[name="message"]').val(),
                    'type':1,//1对个人
                    'from':'admin',
                };
                websocket.send(JSON.stringify(json));
            });
        });
    </script>



@endsection


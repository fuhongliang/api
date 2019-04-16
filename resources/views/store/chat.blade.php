
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            var wsServer = 'ws://47.111.27.189:2346';
            var websocket = new WebSocket(wsServer);
            console.log(websocket);
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

                websocket.send(message);

            });



        });

    </script>

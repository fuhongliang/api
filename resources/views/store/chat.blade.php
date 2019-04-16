@extends('layout.template')
@section("content")






    <div class="col-md-3">
        <!-- DIRECT CHAT SUCCESS -->
        <div class="box box-success direct-chat direct-chat-success">
            <div class="box-header with-border">
                <h3 class="box-title">聊天</h3>

                <div class="box-tools pull-right">
                    <span data-toggle="tooltip" title="3 New Messages" class="badge bg-green" id="tooltip"></span>
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="Contacts" data-widget="chat-pane-toggle">
                        <i class="fa fa-comments"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- Conversations are loaded here -->
                <div class="direct-chat-messages">
                    <!-- Message. Default to the left -->
                    <div class="direct-chat-msg">
                        <div class="direct-chat-info clearfix">
                            <span class="direct-chat-name pull-left">Alexander Pierce</span>
                            <span class="direct-chat-timestamp pull-right">23 Jan 2:00 pm</span>
                        </div>
                        <!-- /.direct-chat-info -->
                        <img class="direct-chat-img" src="{{asset('AdminLTE/dist/img/user1-128x128.jpg')}}" alt="Message User Image"><!-- /.direct-chat-img -->
                        <div class="direct-chat-text">
                            Is this template really for free? That's unbelievable!
                        </div>
                        <!-- /.direct-chat-text -->
                    </div>
                    <!-- /.direct-chat-msg -->

                    <!-- Message to the right -->
                    <div class="direct-chat-msg right">
                        <div class="direct-chat-info clearfix">
                            <span class="direct-chat-name pull-right">Sarah Bullock</span>
                            <span class="direct-chat-timestamp pull-left">23 Jan 2:05 pm</span>
                        </div>
                        <!-- /.direct-chat-info -->
                        <img class="direct-chat-img" src="{{asset('AdminLTE/dist/img/user3-128x128.jpg')}}" alt="Message User Image"><!-- /.direct-chat-img -->
                        <div class="direct-chat-text">
                            You better believe it!
                        </div>
                        <!-- /.direct-chat-text -->
                    </div>
                    <!-- /.direct-chat-msg -->
                </div>
                <!--/.direct-chat-messages-->

                <!-- Contacts are loaded here -->
                <div class="direct-chat-contacts">
                    <ul class="contacts-list">
                        <li>
                            <a href="#">
                                <img class="contacts-list-img" src="{{asset('AdminLTE/dist/img/user1-128x128.jpg')}}" alt="User Image">

                                <div class="contacts-list-info">
                            <span class="contacts-list-name">
                              Count Dracula
                              <small class="contacts-list-date pull-right">2/28/2015</small>
                            </span>
                                    <span class="contacts-list-msg">How have you been? I was...</span>
                                </div>
                                <!-- /.contacts-list-info -->
                            </a>
                        </li>
                        <!-- End Contact Item -->
                    </ul>
                    <!-- /.contatcts-list -->
                </div>
                <!-- /.direct-chat-pane -->
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                    <div class="input-group">
                        <input type="text" name="token" value="962e376d8f251d5eae046ae54fb5e985" class="form-control">
                        <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                        <span class="input-group-btn">
                        <button type="submit" class="btn btn-success btn-flat" id="send">Send</button>
                      </span>
                    </div>
            </div>
            <!-- /.box-footer-->
        </div>
        <!--/.direct-chat -->
    </div>
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            var token=$('input[name="token"]').val();
            var wsServer = 'ws://47.111.27.189:9501?token='+token;
            var websocket = new WebSocket(wsServer);
            websocket.onopen = function (evt) {
                console.log("连接成功");
            };
            websocket.onclose = function (evt) {
                console.log("关闭成功");
            };
            websocket.onmessage = function (evt) {
                console.log(evt.data);
                var jsonData = eval("("+evt.data+")");
                $('#tooltip').html(jsonData.username+jsonData.msg);

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





@endsection


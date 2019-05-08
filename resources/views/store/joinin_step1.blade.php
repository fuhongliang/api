<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1 user-scalable=no">
    <title>@yield('title','鸭梨')</title>
    <link rel="stylesheet" href="{{asset('layui/dist/css/layui.css')}}">
    <link rel="stylesheet" href="https://www.layui.com/admin/std/dist/layuiadmin/style/admin.css">
    <link rel="stylesheet" href="https://www.layui.com/admin/std/dist/layuiadmin/style/template.css">
    <link rel="stylesheet" href="https://www.layui.com/admin/std/dist/layuiadmin/style/login.css">
    <script src="{{asset('layui/dist/layui.js')}}"></script>
    <style type="text/css">
        .yali-nav-bottom {
            position: fixed;
            width: 100%;
            height: 55px;
            left: 0;
            bottom: 0;
            background-color: #fff;
        }

        .yali-nav-title li img {
            display: inline-block;
            width: 26px;
            height: 26px;
            line-height: 26px;
            text-align: center;
            border-radius: 2px;
            background-color: #fff;
            margin-top: 5px;
            transition: all .3s;
            -webkit-transition: all .3s;
        }

        .yali-nav-title li cite {
            position: relative;
            top: 2px;
            display: block;
            color: #666;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
            font-size: 12px;
        }

        .yali-col-xs2 {
            float: left;
            margin: 0;
            width: 20%;
            border-top: 1px solid #f8f8f8;
        }

        .layui-tab-bar {
            display: none;
        }

        .layadmin-carousel {
            height: 135px !important;
            background-color: #fff;
        }

        .layui-fluid {
            padding: 0px;
        }
    </style>
</head>
<body>

    <link rel="stylesheet" href="https://cdn.bootcss.com/weui/1.1.3/style/weui.min.css">
    <link rel="stylesheet" href="https://cdn.bootcss.com/jquery-weui/1.2.1/css/jquery-weui.min.css">
    <!-- body 最后 -->
    <script src="https://cdn.bootcss.com/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://cdn.bootcss.com/jquery-weui/1.2.1/js/jquery-weui.min.js"></script>

    <div class="layui-card">
        <div class="layui-card-body">
            <form class="layui-form layui-form-pane" action="{{URL('active_save')}}" method="POST" enctype=multipart/form-data>
                {{csrf_field()}}
                <div class="layui-form-item">
                    <label class="layui-form-label">活动标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="active_title" lay-verify="required|active_title" autocomplete="off"
                               placeholder="请输入活动标题......." class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">活动详情</label>
                    <div class="layui-input-block">
                        <textarea placeholder="请输入活动详情......" class="layui-textarea" name="describe"
                                  lay-verify="required|describe"></textarea>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">宣传图片</label>
                    <div class="layui-input-block">
                        <div class="weui-uploader__bd">
                            <ul class="weui-uploader__files" id="uploaderFiles">
                            </ul>
                            <div class="weui-uploader__input-box" style="margin-top:10px;margin-left:10px">
                                <input type="file" name="active_img[]" multiple="true" id="uploaderInput" class="weui-uploader__input">
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-left:5%;width:90%;margin-top:30px;height:40px">
                    <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="active_pub" type="submit">立即发布</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        layui.use('form', function(){
            var form = layui.form;

            //各种基于事件的操作，下面会有进一步介绍
        });
    </script>
    <script>
            var tmpl = '<li class="weui-uploader__file" style="background-image:url(#url#);margin-top:10px;margin-left:10px"></li>';
            var $uploaderInput = $("#uploaderInput"); //上传按钮+
            var $uploaderFiles = $("#uploaderFiles");    //图片列表
            var imgCount = 0;
            var maxImgCount = 3;
            $uploaderInput.on("change", function (e) {
                var src, url = window.URL || window.webkitURL || window.mozURL, files = e.target.files;
                if (files.length > maxImgCount || imgCount + files.length > maxImgCount) {
                    layer.msg("超过最大限制");
                } else {
                    for (var i = 0, len = files.length; i < len; ++i) {
                        var file = files[i];

                        if (url) {
                            src = url.createObjectURL(file);
                        } else {
                            src = e.target.result;
                        }
                        imgCount++;
                        $uploaderFiles.append($(tmpl.replace('#url#', src)));
                        if (imgCount == maxImgCount) {
                            $('.weui-uploader__input-box').hide();
                        }
                    }
                }

            });
    </script>


    <script>
        window.onload = function () {
            document.addEventListener('touchstart', function (event) {
                if (event.touches.length > 1) {
                    event.preventDefault();
                }
            });
            var lastTouchEnd = 0;
            document.addEventListener('touchend', function (event) {
                var now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            document.addEventListener('gesturestart', function (event) {
                event.preventDefault();
            });
        }
    </script>

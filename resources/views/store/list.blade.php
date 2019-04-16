@extends('layout.template')
@section("content")


    <section class="content">
        <div class="row">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">店铺列表</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 10px">ID</th>
                                <th>店铺名称</th>
                                <th>会员账号</th>
                                <th>买家账号</th>
                                <th>操作</th>
                            </tr>
                            @foreach ($list as $data)
                            <tr>
                                <td>{{ $data->store_id }}</td>
                                <td>{{ $data->store_name }}</td>
                                <td>{{ $data->member_name }}</td>
                                <td>{{ $data->seller_name }}</td>
                                <td>
                                    <a href="{{URL('admin/store_chat')}}/{{ $data->store_id }}"><i class="fa fa-comments"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer clearfix">
                        {{ $list->links() }}
                    </div>
                </div>
        </div>
    </section>

















@endsection


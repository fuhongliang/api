@include('layout.header')
@include('layout.aside')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" id="pjax-container">
        @yield('main')
    </div>
@include('layout.footer')



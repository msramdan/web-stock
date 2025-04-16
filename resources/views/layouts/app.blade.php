@include('layouts.header')

<div id="main-content">
    @yield('content')
</div>
<script src="{{ asset('mazer/extensions/apexcharts/apexcharts.min.js') }}"></script>
@include('layouts.footer')

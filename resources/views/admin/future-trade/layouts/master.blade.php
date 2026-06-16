<!DOCTYPE HTML>
<html class="no-js" lang="en">
<head>
    @include('admin.include.header_asset')
</head>
@php
    $menu = $menu ?? '';
    $sub_menu = $sub_menu ?? '';
@endphp
<body class="body-bg">
<!-- google_analytics start -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{allsetting('google_analytics_tracking_id')}}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '{{allsetting('google_analytics_tracking_id')}}');
</script>
<!-- google_analytics end -->
<!-- Start sidebar -->
@yield('sidebar',view('admin.future-trade.layouts.sidebar',compact(['menu','sub_menu'])))
<!-- End sidebar -->
<!-- top bar -->
@include('admin.include.header')
<!-- /top bar -->

<!-- main wrapper -->
<div class="main-wrapper">
    <div class="container-fluid">
        @yield('content')
    </div>
</div>
<!-- /main wrapper -->

<!-- js file start -->

<!-- JavaScript -->
@include('admin.include.footer_asset')

<script>

    (function($) {
        "use strict";
        @if(session()->has('success'))
            window.onload = function () {
            VanillaToasts.create({
                text: '{{session('success')}}',
                backgroundColor: "linear-gradient(135deg, #73a5ff, #5477f5)",
                type: 'success',
                timeout: 40000
            });
        };
        @elseif(session()->has('dismiss'))
            window.onload = function () {
            VanillaToasts.create({
                text: '{{session('dismiss')}}',
                type: 'warning',
                timeout: 40000
            });
        };
        @elseif($errors->any())
            @foreach($errors->getMessages() as $error)
            window.onload = function () {
            VanillaToasts.create({
                text: '{{ $error[0] }}',
                type: 'warning',
                timeout: 40000
                });
             };
             @break
             @endforeach
        @endif

        /* Add here all your JS customizations */
        $('.number-only').keypress(function (e) {
            alert(11);
            var regex = /^[+0-9+.\b]+$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        });
        $('.no-regx').keypress(function (e) {
            var regex = /^[a-zA-Z+0-9+\b]+$/;
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            }
            e.preventDefault();
            return false;
        });

    })(jQuery)

</script>

{{-- for web sockets--}}
<script src="https://js.pusher.com/3.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.8.1/echo.iife.min.js"></script>
<script>
    let my_env_socket_port = "{{ env('BROADCAST_PORT')}}";
        Pusher.logToConsole = true;
        window.Echo = new Echo({
            broadcaster: 'pusher',
            wsHost: window.location.hostname,
            wsPort: 6006,
            wssPort: 443,
            key: '{{ env('PUSHER_APP_KEY') }}',
            cluster: 'mt1',
            encrypted: false,
            disableStats: true
        });

</script>

{{-- notification --}}
<script>
    jQuery(document).ready(function () {

    Pusher.logToConsole = true;
    let user_id = '{{Auth::id()}}';

    Echo.channel('New-Ticket-Notification-Send-To-Agent')
        .listen('.Notification', (data) => {
            
            if(data.success == true)
            {
                var notificationDetails = data.data;
                $('#notification_count').empty().text(notificationDetails.total_notification);
                $('#notification_list').empty().append(notificationDetails.html_view);
            }
        })
});
</script>

@yield('script')
<!-- End js file -->
</body>
</html>


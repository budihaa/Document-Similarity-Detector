<!DOCTYPE html>
<html lang="en">

<head>
    @include('_includes.head')
    @stack('styles')
</head>

<body class="animsition">
    <div class="page-wrapper">
        <!-- HEADER MOBILE-->
        @include('_includes/header_mobile')
        <!-- END HEADER MOBILE-->

        <!-- MENU SIDEBAR-->
		@include('_includes/sidebar')
        <!-- END MENU SIDEBAR-->

        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <!-- HEADER DESKTOP-->
            @include('_includes/header_desktop')
            <!-- HEADER DESKTOP-->

            <!-- MAIN CONTENT-->
			@yield('content')
			<!-- END MAIN CONTENT-->
        </div>
		<!-- END PAGE CONTAINER-->

    </div>

    @include('_includes/scripts')
    @stack('scripts')
</body>

</html>

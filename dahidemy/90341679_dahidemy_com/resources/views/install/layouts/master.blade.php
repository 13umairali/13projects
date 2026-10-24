<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title')</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Theme style -->
    <link href="{{ asset('assets/plugins/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- jQuery 2.2.3 -->
    <script src="{{ asset('assets/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
    <!-- Bootstrap 3.3.6 -->
    <script src="{{ asset('assets/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jQueryUI/jquery-ui.min.js') }}" type="text/javascript"></script>
    <!-- CSRF Token for AJAX -->
</head>

<body class="master">
    <div class="box">
        <div class="header">
            <h1 class="header__title">@yield('title')</h1>
        </div>
        <div class="main">
            <div class="row">
                <div class="col-md-12">
                    @if (Session::has('flash_notification.message'))
                        <div class="alert alert-{{ Session::get('flash_notification.level') }}">
                            {{ Session::get('flash_notification.message') }}
                        </div>
                    @endif
                    @yield('container')
                </div>
            </div>
        </div>
    </div>
    @if (Session::has('flash_notification.message'))
        <script>
            toastr.{{ Session::get('flash_notification.level') }}('{{ Session::get('flash_notification.message') }}',
                'Response Status')
        </script>
    @endif

    @yield('scripts')
</body>

</html>

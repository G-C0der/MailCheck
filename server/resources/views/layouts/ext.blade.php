<!DOCTYPE HTML>
<html>
<head>
    <!-- Meta headers -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="UTF-8">

    <!-- Viewport scaling -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=10, user-scalable=yes">

    <!-- Title -->
    <title>@yield('title')</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Scripts -->
    <script>window.Laravel = {"csrfToken":"{{ csrf_token() }}"} ;</script>

    <!-- Backend assets -->
    <link href="{{ mix("css/ext.css") }}" rel="stylesheet">

    <!-- Client -->
    <script type="text/javascript" src="{{ mix("js/ext.js") }}"></script>
@yield('ext')

<!-- Extra -->
    @yield('extra')
</head>
<body class="application">
@yield('content')
</body>
</html>

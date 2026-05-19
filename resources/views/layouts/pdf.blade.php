<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('page-title', 'PDF')</title>
    <style>
        @page {
            margin: 50px;
        }
        body {
            margin: 0;
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>

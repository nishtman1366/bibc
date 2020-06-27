<!DOCTYPE html>
<html lang="en">

<!-- BEGIN HEAD-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>ادمین | داشبورد</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    @include('components.header')
    <link rel="stylesheet" href="{{assets('css/admin/adminLTE/AdminLTE.min.css')}}"/>
    <!-- Vendor CSS -->
    <link rel="stylesheet" href="{{assets('vendor/bootstrap/4.3.1/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/Themify/themify-icons.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/animate/animate.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/jScrollPane/jquery.jscrollpane.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/waves/waves.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/switchery/switchery.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/DataTables/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/DataTables/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/DataTables/css/buttons.dataTables.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/DataTables/css/buttons.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/bootstrap/css/bootstrap-datepicker.min.css')}}" type="text/css">
    <link rel="stylesheet" href="{{assets('vendor/bootstrap/css/bootstrap-glyphicons.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/bootstrap/css/bootstrap-editable.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/dropify/dropify.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/tranxit/core.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/tranxit/style_pagination.css')}}">

    <script>
        window.Laravel = {"csrfToken": "aE99KLvAc1YqR5xG7rr9k4HaM64jqeBViyj8fkCk"}    </script>
    <style type="text/css">
        .rating-outer span,
        .rating-symbol-background {
            color: #ffe000 !important;
        }

        .rating-outer span,
        .rating-symbol-foreground {
            color: #ffe000 !important;
        }

        .row::after {
            content: none;
            display: none;
            clear: none;
        }

        body {
            background-color: #fff;

        }
    </style>
    <link rel="stylesheet" href="{{assets('vendor/jvectormap/jquery-jvectormap-2.0.3.css')}}">
    <style>
        #sidebar {
            width: 200px;
            overflow: auto;
        }

        #sidebar .sidebar-header .user-pic {
            float: right;
            width: 60px;
            padding: 2px;
            border-radius: 12px;
            margin-left: 15px;
            overflow: hidden;
        }

        #sidebar .sidebar-header .user-pic img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }

        #sidebar .sidebar-header .user-info {
            color: #0ab9d3;
            text-align: right;
        }

        #sidebar .sidebar-content {
            clear: both;
            border-top: 1px solid #3a3f48;
        }

        #sidebar .sidebar-content ul {
            padding-right: 0;
        }

        #sidebar .sidebar-content ul li {
            display: block;
            height: 45px;
            line-height: 45px;
            border-bottom: 1px solid #3a3f48;
            text-align: right;
            padding: 5px 15px 5px 5px;
        }

        #sidebar .sidebar-content ul li:hover {
            background-color: #3e454c;
        }

        #sidebar .sidebar-content ul li a {
            display: block;
            color: white;
        }

        #sidebar .sidebar-content ul li a:hover {
            color: #00aced;
        }

        #sidebar .sidebar-content ul li a i {
            margin: 5px;
            padding: 3px;
            border-radius: 3px;
            background-color: #3e454c;
        }

        #sidebar .sidebar-content ul li a:hover i {
            background-color: #3e454c;
        }

        #content {
            padding: 75px 16px 1px;
            text-align: justify;
        }
    </style>
</head>
<body class="m-0">
<div class="container-fluid m-0 p-0" style="direction: rtl">
    @yield('content')
</div>
@include('layouts.dashboard.footer')
@stack('js')
</body>
<!-- END BODY-->
</html>
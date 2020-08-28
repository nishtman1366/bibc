@extends('app',['title'=>'| داشبورد مدیریت'])

@section('main_container')
    @include('layouts.dashboard.sidebar')
    <div style="margin-right: 200px;">
        @include('layouts.dashboard.header')
        <div class="container-fluid" id="content">
            {{--            @php--}}
            {{--                $messages = getMessages();--}}
            {{--                if (count($messages) > 0) {--}}
            {{--                    foreach ($messages as $message) {--}}
            {{--                        ?>--}}
            {{--                        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissable text-right">--}}
            {{--                            <?php echo $message['message']; ?>--}}
            {{--                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>--}}
            {{--                        </div>--}}
            {{--                        <?php--}}
            {{--                    }--}}
            {{--                }--}}
            {{--            @endphp--}}
            @yield('dashboard_content')
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" type="text/css" href="{{assets('css/dashboard.css')}}">

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

        .tooltip-inner {
            font-family: IRANSans !important;
        }
    </style>
@endpush
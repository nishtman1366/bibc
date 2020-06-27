@extends('app')

@section('content')
    @include('layouts.dashboard.sidebar')
    <div style="margin-right: 200px;">
        @include('layouts.dashboard.header')
        <div class="container-fluid" id="content">
            @php
                $messages = getMessages();
                if (count($messages) > 0) {
                    foreach ($messages as $message) {
                        ?>
                        <div class="alert alert-<?php echo $message['type']; ?> alert-dismissable text-right">
                            <?php echo $message['message']; ?>
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        </div>
                        <?php
                    }
                }
            @endphp
            hello
            @yield('dashboard_content')
        </div>
    </div>
@endsection
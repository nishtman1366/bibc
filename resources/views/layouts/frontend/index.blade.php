@extends('app',['title'=>'| پنل کاربری'])

@section('main_container')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 bg-dark text-light">
                <h3 class="text-right">سامانه مدیریت تاکسی اینترنتی</h3>
                <div class="pull-left m-1 p-1">
                    @if(!key_exists('user',$_SESSION) || count($_SESSION['user'])===0)
                        <a href="{{url('login')}}" class="btn btn-primary">ورود</a>
                    @else
                        <a href="{{url('logout')}}" class="btn btn-danger">خروج</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        @yield('content')
    </div>
@endsection
@push('css')
    <link rel="stylesheet" type="text/css" href="{{assets('css/style.css')}}">
@endpush
@extends('layouts.frontend.index')

@section('content')
    <div class="row">
        <div class="col-12 col-md-5 m-auto">
            <form action="{{url('login')}}" method="post">
                <div class="form-group">
                    <label for="username" class="pull-right">نام کاربری</label>
                    <input class="form-control" type="text" name="username" id="username">
                </div>
                <div class="form-group">
                    <label for="password" class="pull-right">کلمه عبور</label>
                    <input class="form-control" type="password" name="password" id="password">
                </div>
                <button class="btn btn-primary">ورود</button>
            </form>
        </div>
    </div>
@endsection
@extends('pages.dashboard')

@section('dashboard_content')
    <div class="row">
        <div class="col-12">
            <h2 class="text-right">امور مالی</h2>
        </div>
        <div class="col-12">
            <a href="{{url('dashboard.payments',['paymentType'=>'unSettled'])}}"
               class="btn btn-outline-primary {{$active==1 ? 'active' : ''}}">سفرهای تسویه
                نشده</a>
            <a href="{{url('dashboard.payments',['paymentType'=>'settled'])}}"
               class="btn btn-outline-primary {{$active==2 ? 'active' : ''}}">سفرهای تسویه شده</a>
            <a href="{{url('dashboard.wallets',['userType'=>'area'])}}"
               class="btn btn-outline-primary {{$active==3 ? 'active' : ''}}">کیف پول ناحیه ها</a>
            <a href="{{url('dashboard.wallets',['userType'=>'company'])}}"
               class="btn btn-outline-primary {{$active==4 ? 'active' : ''}}">کیف پول شرکت ها</a>
            <a href="{{url('dashboard.wallets',['userType'=>'driver'])}}"
               class="btn btn-outline-primary {{$active==5 ? 'active' : ''}}">کیف پول رانندگان</a>
            <a href="{{url('dashboard.wallets',['userType'=>'passenger'])}}"
               class="btn btn-outline-primary {{$active==6 ? 'active' : ''}}">کیف پول مسافران</a>
        </div>
    </div>
    <hr>
    @yield('payments_content')
@endsection
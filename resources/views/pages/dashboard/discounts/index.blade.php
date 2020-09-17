@extends('pages.dashboard')

@section('dashboard_content')
    <div class="row">
        <div class="col-12">
            <h2 class="text-right">تخفیف ها</h2>
        </div>
        <div class="col-12">
            <a href="{{url('dashboard.discounts')}}"
               class="btn btn-outline-primary {{$active==1 ? 'active' : ''}}">کدهای تخفیف</a>
            <a href="{{url('dashboard.payments',['paymentType'=>'settled'])}}"
               class="btn btn-outline-primary {{$active==2 ? 'active' : ''}}">معرفی دوستان</a>
        </div>
    </div>
    <hr>
    @yield('discounts_content')
@endsection
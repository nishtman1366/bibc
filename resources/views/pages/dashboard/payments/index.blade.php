@extends('pages.dashboard')

@section('dashboard_content')
    <div class="row">
        <div class="col-12">
            <h2 class="text-right">امور مالی</h2>
        </div>
        <div class="col-12">
            <a href="#" class="btn btn-outline-primary">گزارش مالی سفرها</a>
            <a href="#" class="btn btn-outline-primary">گزارش درخواست های پرداخت رانندگان</a>
            <a href="#" class="btn btn-outline-primary">گزارش کیف پول رانندگان</a>
            <a href="#" class="btn btn-outline-primary">گزارش کیف پول مسافران</a>
            <a href="#" class="btn btn-outline-primary"></a>
        </div>
    </div>
    <hr>
    @yield('payments_content')
@endsection
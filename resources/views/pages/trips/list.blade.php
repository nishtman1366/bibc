@extends('pages.dashboard',['active'=>'trips'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">لیست سفرها</h2>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">نوع خودرو</th>
            <th scope="col">شماره سفر</th>
            <th scope="col">آدرس</th>
            <th scope="col">تاریخ سفر</th>
            <th scope="col">شرکت</th>
            <th scope="col">راننده</th>
            <th scope="col">مسافر</th>
            <th scope="col">کرایه</th>
            <th scope="col">خودرو</th>
            <th scope="col">عملیات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($trips as $trip)
            <tr>
                <td>{{$trip->eType}}</td>
                <td>{{$trip->vRideNo}}</td>
                <td><span class="d-block">از: {{$trip->tSaddress}}</span>
                    <span class="d-block">به: {{$trip->tDaddress}}</span></td>
                <td>{{jdate($trip->tTripRequestDate)->format('Y/m/d')}}</td>
                <td>{{$trip->driver->company->vCompany}}</td>
                <td>{{$trip->driver->fullName}}</td>
                <td>{{$trip->passenger->fullName}}</td>
                <td>{{tripCurrency($trip->iFare)}}</td>
                <td>{{$trip->vehicleType->vVehicleType}}</td>
                <td><a href="#" class="btn btn-primary">صورتحساب</a> </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
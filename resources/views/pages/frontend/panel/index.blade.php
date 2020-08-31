@extends('layouts.frontend.index')

@section('content')
    <div class="row p-4">
        <div class="col-12 col-md-3 d-none d-md-block">
            <div class="card">
                <div class="card-header">
                    امکانات
                </div>
                <div class="card-body">
                    <ul>
                        @foreach($user as $role=>$info)
                            @if($role==='company')
                                <li class="text-right"><a href="{{url('panel')}}">نام شرکت: {{$info->vCompany}}</a>
                                    <ul>
                                        <li><a href="{{url('company.payments')}}">امور مالی</a> </li>
                                        <li>3</li>
                                        <li>4</li>
                                        <li>5</li>
                                    </ul>
                                </li>
                            @endif
                            @if($role==='driver')
                                <li class="text-right"><a href="{{url('panel')}}">{{$info->fullName}} خوش آمدید</a>
                                    <ul>
                                        <li><a href="{{url('driver.vehicles')}}">خودروها</a></li>
                                        <li><a href="{{url('driver.trips')}}">سفرها</a></li>
                                        <li><a href="{{url('driver.payments')}}">درآمد</a></li>
                                        <li><a href="{{url('driver.wallet')}}">کیف پول</a></li>
                                    </ul>
                                </li>
                            @endif
                            @if($role==='passenger')
                                <li class="text-right"><a href="{{url('panel')}}">{{$info->fullName}} خوش آمدید</a>
                                    <ul>
                                        <li><a href="{{url('/')}}">خودروها</a></li>
                                        <li><a href="{{url('/')}}">سفرها</a></li>
                                        <li><a href="{{url('/')}}">درآمد</a></li>
                                        <li><a href="{{url('/')}}">کیف پول</a></li>
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-9">
            <div class="card">
                <div class="card-body">
                    @yield('panel_content')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('meta')
    @foreach($user as $role=>$info)
        @if($role==='company')
            <meta name="company-api-token" content="{{$info->apiToken->token}}">
        @endif
        @if($role==='driver')
            <meta name="driver-api-token" content="{{$info->apiToken->token}}">
        @endif
        @if($role==='passenger')
            <meta name="passenger-api-token" content="{{$info->apiToken->token}}">
        @endif
    @endforeach
@endpush
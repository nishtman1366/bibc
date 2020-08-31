@extends('pages.frontend.panel.index')

@section('panel_content')
    <div class="col-12">
        <h2 class="text-right">درآمدهای شما</h2>
        <div class="row">
            <div class="col-12 text-right">
                <a href="{{url('driver.payments',['paymentType'=>'notRequested'])}}"
                   class="btn btn-outline-info {{$active=='notRequested' ? 'active' : ''}}">سفرهای تسویه نشده</a>
                <a href="{{url('driver.payments',['paymentType'=>'notSettled'])}}"
                   class="btn btn-outline-info {{$active=='notSettled' ? 'active' : ''}}">درخواست
                    های پرداخت</a>
                <a href="{{url('driver.payments',['paymentType'=>'settled'])}}"
                   class="btn btn-outline-info {{$active=='settled' ? 'active' : ''}}">سفرهای تسویه شده</a>
            </div>
        </div>
        <button id="request-payment-button" class="btn btn-success">درخواست انتقال</button>
    </div>
    <hr/>
    @foreach($trips as $trip)
        <div class="row m-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-right">
                        <div class="row">
                            <div class="col-5">
                                <p><i class="fa fa-sticky-note"></i>شماره سفر:{{$trip->vRideNo}}</p>
                                <p><i class="fa fa-calendar"></i>تاریخ
                                    سفر:{{jdate($trip->tTripRequestDate)->format('Y/m/d')}}</p>
                            </div>
                            <div class="col-5">
                                <p><i class="fa fa-money"></i> مبلغ کرایه: {{tripCurrency($trip->iFare)}} </p>
                                <p><i class="fa fa-money"></i> مبلغ کمیسیون: {{tripCurrency($trip->fCommision)}} </p>
                                <p><i class="fa fa-money"></i> مبلغ
                                    پرداختی: {{tripCurrency($trip->iFare - $trip->fCommision)}} </p>
                            </div>
                            @if($trip->eDriverPaymentStatus!=='Settelled' || ($trip->eDriverPaymentStatus=='Unsettelled' && $trip->ePayment_request=='No'))
                                <div class="col-2">
                                    <button
                                            class="btn btn-outline-primary select-trip"
                                            data-trip-id="{{$trip->iTripId}}"
                                            data-trip-fare="{{$trip->iFare}}">انتخاب
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-4 text-success"><i class="fa fa-money"></i>هزینه
                                سفر: {{tripCurrency($trip->iFare)}}
                            </div>
                            <div class="col-4 text-info"><i class="fa fa-money"></i>نوع
                                خودرو: {{$trip->vehicleType->vVehicleType}}</div>
                            <div class="col-4 text-danger"><i class="fa fa-money"></i>مشاهده صورتحساب</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            let trips = [];
            $('.select-trip').click(function () {
                let tripId = $(this).attr('data-trip-id');
                let tripFare = $(this).attr('data-trip-fare');
                if ($(this).hasClass('active')) {
                    for (let i = 0; i < trips.length; i++) {
                        if (trips[i].tripId === tripId) {
                            trips.splice(i, 1);
                        }
                    }
                    $(this).removeClass('active');
                } else {
                    trips.push({tripId, tripFare});
                    $(this).addClass('active');
                }
            });
            $("#request-payment-button").click(function () {
                if (trips.length <= 0) {
                    alert('error');
                } else {
                    ajax.post('drivers/payments/request', {trips})
                        .then(function (response) {
                            alert(response.data.message);
                            window.location.reload();
                        })
                        .catch(function (error) {
                            alert(response.data.message);
                            window.location.reload();
                        })
                }
            });
        });
    </script>
@endpush
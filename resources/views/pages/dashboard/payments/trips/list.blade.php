@extends('pages.dashboard.payments.index')

@section('payments_content')
    <div class="col-12">
        <a href="{{url('dashboard.payments',['paymentType'=>'all','driverRequest'=>'all'])}}"
           class="btn btn-outline-primary {{$active==='allRecords' ? 'active' : ''}}">همه سفرها</a>
        <a href="{{url('dashboard.payments',['paymentType'=>'unSettled','driverRequest'=>'all'])}}"
           class="btn btn-outline-primary {{$active==='allUnsettledRecords' ? 'active' : ''}}">تسویه
            نشده ها</a>
        <a href="{{url('dashboard.payments',['paymentType'=>'unSettled','driverRequest'=>'requested'])}}"
           class="btn btn-outline-primary {{$active==='requestedUnsettledRecords' ? 'active' : ''}}">درخواست پرداخت
            رانندگان</a>
        <a href="{{url('dashboard.payments',['paymentType'=>'settled','driverRequest'=>'all'])}}"
           class="btn btn-outline-primary {{$active==='allSettledRecords' ? 'active' : ''}}">تسویه شده
            ها</a>
    </div>
    <hr/>
    <div class="row">
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <tr>
                    <th scope="col">ردیف</th>
                    <th scope="col">شماره سفر</th>
                    <th scope="col">نام شرکت</th>
                    <th scope="col">نام راننده</th>
                    <th scope="col">نام مسافر</th>
                    <th scope="col">تاریخ سفر</th>
                    <th scope="col">مبلغ کرایه</th>
                    <th scope="col">کرایه پرداخت شده</th>
                    <th scope="col">دریافتی راننده</th>
                    <th scope="col">مبلغ کمیسیون شرکت</th>
                    <th scope="col">مبلغ کمیسیون نماینده</th>
                    <th scope="col">مبلغ کمیسیون پلتفرم</th>
                    <th scope="col">کد تخفیف</th>
                    <th scope="col">وام نقدی</th>
                    <th scope="col">وضعیت سفر</th>
                    <th scope="col">شیوه پرداخت</th>
                    <th scope="col">وضعیت درخواست راننده</th>
                    <th scope="col">وضعیت پرداخت راننده</th>
                </tr>
                @php
                    $totalFTripGenerateFare=$totalIFare=$totalFCommision=$totalDriverRefund=0
                @endphp
                @foreach($trips as $trip)
                    @php
                        $driverRefund=($trip->fWalletDebit+$trip->fDiscount)-$trip->fCommision;
                    @endphp
                    <tr class="trip-row" data-trip-id="{{$trip->iTripId}}">
                        <td></td>
                        <td>{{$trip->vRideNo}}</td>
                        <td>{{$trip->driver->company->vCompany}}</td>
                        <td>{{$trip->driver->fullName}}</td>
                        <td>{{$trip->passenger->fullName}}</td>
                        <td>{{$trip->jDate}}</td>
                        <td>{{addCurrencySymbol($trip->fTripGenerateFare)}}</td>
                        <td>{{addCurrencySymbol($trip->iFare)}}</td>
                        <td>{{addCurrencySymbol($driverRefund)}}</td>
                        <td>{{addCurrencySymbol($trip->fCommision)}}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>{{addCurrencySymbol($trip->fDiscount)}}</td>
                        <td>{{addCurrencySymbol($trip->fWalletDebit)}}</td>
                        <td>{{$trip->iActiveText}}</td>
                        <td>{{$trip->vTripPaymentModeText}}</td>
                        <td>{{$trip->ePayment_request}}</td>
                        <td>{{$trip->eDriverPaymentStatus}}</td>
                    </tr>
                    @php
                        $totalFTripGenerateFare+=$trip->fTripGenerateFare;
                        $totalIFare+=$trip->iFare;
                        $totalFCommision+=$trip->fCommision;
                        $totalDriverRefund+=$driverRefund;
                    @endphp
                @endforeach
                <tr>
                    <td colspan="6">جمع مبالغ</td>
                    <td>{{addCurrencySymbol($totalFTripGenerateFare)}}</td>
                    <td>{{addCurrencySymbol($totalIFare)}}</td>
                    <td>{{addCurrencySymbol($totalDriverRefund)}}</td>
                    <td>{{addCurrencySymbol($totalFCommision)}}</td>
                    <td>-</td>
                    <td>-</td>
                    <td colspan="6">
                        <button id="request-payment-button" class="btn btn-success d-none">تسویه حساب با رانندگان</button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection
@push('css')
    <style>
        table tr.active {
            background-color: #518293 !important;
        }
    </style>
@endpush
@push('js')
    <script>
        $(document).ready(function () {
            let trips = [];
            $('.trip-row').click(function () {
                let tripId = $(this).attr('data-trip-id');
                if ($(this).hasClass('active')) {
                    let index = trips.indexOf(tripId);
                    trips.splice(index, 1);
                    $(this).removeClass('active');
                } else {
                    trips.push(tripId);
                    $(this).addClass('active');
                }
                if(trips.length > 0){
                    $("#request-payment-button").removeClass('d-none');
                }else{
                    $("#request-payment-button").addClass('d-none');
                }
            });
            $("#request-payment-button").click(function () {
                if (trips.length <= 0) {
                    alert('error');
                } else {
                    ajax.post('dashboard/payments/settle', {trips})
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
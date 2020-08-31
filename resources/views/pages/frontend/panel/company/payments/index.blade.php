@extends('pages.frontend.panel.index')

@section('panel_content')
    <div class="col-12">
        <a href="{{url('company.payments',['paymentType'=>'all','driverRequest'=>'all'])}}"
           class="btn btn-outline-primary {{$active==='allRecords' ? 'active' : ''}}">همه سفرها</a>
        <a href="{{url('company.payments',['paymentType'=>'unSettled','driverRequest'=>'all'])}}"
           class="btn btn-outline-primary {{$active==='allUnsettledRecords' ? 'active' : ''}}">تسویه
            نشده ها</a>
        <a href="{{url('company.payments',['paymentType'=>'unSettled','driverRequest'=>'requested'])}}"
           class="btn btn-outline-primary {{$active==='requestedUnsettledRecords' ? 'active' : ''}}">درخواست پرداخت
            رانندگان</a>
        <a href="{{url('company.payments',['paymentType'=>'settled','driverRequest'=>'all'])}}"
           class="btn btn-outline-primary {{$active==='allSettledRecords' ? 'active' : ''}}">تسویه شده
            ها</a>
    </div>
    <hr/>
    <form action="{{url('company.payments')}}" method="get">
        <input type="hidden" name="gFromDate" id="gFromDate" value="{{is_null($fromDate) ? '' : $fromDate}}">
        <input type="hidden" name="gToDate" id="gToDate" value="{{is_null($toDate) ? '' : $toDate}}">
        <div class="row">
            <div class="input-group col-12 m-1">
                <span class="input-group-text border-left-0"
                      style="border-top-left-radius: 0;border-bottom-left-radius: 0;">تاریخ شروع:</span>
                <input id="jFromDate" type="text" data-mddatetimepicker="true" data-placement="right" name="jFromDate"
                       class="form-control border-right-0 border-left-0 bg-white" readonly
                       style="border-radius: 0;" placeholder="تاریخ بصورت: 1399/01/12"
                       value="">
                <span class="input-group-text border-left-0"
                      style="border-radius: 0;">تاریخ پایان:</span>
                <input id="jToDate" type="text" data-mddatetimepicker="true" data-placement="right" name="jToDate"
                       class="form-control border-right-0 border-left-0 bg-white" readonly
                       style="border-radius: 0;" placeholder="تاریخ بصورت: 1399/01/12"
                       value="">
                <button class="btn btn-primary border-right-0"
                        style="border-top-right-radius: 0;border-bottom-right-radius: 0;">جستجو
                </button>
            </div>
        </div>
    </form>
    <div class="row">
        <div class="col-12">
            @if(is_null($fromDate) || is_null($toDate))
                <h3 class="alert alert-warning text-center">لطفا بازه تاریخ مورد نظر جهت نمایش سفرها را انتخاب
                    کنید.</h3>
            @else
                @if(count($trips) > 0)
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
                            <th scope="col">کد تخفیف</th>
                            <th scope="col">پرداخت از کیف پول</th>
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
                                <td>
                                    <a href="{{url('company/drivers/').$trip->iDriverId}}">{{$trip->driver->fullName}}</a>
                                </td>
                                <td>{{$trip->passenger->fullName}}</td>
                                <td>{{$trip->jDate}}</td>
                                <td>{{addCurrencySymbol($trip->fTripGenerateFare)}}</td>
                                <td>{{addCurrencySymbol($trip->iFare)}}</td>
                                <td>{{addCurrencySymbol($driverRefund)}}</td>
                                <td>{{addCurrencySymbol($trip->fCommision)}}</td>
                                <td>{{addCurrencySymbol($trip->area_commission)}}</td>
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
                                <button id="request-payment-button" class="btn btn-success d-none">تسویه حساب با
                                    رانندگان
                                </button>
                            </td>
                        </tr>
                    </table>
                @else
                    <h3 class="alert alert-warning text-center">در بازه انتخاب شده هیچ سفری توسط رانندگان شما صورت
                        نگرفته است</h3>
                @endif
            @endif
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{assets('vendor/PersianCalendar/jquery.md.bootstrap.datetimepicker.style.css')}}">
    <style>
        table tr.active {
            background-color: #518293 !important;
        }
    </style>
@endpush
@push('js')
    <script src="{{assets('vendor/PersianCalendar/jquery.md.bootstrap.datetimepicker.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('#jFromDate').MdPersianDateTimePicker({
                targetTextSelector: '#jFromDate',
                targetDateSelector: '#gFromDate'
            });
            $('#jToDate').MdPersianDateTimePicker({
                targetTextSelector: '#jToDate',
                targetDateSelector: '#gToDate'
            });
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
                if (trips.length > 0) {
                    $("#request-payment-button").removeClass('d-none');
                } else {
                    $("#request-payment-button").addClass('d-none');
                }
            });
            $("#request-payment-button").click(function () {
                if (trips.length <= 0) {
                    alert('error');
                } else {
                    ajax.post('companies/trips/settle', {trips})
                        .then(function (response) {
                            // alert(response.data.message);
                            // window.location.reload();
                            console.log(response.data);
                        })
                        .catch(function (error) {
                            // alert(response.data.message);
                            // window.location.reload();
                            console.log(error.data.message);

                        })
                }
            });
        });
    </script>
@endpush
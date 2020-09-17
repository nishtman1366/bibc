@extends('pages.dashboard.payments.index')

@section('payments_content')
    <div class="col-12">
        <h4>سفرهای تسویه نشده</h4>
        <form action="{{url('dashboard.payments')}}" method="get">
            <input type="hidden" name="gDate" id="gDate" value="{{is_null($date) ? '' : $date}}">
            <div class="row">
                <div class="input-group col-12 m-1">
                <span class="input-group-text border-left-0"
                      style="border-top-left-radius: 0;border-bottom-left-radius: 0;">تاریخ گزارش:</span>
                    <input id="jDate" type="text" data-mddatetimepicker="true" data-placement="right"
                           name="jDate"
                           class="form-control border-right-0 border-left-0 bg-white" readonly
                           style="border-radius: 0;" placeholder="تاریخ بصورت: 1399/01/12"
                           value="">
                    <span class="input-group-text border-left-0"
                          style="border-radius: 0;">انتخاب راننده:</span>
                    <input id="driver-name" type="text" name="driver"
                           class="form-control border-right-0 border-left-0 bg-white" readonly
                           style="border-radius: 0;" placeholder="جهت انتخاب راننده کلیک کنید"
                           value="">
                    <button type="reset" class="btn btn-secondary  border-right-0 border-left-0"
                            style="border-radius: 0;"
                            style="border-top-right-radius: 0;border-bottom-right-radius: 0;">حذف فیلترها
                    </button>
                    <button class="btn btn-primary border-right-0"
                            style="border-top-right-radius: 0;border-bottom-right-radius: 0;">جستجو
                    </button>
                </div>
            </div>
        </form>
        <div class="row p-1">
            <div class="col-4">
                <form action="{{url('dashboard.payments.settlement')}}" method="post">
                    <input type="hidden" name="date" value="{{$date}}">
                    <button class="btn btn-primary col-12"><h3>انجام عملیات تسویه حساب</h3></button>
                </form>
            </div>
            <div class="col-4">
                <h4 class="alert alert-info text-center">تعداد کل سفرها در این تاریخ: {{$tripsCount}}</h4>
            </div>
            <div class="col-4"></div>
        </div>

    </div>
    <hr/>
    <div class="row">
        <div class="col-12">
            @foreach($list as $item)
                <table class="table table-striped table-bordered table-hover">
                    <tr>
                        <td colspan="2">نام ناحیه: {{$item['areaName']}}</td>
                        <td colspan="9">تعداد سفرها: {{$item['tripsCount']}}</td>
                    </tr>
                    <tr>
                        <th scope="col">ردیف</th>
                        <th scope="col">نام شرکت</th>
                        <th scope="col">تعداد سفرها</th>
                        <th scope="col">کرایه</th>
                        <th scope="col">تخفیف</th>
                        <th scope="col">پرداخت آنلاین</th>
                        <th scope="col">پرداخت نقدی</th>
                        <th scope="col">دریافتی رانندگان</th>
                        <th scope="col">سهم شرکت</th>
                        <th scope="col">سهم نماینده</th>
                        <th scope="col">سهم پلتفرم</th>
                    </tr>
                    @if(count($item['companies']) > 0)
                        @php
                            $j=0;
                            $totalFTripGenerateFare = $totalWalletDebit = 0;
                            $totalIFare = $totalDriverRefund = $totalFCommission = 0;
                            $totalAreaCommission = $totalPlatformCommission = 0;
                            $totalDiscount = 0;
                        @endphp
                        @foreach($item['companies'] as $company)
                            @php
                                $j++;
                            @endphp
                            <tr>
                                <td>{{$j}}</td>
                                <td>{{$company['name']}}</td>
                                <td>{{$company['tripsCount']}}</td>
                                <td>{{addCurrencySymbol($company['totalFTripGenerateFare'])}}</td>
                                <td>{{addCurrencySymbol($company['totalDiscount'])}}</td>
                                <td>{{addCurrencySymbol($company['totalWalletDebit'])}}</td>
                                <td>{{addCurrencySymbol($company['totalIFare'])}}</td>
                                <td>{{addCurrencySymbol($company['totalDriverRefund'])}}</td>
                                <td>{{addCurrencySymbol($company['totalFCommission'])}}</td>
                                <td>{{addCurrencySymbol($company['totalAreaCommission'])}}</td>
                                <td>{{addCurrencySymbol($company['totalPlatformCommission'])}}</td>
                            </tr>
                            @php
                                $totalFTripGenerateFare += $company['totalFTripGenerateFare'];
                                $totalDiscount += $company['totalDiscount'];
                                $totalWalletDebit += $company['totalWalletDebit'];
                                $totalIFare += $company['totalIFare'];
                                $totalDriverRefund += $company['totalDriverRefund'];
                                $totalFCommission += $company['totalFCommission'];
                                $totalAreaCommission += $company['totalAreaCommission'];
                                $totalPlatformCommission += $company['totalPlatformCommission'];
                            @endphp
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td>جمع مبالغ</td>
                            <td>{{addCurrencySymbol($totalFTripGenerateFare)}}</td>
                            <td>{{addCurrencySymbol($totalDiscount)}}</td>
                            <td>{{addCurrencySymbol($totalWalletDebit)}}</td>
                            <td>{{addCurrencySymbol($totalIFare)}}</td>
                            <td>{{addCurrencySymbol($totalDriverRefund)}}</td>
                            <td>{{addCurrencySymbol($totalFCommission)}}</td>
                            <td>{{addCurrencySymbol($totalAreaCommission)}}</td>
                            <td>{{addCurrencySymbol($totalPlatformCommission)}}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="9">
                                <h3 class="text-center">هیچ شرکتی در این ناحیه فعال نیست</h3>
                            </td>
                        </tr>
                    @endif
                </table>
            @endforeach
            <div class="dropdown-divider"></div>
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
            $('#jDate').MdPersianDateTimePicker({
                targetTextSelector: '#jDate',
                targetDateSelector: '#gDate'
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
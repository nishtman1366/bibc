@extends('pages.dashboard.discounts.index')

@section('discounts_content')
    <div class="row">
        <div class="col-12 text-left">
            <a href="{{url('dashboard.discounts')}}" class="btn btn-primary">بازگشت</a>
        </div>
    </div>
    <form action="{{url('dashboard.discounts.create')}}" method="post">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label for="vCouponCode">کد تخفیف</label>
                    <input type="text" value="{{(!is_null($coupon)) ? $coupon->vCouponCode : ''}}" name="vCouponCode" id="vCouponCode" class="form-control">
                </div>
                <div class="form-inline">
                    <span class="form-text"><label for="fDiscount">مقدار تخفیف</label></span>
                    <input type="text" name="fDiscount" value="{{(!is_null($coupon)) ? $coupon->fDiscount : ''}}" id="fDiscount" class="form-control">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eType=='percentage') ? 'active' : ''}}">درصدی
                            <input type="radio" name="eType" value="percentage"
                                   autocomplete="off" {{(!is_null($coupon) && $coupon->eType=='percentage') ? 'checked' : ''}}></label>
                        <label class="btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eType=='cash') ? 'active' : ''}}">مبلغ
                            ثابت
                            <input type="radio" name="eType" value="cash"
                                   autocomplete="off" {{(!is_null($coupon) && $coupon->eType=='cash') ? 'checked' : ''}}></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="iUsageLimit">محدودیت تعداد استفاده</label>
                    <input type="number" value="{{(!is_null($coupon)) ? $coupon->iUsageLimit : ''}}" name="iUsageLimit" id="iUsageLimit" class="form-control">
                </div>
                <div class="form-group">
                    <label>وضعیت</label>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eStatus=='Active') ? 'active' : ''}}">فعال
                            <input type="radio" name="eStatus" value="Active"
                                   autocomplete="off" {{(!is_null($coupon) && $coupon->eStatus=='Active') ? 'checked' : ''}}></label>
                        <label class="btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eStatus=='Inactive') ? 'active' : ''}}">غیرفعال
                            <input type="radio" name="eStatus" value="Inactive"
                                   autocomplete="off" {{(!is_null($coupon) && $coupon->eStatus=='Inactive') ? 'checked' : ''}}></label>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group ">
                    <label for="tDescription">توضیحات</label>
                    <textarea name="tDescription" id="tDescription" class="form-control">{{(!is_null($coupon)) ? $coupon->tDescription : ''}}</textarea>
                </div>

                <div class="form-group">
                    <span class="form-text"><label for="eValidityType">اعتبار</label></span>
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="eValidityTypeButton btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eValidityType=='Permanent') ? 'active' : ''}}">موقتی
                            <input type="radio" name="eValidityType" value="Permanent" class="eValidityType"
                                   autocomplete="off" {{(!is_null($coupon) && $coupon->eValidityType=='Permanent') ? 'checked' : ''}}></label>
                        <label class="eValidityTypeButton btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eValidityType=='Defined') ? 'active' : ''}}">دائم
                            <input type="radio" name="eValidityType" value="Defined" class="eValidityType"
                                   autocomplete="off" {{(!is_null($coupon) && $coupon->eValidityType=='Defined') ? 'checked' : ''}}></label>
                    </div>
                    <div class="row">
                        <div class="form-group col-12 col-md-6 d-none eValidityTypeCalendar">
                            <label for="jdActiveDate">تاریخ شروع</label>
                            <input type="text" name="jdActiveDate" id="jdActiveDate" data-mddatetimepicker="true"
                                   data-placement="right" class="form-control">
                        </div>
                        <div class="form-group col-12 col-md-6 d-none eValidityTypeCalendar">
                            <label for="jdExpiryDate">تاریخ انقضا</label>
                            <input type="text" name="jdExpiryDate" id="jdExpiryDate" data-mddatetimepicker="true"
                                   data-placement="right" class="form-control">
                        </div>
                    </div>
                </div>
                <input type="hidden" name="dActiveDate" id="dActiveDate"
                       value="{{is_null($coupon) ? '' : $coupon->dActiveDate}}">
                <input type="hidden" name="dExpiryDate" id="dExpiryDate"
                       value="{{is_null($coupon) ? '' : $coupon->dExpiryDate}}">

                <div class="form-group">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                        <label class="btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eOnePerUser=='Yes') ? 'active' : ''}}"
                               for="eOnePerUser">فقط برای یک کاربر
                            <input type="checkbox" name="eOnePerUser" id="eOnePerUser" value="Yes"
                                    {{(!is_null($coupon) && $coupon->eOnePerUser=='Yes') ? 'checked' : ''}}></label>
                        <label class="btn btn-outline-secondary {{(!is_null($coupon) && $coupon->eForFirstTrip=='Yes') ? 'active' : ''}}"
                               for="eForFirstTrip">فقط برای اولین سفر
                            <input type="checkbox" name="eForFirstTrip" id="eForFirstTrip" value="Yes"
                                    {{(!is_null($coupon) && $coupon->eForFirstTrip=='Yes') ? 'checked' : ''}}></label>
                    </div>
                </div>
            </div>
            <button class="btn btn-success col-12">ثبت اطلاعات</button>
        </div>
    </form>
@endsection
@push('css')
    <link rel="stylesheet" href="{{assets('vendor/PersianCalendar/jquery.md.bootstrap.datetimepicker.style.css')}}">
@endpush
@push('js')
    <script src="{{assets('vendor/PersianCalendar/jquery.md.bootstrap.datetimepicker.js')}}"></script>

    <script>
        $('#jdActiveDate').MdPersianDateTimePicker({
            targetTextSelector: '#jdActiveDate',
            targetDateSelector: '#dActiveDate'
        });
        $('#jdExpiryDate').MdPersianDateTimePicker({
            targetTextSelector: '#jdExpiryDate',
            targetDateSelector: '#dExpiryDate'
        });
        $(document).ready(function () {
            $(".eValidityTypeButton").click(function () {
                let eValidityValue = $(this).children('input').val();
                if (eValidityValue === 'Permanent') {
                    $(".eValidityTypeCalendar").addClass('d-none');
                } else if (eValidityValue === 'Defined') {
                    $(".eValidityTypeCalendar").removeClass('d-none');
                }
            });
        });
    </script>
@endpush

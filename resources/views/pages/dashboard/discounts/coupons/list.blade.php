@extends('pages.dashboard.discounts.index')

@section('discounts_content')
    <div class="row">
        <div class="col-12 text-left">
            <a href="{{url('dashboard.discounts.new')}}" class="btn btn-primary">ثبت کد تخفیف جدید</a>
        </div>
    </div>
    <table class="table table-striped table-bordered table-hover">
        <tr>
            <th scope="col">ردیف</th>
            <th scope="col">کد تخفیف</th>
            <th scope="col">میزان تخفیف</th>
            <th scope="col">اعتبار</th>
            <th scope="col">تاریخ فعال سازی</th>
            <th scope="col">تاریخ انقضا</th>
            <th scope="col">محدودیت استفاده</th>
            <th scope="col">دفعات استفاده</th>
            <th scope="col">وضعیت</th>
            <th scope="col">عملیات</th>
        </tr>
        @if(count($coupons) > 0)
            @php($i=1)
            @foreach($coupons as $coupon)
                <tr>
                    <td>{{$i}}</td>
                    <td>{{$coupon->vCouponCode}}</td>
                    <td>{{$coupon->fDiscountText}}</td>
                    <td>{{$coupon->eValidityType}}</td>
                    <td>{{$coupon->dActiveDate}}</td>
                    <td>{{$coupon->dExpiryDate}}</td>
                    <td>{{$coupon->iUsageLimit}}</td>
                    <td>{{$coupon->iUsed}}</td>
                    <td>{{$coupon->eStatusText}}</td>
                    <td>
                        <a href="{{url('dashboard.discounts.view',['id'=>$coupon->iCouponId])}}">
                            <i class="fa fa-pencil text-primary m-1"></i>
                        </a>
                        <a class="delete-coupon" data-id="{{$coupon->iCouponId}}" href="#">
                            <i class="fa fa-trash text-danger m-1"></i>
                        </a>
                    </td>
                </tr>
                @php($i++)
            @endforeach
        @else
            <tr>
                <td colspan="10">کد تخفیفی ثبت نشده است</td>
            </tr>
        @endif
    </table>
    <form id="delete-coupon-form" action="{{url('dashboard.discounts.delete',['id'=>''])}}" method="post">
{{--        @method('DELETE')--}}
    </form>
@endsection
@push('js')
    <script>
        $(document).ready(function () {
            $(".delete-coupon").click(function (e) {
                e.preventDefault();
                let action = $("#delete-coupon-form").attr('action');
                let id = $(this).attr('data-coupon-id');
                action = action + '/' + id;
                console.log(action);
            });
        });
    </script>
@endpush
@extends('pages.dashboard',['active'=>'bookings'])

@section('dashboard_content')
    <div class="col-12">
        <h2 class="text-right">رزروها</h2>
        <a class="btn btn-primary pull-left" href="{{url('bookings.new')}}">رزرو سفر جدید</a>
    </div>
    <hr/>
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <th scope="col">مسافر</th>
            <th scope="col">تاریخ</th>
            <th scope="col">مبدا</th>
            <th scope="col">مقصد</th>
            <th scope="col">راننده</th>
            <th scope="col">اطلاعات سفر</th>
            <th scope="col">وضعیت</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bookings as $booking)
            <tr>
                <td>{{$booking->passenger->fullName}}</td>
                <td>{{jdate($booking->dBooking_date)->format('Y/m/d H:i:s')}}</td>
                <td>{{$booking->vSourceAddresss}}</td>
                <td>{{$booking->tDestAddress}}</td>
                <td>
                    @if ($booking->eAutoAssign == "Yes")
                        راننده خودکار اختصاص داشته شده است<a
                                class="btn btn-info"
                                href="add_booking.php?booking_id={{$booking->iCabBookingId}}"
                                data-tooltip="tooltip" title="Edit"><i
                                    class="icon-edit icon-flip-horizontal icon-white"></i></a>
                        <br>
                        (نوع ماشین: {{!is_null($booking->VehicleType) ? $booking->VehicleType->vVehicleType : ''}})
                    @elseif ($booking->eStatus == "Pending")
                        <a class="btn btn-info"
                           href="add_booking.php?booking_id={{$booking->iCabBookingId}}"><i
                                    class="icon-shield icon-flip-horizontal icon-white"></i>
                            راننده اختصاص داده شده</a>
                        <br>
                        (نوع ماشین: {{!is_null($booking->VehicleType) ? $booking->VehicleType->vVehicleType : ''}})
                    @elseif ($booking->eCancelBy == "Driver" && $booking->eStatus == "Cancel")
                        <a class="btn btn-info"
                           href="add_booking.php?booking_id={{$booking->iCabBookingId}}"><i
                                    class="icon-shield icon-flip-horizontal icon-white"></i>
                            راننده اختصاص داده شده</a>
                        <br>
                        (نوع ماشین: {{!is_null($booking->VehicleType) ? $booking->VehicleType->vVehicleType : ''}})
                    @elseif (!is_null($booking->driver))
                        {{$booking->driver->fullName}}
                        <br>
                        (نوع ماشین: {{!is_null($booking->VehicleType) ? $booking->VehicleType->vVehicleType : ''}})
                    @else
                        ---
                        <br>
                        (نوع ماشین: {{!is_null($booking->VehicleType) ? $booking->VehicleType->vVehicleType : ''}})
                    @endif
                </td>
                <td>
                    @if(!is_null($booking->iTripId) && $booking->eStatus=='Completed')
                        <a href="invoice.php?iTripId={{$booking->iTripId}}" class="btn btn-primary">مشاهده</a>
                    @else
                        ----
                    @endif
                </td>
                <td>
                    @if ($booking->eStatus == "Assign")
                        راننده اختصاص داده شده است
                    @else
                        @if(!is_null($booking->trip))
                            @if($booking->trip->iActive=='Canceled')
                                کنسل شده توسط مسافر
                            @else
                                {{$booking->trip->iActive}}
                            @endif
                        @else
                            @if($booking->eStatus=='Cancel')
                                کنسل شده توسط راننده
                            @else
                                {{$booking->eStatus}}
                            @endif
                        @endif
                    @endif
                    @if ($booking->eStatus == "Cancel")
                        <button class="btn btn-info"
                                data-toggle="tooltip"
                                data-html="true"
                                title="<h5>دلیل لغو رزرو</h5>
                                       <p>کنسل شده توسط: {{$booking->eCancelBy}}</p>
                                        <p>دلیل لغو: {{$booking->vCancelReason}}</p>"
                                data-target="#uiModal_{{$booking->iCabBookingId}}">دلیل کنسلی
                        </button>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
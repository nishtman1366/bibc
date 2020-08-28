@extends('pages.dashboard',['active'=>'bookings'])
@php
    if(is_null($booking)){
        $action = url('bookings.create');
    }else{
        $action = url('bookings.update',['id'=>$booking->iDriverVehicleId]);
    }
@endphp
@section('dashboard_content')
    <form id="booking-form" method="post" action="{{$action}}">
        @if(!is_null($booking))
            <input type="hidden" id="u_id" name="id" value="{{$booking->iDriverVehicleId}}"/>
        @endif

    <!-- فاصله مبدا تا مقصد -->
        <input type="hidden" name="vDistance" id="vDistance" value="{{!is_null($booking) ? $booking->vDistance : ''}}">
        <!-- مدت زمان رسیدن از مبدا به مقصد -->
        <input type="hidden" name="vDuration" id="vDuration" value="{{!is_null($booking) ? $booking->vDuration : ''}}">

        <!-- مشخصات نقطه مبدا -->
        <input type="hidden" name="from_lat_long" id="from_lat_long"
               value="{{!is_null($booking) ? '(' . $booking->vSourceLatitude . ', ' . $booking->vSourceLongitude . ')' : ''}}">
        <input type="hidden" name="vSourceLatitude" id="from_lat"
               value="{{!is_null($booking) ? $booking->vSourceLatitude : ''}}">
        <input type="hidden" name="vSourceLongitude" id="from_long"
               value="{{!is_null($booking) ? $booking->vSourceLongitude : ''}}">

        <!-- مشخصات نقطه مقصد -->
        <input type="hidden" name="to_lat_long" id="to_lat_long"
               value="{{!is_null($booking) ? '(' . $booking->vDestLatitude . ', ' . $booking->vDestLongitude . ')' : ''}}">
        <input type="hidden" name="vDestLatitude" id="to_lat"
               value="{{!is_null($booking) ? $booking->vDestLatitude : ''}}">
        <input type="hidden" name="vDestLongitude" id="to_long"
               value="{{!is_null($booking) ? $booking->vDestLongitude : ''}}">


        <input type="hidden" value="1" id="location_found" name="location_found">
        <input type="hidden" value="" id="user_type" name="user_type">
        <input type="hidden" value="{{!is_null($booking) ? $booking->iUserId : ''}}" id="iUserId" name="iUserId">
        <input type="hidden" value="{{!is_null($booking) ? $booking->iCabBookingId : ''}}" id="iCabBookingId"
               name="iCabBookingId">
        <input type="hidden" value="" id="google_server_key"
               name="google_server_key">
        <input type="hidden" value="" id="fromlatitude" name="fromlatitude">
        <input type="hidden" value="" id="fromlongitude" name="fromlongitude">
        <input type="hidden" value="" id="getradius" name="getradius">
        {{-- اطلاعات کشور پیش فرض--}}
        <input type="hidden" value="Iran (Islamic Republic of)" id="vCountry"
               name="vCountry">
        <input type="hidden" value="+98" id="vPhoneCode"
               name="vPhoneCode">
        <div class="row" style="height:100vh">
            <div class="col-6">
                <div class="form-group">
                    <label for="vCountry">کشور</label>
                    <input id="vCountry" type="text" class="form-control" name="vCountryTextField"
                           value="Iran (Islamic Republic of) (+98)"
                           readonly>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <select class="form-control form-control-select" name='iAreaId' id='iAreaId'>
                                <option value="">انتخاب منطقه</option>
                                @foreach($areas as $area)
                                    <option {{$area->aId==$aAreaId ? 'selected' : ''}} value="{{$area->aId}}"
                                            data-area-center="{{$area->mapCenter}}"
                                            data-area-zoom="{{$area->mapZoom}}"
                                    >{{$area->sAreaNamePersian}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <select class="form-control" name="iVehicleTypeId" id="iVehicleTypeId">
                                <option value="">انتخاب وسیله نقلیه</option>
                                @foreach($vehicleTypes as $vehicleType)
                                    <option {{!is_null($booking) && $booking->iVehicleTypeId==$vehicleType['iVehicleTypeId'] ? 'selected' : ''}}
                                            value="{{$vehicleType->iVehicleTypeId}}">{{$vehicleType->vVehicleType}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="trip-info-container">
                    <h4>اطلاعات مسافر</h4>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="">شماره تلفن مسافر</span>
                        </div>
                        <input type="text" pattern="[0-9]{1,}"
                               class="form-control" name="vPhone" id="vPhone"
                               value="{{!is_null($booking) ? $booking->vPhone : ''}}"
                               placeholder="شماره تلفن مسافر را وارد کنید"
                               required style="">
                        <button type="button" class="btn btn-info" id="get-passenger-detail">دریافت اطلاعات</button>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="row">
                        <div class="form-group col-6">
                            <input type="text" title="Only Alpha character allow"
                                   class="form-control" name="vName" id="vName"
                                   value="{{!is_null($booking) ? $booking->vName : ''}}" placeholder="نام" required>
                        </div>
                        <div class="form-group col-6">
                            <input
                                    type="text" title="Only Alpha character allow"
                                    class="form-control" name="vLastName" id="vLastName"
                                    value="{{!is_null($booking) ? $booking->vLastName : ''}}" placeholder="نام خانوادگی"
                                    required>
                            <input type="hidden" class="form-control" name="vEmail"
                                   pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$"
                                   id="vEmail" value="{{!is_null($booking) ? $booking->vEmail : ''}}"
                                   placeholder="ایمیل">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <input type="text"
                                   class="dropdown-toggle form-control"
                                   name="vSourceAddresss" id="from"
                                   value="{{!is_null($booking) ? $booking->vSourceAddresss : ''}}" placeholder="مبدا"
                                   required
                                   data-toggle="dropdown"
                                   aria-haspopup="true"
                                   aria-expanded="false">
                            <div id="from-dropdown" class="dropdown-menu" aria-labelledby="from"></div>
                        </div>
                        <div class="col-6 form-group">
                            <input type="text"
                                   class="dropdown-toggle form-control"
                                   name="tDestAddress" id="to"
                                   value="{{!is_null($booking) ? $booking->tDestAddress : ''}}"
                                   placeholder="مقصد"
                                   required
                                   data-toggle="dropdown"
                                   aria-haspopup="true"
                                   aria-expanded="false">
                            <div id="to-dropdown" class="dropdown-menu" aria-labelledby="to"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="datetime-local" class="form-control"
                               name="dBooking_date_jalali" id="datetimepicker4"
                               value="{{jDate()->format('Y-m-dTH:i:s')}}"
                               placeholder="Select Date / Time" required>
                        <input type="hidden" name="dBooking_date" value="{{date('Y-m-dTH:i:s')}}">
                    </div>
                    <div class="form-group">
                        <textarea type="text"
                                  class="form-control"
                                  name="tTripComment" id="tTripComment"
                                  placeholder="توضیحات">{{!is_null($booking) ? $booking->tTripComment : ''}}</textarea>
                    </div>
                    <div class="input-group">
                        <select class="form-control form-control-select" name='radius-id'
                                id="radius-id" required>
                            @for($i=5;$i<=25;($i=$i+5))
                                <option value="{{$i}}">شعاع {{$i}} کیلومتر</option>
                            @endfor
                        </select>
                        <button type="button" class="btn btn-info" id="manual-select-address">انتخاب دستی مبدا و مقصد
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div id="map" style="width: 100%;height: 100%"></div>
            </div>
            <div class="col-12 d-none" style="height: 500px" id="trip-fare-result">
                <div class="row" id="tripResultLoading">
                    <div class="col-12">
                        <h3 class="text-success text-center">در حال محاسبه هزینه سفر...</h3>
                        <h4 class="text-danger text-center">لطفا منتظر بمانید...</h4>
                    </div>
                </div>
                <div class="d-none text-center p-3" id="tripResultsContainer">
                    <h3>مسافت سفر:<span class="text-success" id="tripDistance"></span></h3>
                    <h3>مدت سفر:<span class="text-success" id="tripDuration"></span></h3>
                    <h3>هزینه سفر:<span class="text-success" id="totalFare"></span></h3>
                </div>
                <div class="row d-none" id="driverContainer">
                    <div class="form-group col-12 col-md-4">
                        <select name="iCompanyId" id="iCompanyId"
                                class="form-control" required>
                            <option value="">انتخاب شرکت</option>
                            @foreach($companies as $company)
                                <option value="{{$company->iCompanyId}}"
                                        {{(!is_null($booking) && $booking->iCompanyId== $company->iCompanyId) ? 'selected' : ''}}>{{$company->vCompany}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-12 col-md-4">
                        <select name="iDriverId" id="iDriverId" class="form-control" required></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="reset" class="btn btn-outline-secondary">پاک کردن لیست</button>
                        <button type="submit" class="btn btn-primary">رزرو سفر</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal" id="user-dialog" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ثبت نام کاربر</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="userFirstName">نام</label>
                        <input type="text" name="userFirstName" id="userFirstName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="userLastName">نام خانوادگی</label>
                        <input type="text" name="userLastName" id="userLastName" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="userPhone">تلفن تماس</label>
                        <input type="text" name="userPhone" id="userPhone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="userEmail">آدرس ایمیل</label>
                        <input type="text" name="userEmail" id="userEmail" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="register-user-btn">ثبت نام کاربر</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    <link rel="stylesheet" href="{{assets('vendor/map.ir/css/mapp.min.css')}}">
    <link rel="stylesheet" href="{{assets('vendor/map.ir/css/fa/style.css')}}">
    <style>

    </style>
@endpush
@push('js')
    <script type="text/javascript" src="{{assets('vendor/map.ir/js/mapp.env.js')}}"></script>
    <script type="text/javascript" src="{{assets('vendor/map.ir/js/mapp.min.js')}}"></script>
@endpush
@push('js')
    <script>
        $(document).ready(function () {
            let fromManualMarker = null;
            let toManualMarker = null;
            let mapCenter = {
                lat: 32,
                lng: 52,
            };
            $("#submit-booking-form").click(function () {
                $("#booking-form").submit();
            });
            $("#iCompanyId").change(function () {
                let companyId = $(this).val();
                $("#company_name").val(companyId);
                console.log(companyId);
                let request = $.ajax({
                    type: "GET",
                    url: '/bibc/api/companies/' + companyId + '/drivers',
                    success: function (data) {
                        console.log(data);
                        $("#iDriverId").children().remove();
                        if (data.length > 0) {
                            $.each(data, function (key, value) {
                                $("#iDriverId").append($('<option>', {value: value.iDriverId})
                                    .text(value.vName + ' ' + value.vLastName));
                            });
                        }
                    }
                });
                request.fail(function (jqXHR, textStatus) {
                    console.log("Request failed: " + textStatus);
                });
            });
            $("#iDriverId").change(function () {
                let driverId = $(this).val();
            })
            let mapIrApiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5In0.eyJhdWQiOiIxMDIxMyIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5IiwiaWF0IjoxNTk1NjYxMTc0LCJuYmYiOjE1OTU2NjExNzQsImV4cCI6MTU5ODI1MzE3NCwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.s0WWrZ2u-B4R9QaI5fxCozQWCP5QFQScUU2bhIt017_Bbpg0ZrnvA_4Ze1ebbSrVAdbGezuyf37ny3ux7Sg4ZO5rttA4EoF1VndtkVsR-br2G7p7FYMKb_e5adZwDrKos8n2mtS6Cytg3ebphaOSUy1GBNS-8rXSU3CsuUgC9AxXsjAIySS5APVoJaBQ9aj3tfO83rY0f1Is34D4emtC39bpw8ZGuj5U9yp4gQrbh7AgWqf217OFE3Od85n8Q8fOEvSN-XrFxE_Rr_dxTes8okfdsnUEiJ-Ha7LIO7lH4efHX1J6SiuwlOrfjasZRexNa1s3Jll3rS-MOP-oOHXKFQ';
            let map = new Mapp({
                element: '#map',
                presets: {
                    latlng: mapCenter,
                    // zoom: 6,
                },
                apiKey: mapIrApiKey
            });
            map.addLayers();
            map.map.on('click', function (e) {
                console.log(e)
            });
            // map.showReverseGeocode({
            //     state: {
            //         latlng: {
            //             lat: 35.73249,
            //             lng: 51.42268,
            //         },
            //         zoom: 16,
            //     },
            // });
            let headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'x-api-key': mapIrApiKey
            };
            let mapSearchData = {
                "text": null,
                "$select": "nearby",
                // "$filter": "province eq تهران",
                "lat": null,
                "lon": null
            };
            $('.trip-info-container').hide();
            $("#iAreaId").change(function () {
                let area = $(this).val();
                let vehicleType = $('#iVehicleTypeId').val();
                let areaCenter = $('option:selected', this).attr('data-area-center');
                areaCenter = JSON.parse(areaCenter);
                let areaZoom = $('option:selected', this).attr('data-area-zoom');
                areaZoom = JSON.parse(areaZoom);
                mapCenter = areaCenter;
                map.map.setView(areaCenter, areaZoom);
                if (area.length > 0 && vehicleType.length > 0) {
                    $('.trip-info-container').fadeIn();
                }
                mapSearchData.lat = areaCenter.lat;
                mapSearchData.lon = areaCenter.lng;
            });
            $("#iVehicleTypeId").change(function () {
                let area = $('#iAreaId').val();
                let vehicleType = $('#iVehicleTypeId').val();
                if (area.length > 0 && vehicleType.length > 0) {
                    $('.trip-info-container').fadeIn();
                }
            });
            $("#from").on("input", function () {
                let searchTerm = $(this).val();
                mapSearchData.text = searchTerm;
                if (searchTerm.length > 4) {
                    axios.post('https://map.ir/search/v2', mapSearchData, {headers})
                        .then(function (response) {
                            console.log(response.data);
                            let results = response.data.value;
                            $("#from-dropdown").children().remove();
                            $("#from").dropdown('show');
                            $.each(results, function (index, item) {
                                $("#from-dropdown").append('<a class="dropdown-item address-item"' +
                                    ' data-item-lat="' + item.geom.coordinates[1] + '"' +
                                    ' data-item-lng="' + item.geom.coordinates[0] + '"' +
                                    ' data-item-address="' + item.address + '"' +
                                    ' href="#">' + item.address + '</a>');
                            });
                            $('#from-dropdown > a.address-item').click(function () {
                                $("#from").val($(this).attr('data-item-address'));
                                addMarkerToMap('from', {
                                    lat: $(this).attr('data-item-lat'),
                                    lng: $(this).attr('data-item-lng')
                                }, 'مبدا', map.icons.red);
                            });
                        })
                        .catch(function (error) {

                        })
                        .finally(function () {

                        });
                }
            });
            $("#to").on("input", function () {
                let searchTerm = $(this).val();
                mapSearchData.text = searchTerm;
                if (searchTerm.length > 4) {
                    axios.post('https://map.ir/search/v2', mapSearchData, {headers})
                        .then(function (response) {
                            console.log(response.data);
                            let results = response.data.value;
                            $("#to-dropdown").children().remove();
                            $("#to").dropdown('show');
                            $.each(results, function (index, item) {
                                $("#to-dropdown").append('<a class="dropdown-item address-item"' +
                                    ' data-item-lat="' + item.geom.coordinates[1] + '"' +
                                    ' data-item-lng="' + item.geom.coordinates[0] + '"' +
                                    ' data-item-address="' + item.address + '"' +
                                    ' href="#">' + item.address + '</a>');
                            });
                            $('#to-dropdown > a.address-item').click(function () {
                                $("#to").val($(this).attr('data-item-address'));
                                addMarkerToMap('to', {
                                    lat: $(this).attr('data-item-lat'),
                                    lng: $(this).attr('data-item-lng')
                                }, 'مقصد', map.icons.blue);
                            });
                        })
                        .catch(function (error) {

                        })
                        .finally(function () {

                        });
                }
            });

            function addMarkerToMap(type, coordinates, title, icon) {
                let name;
                if (type === 'from') {
                    fromManualMarker = map.addMarker({
                        name: 'fromManualMarker',
                        latlng: {
                            lat: coordinates.lat,
                            lng: coordinates.lng,
                        },
                        icon: icon ? icon : map.icons.red,
                        pan: true,
                        draggable: true,
                        history: false,
                        on: {
                            click: function () {
                                console.log('Click callback');
                            },
                            contextmenu: function () {
                                console.log('Contextmenu callback');
                            }
                        },
                    });
                    fromManualMarker.on('dragend', function () {
                        let latitude = this._latlng.lat;
                        let longitude = this._latlng.lng;
                        axios.get('https://map.ir/reverse?lat=' + latitude + '&lon=' + longitude, {headers})
                            .then(function (response) {
                                let address = response.data.address;
                                $("#from").val(address);
                                $("#from_lat").val(latitude);
                                $("#from_long").val(longitude);
                                $("#from_lat_long").val('(' + latitude + ', ' + longitude + ')');
                                calculateDistance();
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    });
                } else if (type === 'to') {
                    toManualMarker = map.addMarker({
                        name: 'toManualMarker',
                        latlng: {
                            lat: coordinates.lat,
                            lng: coordinates.lng,
                        },
                        icon: icon ? icon : map.icons.red,
                        pan: true,
                        draggable: true,
                        history: false,
                        on: {
                            click: function () {
                                console.log('Click callback');
                            },
                            contextmenu: function () {
                                console.log('Contextmenu callback');
                            }
                        },
                    });
                    toManualMarker.on('dragend', function () {
                        let latitude = this._latlng.lat;
                        let longitude = this._latlng.lng;
                        axios.get('https://map.ir/reverse?lat=' + latitude + '&lon=' + longitude, {headers})
                            .then(function (response) {
                                let address = response.data.address;
                                $("#to").val(address);
                                $("#to_lat").val(latitude);
                                $("#to_long").val(longitude);
                                $("#to_lat_long").val('(' + latitude + ', ' + longitude + ')');
                                calculateDistance();
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    });
                }
            }

            $("#manual-select-address").click(function () {
                if ($("#iAreaId").val() === '') {

                } else {
                    if (fromManualMarker === null) {
                        addMarkerToMap('from', mapCenter, 'مقصد', map.icons.blue);
                    } else {
                        console.error('error1');
                    }
                    if (toManualMarker === null) {
                        addMarkerToMap('to', mapCenter, 'مقصد', map.icons.blue);
                    } else {
                        console.error('error2');
                    }
                }
            });
            $("#get-passenger-detail").click(function () {
                let phone = $('#vPhone').val();
                checkUserByMobile(phone);
            });
            $("#register-user-btn").click(function () {
                let data = {
                    vName: $("#userFirstName").val(),
                    vLastName: $("#userLastName").val(),
                    vPhone: $("#userPhone").val(),
                    vEmail: $("#userEmail").val(),
                };
                axios.post('/bibc/api/passengers/quickCreate', data)
                    .then(function (response) {
                        $("#user-dialog").modal('toggle');
                        checkUserByMobile($("#userPhone").val());
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            });

            function checkUserByMobile(phone) {
                if (phone.length > 0) {
                    let request = $.ajax({
                        type: "GET",
                        url: '/bibc/api/passengers/byPhone/' + phone,
                        success: function (passenger) {
                            $("#user_type").val('registered');
                            $('#vName').val(passenger.vName);
                            $('#vLastName').val(passenger.vLastName);
                            $('#vEmail').val(passenger.vEmail);
                            $('#iUserId').val(passenger.iUserId);
                            $('#vAddress').val(passenger.vAddress);
                            $('#vDescription').val(passenger.vDescription);
                            let bookingRequest = $.ajax({
                                type: "GET",
                                url: '/bibc/api/bookings/byUserId/' + passenger.iUserId,
                                success: function (booking) {
                                    $('#from').val(booking.vSourceAddresss);
                                    $('#to').val(booking.tDestAddress);
                                    $("#from_lat_long").val('(' + booking.vSourceLatitude + ',' + booking.vSourceLongitude + ')');
                                    $("#from_lat").val(booking.vSourceLatitude);
                                    $("#from_long").val(booking.vSourceLongitude);
                                    $("#to_lat_long").val('(' + booking.vDestLatitude + ',' + booking.vDestLongitude + ')');
                                    $("#to_lat").val(booking.vDestLatitude);
                                    $("#to_long").val(booking.vDestLongitude);
                                    $('#tTripComment').val(booking.tTripComment);
                                }
                            });
                        }
                    });
                    request.fail(function (jqXHR, textStatus) {
                        if (jqXHR.status === 404) {
                            $("#userFirstName").val('');
                            $("#userLastName").val('');
                            $("#userPhone").val('');
                            $("#userEmail").val('');
                            $("#user-dialog").modal('toggle');
                            $("#userPhone").val(phone);
                        }
                    });
                }
            }

            function calculateDistance() {
                let fromLat = $("#from_lat").val();
                let fromLng = $("#from_long").val();
                let fromCoordinates = fromLng + ',' + fromLat;
                let toLat = $("#to_lat").val();
                let toLng = $("#to_long").val();
                let toCoordinates = toLng + ',' + toLat;
                let vehicleTypeId = $("#iVehicleTypeId").val();
                let radius = $("#radius-id").val();
                if (fromLat === '' || fromLng === '' || toLat === '' || toLng === '') {

                } else {
                    // map.drawRoute({
                    //     start: [fromLat, fromLng],
                    //     end: [toLat, toLng],
                    //     mode: 'car',
                    //     draggable: true,
                    //     fit: true,
                    // });
                    $("#trip-fare-result").removeClass('d-none')
                    $("#tripResultsContainer").addClass('d-none');
                    $("#driverContainer").addClass('d-none');
                    $("#tripResultLoading").removeClass('d-none');
                    $("#fare-dialog").modal('toggle');
                    axios.post('/bibc/api/bookings/calculateDistanceAndFare', {
                        origin: {lat: fromLat, lng: fromLng},
                        destination: {lat: toLat, lng: toLng},
                        vehicleTypeId: vehicleTypeId
                    })
                        .then(function (response) {
                            $("#vDistance").val(response.data.tripDistance);
                            $("#vDuration").val(response.data.tripDuration);
                            $("#tripDistance").html(response.data.tripDistance + ' کیلومتر');
                            $("#tripDuration").html(response.data.tripDuration + ' دقیقه');
                            $("#totalFare").html(response.data.total_fare + ' تومان');
                            $("#tripResultLoading").addClass('d-none');
                            $("#tripResultsContainer").removeClass('d-none');
                            $("#driverContainer").removeClass('d-none');
                            location.hash = "#trip-fare-result";
                            axios.post('/bibc/api/drivers/driverListByLocation', {
                                lat: fromLat,
                                lng: fromLng,
                                radius: radius ? radius : 5,
                            })
                                .then(function (response) {
                                    let drivers = response.data;
                                    let driverMarkers = [];
                                    for (let i = 0; i < drivers.length; i++) {
                                        driverMarkers[i] = map.addMarker({
                                            name: 'driverMarker' + i,
                                            latlng: {
                                                lat: drivers[i].location.lat,
                                                lng: drivers[i].location.lng,
                                            },
                                            popup: {
                                                title: {
                                                    html: drivers[i].name,
                                                },
                                                description: {
                                                    html: drivers[i].address,
                                                },
                                                open: true
                                            },
                                            icon: map.icons.green,
                                            pan: false,
                                            draggable: false,
                                            history: false,
                                            on: {
                                                click: function () {
                                                    console.log('driver Click callback');
                                                },
                                                contextmenu: function () {
                                                    console.log('Contextmenu callback');
                                                }
                                            },
                                        });
                                    }
                                })
                                .catch(function (error) {
                                    console.log(error);
                                });
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            }
        });
    </script>
@endpush
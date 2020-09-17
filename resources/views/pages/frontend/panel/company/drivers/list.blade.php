@extends('pages.frontend.panel.index')

@section('panel_content')
    <div class="row">
        <div class="col-12">
            <a href="{{url('company.drivers.new')}}" class="btn btn-primary">ثبت راننده جدید</a>
        </div>
        <div class="col-12 m-1 h-100" data-map-center="{{$user['company']->area->mapCenter}}"
             data-map-zoom="{{$user['company']->area->mapZoom}}" style="height: 25rem!important;" id="map"></div>
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
    <script type="text/javascript">
        $(document).ready(function () {
            let mapCenter = $("#map").attr('data-map-center');
            if (mapCenter !== '') {
                mapCenter = JSON.parse(mapCenter);
            } else {
                mapCenter = {
                    lat: 33.22949814144951,
                    lng: 54.62402343750001,
                };
            }
            let mapZoom = $("#map").attr('data-map-zoom');
            if (mapZoom === '') {
                mapZoom = 5;
            }
            let mapIrApiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5In0.eyJhdWQiOiIxMDIxMyIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5IiwiaWF0IjoxNTk1NjYxMTc0LCJuYmYiOjE1OTU2NjExNzQsImV4cCI6MTU5ODI1MzE3NCwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.s0WWrZ2u-B4R9QaI5fxCozQWCP5QFQScUU2bhIt017_Bbpg0ZrnvA_4Ze1ebbSrVAdbGezuyf37ny3ux7Sg4ZO5rttA4EoF1VndtkVsR-br2G7p7FYMKb_e5adZwDrKos8n2mtS6Cytg3ebphaOSUy1GBNS-8rXSU3CsuUgC9AxXsjAIySS5APVoJaBQ9aj3tfO83rY0f1Is34D4emtC39bpw8ZGuj5U9yp4gQrbh7AgWqf217OFE3Od85n8Q8fOEvSN-XrFxE_Rr_dxTes8okfdsnUEiJ-Ha7LIO7lH4efHX1J6SiuwlOrfjasZRexNa1s3Jll3rS-MOP-oOHXKFQ';
            let selectedPoints = [];

            let map = new Mapp({
                element: '#map',
                presets: {
                    latlng: mapCenter,
                    zoom: mapZoom,
                },
                apiKey: mapIrApiKey
            });
            map.addLayers();

            ajax.get('company/drivers/online')
                .then(function (response) {
                    let drivers = response.data;
                    if (drivers.length > 0) {
                        for (let i = 0; i < drivers.length; i++) {
                            if (drivers[i].vLatitude !== '' && drivers[i].vLongitude !== '') {
                                addMarkerToMap(drivers[i]);
                            }
                        }
                    }
                })
                .catch(function (error) {
                    console.log(error);
                });

            function addMarkerToMap(driver) {
                var marker = map.addMarker({
                    name: 'driver-' + driver.iDriverId,
                    latlng: {
                        lat: driver.vLatitude,
                        lng: driver.vLongitude,
                    },
                    popup: {
                        title: {
                            html: driver.fullName,
                        },
                        description: {
                            html: '<p style="font-size: .7rem">' +
                                '<br><span>اخرین زمان آنلاین: ' + driver.jLastOnline + '</span>' +
                                '<br>میانگین امتیاز: ' + driver.vAvgRating + '' +
                                '<br>اعتبار حساب: ' + driver.credit + '' +
                                '<br><a href="">مشاهده اطلاعات</a>' +
                                '</p>',
                        },
                        open: false
                    },
                    icon: driver.isDriverOnline ? map.icons.red : map.icons.blue,
                    pan: false,
                    draggable: false,
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
            }

            map.map.on('drag', function () {
                $("#mapCenter").val(JSON.stringify(map.map.getCenter()));
                $("#mapZoom").val(map.map.getZoom());
            });
            map.map.on('zoom', function () {
                $("#mapCenter").val(JSON.stringify(map.map.getCenter()));
                $("#mapZoom").val(map.map.getZoom());
            });
        });
    </script>
@endpush
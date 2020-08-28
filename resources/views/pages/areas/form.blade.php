@extends('pages.dashboard')
@php
    if(is_null($area)){
        $action = url('areas.create');
    }else{
        $action = url('areas.update',['id'=>$area['aId']]);
    }
@endphp
@section('dashboard_content')
    <div class="row">
        <div class="col-12 col-md-5">
            <form method="post" action="{{$action}}" enctype="multipart/form-data">
                @if(!is_null($area))
                    <input type="hidden" name="iAdminId" value="{{$area['aId']}}"/>
                @endif
                <input type="hidden" name="mapCenter" id="mapCenter"
                       value="{{!is_null($area) ? $area->mapCenter : ''}}"/>
                <input type="hidden" name="mapZoom" id="mapZoom"
                       value="{{!is_null($area) ? $area->mapZoom : ''}}"/>
                <div class="form-group">
                    <label for="sAreaName">نام محدوده</label>
                    <input type="text" title="Enter Area Name."
                           class="form-control add-book-input1" name="sAreaName"
                           id="sAreaName" value="{{!is_null($area) ? $area->sAreaName : ''}}"
                           placeholder="نام محدوده" required style="">
                </div>
                <div class="form-group">
                    <label for="sAreaNamePersian">نام محدوده به فارسی</label>
                    <input type="text" title="Persian Area Name"
                           class="form-control first-name2" name="sAreaNamePersian"
                           id="sAreaNamePersian" value="{{!is_null($area) ? $area->sAreaNamePersian : ''}}"
                           placeholder="نام محدوده به فارسی" required>
                </div>
                <div class="form-group">
                    <label for="radius-id">منطقه ویژه</label>
                    <select class="form-control form-control-select" name='sSpecialArea' id="radius-id"
                            required>
                        <option value="No" {{!is_null($area) && $area->sSpecialArea=='No' ? 'selected' : ''}}>منطقه
                            مخصوص
                        </option>
                        <option value="Yes" {{!is_null($area) && $area->sSpecialArea=='Yes' ? 'selected' : ''}}>منطقه
                            عادی
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sPriority">اولویت</label>
                    <input type="text" class="form-control" name="sPriority" id="sPriority"
                           value="{{!is_null($area) ? $area->sPriority : ''}}" placeholder="اولویت" required>
                </div>
                <div class="form-group">
                    <label for="sActive">وضعیت</label>
                    <select class="form-control form-control-select" name='sActive' id="radius-id" required>
                        <option value="Yes" {{!is_null($area) && $area->sActive=='Yes' ? 'selected' : ''}}>فعال</option>
                        <option value="No" {{!is_null($area) && $area->sActive=='No' ? 'selected' : ''}}>غیر فعال
                        </option>
                    </select>
                </div>
                <textarea style="display:none;" class="form-control"
                          id="sFeatureCollection"
                          name="sFeatureCollection">{{!is_null($area) ? $area->sFeatureCollection : ''}}</textarea>
                <input type="submit" class="btn btn-primary col-12" name="submit" id="submit"
                       value="ذخیره اطلاعات">
            </form>
        </div>
        <div class="col-12 col-md-7">
            <div id="map" style="width: 100%;height: 100%"></div>
            <input type="button" class="btn btn-warning" value="رسم چندضلعی" id="addPolygon">
            <input type="button" class="btn btn-info" value="بروز رسانی نقشه" id="resetMap"></div>
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
            let polygonExists = false;
            let mapCenter = $("#mapCenter").val();
            if (mapCenter !== '') {
                mapCenter = JSON.parse(mapCenter);
            } else {
                mapCenter = {
                    lat: 33.22949814144951,
                    lng: 54.62402343750001,
                };
            }
            let mapZoom = $("#mapZoom").val();
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
            let areaPolygon = $("#sFeatureCollection").val();
            if (areaPolygon !== '') {
                areaPolygon = JSON.parse(areaPolygon);
                let areaPolygonPoints = areaPolygon.features[0].geometry.coordinates[0];
                for (let i = 0; i < areaPolygonPoints.length; i++) {
                    let latlng = {
                        lat: areaPolygonPoints[i][1],
                        lng: areaPolygonPoints[i][0],
                    }
                    selectedPoints.push(latlng);
                    addMarkerToMap('marker_' + (selectedPoints.length + 1), latlng);
                }
                if (selectedPoints.length >= 3) {
                    addPolygon(selectedPoints);
                }
                console.log(selectedPoints);
            }
            map.map.on('click', function (e) {
                selectedPoints.push(e.latlng);
                addMarkerToMap('marker_' + (selectedPoints.length + 1), e.latlng);
            });
            $("#addPolygon").click(function () {
                if (polygonExists) {
                    map.removePolygons({
                        group: map.groups.features.polygons,
                    });
                    map.removeMarkers({
                        group: map.groups.features.markers,
                    });
                    selectedPoints = [];
                    polygonExists = false;
                } else {
                    if (selectedPoints.length >= 3) {
                        addPolygon(selectedPoints);
                    }
                }
            });

            function addMarkerToMap(name, coordinates, icon) {
                var marker = map.addMarker({
                    name: name,
                    latlng: {
                        lat: coordinates.lat,
                        lng: coordinates.lng,
                    },
                    icon: icon ? icon : map.icons.red,
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

            function addPolygon(coordinates) {
                polygonExists = true;
                let polygon = map.addPolygon({
                    name: 'area',
                    coordinates: coordinates,
                });
                let geoJsonFeature = {
                    "type": "FeatureCollection",
                    "features":
                        [{
                            "type": "Feature",
                            "geometry": {
                                "type": "Polygon",
                                "coordinates": polygon.toGeoJSON().geometry.coordinates
                            }
                        }
                        ]
                };
                $('#sFeatureCollection').val(JSON.stringify(geoJsonFeature));
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

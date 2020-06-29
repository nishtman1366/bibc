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
                       value="{{!is_null($area) ? htmlentities($area->mapCenter) : ''}}"/>
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
            <div class="gmap-div gmap-div1" style="float:right;width:100%;">
                <div id="map" class="gmap3"></div>
                <input type="button" class="btn btn-warning" value="رسم چندضلعی"
                       onclick="polygon2()">
                <input type="button" class="btn btn-info" value="بروز رسانی نقشه"
                       onclick="ResetMap()"></div>
        </div>
    </div>
@endsection
@push('css')
    <link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet'/>
@endpush
@push('js')
    <script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
    <script type="text/javascript">
        L.cedarmaps.accessToken = '4a0a95307ce57f099d59085bf0b36c46668124b2'; // See the note below on how to get an access token

        // Getting maps info from a tileJSON source
        var dddd = 0;
        var sssss = 0;
        var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;
        var map = L.cedarmaps.map('map', tileJSONUrl, {
            scrollWheelZoom: true,
            fullscreenControl: true,
            center: {!! ((!is_null($area) && $area->mapCenter != '') ? $area->mapCenter . ',' : '{lat: 35.6899828, lng: 51.389644},') !!}
                zoom
        : {!! ((!is_null($area) && $area->mapZoom != '') ? $area->mapZoom . ',' : '15,') !!}
        })
        ;
        var markerGroup = L.layerGroup().addTo(map);
        var longs = [];
        if ($('#sFeatureCollection').val() == "") {

        } else {
            ss = JSON.parse($('#sFeatureCollection').val());

            for (i = 0; i <= ss.features[0].geometry.coordinates[0].length - 1; i++) {

                longs.push([ss.features[0].geometry.coordinates[0][i][1], ss.features[0].geometry.coordinates[0][i][0]],);
                //console.log(long.lat[0]);
            }
            //console.log(longs);

            polygon = L.polygon(longs, {color: 'black'}).addTo(map);
            dddd = 1;
            //mapinit();
        }
        map.on('click', function (e) {
            //marker = new L.marker(e.latlng).addTo(map);
            L.marker(e.latlng).addTo(markerGroup);
            longs.push([e.latlng.lat, e.latlng.lng],);
            sssss++;
        });

        function polygon2() {
            if (dddd == 0 && sssss > 0) {
                polygon = L.polygon(longs, {color: 'black'}).addTo(map);
                dddd = 1;
                var geojsonFeature = {
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
                $("#sFeatureCollection").val(JSON.stringify(geojsonFeature));
                //alert("ترسیم شد");
//console.log(geojsonFeature.features[0].geometry.coordinates[0][0][1]);
            } else {
                polygonremove();
                dddd = 0;
                markerGroup = L.layerGroup().addTo(map);
                //	alert("برای ترسیم چند نقطه را بر روی نقشه مشخص کنید");
            }

        }

        function polygonremove() {
            window.map.removeLayer(window.polygon);
            map.removeLayer(markerGroup);
            longs.length = 0;
            sssss = 0;

        }

        map.on('drag', function () {
            $("#mapCenter").val(JSON.stringify(map.getCenter()));
            $("#mapZoom").val(map.getZoom());
            //console.log(longs);
        });
        map.on('zoom', function () {
            $("#mapCenter").val(JSON.stringify(map.getCenter()));
            $("#mapZoom").val(map.getZoom());

            //console.log('#someButton was clicked');
        });


        function ResetMap() {
            map.remove();
            dddd = 1;
            map = L.cedarmaps.map('map', tileJSONUrl, {
                scrollWheelZoom: true,
{{--                {!! !is_null($area) ? 'center: ' . $area->mapCenter : 'center: {lat: 35.6899828, lng: 51.389644},' !!}--}}
                center: {lat: 35.6899828, lng: 51.389644},
                fullscreenControl: true
            });
            markerGroup = L.layerGroup().addTo(map);
            longs = [];
            if ($('#sFeatureCollection').val() == "") {

            } else {
                ss = JSON.parse($('#sFeatureCollection').val());
                for (i = 0; i <= ss.features[0].geometry.coordinates[0].length - 1; i++) {

                    longs.push([ss.features[0].geometry.coordinates[0][i][1], ss.features[0].geometry.coordinates[0][i][0]],);
                    //console.log(long.lat[0]);
                }
                //console.log(longs);
                polygon = L.polygon(longs, {color: 'black'}).addTo(map);
                //mapinit();
            }

            map.on('click', function (e) {
                //marker = new L.marker(e.latlng).addTo(map);
                L.marker(e.latlng).addTo(markerGroup);
                longs.push([e.latlng.lat, e.latlng.lng],);
            });
            map.on('drag', function () {
                $("#mapCenter").val(JSON.stringify(map.getCenter()));
                $("#mapZoom").val(map.getZoom());
                //console.log(longs);
            });
            map.on('zoom', function () {
                $("#mapCenter").val(JSON.stringify(map.getCenter()));
                $("#mapZoom").val(map.getZoom());

                //console.log('#someButton was clicked');
            });

        }

        // Apply listeners to refresh the GeoJson display on a given data layer.
        function bindDataLayerListeners(dataLayer) {
            dataLayer.addListener('addfeature', refreshGeoJsonFromData);
            dataLayer.addListener('removefeature', refreshGeoJsonFromData);
            dataLayer.addListener('setgeometry', refreshGeoJsonFromData);
        }

        function refreshGeoJsonFromData() {
            map.data.toGeoJson(function (geoJson) {
                console.log(geoJson);
                $("#sFeatureCollection").val(JSON.stringify(geoJson, null, 2));
            });
        }
    </script>
@endpush

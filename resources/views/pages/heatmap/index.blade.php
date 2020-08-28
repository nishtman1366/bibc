@extends('pages.dashboard',['active'=>'heatmap'])

@section('dashboard_content')
    <div class="row" style="height: 100vh">
        <div class="col-12 h-100">
            <div id="map" style="width: 100%;height: 100%"></div>
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
    <script type="text/javascript" src="{{assets('vendor/leaflet/leaflet-heat.js')}}"></script>
@endpush
@push('js')
    <script>
        $(document).ready(function () {
            let mapIrApiKey = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5In0.eyJhdWQiOiIxMDIxMyIsImp0aSI6ImVlMTQ4ZGYxYWYwNDUwMDY5YjBlMzgwOGFlYTMwMjUxNWI0ZmFmNGU3N2Y0Nzc3MmY2MGFlZDJjY2JkNWE4ZmZiMzE2MTgxNjg4NGZjNjM5IiwiaWF0IjoxNTk1NjYxMTc0LCJuYmYiOjE1OTU2NjExNzQsImV4cCI6MTU5ODI1MzE3NCwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.s0WWrZ2u-B4R9QaI5fxCozQWCP5QFQScUU2bhIt017_Bbpg0ZrnvA_4Ze1ebbSrVAdbGezuyf37ny3ux7Sg4ZO5rttA4EoF1VndtkVsR-br2G7p7FYMKb_e5adZwDrKos8n2mtS6Cytg3ebphaOSUy1GBNS-8rXSU3CsuUgC9AxXsjAIySS5APVoJaBQ9aj3tfO83rY0f1Is34D4emtC39bpw8ZGuj5U9yp4gQrbh7AgWqf217OFE3Od85n8Q8fOEvSN-XrFxE_Rr_dxTes8okfdsnUEiJ-Ha7LIO7lH4efHX1J6SiuwlOrfjasZRexNa1s3Jll3rS-MOP-oOHXKFQ';
            let map = new Mapp({
                element: '#map',
                presets: {
                    latlng: {
                        lat: 32,
                        lng: 52,
                    },
                    zoom: 6,
                },
                apiKey: mapIrApiKey
            });
            map.addVectorLayers();

            let locations = [
                [36.84494316098485, 54.332982636988156], [36.84494316098485, 54.332982636988156],
                [36.84497911431424, 54.333091266453266], [36.84494316098485, 54.332982636988156],
                [36.84403171160764, 54.33215618133545], [36.84275775709367, 54.3329306691885],
                [36.843929216473555, 54.33266345411539], [36.844679413283004, 54.33287367224693],
                [36.843790767330844, 54.3328357860446], [36.84009896604792, 54.435453414917],
                [36.83882816597645, 54.43562507629395], [36.83669866987094, 54.43742752075196],
                [34.801541798959036, 48.492547273635864], [36.83772907861591, 54.44583892822266],
                [34.80150353133592, 48.492549955844886], [34.8011, 48.49435], [36.830949861339, 54.452979639172554],
                [36.83194304631518, 54.453819170594215]
            ];
            // Add a geojson point source.
            // Heatmap layers also work with a vector tile source.
            // map.map.addSource('earthquakes', {
            //     'type': 'geojson',
            //     'data':
            //         'https://docs.mapbox.com/mapbox-gl-js/assets/earthquakes.geojson'
            // });
            map.addLayer(
                {
                    'id': 'earthquakes-heat',
                    'type': 'heatmap',
                    'source': 'earthquakes',
                    'maxzoom': 9,
                    'paint': {
                        // Increase the heatmap weight based on frequency and property magnitude
                        'heatmap-weight': [
                            'interpolate',
                            ['linear'],
                            ['get', 'mag'],
                            0,
                            0,
                            6,
                            1
                        ],
// Increase the heatmap color weight weight by zoom level
// heatmap-intensity is a multiplier on top of heatmap-weight
                        'heatmap-intensity': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            0,
                            1,
                            9,
                            3
                        ],
// Color ramp for heatmap.  Domain is 0 (low) to 1 (high).
// Begin color ramp at 0-stop with a 0-transparancy color
// to create a blur-like effect.
                        'heatmap-color': [
                            'interpolate',
                            ['linear'],
                            ['heatmap-density'],
                            0,
                            'rgba(33,102,172,0)',
                            0.2,
                            'rgb(103,169,207)',
                            0.4,
                            'rgb(209,229,240)',
                            0.6,
                            'rgb(253,219,199)',
                            0.8,
                            'rgb(239,138,98)',
                            1,
                            'rgb(178,24,43)'
                        ],
// Adjust the heatmap radius by zoom level
                        'heatmap-radius': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            0,
                            2,
                            9,
                            20
                        ],
// Transition from heatmap to circle layer by zoom level
                        'heatmap-opacity': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            7,
                            1,
                            9,
                            0
                        ]
                    }
                },
                'waterway-label'
            );

            map.addLayer(
                {
                    'id': 'earthquakes-point',
                    'type': 'circle',
                    'source': 'earthquakes',
                    'minzoom': 7,
                    'paint': {
// Size circle radius by earthquake magnitude and zoom level
                        'circle-radius': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            7,
                            ['interpolate', ['linear'], ['get', 'mag'], 1, 1, 6, 4],
                            16,
                            ['interpolate', ['linear'], ['get', 'mag'], 1, 5, 6, 50]
                        ],
// Color circle by earthquake magnitude
                        'circle-color': [
                            'interpolate',
                            ['linear'],
                            ['get', 'mag'],
                            1,
                            'rgba(33,102,172,0)',
                            2,
                            'rgb(103,169,207)',
                            3,
                            'rgb(209,229,240)',
                            4,
                            'rgb(253,219,199)',
                            5,
                            'rgb(239,138,98)',
                            6,
                            'rgb(178,24,43)'
                        ],
                        'circle-stroke-color': 'white',
                        'circle-stroke-width': 1,
// Transition from heatmap to circle layer by zoom level
                        'circle-opacity': [
                            'interpolate',
                            ['linear'],
                            ['zoom'],
                            7,
                            0,
                            8,
                            1
                        ]
                    }
                },
                'waterway-label'
            );
        });
    </script>
@endpush
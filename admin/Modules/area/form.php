<?php

$generalobjAdmin->check_member_login();

if (checkPermission('AREA') == false)
    die('you dont`t have permission...');


$script = "Area";

$error = '';

$aId = '';
$sAreaName = '';
$sAreaNamePersian = '';
$sPriority = '';
$sFeatureCollection = '';
$sActive = '';
$mapCenter = '';
$mapZoom = '';

$action = 'add';


if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $aId = $_GET['id'];
    $res = $obj->MySQLSelect("SELECT * FROM savar_area WHERE  `aId` = '$aId'");

    if (count($res) > 0) {
        $aId = $res[0]['aId'];
        $sAreaName = $res[0]['sAreaName'];
        $sAreaNamePersian = $res[0]['sAreaNamePersian'];
        $sPriority = $res[0]['sPriority'];
        $sFeatureCollection = $res[0]['sFeatureCollection'];
        $sActive = $res[0]['sActive'];
        $mapCenter = $res[0]['mapCenter'];
        $mapZoom = $res[0]['mapZoom'];


        $action = 'edit';
    }
}

if (isset($_POST['action'])) {

    #echo "<pre>";print_r($_POST);die();
    $sAreaName = GetPost('sAreaName');
    $sAreaNamePersian = GetPost('sAreaNamePersian');
    $sSpecialArea = GetPost('sSpecialArea');
    $sPriority = GetPost('sPriority');
    $sFeatureCollection = stripcslashes(GetPost('sFeatureCollection'));
    $aId = GetPost('aId');
    $mapCenter = stripcslashes(GetPost('mapCenter'));
    $mapZoom = GetPost('mapZoom');
    $sActive = GetPost('sActive');

    // $sAreaName			= "Esfahan";
    // $sAreaNamePersian	= "اصفهان";
    // $sSpecialArea		= "No";
    // $sPriority			= "5";
    // $sFeatureCollection = stripcslashes(file_get_contents("area_new_str.txt"));
    // $aId			    = GetPost('aId');
    // $mapCenter			= "{\"lat\":32.61343575281241,\"lng\":51.68664923589722}";
    // $mapZoom			= 12;
    // $sActive 			= "Yes";


    $fCollectionArray = json_decode($sFeatureCollection, true);

    if ($sAreaName == '' || $sAreaNamePersian == '')
        $error .= 'لطفا نام منطقه را بدرستی وارد نمایید<br>';


    else if ($_POST['action'] == 'add' && count($obj->MySQLSelect("SELECT aId FROM savar_area WHERE  `sAreaName` = '$sAreaName'")) > 0)
        $error .= 'نام انتخابی تکراریست<br>';

    if ($sFeatureCollection == '')
        $error .= 'لطفا یک منطقه را انتخاب نمایید<br>';
    else if (isset($fCollectionArray['features']) == false || count($fCollectionArray['features']) == 0)
        $error .= 'خطا در انتخاب منطقه<br>';
    else if (isset($fCollectionArray['features'][0]['geometry']['coordinates'][0]) == false
        || count($fCollectionArray['features'][0]['geometry']['coordinates'][0]) == 0)
        $error .= 'خطا در تعداد مناطق<br>';


    if ($sPriority == '')
        $sPriority = 5;


    //$sFeatureCollection = str_replace('\r\n',"\r\n",$sFeatureCollection);
    //echo $sFeatureCollection;


    if ($error == '') {
        /*
         * دریافت و تحلیل اطلاعات نقشه
         */
        $polyText = 'POLYGON((';
        $points = $fCollectionArray['features'][0]['geometry']['coordinates'][0];
        $pointLen = count($points);
        // ezafe kardane noghte ebteda be enteha
        if ($points[0][0] != $points[$pointLen - 1][0] && $points[0][1] != $points[$pointLen - 1][1]) {
            $points[] = $points[0];
            $pointLen++;
        }
        for ($i = 0; $i < $pointLen; $i++) {
            $polyText .= $points[$i][0] . ' ' . $points[$i][1] . ',';
        }
        if (substr($polyText, -1, 1) == ',') {
            $polyText = substr($polyText, 0, -1);
        }
        $polyText .= '))';

        /*
         * ثبت اطلاعات در پایگاه داده
         */
        if ($_POST['action'] == 'add') {
            $sql = "INSERT INTO `savar_area` (`sAreaName`, `sAreaNamePersian`, `sSpecialArea`, `sPriority`, `sPolygonArea`, `sFeatureCollection`, `sActive`, `mapCenter`, `mapZoom`) VALUES ('$sAreaName', '$sAreaNamePersian', '$sSpecialArea', '$sPriority', PolyFromText('$polyText'), '$sFeatureCollection', '$sActive', '$mapCenter', '$mapZoom');";
            $res = $obj->sql_query($sql);
            redirect()->adminUrl('area')->setMessage('ثبت اطلاعات ناحیه با موفقیت انجام شد.');
        } else if ($_POST['action'] == 'edit') {
            $sql = "UPDATE `savar_area` SET `sAreaName` = '$sAreaName', `sAreaNamePersian` = '$sAreaNamePersian', `sSpecialArea` = '$sSpecialArea', `sPriority` = '$sPriority', `sFeatureCollection` = '$sFeatureCollection', `sPolygonArea` = PolyFromText('$polyText'), `sActive` = '$sActive', `mapCenter` = '$mapCenter', `mapZoom` = '$mapZoom' WHERE `savar_area`.`aId` = $aId;";
            $res = $obj->sql_query($sql);
            redirect()->adminUrl('area')->setMessage('ویرایش اطلاعات ناحیه با موفقیت انجام شد.');
        }
    }
}

function GetPost($key)
{
    return isset($_POST[$key]) ? $_POST[$key] : '';
}

?><!DOCTYPE html>
<script src='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.js'></script>
<link href='https://api.cedarmaps.com/cedarmaps.js/v1.8.0/cedarmaps.css' rel='stylesheet'/>
<div class="row">
    <div class="col-lg-12">
        <h2 class="text-right">محدوده</h2>
        <a href="<?php echo adminUrl('area'); ?>" class="btn btn-primary">بازگشت به لیست</a>
    </div>
</div>
<hr/>

<div class="card">
    <div class="card-body">
        <?php if ($error !== '') { ?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                <?php echo $error ?>
            </div><br/>
        <?php } ?>
        <?php if ($success == 0 && $var_msg != "") { ?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                <?php echo $var_msg; ?>
            </div><br/>
        <?php } ?>
        <div class="row">
            <div class="col-lg-5">
                <form name="add_booking_form" id="add_booking_form" method="post"
                      action="<?php echo adminUrl('area', ['op' => 'form']); ?>"
                      enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action ?>"/>
                    <input type="hidden" name="aId" value="<?php echo $aId ?>"/>
                    <input type="hidden" name="mapCenter" id="mapCenter"
                           value="<?php echo htmlentities($mapCenter) ?>"/>
                    <input type="hidden" name="mapZoom" id="mapZoom" value="<?php echo $mapZoom ?>"/>
                    <div class="form-group">
                        <label for="sAreaName">نام محدوده</label>
                        <input type="text" title="Enter Area Name."
                               class="form-control add-book-input1" name="sAreaName"
                               id="sAreaName" value="<?php echo $sAreaName; ?>"
                               placeholder="نام محدوده" required style="">
                    </div>
                    <div class="form-group">
                        <label for="sAreaNamePersian">نام محدوده به فارسی</label>
                        <input type="text" title="Persian Area Name"
                               class="form-control first-name2" name="sAreaNamePersian"
                               id="sAreaNamePersian" value="<?php echo $sAreaNamePersian; ?>"
                               placeholder="نام محدوده به فارسی" required>
                    </div>
                    <div class="form-group">
                        <label for="radius-id">منطقه ویژه</label>
                        <?php
                        $select1 = ($sSpecialArea == 'Yes' ? 'selected' : '');
                        $select2 = ($sSpecialArea == 'No' ? 'selected' : '');
                        ?>
                        <select class="form-control form-control-select" name='sSpecialArea' id="radius-id"
                                required>
                            <option value="No" <?php echo $select1 ?>>Is Not Special Area</option>
                            <option value="Yes" <?php echo $select2 ?>>Special Area</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sPriority">اولویت</label>
                        <input type="text" class="form-control" name="sPriority" id="sPriority"
                               value="<?php echo $sPriority; ?>" placeholder="اولویت" required>
                    </div>
                    <div class="form-group">
                        <label for="sActive">وضعیت</label>
                        <?php
                        $select1 = ($sActive == 'Yes' ? 'selected' : '');
                        $select2 = ($sActive == 'No' ? 'selected' : '');
                        ?>
                        <select class="form-control form-control-select" name='sActive' id="radius-id" required>
                            <option value="Yes" <?php echo $select1 ?>>فعال</option>
                            <option value="No" <?php echo $select2 ?>>غیر فعال</option>
                        </select>
                    </div>
                    <textarea style="display:none;" class="form-control"
                              id="sFeatureCollection"
                              name="sFeatureCollection"><?php echo $sFeatureCollection; ?></textarea>
                    <input type="submit" class="btn btn-primary" name="submit"
                           id="submit" value="ذخیره اطلاعات">
                    <input type="reset" class="btn btn-secondary" name="reset"
                           id="reset12" value="پاک سازی لیست">
                </form>
            </div>
            <div class="col-lg-7">
                <div class="gmap-div gmap-div1" style="float:right;width:100%;">
                    <div id="map" class="gmap3"></div>
                    <input type="button" class="btn btn-warning" value="رسم چندضلعی"
                           onclick="polygon2()">
                    <input type="button" class="btn btn-info" value="بروز رسانی نقشه"
                           onclick="ResetMap()"></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">

            </div>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
<div class="clear"></div>

<link rel="stylesheet" type="text/css" media="screen"
      href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/bootstrap-datetimepicker.min.js"></script>
<!---->
<!--<script async defer-->
<!--        src1="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?key=--><?php //echo $GOOGLE_SEVER_API_KEY_WEB ?><!--&callback=initMap"></script>-->
<!---->
<!--<script src1="http://freegoogle.ir/https://maps.googleapis.ir/maps/api/js?key=--><?php //echo $GOOGLE_SEVER_API_KEY_WEB ?><!--&v=3.exp"></script>-->
<!---->
<!--<script src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language=en&key=--><?php //echo $GOOGLE_SEVER_API_KEY_WEB ?><!--"></script>-->
<!---->
<!--<script src="http://freegoogle.ir/https://maps.googleapis.com/maps/api/js?v=3.exp&key=--><?php //echo $GOOGLE_SEVER_API_KEY_WEB ?><!--&sensor=false"></script>-->


<script type="text/javascript">
    L.cedarmaps.accessToken = '4a0a95307ce57f099d59085bf0b36c46668124b2'; // See the note below on how to get an access token

    // Getting maps info from a tileJSON source
    var dddd = 0;
    var sssss = 0;
    var tileJSONUrl = 'https://api.cedarmaps.com/v1/tiles/cedarmaps.streets.json?access_token=' + L.cedarmaps.accessToken;
    var map = L.cedarmaps.map('map', tileJSONUrl, {
        scrollWheelZoom: true,
        fullscreenControl: true,
        center: <?php echo (($mapCenter!='') ? $mapCenter.',' : '{lat: 35.6899828, lng: 51.389644},'); ?>
        zoom: <?php echo (($mapZoom!='') ? $mapZoom.',' : '15,'); ?>
    });
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
            <?php if ($mapCenter != '') {
                echo 'center: ' . $mapCenter . ',';
            } else {
                echo 'center: {lat: 35.6899828, lng: 51.389644},';
                //echo 'center: {lat: 51.505, lng: -0.09},';
            }
            if ($mapZoom != '') {
                echo 'zoom:' . $mapZoom . ',';
            } else {
                echo 'zoom: 15,';
            } ?>
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


</script>


<script>
    //function mapinit() {
    //    // Initialise the map.
    //    map = new google.maps.Map(document.getElementById('map-canvas'), {
    //        center: {lat: 35.6899828, lng: 51.389644},
    //        zoom: 12,
    //        fullscreenControl: true
    //    });
    //    map.data.setControls(['Polygon']); //'Point', 'LineString',
    //    map.data.setStyle({
    //        editable: true,
    //        draggable: true
    //    });
    //
    //    <?php //if($mapCenter != '') : ?>
    //    map.setCenter(JSON.parse('<?php //echo $mapCenter ?>//'));
    //    <?php //endif; ?>
    <!--    --><?php //if($mapZoom != '') : ?>
    //    map.setZoom(<?php //echo $mapZoom ?>//);
    //    <?php //endif; ?>
    //
    //
    //    map.addListener('center_changed', function () {
    //        $("#mapCenter").val(JSON.stringify(map.getCenter()));
    //        $("#mapZoom").val(map.getZoom());
    //    });
    //
    //
    //    bindDataLayerListeners(map.data);
    //
    //    if (typeof window.mapReset == "undefined" || window.mapReset != true) {
    //        window.mapReset = false;
    //        <?php
    //        if ($sFeatureCollection != '')
    //            echo "map.data.addGeoJson(JSON.parse($('#sFeatureCollection').val()));\r\n";
    //        ?>
    //    }
    //}
    //
    //google.maps.event.addDomListener(window, 'load', mapinit);

    /*{
        window.mapReset = true;
        mapinit();
    }*/

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

<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>


</script>
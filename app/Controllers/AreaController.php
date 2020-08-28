<?php


namespace App\Controllers;


use App\Controllers\Controller as BaseController;
use App\Models\Area;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class AreaController extends BaseController
{
    public function index()
    {
        $areas = Area::orderBy('sAreaNamePersian', 'ASC')->get();
        return view('pages.areas.list', compact('areas'));
    }

    public function form(int $id = null)
    {
        $area = null;
        if (!is_null($id)) {
            $area = Area::find($id);
        }
        return view('pages.areas.form', compact('area'));
    }

    public function create()
    {
        $sAreaName = input('sAreaName');
        $sAreaNamePersian = input('sAreaNamePersian');
        $sSpecialArea = input('sSpecialArea');
        $sPriority = input('sPriority');
        $sFeatureCollection = stripcslashes(input('sFeatureCollection'));
        $aId = input('aId');
        $mapCenter = stripcslashes(input('mapCenter'));
        $mapZoom = input('mapZoom');
        $sActive = input('sActive');

        $fCollectionArray = json_decode($sFeatureCollection, true);

        if ($sPriority == '') $sPriority = 5;
        /*
         * دریافت و تحلیل اطلاعات نقشه
         */
        $polyText = 'POLYGON((';

        $points = $fCollectionArray['features'][0]['geometry']['coordinates'][0];
        $pointLen = count($points);
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

        $result = Manager::statement("INSERT INTO `savar_area` (`sAreaName`, `sAreaNamePersian`, `sSpecialArea`, `sPriority`, `sPolygonArea`, `sFeatureCollection`, `sActive`, `mapCenter`, `mapZoom`) VALUES ('$sAreaName', '$sAreaNamePersian', '$sSpecialArea', '$sPriority', PolyFromText('$polyText'), '$sFeatureCollection', '$sActive', '$mapCenter', '$mapZoom');");
        if ($result) redirect(url('areas'));
        else dd('error');


        return redirect(url('areas'));
    }

    public function update(int $id)
    {
        $sAreaName = input('sAreaName');
        $sAreaNamePersian = input('sAreaNamePersian');
        $sSpecialArea = input('sSpecialArea');
        $sPriority = input('sPriority');
        $sFeatureCollection = stripcslashes(input('sFeatureCollection'));
        $aId = input('aId');
        $mapCenter = stripcslashes(input('mapCenter'));
        $mapZoom = input('mapZoom');
        $sActive = input('sActive');

        $fCollectionArray = json_decode($sFeatureCollection, true);

        if ($sPriority == '') $sPriority = 5;
        /*
         * دریافت و تحلیل اطلاعات نقشه
         */
        $polyText = 'POLYGON((';
        $points = $fCollectionArray['features'][0]['geometry']['coordinates'][0];
        $pointLen = count($points);
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
        $result = Manager::statement("UPDATE `savar_area` SET `sAreaName` = '$sAreaName', `sAreaNamePersian` = '$sAreaNamePersian', `sSpecialArea` = '$sSpecialArea', `sPriority` = '$sPriority', `sFeatureCollection` = '$sFeatureCollection', `sPolygonArea` = PolyFromText('$polyText'), `sActive` = '$sActive', `mapCenter` = '$mapCenter', `mapZoom` = '$mapZoom' WHERE `savar_area`.`aId` = $id;");
        if ($result) redirect(url('areas'));
        else dd('error');
        return redirect(url('areas'));
    }


    public function delete(int $id)
    {
        if (!is_null($id)) {
            Area::destroy($id);
        }
        return redirect(url('areas'));
    }
}
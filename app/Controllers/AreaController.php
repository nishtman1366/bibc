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
        //TODO validation for create company
        $sAreaName = input('sAreaName');
        $sAreaNamePersian = input('sAreaNamePersian');
        $sSpecialArea = input('sSpecialArea');
        $sPriority = input('sPriority');
        $sFeatureCollection = stripcslashes(input('sFeatureCollection'));
        $aId = input('aId');
        $mapCenter = stripcslashes(input('mapCenter'));
        $mapZoom = input('mapZoom');
        $sActive = input('sActive');

        $error = '';
        $fCollectionArray = json_decode($sFeatureCollection, true);
        if ($sAreaName == '' || $sAreaNamePersian == '')
            $error .= 'لطفا نام منطقه را بدرستی وارد نمایید<br>';
//        else if (count($obj->MySQLSelect("SELECT aId FROM savar_area WHERE  `sAreaName` = '$sAreaName'")) > 0)
//            $error .= 'نام انتخابی تکراریست<br>';
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

            $result = Manager::statement("INSERT INTO `savar_area` (`sAreaName`, `sAreaNamePersian`, `sSpecialArea`, `sPriority`, `sPolygonArea`, `sFeatureCollection`, `sActive`, `mapCenter`, `mapZoom`) VALUES ('$sAreaName', '$sAreaNamePersian', '$sSpecialArea', '$sPriority', PolyFromText('$polyText'), '$sFeatureCollection', '$sActive', '$mapCenter', '$mapZoom');");
            if ($result) redirect(url('areas'));
            else dd('error');
        } else {
            dd($error);
        }
        return redirect(url('areas'));
    }

    public function update(int $id)
    {
        //TODO validation for edit company
        $sAreaName = input('sAreaName');
        $sAreaNamePersian = input('sAreaNamePersian');
        $sSpecialArea = input('sSpecialArea');
        $sPriority = input('sPriority');
        $sFeatureCollection = stripcslashes(input('sFeatureCollection'));
        $aId = input('aId');
        $mapCenter = stripcslashes(input('mapCenter'));
        $mapZoom = input('mapZoom');
        $sActive = input('sActive');

        $error = '';
        $fCollectionArray = json_decode($sFeatureCollection, true);
        if ($sAreaName == '' || $sAreaNamePersian == '')
            $error .= 'لطفا نام منطقه را بدرستی وارد نمایید<br>';
//        else if (count($obj->MySQLSelect("SELECT aId FROM savar_area WHERE  `sAreaName` = '$sAreaName'")) > 0)
//            $error .= 'نام انتخابی تکراریست<br>';
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
            $result = Manager::statement("UPDATE `savar_area` SET `sAreaName` = '$sAreaName', `sAreaNamePersian` = '$sAreaNamePersian', `sSpecialArea` = '$sSpecialArea', `sPriority` = '$sPriority', `sFeatureCollection` = '$sFeatureCollection', `sPolygonArea` = PolyFromText('$polyText'), `sActive` = '$sActive', `mapCenter` = '$mapCenter', `mapZoom` = '$mapZoom' WHERE `savar_area`.`aId` = $id;");
            if ($result) redirect(url('areas'));
            else dd('error');
        } else {
            dd($error);
        }
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
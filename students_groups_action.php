<?php

//$ISDEBUG = true;
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	
	require_once(TPATH_CLASS . "/Imagecrop.class.php");
	$thumb = new thumbnail();
	$generalobj->check_member_login();
	$sql = "select * from country where eStatus = 'Active'";
	$db_country = $obj->MySQLSelect($sql);
//error_reporting(E_ALL & ~E_NOTICE);	

// ADDED BY SEYYED AMIR
$iCompanyId = $_SESSION['sess_iCompanyId'];
$sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
$comp_childs = $obj->MySQLSelect($sql);
$comp_list = $iCompanyId;

foreach($comp_childs as $comp)
{
    $comp_list .= ',' . $comp['iCompanyId'];
}
////////////////////////


    if ($_SESSION['sess_user'] == 'company') {
		$iCompanyId = $_SESSION['sess_iCompanyId'];
		$sql = "select * from register_driver where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
		$db_drvr = $obj->MySQLSelect($sql);
	}


	if($_REQUEST['id'] != '' && $_SESSION['sess_iCompanyId'] != ''){
		
        $iAreaId = 0;
        $sql = "SELECT * FROM `company` where `iCompanyId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
		$thiscomp = $obj->MySQLSelect($sql);
        
        if(count($thiscomp) > 0)
            $iAreaId = $thiscomp[0]['iAreaId'];
	}

    $var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
	$action = ($id != '') ? 'Edit' : 'Add';
	$action_show = ($id != '') ? $langage_lbl['BTN_EDIT_STUDENT'] : $langage_lbl['BTN_EDIT_STUDENT'];
	$iCompanyId = $_SESSION['sess_iUserId'];
	$tbl_name = 'register_student_groups';
	$script = 'Student';
	
	$sql = "select * from language_master where eStatus = 'Active' and eDefault='Yes' ORDER BY vTitle ASC";
	$db_lang = $obj->MySQLSelect($sql);
	
	$sql = "select * from company where eStatus != 'Deleted'";
	$db_company = $obj->MySQLSelect($sql);
	

	if (isset($_POST['submit'])) {

		$iCompanyId = $_SESSION['sess_iUserId'];

        
        $iGroupId = $_POST['iGroupId'];
        
        
        
        $data = array(
            //'iDriverId'     => $_POST['iDriverId'],
            'vGroupName'    => $_POST['vGroupName'],
            'vIseatNumber'  => $_POST['vIseatNumber'],
            'vStartLat'     => $_POST['vStartLat'],
            'vStartLon'     => $_POST['vStartLon'],
        );
        
        // For InsertNew
        $query = 'INSERT ';
        $where = '';
        $insertExtra = " , `eTripStatus`='Active' , `eStatus`='NEW' ";
        
        // For Update exists item
        if($iGroupId != '' && $iGroupId != '0')
        {
            $query = 'UPDATE ';
            $where = " WHERE iGroupId = $iGroupId ";
            $insertExtra = '';
        } 
        /////////////End for
        
        $query .=  ' `register_student_groups` SET ';
        
        $isFirst = true;
        foreach($data as $key => $val)
        {
            if($key == '' || $val == '')
                continue;
            if($isFirst)
            {
                $isFirst = false;
                $query .= " `$key` = '$val' ";
            }
            else
            {
                $query .= " , `$key` = '$val' ";
            }
            
        }
        
        $query .= $insertExtra . $where;
        
        //die($query);
        
        $insert_log = $obj->sql_query($query);
        
        
            header("Location:students.php?id=" . $id . '&success=1&var_msg='.$var_msg);
                        exit;
	
        }
    
	// for Edit
	
	if ($action == 'Edit' && $iAreaId != 0) {
        $sql = "SELECT stg.*, CONCAT(rd.vName,' ',rd.vLastName) as driverName FROM {$tbl_name} as stg
		 LEFT JOIN register_driver as rd on stg.iDriverId=rd.iDriverId
		 WHERE stg.iGroupId = $id  ORDER BY `stg`.`iGroupId` DESC";
		$db_data = $obj->MySQLSelect($sql);
		#echo "<pre>";print_R($db_data);echo "</pre>";die();
		
		if (count($db_data) > 0) {
			foreach ($db_data as $key => $value) {
				$vGroupName     = $value['vGroupName'];
				$vIseatNumber   = $value['vIseatNumber'];
				$iDriverId      = $value['iDriverId'];
				$vStartLat     = $value['vStartLat'];
				$vStartLon      = $value['vStartLon'];
                $tTripGoingData = $value['tTripGoingData'];
                #echo "<pre>";print_R($value);echo "</pre>";die();
                
			}
		}
	}
	
$tTripGoingData = json_decode($tTripGoingData,true);

//array_pop($tTripGoingData);
//array_pop($tTripGoingData);
//array_pop($tTripGoingData);
//array_pop($tTripGoingData);
//array_pop($tTripGoingData);


unset($tTripGoingData['driverStartTime']);
unset($tTripGoingData['startLatLon']);
unset($tTripGoingData['endLatLon']);
unset($tTripGoingData['waypointsLatLon']);
unset($tTripGoingData['studentWaypoints']);


	
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo $SITE_NAME?> |  <?php echo  $action_show; ?></title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		<!-- End: Default Top Script and css-->
        <link rel="stylesheet" href="assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
	</head>
	<body>
		<!-- home page -->
		<div id="main-uber-page">
			<!-- Left Menu -->
			<?php include_once("top/left_menu.php");?>
			<!-- End: Left Menu-->
			<!-- Top Menu -->
			<?php include_once("top/header_topbar.php");?>
			<!-- End: Top Menu-->
			<!-- contact page-->
			<div class="page-contant ">
				<div class="page-contant-inner page-trip-detail">
					<h2 class="header-page trip-detail driver-detail1"><?php echo $langage_lbl['BTN_ADD_STUDENT_GROUP'];?>
					<a href="students_groups.php">
						<img src="assets/img/arrow-white.png" alt=""><?php echo $langage_lbl['LBL_BACK_LIST_TEXT'];?>
					</a></h2>
					<!-- login in page -->
					<div class="driver-action-page">
						<?php if ($success == 1) {?>
							<div class="alert alert-success alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								<?php echo $langage_lbl['LBL_Record_Updated_successfully.'];?>
							</div>
							<?}else if($success == 2){ ?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								"Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
							</div>
                        
                        <?}else if($success == 3){ ?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
								Email or Phone is exists.
							</div>
							<?php 
							}
						?>
						<form id="frm1" method="post" onSubmit="return editPro('login')" enctype="multipart/form-data">
							<input  type="hidden" class="edit" name="action" value="login">
							<input  type="hidden" class="edit" name="vLang" value="<?php echo $db_lang[0]['vCode']?>">
							<input  type="hidden" class="edit" name="iGroupId" value="<?php echo $id?>">
							
							<div class="driver-action-page-right validation-form">
								<div class="row">
									
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_LAST_NAME_TXT'];?>	</label>
											<input type="text" class="driver-action-page-input" name="vSLastName"  id="vSLastName" value="<?php echo  $vGroupName; ?>" placeholder="<?php echo $langage_lbl['LBL_LAST_NAME_TXT'];?>" pattern="[\D]+"  required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
                                    <div class="col-md-6">
										<span>
											<label>vStartLat</label>
											<input type="text" class="driver-action-page-input" name="vStartLat"  id="vStartLat" value="<?php echo  $vStartLat; ?>" placeholder="vStartLon">
										</span> 
									</div>
									<div class="col-md-6">
										<span>
											<label>vStartLon</label>	
											<input type="text" class="driver-action-page-input" name="vStartLon"  id="vStartLon" value="<?php echo  $vStartLon; ?>" placeholder="vStartLon">
										</span> 
									</div>
                                    
                                    
                                    <div class="col-md-6">
										<span>
											<label>iSeatNumber</label>
											<input type="text" class="driver-action-page-input" name="iSeatNumber"  id="iSeatNumber" value="<?php echo  $vIseatNumber; ?>" placeholder="Seat Number" >
										</span> 
									</div>
									
                                    
                                    
									<p>
										<input type="submit" class="save-but" name="submit" id="submit" value="<?php echo  $action_show; ?> ">
										
									</p>
									<div style="clear:both;"></div>
                                    
                                    <div class="col-md-12" style="1px solid #dfdfdf">
              			               <div class="trip-detail-map"><div id="map-canvas" class="gmap3" style="width:100%;height:300px;margin-bottom:10px;"></div></div>
                                    </div>
								</div>  
							</div>                      
						</form>
					</div>
					<div style="clear:both;"></div>
                    
				</div>
			</div>
			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
			<!-- End:contact page-->
			<div style="clear:both;"></div>
		</div>
		<!-- home page end-->
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');?>
        <script src="assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
		<script>
			function changeCode(id)
			{
				var request = $.ajax({
					type: "POST",
					url: 'change_code.php',
					data: 'id=' + id,
					success: function (data)
					{
						document.getElementById("code").value = data;
						//window.location = 'profile.php';
					}
				});
			}
			
			$(document).ready(function(){
				var contry=$("#con").val();
				 changeCode(contry);
			});
            
            
            
            function validate_refercode(id){        
    
                console.log(id);
                if(id == ""){
                    return true;
                }else{


                    var request = $.ajax({
                        type: "POST",
                        url: 'ajax_validate_refercode.php',
                        data: 'refcode=' +id,
                        success: function (data)
                        {
                            console.log(data);
                            if(data == 0){
                            $("#referCheck").remove();
                            $(".vRefCode_verify").addClass('required-active');
                            $('#refercodeCheck').append('<div class="required-label" id="referCheck" >Not Found</div>');
                            $('#vRefCode').attr("placeholder", "<?php echo $langage_lbl['LBL_REFERAL_CODE'];?>");
                            $('#vRefCode').val("");
                            return false;
                            }else{
                                var reponse = data.split('|');              
                                $('#iRefUserId').val(reponse[0]);
                                $('#eRefType').val(reponse[1]);
                            }
                        }
                    });

                }   

            }
            
            
			function validate_email(id)
			{
				
				var request = $.ajax({
					type: "POST",
					url: 'ajax_validate_email.php',
					data: 'id=' +id,
					success: function (data)
					{
						if(data==0)
						{
							$('#emailCheck').html('<i class="icon icon-remove alert-danger alert"><?php echo $langage_lbl['LBL_EMAIL_ALREADY_EXIST']?></i>');
							$('input[type="submit"]').attr('disabled','disabled');
						}
						else if(data==1)
						{
							var eml=/^[-.0-9a-zA-Z]+@[a-zA-z]+\.[a-zA-z]{2,3}$/;
							result=eml.test(id);
							if(result==true)
							{
								$('#emailCheck').html('<i class="icon icon-ok alert-success alert"> <?php echo $langage_lbl['LBL_VALID']?></i>');
								$('input[type="submit"]').removeAttr('disabled');
							}
							else
							{
								$('#emailCheck').html('<i class="icon icon-remove alert-danger alert"><?php echo $langage_lbl['LBL_ENTER_PROPER_EMAIL']?></i>');
								$('input[type="submit"]').attr('disabled','disabled');
							}
						}
					}
				});
			}
            
            
    function initMap() {
        
        var directionsDisplay = new google.maps.DirectionsRenderer();
        var directionsService = new google.maps.DirectionsService();

        
        var source = {lat: <?php echo  $vStartLat ?>, lng: <?php echo  $vStartLon ?>};
        var map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 13,
        center: source
        });

        directionsDisplay.setMap(map);
        <?php if($tTripGoingData != "") : ?>
            directionsDisplay.setDirections(JSON.parse('<?php echo  json_encode($tTripGoingData) ?>'));
        <?php endif; ?>
//        var request = {
//            origin: source,
//            destination: dest,
//            travelMode: 'DRIVING'
//        };
//
//        directionsService.route(request, function(result, status) {
//            if (status == 'OK') {
//              directionsDisplay.setDirections(result);
//            }
//        });
      }
		</script>
        
        <script  async defer src="https://maps.googleapis.com/maps/api/js?v=3.exp&callback=initMap&sensor=false&libraries=places&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>
		<!-- End: Footer Script -->
	</body>
</html>

<?php
	function getMinTimeDijkstra($driver,$sourceLatLon,$destLatLon,$tMaxDate)
	{
		// لوکیشن راننده به عنوان مبدا
        $driverLocalLatLon = $driver['vLatitude'] . ',' . $driver['vLongitude'];

        // تمامی سفرهای فعال این راننده گرفته می شود
		$trips = getDriverActiveTrips($driver['vTripsML']);

		// متغیری برای تست اولویت مبدا و مقصد
        $tripTester = array();


		// تمامی نقاطی که مسیر باید از آنها بگذرد درون آرایه قرار میگیرند
		$waypoints = array();
		$tripArray = array();

		foreach($trips as $tripItem)
		{
            if($tripItem['tStartDate'] == '0000-00-00 00:00:00') {
                $waypoints[] = array(
                    'LatLon' => $tripItem['tStartLat'] . ',' . $tripItem['tStartLong'],
                    'isSource' => true,
                    'tripId' => $tripItem['iTripId']
                );
            }
            else
			{
				// نشان می دهد که از سورس عبور کرده ایم
                $tripTester[$tripItem['iTripId']] = true;
			}

            if($tripItem['tEndDate'] == '0000-00-00 00:00:00')
                $waypoints[] = array(
                    'LatLon' => $tripItem['tEndLat'] . ',' . $tripItem['tEndLong'],
                    'isSource' => false,
                    'tripId' => $tripItem['iTripId']
                );

            $tripArray[$tripItem['iTripId']] = $tripItem;
        }

        if($sourceLatLon != '' && $destLatLon != '') {
            // نقاط ابتدا و انتهای سفر درخواستی نیز اضافه می شوند
            $waypoints[] = array(
                'LatLon' => $sourceLatLon,
                'isSource' => true,
                'tripId' => 0
            );

            $waypoints[] = array(
                'LatLon' => $destLatLon,
                'isSource' => false,
                'tripId' => 0
            );
            // برای مبدا و مقصد جدید چون سفری ثبت نشده است خودمان سفر فرضی اضافه میکنیم
            $tripArray[0] = array('tStartDate' => "0000-00-00 00:00:00",
                'tMaxDate' => date('Y-m-d H:i:s', $tMaxDate)
            );
            //////////////////////////////////////////////////////
        }

        // آرایه نقاط استخراج میشود تا به تابع پیدا کردن بهترین مسیر ارسال شود
        $extraLatLonArray = array();

        for($i = 0 ; $i < count($waypoints) ; $i++)
		{
            $extraLatLonArray[] = $waypoints[$i]['LatLon'];
		}


		// پیدا کردن بهترین مسیر بین نقاط با گوگل
        $data = getDijkstraByGoogle($driverLocalLatLon,$driverLocalLatLon,$extraLatLonArray);

        if($data === false || $data['status'] != 'OK')
		{
            $data = getDijkstraByGoogle($driverLocalLatLon,$driverLocalLatLon,$extraLatLonArray);
            if($data === false || $data['status'] != 'OK')
                return array('timePast' => PHP_INT_MAX);
		}

		// منظم کردن نقاط بر اساس پیشنهاد گوگل
		$waypoints_order = $data['routes'][0]['waypoint_order'];
		$legs = $data['routes'][0]['legs'];
		$newWaypointsArray = array();
		foreach ($waypoints_order as $item)
		{
			$newWaypointsArray[] = $waypoints[$item];
		}

		$waypoints = $newWaypointsArray;
		unset($newWaypointsArray);


		// if source goto after dest is Error

        $tripId = 0;

		foreach ($waypoints as &$item)
		{
			///////////////////////////////////////////////////////
			/// اگر در یک سفر نقطه مبدا بعد از نقطه مقصد قرار گرفته باشد خطا است
			$tripId = $item['tripId'];

			if($item['isSource'])
                $tripTester[$tripId] = true;
			else if(isset($tripTester[$tripId]) == false)
                return array('timePast' => PHP_INT_MAX);
			//////////////////////////////////////////////////////
		}



		////////////////////////////////////////////////////////
		/// در این قسمت فاصله زمانی باقی مانده برای هر سفر با حداکثر زمان پیش بینی شده
		/// مقایسه می شود و جمع زمان های باقیمانده برگردانده می شود
        $timeSec  = 0;
		$timeLeft = 0;
        $timePast = 0;
		$nowTime  = time();

		$waypointsLen = count($waypoints);


		for($i = 0 ; $i < $waypointsLen ; $i++)
		{
            $timeSec += intval($legs[$i]['duration']['value']);

            $itemTripId = $waypoints[$i]['tripId'];

            $thisTrip = $tripArray[$itemTripId];


            // در صورتی هنوز مبدا این مسافر نرسیده ایم زمان شروع سفر او را
			// حدودی برابر زمان فعلا به علاوه زمان رسیدن به او قرار میدهیم
            if($waypoints[$i]['isSource'] == true)
			{
                if($thisTrip['tStartDate'] == "0000-00-00 00:00:00")
                    $tripArray[$itemTripId]["aboutStartTime"] = $nowTime + $timeSec;
			}


            if($waypoints[$i]['isSource'] == false)
			{


                if($thisTrip['tStartDate'] != "0000-00-00 00:00:00")
                	$startTime = strtotime($thisTrip['tStartDate']);
                else
                	$startTime = $tripArray[$itemTripId]["aboutStartTime"];

                $startTime = max($startTime,$nowTime);

                $maxTime = strtotime($thisTrip['tMaxDate']);

                $timeLeftTemp = $maxTime - ($nowTime + $timeSec);

                if($timeLeftTemp < 0 )
                    return array('timePast' => PHP_INT_MAX);
                else
				{
					$timeLeft += $timeLeftTemp;
                    $timePast +=  $nowTime - $startTime + $timeSec ;
                }
			}
		}

		return array('timePast' => $timePast, 'timeLeft' => $timeLeft , 'waypoints' => $waypoints);
    }

	// add by seyyed amir
	function getDijkstraByGoogle($sourceLatLon,$destLatLon,$extraLatLonArray)
	{
		global $generalobj;

		// get default language for google result
        $vLangCodeData = get_value('language_master', 'vCode, vGMapLangCode', 'eDefault','Yes');
        $vGMapLangCode=$vLangCodeData[0]['vGMapLangCode'];
        // get google api key of server
		$GOOGLE_API_KEY=$generalobj->getConfigurations("configurations","GOOGLE_SEVER_GCM_API_KEY");


        $url = "https://maps.googleapis.com/maps/api/directions/json?alternatives=false&origin=" . $sourceLatLon . "&destination=" . $destLatLon . "&sensor=true" ."&key=". $GOOGLE_API_KEY ."&language=".$vGMapLangCode;;

        $waypoints = '';

        if(is_array($extraLatLonArray))
		{
			foreach ($extraLatLonArray as $point)
			{
                $waypoints .= '|' . $point;
			}
		}

		if($waypoints != '')
			$url .= "&waypoints=optimize:true" /* '|' */ . $waypoints;


        try {

            $jsonfile = file_get_contents($url);
            $data = json_decode($jsonfile,true);

            return $data;

        } catch (ErrorException $ex) {

            return false;
        }
    }

    
    
    
?>
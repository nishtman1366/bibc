<?php

//echo "<pre>";
//echo strtotime('01:00:00') - strtotime('TODAY'); echo "\n";
//echo date('H:i:s',strtotime('TODAY'))."\n";
//echo strtotime('01:00:00')."\n";
//die();

//$ISDEBUG = true;
$LOG = false;
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


//    if ($_SESSION['sess_user'] == 'company') {
//		$iCompanyId = $_SESSION['sess_iCompanyId'];
//		$sql = "select * from register_driver where iCompanyId = '" . $_SESSION['sess_iCompanyId'] . "'";
//		$db_drvr = $obj->MySQLSelect($sql);
//	}


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
	$tbl_name = 'register_student';
	$script = 'Student';
	
	$sql = "select * from language_master where eStatus = 'Active' and eDefault='Yes' ORDER BY vTitle ASC";
	$db_lang = $obj->MySQLSelect($sql);
	
	$sql = "select * from company where eStatus != 'Deleted'";
	$db_company = $obj->MySQLSelect($sql);
	

	if (isset($_POST['submit'])) {

		$iCompanyId = $_SESSION['sess_iUserId'];

        
        $iStudentId = $_POST['iStudentId'];
        
        
        
        $data = array(
            //'iGroupId'     => $_POST['iGroupId'],
            'vSName'        => $_POST['vSName'],
            'vSLastName'    => $_POST['vSLastName'],
            'vSchoolName'   => $_POST['vSchoolName'],
            'tGoingTime'    => $_POST['tGoingTime'],
            'tComeBackTime' => $_POST['tComeBackTime'],
            'eGender'       => $_POST['eGender'],
            'SAT'           => $_POST['SAT'],
            'SUN'           => $_POST['SUN'],
            'MON'           => $_POST['MON'],
            'TUE'           => $_POST['TUE'],
            'WED'           => $_POST['WED'],
            'THU'           => $_POST['THU'],
            'FRI'           => $_POST['FRI'],
            //'eStatus'           => 'AssignDriver',
        );
        
        // For InsertNew
        $query = 'INSERT ';
        $where = '';
        $insertExtra = " , `eTripStatus`='Active' , `eStatus`='NEW' ";
        
        // For Update exists item
        if($iStudentId != '' && $iStudentId != '0')
        {
            $query = 'UPDATE ';
            $where = " WHERE iStudentId = $iStudentId ";
            $insertExtra = '';
        } 
        /////////////End for
        
        $query .=  ' `register_student` SET ';
        
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
        
        
        
        /////////////////////////////////////////////////////////////////////
        $sql = "SELECT * FROM {$tbl_name} WHERE iStudentId = $iStudentId ";
	    $db_currentstudent = $obj->MySQLSelect($sql);
        if(count($db_currentstudent) > 0)
            $currentStudent = $db_currentstudent[0];
        
        $groupId = $_POST['iGroupId'];
        
        
        $ret = getMinTimeDijkstraSchool($groupId,$currentStudent);
        if($LOG == true ) {
            echo '<pre>';
            print_r($ret);
            if($ret === false)
                var_dump($ret);
            die("END");
        }
        
        
        if($ret !== false && $ret['status'] !== false)
        {
            $groupsData['tGroupGoingTime'] = $ret['data']['driverStartTime'];
            //$groupsData['tGroupComeBackTime'] = //$ret['data']['driverStartTime'];
            $groupsData['tTripGoingData'] = json_encode($ret['data']);
            $groupsData['iSeatUsage'] = count($ret['students']);

            
            $where = " iGroupId = '".$groupId."'";
            $obj->MySQLQueryPerform("register_student_groups",$groupsData,'update',$where);
            
            
            foreach($ret['students'] as $stuData)
            {
                $studentData = array(
                    'iGroupId' => $groupId,
                    'fFixedTime' => $stuData['fFixedTime'],
                    'fFixedDistance' => $stuData['fFixedDistance'],
                    'eStatus'  => 'AssignDriver',
                );

                $where = " iStudentId = '".$stuData['iStudentId']."'";
                $obj->MySQLQueryPerform("register_student",$studentData,'update',$where);
            }
            
            $success = 1;
            $var_msg = 'Student Update success..';
            header("Location:students.php?id=" . $id . '&success='.$success.'&var_msg='.$var_msg);
            exit;
        }
        else
        {
            $success = 3;
            $var_msg = $ret['message'];
            header("Location:students_action.php?id=" . $id . '&success='.$success.'&var_msg='.$var_msg);
            exit;
        }
        /////////////////////////////////////////////////////////////////////
    }
    
	// for Edit
	
	if ($action == 'Edit' && $iAreaId != 0) {
		$sql = "SELECT st.*, CONCAT(rd.vName,' ',rd.vLastName) as driverName, CONCAT(ru.vName,' ',ru.vLastName) as parentName FROM {$tbl_name} as st
		  LEFT JOIN register_user as ru on st.iParentId=ru.iUserId 
          LEFT JOIN register_student_groups as groups ON  st.iGroupId=groups.iGroupId  
          LEFT JOIN register_driver as rd on groups.iDriverId=rd.iDriverId 
          WHERE st.iStudentId = $id AND st.iAreaId = $iAreaId ".$cmp_ssql . "  ORDER BY `st`.`iStudentId` DESC";
        
		$db_data = $obj->MySQLSelect($sql);
		#echo "<pre>";print_R($db_data);echo "</pre>";die();
		
		if (count($db_data) > 0) {
			foreach ($db_data as $key => $value) {
				$vSName     = $value['vSName'];
				$vSLastName = $value['vSLastName'];
				$iStudentId = $value['iStudentId'];
				$iDriverId  = $value['iDriverId'];
				$iParentId  = $value['iParentId'];
				$iGroupId   = $value['iGroupId'];
				$iAreaId    = $value['iAreaId'];
				$iVehicleTypeId  = $value['iVehicleTypeId'];
				$driverName = $value['driverName'];
				$parentName = $value['parentName'];
				$vSchoolName = $value['vSchoolName'];
				$vSchoolAddress = $value['vSchoolAddress'];
				$vHomeAddress = $value['vHomeAddress'];
				$eGender     = $value['eGender'];
				$eEnableGoing = $value['eEnableGoing'];
				$eEnableComeBack = $value['eEnableComeBack'];
				$tGoingTime = $value['tGoingTime'];
				$tComeBackTime = $value['tComeBackTime'];
				$vImageSrc = $value['vImageSrc'];
				$vImageUrl = $tconfig["tsite_upload_images_passenger"] ."/".$iParentId."/students/" . $value['vImageSrc'];
				$SAT = $value['SAT'];
				$SUN = $value['SUN'];
				$MON = $value['MON'];
				$TUE = $value['TUE'];
				$WED = $value['WED'];
				$THU = $value['THU'];
				$FRI = $value['FRI'];
                
                $vHomeLat   = $value['vHomeLat'];
                $vHomeLon   = $value['vHomeLon'];
                $vSchoolLat = $value['vSchoolLat'];
                $vSchoolLon = $value['vSchoolLon'];
                #echo "<pre>";print_R($value);echo "</pre>";die();
			}
		}
        
        
        
	}
	

if($iVehicleTypeId != 0)
{
    $ssql = " AND eGroupGender IN ('$eGender','None','Both') ";
    $sql = "SELECT stg.*, CONCAT(rd.vName,' ',rd.vLastName) as driverName FROM `register_student_groups` as stg
		 LEFT JOIN `driver_vehicle` as vcl on stg.iDriverId=vcl.iDriverId
         LEFT JOIN register_driver as rd on stg.iDriverId=rd.iDriverId
		 WHERE  FIND_IN_SET($iVehicleTypeId,vcl.vCarType) $ssql ORDER BY `stg`.`iGroupId` DESC";
    
    $db_groups = $obj->MySQLSelect($sql);
    #echo "<pre>";print_R($db_groups);echo "</pre>";die();
}
	
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
					<h2 class="header-page trip-detail driver-detail1"><?php echo  $action_show; ?> <?php echo  $vName; ?>
					<a href="students.php">
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
								<?php echo  $var_msg ?>
							</div>
							<?php 
							}
						?>
						<form id="frm1" method="post" onSubmit="return editPro('login')" enctype="multipart/form-data">
							<input  type="hidden" class="edit" name="action" value="login">
							<input  type="hidden" class="edit" id="con" name="vCountry" value="<?php echo $vCountry?>">
							<input  type="hidden" class="edit" name="vLang" value="<?php echo $db_lang[0]['vCode']?>">
							<input  type="hidden" class="edit" name="iStudentId" value="<?php echo $id?>">
							<div class="driver-action-user-image">
								<?php if($id){?>
									<?php if ($vImageSrc == '') { ?>
										<img src="assets/img/profile-user-img.png" alt="">
										<?}else{?>
										<img src = "<?php echo  $vImageUrl ?>" style="height:150px;"/>
									<?}?>
								<?php }?>
							</div>
							<div class="driver-action-page-right validation-form">
								<div class="row">
									
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_LAST_NAME_TXT'];?>	</label>
											<input type="text" class="driver-action-page-input" name="vSLastName"  id="vSLastName" value="<?php echo  $vSLastName; ?>" placeholder="<?php echo $langage_lbl['LBL_LAST_NAME_TXT'];?>"   required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
                                    <div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_FIRST_NAME_TXT'];?></label>
											<input type="text" class="driver-action-page-input" name="vSName"  id="vSName" value="<?php echo  $vSName; ?>" placeholder="<?php echo $langage_lbl['LBL_FIRST_NAME_TXT'];?>"   required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
                                    <div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_SCHOOL_NAME'];?></label>
											<input type="text" class="driver-action-page-input" name="vSchoolName"  id="vSchoolName" value="<?php echo  $vSchoolName; ?>" placeholder="<?php echo $langage_lbl['LBL_SCHOOL_NAME'];?>" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')">
										</span> 
									</div>
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_PARENT_NAME'];?>	</label>
											<input type="text" class="driver-action-page-input" name="parentName"  id="parentName" value="<?php echo  $parentName; ?>" placeholder="<?php echo $langage_lbl['LBL_PARENT_NAME'];?>" oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
                                    
                                    
                                    <div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_SCHOOL_PLACE'];?></label>
											<input type="text" class="driver-action-page-input" name="vSchoolName"  id="vSchoolName" value="<?php echo  $vSchoolAddress; ?>" placeholder="<?php echo $langage_lbl['LBL_SCHOOL_PLACE'];?>" oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" >
										</span> 
									</div>
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_HOME_PLACE'];?>	</label>
											<input type="text" class="driver-action-page-input" name="parentName"  id="parentName" value="<?php echo  $vHomeAddress; ?>" placeholder="<?php echo $langage_lbl['LBL_HOME_PLACE'];?>"  >
										</span> 
									</div>
                                    <div class="col-md-6">
										<span <?php if($eEnableGoing== "No") echo 'style="background-color:rgba(255, 0, 0, 0.11)"' ?>>
											<label><?php echo $langage_lbl['LBL_SCHOOL_GO_TIME_LIMIT'];?>	</label>
											<input type="text" class="driver-action-page-input" name="parentName"  id="parentName" value="<?php echo  $tGoingTime; ?>" placeholder="<?php echo $langage_lbl['LBL_SCHOOL_GO_TIME_LIMIT'];?>" >
										</span> 
									</div>
                                    <div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_STUDENT_GENDER_BOY'];?>
											<input type="radio" class="" name="gender"  id="parentName" value="Boy" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')" <?php if($eGender == 'Boy'){?>checked<?php } ?>>
                                                </label>
										</span>
                                        <span>
											<label><?php echo $langage_lbl['LBL_STUDENT_GENDER_GIRL'];?>
											<input type="radio" class="" name="gender"  id="parentName" value="Girl"  required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')"  <?php if($eGender == 'Girl'){?>checked<?php } ?> >
                                                </label>
										</span>
									</div>
                                    
                                    <div class="col-md-6">
										<span <?php if($eEnableComeBack== "No") echo 'style="background-color:rgba(255, 0, 0, 0.11)"' ?>>
											<label><?php echo $langage_lbl['LBL_SCHOOL_REBACK_TIME_LIMIT'];?>	</label>
											<input type="text" class="driver-action-page-input" name="parentName"  id="parentName" value="<?php echo  $tComeBackTime; ?>" placeholder="<?php echo $langage_lbl['LBL_SCHOOL_REBACK_TIME_LIMIT'];?>" >
                                            
										</span> 
									</div>
                    
									
                                    <h3><?php echo $langage_lbl['LBL_SELECT_DRIVER']; ?></h3>
                                    <div class="col-md-12">
										<span>
											<label><?php echo $langage_lbl['LBL_SELECT_DRIVER']?></label>
                                        <select name = "iGroupId" id="iGroupId" class="custom-select-new" required>
                                            <option value=""><?php echo $langage_lbl['LBL_CHOOSE_DRIVER']; ?></option>
                                            <?php for ($j = 0; $j < count($db_groups); $j++) { ?>
                                                <option value="<?php echo  $db_groups[$j]['iGroupId'] ?>" <?php if($db_groups[$j]['iGroupId'] == $iGroupId){?> selected <?} ?>><?php echo  $db_groups[$j]['vGroupName'] . ' - ' . $db_groups[$j]['driverName'] ?></option>
                                            <?php } ?>
                                        </select>
										</span> 
									</div>
                                    
                                    <h3><?php echo $langage_lbl['LBL_SCHOOL_WEEKDAYS']; ?></h3>
		    			
		    			           <div class="car-type" dir="ltr">
                                    <ul>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_SATURDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($SAT == 'Yes'){?>checked<?php } ?> value="<?php echo  $SAT ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_SUNDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($SUN == 'Yes'){?>checked<?php } ?> value="<?php echo  $SUN ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_MONDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($MON == 'Yes'){?>checked<?php } ?> value="<?php echo  $MON ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_TUESDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($TUE == 'Yes'){?>checked<?php } ?> value="<?php echo  $TUE ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_WEDNESDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($WED == 'Yes'){?>checked<?php } ?> value="<?php echo  $WED ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_THURESDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($THU == 'Yes'){?>checked<?php } ?> value="<?php echo  $THU ?>"/>
                                            </div>
                                        </li>
                                        <li>
                                            <b><?php echo $langage_lbl['LBL_FRIDAY']; ?></b>
                                            <div class="make-switch" data-on="success" data-off="warning" data-on-label="<?php echo $langage_lbl['LBL_ON_TXT']?>" data-off-label="<?php echo $langage_lbl['LBL_OFF_TXT']?>" >
                                                <input type="checkbox" class="chk" name="vCarType[]" <?php if($FRI == 'Yes'){?>checked<?php } ?> value="<?php echo  $FRI ?>"/>
                                            </div>
                                        </li>
                                        
                                    </ul>
                                    </div>
									<p>
										<input type="submit" class="save-but" name="submit" id="submit" value="<?php echo  $action_show; ?> ">
										
									</p>
									<div style="clear:both;"></div>
                                    <br />
                                    <br />
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

        
        var source = {lat: <?php echo  $vHomeLat ?>, lng: <?php echo  $vHomeLon ?>};
        var dest = {lat: <?php echo  $vSchoolLat ?>, lng: <?php echo  $vSchoolLon ?>};
        var map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 13,
        center: source
        });

        directionsDisplay.setMap(map);

        var request = {
            origin: source,
            destination: dest,
            travelMode: 'DRIVING'
        };

        directionsService.route(request, function(result, status) {
            if (status == 'OK') {
              directionsDisplay.setDirections(result);
            }
        });


//          var image = {
//            url: 'assets/img/savar/StartPin.png',
//            //size: new google.maps.Size(80, 80),
//            // The origin for this image is (0, 0).
//            //origin: new google.maps.Point(0, 0),
//            // The anchor for this image is the base of the flagpole at (0, 32).
//            anchor: new google.maps.Point(25,25)
//              
//          };
//
//         
//        var markerSource = new google.maps.Marker({
//          position: driverSource,
//          map: map,
//          icon: image,
//          opacity: 0.7
//        });
//          
//          image['url'] = 'assets/img/savar/EndPin.png';
//          var markerEnd = new google.maps.Marker({
//          position: driverDest,
//          map: map,
//          icon: image,
//          opacity: 0.7
//        });
      }
            
		</script>
        <script  async defer src="https://maps.googleapis.com/maps/api/js?v=3.exp&callback=initMap&sensor=false&libraries=places&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>
		<!-- End: Footer Script -->
	</body>
</html>

<?php
    
    
function getMinTimeDijkstraSchool($groupId,$currentStudent)
{
    global $obj,$LOG;

    if($LOG == true ) echo "<pre>\n";
    $sql = "SELECT * FROM `register_student_groups` where `iGroupId` = '" . $groupId . "'";
    $group = $obj->MySQLSelect($sql);
    if(count($group) == 0)
        return array('status' => false,'message' => 'group id is empty');
    
    $group = $group[0];
    
    // لوکیشن راننده به عنوان مبدا
    $driverLocalLatLon = $group['vStartLat'] . ',' . $group['vStartLon'];

    if($LOG == true )  echo "DRIVER LOC : " .$driverLocalLatLon . "\n";
    // تمامی سفرهای فعال این راننده گرفته می شود
    //$trips = getDriverActiveTrips($driver['vTripsML']);
    
    $sql = "SELECT * FROM `register_student` where `iGroupId` = '" . $groupId . "' ";
    
    
    if(isset($currentStudent['iStudentId']) && $currentStudent['iStudentId'] != 0)
    {
        $sql .= " AND iStudentId != " . $currentStudent['iStudentId'];
    }
    
    $students = $obj->MySQLSelect($sql);
    
    
    
    // اضافه کرد دانش آموز فعلی به لیست
    $students[] = $currentStudent;
    
    if(count($students) > $group['iSeatNumber'])
        return array('status' => false,'message' => 'Group in Full');

    
    // متغیری برای تست اولویت مبدا و مقصد
    $tripTester = array();


    // تمامی نقاطی که مسیر باید از آنها بگذرد درون آرایه قرار میگیرند
    $waypoints = array();
    $studentArray = array();

    foreach($students as $studentItem)
    {
        
        $waypoints[] = array(
            'LatLon' => $studentItem['vHomeLat'] . ',' . $studentItem['vHomeLon'],
            'isSource' => true,
            'studentId' => $studentItem['iStudentId']
        );

        $waypoints[] = array(
            'LatLon' => $studentItem['vSchoolLat'] . ',' . $studentItem['vSchoolLon'],
            'isSource' => false,
            'studentId' => $studentItem['iStudentId']
        );
        
        $studentArray[$studentItem['iStudentId']] = $studentItem;
    }
    


//    if($currentStudent['vStartLat'] != '' && $currentStudent['vStartLon'] != '') {
//        // نقاط ابتدا و انتهای سفر درخواستی نیز اضافه می شوند
//        $waypoints[] = array(
//            'LatLon' => $currentStudent['vStartLat'],
//            'isSource' => true,
//            'studentId' => 0
//        );
//
//        $waypoints[] = array(
//            'LatLon' => $currentStudent['vStartLon'],
//            'isSource' => false,
//            'studentId' => 0
//        );
//        // برای مبدا و مقصد جدید چون سفری ثبت نشده است خودمان سفر فرضی اضافه میکنیم
//        $tripArray[0] = array('tStartDate' => "0000-00-00 00:00:00",
//            'tMaxDate' => date('Y-m-d H:i:s', $tMaxDate)
//        );
//        //////////////////////////////////////////////////////
//    }

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
            return array('status' => false,'message' => 'google api error');
    }

    // منظم کردن نقاط بر اساس پیشنهاد گوگل
    $waypoints_order = $data['routes'][0]['waypoint_order'];
    $newWaypointsArray = array();
    foreach ($waypoints_order as $item)
    {
        $newWaypointsArray[] = $waypoints[$item];
    }

    $waypoints = $newWaypointsArray;
    unset($newWaypointsArray);

    if($LOG == true )  print_r($waypoints);

    // if source goto after dest is Error

    $tripId = 0;

    foreach ($waypoints as &$item)
    {
        ///////////////////////////////////////////////////////
        /// اگر در یک سفر نقطه مبدا بعد از نقطه مقصد قرار گرفته باشد خطا است
        $studentId = $item['studentId'];

        if($item['isSource'])
            $tripTester[$studentId] = true;
        else if(isset($tripTester[$studentId]) == false)
            return  array('status' => false,'message' => 'destination is first for Student : ' .$studentId);
        //////////////////////////////////////////////////////
    }
    
    if($LOG == true )   echo "OK1";

    $studentsWayPoints = $waypoints;
    
    $endWayPoint = array_pop($waypoints);
    
    $extraLatLonArray = array();

    for($i = 0 ; $i < count($waypoints) ; $i++)
    {
        $extraLatLonArray[] = $waypoints[$i]['LatLon'];
    }
    
    $data = getDijkstraByGoogle($driverLocalLatLon,$endWayPoint['LatLon'],$extraLatLonArray);

    if($data === false || $data['status'] != 'OK')
    {
        $data = getDijkstraByGoogle($driverLocalLatLon,$endLatLon,$waypoints);
        if($data === false || $data['status'] != 'OK')
            return array('status' => false,'message' => 'End Google API Error, try again...');
    }

    $data['startLatLon'] = $driverLocalLatLon ;
    $data['endLatLon'] =  $endWayPoint['LatLon'];
    $data['waypointsLatLon'] =  $extraLatLonArray;
    

    $legs = $data['routes'][0]['legs'];
    

    ////////////////////////////////////////////////////////
    /// در این قسمت فاصله زمانی باقی مانده برای هر سفر با حداکثر زمان پیش بینی شده
    /// مقایسه می شود و جمع زمان های باقیمانده برگردانده می شود
    
    $timeAll = 0;
    $timeStudends = 0;
    

    $isFirst = true;

    foreach($legs as $leg)
    {
        if($isFirst)
        {
            $isFirst = false; 
        }
        else
            $timeStudends += intval($legs[$i]['duration']['value']);
        
        $timeAll += intval($legs[$i]['duration']['value']);
    }
    
    
    
    
    // در این قسمت زمان حدودی حرکت راننده را بدست می آوریم 
    $TIME_SPACE = 5 * 60; //Sec
    $minGoingTime = '23:59:59';
    
    foreach($students as $stditem)
    {
        if(strcmp($stditem['tGoingTime'],$minGoingTime) < 0)
            $minGoingTime = $stditem['tGoingTime'];
    }

    $driverStartTime = strtotime($minGoingTime) - ($TIME_SPACE + $timeAll);
    
//    echo "Min: " . $minGoingTime ."\n";
//    echo "Min: " . $timeAll ."\n";
//    echo "Driver Start: " . date("H:i:s" , $driverStartTime) ."\n";


    // از اینجا به بعد هم میخواهیم بهترین زمان حرکت را محاسبه کنیم
    $timeSec  = 0;
    $distanceMeter = 0;
    $timeLeft = 0;
    $timePast = 0;
    $minTimeLeft = PHP_INT_MAX;
    $stwaypointsLen = count($studentsWayPoints);
    
   
    
    for($i = 0 ; $i < $stwaypointsLen ; $i++)
    {
        $timeSec +=  intval($legs[$i]['duration']['value']);
        $distanceMeter += intval($legs[$i]['distance']['value']);

        $thisWaypoint = $studentsWayPoints[$i];
        $itemStudentId = $thisWaypoint['studentId'];

        $thisTrip = $studentArray[$itemStudentId];

        // در صورتی هنوز مبدا این مسافر نرسیده ایم زمان شروع سفر او را
        // حدودی برابر زمان فعلا به علاوه زمان رسیدن به او قرار میدهیم
        if($thisWaypoint['isSource'] == true)
        {
            $studentArray[$itemStudentId]["startTime"] = $driverStartTime + $timeSec;
            $studentArray[$itemStudentId]["startDistanceMeter"] = $distanceMeter;
            
            $startTime = $studentArray[$itemStudentId]["startTime"];
        }


        if($thisWaypoint['isSource'] == false)
        {

            $startTime = $studentArray[$itemStudentId]["startTime"];
            
            // زمان سفر بر حسب ثانیه
            // برابر زمان شروع سفر راننده به علاوه زمانی که تا مقصد گذشته
            // منهای زمان شروع سفر
            $studentArray[$itemStudentId]["inTripTimeSec"] = ($driverStartTime + $timeSec) - $startTime;
            $studentArray[$itemStudentId]["endDistanceMeter"] = $distanceMeter;

            $maxTime = strtotime($thisTrip['tGoingTime']);
            $timeLeftTemp = $maxTime - ($driverStartTime + $timeSec);
            
            // ذخیره کمترین زمانی که یک دانش آموز زودتر از موعد مقرر میرسد
            $minTimeLeft = min($minTimeLeft,$timeLeftTemp);
        }
    }
    
    $eslaheTime = $minTimeLeft - $TIME_SPACE;
    
    $driverStartTime += $eslaheTime;
    //print_r($studentArray);
    foreach($studentArray as &$studentItem1)
    {
       $studentItem1["startTime"] += $eslaheTime;
       $studentItem1["startTimeStr"] = date('H:i:s',$studentItem1["startTime"]);
       $studentItem1["fFixedTime"] = round($studentItem1["inTripTimeSec"] / 60,2);
       $studentItem1["fFixedDistance"] = round(($studentItem1["endDistanceMeter"] - $studentItem1["startDistanceMeter"]) / 1000, 2);
    }

    //$data['studentWaypoints'] = $waypoints;
    $data['studentWaypoints'] = $studentsWayPoints;
    $data['driverStartTime'] = date('H:i:s',$driverStartTime);

    return array('status' => true,'timeAll' => $timeAll, 'timeStudents' => $timeStudends ,'data' => $data,"students" => $studentArray);
}

// add by seyyed amir
function getDijkstraByGoogle($sourceLatLon,$destLatLon,$extraLatLonArray)
{
    global $generalobj;
    
    // get default language for google result
    $vLangCodeData = 'fa';//get_value('language_master', 'vCode, vGMapLangCode', 'eDefault','Yes');
    
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

    #echo $url . "\r";

    try {

        $jsonfile = file_get_contents($url);
        $data = json_decode($jsonfile,true);

        return $data;

    } catch (ErrorException $ex) {

        return false;
    }
}
?>
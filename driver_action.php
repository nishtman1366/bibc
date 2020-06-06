<?php
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	
	require_once(TPATH_CLASS . "/Imagecrop.class.php");
	$thumb = new thumbnail();
	$generalobj->check_member_login();
	$sql = "select * from country where eStatus = 'Active'";
	$db_country = $obj->MySQLSelect($sql);
	

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

	if($_REQUEST['id'] != '' && $_SESSION['sess_iCompanyId'] != ''){
		
        
        
		$sql = "select * from register_driver where iDriverId = '".$_REQUEST['id']."' AND iCompanyId IN (".$comp_list.")";
		$db_cmp_id = $obj->MySQLSelect($sql);
		
		if(!count($db_cmp_id) > 0) 
		{
			header("Location:driver.php?success=0&var_msg=".$langage_lbl['LBL_NOT_YOUR_DRIVER']);
		}
	}

	
	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
	$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
	$action = ($id != '') ? 'Edit' : 'Add';
	$action_show = ($id != '') ? $langage_lbl['LBL_EDIT_DRIVER_TXT'] : $langage_lbl['LBL_ADD_DRIVER_TXT'];
	$iCompanyId = $_SESSION['sess_iUserId'];
	$tbl_name = 'register_driver';
	$script = 'Driver';
	
	$sql = "select * from language_master where eStatus = 'Active' and eDefault='Yes' ORDER BY vTitle ASC";
	$db_lang = $obj->MySQLSelect($sql);
	
	$sql = "select * from company where eStatus != 'Deleted'";
	$db_company = $obj->MySQLSelect($sql);
	
	//echo '<prE>'; print_R($_REQUEST); echo '</pre>';
	// set all variables with either post (when submit) either blank (when insert)
	$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
	
	$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
	$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
	$vUserName = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
	$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
	$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
	$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : $db_country[0]['vCountryCode'];
	$vCode = isset($_POST['vCode']) ? $_POST['vCode'] : '';
	$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';
	$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
	$vImage = isset($_POST['vImage']) ? $_POST['vImage'] : '';
	$vRefCode = isset($_POST['vRefCode']) ? $_POST['vRefCode'] : '';


	$dBirthDate="";
	if($_POST['vYear'] != "" && $_POST['vMonth'] != "" && $_POST['vDay'] != "") {
		
		//$dBirthDate=$_POST['vYear'].'-'.$_POST['vMonth'].'-'.$_POST['vDay'];
		$dBirthDate = jalali_to_gregorian($_POST['vYear'],$_POST['vMonth'],$_POST['vDay']);
		$dBirthDate= $dBirthDate[0].'-'.$dBirthDate[1].'-'.$dBirthDate[2];
	}
	$vPass = $generalobj->encrypt($vPassword);

    
    if($vRefCode != ""){
        $vInviteCode = $vRefCode;
        $check_inviteCode ="";
        if($vInviteCode != ""){
            $check_inviteCode = $generalobj->validationrefercode($vInviteCode);
        
            if($check_inviteCode == "" || $check_inviteCode == "0" || $check_inviteCode == 0){
                
            }else{
                $inviteRes= explode("|",$check_inviteCode);
                $ref['iRefUserId'] = $inviteRes[0];
                $ref['eRefType'] = $inviteRes[1];
            }
        }
	}


	if (isset($_POST['submit'])) {
		// if(SITE_TYPE=='Demo' && $action=='Edit')
		// {
			// header("Location:driver_action.php?id=" . $id . '&success=2');
			// exit;
		// }
		$iCompanyId = $_SESSION['sess_iUserId'];
            
        
		
		//Start :: Upload Image Script
		if(!empty($id)){
			
			if(isset($_FILES['vImage'])){
				$id = $_REQUEST['id'];
				$img_path = $tconfig["tsite_upload_images_driver_path"];
				$temp_gallery = $img_path . '/';
				$image_object = $_FILES['vImage']['tmp_name'];
				$image_name = $_FILES['vImage']['name'];
				$check_file_query = "select iDriverId,vImage from register_driver where iDriverId=" . $id;
				$check_file = $obj->sql_query($check_file_query);
				if ($image_name != "") {
					$check_file['vImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vImage'];
					
					if ($check_file['vImage'] != '' && file_exists($check_file['vImage'])) {
						unlink($img_path . '/' . $id. '/' . $check_file[0]['vImage']);
						unlink($img_path . '/' . $id. '/1_' . $check_file[0]['vImage']);
						unlink($img_path . '/' . $id. '/2_' . $check_file[0]['vImage']);
						unlink($img_path . '/' . $id. '/3_' . $check_file[0]['vImage']);
					}
					
					$filecheck = basename($_FILES['vImage']['name']);
					$fileextarr = explode(".", $filecheck);
					$ext = strtolower($fileextarr[count($fileextarr) - 1]);
					$flag_error = 0;
					if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
						$flag_error = 1;
						$var_msg = $langage_lbl['LBL_WRONG_IMAGE_FORMAT'];
					}
					/*if ($_FILES['vImage']['size'] > 1048576) {
						$flag_error = 1;
						$var_msg = "Image Size is too Large";
					}*/
					if ($flag_error == 1) {
						$generalobj->getPostForm($_POST, $var_msg, "driver_action?success=0&var_msg=" . $var_msg);
						exit;
						} else {
						
						$Photo_Gallery_folder = $img_path . '/' . $id . '/';
						
						if (!is_dir($Photo_Gallery_folder)) {
							mkdir($Photo_Gallery_folder, 0777);
						}
						$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
						$vImage = $img;
					}
					}else{
                    $vImage = $check_file[0]['vImage'];
				}
				//die();
			}
		}
		//End :: Upload Image Script
		
        
        // added by seyyed amir
        $sql = "SELECT * FROM `register_driver` WHERE (`vEmail` = '$vEmail' OR `vPhone` = '$vPhone')";
        if(!empty($id))
            $sql .= " AND `iDriverId` != $id";
        //die($sql);
        $user = $obj->MySQLSelect($sql);
        if(count($user) > 0)
        {
            $success = 3;
        }
        else
        {
            
            $q = "INSERT INTO ";
            $where = '';
            if ($action == 'Edit') {
                $str = ", eStatus = 'Inactive' ";
            } else {

                if(SITE_TYPE=='Demo')
                {	
                    $str = ", eStatus = 'active' ";
                }
                else
                {
                    $sqlc = "select vValue from configurations where vName = 'DEFAULT_CURRENCY_CODE'";
                    $db_currency = $obj->MySQLSelect($sqlc);				
                    $defaultCurrency = $db_currency[0]['vValue'];


                    $str = ", vCurrencyDriver = '$defaultCurrency' ,dBirthDate ='$dBirthDate'";


                    $refCode = $generalobj->ganaraterefercode('Driver');

                    $str .= ", vRefCode = '$refCode' ";
                }
            }
            if ($id != '') {
                $q = "UPDATE ";
                $where = " WHERE `iDriverId` = '" . $id . "'";

                $sql="select * from ".$tbl_name .$where;
                $edit_data=$obj->sql_query($sql);

                if($vEmail != $edit_data[0]['vEmail'])
                {
                    $query = $q ." `".$tbl_name."` SET `eEmailVerified` = 'No' ".$where;
                    $obj->sql_query($query);
                }
                #echo"<pre>";print_r($query);
                if($vPhone != $edit_data[0]['vPhone'])
                {
                    $query = $q ." `".$tbl_name."` SET `ePhoneVerified` = 'No' ".$where;
                    $obj->sql_query($query);
                }
                #echo"<pre>";print_r($query);
                if($vCode != $edit_data[0]['vCode'])
                {
                    $query = $q ." `".$tbl_name."` SET `ePhoneVerified` = 'No' ".$where;
                    $obj->sql_query($query);		
                }		
            }

             $query = $q . " `" . $tbl_name . "` SET
            `vName` = '" . $vName . "',
            `vLastName` = '" . $vLastName . "',
            `vCountry` = '" . $vCountry . "',
            `vCode` = '" . $vCode . "',
            `vEmail` = '" . $vEmail . "',
            `vLoginId` = '" . $vEmail . "',
            `vPassword` = '" . $vPass . "',
            `iCompanyId` = '" . $iCompanyId . "',
            `vPhone` = '" . $vPhone . "',
            `vImage` = '" . $vImage . "',
            `vLang` = '" . $vLang . "' "; 

            if(isset($ref['iRefUserId']) && $ref['iRefUserId'] != '')
            {
                $query .= ", `iRefUserId` = '{$ref['iRefUserId']}' ";
                $query .= ", `eRefType` = '{$ref['eRefType']}' ";
            }
            
            $query .= $str . ' ' . $where;
            //die($query);
            $obj->sql_query($query);

            if (mysql_insert_id() != '') {
                if(isset($_FILES['vImage'])){
                    $id = mysql_insert_id();
                    $img_path = $tconfig["tsite_upload_images_driver_path"];
                    $temp_gallery = $img_path . '/';
                    $image_object = $_FILES['vImage']['tmp_name'];
                    $image_name = $_FILES['vImage']['name'];
                    $check_file_query = "select iDriverId,vImage from register_driver where iDriverId=" . $id;
                    $check_file = $obj->sql_query($check_file_query);
                    if ($image_name != "") {
                        $check_file['vImage'] = $img_path . '/' . $id . '/' . $check_file[0]['vImage'];

                        if ($check_file['vImage'] != '' && file_exists($check_file['vImage'])) {
                            unlink($img_path . '/' . $id. '/' . $check_file[0]['vImage']);
                            unlink($img_path . '/' . $id. '/1_' . $check_file[0]['vImage']);
                            unlink($img_path . '/' . $id. '/2_' . $check_file[0]['vImage']);
                            unlink($img_path . '/' . $id. '/3_' . $check_file[0]['vImage']);
                        }

                        $filecheck = basename($_FILES['vImage']['name']);
                        $fileextarr = explode(".", $filecheck);
                        $ext = strtolower($fileextarr[count($fileextarr) - 1]);
                        $flag_error = 0;
                        if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                            $flag_error = 1;
                            $var_msg = $langage_lbl['LBL_WRONG_IMAGE_FORMAT'];
                        }
                        /*if ($_FILES['vImage']['size'] > 1048576) {
                            $flag_error = 1;
                            $var_msg = "Image Size is too Large";
                        }*/
                        if ($flag_error == 1) {
                            $generalobj->getPostForm($_POST, $var_msg, "driver_action?success=0&var_msg=" . $var_msg);
                            exit;
                            } else {

                            $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                            if (!is_dir($Photo_Gallery_folder)) {
                                mkdir($Photo_Gallery_folder, 0777);
                            }
                            $img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"], '', '', '', 'Y', '', $Photo_Gallery_folder);
                            $vImage = $img;

                            $sql = "UPDATE ".$tbl_name." SET `vImage` = '" . $vImage . "' WHERE `iDriverId` = '" . $id . "'";
                            $obj->sql_query($sql);
                        }
                    }
                }
            }
            $id = ($id != '') ? $id : mysql_insert_id();
            if($action== 'Edit')
            {
                $var_msg= $langage_lbl['LBL_RECORD_DELETED'];
            }
            else
            {
                $var_msg= $langage_lbl['LBL_RECORD_INSERTED'];
            }

            $maildata['NAME'] =$vName;
            $maildata['EMAIL'] =  $vEmail;
            $maildata['PASSWORD'] = $vPassword;
            //$generalobj->send_email_user("MEMBER_REGISTRATION_USER",$maildata);
            if($_REQUEST['id'] == '')
            {
                $generalobj->send_email_user("DRIVER_REGISTRATION_ADMIN",$maildata);
                $generalobj->send_email_user("DRIVER_REGISTRATION_USER",$maildata);
            }

            /* $sql = "select * from company where iCompanyId="$sess_iCompanyId;
                $db_company = $obj->MySQLSelect($sql);
                $companydata['NAME'] =db_company[0]['vName'];
                $companydata['EMAIL'] = db_company[0]['vEmail'];
                $companydata['PASSWORD'] = " Added New Driver named".$vName;
            $generalobj->send_email_user("MEMBER_REGISTRATION_USER",$maildata);*/
            header("Location:driver.php?id=" . $id . '&success=1&var_msg='.$var_msg);
                        exit;
	
        }
    }
	// for Edit
	
	if ($action == 'Edit') {
		$sql = "SELECT * FROM " . $tbl_name . " WHERE iDriverId = '" . $id . "'";
		$db_data = $obj->MySQLSelect($sql);
		//echo "<pre>";print_R($db_data);echo "</pre>";
		$vPass = $generalobj->decrypt($db_data[0]['vPassword']);
		$vLabel = $id;
		if (count($db_data) > 0) {
			foreach ($db_data as $key => $value) {
				$vName = $value['vName'];
				$iCompanyId = $value['iCompanyId'];
				$vLastName = $value['vLastName'];
				$vCountry = $value['vCountry'];
				$vCode = $value['vCode'];
				$vEmail = $value['vEmail'];
				$vUserName = $value['vLoginId'];
				$vPassword = $value['vPassword'];
				$vPhone = $value['vPhone'];
				$vLang = $value['vLang'];
				$vImage = $value['vImage'];
			}
		}
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
					<a href="driverlist">
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
							<input  type="hidden" class="edit" id="con" name="vCountry" value="<?php echo $vCountry?>">
							<input  type="hidden" class="edit" name="vLang" value="<?php echo $db_lang[0]['vCode']?>">
							<div class="driver-action-user-image">
								<?php if($id){?>
									<?php if ($vImage == 'NONE' || $vImage == '') { ?>
										<img src="assets/img/profile-user-img.png" alt="">
										<?}else{?>
										<img src = "<?php echo $tconfig["tsite_upload_images_driver"]. '/' .$id. '/3_' .$vImage ?>" style="height:150px;"/>
									<?}?>
								<?php }?>
							</div>
							<div class="driver-action-page-right validation-form">
								<div class="row">
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_FIRST_NAME_TXT'];?></label>
											<input type="text" class="driver-action-page-input" name="vName"  id="vName" value="<?php echo  $vName; ?>" placeholder="<?php echo $langage_lbl['LBL_FIRST_NAME_TXT'];?>" pattern="[\D]+"  required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_LAST_NAME_TXT'];?>	</label>	
											<input type="text" class="driver-action-page-input" name="vLastName"  id="vLastName" value="<?php echo  $vLastName; ?>" placeholder="<?php echo $langage_lbl['LBL_LAST_NAME_TXT'];?>" pattern="[\D]+"  required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_ONLY_ALPHABATIC'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_EMAIL_ID_TXT'];?></label>
											<input type="email" class="driver-action-page-input " name="vEmail" onBlur="validate_email(this.value)"  id="vEmail" value="<?php echo  $vEmail; ?>" placeholder="<?php echo $langage_lbl['LBL_EMAIL_ID_TXT'];?>" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_ENTER_EMAIL'];?>')" oninput="setCustomValidity('')"<?php  if(!empty($_REQUEST['id'])){?> readonly <?php } ?>>
											<div style="float: none;margin-top: 14px;" id="emailCheck"></div>
										</span> 
									</div>
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_SELECT_IMAGE_TXT'];?></label>
											<input type="file" class="driver-action-page-input" name="vImage"  id="vImage" placeholder="<?php echo $langage_lbl['LBL_SELECT_IMAGE_TXT'];?>">
										</span> 
									</div>
								<!--<div class="col-md-6"> 
										<span>
											<label><?/*=$langage_lbl['LBL_SELECT_COUNTRY'];?></label>
											<select class="custom-select-new" name = 'vCountry' id="con" onChange="changeCode(this.value);" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_ITEM'];?>')" oninput="setCustomValidity('')">
												<option value="">--- <?php echo $langage_lbl['LBL_SELECT_COUNTRY'];?> ---</option>
												<?php for($i=0;$i<count($db_country);$i++){ ?>
													<option value = "<?php echo  $db_country[$i]['vCountryCode'] ?>" <?php if($vCountry==$db_country[$i]['vCountryCode']){?>selected<?php } ?>><?php echo  $db_country[$i]['vCountry'] ?></option>
												<?php } */?>
											</select>
										</span>
									</div> -->
									<div class="col-md-6">   
										<span class="driver-phone-number">
											<label><?php echo $langage_lbl['LBL_PHONE_NUMBER_TXT'];?></label>
											<input type="text" pattern=".{10}" class="input-phNumber1" id="code" name="vCode" value="<?php echo  $vCode ?>" readonly >
											<input name="vPhone" type="text" value="<?php echo  $vPhone; ?>" class="driver-action-page-input input-phNumber2" placeholder="<?php echo $langage_lbl['LBL_PHONE_NUMBER_TXT'];?>" pattern="[0-9]{1,}" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_ENTER_PROPER_PHONE'];?>')" oninput="setCustomValidity('')"/>
										</span>
									</div>
									<!--
									<div class="col-md-6">
										<span>       
											<label><?/*=$langage_lbl['LBL_SELECT_LANGUAGE_TEXT'];?></label>                         
											<select  class="custom-select-new" name = 'vLang' required onChange="changeCode(this.value);" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_ITEM'];?>')" oninput="setCustomValidity('')">
												<option value="">--- <?php echo $langage_lbl['LBL_SELECT_LANGUAGE_TEXT'];?> ---</option>
												<?php for ($i = 0; $i < count($db_lang); $i++) { ?>
													<option value = "<?php echo  $db_lang[$i]['vCode'] ?>" <?php echo  ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>><?php echo  $db_lang[$i]['vTitle'] ?></option>
												<?php } */?>
											</select>
										</span>
									</div>
									-->
									<div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT'];?></label>
											<input type="password" class="driver-action-page-input" name="vPassword"  id="vPassword" value="<?php echo  $vPass ?>" placeholder="<?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']; ?>" pattern=".{6,}" title="Six or more characters" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_ENTER_MIN_6'];?>')" oninput="setCustomValidity('')">
										</span> 
									</div>
                                    <div class="col-md-6">
										<span>
											<label><?php echo $langage_lbl['LBL_REFERAL_CODE'];?></label>
											<input type="text" class="driver-action-page-input" name="vRefCode"  id="vRefCode" value="<?php echo  $vPass ?>" placeholder="<?php echo $langage_lbl['LBL_REFERAL_CODE']; ?>" title="" onBlur=" validate_refercode(this.value)">
										</span> 
                                        <strong id="refercodeCheck"></strong>
									</div>
								<?php if($action == "Add"){?>
								<div class="col-md-6 driver-action1"><span>
									<b id="li_dob">
											<strong>
											<?php echo $langage_lbl['LBL_Date_of_Birth']; ?></strong>
											<select name="vDay" data="DD" class="custom-select-new" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_DATE'];?>')" onChange="setCustomValidity('')">
												<option value=""><?php echo $langage_lbl['LBL_DATE_TXT']; ?></option>
												<?php for($i=1;$i<=31;$i++) {?>
												<option value="<?php echo $i?>">
												<?php echo $i?>
												</option>
												<?php }?>
											</select>
											<select data="MM" name="vMonth" class="custom-select-new" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_MONTH'];?>')" onChange="setCustomValidity('')">
												<option value=""><?php echo $langage_lbl['LBL_MONTH_TXT']; ?></option>
												<?php for($i=1;$i<=12;$i++) {?>
												<option value="<?php echo $i?>">
												<?php echo $i?>
												</option>
												<?php }?>
											</select>
											<select data="YYYY" name="vYear" class="custom-select-new" required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_YEAR'];?>')" onChange="setCustomValidity('')">
												<option value=""><?php echo $langage_lbl['LBL_YEAR']; ?></option>
												<?php for($i=1300;$i<=jdate("Y");$i++) {?>
												<option value="<?php echo $i?>">
												<?php echo $i?>
												</option>
												<?php }?>
											</select>
										</b>
									</span></div>
								<?php } ?>
									<p>
										<input type="submit" class="save-but" name="submit" id="submit" value="<?php echo  $action_show; ?> ">
										
									</p>
									<div style="clear:both;"></div>
								</div>  
							</div>                      
						</form>
					</div>
					<div style="clear:both;"></div>
                    <?php
                    if(isset($_REQUEST['id']) && $_REQUEST['id']!= '')
                    {
                        $vehicles = $sql = "SELECT * FROM `driver_vehicle` WHERE iDriverId = '".$_REQUEST['id']."' and eStatus != 'Deleted'";
                        $db_driver_vehicle = $obj->MySQLSelect($sql);
                    } 
                    
                    if(count($vehicles) > 0) :
                    
                    ?>
					<div class="row">
					    <div class="trips-table"> 
                            <div class="trips-table-inner">
                            <div class="driver-trip-table">
                                <table width="100%" border="0" cellpadding="0" cellspacing="1" id="dataTables-example">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>نام ماشین</th>
                                            <th>رنگ</th>
                                            <th>شماره پلاک</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        for($i=0;$i<count($db_driver_vehicle);$i++) :
                                        

                                            $vLicencePlate = $db_driver_vehicle[$i]['vLicencePlate'];
                                            $iMakeId = $db_driver_vehicle[$i]['iMakeId'];
                                            $iModelId = $db_driver_vehicle[$i]['iModelId'];
                                            $iColor = $db_driver_vehicle[$i]['iColor'];
                                            $eStatus = $db_driver_vehicle[$i]['eStatus'];
                                            $eType = $db_driver_vehicle[$i]['eType'];
                                            
                                            $sql = "select vMake from make where iMakeId = '" . $iMakeId . "' AND vMake !=''";
                                            $name1 = $obj->MySQLSelect($sql);
                                            
                                            $sql = "select vTitle from model where iModelId = '" . $iModelId . "' AND vTitle !=''";
                                            $name2 = $obj->MySQLSelect($sql);
                                        
                                        if(count($name1) == 0 || count($name2) == 0 )
                                            continue;
                                        
                                            $vName = $name1[0]['vMake'] . ' ' . $name2[0]['vTitle'];
                                    ?>
                                        <tr class="gradeA">
                                            <td ><?php echo $i+1;?></td>

                                            <td width="30%"><?php echo $vName;?></td>
                                            <td>
                                                <?php echo $iColor;?>
                                            </td>
                                            <td>
                                                <?php echo $vLicencePlate;?>
                                            </td>
                                            <td>
                                                <?php echo $eStatus;?>
                                            </td>
                                            
                                        </tr>
                                    <?php endfor; ?>
                                    </tbody>
                                </table>
                            </div>	</div>
                    </div>
                    </div>
                    <div style="clear:both;"></div>
                    <?php 
                    endif;
                    ?>
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
		</script>
		<!-- End: Footer Script -->
	</body>
</html>


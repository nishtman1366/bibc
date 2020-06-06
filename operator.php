<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

//echo "<pre>";print_r($_FILES);
//echo "<pre>";print_r($_POST);exit;

$sql = "select * from country";
$db_country = $obj->MySQLSelect($sql);

//For Currency
$sql="select * from  currency where eStatus='Active'";
$db_currency=$obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$script = 'Rider';
$tbl_name = 'register_user';

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);

#echo '<prE>'; print_R($_REQUEST); echo '</pre>';
// set all variables with either post (when submit) either blank (when insert)
$vName = isset($_POST['vName']) ? $_POST['vName'] : '';
$vLastName = isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
$vEmail = isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
$vPassword = isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
$vPhone = isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
$vPhoneCode = isset($_POST['vPhoneCode']) ? $_POST['vPhoneCode'] : '';
$vCountry = isset($_POST['vCountry']) ? $_POST['vCountry'] : '';
$vCity = isset($_POST['vCity']) ? $_POST['vCity'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : 'Inactive';
$vInviteCode = isset($_POST['vInviteCode']) ? $_POST['vInviteCode'] : '';
$vImgName = isset($_POST['vImgName']) ? $_POST['vImgName'] : '';
$vCurrencyPassenger=isset($_POST['vCurrencyPassenger']) ? $_POST['vCurrencyPassenger'] : '';
$vLang = isset($_POST['vLang']) ? $_POST['vLang'] : '';
$vPass = $generalobj->encrypt($vPassword);
$vAddress = isset($_POST['vAddress']) ? $_POST['vAddress'] : '';
$vDescription = isset($_POST['vDescription']) ? $_POST['vDescription'] : '';

if (isset($_POST['submit'])) {
     //Start :: Upload Image Script
     if(!empty($id)){
       if(SITE_TYPE =='Demo'){
          header("Location:operator.php?id=".$id."&success=2");exit;
       }
          if(isset($_FILES['vImgName'])){
          //$id = $_GET['id'];
          $img_path = $tconfig["tsite_upload_images_passenger_path"];
          $temp_gallery = $img_path . '/';
          $image_object = $_FILES['vImgName']['tmp_name'];
          $image_name = $_FILES['vImgName']['name'];
          if(isset($id)){
          $check_file_query = "select iUserId,vImgName from register_user where iUserId=" . $id;
          $check_file = $obj->MySQLSelect($check_file_query);
          if ($image_name != "") {
               $check_file['vImgName'] = $img_path . '/' . $id . '/' . $check_file[0]['vImgName'];

               if ($check_file['vImgName'] != '' && file_exists($check_file['vImgName'])) {
                      unlink($img_path . '/' . $id. '/' . $check_file[0]['vImgName']);
                      unlink($img_path . '/' . $id. '/1_' . $check_file[0]['vImgName']);
                      unlink($img_path . '/' . $id. '/2_' . $check_file[0]['vImgName']);
                      unlink($img_path . '/' . $id. '/3_' . $check_file[0]['vImgName']);
               }

               $filecheck = basename($_FILES['vImgName']['name']);
               $fileextarr = explode(".", $filecheck);
               $ext = strtolower($fileextarr[count($fileextarr) - 1]);
               $flag_error = 0;
               if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $flag_error = 1;
                    $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
               }
              /* if ($_FILES['vImgName']['size'] > 1048576) {
                    $flag_error = 1;
                    $var_msg = "Image Size is too Large";
               }*/
               if ($flag_error == 1) {
                    $generalobj->getPostForm($_POST, $var_msg, "operator?success=0&var_msg=" . $var_msg);
                    exit;
               } else {

                    $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                         mkdir($Photo_Gallery_folder, 0777);
                    }
                    $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '','','', '', '', '', 'Y', '', $Photo_Gallery_folder);

					if($img1!=''){
					if(is_file($Photo_Gallery_folder.$img1))
					{
					   include_once(TPATH_CLASS."/SimpleImage.class.php");
					   $img = new SimpleImage();
					   list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$img1);
					   if($width < $height){
						  $final_width = $width;
					   }else{
						  $final_width = $height;
					   }
					   $img->load($Photo_Gallery_folder.$img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$img1);
					   $img1 = $generalobj->img_data_upload($Photo_Gallery_folder,$img1,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],"");
					}
					}
					$vImgName = $img1;
               }
          }else{
                    $vImgName = $check_file[0]['vImgName'];
          }
          }
     }
     }
//End :: Upload Image Script
	if(!empty($id)){
		$SQL1 = "SELECT 'vName' FROM $tbl_name WHERE vEmail = '$vEmail' AND iUserId != '$id'";
		$email_exist = $obj->MySQLSelect($SQL1);
		if(count($email_exist) > 0) {
			$var_msg = "Email Id Already Exist,Select Another.";
			$generalobj->getPostForm($_POST, $var_msg, "operator?success=0&var_msg=" . $var_msg);
			exit;
		}
	}else {
		$SQL1 = "SELECT 'vName' FROM $tbl_name WHERE vEmail = '$vEmail'";
		$email_exist = $obj->MySQLSelect($SQL1);
		if(count($email_exist) > 0) {
			$var_msg = "Email Id Already Exist,Select Another.";
			//$generalobj->getPostForm($_POST, $var_msg, "operator?success=0&var_msg=" . $var_msg);
			header("location:operator?success=0&var_msg=" . $var_msg);
			exit;
		}
	}

     $q = "INSERT INTO ";
     $where = '';

     if ($id != '') {
          $q = "UPDATE ";
          $where = " WHERE `iUserId` = '" . $id . "'";
     }


     $query = $q . " `" . $tbl_name . "` SET
			`vName` = '" . $vName . "',
			`vLastName` = '" . $vLastName . "',
			`vEmail` = '" . $vEmail . "',
			`vPassword` = '" . $vPass . "',
			`vPhone` = '" . $vPhone . "',
			`vCountry` = '" . $vCountry . "',
			`vPhoneCode` = '" . $vPhoneCode . "',
			`eStatus` = '" . $eStatus . "',
			`vImgName` = '" . $vImgName . "',
			`vLang` = '" . $vLang . "',
      `vDescription` = '" . $vDescription . "',
      `vAddress` = '" . $vAddress . "',
			`vCurrencyPassenger`='" . $vCurrencyPassenger . "',
			`vInviteCode` = '" . $vInviteCode . "'" . $where;
     $obj->sql_query($query);
      if (mysql_insert_id() != '') {
          $id = mysql_insert_id();
          //Start :: Upload Image Script
          //echo "<pre>";print_r($_FILES['vImgName']);exit;
          if($_FILES['vImgName']['name']!=''){
          //$id = $_GET['id'];
          $img_path = $tconfig["tsite_upload_images_passenger_path"];
          $temp_gallery = $img_path . '/';
          $image_object = $_FILES['vImgName']['tmp_name'];
          $image_name = $_FILES['vImgName']['name'];

               $filecheck = basename($_FILES['vImgName']['name']);
               $fileextarr = explode(".", $filecheck);
               $ext = strtolower($fileextarr[count($fileextarr) - 1]);
               $flag_error = 0;
               if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp") {
                    $flag_error = 1;
                    $var_msg = "Not valid image extension of .jpg, .jpeg, .gif, .png";
               }
               /*if ($_FILES['vImgName']['size'] > 1048576) {
                    $flag_error = 1;
                    $var_msg = "Image Size is too Large";
               }*/
               if ($flag_error == 1) {
                    $generalobj->getPostForm($_POST, $var_msg, "operator?success=0&var_msg=" . $var_msg);
                    exit;
               } else {

                    $Photo_Gallery_folder = $img_path . '/' . $id . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                         mkdir($Photo_Gallery_folder, 0777);
                    }
                   $img1 = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, '','','', '', '', '', 'Y', '', $Photo_Gallery_folder);

					if($img1!=''){
					if(is_file($Photo_Gallery_folder.$img1))
					{
					   include_once(TPATH_CLASS."/SimpleImage.class.php");
					   $img = new SimpleImage();
					   list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$img1);
					   if($width < $height){
						  $final_width = $width;
					   }else{
						  $final_width = $height;
					   }
					   $img->load($Photo_Gallery_folder.$img1)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$img1);
					   $img1 = $generalobj->img_data_upload($Photo_Gallery_folder,$img1,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],"");
					}
					}
					$vImgName = $img1;

                    $sql = "UPDATE ".$tbl_name." SET `vImgName` = '" . $vImgName . "' WHERE `iUserId` = '" . $id . "'";
                     $obj->sql_query($sql);
               }

     }
//End :: Upload Image Script
      $id = ($id != '') ? $id : mysql_insert_id();


      header("Location:operator.php?id=" . $id . '&success=1');exit;
      }
       header("Location:operator.php?id=" . $id . '&success=1');exit;

}
// for Edit
if ($action == 'Edit') {
     $sql = "SELECT * FROM " . $tbl_name . " WHERE iUserId = '" . $id . "'";
     $db_data = $obj->MySQLSelect($sql);
     //echo "<pre>";print_R($db_data);echo "</pre>";

     $vLabel = $id;
     if (count($db_data) > 0) {
          foreach ($db_data as $key => $value) {
               $vName = $value['vName'];
               $vLastName = $value['vLastName'];
               $vEmail = $generalobjAdmin->clearEmail($value['vEmail']);
               $vPassword = $value['vPassword'];
               $vPass = $generalobj->decrypt($vPassword);
               $vPhone = $value['vPhone'];
               $vPhoneCode = $value['vPhoneCode'];
               $vCountry = $value['vCountry'];
               $vInviteCode = $value['vInviteCode'];
               $eStatus = $value['eStatus'];
               $vImgName = $value['vImgName'];
               $vDescription = $value['vDescription'];
               $vAddress = $value['vAddress'];
               $vCurrencyPassenger=$value['vCurrencyPassenger'];
			   $vLang = $value['vLang'];
          }
     }
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          
          <title>Admin | <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?>  <?php echo  $action; ?></title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

          <?php include_once('global_files.php');?>
          <!-- On OFF switch -->
          <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
          <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
     </head>
     <!-- END  HEAD-->
     <!-- BEGIN BODY-->
     <body class="padTop53 " >

          <!-- MAIN WRAPPER -->
          <div id="wrap">
               <?php include_once('header.php'); ?>
               <?php include_once('left_menu.php'); ?>
               <!--PAGE CONTENT -->
               <div id="content">
                    <div class="inner">
                         <div class="row">
                              <div class="col-lg-12">
                                   <h2><?php echo  $action; ?> <?php echo $langage_lbl_admin['LBL_EDIT_RIDERS_TXT_ADMIN'];?> <?php echo  $vName; ?> <?php echo  $vLastName; ?></h2>
                                   <a href="rider.php">
                                        <input type="button" value="بازگشت" class="add-btn">
                                   </a>
                              </div>
                         </div>
                         <hr />
                         <?php if ($success == 2) { ?>
               						<div class="alert alert-danger alert-dismissable msgs_hide">
               								 <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
               								 "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
               						</div><br/>
               					<?php } ?>
                         <div class="body-div">
                              <div class="form-group">
                                   <?php if($success == 1) { ?>
                                   <div class="alert alert-success alert-dismissable msgs_hide">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
                                        سوار با موفقیت ویرایش شد
                                   </div><br/>
                                   <?php } ?>
                                   <form method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" id="u_id" name="id" value="<?php echo  $id; ?>"/>
                                         <?php if($id){?>
                                        <div class="row" id="hide-profile-div">
                                             <div class="col-lg-4">
                                                  <b><?php if ($vImgName == 'NONE' || $vImgName == '') { ?>
                                                                 <img src="../assets/img/profile-user-img.png" alt="">
                                                            <?}else{?>
                                                                 <img src = "<?php echo $tconfig["tsite_upload_images_passenger"]. '/' .$id. '/3_' .$vImgName ?>" style="height:150px;"/>
                                                            <?}?>
                                                       </b>
                                             </div>
                                        </div>
                                        <?php } ?>

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>نام <span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text" class="form-control" name="vName"  id="vName" value="<?php echo  $vName; ?>" placeholder="First Name" required>
                                             </div>
                                        </div>
                                        <div class="row">
											                       <div class="col-lg-12">
                                                  <label>نام خانوادگی <span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text" class="form-control" name="vLastName"  id="vLastName" value="<?php echo  $vLastName; ?>" placeholder="Last Name" required>
                                             </div>
                                        </div>

                                        <!-- Mehrshad added -->
                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>آدرس <span class="red"> </span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text" class="form-control" name="vAddress"  id="vAddress" value="<?php echo  $vAddress; ?>" placeholder="Address">
                                             </div>
                                        </div>
                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>توضیحات <span class="red"> </span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text" class="form-control" name="vDescription"  id="vDescription" value="<?php echo  $vDescription; ?>" placeholder="Description">
                                             </div>
                                        </div>
                                        <!-- Mehrshad added -->

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>ایمیل <span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="email" class="form-control" name="vEmail"  id="vEmail" value="<?php echo  $vEmail; ?>" onChange="return validate_email(this.value)" placeholder="Email" required=""/>

                                             </div>
											 <label id="emailCheck"><label>
                                        </div>
										<div class="row">
                                             <div class="col-lg-12">
                                                  <label>رمز عبور<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text" class="form-control" name="vPassword"  id="vPassword" value="<?php echo  $vPass ?>" placeholder="Password" required>
                                             </div>
                                        </div>

										<div class="row">
                                             <div class="col-lg-12">
                                                  <label>تصویر پروفایل</label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="file" class="form-control" name="vImgName"  id="vImgName" placeholder="Name Label">
                                             </div>
                                        </div>

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>کشور <span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <select class="form-control" name = 'vCountry' onChange="changeCode(this.value);" required>
                                                       <option value="">--select--</option>
                                                       <?php for($i=0;$i<count($db_country);$i++){ ?>
                                                       <option value = "<?php echo  $db_country[$i]['vCountryCode'] ?>" <?php if($vCountry==$db_country[$i]['vCountryCode']){?>selected<?php } ?>><?php echo  $db_country[$i]['vCountry'] ?></option>
                                                       <?php } ?>
                                                  </select>
                                             </div>
                                        </div>

                                        <div class="row">
                                             <div class="col-lg-12" style="width:30%">
                                                  <label>شماره تلفن همراه<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6"  style="width:50%">
												 <input type="text" class="form-select-2" id="code" name="vPhoneCode" value="<?php echo  $vPhoneCode ?>">
                                                  <input type="text" pattern = "[0-9]{1,}" title="Please enter proper mobile number."  class="mobile-text" name="vPhone"  id="vPhone" value="<?php echo  $vPhone; ?>" placeholder="Phone" required="">
                                             </div>
                                        </div>

                                        <!--<div class="row">
                                             <div class="col-lg-12">
                                                  <label>Promotional Code</label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text" class="form-control" name="vInviteCode"  id="vInviteCode" value="<?php echo  $vInviteCode; ?>" placeholder="Promotional Code">
                                             </div>
                                        </div>-->
                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>وضعیت</label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <div class="make-switch" data-on="success" data-off="warning">
                                                       <input type="checkbox" name="eStatus" id="eStatus" <?php echo  ($id != '' && $eStatus == 'Inactive') ? '' : 'checked'; ?> value="1"/>
                                                  </div>
                                             </div>
                                        </div>
										<?php
										if(count($db_lang) <=1){ ?>
										<input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode'];?>"/>

										<?php }else{ ?>
										 <div class="row">
                                             <div class="col-lg-12">
                                                  <label>زبان<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <select  class="form-control" name = 'vLang' required>
                                                       <option value="">--select--</option>
                                                       <?php for ($i = 0; $i < count($db_lang); $i++) { ?>
                                                       <option value = "<?php echo  $db_lang[$i]['vCode'] ?>" <?php echo  ($db_lang[$i]['vCode'] == $vLang) ? 'selected' : ''; ?>><?php echo  $db_lang[$i]['vTitle'] ?> </option>
                                                       <?php } ?>
                                                  </select>
                                             </div>
                                        </div>

										<?php }

										?>

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>واحد پول <span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <select class="form-control" name = 'vCurrencyPassenger' required>
                                                       <option value="">--select--</option>
                                                       <?php for($i=0;$i<count($db_currency);$i++){ ?>
                                                       <option value = "<?php echo  $db_currency[$i]['vName'] ?>" <?php if($vCurrencyPassenger==$db_currency[$i]['vName']){?>selected<?php } ?>><?php echo  $db_currency[$i]['vName'] ?></option>
                                                       <?php } ?>
                                                  </select>
                                             </div>
                                        </div>

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <input type="submit" class="save btn-info" name="submit" id="submit" value="<?php echo  $action; ?> Rider">
                                             </div>
                                        </div>
                                   </form>
                              </div>
                         </div>
                    </div>
               </div>
               <!--END PAGE CONTENT -->
          </div>
          <!--END MAIN WRAPPER -->


          <?php include_once('footer.php');?>
          <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
          <script>
          var successMSG1 = '<?php echo $success;?>';

                    if(successMSG1 != ''){
                         setTimeout(function() {
                            $(".msgs_hide").hide(1000)
                        }, 5000);
                    }
											function validate_email(id)
                                                       {
															var uid=$("#u_id").val();
                                                            var request = $.ajax({
                                                                 type: "POST",
                                                                 url: '../ajax_rider_email.php',
                                                                 data: 'id=' +id+"&uid="+uid,
                                                                 success: function (data)
                                                                 {
																	if(data==0)
																	{
                                                                      $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Invalid Email,Already Exist</i>');
																	 $('input[type="submit"]').attr('disabled','disabled');

																	 return false;
																	}
																	else if(data==1 || data ==2)
																	{
																		var eml=/^[-.0-9a-zA-Z]+@[a-zA-z]+\.[a-zA-z]{2,3}$/;
																		result=eml.test(id);
																		if(result==true)
																		{
																		$('#emailCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
																		$('input[type="submit"]').removeAttr('disabled');
																		}
																		else
																		{
																			$('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> Enter Proper Email</i>');
																			 $('input[type="submit"]').attr('disabled','disabled');
																			  return false;
																		}
																	}
                                                                 }
                                                            });
                                                       }

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
          </script>
     </body>
     <!-- END BODY-->
</html>

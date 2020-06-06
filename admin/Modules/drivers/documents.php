<?php
include_once('../common.php');
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

$sql = "select * from country";
$db_country = $obj->MySQLSelect($sql);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? 'Edit' : 'Add';
$script = 'Driver';
$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);

$sql = "select * from register_driver where iDriverId = '" . $_REQUEST['id'] . "'";
$db_user = $obj->MySQLSelect($sql);
$vName=$db_user[0]['vName'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] :'';
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';

if ($action == 'noc') {

    if(SITE_TYPE == 'Demo')
    {
      header("location:driver_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
      exit;
    }

     if (isset($_POST['doc_path'])) {
        $doc_path = $_POST['doc_path'];
     }
     $temp_gallery = $doc_path . '/';
     $image_object = $_FILES['noc']['tmp_name'];
     $image_name = $_FILES['noc']['name'];

	 if($image_name=="")
		{
			$var_msg="Please Upload valid file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
			header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			exit;
		}

     if ($image_name != "") {
          $check_file_query = "select iDriverId,vNoc from register_driver where iDriverId=" . $_REQUEST['id'];
          $check_file = $obj->sql_query($check_file_query);
          $check_file['vNoc'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vNoc'];


         /* if ($check_file['vNoc'] != '' && file_exists($check_file['vNoc'])) {
               unlink($doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vNoc']);
               unlink($doc_path . '/' . $_REQUEST['id'] . '/1_' . $check_file[0]['vNoc']);
               unlink($doc_path . '/' . $_REQUEST['id'] . '/2_' . $check_file[0]['vNoc']);
          }*/

          $filecheck = basename($_FILES['noc']['name']);
          $fileextarr = explode(".", $filecheck);
          $ext = strtolower($fileextarr[count($fileextarr) - 1]);
          $flag_error = 0;
          if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
               $flag_error = 1;
               $var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
          }
         /* if ($_FILES['noc']['size'] > 1048576) {
               $flag_error = 1;
               $var_msg = "Image Size is too Large";
          }*/
          if ($flag_error == 1) {
              header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
               $generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=".$var_msg);

               exit;

          } /*else if($_REQUEST['id'] != '') {
		  header("location:driver_document_action.php?success=0&var_msg=something went wrong. Try again");

               exit;
		  }*/ else {
                    $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                         mkdir($Photo_Gallery_folder, 0777);
                    }
                    //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                    $vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
                    $vImage = $vFile[0];
                    $var_msg = "NOC File uploaded successfully";
                    $tbl = 'register_driver';
                    $sql = "SELECT * FROM " . $tbl . " WHERE iDriverId = '" .  $_REQUEST['id'] . "'";
                    $db_data = $obj->MySQLSelect($sql);
                    $q = "INSERT INTO ";
                    $where = '';

                    if (count($db_data) > 0) {
                         $q = "UPDATE ";
                         $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
                    }
                    $query = $q . " `" . $tbl . "` SET `vNoc` = '" . $vImage . "'" . $where ;
                    $obj->sql_query($query);

                    //Start :: Log Data Save
                         if(empty($check_file[0]['vNoc'])){ $vNocPath = $vImage ; }else{ $vNocPath = $check_file[0]['vNoc']; }
                         $generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'company','noc',$vNocPath);
                    //End :: Log Data Save

                   // Start :: Status in edit a Document upload time
                      // $set_value = "`eStatus` ='inactive'";
                       //$generalobj->estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
                    // End :: Status in edit a Document upload time
                        header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);

          }
     }
}

if ($action == 'certi') {

  if(SITE_TYPE == 'Demo')
  {
    header("location:driver_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
    exit;
  }

     if (isset($_POST['doc_path'])) {
          $doc_path = $_POST['doc_path'];
     }
     $temp_gallery = $doc_path . '/';
     $image_object = $_FILES['certi']['tmp_name'];
     $image_name = $_FILES['certi']['name'];

	  if($image_name=="")
		{
			$var_msg="Please Upload valid file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
			header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			exit;
		}

     if ($image_name != "") {
          $check_file_query = "select iDriverId,vCerti from register_driver where iDriverId=" . $_REQUEST['id'];
          $check_file = $obj->sql_query($check_file_query);
          $check_file['vCerti'] = $doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vCerti'];

         /* if ($check_file['vCerti'] != '' && file_exists($check_file['vCerti'])) {
               unlink($doc_path . '/' . $_REQUEST['id'] . '/' . $check_file[0]['vCerti']);
               unlink($doc_path . '/' . $_REQUEST['id'] . '/1_' . $check_file[0]['vCerti']);
               unlink($doc_path . '/' . $_REQUEST['id'] . '/2_' . $check_file[0]['vCerti']);
          }*/

          $filecheck = basename($_FILES['certi']['name']);
          $fileextarr = explode(".", $filecheck);
          $ext = strtolower($fileextarr[count($fileextarr) - 1]);
          $flag_error = 0;
           if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
               $flag_error = 1;
               $var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
          }
         /* if ($_FILES['certi']['size'] > 1048576) {
               $flag_error = 1;
               $var_msg = "Image Size is too Large";
          }*/
          if ($flag_error == 1) {
            header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
               $generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
               exit;
          } else {
                    $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                         mkdir($Photo_Gallery_folder, 0777);
                    }
                    //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                    $vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
                    $vImage = $vFile[0];
                    $var_msg = "Certificate File uploaded successfully";
                    $tbl = 'register_driver';
                    $sql = "SELECT * FROM " . $tbl . " WHERE iDriverId = '" .$_REQUEST['id']. "'";
                    $db_data = $obj->MySQLSelect($sql);
                    $q = "INSERT INTO ";
                    $where = '';

                    if (count($db_data) > 0) {
                         $q = "UPDATE ";
                         $where = " WHERE `iDriverId` = '" .$_REQUEST['id']. "'";
                    }
                    $query = $q . " `" . $tbl . "` SET `vCerti` = '" . $vImage . "'".$where;
                    $obj->sql_query($query);

                    //Start :: Log Data Save
                         if(empty($check_file[0]['vCerti'])){ $vCertiPath = $vImage ; }else{ $vCertiPath = $check_file[0]['vCerti']; }
                         $generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'company','certificate',$vCertiPath);
                    //End :: Log Data Save

                    // Start :: Status in edit a Document upload time
                       //$set_value = "`eStatus` ='inactive'";
                       //$generalobj->estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
                    // End :: Status in edit a Document upload time

                    header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
          }
     }
}

if ($action == 'licence') {

  if(SITE_TYPE == 'Demo')
  {
    header("location:driver_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
    exit;
  }
     if (isset($_POST['doc_path'])) {
          $doc_path = $_POST['doc_path'];
		   $expDate=$_POST['dLicenceExp'];
     }
     $temp_gallery = $doc_path . '/';
     $image_object = $_FILES['licence']['tmp_name'];
    $image_name = $_FILES['licence']['name'];

	  if($image_name=="")
		{
			$tbl = 'register_driver';
			$q = "UPDATE ";
			$where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";

			 $check_file_query2 = "select iDriverId,vLicence,dLicenceExp from register_driver where iDriverId=" . $_REQUEST['id'];
			$db_licence = $obj->sql_query($check_file_query2);



			if($db_licence[0]['dLicenceExp']==$expDate)
			 {
				 $var_msg = "Licence Document uploaded successfully";

			}
			else
			{
				 if ($image_name != "") {
				$filecheck = basename($_FILES['licence']['name']);
				 $fileextarr = explode(".", $filecheck);
				$ext = strtolower($fileextarr[count($fileextarr) - 1]);
				  $var_msg1  = '';

				  if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
					   //$flag_error = 1;
					 $var_msg1 = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
				  }else{

				   $var_msg1 = "Licence Document uploaded successfully";

				  }
				 }
				$var_msg="Licence Expire date Updated". $var_msg1;
				 $query = $q . " `" . $tbl . "` SET
					`dLicenceExp` = '".$_POST['dLicenceExp']."'  " . $where;
				$obj->sql_query($query);
			}

			header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			exit;
		}


     if ($image_name != "") {
          $check_file_query = "select iDriverId,vLicence from register_driver where iDriverId=" . $_REQUEST['id'];
          $check_file = $obj->sql_query($check_file_query);
          $check_file['vLicence'] = $doc_path . '/' . $_REQUEST['id']. '/' . $check_file[0]['vLicence'];

         /* if ($check_file['vLicence'] != '' && file_exists($check_file['vLicence'])) {
               unlink($doc_path . '/' .$_REQUEST['id'] . '/' . $check_file[0]['vLicence']);
               unlink($doc_path . '/' . $_REQUEST['id'] . '/1_' . $check_file[0]['vLicence']);
               unlink($doc_path . '/' . $_REQUEST['id'] . '/2_' . $check_file[0]['vLicence']);
          }*/

          $filecheck = basename($_FILES['licence']['name']);
          $fileextarr = explode(".", $filecheck);
          $ext = strtolower($fileextarr[count($fileextarr) - 1]);
          $flag_error = 0;

		   $check_file_query2 = "select iDriverId,vLicence,dLicenceExp from register_driver where iDriverId=" . $_REQUEST['id'];
			$db_licence = $obj->sql_query($check_file_query2);

			if($db_licence[0]['dLicenceExp']!=$expDate)
			{
				$var_msg1 ='';
				 if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {

					$var_msg1 = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
				}else{
				 $var_msg1 = "Licence uploaded successfully";

				}
				$var_msg1 ="Licence Expire date Updated ". $var_msg1;

			}else{
			$var_msg1 = "Licence uploaded successfully";
			}


          if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
               $flag_error = 1;
          }
          if ($flag_error == 1) {
			 $var_msg = "You have selected wrong file format for Image. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png";
			header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
			exit;
             //  $generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
               exit;
          } else {
              $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id']. '/';
               if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777);
               }

               //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
               $vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");

               $vImage = $vFile[0];
               $var_msg = "Licence uploaded successfully";
               $tbl = 'register_driver';
               $sql = "SELECT * FROM " . $tbl . " WHERE iDriverId = '" . $_REQUEST['id'] . "'";
               $db_data = $obj->MySQLSelect($sql);
               $q = "INSERT INTO ";
               $where = '';

               if (count($db_data) > 0) {
                    $q = "UPDATE ";
                    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
               }
               $query = $q . " `" . $tbl . "` SET `vLicence` = '" . $vImage . "',`dLicenceExp` = '".$_POST['dLicenceExp']."'".$where;
               $obj->sql_query($query);

              //Start :: Log Data Save
                         if(empty($check_file[0]['vLicence'])){ $vLicencePath = $vImage ; }else{ $vLicencePath = $check_file[0]['vLicence']; }
                         $generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'company','licence',$vLicencePath);
              //End :: Log Data Save

              // Start :: Status in edit a Document upload time
                      // $set_value = "`eStatus` ='inactive'";
                       //$generalobj->estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
               // End :: Status in edit a Document upload time

               header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg1);
          }
     }
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title><?php echo $SITE_NAME?> | Driver <?php echo  $action; ?></title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <meta content="" name="keywords" />
          <meta content="" name="description" />
          <meta content="" name="author" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

          <?php  include_once('global_files.php'); ?>
          <!-- On OFF switch -->
          <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
          <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
          <link rel="stylesheet" href="../assets/css/bootstrap-fileupload.min.css" >
		   <script src="../	assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
     </head>
     <!-- END  HEAD-->
     <!-- BEGIN BODY-->
     <body class="padTop53 " >

          <!-- MAIN WRAPPER -->
          <div id="wrap">
               <?
               include_once('header.php');
               ?>
               <?
               include_once('left_menu.php');
               ?>
               <!--PAGE CONTENT -->
               <div id="content">
                    <div class="inner">
                         <div class="row">
                              <div class="col-lg-12">
                                   <h2>تغییر مستندات <?php echo  $vName; ?></h2>
                                   <a href="driver.php?type=<?php echo $_REQUEST['type']?>">
                                        <input type="button" value="بازگشت به لیست" class="add-btn">
                                   </a>
                              </div>
                         </div>
                         <hr />
                         <div class="body-div">
                              <div class="form-group">
                                   <?php if ($success == 1) {?>
                                   <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                         <?php echo  $var_msg; ?>
                                   </div><br/>
                                   <?} ?>

                                   <?php if ($success == 2) {?>
                                   <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                         "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                                   </div><br/>
                                   <?} ?>
                                   <input type="hidden" name="id" value="<?php echo  $id; ?>"/>
                                   <div class="row">
                                        <div class="col-sm-12">
                                             <h4 style="margin-top:0px;">مدارک</h4>
                                        </div>
                                   </div>
                                   <div class="row company-document-action">

                                      <?php if($APP_TYPE != 'UberX'){?>

                                        <div class="col-lg-3">
                                             <div class="panel panel-default upload-clicking">
                                                  <div class="panel-heading">گواهینامه</div>
                                                  <div class="panel-body">
                                                       <?php if ($db_user[0]['vLicence'] != '') { ?>
                                                       <?php $file_ext = $generalobj->file_ext($db_user[0]['vLicence']);
                                                                      if($file_ext == 'is_image'){ ?>
                                                                           <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vLicence'] ?>" style="width:200px;cursor:pointer;" alt ="YOUR DRIVING LICENCE" data-toggle="modal" data-target="#myModallicence"/>
                                                                      <?php }else{ ?>
                                                                           <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vLicence']  ?>" target="_blank">گواهینامه</a>
                                                                      <?php } ?>
                                                       <?php } else { ?>
                                                            یافت نشد
                                                       <?php } ?>
                                                       <b><button class="btn btn-info" data-toggle="modal" data-target="#uiModal" <?php if(SITE_TYPE=='Demo') echo "disabled";?>>

                                                            <?php if ($db_user[0]['vLicence'] != '') {
                                                              echo $langage_lbl['LBL_UPDATE_LICENCE'];

                                                              }else{
                                                                echo $langage_lbl['LBL_ADD_LICENCE'];


                                                                }?>
                                                       </button></b>
                                                  </div>
                                             </div>
                                        </div>
                                        <?php }?>

                                        <div class="col-lg-3">
                                             <div class="panel panel-default upload-clicking">
                                                  <div class="panel-heading">
                                                       کارت ماشین
                                                  </div>
                                                  <div class="panel-body">
                                                       <?php if ($db_user[0]['vNoc'] != '') { ?>
                                                            <?php $file_ext = $generalobj->file_ext($db_user[0]['vNoc']);
                                                            if($file_ext == 'is_image'){ ?>
                                                                 <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vNoc'] ?>" style="width:200px;" alt ="یافت نشد"/>



														                                  <?php }else{ ?>
                                                                 <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vNoc']  ?>" target="_blank">فایل</a>
                                                            <?php } ?>
                                                       <?php } else { ?>
                                                            نیاز به آپلود</br>
                                                       <?php } ?>
                                                       <b>
                                                       <button class="btn btn-info" data-toggle="modal" data-target="#uiModal_2" <?php if(SITE_TYPE=='Demo') echo "disabled";?>>

                                                           <?php if ($db_user[0]['vNoc'] != '') {

                                                            echo $langage_lbl['LBL_UPDATE_NOC'];
                                                           }else{

                                                            echo $langage_lbl['LBL_ADD_NOC'];

                                                            }?>
                                                       </button>
                                                       </b>
                                                  </div>
                                             </div>
                                        </div>

                                        <div class="col-lg-3">
                                             <div class="panel panel-default upload-clicking">
                                                  <div class="panel-heading">
                                                       کارت ملی
                                                  </div>
                                                  <div class="panel-body">
                                                       <?php if ($db_user[0]['vCerti'] != '') { ?>

                                                            <?php $file_ext = $generalobj->file_ext($db_user[0]['vCerti']);
                                                            if($file_ext == 'is_image'){ ?>
                                                                 <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vCerti'] ?>" style="width:200px;" alt ="یافت نشد"/>
                                                            <?php }else{ ?>
                                                                 <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vCerti']  ?>" target="_blank">فایل</a>
                                                            <?php } ?>

                                                       <?php } else { ?>
                                                            نیاز به آپلود
                                                       <?php } ?>
                                                       <b>
                                                       <button class="btn btn-info" data-toggle="modal" data-target="#uiModal_3" <?php if(SITE_TYPE=='Demo') echo "disabled";?>>

                                                            <?php if ($db_user[0]['vCerti'] != '') {

                                                              echo $langage_lbl['LBL_UPDATE_CERTI'];
                                                             }else{
                                                              echo $langage_lbl['LBL_ADD_CERTI'];


                                                              }?>
                                                       </button>
                                                       </b>
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="col-lg-12">
                                             <div class="modal fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                  <div class="modal-content image-upload-1">
                                                       <div class="upload-content">
                                                            <h4>گواهینامه</h4>
                                                            <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="driver_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm6">
                                                                 <input type="hidden" name="action" value ="licence"/>
                                                                 <input type="hidden" name="doc_path" value =" <?php echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <div class="fileupload fileupload-new" data-provides="fileupload">
                                                                                <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px; ">
                                                                                     <?php if ($db_user[0]['vLicence'] == '') { ?>
                                                                                          تصویر
                                                                                     <?php } else { ?>
                                                                                          <?php $file_ext = $generalobj->file_ext($db_user[0]['vLicence']);
                                                                                          if($file_ext == 'is_image'){ ?>
                                                                                               <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vLicence'] ?>" style="width:200px;" alt ="یافت نشد"/>
                                                                                          <?php }else{ ?>
                                                                                               <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vLicence']  ?>" target="_blank">فایل</a>
                                                                                          <?php } ?>
                                                                                     <?php } ?>
                                                                                </div>
                                                                                <div>
                                                                                     <span class="btn btn-file btn-success"><span class="fileupload-new">آپلود</span>
                                                                                          <span class="fileupload-exists">تغییر</span><input type="file" name="licence" /></span>
                                                                                     <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">حذف</a>
                                                                                </div>
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                                 تاریخ اتمام<br>
                                                                 <div class="col-lg-13">
                                                                      <div class="input-group input-append date" id="dp3" data-date="" data-date-format="yyyy-mm-dd">
                                                                           <input class="form-control" type="text" name="dLicenceExp" value="<?php echo isset($db_user[0]['dLicenceExp']) ? $db_user[0]['dLicenceExp'] : ' '; ?>" readonly="" />
                                                                           <span class="input-group-addon add-on"><i class="icon-calendar"></i></span>
                                                                      </div>
                                                                 </div>
																 <?php ?>
                                                                 <input type="submit" class="save" name="save" value="ذخیره">
                                                                 <input type="button" class="cancel" data-dismiss="modal" name="cancel" value="لفو">
                                                            </form>
                                                       </div>
                                                  </div>
                                             </div>
                                        </div>
                                        <div class="col-lg-12">
                                             <div class="modal fade" id="uiModal_2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                  <div class="modal-content image-upload-1">
                                                       <div class="upload-content">
                                                            <h4>کارت ماشین</h4>
                                                            <form class="form-horizontal" id="frm7" method="post" enctype="multipart/form-data" action="driver_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm7">
                                                                 <input type="hidden" name="action" value ="noc"/>
                                                         <input type="hidden" name="doc_path" value ="<?php echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
                                                                 <div class="form-group">
                          <div class="col-lg-12">
             <div class="fileupload fileupload-new" data-provides="fileupload">
           <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px; ">
                                                   <?php if ($db_user[0]['vNoc'] == '') { ?>
                                                        تصویر
                                                   <?php } else { ?>
                                                         <?php $file_ext = $generalobj->file_ext($db_user[0]['vNoc']);
                                                        if($file_ext == 'is_image'){ ?>
                                                             <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vNoc'] ?>" style="width:200px;" alt ="NOC not found"/>
                                                        <?php }else{ ?>
                                                             <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vNoc']  ?>" target="_blank">فایل</a>
                                                        <?php } ?>
                                                   <?php } ?>
                                              </div>
                                              <div>
                                                   <span class="btn btn-file btn-success"><span class="fileupload-new">آپلود</span><span class="fileupload-exists">تغییر</span><input type="file" name="noc"/></span>
                                                   <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">حذف</a>
                                              </div>
                                         </div>
                                    </div>
                               </div>
                               <input type="submit" class="save" name="save" value="ذخیره">
                               <input type="button" class="cancel" data-dismiss="modal" name="cancel" value="لفو">
                          </form>
                     </div>
                </div>
           </div>
                                        </div>
                                        <div class="col-lg-12">
                                             <div class="modal fade" id="uiModal_3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                  <div class="modal-content image-upload-1">
                                                       <div class="upload-content">
                                                            <h4>کارت ملی</h4>
                                                            <form class="form-horizontal" id="frm9" method="post" enctype="multipart/form-data" action="driver_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm9">
                                                                 <input type="hidden" name="action" value ="certi"/>
                                                                 <input type="hidden" name="doc_path" value ="<?php echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
                                                                 <div class="form-group">
                                                                      <div class="col-lg-12">
                                                                           <div class="fileupload fileupload-new" data-provides="fileupload">
                                                                                <div class="fileupload-preview thumbnail" style="width: 200px; height: 150px; ">
                                                                                     <?php if ($db_user[0]['vCerti'] == '') { ?>
                                                                                          تصویر
                                                                                     <?php } else { ?>
                                                                                          <?php $file_ext = $generalobj->file_ext($db_user[0]['vCerti']);
                                                                                          if($file_ext == 'is_image'){ ?>
                                                                                               <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vCerti'] ?>" style="width:200px;" alt ="NOC not found"/>
                                                                                          <?php }else{ ?>
                                                                                               <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vCerti']  ?>" target="_blank">کارت ماشین</a>
                                                                                          <?php } ?>
                                                                                     <?php } ?>
                                                                                </div>
                                                                                <div>
                                                                                     <span class="btn btn-file btn-success"><span class="fileupload-new">آپلود</span>
                                                                                          <span class="fileupload-exists">تغییر</span><input type="file" name="certi"/></span>
                                                                                     <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">حذف</a>
                                                                                </div>
                                                                           </div>
                                                                      </div>
                                                                 </div>
                                                                 <input type="submit" class="save" name="save" value="ذخیره" ><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="لغو">


                                                            </form>

                                                       </div>
                                                  </div>
                                             </div>
                                        </div>
                                   </div>
                              </div>
                         </div>
                    </div>
               </div>
          </div>
          <!--END PAGE CONTENT -->
     </div>
     <!--END MAIN WRAPPER -->

<!-- Modal -->
<div class="modal fade" id="myModallicence" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">YOUR DRIVING LICENCE</h4>
            </div>
            <div class="modal-body">
                <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $db_user[0]['vLicence'] ?>" style="width:500px;" alt ="" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!--<button type="button" class="btn btn-primary">Save changes</button>  -->
            </div>
        </div>
    </div>
</div>

     <?php include_once('footer.php');?>
     <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>



<!-- Start :: Datepicker css-->
<link rel="stylesheet" href="../assets/plugins/datepicker/css/datepicker.css" />
<!-- Start :: Datepicker-->

<!-- Start :: Datepicker Script-->
<script src="../assets/js/jquery-ui.min.js"></script>
<script src="../assets/plugins/uniform/jquery.uniform.min.js"></script>
<script src="../assets/plugins/inputlimiter/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="../assets/plugins/chosen/chosen.jquery.min.js"></script>
<script src="../assets/plugins/colorpicker/js/bootstrap-colorpicker.js"></script>
<script src="../assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
<script src="../assets/plugins/validVal/js/jquery.validVal.min.js"></script>
<script src="../assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="../assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script src="../assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>
<script src="../assets/plugins/autosize/jquery.autosize.min.js"></script>
<script src="../assets/plugins/jasny/js/bootstrap-inputmask.js"></script>
<script src="../assets/js/formsInit.js"></script>
<script>
     $(function () {

          var nowTemp = new Date();
var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

$('#dp3').datepicker({
  onRender: function(date) {
    return date.valueOf() < now.valueOf() ? 'disabled' : '';
  }
});
			formInit();
     });
</script>

</body>
<!-- END BODY-->
</html>

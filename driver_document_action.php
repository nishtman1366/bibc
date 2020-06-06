<?php
include_once('common.php');
$generalobj->check_member_login();
require_once(TPATH_CLASS .'savar/jalali_date.php');
require_once(TPATH_CLASS . "/Imagecrop.class.php");
require_once(TPATH_CLASS . "/class.general.php");

$thumb = new thumbnail();
$script="Driver";
$sql = "select * from country";
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
      header("Location:driver.php?success=0&var_msg=".$langage_lbl['LBL_Driver_document_NOT_YOUR_DRIVER_DOCUMENT']);
    }
  } else {
      header("Location:driver.php?success=0&var_msg=".$langage_lbl['LBL_Driver_document_SOMETHING_WENT_WRONG']);
  }

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] :'';
$action = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? 'Edit' : 'Add';
$action_show = (isset($_REQUEST['action']) && $_REQUEST['action'] != '') ? $langage_lbl['LBL_EDIT'] : $langage_lbl['LBL_ADD'];
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);

$sql = "select * from register_driver where iDriverId = '" . $_REQUEST['id'] . "'";
$db_user = $obj->MySQLSelect($sql);
#echo"<pre>";print_r($db_user);exit;
$LicenceEXP=$db_user[0]['dLicenceExp'] ? $db_user[0]['dLicenceExp'] : '';

$vName=$db_user[0]['vName'];
$vLicence = $db_user[0]['vLicence'];
$vNoc = $db_user[0]['vNoc'];
$vCerti = $db_user[0]['vCerti'];
$action_doc = isset($_REQUEST['action_doc']) ? $_REQUEST['action_doc'] : '';
$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : 0;
$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : ''; 
$er = isset($_REQUEST["er"]) ? $_REQUEST["er"] : ''; 


$_POST['doc_path'] = $tconfig["tsite_upload_driver_doc_path"];

#echo"<pre>";print_r($action_doc);exit;
if ($action_doc == 'noc') { 
   // if(SITE_TYPE == 'Demo')
  // {
    // $var_msg="Edit / Delete Record Feature has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.";
    // header("location:driver_document_action.php?success=2&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
     // exit;
  
  // }

     if (isset($_POST['doc_path'])) {
          $doc_path = $_POST['doc_path'];
       
     }
     $temp_gallery = $doc_path . '/';
    
    
    
    // added by seyyed amir
    $error_msg = "";
    
    $limit_file_size = $generalobj->getConfigurations("configurations","LIMIT_UPLOAD_SIZE");
    
    $limit_kb = $limit_file_size / 1024;
    
    if($_FILES['noc']['size'] > $limit_file_size)
        $error_msg .= "files must be less than {$limit_kb}KB\r\n<br>";
    else
    {
        $image_object = $_FILES['noc']['tmp_name'];
        $image_name = $_FILES['noc']['name'];
    }
    
    if($error_msg != '')
    {
        header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&er=" . urlencode($error_msg));
        exit;
    }
    ///////////////////////////////
     
   
   
   
   if($image_name=="")
    {
      $var_msg=$langage_lbl['LBL_PLEASE_UPLOAD_VALID_FILE_FORMAT'];
      header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
      //$generalobjAdmin->getPostForm($_POST, $var_msg, "company_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
      
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
               $var_msg = $langage_lbl['LBL_WRONG_FILE_FORMAT'];
          }
        /* else if ($_FILES['noc']['size'] > 1048576) {
               $flag_error = 1;
               $var_msg = "Image Size is too Large";
          }*/
          if ($flag_error == 1) {
          //echo $flag_error;exit;
        /*$generalobj->getPostForm($_POST, $var_msg, "profile.php?success=0&var_msg=" . $var_msg);
               exit;*/
             $generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=0&id=".$id."&var_msg=".$var_msg);
             exit;
          } else {
          
                    $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                         mkdir($Photo_Gallery_folder, 0777,true);
                    }
                    //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                    $vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
                    if($vFile[2] == 1)
                    {
                        header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&er=" . urlencode($vFile[1]));
                        exit;
                    }
                    $vImage = $vFile[0];
                    $var_msg = $langage_lbl['LBL_NOC_UPLOADED'];
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
                  
                    $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $_REQUEST['id'] . "'";
                    $db_data = $obj->MySQLSelect($sql);

                    $sql = "SELECT * FROM company WHERE iCompanyid = '" .$_SESSION['sess_iUserId']. "'";
                    $db_company = $obj->MySQLSelect($sql);

                    $maildata['NAME'] = $db_data[0]['vName'];
                    $maildata['EMAIL'] = $db_data[0]['vEmail'];
                    $maildata['COMPANY'] = $db_company[0]['vName']." Company ";
                    $generalobj->send_email_user("DOCCUMENT_UPLOAD",$maildata);

                    //Start :: Log Data Save
                  $curr_date=Date('Y-m-d H:i:s');
                         if(empty($check_file[0]['vNoc'])){ $vNocPath = $vImage ; }else{ $vNocPath = $check_file[0]['vNoc']; }
                         $generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'company','noc',$vNocPath,$curr_date);
                    //End :: Log Data Save
                    
                   // Start :: Status in edit a Document upload time
                      // $set_value = "`eStatus` ='inactive'";
                      // $generalobj->estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
                    // End :: Status in edit a Document upload time
                       
                   header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
          }
     } 
}

if ($action_doc == 'certi') {
  
  
     if (isset($_POST['doc_path'])) {
          $doc_path = $_POST['doc_path'];
     }
     $temp_gallery = $doc_path . '/';
    
    
    // added by seyyed amir
    $error_msg = "";
    
    $limit_file_size = $generalobj->getConfigurations("configurations","LIMIT_UPLOAD_SIZE");
    
    $limit_kb = $limit_file_size / 1024;
    
    if($_FILES['certi']['size'] > $limit_file_size)
        $error_msg .= "files must be less than {$limit_kb}KB\r\n<br>";
    else
    {
        $image_object = $_FILES['certi']['tmp_name'];
        $image_name = $_FILES['certi']['name'];
    }
    
    if($error_msg != '')
    {
        header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&er=" . urlencode($error_msg));
        exit;
    }
    ///////////////////////////////
     
   
   if($image_name=="")
    {
      $var_msg = $langage_lbl['LBL_PLEASE_UPLOAD_VALID_FILE_FORMAT'];
      header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
      //$generalobjAdmin->getPostForm($_POST, $var_msg, "company_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
      
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
               $var_msg = $langage_lbl['LBL_PLEASE_UPLOAD_VALID_FILE_FORMAT'];
          }
         /* if ($_FILES['certi']['size'] > 1048576) {
               $flag_error = 1;
               $var_msg = "Image Size is too Large";
          }*/
          if ($flag_error == 1) {
               $generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
               exit;
          } else {
                    $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id'] . '/';
                    if (!is_dir($Photo_Gallery_folder)) {
                         mkdir($Photo_Gallery_folder, 0777,true);
                    }
                    //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
                    $vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
                    if($vFile[2] == 1)
                    {
                        header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&er=" . urlencode($vFile[1]));
                        exit;
                    }
                    $vImage = $vFile[0];
                    $var_msg = $langage_lbl['LBL_CERTIFICATION_UPLOADED'];
                    $tbl = 'register_driver';
                    $sql = "SELECT * FROM " . $tbl . " WHERE iDriverId = '" .$_REQUEST['id']. "'";
                    $db_data = $obj->MySQLSelect($sql);
          //echo "<pre>==";print_r($db_data);exit;
                    $q = "INSERT INTO ";
                    $where = '';

                    if (count($db_data) > 0) {
                         $q = "UPDATE ";
                         $where = " WHERE `iDriverId` = '" .$_REQUEST['id']. "'";
                    }
                    $query = $q . " `" . $tbl . "` SET `vCerti` = '" . $vImage . "' $where"; 
                    $obj->sql_query($query); 
                    
                    $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $_REQUEST['id'] . "'";
                    $db_data = $obj->MySQLSelect($sql);

                    $sql = "SELECT * FROM company WHERE iCompanyid = '" .$_SESSION['sess_iUserId']. "'";
                    $db_company = $obj->MySQLSelect($sql);

                    $maildata['NAME'] = $db_data[0]['vName'];
                    $maildata['EMAIL'] = $db_data[0]['vEmail'];
                    $maildata['COMPANY'] = $db_company[0]['vName']." Company ";
                    $generalobj->send_email_user("DOCCUMENT_UPLOAD",$maildata);
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

if ($action_doc == 'licence') {
  
  
  
     if (isset($_POST['doc_path'])) {
          $doc_path = $_POST['doc_path'];
      $expDate=$_POST['dLicenceExp'];
     }

     $temp_gallery = $doc_path . '/';
    
    
    // added by seyyed amir
    $error_msg = "";
    
    $limit_file_size = $generalobj->getConfigurations("configurations","LIMIT_UPLOAD_SIZE");
    
    $limit_kb = $limit_file_size / 1024;
    
    if($_FILES['licence']['size'] > $limit_file_size)
        $error_msg .= "files must be less than {$limit_kb}KB\r\n<br>";
    else
    {
        $image_object = $_FILES['licence']['tmp_name'];
        $image_name = $_FILES['licence']['name'];
    }
    
    if($error_msg != '')
    {
        header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&er=" . urlencode($error_msg));
        exit;
    }
    ///////////////////////////////
     

     
   
    $sql = "select * from register_driver where iDriverId = '" . $_REQUEST['id'] . "'";
     $db_licence = $obj->MySQLSelect($sql);
   
  /*if($image_name=="")
    {
      $var_msg="Please Upload valid file format for Document. Valid formats are pdf,doc,docx,jpg,jpeg,gif,png and Document Should not Empty";
      header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
      //$generalobjAdmin->getPostForm($_POST, $var_msg, "company_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
      
      exit;
    }*/
    
   if($image_name=="")
  {
      $tbl = 'register_driver';
    $q = "UPDATE ";
    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
    
    
      
       if($db_licence[0]['dLicenceExp']==$expDate)
       {
        $var_msg=$langage_lbl['LBL_WRONG_FILE_FORMAT'];
        
      }
      else 
      {
         $var_msg=$langage_lbl['LBL_EXPIRY_DATE_CHANGE_DOC_SAME'];
         $query = $q . " `" . $tbl . "` SET   
  `dLicenceExp` = '".$_POST['dLicenceExp']."'  " . $where;
               $obj->sql_query($query);
      }

      //---------------------------email upload docs-------------------------       
               $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $_REQUEST['id'] . "'";
                $db_data = $obj->MySQLSelect($sql);

                $sql = "SELECT * FROM company WHERE iCompanyid = '" .$_SESSION['sess_iUserId']. "'";
                $db_company = $obj->MySQLSelect($sql);

                $maildata['NAME'] = $db_data[0]['vName'];
                $maildata['EMAIL'] = $db_data[0]['vEmail'];
                $maildata['COMPANY'] = $db_company[0]['vName']." Company ";
                $generalobj->send_email_user("DOCCUMENT_UPLOAD",$maildata);
        //---------------------------email upload docs-------------------------       
      
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
          
          if ($ext != "jpg" && $ext != "gif" && $ext != "png" && $ext != "jpeg" && $ext != "bmp" && $ext != "pdf" && $ext != "doc" && $ext != "docx") {
               $flag_error = 1;
               $var_msg = $langage_lbl['LBL_WRONG_FILE_FORMAT'];
          }
         /* else if ($_FILES['licence']['size'] > 1048576) {
               $flag_error = 1;
               $var_msg = "Image Size is too Large";
          }*/
          if ($flag_error == 1) {
        //echo $var_msg;exit;
               $generalobj->getPostForm($_POST, $var_msg, "driver_document_action.php?success=0&id=".$_REQUEST['id']."&var_msg=".$var_msg);
               exit;
          } else {
              $Photo_Gallery_folder = $doc_path . '/' . $_REQUEST['id']. '/';
               if (!is_dir($Photo_Gallery_folder)) {
                    mkdir($Photo_Gallery_folder, 0777,true);
               }

               //$img = $generalobj->general_upload_image($image_object, $image_name, $Photo_Gallery_folder, $tconfig["tsite_upload_documnet_size1"], $tconfig["tsite_upload_documnet_size2"], '', '', '', '', 'Y', '', $Photo_Gallery_folder);
               $vFile = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
               if($vFile[2] == 1)
                {
                    header("location:driver_document_action.php?success=0&id=".$_REQUEST['id']."&er=" . urlencode($vFile[1]));
                    exit;
                }
               $vImage = $vFile[0];
              
               $var_msg = $langage_lbl['LBL_LICENCE_UPLOADED'];
               $tbl = 'register_driver';
               $sql = "SELECT * FROM " . $tbl . " WHERE iDriverId = '" . $_REQUEST['id'] . "'";
               $db_data = $obj->MySQLSelect($sql);
               $q = "INSERT INTO ";
               $where = '';

               if (count($db_data) > 0) {
                    $q = "UPDATE ";
                    $where = " WHERE `iDriverId` = '" . $_REQUEST['id'] . "'";
               }
                $query = $q . " `" . $tbl . "` SET `vLicence` = '" . $vImage . "',`dLicenceExp` = '".$_POST['dLicenceExp']."' $where"; 
               $obj->sql_query($query);
        //---------------------------email upload docs-------------------------       
               $sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $_REQUEST['id'] . "'";
                $db_data = $obj->MySQLSelect($sql);

                $sql = "SELECT * FROM company WHERE iCompanyid = '" .$_SESSION['sess_iUserId']. "'";
                $db_company = $obj->MySQLSelect($sql);

                $maildata['NAME'] = $db_data[0]['vName'];
                $maildata['EMAIL'] = $db_data[0]['vEmail'];
                $maildata['COMPANY'] = $db_company[0]['vName']."  ".$langage_lbl['LBL_Company'];
                $generalobj->send_email_user("DOCCUMENT_UPLOAD",$maildata);
        //---------------------------email upload docs-------------------------       
              //Start :: Log Data Save
                         if(empty($check_file[0]['vLicence'])){ $vLicencePath = $vImage ; }else{ $vLicencePath = $check_file[0]['vLicence']; }
                         $generalobj->save_log_data ($_SESSION['sess_iUserId'],$_REQUEST['id'],'company','licence',$vLicencePath);
              //End :: Log Data Save
               
              // Start :: Status in edit a Document upload time
                      // $set_value = "`eStatus` ='inactive'";
                      // $generalobj->estatus_change('register_driver','iDriverId',$_REQUEST['id'],$set_value);
               // End :: Status in edit a Document upload time


                       
               header("location:driver_document_action.php?success=1&id=".$_REQUEST['id']."&var_msg=" . $var_msg);
          }
     }
}
 /*$sql = "SELECT * FROM register_driver WHERE iDriverId = '" . $_REQUEST['id'] . "'";
 $db_data = $obj->MySQLSelect($sql);

if($db_data[0]['vNoc']!=NULL && $db_data[0]['vLicence'] !=NULL && $db_data[0]['vCerti'] !=NULL)
    {
       $sql = "SELECT * FROM company WHERE iCompanyid = '" .$_SESSION['sess_iUserId']. "'";
            $db_company = $obj->MySQLSelect($sql);
            $maildata['NAME'] = $db_data[0]['vName'];
            $maildata['EMAIL'] = $db_data[0]['vEmail'];
            $maildata['COMPANY'] = $db_company[0]['vName']." Company ";
            $generalobj->send_email_user("DOCCUMENT_UPLOAD",$maildata);
    }*/

?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo $SITE_NAME?> | <?php echo $langage_lbl['LBL_DRIVER']?> <?php echo  $action_show; ?></title>
  <!-- Default Top Script and css -->
  <?php include_once("top/top_script.php");?>
  <link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >
  <style>
      .fileupload-preview  { line-height:150px;}
  </style>
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
      <div class="page-contant">
        <div class="page-contant-inner">
          <h2 class="header-page trip-detail driver-detail1"><?php echo  ucfirst($action_show); ?> <?php echo $langage_lbl['LBL_Driver_document_Document_of']; ?>  <?php echo  $vName; ?> 
             <a href="driver.php"><img src="assets/img/arrow-white.png" alt=""><?php echo $langage_lbl['LBL_Driver_document-back_to_listing']; ?></a>
          </h2>
        <!-- driver vehicles page -->
          <div class="driver-vehicles-page">
            <?php if ($_REQUEST['success']==0 && $er != "") {?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                <?php echo  $er ?>
            </div>
            <?}else if ($_REQUEST['success']==1) {?>
            <div class="alert alert-success alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                <?php echo  $var_msg ?>
            </div>
            <?}else if($_REQUEST['success']==2){ ?>
            <div class="alert alert-danger alert-dismissable">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
            </div>
            <?php 
                }
            ?>
            <ul>
              <li>
                <h4><?php echo $langage_lbl['LBL_Driver_document_YOUR_DRIVING_LICENCE'];?></h4>
                <b>
                <?php 
                //if ($db_user[0]['vLicence'] != '')
                if($vLicence != '') 
                {
                  $file_ext = $generalobj->file_ext($vLicence);
                  if($file_ext == 'is_image'){ 
                ?>
                  <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vLicence ?>" style="width:200px;" alt ="Licence not found"/>
                <?php }else{ ?>
                  <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vLicence  ?>" target="_blank"><?php echo $langage_lbl['LBL_LICENCE_DOC']?></a> 
                <?php } ?>
                <?php } else { ?>
                    <?php echo $langage_lbl['LBL_Driver_document_LICENCE_NOT_FOUND']; ?>
                <?php } ?>
                </b>
                <span>
					<?php 
						if($LicenceEXP != "0000-00-00")
						{
							echo $langage_lbl['LBL_Driver_document_EXP_DATE']." : ".$LicenceEXP;
							echo "<br>";
						}	
						else
						{
						    echo $langage_lbl['LBL_Driver_document_EXP_DATE']." : ".jdate('Y-m-d');
						    echo "<br>";
						}
					?>
					<a data-toggle="modal" data-target="#uiModal"><?php echo $langage_lbl['LBL_CHANGE']?></a>
				</span>
              </li>
              <li>
                <h4><?php echo $langage_lbl['LBL_Driver_document_YOUR_NOC']; ?></h4>
                <b>
                <?php 
                  if ($vNoc != '') { 
                    $file_ext = $generalobj->file_ext($vNoc);
                    
                    if($file_ext == 'is_image'){ 
                ?>
                  <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vNoc ?>" style="width:200px;" alt ="NOC"/>
                <?php
                    }else{ 
                ?>  
                  <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vNoc  ?>" target="_blank"><?php echo $langage_lbl['LBL_Driver_document_NOC_DOC']; ?></a> 
                <?php
                    } 
                  } 
                  else { 
                ?>
                  <?php echo $langage_lbl['LBL_Driver_document_NEED_TO_UPLOAD']; ?>
                <?php } ?>
                </b>
                <span><a data-toggle="modal" data-target="#uiModal_2"><?php echo $langage_lbl['LBL_CHANGE']?></a></span>
              </li>
              <li>
                <h4><?php echo $langage_lbl['LBL_Driver_document_VERIFICATION_CERTIFICATE']; ?></h4>
                <b>
                
                <?php 

                  if($vCerti != '') {
                                                            
                    $file_ext = $generalobj->file_ext($vCerti);
                    if($file_ext == 'is_image'){ 
                ?>

                  <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vCerti ?>" style="width:200px;" alt =""/>
                <?php
                    }else{ 
                ?>
                  <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' .$vCerti  ?>" target="_blank"><?php echo $langage_lbl['LBL_Driver_document_CERTIFICATE_FILE'];?></a> 
                <?php
                    }  
                  } 
                  else { 
                ?>
                    <?php echo $langage_lbl['LBL_Driver_document_NEED_TO_UPLOAD']; ?>
                <?php } ?>
                </b>
                <span><a data-toggle="modal" data-target="#uiModal_3"><?php echo $langage_lbl['LBL_CHANGE']?></a></span>
              </li>
            </ul>
          </div>
          <div style="clear:both;"></div>
        </div>
      </div>
      <!-- End:contact page-->

      <div class="col-lg-12">
        <div class="modal fade" id="uiModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-content image-upload-1 popup-box3">
            <div class="upload-content">
              <h4><?php echo $langage_lbl['LBL_Driver_document_DRIVER_LICENCE']; ?></h4>
              <form class="form-horizontal" id="frm6" method="post" enctype="multipart/form-data" action="driver_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm6">
                <input type="hidden" name="action_doc" value ="licence"/>
                <input type="hidden" name="doc_path" value ="<?php //echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
                <div class="form-group">
                  <div class="col-lg-12">
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                      <div class="fileupload-preview thumbnail" >
                      <?php if ($vLicence  == '') { ?>
                          <?php echo $langage_lbl['LBL_Driver_document_LICENCE_IMAGE']; ?>
                      <?php } else { ?>
                          <?php $file_ext = $generalobj->file_ext($vLicence );
                          if($file_ext == 'is_image'){ ?>
                               <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vLicence  ?>" style="width:200px;" alt ="Licence not found"/>
                          <?php }else{ ?>
                               <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vLicence   ?>" target="_blank"><?php echo $langage_lbl['LBL_LICENCE_DOC'];?></a> 
                          <?php } ?>
                      <?php } ?>
                      </div>
                      <div>
                        <span class="btn btn-file btn-success"><span class="fileupload-new"><?php echo $langage_lbl['LBL_Driver_document_UPLOADE_LICENCE']; ?></span>
                                <span class="fileupload-exists"><?php echo $langage_lbl['LBL_CHANGE']?></span><input type="file" name="licence" /></span>
                          <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">x</a>
                      </div>
                    </div>
                  </div>
                </div>

                                <!--Mehrshad Modified-->
                                 <div class="col-lg-13 exp-date">
                                    <h5><?php echo $langage_lbl['LBL_Driver_document_EXP_DATE_HEADER_TXT']; ?></h5>
                                      <!--<div class="input-group input-append date" id="datepicker4" data-date="" data-date-format="yyyy-mm-dd">-->
                                        <!--<div>-->
                                          <input class="form-control" type="text" name="dLicenceExp" id="datepicker4" value="<?php echo isset($LicenceEXP) ? $LicenceEXP : jdate('Y-m-d'); ?>" />
                                           <!--<input class="form-control hasDatepicker" type="text" name="dLicenceExp" value="<?php echo isset($LicenceEXP) ? $LicenceEXP : jdate('Y-m-d'); ?>" readonly="" />-->
      
                                           <!--<span class="input-group-addon add-on"><i class="icon-calendar"></i></span>-->
      
                                      <!--</div>-->
                                 </div>
                  <input type="submit" class="save" name="save" value="<?php echo $langage_lbl['LBL_Driver_document_Save']; ?>">
                  <input type="button" class="cancel" data-dismiss="modal" name="cancel" value="<?php echo $langage_lbl['LBL_Driver_document_BTN_CANCEL_TRIP_TXT']; ?>">
            </form>
            <div style="clear:both;"></div>
          </div>
        </div>
      </div>
    </div>
      <div class="col-lg-12">
             <div class="modal fade" id="uiModal_2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                  <div class="modal-content image-upload-1 popup-box3">
                       <div class="upload-content">
                            <h4><?php echo $langage_lbl['LBL_Driver_document_NOC']; ?></h4>
                            <form class="form-horizontal" id="frm7" method="post" enctype="multipart/form-data" action="driver_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm7">
                                 <input type="hidden" name="action_doc" value ="noc"/>
                                 <input type="hidden" name="doc_path" value ="<?php //echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
                                 <div class="form-group">
                                      <div class="col-lg-12">
                                           <div class="fileupload fileupload-new" data-provides="fileupload">
                                                <div class="fileupload-preview thumbnail">
                                                     <?php if ($db_user[0]['vNoc'] == '') { ?>
                                                          <?php echo $langage_lbl['LBL_Driver_document_NOC_IMAGE']; ?>
                                                     <?php } else { ?>
                                                           <?php $file_ext = $generalobj->file_ext($vNoc);
                                                          if($file_ext == 'is_image'){ ?>
                                                               <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vNoc ;?>" style="width:200px;" alt ="NOC not found "/>
                                                          <?php }else{ ?>
                                                               <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vNoc  ?>" target="_blank"><?php echo $langage_lbl['LBL_Driver_document_NOC_DOC']; ?></a> 
                                                          <?php } ?>
                                                     <?php } ?>
                                                </div>
                                                <div>
                                                     <span class="btn btn-file btn-success"><span class="fileupload-new"><?php echo $langage_lbl['LBL_Driver_document_UPLOAD_NOC']; ?></span><span class="fileupload-exists"><?php echo $langage_lbl['LBL_Driver_document_CHANGE']; ?></span><input type="file" name="noc"/></span>
                                                     <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">x</a>
                                                </div>
                                           </div>
                                      </div>
                                 </div>
                                 <input type="submit" class="save" name="save" value="<?php echo $langage_lbl['LBL_Driver_document_Save']; ?>"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="<?php echo $langage_lbl['LBL_Driver_document_BTN_CANCEL_TRIP_TXT']; ?>">
                            </form>  <div style="clear:both;"></div>
                       </div>
                  </div>
             </div>
        </div>
        <div class="col-lg-12">
             <div class="modal fade" id="uiModal_3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                  <div class="modal-content image-upload-1 popup-box3">
                       <div class="upload-content">
                            <h4><?php echo $langage_lbl['LBL_Driver_document_POLICE_VARIFICATION_CERTIFICATE']; ?></h4>
                            <form class="form-horizontal" id="frm9" method="post" enctype="multipart/form-data" action="driver_document_action.php?id=<?php echo $_REQUEST['id']; ?>" name="frm9">
                                 <input type="hidden" name="action_doc" value ="certi"/>
                                 <input type="hidden" name="doc_path" value="<?php //echo $tconfig["tsite_upload_driver_doc_path"]; ?>"/>
                                 <div class="form-group">
                                      <div class="col-lg-12">
                                           <div class="fileupload fileupload-new" data-provides="fileupload">
                                                <div class="fileupload-preview thumbnail" >
                                                     <?php if ($db_user[0]['vCerti'] == '') { ?>
                                                          <?php echo $langage_lbl['LBL_Driver_document_Verification_Certi_Image']; ?>
                                                     <?php } else { ?>
                                                          <?php $file_ext = $generalobj->file_ext($vCerti);
                                                          if($file_ext == 'is_image'){ ?>
                                                               <img src = "<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vCerti; ?>" style="width:200px;" alt ="Certificate not found"/>
                                                          <?php }else{ ?>
                                                               <a href="<?php echo  $tconfig["tsite_upload_driver_doc"] . '/' . $_REQUEST['id'] . '/' . $vCerti; ?>" target="_blank"><?php echo $langage_lbl['LBL_Driver_document_CERTIFICATE_FILE']; ?></a> 
                                                          <?php } ?>
                                                     <?php } ?>             
                                                </div>
                                                <div>
                                                     <span class="btn btn-file btn-success"><span class="fileupload-new"><?php echo $langage_lbl['LBL_Driver_document_UPLOAD_PHOTO']; ?></span>
                                                          <span class="fileupload-exists"><?php echo $langage_lbl['LBL_Driver_document_CHANGE']; ?></span><input type="file" name="certi"/></span>
                                                     <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload">x</a>
                                                </div>
                                           </div>
                                      </div>
                                 </div>
                                 <input type="submit" class="save" name="save" value="<?php echo $langage_lbl['LBL_Driver_document_Save']; ?>"><input type="button" class="cancel" data-dismiss="modal" name="cancel" value="<?php echo $langage_lbl['LBL_Driver_document_BTN_CANCEL_TRIP_TXT']; ?>">
                            </form>  <div style="clear:both;"></div>
                       </div>
                  </div>
             </div>
        </div>
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
    
    <!-- footer part end -->
    <div style="clear:both;"></div>
    </div>
    <!-- home page end-->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
    
    <!-- Start :: Datepicker css-->
<link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css" />
<script src="assets/plugins/daterangepicker/daterangepicker.js"></script>
<script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
<script src="assets/plugins/timepicker/js/bootstrap-timepicker.min.js"></script>

<!-- Mehrshad Added -->
<script type="text/javascript" src="admin/js/moment.min.js"></script>
<script type="text/javascript" src="admin/js/bootstrap-datetimepicker.min.js"></script>
<script src="assets/js/jquery.ui.datepicker-cc-fa.js"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>

<!-- Start :: Datepicker-->

    
    <script type="text/javascript">
        function confirm_delete(id)
        {
            var tsite_url = '<?php echo $tconfig["tsite_url"]; ?>';
            if (id != '') {
                 var confirm_ans = confirm("<?php echo $langage_lbl['LBL_CONFIRM_DELETE_VEHICLE']?>");
                 if (confirm_ans == true) {
                      window.location.href = "vehicle.php?action=delete&id="+id;
                 }
                 }
            //document.getElementById(id).submit();
        }
    </script>
    <script>
     $(function () {
      
           $("#add_booking_form").submit(function(){
                    if($("#from_lat_long").val() == '')
                    {
                        $("#from").css('border-color','red');
                        return false;
                    }
                    return true;
                });
                
				newDate = new Date('Y-M-D');
//                $('#datetimepicker4').datetimepicker({
//					format: 'YYYY-MM-DD HH:mm:ss',
//					minDate: moment().format('l'),
//					ignoreReadonly: true,
//					sideBySide: true,
//				});
                $('#datepicker4').datepicker({
                    dateFormat: "yy-mm-dd",
                    changeYear: true,
                    changeMonth: true,
                    yearRange: "0:+10"
                });
                
                
      //Mehrshad
        //var nowTemp = new Date();
        //var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

    //     $('#datepicker4').datepicker({
    //         onRender: function(date) {
    //             return true ? 'disabled' : '';
    //         }
    //     });
    //   formInit();
     });
     

                
</script>

    <!-- End: Footer Script -->
</body>
</html>


<?php
include_once('../common.php');

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id=$id;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'vehicle_category';
$script = 'VehicleCategory';

$vCategory_ES = isset($_POST['vCategory_ES']) ? $_POST['vCategory_ES'] : '';
$vCategory_EN = isset($_POST['vCategory_EN']) ? $_POST['vCategory_EN'] : '';
$eStatus   = isset($_POST['eStatus'])?$_POST['eStatus']:'';  
$iParentId   = isset($_POST['vCategory'])?$_POST['vCategory']:'';  
$vTitle_store =array();
$sql = "SELECT * FROM `language_master` where eStatus='Active' ORDER BY `iDispOrder`";
$db_master = $obj->MySQLSelect($sql);
$count_all = count($db_master);
if($count_all > 0) {
  for($i=0;$i<$count_all;$i++) {
    $vValue = 'vCategory_'.$db_master[$i]['vCode'];
    array_push($vTitle_store ,$vValue);   
    $$vValue  = isset($_POST[$vValue])?$_POST[$vValue]:'';
    //array_push($vTitleval_store ,$$vValue); 
   
  }
}


 if(isset($_POST['btnsubmit'])){
  
  if(isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != ""){
     $filecheck = basename($_FILES['vLogo']['name']);
     $fileextarr = explode(".", $filecheck);
     $ext = strtolower($fileextarr[count($fileextarr) - 1]);
     $flag_error = 0;
     if($ext != "png") {
        $flag_error = 1;
        $var_msg = "Upload only png image";
     }
    $data = getimagesize($_FILES['vLogo']['tmp_name']);
   
  
     $width = $data[0];
     $height = $data[1];
     
     if($width != 360 && $height != 360) {
     
        $flag_error = 1;
        $var_msg = "Please Upload image only 360px * 360px";
     }
     if ($flag_error == 1) {
      
      
      if($action == "Add"){
      header("Location:vehicle_category_action.php?var_msg=".$var_msg);
      exit; 
      }else{
      header("Location:vehicle_category_action.php?id=".$id."&var_msg=".$var_msg);      
      exit;
      } 
      
       // $generalobj->getPostForm($_POST, $var_msg, "vehicle_type_action.php?success=0&var_msg=".$var_msg);
       // exit;
     }
  }
  
  if(isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != ""){

     $filecheck = basename($_FILES['vLogo1']['name']);
     $fileextarr = explode(".", $filecheck);

     $ext = strtolower($fileextarr[count($fileextarr) - 1]);
     $flag_error = 0;
     if($ext != "png") {
        $flag_error = 1;
        $var_msg = "Upload only png image";
     }
     $data = getimagesize($_FILES['vLogo1']['tmp_name']);

     $width = $data[0];
     $height = $data[1];
     
     if($width != 360 && $height != 360) {
     
        $flag_error = 1;
        $var_msg = "Please Upload image only 360px * 360px";
     }
     if ($flag_error == 1) {
     
      if($action == "Add"){
      header("Location:vehicle_category_action.php?var_msg=".$var_msg);
     
      }else{
      header("Location:vehicle_category_action.php?id=".$id."&var_msg=".$var_msg);
      
      exit;
      } 
        
        exit;
     }
  }  
    
    if(SITE_TYPE =='Demo'){
       header("Location:vehicle_category_action.php?id=".$id."&success=2");exit;
    }  
        for($i=0;$i<count($vTitle_store);$i++)
        {   
         $q = "INSERT INTO ";
        $where = '';

        if($id != '' ){
          $q = "UPDATE ";
          $where = " WHERE `iVehicleCategoryId` = '".$id."'";
        }   
        
      $vValue = 'vCategory_'.$db_master[$i]['vCode'];
        $query = $q . " `" . $tbl_name . "` SET
       `eStatus` = '" . $eStatus . "',
       `iParentId` = '" . $iParentId . "',
       ".$vValue." = '" .$_POST[$vTitle_store[$i]]. "'"
      . $where;
    
        $obj->sql_query($query);
          $id = ($id != '') ? $id : mysql_insert_id();  
       

     }   
    
    if(isset($_FILES['vLogo']) && $_FILES['vLogo']['name'] != ""){    


    
      $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];      
      $temp_gallery = $img_path . '/';
      $image_object = $_FILES['vLogo']['tmp_name'];
      $image_name = $_FILES['vLogo']['name'];

      
      $check_file_query = "select iVehicleCategoryId,vLogo from vehicle_category where iVehicleCategoryId=" . $id;
      $check_file = $obj->sql_query($check_file_query);   

      
      if($image_name != "") {
      
      
        if($message_print_id != "") {
           $check_file['vLogo'] = $img_path . '/' . $id . '/android/' . $check_file[0]['vLogo'];    


           $android_path = $img_path . '/' . $id . '/android';
           $ios_path = $img_path . '/' . $id . '/ios';
         
          
          if ($check_file['vLogo'] != '' && file_exists($check_file['vLogo'])) {
             //print_r($ios_path . '/'.$check_file['vLogo']); exit;

            @unlink($android_path . '/'.$check_file['vLogo']);
            @unlink($android_path . '/mdpi_'.$check_file['vLogo']);
            @unlink($android_path . '/hdpi_'.$check_file['vLogo']);
            @unlink($android_path . '/xhdpi_'.$check_file['vLogo']);
            @unlink($android_path . '/xxhdpi_'.$check_file['vLogo']);
            @unlink($android_path . '/xxxhdpi_'.$check_file['vLogo']);
            @unlink($ios_path . '/'.$check_file['vLogo']);
            @unlink($ios_path . '/1x_'.$check_file['vLogo']);
            @unlink($ios_path . '/2x_'.$check_file['vLogo']);
            @unlink($ios_path . '/3x_'.$check_file['vLogo']);
          }
        }
          $Photo_Gallery_folder = $img_path . '/' . $id . '/';
         
          $Photo_Gallery_folder_android = $Photo_Gallery_folder . 'android/';
         $Photo_Gallery_folder_ios = $Photo_Gallery_folder . 'ios/';
        if (!is_dir($Photo_Gallery_folder)) {
           mkdir($Photo_Gallery_folder, 0777);
           mkdir($Photo_Gallery_folder_android, 0777);
           mkdir($Photo_Gallery_folder_ios, 0777);
        }   
       
       $vVehicleType1 = str_replace(' ','',$vCategory_EN);  

       
      $img = $generalobj->general_upload_image_vehicle_android($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_category_size1_android"], $tconfig["tsite_upload_images_vehicle_category_size2_android"], $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_both"], $Photo_Gallery_folder_android,$vVehicleType1,NULL);
      $img1 = $generalobj->general_upload_image_vehicle_ios($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_ios"], $Photo_Gallery_folder_ios,$vVehicleType1,NULL);
      $vImage = "ic_car_".$vVehicleType1.".png";      
       

        $sql = "UPDATE ".$tbl_name." SET `vLogo` = '" . $vImage . "' WHERE `iVehicleCategoryId` = '" . $id . "'"; 
      
        $obj->sql_query($sql);
      }
    }
     
      if(isset($_FILES['vLogo1']) && $_FILES['vLogo1']['name'] != ""){
      $img_path = $tconfig["tsite_upload_images_vehicle_category_path"];
      $temp_gallery = $img_path . '/';
      $image_object = $_FILES['vLogo1']['tmp_name'];
      $image_name = $_FILES['vLogo1']['name'];
       $check_file_query = "select iVehicleCategoryId,vLogo1 from vehicle_category where iVehicleCategoryId=" . $id;
      $check_file = $obj->sql_query($check_file_query);
        if($image_name != "") {
          if($message_print_id != "") {
            $check_file['vLogo1'] = $img_path . '/' . $id . '/android/' . $check_file[0]['vLogo1'];
            $android_path = $img_path . '/' . $id . '/android';
            $ios_path = $img_path . '/' . $id . '/ios';
            
            if ($check_file['vLogo1'] != '' && file_exists($check_file['vLogo1'])) {
              @unlink($android_path . '/'.$check_file['vLogo1']);
              @unlink($android_path . '/mdpi_hover_'.$check_file['vLogo1']);
              @unlink($android_path . '/hdpi_hover_'.$check_file['vLogo1']);
              @unlink($android_path . '/xhdpi_hover_'.$check_file['vLogo1']);
              @unlink($android_path . '/xxhdpi_hover_'.$check_file['vLogo1']);
              @unlink($android_path . '/xxxhdpi_hover_'.$check_file['vLogo1']);
              @unlink($ios_path . '/'.$check_file['vLogo1']);
              @unlink($ios_path . '/1x_hover_'.$check_file['vLogo1']);
              @unlink($ios_path . '/2x_hover_'.$check_file['vLogo1']);
              @unlink($ios_path . '/3x_hover_'.$check_file['vLogo1']);
            }
          }
          $Photo_Gallery_folder = $img_path . '/' . $id . '/';
          $Photo_Gallery_folder_android = $Photo_Gallery_folder . '/android/';
          $Photo_Gallery_folder_ios = $Photo_Gallery_folder . '/ios/';
          if (!is_dir($Photo_Gallery_folder)) {
             mkdir($Photo_Gallery_folder, 0777);
             mkdir($Photo_Gallery_folder_android, 0777);
             mkdir($Photo_Gallery_folder_ios, 0777);
          } 
          $vVehicleType1 = str_replace(' ','',$vCategory_EN);       
            $img = $generalobj->general_upload_image_vehicle_android($image_object, $image_name, $Photo_Gallery_folder_android, $tconfig["tsite_upload_images_vehicle_category_size1_android"], $tconfig["tsite_upload_images_vehicle_category_size2_android"], $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size4_android"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_type_size5_both"], $Photo_Gallery_folder_android,$vVehicleType1,"hover_");
            $img1 = $generalobj->general_upload_image_vehicle_ios($image_object, $image_name, $Photo_Gallery_folder_ios, '', '', $tconfig["tsite_upload_images_vehicle_category_size3_both"], $tconfig["tsite_upload_images_vehicle_category_size5_both"], '', '', 'Y', $tconfig["tsite_upload_images_vehicle_category_size5_ios"], $Photo_Gallery_folder_ios,$vVehicleType1,"hover_");
            $vImage1 = "ic_car_".$vVehicleType1.".png";
            
            $sql = "UPDATE ".$tbl_name." SET `vLogo1` = '" . $vImage1 . "' WHERE `iVehicleCategoryId` = '" . $id . "'";
            $obj->sql_query($sql);
        }
      } 
    
     //$obj->sql_query($query);
     header("Location:vehicle_category_action.php?id=" . $id . '&success=1');
  }

// for Edit
  if($action == 'Edit') {

     $sql = "SELECT * FROM " . $tbl_name . " WHERE iVehicleCategoryId = '" . $id . "'";
     $db_data = $obj->MySQLSelect($sql);

     $vLabel = $id;
     if (count($db_data) > 0) {
         /* foreach ($db_data as $key => $value) {
               $vCategory_EN = $value['vCategory_EN'];
               $vCategory_ES = $value['vCategory_ES'];
               $eStatus = $value['eStatus'];          
              $vLogo = $value['vLogo'];
          }*/
         # echo"<pre>";print_r($db_data);exit;
          for($i=0;$i<count($db_master);$i++)
          {
            foreach($db_data as $key => $value) {
              $vValue = 'vCategory_'.$db_master[$i]['vCode'];
              $$vValue = $value[$vValue];
              $eStatus = $value['eStatus'];
              $iParentId = $value['iParentId'];
               $vLogo = $value['vLogo'];

            
            }
          }

     }

  }   
  #echo"<pre>";print_r($iVehicleCategoryId);exit;
  $sql="select vCategory_EN, iVehicleCategoryId from vehicle_category where iParentId='0'";
  $db_data1 = $obj->MySQLSelect($sql);
  /*echo"<pre>";print_r(count($db_data1));
  echo"<pre>";print_r($db_data1);exit;*/
 /* $result1=mysqli_query($condbc,$sql);
  $rows2=array();
  while($row1=mysql_fetch_assoc($result1))
  {
    array_push($rows2, $row1);    
  }*/

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>Admin | Vehicle Category <?php echo  $action; ?></title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
          <?
          include_once('global_files.php');
          ?>
          <!-- On OFF switch -->
          <link href="../assets/css/jquery-ui.css" rel="stylesheet" />
          <link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
     </head>
     <!-- END  HEAD-->
     <!-- BEGIN BODY-->
     <body class="padTop53 " >

          <!-- MAIN WRAPPER -->
          <div id="wrap">
               <?
               include_once('header.php');
               include_once('left_menu.php');
               ?>
               <!--PAGE CONTENT -->
               <div id="content">
                    <div class="inner">
                         <div class="row">
                              <div class="col-lg-12">
                                   <h2> Vehicle category</h2>
                                   <a href="vehicle_category.php">
                                        <input type="button" value="Back to Listing" class="add-btn">
                                   </a>
                              </div>
                         </div>
                         <hr />
                         <div class="body-div">
                              <div class="form-group">
                                   <?php if ($success == 1) {?>
                                   <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        Record Updated successfully.
                                   </div><br/>
                                   <?php } elseif ($success == 2) { ?>
                                    <div class="alert alert-danger alert-dismissable">
                                         <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                         "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                                    </div><br/>
                                  <?php } elseif ($success == 3) { ?>
                                   <div class="alert alert-danger alert-dismissable">
                                         <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                         "Please Select Pickup Start Time less than Pickup End Time." 
                                    </div><br/> 
                                  <?php } elseif ($success == 4) { ?>
                                   <div class="alert alert-danger alert-dismissable">
                                         <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                         "Please Select Night Start Time less than Night End Time." 
                                    </div><br/> 
                                  <?php } ?>
                  <?php if($_REQUEST['var_msg'] !=Null) { ?>
                <div class="alert alert-danger alert-dismissable">
                  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                  Record  Not Updated .
                </div><br/>
              <?php } ?>   
                   <div id="price1" >

                                   </div><br/>
                   <div id="price" ></div><br/>
                                   <form id="vtype" method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo  $id; ?>"/>
                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>Parent Category :</label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <select  class="form-control" name = 'vCategory'  id= 'vCategory' >
                                                       <option value="0">Select Parent Category</option>
                                                       <?php for ($i = 0; $i < count($db_data1); $i++) { ?>
                                                       <option value = "<?php echo  $db_data1[$i]['iVehicleCategoryId'] ?>" <?php echo  ($db_data1[$i]['iVehicleCategoryId'] == $iParentId) ? 'selected' : ''; ?>><?php echo  $db_data1[$i]['vCategory_EN']; ?>
                                                       </option>
                                                       <?php } ?>
                                                  </select>
                                             </div>
                                        </div>
                                         <?
                                        if($count_all > 0) {
                                          for($i=0;$i<$count_all;$i++) {
                                            $vCode = $db_master[$i]['vCode'];
                                            $vTitle = $db_master[$i]['vTitle'];
                                            $eDefault = $db_master[$i]['eDefault'];

                                            $vValue = 'vCategory_'.$vCode;

                                            $required = ($eDefault == 'Yes')?'required':'';
                                            $required_msg = ($eDefault == 'Yes')?'<span class="red"> *</span>':'';
                                          ?>
                                                <div class="row">
                                                   <div class="col-lg-12">
                                                   <label>Category (<?php echo $vTitle;?>) <span class="red"> *</span></label>
                                                       
                                                   </div>
                                                   <div class="col-lg-6">
                                                   <input type="text" class="form-control" name="<?php echo $vValue;?>" id="<?php echo $vValue;?>" value="<?php echo $$vValue;?>" placeholder="<?php echo $vTitle;?>Value" <?php echo $required;?>>
                                                       
                                                   </div>
                                              </div>
                                               <?php }
                                        } ?>

                                          <div class="row">
                                             <div class="col-lg-12">
                                                  <label>Logo (Gray image)</label>
                                             </div>
                                             <div class="col-lg-6">
                                                <?php if($vLogo != '') { ?>                                               
                                                <img src="<?php echo $tconfig['tsite_upload_images_vehicle_category']."/".$id."/ios/3x_".$vLogo;?>" style="width:100px;height:100px;">

                                                <?}?>
                                                  <input type="file" class="form-control" name="vLogo" <?php echo $required_rule; ?> id="vLogo" placeholder="" style="padding-bottom: 39px;">
                                                  <br/>
                                                  [Note: Upload only png image size of 360px*360px.]
                                             </div>
                                        </div>                    
                                         <div class="row">
                                             <div class="col-lg-12">
                                                  <label>Logo (Orange image)</label>
                                             </div>
                                             <div class="col-lg-6">
                                                <?php if($vLogo != '') { ?>                                               
                                                 <img src="<?php echo $tconfig['tsite_upload_images_vehicle_category']."/".$id."/ios/3x_hover_".$vLogo;?>" style="width:100px;height:100px;">
                                                <?}?>
                                                  <input type="file" class="form-control" name="vLogo1" <?php echo $required_rule; ?> id="vLogo1" placeholder="" style="padding-bottom: 39px;">
                                                  <br/>
                                                  [Note: Upload only png image size of 360px*360px.]
                                             </div>
                                        </div>
                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>Status<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <select  class="form-control" name = 'eStatus'  id= 'eStatus' required>                                   
                                                       <option value="Active" <?php if('Active' == $db_data[0]['eStatus']){?>selected<?php } ?>>Active</option>
                                                       <option value="Inactive"<?php if('Inactive' == $db_data[0]['eStatus']){?>selected<?php } ?>>Inactive</option>                                                      
                                                       </option>                                                    
                                                  </select>
                                             </div>
                                        </div>     
                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <input type="submit" class="save btn-info" name="btnsubmit" id="btnsubmit" value="Update" >
                                             </div>
                                        </div>
                                   </form>
                              </div>
                              
                         </div>
                            <div style="clear:both;"></div>
                    </div>
                    
              </div>
              
               <!--END PAGE CONTENT -->
          </div>
          <!--END MAIN WRAPPER -->


          <?
          include_once('footer.php');
          ?>
          <script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
          <link rel="stylesheet" type="text/css" media="screen" href="css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css">
          <script type="text/javascript" src="js/moment.min.js"></script>              
          
     </body>
     <!-- END BODY-->
</html>
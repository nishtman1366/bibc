<?php
include_once('../common.php');

//print_r($_SESSION['sess_lang']);

require_once(TPATH_CLASS . "/Imagecrop.class.php");
$thumb = new thumbnail();

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$message_print_id=$id;
$ksuccess=isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$action = ($id != '') ? 'Edit' : 'Add';
$tbl_name = 'package_type';
$script = 'PahckageType';
$iPackageTypeId = isset($_POST['iPackageTypeId']) ? $_POST['iPackageTypeId'] : '';
$vName_EN = isset($_POST['vName_EN']) ? $_POST['vName_EN'] : '';
$vName_PS = isset($_POST['vName_PS']) ? $_POST['vName_PS'] : '';
$eStatus = isset($_POST['eStatus']) ? $_POST['eStatus'] : '';


if (isset($_POST['submit'])) {
     //echo '<pre>'; print_r($_POST); exit;
     //Start :: Upload Image Script
      if(!empty($id)){
          if(SITE_TYPE=='Demo')
          {
            header("Location:package_type_action.php?id=" . $id . '&success=2');
            exit;
          }
      }
     $q = "INSERT INTO ";
     $where = '';
     if ($action == 'Edit') {
          $str = " ";
     }

     if ($id != '') {
          $q = "UPDATE ";
          $where = " WHERE `iPackageTypeId` = '" . $id . "'";
     }
     $query = $q . " `" . $tbl_name . "` SET
    `vName` = '" . $vName_EN . "',
    `vName_EN` = '" . $vName_EN . "',
    `vName_PS` = '" . $vName_PS . "',
		`eStatus` = '" . $eStatus . "' $str" . $where;
     //echo '<pre>'; print_r($query); exit;
     $obj->sql_query($query);

     $id = ($id != '') ? $id : mysql_insert_id();

     if($action=="Add")
     {
        $ksuccess="1";
      }
     else
     {
        $ksuccess="2";
     }
     //echo $ksuccess;exit;
     header("Location:package_type_action.php?id=" . $id . '&success=1 &ksuccess='.$ksuccess);
}
// for Edit

if ($action == 'Edit') {
     $sql = "SELECT * FROM " . $tbl_name . " WHERE  iPackageTypeId = '" . $id . "'";
     $db_data = $obj->MySQLSelect($sql);

     if (count($db_data) > 0) {
          foreach ($db_data as $key => $value) {
               $vName_EN = $value['vName_EN'];
               $vName_PS = $value['vName_PS'];
               $eStatus = $value['eStatus'];


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
          <title>Admin |  Package Type  <?php echo  $action; ?></title>
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
                                   <h2><?php echo  $action; ?> نوع بسته <?php echo  $vName_EN; ?></h2>
                                   <a href="package_type.php">
                                        <input type="button" value="بازگشت" class="add-btn">
                                   </a>
                              </div>
                         </div>
                         <hr />
                         <div class="body-div">
                              <div class="form-group">
                                   <?php if ($success == 1) {?>
                                   <div class="alert alert-success alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                          <?php
                                          if($ksuccess == "1")
                                          {?>
                                              نوع بسته با موفقیت افزوده شد
                                          <?php } else
                                          {?>
                                              نوع بسته با موفقیت ویرایش شد
                                          <?php } ?>

                                   </div><br/>
                                   <?} ?>

                                   <?php if ($success == 2) {?>
                                   <div class="alert alert-danger alert-dismissable">
                                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                        "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                                   </div><br/>
                                   <?} ?>
                                   <form method="post" action="" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?php echo  $id; ?>"/>

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>نام بسته (English)<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text"  class="form-control" name=" vName_EN"  id="vName_EN" value="<?php echo  $vName_EN ?>" placeholder="Enter Package name English" required>
                                             </div>
                                        </div>


                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <label>نام بسته (Persian)<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <input type="text"  class="form-control" name="vName_PS"  id="vName_PS" value="<?php echo  $vName_PS ?>" placeholder="Enter Package name Spanish" required >
                                             </div>
                                        </div>
                                         <div class="row">
                                             <div class="col-lg-12">
                                                  <label>وضعیت<span class="red"> *</span></label>
                                             </div>
                                             <div class="col-lg-6">
                                                  <select  class="form-control" name = 'eStatus'  id= 'eStatus' required>
                                                       <option value="Active" <?php if('Active' == $db_data[0]['eStatus']){?>selected<?php } ?>>فعال</option>
                                                       <option value="Inactive"<?php if('Inactive' == $db_data[0]['eStatus']){?>selected<?php } ?>>غیر فعال</option>
                                                       </option>
                                                  </select>
                                             </div>
                                        </div>

                                        <div class="row">
                                             <div class="col-lg-12">
                                                  <input type="submit" class="save btn-info" name="submit" id="submit" value="<?php echo  $action; ?> Package Type"  >
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

     </body>
     <!-- END BODY-->
</html>

<?php
include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('PACKAGE_TYPE') == false)
		die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");

     $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();


$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$iPackageTypeId = isset($_REQUEST['iPackageTypeId']) ? $_REQUEST['iPackageTypeId'] : '';
$Status = isset($_REQUEST['Status']) ? $_REQUEST['Status'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess=isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$script = 'PahckageType';

if($iPackageTypeId != '' && $Status != ''){
  if(SITE_TYPE !='Demo'){
  $query = "UPDATE package_type SET eStatus = '".$Status."' WHERE iPackageTypeId = '".$iPackageTypeId ."'";
  $obj->sql_query($query);
  }
  else{
    header("Location:package_type.php?success=2");exit;
  }
}

if ($action == 'delete' && $hdn_del_id != '') {
  $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
     if(SITE_TYPE !='Demo'){
       $query = "DELETE FROM `package_type` WHERE  iPackageTypeId = '" . $hdn_del_id . "'";
       $obj->sql_query($query);
       $action = "view";
       $success = "1";
       $ksuccess="3";
     }
     else{
       header("Location:package_type.php?success=2");exit;
     }

}

$tbl_name = "package_type";


if ($action == 'view') {

    $sql="SELECT * FROM `package_type`";
    $data_pckg = $obj->MySQLSelect($sql);

}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>ادمین | نوع بسته</title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />

          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

          <?php include_once('global_files.php');?>
          <script>
               $(document).ready(function () {
                    $("#show-add-form").click(function () {
                         $("#show-add-form").hide(1000);
                         $("#add-hide-div").show(1000);
                         $("#cancel-add-form").show(1000);
                    });

               });
          </script>
          <script>
               $(document).ready(function () {
                    $("#cancel-add-form").click(function () {
                         $("#cancel-add-form").hide(1000);
                         $("#show-add-form").show(1000);
                         $("#add-hide-div").hide(1000);
                    });

               });

          </script>
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
                         <div id="add-hide-show-div">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <h2><?php echo $langage_lbl['LBL_PACKAGE_TYPE_TXT'];?> </h2>
                                        <a class="add-btn" href="package_type_action.php" style="text-align: center;">افزودن نوع بسته</a>
                                        <input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn">
                                   </div>
                              </div>
                              <hr />
                         </div>
                         <?php if($success == 1) { ?>
                         <div class="alert alert-success alert-dismissable">
                              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>

                                <?php if($ksuccess == "1")
                                    {?>
                                        نوع بسته با موفقیا افزوده شد
                                    <?php }
                                     else if ($ksuccess=="2")
                                     {?>
                                        نوع بسته با موفقیت ویرایش شد
                                     <?php }
                                      else if($ksuccess=="3")
                                    {?>
                                        نوع بسته با موفقیت حذف شد
                                    <?php } ?>
                                    <?echo $msg;?>

                         </div><br/>
                         <?php }elseif ($success == 2 & $msg == '') { ?>
                           <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                           </div><br/>
                         <?php } elseif ($success == 2 & $msg != '') { ?>
                           <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?echo $msg;?>
                           </div><br/>
                         <?php } ?>
                         <div class="table-list">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <div class="panel panel-default">
                                             <div class="panel-heading">
                                                  <?php echo $langage_lbl_admin['LBL_PACKAGE_TYPE_TXT'];?>
                                             </div>
                                             <div class="panel-body">
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                            <thead>
                                                                 <tr>
                                                                      <th>نام بسته</th>
                                                                      <th>وضعیت</th>
                                                                      <th>ویرایش</th>
                                                                      <th>حذف</th>
                                                                 </tr>
                                                            </thead>
                                                            <tbody>
                                                                 <?php for ($i = 0; $i < count($data_pckg); $i++) { ?>
                                                                <tr class="gradeA">
                                                                      <td><?php echo  $data_pckg[$i]['vName_PS']; ?></td>

                                                                  <td>
                                                                     <a href="package_type.php?iPackageTypeId=<?php echo  $data_pckg[$i]['iPackageTypeId']; ?>&Status=<?php echo  ($data_pckg[$i]['eStatus'] == "Active") ? 'Inactive' : 'Active' ?>">
                                                                          <button class="btn">
                                                                            <i class="<?php echo  ($data_pckg[$i]['eStatus'] == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?php echo  ucfirst($data_pckg[$i]['eStatus']); ?>
                                                                          </button>
                                                                        </a>
                                                                  </td>
                                                                  <td>
                                                                           <a href="package_type_action.php?id=<?php echo  $data_pckg[$i]['iPackageTypeId']; ?>" style="float: left;">
                                                                                <button class="btn btn-primary">
                                                                                     <i class="icon-pencil icon-white"></i> ویرایش
                                                                                </button>
                                                                           </a>
                                                                  </td>
                                                                   <td>
                                                                         <form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm('Are you sure you want to delete record?')" class="margin0">
                                                                              <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo  $data_pckg[$i]['iPackageTypeId']; ?>">
                                                                              <input type="hidden" name="action" id="action" value="delete">
                                                                              <button class="btn btn-danger">
                                                                                   <i class="icon-remove icon-white"></i> حذف
                                                                              </button>
                                                                         </form>
                                                                  </td>

                                                                 </tr>
                                                                 <?php } ?>
                                                            </tbody>
                                                       </table>
                                                  </div>
                                             </div>
                                        </div>
                                   </div> <!--TABLE-END-->
                              </div>
                         </div>
                         <div style="clear:both;"></div>
                    </div>
               </div>
               <!--END PAGE CONTENT -->
          </div>
          <!--END MAIN WRAPPER -->


        <?php include_once('footer.php');?>
    <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
  <script>
    $(document).ready(function () {
      $('#dataTables-example').dataTable({
        "order": [[ 3, "desc" ]]
      });
    });


  </script>
</body>
<!-- END BODY-->
</html>

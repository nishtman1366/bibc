<?php
include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('VEHICLE_CATEGORY') == false)
		die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");

     $generalobjAdmin = new General_admin();
}

$generalobjAdmin->check_member_login();
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$Status = isset($_REQUEST['Status']) ? $_REQUEST['Status'] : '';
$iVehicleCategoryId = isset($_REQUEST['iVehicleCategoryId']) ? $_REQUEST['iVehicleCategoryId'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$ksuccess=isset($_REQUEST['ksuccess']) ? $_REQUEST['ksuccess'] : 0;
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '';
$script = 'VehicleCategory';

if($iVehicleCategoryId != '' && $Status != ''){
  if(SITE_TYPE !='Demo'){
  $query = "UPDATE vehicle_category SET eStatus = '".$Status."' WHERE iVehicleCategoryId = '".$iVehicleCategoryId ."'";
  $obj->sql_query($query);
  }
  else{
    header("Location:vehicle_category.php?success=2");exit;
  }
}


if ($action == 'delete' && $hdn_del_id != '') {
  $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';   
     if(SITE_TYPE !='Demo'){
       $query = "DELETE FROM `vehicle_category` WHERE  iVehicleCategoryId = '" . $hdn_del_id . "'";
       $obj->sql_query($query);
       $action = "view";
       $success = "1";
       $ksuccess="3";
     }
     else{
       header("Location:vehicle_category.php?success=2");exit;
     }
    
}
$tbl_name = "vehicle_category";

if ($action == 'view') {
    
    $sql="SELECT * FROM `vehicle_category`";
    $data_drv = $obj->MySQLSelect($sql);
     
	$sql="SELECT iVehicleCategoryId, vCategory_EN, iParentId FROM `vehicle_category`";
    $data_drv_cat = $obj->MySQLSelect($sql);
	for($i=0;$i<count($data_drv_cat);$i++)
	{
		if($data_drv_cat[$i]['iParentId'] != 0)
		{
			$sql="SELECT vCategory_EN from vehicle_category where iVehicleCategoryId ='".$data_drv_cat[$i]['iParentId']."'";
			$data_drv_cat_name = $obj->MySQLSelect($sql);
			$data_drv_cat[$i]['path'] = $data_drv_cat_name[0]['vCategory_EN'].' >> '.$data_drv_cat[$i]['vCategory_EN'];
		}
		else
		{
			$data_drv_cat[$i]['path'] = $data_drv_cat[$i]['vCategory_EN'];
		}
	}
	#echo"<pre>";print_r($data_drv_cat);exit;
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>Admin | <?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN'];?> </title>
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
                                        <h2><?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_TXT_ADMIN'];?> </h2>                               
                                        <a class="add-btn" href="vehicle_category_action.php" style="text-align: center;">ADD Vehicle Category</a>
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
                                        Record Insert Successfully.
                                    <?php }
                                     else if ($ksuccess=="2")
                                     {?>
                                        Record Updated Successfully.
                                     <?php }
                                      else if($ksuccess=="3") 
                                    {?>
                                        Record Deleted Successfully.
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
                                                  <?php echo $langage_lbl_admin['LBL_VEHICLE_CATEGORY_ADMIN'];?> 
                                             </div>
                                             <div class="panel-body">
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                            <thead>
                                                                 <tr>
                                                                      <th>TITLE</th>                                                    
                                                                      <?php if($APP_TYPE == 'UberX'){?>
                                                                      <th>PATH</th>
                                                                      <?php } ?>
                                                                      <th>STATUS</th> 
                                                                      <th>ACTION</th>
                                                                      <th>DELETE</th>                                                                     
                                                                 </tr>
                                                            </thead>
                                                            <tbody>
                                                                 <?php for ($i = 0; $i < count($data_drv); $i++) { ?>
                                                                 <tr class="gradeA">
                                                                      <td><?php echo  $data_drv[$i]['vCategory_EN']; ?></td>
                                                                      <?php if($APP_TYPE == 'UberX'){?>
                                                                          <td><?php echo  $data_drv_cat[$i]['path'];?></td>
                                                                      <?php } ?>
                                                                      <td>
                                                                     <a href="vehicle_category.php?iVehicleCategoryId=<?php echo  $data_drv[$i]['iVehicleCategoryId']; ?>&Status=<?php echo  ($data_drv[$i]['eStatus'] == "Active") ? 'Inactive' : 'Active' ?>">
                                                                          <button class="btn">
                                                                            <i class="<?php echo  ($data_drv[$i]['eStatus'] == "Active") ? 'icon-eye-open' : 'icon-eye-close' ?>"></i> <?php echo  ucfirst($data_drv[$i]['eStatus']); ?>
                                                                          </button>
                                                                        </a>
                                                                      </td>    
                                                                      <td>
                                                                           <a href="vehicle_category_action.php?id=<?php echo  $data_drv[$i]['iVehicleCategoryId']; ?>" style="float: left;">
                                                                                <button class="btn btn-primary">
                                                                                     <i class="icon-pencil icon-white"></i> Edit
                                                                                </button>
                                                                           </a>
                                                                       </td>
                                                                        <td>     
                                                                           <form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm('Are you sure you want to delete record?')" class="margin0">
                                                                                <input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo  $data_drv[$i]['iVehicleCategoryId']; ?>">
                                                                                <input type="hidden" name="action" id="action" value="delete">
                                                                                <button class="btn btn-danger">
                                                                                     <i class="icon-remove icon-white"></i> Delete
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
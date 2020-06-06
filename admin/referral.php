<?php
include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('REFERRAL') == false)
		die('you dont`t have permission...');

	$script = "Referral";
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");

     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();



if(isset($_POST['rId']) && isset($_POST['action']) && $_POST['action'] == 'delete')
{
	$rId = $_POST['rId'];
	$sql = "DELETE FROM `savar_referrals` WHERE `rId` = $rId";
	$res = $obj->sql_query($sql);
}

if(isset($_GET['rId']) && isset($_GET['sActive']))
{
	$rId = $_GET['rId'];
	$sActive = $_GET['sActive'];

	$sql = "UPDATE `savar_referrals` SET
		`sActive`='$sActive' WHERE rId = $rId";
	$res = $obj->sql_query($sql);
}



$sql = "SELECT * FROM `savar_referrals` ";
$referrals = $obj->MySQLSelect($sql);




?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>ادمین | ارجاع</title>
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
                                        <h2>ارجاع</h2>
                                        <!--<input type="button" id="" value="ADD A DRIVER" class="add-btn">-->
                                        <a class="add-btn" href="referral_action.php" style="text-align: center;">افزودن ارجاع</a>
                                        <input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn">
                                   </div>
                              </div>
                              <hr />
                         </div>
                         <?php if($_GET['success'] == 1) { ?>
                         <div class="alert alert-success alert-dismissable msgs_hide">
                              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                              ارجاع با موفقیت ویرایش شده است
                         </div><br/>
                         <?php }elseif ($_GET['success'] == 2 & $msg == '') { ?>
                           <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                           </div><br/>
                         <?php } elseif ($_GET['success'] == 2 & $msg != '') { ?>
                           <div class="alert alert-success alert-dismissable msgs_hide">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                <?echo $msg;?>
                           </div><br/>
                         <?php } ?>
                         <div id="add-hide-div">

                         </div>
                         <div class="table-list">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <div class="panel panel-default">
                                             <div class="panel-heading">
                                                  ارجاع
                                             </div>
                                             <div class="panel-body">
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                            <thead>
                                                                 <tr>
                                                                      <th>نام ارجاع</th>
                                                                      <th>برای نوع حساب</th>

                                                                      <th>برای محدوده</th>
                                                                      <th>برای خودرو</th>
                                                                      <!--<th>SERVICE LOCATION</th>-->
                                                                      <th>تاریخ انقضا</th>
                                                                      <!--<th>LANGUAGE</th>-->
																	   <th>وضعیت</th>
                                                                       <th align="center" style="text-align:center;">فعالیت</th>
                                                                 </tr>
                                                            </thead>
                                                            <tbody>

                                                                 <?php foreach($referrals as $ref) { ?>
                                                                 <tr class="gradeA">
                                                                      <td><?php echo  $ref['sRefName']; ?></td>
                                                                      <td><?php echo  $ref['sForUserType'] ?></td>

                                                                      <td><?php echo  'area'; ?></td>
                                                                      <td><?php echo  'vehicle'; ?></td>
                                                                      <td><?php echo  $ref['sExpireDate']; ?></td>

																	  <td width="10%" align="center">
																		<?php 	$dis_img;
																			if($ref['sActive'] == 'Yes') {
																				$dis_img = "img/active-icon.png";
																			}else if($ref['sActive'] == 'No'){
																				$dis_img = "img/inactive-icon.png";
																			}?>
																		<img src="<?php echo  $dis_img ;?>" alt="<?php echo  $ref['sActive']?>">
                                                                      </td>
                                                                      <td class="veh_act" align="center" style="text-align:center;">
                                                                           <a href="referral_action.php?rId=<?php echo  $ref['rId']; ?>" data-toggle="tooltip" title="Edit Referral">
                                                                                <img src="img/edit-icon.png" alt="Edit">
                                                                           </a>

																	<a href="referral.php?rId=<?php echo  $ref['rId']; ?>&sActive=Yes" data-toggle="tooltip" title="Active Referral">
																		<img src="img/active-icon.png" alt="<?php echo  $ref['sActive']; ?>" >
																	</a>

																	<a href="referral.php?rId=<?php echo  $ref['rId']; ?>&sActive=No" data-toggle="tooltip" title="Inactive Referral">
																		<img src="img/inactive-icon.png" alt="<?php echo  $ref['sActive']; ?>" >
																	</a>
                                                                           <form name="delete_form" id="delete_form" method="post" action="referral.php" onSubmit="return confirm_delete()" class="margin0">
                                                                                <input type="hidden" name="rId" id="iCouponId" value="<?php echo  $ref['rId']; ?>">
                                                                                <input type="hidden" name="action" id="action" value="delete">
                                                                                <button class="remove_btn001" data-toggle="tooltip" title="Delete Referral">
                                                                                     <img src="img/delete-icon.png" alt="Delete">
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
                        <div class="clear"></div>
                    </div>
               </div>
               <!--END PAGE CONTENT -->
          </div>
          <!--END MAIN WRAPPER -->


        <?php include_once('footer.php');?>
    <script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
    <script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
    <link rel="stylesheet" media="all" type="text/css" href="../assets/dtp/jquery-ui.css" />
<link rel="stylesheet" media="all" type="text/css" href="../assets/dtp/jquery-ui-timepicker-addon.css" />
<script type="text/javascript" src="../assets/dtp/jquery-ui.min.js"></script>
<script type="text/javascript" src="../assets/dtp/jquery-ui-timepicker-addon.js"></script>
	<script>
   var successMSG1 = '<?php echo $success;?>';



      if(successMSG1 != ''){
           setTimeout(function() {
              $(".msgs_hide").hide(1000)
          }, 5000);
      }

		$(document).ready(function () {
			$('#dataTables-example').dataTable({
        "order": [[ 3, "desc" ]]
      });
		});
		function confirm_delete()
          {
               var confirm_ans = confirm("Are You sure You want to Delete Coupon?");
               return confirm_ans;
               //document.getElementById(id).submit();
          }


	</script>
</body>
<!-- END BODY-->
</html>

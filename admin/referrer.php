<?php
include_once('../common.php');

	include_once('savar_check_permission.php');
	if(checkPermission('REPORTS') == false)
		die('you dont`t have permission...');

if($REFERRAL_SCHEME_ENABLE == "No"){

header('Location: dashboard.php'); exit;

}

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$script = 'referrer';



	$type=(isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] !='')?$_REQUEST['reviewtype']:'Driver';

	$sql = "SELECT uw.*,rd.vName as Diver,rd.vLastName as Driverlastname,rd.eRefType as eRefType ,rd.iDriverId as rduserid ,ru.vName as Passanger,ru.eRefType as eRefType ,ru.vLastName as passangerlast, ru.iUserId as ruuserid
					FROM user_wallet as uw LEFT JOIN register_driver as rd ON rd.iDriverId=uw.iUserId LEFT JOIN register_user as ru ON ru.iUserId=uw.iUserId
					WHERE eUserType='".$type."' AND eFor = 'Referrer' GROUP BY uw.iUserId";

		//$query = "SELECT ".$d ." FROM user_wallet AS u ".$sql." WHERE u.eUserType = '".$type."'";

	$data_drv = $obj->MySQLSelect($sql);

	if($data_drv[0]['eUserType'] != null){
	 $data_drv[0]['eUserType']= $type;
	}
	 $success	= isset($_REQUEST['success'])?$_REQUEST['success']:'';


?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>گزارش ارجاع | ادمین</title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

          <?php include_once('global_files.php');?>

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
                                        <h2>گزارش ارجاع</h2>

                                   </div>
                              </div>
                              <hr />
                         </div>
                         <?php if($success == 1) { ?>
                         <div class="alert alert-success alert-dismissable">
                              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                             <?php echo $_REQUEST['succe_msg']; echo isset($_REQUEST['succe_msg'])? $_REQUEST['succe_msg'] : ''; ?>
                         </div><br/>
                         <?php }elseif ($success == 2) { ?>
                           <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                           </div><br/>
                         <?php } ?>

                         <div class="table-list">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <div class="panel panel-default">
                                             <div class="panel-heading">
                                                  <ul class="nav nav-tabs">
													  <li <?php if($type=='Driver'){?> class="active" <?php } ?>>
														  <a data-toggle="tab"  onclick="getReview('Driver')"  href="#home" >راننده</a></li>
													  <li <?php if($type=='Rider'){?> class="active" <?php } ?>>
														  <a data-toggle="tab" onclick="getReview('Rider')"  href="#menu1">مسافر</a></li>
												  </ul>
                                             </div>
                                             <div class="panel-body">
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                            <thead>
                                                                <tr>
																	<th width="35%">نام عضو</th>
																	<th width="25%">تعداد کل اعضای ذکر شده</th>
																	<th width="25%">مجموع مبلغ ارجاع</th>
																	<th width="15%">جزئیات</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
																<?php
																$count = count($data_drv);
																 if($count > 0){
																for($i=0;$i<count($data_drv);$i++) {
																	//if($vehicles[$i]['eRefType'] == "Driver"){
																	if($type == "Driver"){

																		$eUserType = $data_drv[$i]['eUserType'];
																		$id = $data_drv[$i]['rduserid'];
																	}
																	else{

																		$eUserType = $data_drv[$i]['eUserType'];
																		$id = $data_drv[$i]['ruuserid'];
																	}

																	$totalbalance = $generalobj->getTotalbalance($id,$eUserType);
																	$totalreffer = $generalobj->getTotalReferrer($id,$eUserType);
																	$data_drv[$i]['totalbalance'] = $totalbalance;
																	$data_drv[$i]['totalreffer'] = $totalreffer;
																	?>
                                                                 <tr class="gradeA">
																		<?php if($data_drv[0]['eUserType']=='Driver'){ ?>
                                                                      <td><?php echo $data_drv[$i]['Diver'].' '.$data_drv[$i]['Driverlastname']; ?></td>

																	  <?php }else{?>

																	   <td><?php echo $data_drv[$i]['Passanger'].' '.$data_drv[$i]['passangerlast']; ?></td>

																	  <?}?>

                                                                      <td> <?php echo  $data_drv[$i]['totalreffer']; ?></td>
                                                                      <td> <?php echo $generalobj->trip_currency($data_drv[$i]['totalbalance']);?></td>
																	    <td>
																	   	<?php if($data_drv[0]['eUserType']=='Driver'){?>

																		<!---
																		<a href="referrer_action.php?id=<?php echo $data_drv[$i]['rduserid']; ?>&eUserType=Driver">
																			<button class="btn btn-primary">
																				 <i></i> View
																			</button>
                                                                        </a>
																		-->
																		<a href="referrer_action.php?id=<?php echo $data_drv[$i]['rduserid']; ?>&eUserType=Driver" data-toggle="tooltip" title="View Details">
																			<img src="img/view-details.png" alt="View Details">
																		</a>
																		<?php }else{?>
																			<!----
																			<a href="referrer_action.php?id=<?php echo $data_drv[$i]['ruuserid'];?>&eUserType=Rider">
																			<button class="btn btn-primary">
																				 <i></i> View
																			</button>
																			</a>
																			--->
																			<a href="referrer_action.php?id=<?php echo $data_drv[$i]['ruuserid'];?>&eUserType=Rider" data-toggle="tooltip" title="View Details">
																			<img src="img/view-details.png" alt="View Details">
																		</a>
																		<?php } ?>
                                                                      </td>
                                                                 </tr>
                                                                 <?php }}	?>


                                                            </tbody>
                                                       </table>
													    <form name="frmreview" id="frmreview" method="post" action="">

															<input type="hidden" name="reviewtype" value="" id="reviewtype">
															<input type="hidden" name="action" value="" id="action">
															<!--<input type="hidden" name="iRatingId" value="" id="iRatingId">-->
														</form>
                                                  </div>

                                             </div>
                                        </div>
                                   </div> <!--TABLE-END-->
                              </div>
                         </div>
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

			 function getReview(type)
			{

				$('#reviewtype').val(type);
				document.frmreview.submit();

			}
		</script>
     </body>
     <!-- END BODY-->
</html>

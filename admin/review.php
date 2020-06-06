<?php
include_once('../common.php');
require_once(TPATH_CLASS .'savar/jalali_date.php');
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$script = 'Review';


	$type=(isset($_REQUEST['reviewtype']) && $_REQUEST['reviewtype'] !='')?$_REQUEST['reviewtype']:'Driver';
	if($type == "Driver"){
		$chkusertype = "Passenger";
	}else{
		$chkusertype = "Driver";
	}

	$sql = "SELECT r.*,rd.vName as Diver,rd.vLastName as Driverlastname,rd.vAvgRating,ru.vName as Passanger,ru.vLastName as passangerlast,ru.vAvgRating as passangerrate,t.iDriverId,t.iUserId,t.iTripId,t.vRideNo
			FROM ratings_user_driver as r
			LEFT JOIN trips as t ON r.iTripId=t.iTripId
			LEFT JOIN register_driver as rd ON rd.iDriverId=t.iDriverId
			LEFT JOIN register_user as ru ON ru.iUserId=t.iUserId
			WHERE eUserType='".$chkusertype."'";

     $data_drv = $obj->MySQLSelect($sql);
	  $data_drv[0]['reviewtype']=$type;
//echo "<pre>";print_r($vehicles);exit;
	  $success	= isset($_REQUEST['success'])?$_REQUEST['success']:'';
	if($_REQUEST['action']=='delete' && $_REQUEST['iRatingId']!='')
	{
		if(SITE_TYPE =='Demo'){
	     header("Location:review.php?success=2");exit;
	  }
		$sql="DELETE FROM ratings_user_driver WHERE iRatingId='".$_REQUEST['iRatingId']."'";
		$obj->sql_query($sql);
		$succe_msg="Record Deleted Successfully.";
		header("Location:review.php?success=1&succe_msg=".$succe_msg);exit;
	}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

     <!-- BEGIN HEAD-->
     <head>
          <meta charset="UTF-8" />
          <title>نظرات | ادمین</title>
          <meta content="width=device-width, initial-scale=1.0" name="viewport" />
          <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />

          <?php include_once('global_files.php');?>
          <!-- <script>
                       $(document).ready(function(){
                                    $("#show-add-form").click(function(){
                                                 $("#show-add-form").hide(1000);
                                                 $("#add-hide-div").show(1000);
                                                 $("#cancel-add-form").show(1000);
                                    });

                       });
          </script>
          <script>
                       $(document).ready(function(){
                                    $("#cancel-add-form").click(function(){
                                                 $("#cancel-add-form").hide(1000);
                                                 $("#show-add-form").show(1000);
                                                 $("#add-hide-div").hide(1000);
                                    });

                       });

          </script>	-->
     </head>
     <!-- END  HEAD-->
     <!-- BEGIN BODY-->
     <body class="padTop53 " >

          <!-- MAIN WRAPPER -->
          <div id="wrap">
               <?php include_once('header2.php'); ?>
               <?php include_once('left_menu.php'); ?>

               <!--PAGE CONTENT -->
               <div id="content">
                    <div class="inner">
                         <div id="add-hide-show-div">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <h2>نظرات</h2>

                                   </div>
                              </div>
                              <hr />
                         </div>
                         <?php if($success == 1) { ?>
                         <div class="alert alert-success alert-dismissable">
                              <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                             <?php echo isset($_REQUEST['succe_msg'])? $_REQUEST['succe_msg'] : ''; ?>
                         </div><br/>
                         <?php }elseif ($success == 2) { ?>
                           <div class="alert alert-danger alert-dismissable">
                                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                           </div><br/>
                         <?php } ?>
                         <!-- <div id="add-hide-div">
                                      <form name = "myForm" method="post" action="">
                                                   <div class="page-form">
                                                                <h2>ADD RIDER</h2>
                                                                <br><br>
                                                                <ul>
                                                                             <li>
                                                                                          FIRST NAME<br>
                                                                                          <input type="text" name="vName" class="form-control" placeholder="First Name" required>
                                                                             </li>
                                                                             <li>
                                                                                          LAST NAME<br>
                                                                                          <input type="text" name="vLname" class="form-control" placeholder="Last Name" required>
                                                                             </li>
                                                                             <li>
                                                                                          EMAIL<br>
                                                                                          <input type="email" name="vEmail" class="form-control" placeholder="Email" required>
                                                                             </li>
                                                                             <li>
                                                                                          COUNTRY<br>
                                                                                          <select class="contry-select" name = 'vCountry' onChange="changeCode(this.value);" required>
                                                                                                       <option value="">--select--</option>
                                                                                                       <?php for($i=0;$i<count($db_country);$i++){ ?>
                                                                                                                    <option value = "<?php echo  $db_country[$i]['vCountryCode'] ?>"><?php echo  $db_country[$i]['vCountry'] ?></option>
                                                                                                       <?php } ?>
                                                                                          </select>
                                                                                          <!--<input type="text" name="vEmail" class="form-control" placeholder="" >- ->
                                                                             </li>
                                                                             <li>
                                                                                          LANGUAGE<br>
                                                                                          <select name = 'vLang' class="form-control" required>
                                                                                                       <option value="">--select--</option>
                                                                                                       <?php for($i=0;$i<count($db_lang);$i++){ ?>
                                                                                                                    <option value = "<?php echo  $db_lang[$i]['vCode'] ?>"><?php echo  $db_lang[$i]['vTitle'] ?></option>
                                                                                                       <?php } ?>
                                                                                          </select>
                                                                                          <!--<input type="text" name="vEmail" class="form-control" placeholder="" >- ->
                                                                             </li>
                                                                             <li>
                                                                                          Phone<br>
                                                                                          <input type="text" class="form-select-2" id="code" name="vCode">
                                                                                          <input type="text" name="vPhone" class="mobile-text" placeholder="Phone" required pattern=".{10}"/>
                                                                             </li>
                                                                             <li>


                                                                             PASSWORD<br>
                                                                             <input type="password" class="form-control" placeholder="" name="vPassword" required>
                                                                </li>
                                                                <li>
                                                                             <input type="submit" name="submit" class="btn btn-primary" value="SUBMIT" >
                                                                </li>
                                                   </ul>
                                      </div>
                         </form>
            </div> -->
                         <div class="table-list">
                              <div class="row">
                                   <div class="col-lg-12">
                                        <div class="panel panel-default">
                                             <div class="panel-heading">
                                                  <ul class="nav nav-tabs">
													  <li <?php if($data_drv[0]['reviewtype']=='Driver'){?> class="active" <?php } ?>>
														  <a data-toggle="tab"  onclick="getReview('Driver')"  href="#home" >راننده</a></li>
													  <li <?php if($data_drv[0]['reviewtype']=='Passenger'){?> class="active" <?php } ?>>
														  <a data-toggle="tab" onclick="getReview('Passenger')"  href="#menu1">مسافر</a></li>
												  </ul>
                                             </div>
                                             <div class="panel-body">
                                                  <div class="table-responsive">
                                                       <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                                            <thead>
                                                                 <tr>
																 <th>تعداد سواری</th>
																	<?php if($data_drv[0]['reviewtype']=='Driver'){?>
                                                                      <th>نام (میانگین امتیاز) <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?>  </th>
                                                                      <th><?php echo $langage_lbl_admin['LBL_PASSANGER_TXT_ADMIN'];?>  Name</th>
																	<?php }else{?>
																	   <th>نام مسافر (میانگین امتیاز)</th>
																	   <th><?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?> نام </th>
																	<?}?>
                                                                      <th>امتیاز</th>

                                                                      <th>تاریخ</th>
                                                                      <th>نظر</th>
                                                                      <th>حذف</th>
                                                                 </tr>
                                                            </thead>
                                                            <tbody>
                                                                 <?php for($i=0;$i<count($data_drv);$i++) {?>
                                                                 <tr class="gradeA">
																 <td width="10%"><?php echo $data_drv[$i]['vRideNo']; ?></td>
																		<?php if($data_drv[0]['reviewtype']=='Driver'){?>

                                                                      <td width="10%"><?php echo $data_drv[$i]['Diver'].' '.$data_drv[$i]['Driverlastname'];
																		  echo " <b>( ".$data_drv[$i]['vAvgRating']." )</b>";
																		  ?></td>

                                                                      <td width="10%" data-order="<?php echo $data_drv[$i]['Passanger']; ?>"><?php echo $data_drv[$i]['passangerlast']; ?></td>
																	  <?php }else{?>
																	  <td data-order="<?php echo $data_drv[$i]['Passanger']; ?>"><?php echo $data_drv[$i]['passangerlast'];
																		   echo " <b>( ".$data_drv[$i]['passangerrate']." )</b>";
																		  ?></td>
																	   <td><?php echo $data_drv[$i]['Diver'].' '.$data_drv[$i]['Driverlastname']; ?></td>

																	  <?}?>
																	    <td width="5%" align="center"> <?php echo  $data_drv[$i]['vRating1'] ?> </td>


                                                                      <td align="center" width="10%"> <?php echo jdate('d-F-Y',strtotime($data_drv[$i]['tDate'])); ?> </td>
                                                                      <td align="center" width="50%"> <?php echo  $data_drv[$i]['vMessage'] ?></td>
																	   <td align="center" width="5%">
																		<a href="javascript:void(0)" onclick="confirm_delete('delete','<?php echo $data_drv[$i]['iRatingId'] ?>')" data-toggle="tooltip" title="Delete Review">
																			<button class="remove_btn001">
																				 <img src="img/delete-icon.png" alt="Delete">
																			</button>
                                                                         </a>
                                                                      </td>
                                                                 </tr>
                                                                 <?php } ?>

                                                            </tbody>
                                                       </table>
													    <form name="frmreview" id="frmreview" method="post" action="">

															<input type="hidden" name="reviewtype" value="" id="reviewtype">
															<input type="hidden" name="action" value="" id="action">
															<input type="hidden" name="iRatingId" value="" id="iRatingId">
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
			function confirm_delete(action,id)
			{
					 //alert(action);alert(id);
				 var confirm_ans = confirm("Are You sure You want to Delete this Rider?");
					   //alert(confirm_ans);
				 if(confirm_ans==false)
				 {
					return false;
					}
				 else
				 {
					 $('#action').val(action);
					 $('#iRatingId').val(id);
					 document.frmreview.submit();
				}

			 }
			 function getReview(type)
			{

				$('#reviewtype').val(type);
				document.frmreview.submit();

			}
	</script>
     </body>
     <!-- END BODY-->
</html>

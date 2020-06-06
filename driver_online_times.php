<?php 
	include_once('common.php');
    require_once(TPATH_CLASS .'savar/jalali_date.php');


	$generalobj->check_member_login();
	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$iCompanyId = $_SESSION['sess_iUserId'];
	
	//echo "<pre>";print_r($_SESSION);exit;
	
	$sql = "select * from country";
	$db_country = $obj->MySQLSelect($sql);
	
	$sql = "select * from language_master where eStatus = 'Active'";
	$db_lang = $obj->MySQLSelect($sql);
	

        // ADDED BY SEYYED AMIR
        $iCompanyId = $_SESSION['sess_iUserId'];
        $sql = "SELECT `iCompanyId` FROM `company` where `iParentId` = '" . $iCompanyId . "' and eStatus != 'Deleted'";
		$comp_childs = $obj->MySQLSelect($sql);
        $comp_list = $iCompanyId;
        
        foreach($comp_childs as $comp)
        {
            $comp_list .= ',' . $comp['iCompanyId'];
        }

        ////////////////////

    

    $iDriverId = 0;
    $driverName = '';

    if(isset($_GET['id']))
        $iDriverId = $_GET['id'];


    $sql = " SELECT CONCAT(vName,' ' ,vLastName) as name FROM `register_driver` WHERE iDriverId = {$iDriverId}";
	$driver = $obj->MySQLSelect($sql);

    if(isset($driver[0]['name']))
        $driverName = $driver[0]['name'];
        

    $limit = 0;

    if(isset($_GET['limit']))
        $limit = intval($_GET['limit']);
    if($limit <= 200)
        $limit = 200;

    $sql = " SELECT * FROM `driver_log_report` WHERE iDriverId = {$iDriverId} ORDER BY iDriverLogId DESC LIMIT {$limit}";
	$driver_requests = $obj->MySQLSelect($sql);
	
    //die($sql);

	
	
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo $SITE_NAME?> | Driver Online Times</title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		
		<!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
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
					<h2 class="header-page trip-detail driver-detail1">Driver Online Times</h2>
					<!-- trips page -->
					<div class="trips-page trips-page1">
						<?php if ($_REQUEST['success']==1) {?>
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
							} else if(isset($_REQUEST['success']) && $_REQUEST['success']==0){?>
							<div class="alert alert-danger alert-dismissable">
								<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
								<?php echo  $var_msg ?>
							</div>
							<?php }
						?>
						<div class="trips-table trips-table-driver trips-table-driver-res"> 
							<div class="trips-table-inner">
								<div class="driver-trip-table">
									<table width="100%" border="0" cellpadding="0" cellspacing="0" id="dataTables-example">
										<thead>
											<tr>
												<th width="5%">شماره</th>
												<th width="25%">نام راننده</th>
												<th>ساعت ورود</th>
												<th width="10%">ساعت خروج</th>
											</tr>
										</thead>
										<tbody>
											<?php
                                            $len = count($driver_requests);
                                            
                                            for ($i = 0; $i < $len; $i++) { ?>
                                            <?php 
                                                
                                                
                                                
                                                $dLoginTimeStamp = strtotime($driver_requests[$i]['dLoginDateTime']);
                                                $dLogoutTimeStamp = strtotime($driver_requests[$i]['dLogoutDateTime']);
                                                
                                                if(date('d',$dLoginTimeStamp) == date('d',time()))
                                                    $strdateLogin = 'امروز';
                                                else
                                                    $strdateLogin = jdate("d F",$dLoginTimeStamp);
                                                
                                                if(date('d',$dLogoutTimeStamp) == date('d',time()))
                                                    $strdateLogout = 'امروز';
                                                else
                                                    $strdateLogout = jdate("d F",$dLogoutTimeStamp);
                                            ?>
												<tr class="gradeA" >
                                                    <td style="<?php echo  $bgcolor; ?>"><?php echo  $driver_requests[$i]['iDriverLogId'] ?></td>
                                                    <td><?php echo  $driverName ?></td>
                                                    <td><?php echo  jdate("H:i:s", $dLoginTimeStamp); ?><br /><?php echo  $strdateLogin; ?></td>
                                                    <td><?php echo  jdate("H:i:s", $dLogoutTimeStamp); ?><br /><?php echo  $strdateLogout; ?></td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>  </div>
						</div>
						<!-- -->
						<?php //if(SITE_TYPE=="Demo"){?>
							<!--<div class="record-feature"> <span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
							This feature will be enabled in the main product we will provide you.</span> </div>
						<?php //}?> -->
						<!-- -->
					</div>
					<!-- -->
					<div style="clear:both;"></div>
				</div>
			</div>
			<!-- footer part -->
			<?php include_once('footer/footer_home.php');?>
			<!-- footer part end -->
            <!-- End:contact page-->
            <div style="clear:both;"></div>
		</div>
		<!-- home page end-->
		<!-- Footer Script -->
		<?php include_once('top/footer_script.php');?>
		<script src="assets/js/jquery-ui.min.js"></script>
		<script src="assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script type="text/javascript">
			$(document).ready(function () {
				$('#dataTables-example').dataTable({
						 "language": {
								<?php echo Datatablelang?>},
                         "order": [[ 0, 'desc' ]]
						
					});
			});
			function confirm_delete(id)
			{
				bootbox.confirm("<?php echo $langage_lbl['LBL_CONFIRM_DELETE_DRIVER'];?>", function(result) {
					if(result){
						document.getElementById('delete_form_'+id).submit();
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
			
			function add_driver_form(){
				window.location.href = "driver_action.php";
			}
		</script>
		
		<script type="text/javascript">
			$(document).ready(function(){
				$("[name='dataTables-example_length']").each(function(){
					$(this).wrap("<em class='select-wrapper'></em>");
					$(this).after("<em class='holder'></em>");
				});
				$("[name='dataTables-example_length']").change(function(){
					var selectedOption = $(this).find(":selected").text();
					$(this).next(".holder").text(selectedOption);
				}).trigger('change');
			})
		</script>
		<!-- End: Footer Script -->
	</body>
</html>

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

    $limit = 0;
    
    if(isset($_GET['limit']))
        $limit = intval($_GET['limit']);
    if($limit <= 200)
        $limit = 200;

    $sql = " SELECT drq.`iDriverRequestId`,drq.`iTripId`,drq.`eStatus`,drq.`tDate`,CONCAT(dr.vName,' ',dr.vLastName) as driverName, CONCAT(usr.vName,' ' ,usr.vLastName) as passengerName,trips.iActive as tripStatus FROM `driver_request` as drq LEFT JOIN `register_driver` as dr ON drq.`iDriverId` = dr.`iDriverId` LEFT JOIN `register_user` as usr ON drq.`iUserId` = usr.`iUserId` LEFT JOIN trips ON drq.iTripId = trips.iTripId WHERE dr.iCompanyId IN({$comp_list}) ORDER BY iDriverRequestId DESC LIMIT {$limit}";
	$driver_requests = $obj->MySQLSelect($sql);
	
    

	
	
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo $SITE_NAME?> | آخرین درخواست ها</title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		
		<!-- <link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" /> -->
		<!-- End: Default Top Script and css-->
		
		<!-- Mehrshad -->
        <?php
            $page = $_SERVER['PHP_SELF'];
            $sec = "20";
        ?>
        <meta http-equiv="refresh" content="<?php echo $sec?>;URL='<?php echo $page?>'">
        <!-- Mehrshad -->
    
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
					<h2 class="header-page trip-detail driver-detail1">آخرین درخواست ها</h2>
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
												<th width="5%">شماره درخواست</th>
												<th width="25%">نام مسافر</th>
												<th width="25%">نام راننده</th>
												<th>تاریخ درخواست</th>
												<th width="10%">شماره سفر</th>
												<th width="15%" style="width: 67px;">وضعیت</th>
											</tr>
										</thead>
										<tbody>
											<?php
                                            $len = count($driver_requests);
                                            
                                            for ($i = 0; $i < $len; $i++) { ?>
                                            <?php 
                                                if($driver_requests[$i]['eStatus'] == "Timeout")
                                                    continue;
                                                else if($driver_requests[$i]['eStatus'] == "Accept")
                                                    $bgcolor = "background-color:rgba(0,255,0,0.5);";
                                                else if($driver_requests[$i]['eStatus'] == "Decline")
                                                    $bgcolor = "background-color:red;";
                                                else
                                                    $bgcolor = "background-color:rgba(255,0,0,0.1);";
                                
                                                if($driver_requests[$i]['tripStatus'] == "Finished")
                                                    $tripcolor = "background-color:rgba(0,255,0,0.5);";
                                                else if($driver_requests[$i]['tripStatus'] == "Canceled")
                                                    $tripcolor = "background-color:rgba(255,0,0,0.5);";
                                                else
                                                    $tripcolor = '';
                                                
                                                $dtimeStamp = strtotime($driver_requests[$i]['tDate']);
                                                
                                                if(date('d',$dtimeStamp) == date('d',time()))
                                                    $strdate = 'امروز';
                                                else
                                                    $strdate = jdate("F-d",$dtimeStamp);
                                            ?>
												<tr class="gradeA" >
                                                    <td style="<?php echo  $bgcolor; ?>"><?php echo  $driver_requests[$i]['iDriverRequestId'] ?></td>
                                                    <td><?php echo  $driver_requests[$i]['passengerName'] ?></td>
                                                    <td><?php echo  $driver_requests[$i]['driverName'] ?></td>
                                                    <td><?php echo  jdate("H:i:s", $dtimeStamp); ?> <?php echo  $strdate; ?></td>
                                                    <td style="<?php echo  $tripcolor ?>"><?php echo  $driver_requests[$i]['iTripId'] ?><br /><?php echo  $driver_requests[$i]['tripStatus']?></td>
                                                    <td style="<?php echo  $bgcolor; ?>">
                                                    <?php
                                                        switch($driver_requests[$i]['eStatus'])
                                                        {
                                                            case 'Accept':
                                                                echo "قبول شده";
                                                                break;
                                                            case 'Timeout':
                                                                echo "نرسیده";
                                                                break;
                                                            case 'Arrived':
                                                                echo "رسیده به راننده";
                                                                break;
                                                            case 'Decline':
                                                                echo "رد شده";
                                                                break;
                                                            
                                                        }
                                                        ?>
                                                   </td>
                                                    
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

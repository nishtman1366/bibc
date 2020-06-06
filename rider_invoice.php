<?
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	$tbl_name 	= 'trips';

	$generalobj->check_member_login();
	$iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';
	$script="Trips";
	$sql = "select trips.*,vVehicleType as eCarType from trips left join vehicle_type on vehicle_type.iVehicleTypeId=trips.iVehicleTypeId where iTripId = '".$iTripId."'";
	$db_trip = $obj->MySQLSelect($sql);
	
	$sql = "SELECT vt.*,vc.vCategory_EN as vehcat from vehicle_type as vt LEFT JOIN vehicle_category as vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId where iVehicleTypeId = '".$db_trip[0]['iVehicleTypeId']."'";
	$db_vtype = $obj->MySQLSelect($sql); if($db_vtype[0]['vehcat'] != ""){		   $car = $db_vtype[0]['vehcat'].' - '.$db_vtype[0]['vVehicleType'];    }else{       $car = $db_vtype[0]['vVehicleType_'.$_SESSION['sess_lang']];    }

	//echo '<pre>'; print_R($db_trip); echo '</pre>';
	/* #echo '<pre>'; print_R($db_trip); echo '</pre>';
		$to_time = @strtotime($db_trip[0]['tStartDate']);
		$from_time = @strtotime($db_trip[0]['tEndDate']);
		$diff=round(abs($to_time - $from_time) / 60,2);
		$db_trip[0]['starttime'] = $generalobj->DateTime($db_trip[0]['tStartDate'],18);
		$db_trip[0]['endtime'] = $generalobj->DateTime($db_trip[0]['tEndDate'],18);
		$db_trip[0]['triptime'] = $diff;
	*/
	$sql = "select * from ratings_user_driver where iTripId = '".$iTripId."'";
	$db_ratings = $obj->MySQLSelect($sql);
	//echo"<pre>";print_r($db_ratings);exit;

	$rating_width = ($db_ratings[0]['vRating1'] * 100) / 5;
	$db_ratings[0]['vRating1'] = '<span style="display: block; width: 65px; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 0;">
		<span style="float: left !important; margin: 0;display: block; width: '.$rating_width.'%; height: 13px; background: url('.$tconfig['tsite_upload_images'].'star-rating-sprite.png) 0 -13px;"></span>
		</span>';
		//echo"<pre>";print_r($db_ratings);exit;
	$sql = "select * from register_driver where iDriverId = '".$db_trip[0]['iDriverId']."' LIMIT 0,1";
	$db_driver = $obj->MySQLSelect($sql);

	$sql = "select * from register_user where iUserId = '".$db_trip[0]['iUserId']."' LIMIT 0,1";
	$db_user = $obj->MySQLSelect($sql);
	
	$sql = "SELECT Ratio, vName, vSymbol FROM currency WHERE vName='".$db_user[0]['vCurrencyPassenger']."'";
    $db_curr_ratio = $obj->MySQLSelect($sql);

	$tripcursymbol=$db_curr_ratio[0]['vSymbol'];
	$tripcur=$db_curr_ratio[0]['Ratio'];
	$tripcurname=$db_curr_ratio[0]['vName'];
	
	$ts1 = strtotime($db_trip[0]['tStartDate']);
	$ts2 = strtotime($db_trip[0]['tEndDate']);
	$diff = abs($ts2 - $ts1);
	$years = floor($diff / (365*60*60*24)); $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
	$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	$hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
	$minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
	$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));
	$diff = $hours.':'.$minuts.':'.$seconds;

	$date1 = $db_trip[0]['tStartDate'];
	 $date2 = $db_trip[0]['tEndDate'];
	 $totalTimeInMinutes_trip=@round(abs(strtotime($date2) - strtotime($date1)) / 60,2);
	 //$distance=$db_trip[0]['fPricePerKM']*$db_trip[0]['fDistance'];
	 //$time=$db_trip[0]['fPricePerMin']*$totalTimeInMinutes_trip;
	//$total_fare=$db_trip[0]['iBaseFare']+($time)+($distance);
	//$commision=($total_fare*$db_trip[0]['fCommision'])/100;
	//$tot = $total_fare + ($commision);
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo $SITE_NAME?> | Login Page</title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
   
     <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&key=<?php echo $GOOGLE_SEVER_API_KEY_WEB?>"></script>

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
    		<div class="page-contant-inner page-trip-detail">
          		<h2 class="header-page trip-detail"><?php echo $langage_lbl['LBL_RIDER_Invoice']; ?>
          			<a href="javascript:void(0);" onClick="history.go(-1)"><img src="assets/img/arrow-white.png" alt="" /><?php echo $langage_lbl['LBL_RIDER_back_to_listing']; ?></a>
            		<p><?php echo $langage_lbl['LBL_RIDER_RATING_PAGE_HEADER_TXT']; ?> <strong><?php echo @jdate('h:i A',@strtotime($db_trip[0]['tStartDate']));?> در تاریخ <?php echo @jdate('d F Y',@strtotime($db_trip[0]['tStartDate']));?></strong></p>
          		</h2>
          		<!-- trips detail page -->
          		<div class="trip-detail-page">
                <div class="trip-detail-page-inner">
            		<div class="trip-detail-page-left">
              			<div class="trip-detail-map"><div id="map-canvas" class="gmap3" style="width:100%;height:200px;margin-bottom:10px;"></div></div>
              			<div class="map-address">
                			<ul>
                  				<li> 
                  					<b><i aria-hidden="true" class="fa fa-map-marker fa-22 green-location"></i></b> 
              						<span>
                    					<h3><?php echo @jdate('h:i A',@strtotime($db_trip[0]['tStartDate']));?></h3>
                						<?php echo $db_trip[0]['tSaddress'];?>
            						</span> 
        						</li>
        						<?php if($APP_TYPE != 'UberX'){ ?> 
              					<li> 
              						<b><i aria-hidden="true" class="fa fa-map-marker fa-22 red-location"></i></b> 
          							<span>
                    					<h3><?php echo @jdate('h:i A',@strtotime($db_trip[0]['tEndDate']));?></h3>
                    					<?php echo $db_trip[0]['tDaddress'];?>
                    				</span> 
                				</li>
                				<?php } ?> 
                			</ul>
              			</div>
              			<?php 
              			if($APP_TYPE == 'UberX'){

              				$class_name = 'location-time location-time-second';

              			}else{

              				$class_name = 'location-time';
              			}
              			?>
              			<div class="<?php echo $class_name;?>">
	            			<ul>
	                  			<li>
	                    			<h3><?php echo $langage_lbl['LBL_RIDER_RIDER_INVOICE_Car']; ?></h3>
	                    				<?php echo $db_vtype[0]['vehcat'].$car;?>
	            				</li>
	            					<?php if($APP_TYPE != 'UberX'){ ?> 
	                  			<li>
	                    			<h3><?php echo $langage_lbl['LBL_RIDER_DISTANCE_TXT']; ?></h3>
	                    			<?php echo $db_trip[0]['fDistance'];?>
	                			</li>
	                			<?php } ?>
	                  			<li>
	                    			<h3><?php echo $langage_lbl['LBL_RIDER_Trip_time']; ?></h3>
	                    			<?echo $diff;?>
	                			</li>
	                		</ul>
              			</div>
            		</div>
            		<div class="trip-detail-page-right">
              			<div class="driver-info">
              				<div class="driver-img">
              					<span class="invoice-img">
													<?php if($db_driver[0]['vImage'] != '' && file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $db_driver[0]['iDriverId'] . '/2_' . $db_driver[0]['vImage'])){?>
													<img src = "<?php echo  $tconfig["tsite_upload_images_driver"]. '/' . $db_driver[0]['iDriverId'] . '/2_' .$db_driver[0]['vImage'] ?>" style="height:150px;"/>
													<?php }else{ ?>
													<img src="assets/img/profile-user-img.png" alt="">
													<?php } ?></span>
              				</div>
                			<h3><?php echo $langage_lbl['LBL_RIDER_You_ride_with']; ?> <?php echo  $db_driver[0]['vName']?></h3>
                			<p><b><?php echo $langage_lbl['LBL_RIDER_Rate_Your_Ride']; ?>:</b><?php echo $db_ratings[0]['vRating1'];?></p>
              			</div>
          				<div class="fare-breakdown">
                			<div class="fare-breakdown-inner">
                  				<h3><?php echo $langage_lbl['LBL_RIDER_FARE_BREAK_DOWN_TXT']; ?></h3>
                  				<ul>
									<?
									
									if($db_trip[0]['eFareType'] != 'Fixed')
									{
										?>
										<li><strong><?php echo $langage_lbl['LBL_RIDER_Basic_Fare']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['iBaseFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<li><strong><?php echo $langage_lbl['LBL_RIDER_DISTANCE_TXT']; ?> (<?php echo $db_trip[0]['fDistance'];?>)</strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fPricePerKM'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<li><strong><?php echo $langage_lbl['LBL_RIDER_TIME_TXT']; ?> (<?echo $diff;?>)</strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fPricePerMin'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<?
									}
									else
									{
										?>
										<li><strong><?php echo $langage_lbl['LBL_RIDER_Total_Fare']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['iFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<?
									}
									if($db_trip[0]['fWalletDebit'] > 0)
									{
										?>
											<li><strong><?php echo $langage_lbl['LBL_RIDER_WALLET_DEBIT_MONEY']; ?></strong><b> - <?php echo $generalobj->trip_currency($db_trip[0]['fWalletDebit'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?> </b></li>
											<?
									}
									if($db_trip[0]['fDiscount'] > 0)
									{
										?>
										<li><strong><?php echo $langage_lbl['LBL_RIDER_DISCOUNT']; ?> </strong><b> - <?php echo $generalobj->trip_currency($db_trip[0]['fDiscount'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
										<?
									}
									if($db_trip[0]['fSurgePriceDiff'] > 0)
										{
											?>
											<li><strong><?php echo $langage_lbl['LBL_RIDER_SURGE_MONEY']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fSurgePriceDiff'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
											<?
										}
									?>

				                    <!-- <li><strong><?php echo $langage_lbl['LBL_RIDER_Commision']; ?></strong><b><?php echo $generalobj->trip_currency($db_trip[0]['fCommision'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li> -->

				                     <?php if($db_trip[0]['fMinFareDiff']!="" && $db_trip[0]['fMinFareDiff'] > 0){
			                            //$minimum_fare=round($db_trip[0]['fMinFareDiff'] * $db_trip[0]['fRatioPassenger'],1);
										$minimum_fare=$db_trip[0]['iBaseFare']+$db_trip[0]['fPricePerKM']+$db_trip[0]['fPricePerMin']+$db_trip[0]['fMinFareDiff'];
			                            ?>

			                           <li><strong><?php echo $generalobj->trip_currency($minimum_fare,$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b> <?php echo $langage_lbl['LBL_RIDER_MINIMUM']; ?>
			                              </strong><b>
			                              <?php echo $generalobj->trip_currency($db_trip[0]['fMinFareDiff'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></b></li>
			                          

			                          <?php }
			                          ?>
                  				</ul>
                  				<span><?php $paymentMode = ($db_trip[0]['vTripPaymentMode'] == 'Cash')? $langage_lbl['LBL_VIA_CASH_TXT']: $langage_lbl['LBL_VIA_CARD_TXT']?>
                  					<h4><?php echo $langage_lbl['LBL_RIDER_Total_Fare']; ?> (<?php echo $paymentMode;?>)</h4>
                  					<em><?php echo $generalobj->trip_currency($db_trip[0]['iFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);?></em>
              					</span>
                  				<div style="clear:both;"></div>

                  				<?php if($db_trip[0]['eType'] == 'Deliver'){ ?>
			                          <br>
			                        <h3><?php echo $langage_lbl['LBL_DELIVERY_DETAILS']?></h3><hr/>

			                        <ul style="border-bottom:none">
			                            <li><strong><?php echo $langage_lbl['LBL_RECEIVER_NAME']?></strong><b><?php echo $db_trip[0]['vReceiverName'];?></b></li>
			                            <li><strong><?php echo $langage_lbl['LBL_RECEIVER_MOBILE']?></strong><b><?php echo $db_trip[0]['vReceiverMobile'];?></b></li>
			                            <li><strong><?php echo $langage_lbl['LBL_PICKUP_INSTRUCTION']?></strong><b><?php echo $db_trip[0]['tPickUpIns'];?></b></li>
			                            <li><strong><?php echo $langage_lbl['LBL_DELIVERY_INSTRUCTION']?></strong><b><?php echo $db_trip[0]['tDeliveryIns'];?></b></li>
			                            <li><strong><?php echo $langage_lbl['LBL_PACKAGE_DETAILS']?></strong><b><?php echo $db_trip[0]['tPackageDetails'];?></b></li>
			                            <li><strong><?php echo $langage_lbl['LBL_DELIVERY_CONFIRMATION_CODE_TXT']?></strong><b><?php echo $db_trip[0]['vDeliveryConfirmCode'];?></b></li>
			                          
			                        </ul>

			                        <?php } ?>

                       				 <div style="clear:both;"></div>
                       				 <?php if($APP_TYPE == 'UberX' && $db_trip[0]['vBeforeImage'] != ''){
									 ?> 
										<h3 style="float:left; width:100%; margin:0 0 10px;"><?php echo $langage_lbl_admin['LBL_TRIP_DETAIL_HEADER_TXT'];?></h3>
										<div class="invoice-right-bottom-img">
											<div class="col-sm-6">											
												<h4>
												<?php														
												$img_path = $tconfig["tsite_upload_trip_images"];
												echo $langage_lbl_admin['LBL_SERVICE_BEFORE_TXT_ADMIN'];?></h4>
												 <b><a href="<?php echo  $img_path .$db_trip[0]['vBeforeImage'] ?>" target="_blank" ><img src = "<?php echo  $img_path.$db_trip[0]['vBeforeImage'] ?>" style="width:200px;" alt ="Before Images"/></b></a>
											</div>
											<div class="col-sm-6">
												<h4><?php echo $langage_lbl_admin['LBL_SERVICE_AFTER_TXT_ADMIN'];?></h4>
												 <b><a href="<?php echo  $img_path .$db_trip[0]['vBeforeImage'] ?>" target="_blank" ><img src = "<?php echo  $img_path.$db_trip[0]['vAfterImage'] ?>" style="width:200px;" alt ="After Images"/></b></a>
											</div>
										</div>

										<?php } ?>
                			</div>
              			</div>
            		</div>
                    </div>
            		<!-- -->
        		 	<?php //if(SITE_TYPE=="Demo"){?>
            		<!-- <div class="record-feature"> 
            			<span><strong>“Edit / Delete Record Feature”</strong> has been disabled on the Demo Admin Version you are viewing now.
              			This feature will be enabled in the main product we will provide you.</span> 
              		</div> -->
              		<?php //}?>
        		<!-- -->
          		</div>
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
    <script src="assets/js/gmap3.js"></script>
    <script type="text/javascript">
		h = window.innerHeight;
		$("#page_height").css('min-height', Math.round( h - 99)+'px');

		function from_to(){

			$("#map-canvas").gmap3({
				getroute:{
					options:{
						origin:'<?php echo  $db_trip[0]['tSaddress']?>',
						destination:'<?php echo  $db_trip[0]['tDaddress']?>',
						travelMode: google.maps.DirectionsTravelMode.DRIVING
					},
					callback: function(results){
						if (!results) return;
						$(this).gmap3({
							map:{
								options:{
									zoom: 13,
									center: [-33.879, 151.235]
								}
							},
							directionsrenderer:{
								options:{
									directions:results
								}
							}
						});
					}
				}
			});
		}
		from_to();
	</script>
    <!-- End: Footer Script -->
</body>
</html>

<?php
   	$action_from = isset($_REQUEST['action_from'])?$_REQUEST['action_from']:'';
   	$iTripId = isset($_REQUEST['iTripId'])?$_REQUEST['iTripId']:'';
    
   	if($action_from != '' && $iTripId != ''){
   		include_once('common.php');   
   		include_once('generalFunctions.php');  		
   		sendTripReceipt($iTripId);
   		//sendTripReceiptAdmin($iTripId);
   		header('Location: admin/invoice.php?iTripId='.$iTripId.'&success=1'); exit;
   	}
	####################### FUNCTIONS:for email receipt ##########################	

	function sendTripReceipt($iTripId){
		global $obj,$generalobj,$tconfig,$APP_TYPE;

		$Data = array();
		$sql = "SELECT * FROM trips WHERE iTripId = ".$iTripId;
		$db_trip = $obj->MySQLSelect($sql);
		//echo "<pre>";print_r($db_trip);echo "</pre>";
		$Data[0]['slocation'] = $db_trip[0]['tSaddress'];
		$Data[0]['elocation'] = $db_trip[0]['tDaddress'];

		$Data[0]['vReceiverName'] = $db_trip[0]['vReceiverName'];
		$Data[0]['vReceiverMobile'] = $db_trip[0]['vReceiverMobile'];
		$Data[0]['tPickUpIns'] = $db_trip[0]['tPickUpIns'];
		$Data[0]['tDeliveryIns'] = $db_trip[0]['tDeliveryIns'];
		$Data[0]['tPackageDetails'] = $db_trip[0]['tPackageDetails'];
		$Data[0]['vDeliveryConfirmCode'] = $db_trip[0]['vDeliveryConfirmCode'];


		$sql = "SELECT concat(vName,' ',vLastName) as name, iDriverId, vImage from register_driver where iDriverId = '".$db_trip[0]['iDriverId']."'";
		$db_driver = $obj->MySQLSelect($sql);
		$Data[0]['driver'] = $db_driver[0]['name'];

		$sql = "SELECT concat(vName,' ',vLastName) as rname,vEmail,vLang,vCurrencyPassenger FROM register_user where iUserId = '".$db_trip[0]['iUserId']."'";
		$db_user = $obj->MySQLSelect($sql);
		$Data[0]['rider'] = $db_user[0]['rname'];
		$Data[0]['email'] = $db_user[0]['vEmail'];
		$Data[0]['vLang'] = $db_user[0]['vLang'];
		
		
		$sql = "SELECT Ratio, vName, vSymbol FROM currency WHERE vName='".$db_user[0]['vCurrencyPassenger']."'";
		$db_curr_ratio = $obj->MySQLSelect($sql);

		$tripcursymbol=$db_curr_ratio[0]['vSymbol'];
		$tripcur=$db_curr_ratio[0]['Ratio'];
		$tripcurname=$db_curr_ratio[0]['vName'];
		
		/*############### language code################*/
		$user_lang_code = $Data[0]['vLang'];
        if($user_lang_code == ""){
        	$user_lang_code = "EN";
        }
       // echo $user_lang_code; exit;	
	   
	   if($user_lang_code == "PS"){
			$dir_lng="rtl";
			$txt_right = "left";
			$txt_left = "right";
		}else{
			$dir_lng="ltr";
			$txt_right = "right";
			$txt_left = "left";
		}

		$vLabel_user_mail = array();	
		$sql="select vLabel,vValue from language_label where vCode='".$user_lang_code."'";
		$db_lbl=$obj->MySQLSelect($sql);
		    
		    foreach ($db_lbl as $key => $value) {
		    	$vLabel_user_mail[$value['vLabel']] = $value['vValue'];	           
		}

		/*Language Label Other*/
		$sql="select vLabel,vValue from language_label_other where vCode='".$user_lang_code."'";
		$db_lbl=$obj->MySQLSelect($sql);
		foreach ($db_lbl as $key => $value) {
			$vLabel_user_mail[$value['vLabel']] = $value['vValue'];
		}

		//echo "<pre>"; print_r($vLabel_user_mail); exit;
   $mailcont_member_trips_img ='';
	 if($APP_TYPE == 'UberX' && $db_trip[0]['vBeforeImage'] != ''){

		 	$mailcont_member_trips_img='<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;color:#959595;line-height:14px;padding:0">
				<tbody>
			<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
				<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
					<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_SERVICE_BEFORE_TXT_ADMIN'].'</span><br>
					<span style="font-size:13px;color:#111125;font-weight:normal">
					<a href="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vBeforeImage'].'" target="_blank"><img src="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vBeforeImage'].'"  style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left" height="100" width="100" class="CToWUd"></a>
					</span>
				</td>
				<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
					<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_SERVICE_AFTER_TXT_ADMIN'].'</span><br>
					<span style="font-size:13px;color:#111125;font-weight:normal">
					<a href="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vAfterImage'].'" target="_blank"><img src="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vAfterImage'].'"  style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left" height="100" width="100" class="CToWUd"></a>
					</span>
				</td>

				</tr></tbody></table>';

	 }


		$mailcont_member_trips ='';
		if($db_trip[0]['eType'] == 'Deliver'){		

			$mailcont_member_trips='
				 <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%!important;padding:0">
						<tbody>
							<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
									<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
									</p>
								</td>
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:center;display:table-cell;width:120px!important;font-size:11px;white-space:pre-wrap;padding:12px 10px 5px" align="center" valign="middle">'.$vLabel_user_mail['LBL_DELIVERY_DETAILS_ADMIN'].'</td>
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
									<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				 <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';margin-top:15px;width:auto;padding:0">
					<tbody>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_user_mail['LBL_RECEIVER_NAME'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$Data[0]['vReceiverName'].'</td>
						</tr>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_user_mail['LBL_RECEIVER_MOBILE'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$Data[0]['vReceiverMobile'].'</td>
						</tr>

						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_user_mail['LBL_PICK_UP_INS'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$Data[0]['tPickUpIns'].'</td>
						</tr>						

						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_user_mail['LBL_DELIVERY_INS'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$Data[0]['tDeliveryIns'].'</td>
						</tr>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_user_mail['LBL_PACKAGE_DETAILS'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$Data[0]['tPackageDetails'].'</td>
						</tr>

						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_user_mail['LBL_DELIVERY_CONFIRMATION_CODE_TXT'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$Data[0]['vDeliveryConfirmCode'].'</td>
						</tr>				

					</tbody>
				</table>';

		}

		$sql = "SELECT * from ratings_user_driver where iTripId = '".$iTripId."' and eUserType = 'Passenger'";
		$db_rating = $obj->MySQLSelect($sql);
		// $Data[0]['vRating'] = $db_rating[0]['vRating1'];
		$Data[0]['vRating'] = $db_rating[0]['vRating1'];

		/*######## for total-time ########*/
		$to_time = strtotime($db_trip[0]['tStartDate']);
        $from_time = strtotime($db_trip[0]['tEndDate']);
        $total_time = round(abs($to_time - $from_time) / 60,2). " minute";
		$Data[0]['tot_time'] = $total_time;
		/*######## for total-time end ########*/

		$date1 = $db_trip[0]['tStartDate'];
        $date2 = $db_trip[0]['tEndDate'];
		$totalTimeInMinutes_trip=@round(abs(strtotime($date2) - strtotime($date1)) / 60,2);

        $diff = abs(strtotime($date2) - strtotime($date1));
        $years = floor($diff / (365*60*60*24)); $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
        $minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
        $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));
		$Data[0]['time_taken'] = $hours.':'.$minuts.':'.$seconds;
		//$sql = "SELECT * from vehicle_type where iVehicleTypeId = '".$db_trip[0]['iVehicleTypeId']."'";
		$sql = "SELECT vt.*,vc.vCategory_EN as vehcat from vehicle_type as vt LEFT JOIN vehicle_category as vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId where iVehicleTypeId = '".$db_trip[0]['iVehicleTypeId']."'";
		$db_vtype = $obj->MySQLSelect($sql);

		// $priceRatio=($obj->MySQLSelect("SELECT Ratio FROM currency WHERE vName='".$db_trip[0]['vCurrencyPassenger']."' ")[0]['Ratio']);
		$priceRatio = get_value('currency', 'Ratio', 'vName', $db_trip[0]['vCurrencyPassenger'],'','true');
		
		

		$distance=$generalobj->trip_currency($db_trip[0]['fPricePerKM'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);
		$time=$generalobj->trip_currency($db_trip[0]['fPricePerMin'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);

		$total_amt=$generalobj->trip_currency($db_trip[0]['iFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);
		$fCommision= $generalobj->trip_currency($db_trip[0]['fCommision'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);
		$basefare=$generalobj->trip_currency($db_trip[0]['iBaseFare'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);		

		$eFareType = $db_trip[0]['eFareType'];		

		if(file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $db_driver[0]['iDriverId'] . '/2_' . $db_driver[0]['vImage'])){
			$img=$tconfig["tsite_upload_images_driver"]. '/' . $db_driver[0]['iDriverId'] . '/2_' .$db_driver[0]['vImage'];
		}
		else{
			$img=$tconfig["tsite_url"]."webimages/icons/help/driver.png";
		}

		$Data[0]['driver'] = $db_driver[0]['name'];

		//$car = $db_vtype[0]['vVehicleType'];
    if($db_vtype[0]['vehcat'] != ""){
		   $car = $db_vtype[0]['vehcat'].' - '.$db_vtype[0]['vVehicleType'];
    }else{
       $car = $db_vtype[0]['vVehicleType'];
    }
		$payment_mode = $db_trip[0]['vTripPaymentMode'];
		$ridenum = $db_trip[0]['vRideNo'];

		// $Data[0]['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE vName='".$db_trip[0]['vCurrencyPassenger']."' ")[0]['vSymbol']);
		$Data[0]['CurrencySymbol']=get_value('currency', 'vSymbol', 'vName', $db_trip[0]['vCurrencyPassenger'],'','true');

		$Data[0]['ProjectName'] = $generalobj->getConfigurations("configurations","SITE_NAME");
		$Data[0]['ProjectName1'] ='<img class="logo" src="'.$tconfig["tsite_home_images"].'logo.png" alt="">';
		$Data[0]['car'] = $car;
		$Data[0]['basefare'] = $basefare;
		$Data[0]['distance'] = $distance;
		$Data[0]['time'] = $time;
		$Data[0]['total_amt'] = $total_amt;
		$Data[0]['fCommision'] = $fCommision;
		$Data[0]['payment_mode'] = $payment_mode;
		$Data[0]['payment_mode_lbl'] = ($payment_mode == "Cash" ) ? $vLabel_user_mail['LBL_CASH_TXT'] : $payment_mode;
		$Data[0]['ridenum'] = $ridenum;
		$sql = "SELECT * from configurations where vName = 'COPYRIGHT_TEXT'";
		$copy = $obj->MySQLSELECT($sql);
		
		$Data[0]['copyright'] = $copy[0]['vValue'];
		$start_time = $generalobj->DateTime($db_trip[0]['tStartDate'],12);
		$endtime = $generalobj->DateTime($db_trip[0]['tEndDate'],12);
	    $kms = $db_trip[0]['fDistance'];
		$Data[0]['start_time'] = $start_time;
		$Data[0]['endtime'] = $endtime;
		$Data[0]['kms'] = $kms;
    $disp_km_txt ='';
	
	
		if($APP_TYPE != 'UberX'){

			$disp_km_txt ='<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
				<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_KILOMETERS_TXT_ADMIN'].'</span><br><span style="font-size:13px;color:#111125;font-weight:normal">'.$Data[0]['kms'].'</span>
			</td>';
		}
		$email_con_location ='';
		if($APP_TYPE != 'UberX'){
			$email_con_location ='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
			<td rowspan="2" style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:17px!important;padding:3px 10px 10px 17px" align="left" valign="top">
				<img src="'.$tconfig["tsite_url"].'webimages/icons/help/route_line.png" style="outline:none;text-decoration:none;float:'.$txt_left.';clear:both;display:block" align="left" height="80" width="13" class="CToWUd">
			</td>
		
			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:279px;line-height:16px;height:57px;" align="left" valign="top">
				<span style="font-size:15px;font-weight:500;color:#000000!important">
					<span class="aBn" data-term="goog_43159640" tabindex="0">
						<span class="aQJ">'.$Data[0]['start_time'].'</span>
					</span>
				</span>
				<br>
				<span style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">'.$Data[0]['slocation'].'</span>
			</td>
		</tr>
		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">

			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:279px;line-height:16px;height:auto;padding:0 0px 0 0" align="left" valign="top">
				<span style="font-size:15px;font-weight:500;color:#000000!important">
					<span class="aBn" data-term="goog_43159641" tabindex="0">
						<span class="aQJ">'.$Data[0]['endtime'].'</span>
					</span>
				</span><br>
				<span style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">'.$Data[0]['elocation'].'</span>
			</td>
		</tr>';

		}else{

			$email_con_location ='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left"><td rowspan="2" style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:17px!important;padding:3px 10px 10px 17px" align="left" valign="top">
				<img src="'.$tconfig["tsite_url"].'webimages/icons/help/green-lolo.png" style="outline:none;text-decoration:none;float:'.$txt_left.';clear:both;display:block" align="left"  width="13" class="CToWUd">
			</td>			
		</tr>
		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">

			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:279px;line-height:16px;height:auto;padding:0 0px 0 0" align="left" valign="top">
				<span style="font-size:15px;font-weight:500;color:#000000!important">
					<span class="aBn" data-term="goog_43159641" tabindex="0">
						<span class="aQJ">'.$Data[0]['endtime'].'</span>
					</span>
				</span><br>
				<span style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">'.$Data[0]['elocation'].'</span>
			</td>
		</tr>';
		}
		
		$tripDeleteByDriverStatus='';
		if($db_trip[0]['eCancelled']=="Yes"){
			$tripDeleteByDriverStatus = '<table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:'.$txt_left.';width:auto;padding:0">
					<tbody>
						<tr style="vertical-align:middle;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:100%;padding:5px 5px 5px" align="left" valign="middle">
								<span style="padding-bottom:5px;display:inline-block">'.$vLabel_user_mail['LBL_TRIP_CANCEL_DRIVER_REASON'].' '.$db_trip[0]['vCancelReason'].'</span>
							</td>
						</tr>
					</tbody>
				</table>';
		}
		
		$discount_area ="";
		if($db_trip[0]['fDiscount']!="" && $db_trip[0]['fDiscount'] != 0){
			$discount_fare=$generalobj->trip_currency($db_trip[0]['fDiscount'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname);
			$discount_area='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">Promo Code Discount</td>
			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top"> - '.$discount_fare.'</td>
			</tr>';
		}
    
    $minimum_fare ="";
		if($db_trip[0]['fMinFareDiff']!="" && $db_trip[0]['fMinFareDiff'] > 0){
			$minimum_farePrice=$db_trip[0]['iBaseFare']+$db_trip[0]['fPricePerKM']+$db_trip[0]['fPricePerMin']+$db_trip[0]['fMinFareDiff'];
			$minimum_fare='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0;border-top:1px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;" align="left">
			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top"> '.$generalobj->trip_currency($minimum_farePrice,$db_trip[0]['fRatio_'.$tripcurname],$tripcurname).' '.$vLabel_user_mail['LBL_MINIMUM'].'</td>
			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align="right" valign="top">'.$generalobj->trip_currency($db_trip[0]['fMinFareDiff'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname).'</td>
			</tr>';
		}
		

		# User Email below code
		$mailcont_member =
'<div style="width:730px;!important;color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,
Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:19px;font-size:14px;margin:0;padding:0">
<table  style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;line-height:19px;font-size:14px;margin:0;padding:0"><tbody><tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left"><td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;padding:0" align="center" valign="top">
	<center style="width:100%;min-width:580px">
		<table style="border-color:#e3e3e3;border-style:solid;border-width:1px 1px 1px 1px;vertical-align:top;text-align:inherit;width:660px;margin:0 auto;padding:0"><tbody><tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left"><td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';padding:0" align="left" valign="top">
			<table style="vertical-align:top;text-align:'.$txt_left.';width:640px;margin:0 10px;padding:0">
				<tbody >
					<!--<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
						<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;padding:28px 0" align="left" valign="top" width="127">
							
							<span style="color:white;">'.$Data[0]['ProjectName1'].'</span>
						</td>
						<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;font-size:11px;color:#999999;line-height:15px;text-transform:uppercase;padding:30px 0 26px" align='.$txt_right.' valign="top">
							<span>'.$vLabel_user_mail['LBL_RIDE_NUMBER_TXT_ADMIN'].':'.$Data[0]['ridenum'].'</span>
						</td>   
					</tr>   -->
				</tbody>
			</table>
			<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:640px;max-width:640px;border-radius:2px;background-color:#ffffff;margin:0 10px;padding:0" bgcolor="#ffffff">
				<tbody>
					<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
						<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:100%;padding:0" align="left" valign="top">
							<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#e3e3e3;border-bottom-style:solid;padding:0">
								<tbody>
									<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;background-color:rgb(250,250,250);padding:0" align="left" bgcolor="rgb(250,250,250)">
										
										<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:295px;border-radius:0 3px 0 0;background-color:#fafafa;padding:26px 10px 20px" align='.$txt_left.' bgcolor="#FAFAFA" valign="top">
											<span style="vertical-align:top;text-align:'.$txt_left.';font-size:11px;color:#999999;text-transform:uppercase;padding-right:10px">'.$vLabel_user_mail['LBL_RIDE_NUMBER_TXT_ADMIN'].':'.$Data[0]['ridenum'].'</span> <BR/>
											<span style="padding-right:10px;line-height:10px;font-size:13px;font-weight:normal;color:#b2b2b2">'.$vLabel_user_mail['LBL_THANKS_FOR_CHOOSING_TXT_ADMIN'].' '.$Data[0]['ProjectName'].', '.$Data[0]['rider'].'</span>
										</td>
										<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:inline-block;width:299px;border-radius:3px 0 0 0;background-color:#fafafa;padding:26px 10px 20px" align="left" bgcolor="#FAFAFA" valign="top">
											<span style="font-weight:bold;font-size:32px;color:#000;line-height:30px;padding-left:15px">
												'.$Data[0]['total_amt'].'
											</span>
										</td>
									</tr>
								</tbody>
							</table>
							<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;padding:0">
								<tbody>
									<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
										<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:300px;padding:25px 10px 25px 5px" align="left" valign="top">
											<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';margin-left:19px;padding:0">
												<tbody>
													<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
														<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:300px;padding:0" align="left" valign="top">
															
															<div class="a6S" dir="ltr" style="opacity: 0.01; left: 432.922px; top: 670px;">
																<div id=":n0" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Download attachment map_32aba2dc-7679-4c0e-bea3-7f4d8e8f934a" data-tooltip-class="a1V" data-tooltip="Download">
																	<div class="aSK J-J5-Ji aYr"></div>
																</div>
																<div id=":n1" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Save attachment to Drive map_32aba2dc-7679-4c0e-bea3-7f4d8e8f934a" data-tooltip-class="a1V" data-tooltip="Save to Drive">
																	<div class="wtScjd J-J5-Ji aYr aQu">
																		<div class="T-aT4" style="display: none;">
																			<div></div>
																			<div class="T-aT4-JX"></div>
																		</div>
																	</div>
																</div>
															</div>
														</td>
													</tr>
													<tr style="vertical-align:top;text-align:'.$txt_left.';width:279px;display:block;background-color:#fafafa;padding:20px 0;border-color:#e3e3e3;border-style:solid;border-width:1px 1px 0px" align="left" bgcolor="#FAFAFA">
														<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:279px;padding:0" align="left" valign="top">
															<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:auto;padding:0">
																<tbody>
																	'.$email_con_location.'
																</tbody>
															</table>
														</td>
													</tr>
													<tr style="vertical-align:top;text-align:'.$txt_left.';width:279px;display:block;background-color:#fafafa;padding:0;border:1px solid #e3e3e3" align="left" bgcolor="#FAFAFA">
														<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell!important;width:279px!important;padding:0" align="left" valign="top">
															<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;color:#959595;line-height:14px;padding:0">
																<tbody>
																	<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																		<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
																			<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_CAR_ADMIN'].'</span><br>
																			<span style="font-size:13px;color:#111125;font-weight:normal">'.$Data[0]['car'].'</span>
																		</td>
																		'.$disp_km_txt.'
																		<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
																			<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_TRIP_TIME_TXT_ADMIN'].'</span><br><span style="font-size:13px;color:#111125;font-weight:normal"><span class="aBn" data-term="goog_43159642" tabindex="0"><span class="aQJ">'.$Data[0]['time_taken'].'</span></span></span>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
										<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:300px;padding:10px" align="left" valign="top">
											<span style="display:block;padding:0px 8px 0 10px">
												<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%!important;padding:0">
													<tbody>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
																<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
																</p>
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:center;display:table-cell;width:120px!important;font-size:11px;white-space:pre-wrap;padding:12px 10px 5px" align="center" valign="middle">'.$vLabel_user_mail['LBL_FARE_BREAKDOWN'].'</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
																<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
																</p>
															</td>
														</tr>
													</tbody>
												</table>
												<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';margin-top:15px;width:auto;padding:0">
													<tbody>';
														if($eFareType != 'Fixed')
														{
														$mailcont_member .= '
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																'.$vLabel_user_mail['LBL_BASE_FARE_SMALL_TXT'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['basefare'].'</td>
														</tr>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																'.$vLabel_user_mail['LBL_DISTANCE_TXT'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['distance'].'</td>
														</tr>

														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																'.$vLabel_user_mail['LBL_TIME_TXT'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['time'].'</td>
														</tr>';
														}
														else
														{
														$mailcont_member .= '<tr style="vertical-align:top;text-align:'.$txt_left.';border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px 4px 5px" align="left" valign="top">
																'.$vLabel_user_mail['LBL_Total_Fare'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px 4px 5px" align='.$txt_right.' valign="top">'.$Data[0]['total_amt'].'</td>
														</tr>';
														}
														if($db_trip[0]['fWalletDebit'] > 0)
														{
															$mailcont_member .= '<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																	'.$vLabel_user_mail['LBL_WALLET_DEBIT_MONEY'].'
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">- '.$generalobj->trip_currency($db_trip[0]['fWalletDebit'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname).'</td>
															</tr>';
														}
														if($db_trip[0]['fSurgePriceDiff'] > 0)
														{
														$mailcont_member .= '<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																'.$vLabel_user_mail['LBL_SURGE_MONEY'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$generalobj->trip_currency($db_trip[0]['fSurgePriceDiff'],$db_trip[0]['fRatio_'.$tripcurname],$tripcurname).'</td>
														</tr>';
														}

														$mailcont_member .=$discount_area.'
														'.$minimum_fare.'
														<tr style="vertical-align:top;text-align:'.$txt_left.';font-weight:bold;width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#111125;padding:5px 4px 4px" align="left" valign="top">'.$vLabel_user_mail['LBL_SUBTOTAL_TXT'].'</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:5px 4px 4px" align='.$txt_right.' valign="top">'.$Data[0]['total_amt'].'</td>
														</tr>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;line-height:18px;padding:5px 4px" align="left" valign="top">
																<span style="font-size:9px;line-height:7px">'.$vLabel_user_mail['LBL_CHARGED_TXT'].'</span>
																<br>

																<img src="'.paymentimg($Data[0]['payment_mode']).'" style="outline:none;text-decoration:none;float:left;clear:both;display:block;width:40px!important;min-height:25px;margin-right:5px;margin-top:3px" align="left" height="12" width="17" >
																<span style="font-size:13px">
																	'.$Data[0]['payment_mode_lbl'].'
																</span>
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;font-size:19px;font-weight:bold;line-height:30px;padding:20px 4px 5px" align='.$txt_right.' valign="top">
																'.$Data[0]['total_amt'].'
															</td>
														</tr>
													</tbody>
												</table>
												'.$mailcont_member_trips.'
                        '.$mailcont_member_trips_img.'	
											</span>
										</td>
									</tr>
								</tbody>
							</table>
							
							'.$tripDeleteByDriverStatus.'
							
							
							<table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:'.$txt_left.';width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;padding:0">
								<tbody>
									<tr style="vertical-align:middle;text-align:'.$txt_left.';width:100%;padding:0" align="left">
									<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:inline-block;width:48%;height:100%;padding:0px 0px 0px" align="left" valign="middle">
											<table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:'.$txt_left.';width:100%;max-width:640px;display:block;padding:20px 0px 0px">
												<tbody style="width:100%;display:block">
													<tr style="vertical-align:middle;text-align:'.$txt_left.';width:100%;display:block;padding:0px" align='.$txt_left.'>
														<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:inline;font-size:12px;color:#808080;text-transform:uppercase;padding:0px 5px 0px 0px" align="left" valign="middle">
															<span>'.$vLabel_user_mail['LBL_TRIP_RATING_TXT'].'</span>
														</td>
														<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:inline-block" align="left" valign="middle">
															<span style="font-size:11px;display:inline-block!important;padding:0px 2px">'.ratingmark($Data[0]['vRating']).'</span>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
										<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:inline-block;width:50%;padding:0px" align="left" valign="middle">
											<table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:'.$txt_left.';width:100%;max-width:640px;display:inline-block;padding:0">
												<tbody>
													<tr style="vertical-align:middle;text-align:'.$txt_left.';width:100%;padding:0" align="left">
														<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:inline-block;width:100%!important;line-height:15px;padding:0px 0px 0px 10px" align="left" valign="middle">
															<table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:'.$txt_left.';width:auto;padding:0">
																<tbody>
																	<tr style="vertical-align:middle;text-align:'.$txt_right.';width:100%;padding:0" align="left">
																		<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_right.';display:table-cell;width:300px;padding:5px 15px 5px" align="left" valign="middle">
																			<span style="padding-bottom:5px;display:inline-block">'.$vLabel_user_mail['LBL_You_ride_with'].' '.$Data[0]['driver'].'</span>
																		</td>
																		<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:45px;padding:5px 0px 5px" align="left" valign="middle">
																			<img src="'.$img.'" style="outline:none;text-decoration:none;float:left;clear:both;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;margin-left:15px;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7" align="left" height="45" width="45" class="CToWUd">
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
												</tbody>
											</table>
										</td>
										
									</tr>
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</td>
		</tr>
		</tbody>
		</table>
		 
	
	</div>';
			// echo $mailcont_member;exit;
			 $maildata_member['details'] = $mailcont_member;
			$maildata_member['email'] = $Data[0]['email'];


	  return $generalobj->send_email_user("RIDER_INVOICE",$maildata_member);
	}
	
	####################### for email receipt end ##########################
	
	####################### for email receipt to admin ##########################
	
	function sendTripReceiptAdmin($iTripId){
			global $obj,$generalobj,$tconfig,$APP_TYPE;

			$Data = array();
			$sql = "SELECT * FROM trips WHERE iTripId = ".$iTripId;
			$db_trip = $obj->MySQLSelect($sql);
			//echo "<pre>";print_r($db_trip);echo "</pre>";
      
			$Data[0]['elocation'] = $db_trip[0]['tDaddress'];
			$Data[0]['slocation'] = $db_trip[0]['tSaddress'];

			$Data[0]['vReceiverName'] = $db_trip[0]['vReceiverName'];
			$Data[0]['vReceiverMobile'] = $db_trip[0]['vReceiverMobile'];
			$Data[0]['tPickUpIns'] = $db_trip[0]['tPickUpIns'];
			$Data[0]['tDeliveryIns'] = $db_trip[0]['tDeliveryIns'];
			$Data[0]['tPackageDetails'] = $db_trip[0]['tPackageDetails'];
			$Data[0]['vDeliveryConfirmCode'] = $db_trip[0]['vDeliveryConfirmCode'];


			$vLabel_admin_mail = array();	
			$sql1="select vLabel,vValue from language_label where vCode='EN'";
			$db_lbl_admin=$obj->MySQLSelect($sql1);
			    
			    foreach ($db_lbl_admin as $key => $value) {
			    	$vLabel_admin_mail[$value['vLabel']] = $value['vValue'];	           
			}

			/*Language Label Other*/
			$sql2="select vLabel,vValue from language_label_other where vCode='EN'";
			$db_lbl_admin=$obj->MySQLSelect($sql2);
			foreach ($db_lbl_admin as $key => $value) {
				$vLabel_admin_mail[$value['vLabel']] = $value['vValue'];
			}
			
			 $user_lang_code = "EN";
			 if($user_lang_code == "PS"){
				$dir_lng="rtl";
				$txt_right = "left";
				$txt_left = "right";
			}else{
				$dir_lng="ltr";
				$txt_right = "right";
				$txt_left = "left";
			}


			//echo "<pre>"; print_r($vLabel_admin_mail); exit;
    $mailcont_member_trips_img ='';
		 if($APP_TYPE == 'UberX' && $db_trip[0]['vBeforeImage'] != ''){

		 	$mailcont_member_trips_img='<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;color:#959595;line-height:14px;padding:0">
				<tbody>
			<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
				<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
					<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_SERVICE_BEFORE_TXT_ADMIN'].'</span><br>
					<span style="font-size:13px;color:#111125;font-weight:normal">
					<a href="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vBeforeImage'].'" target="_blank"><img src="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vBeforeImage'].'"  style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left" height="100" width="100" class="CToWUd"></a>
					</span>
				</td>
				<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
					<span style="font-size:9px;text-transform:uppercase">'.$vLabel_user_mail['LBL_SERVICE_AFTER_TXT_ADMIN'].'</span><br>
					<span style="font-size:13px;color:#111125;font-weight:normal">
					<a href="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vAfterImage'].'" target="_blank"><img src="'.$tconfig["tsite_upload_trip_images"].$db_trip[0]['vAfterImage'].'"  style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left" height="100" width="100" class="CToWUd"></a>
					</span>
				</td>

				</tr></tbody></table>';

		}			

		$mailcont_member_trips ='';
		if($db_trip[0]['eType'] == 'Deliver'){		

			$mailcont_member_trips='
				 <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%!important;padding:0">
						<tbody>
							<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
									<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
									</p>
								</td>
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:center;display:table-cell;width:120px!important;font-size:11px;white-space:pre-wrap;padding:12px 10px 5px" align="center" valign="middle">'.$vLabel_admin_mail['LBL_DELIVERY_DETAILS_ADMIN'].'</td>
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
									<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				 <table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';margin-top:15px;width:auto;padding:0">
					<tbody>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_admin_mail['LBL_RECEIVER_NAME'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['vReceiverName'].'</td>
						</tr>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_admin_mail['LBL_RECEIVER_MOBILE'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['vReceiverMobile'].'</td>
						</tr>

						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_admin_mail['LBL_PICK_UP_INS'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['tPickUpIns'].'</td>
						</tr>						

						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_admin_mail['LBL_DELIVERY_INS'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['tDeliveryIns'].'</td>
						</tr>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_admin_mail['LBL_PACKAGE_DETAILS'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['tPackageDetails'].'</td>
						</tr>

						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
								'.$vLabel_admin_mail['LBL_DELIVERY_CONFIRMATION_CODE_TXT'].'
							</td>
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['vDeliveryConfirmCode'].'</td>
						</tr>				

					</tbody>
				</table>';

		}


			$sql = "SELECT concat(vName,' ',vLastName) as name, iDriverId, vImage,vEmail,vPhone from register_driver where iDriverId = '".$db_trip[0]['iDriverId']."'";
			$db_driver = $obj->MySQLSelect($sql);
			$Data[0]['driver'] = $db_driver[0]['name'];
			//echo "<pre>";print_r($db_driver);echo "</pre>";

			$sql = "SELECT concat(vName,' ',vLastName) as rname,vEmail,vImgName FROM register_user where iUserId = '".$db_trip[0]['iUserId']."'";
			$db_user = $obj->MySQLSelect($sql);
			$Data[0]['rider'] = $db_user[0]['rname'];
			$Data[0]['email'] = $db_user[0]['vEmail'];
			//echo "<pre>";print_r($db_driver);echo "</pre>";
			$sql = "SELECT * from ratings_user_driver where iTripId = '".$iTripId."' and eUserType = 'Passenger'";
			$db_rating = $obj->MySQLSelect($sql);
			$Data[0]['vRating'] = $db_rating[0]['vRating1'];
			#echo "<pre>";print_r($db_rating);echo "</pre>";

			/*######## for total-time ########*/
			$to_time = strtotime($db_trip[0]['tStartDate']);
	        $from_time = strtotime($db_trip[0]['tEndDate']);
	        $total_time = round(abs($to_time - $from_time) / 60,2). " minute";
			$Data[0]['tot_time'] = $total_time;
			/*######## for total-time end ########*/

			$date1 = $db_trip[0]['tStartDate'];
	        $date2 = $db_trip[0]['tEndDate'];
			$totalTimeInMinutes_trip=@round(abs(strtotime($date2) - strtotime($date1)) / 60,2);

	        $diff = abs(strtotime($date2) - strtotime($date1));
	        $years = floor($diff / (365*60*60*24)); $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
			$days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	        $hours = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
	        $minuts = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
	        $seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));
			$Data[0]['time_taken'] = $hours.':'.$minuts.':'.$seconds;
			
			$sql = "SELECT vt.*,vc.vCategory_EN as vehcat from vehicle_type as vt LEFT JOIN vehicle_category as vc ON vc.iVehicleCategoryId = vt.iVehicleCategoryId where iVehicleTypeId = '".$db_trip[0]['iVehicleTypeId']."'";
			$db_vtype = $obj->MySQLSelect($sql);
			$eFareType = $db_trip[0]['eFareType'];
			
			$Symbole=get_value('currency', 'vName', 'eDefault', 'Yes','','true');
			
			$priceRatio=get_value('currency', 'Ratio', 'eDefault', 'Yes','','true');
			
			$distance=$generalobj->trip_currency($db_trip[0]['fPricePerKM']);
			$time=$generalobj->trip_currency($db_trip[0]['fPricePerMin']);

			$total_amt=$generalobj->trip_currency($db_trip[0]['iFare']);
			$fCommision=  $generalobj->trip_currency($db_trip[0]['fCommision']);
			$basefare=$generalobj->trip_currency($db_trip[0]['iBaseFare']);	

			if(file_exists($tconfig["tsite_upload_images_driver_path"]. '/' . $db_driver[0]['iDriverId'] . '/2_' . $db_driver[0]['vImage'])){
				$img=$tconfig["tsite_upload_images_driver"]. '/' . $db_driver[0]['iDriverId'] . '/2_' .$db_driver[0]['vImage'];
			}
			else{
				$img=$tconfig["tsite_url"]."webimages/icons/help/driver.png";
			}

			if(file_exists($tconfig["tsite_upload_images_passenger_path"]. '/' . $db_user[0]['iUserId'] . '/2_' . $db_user[0]['vImgName'])){
				$img1=$tconfig["tsite_upload_images_passenger"]. '/' . $db_user[0]['iUserId'] . '/2_' .$db_user[0]['vImgName'];
			}
			else{
				$img1=$tconfig["tsite_url"]."webimages/icons/help/taxi_passanger.png";
			}

			$Data[0]['driver'] = $db_driver[0]['name'];
			$Data[0]['user']=$db_user[0]['rname'];
			$Data[0]['email']=$db_driver[0]['vPhone'];
			$Data[0]['uEmail']=$db_user[0]['vEmail'];

			//$car = $db_vtype[0]['vVehicleType'];
			//$car = $db_vtype[0]['vehcat'].' - '.$db_vtype[0]['vVehicleType'];
      if($db_vtype[0]['vehcat'] != ""){
		   $car = $db_vtype[0]['vehcat'].' - '.$db_vtype[0]['vVehicleType'];
      }else{
         $car = $db_vtype[0]['vVehicleType'];
      }
			$payment_mode = $db_trip[0]['vTripPaymentMode'];
			$ridenum = $db_trip[0]['vRideNo'];

			// $Data[0]['CurrencySymbol']=($obj->MySQLSelect("SELECT vSymbol FROM currency WHERE eDefault='Yes'")[0]['vSymbol']);
			$Data[0]['CurrencySymbol']=get_value('currency', 'vSymbol', 'eDefault', 'Yes','','true');
			$Data[0]['ProjectName'] = $generalobj->getConfigurations("configurations","SITE_NAME");
			$Data[0]['ProjectName1'] ='<img class="logo" src="'.$tconfig["tsite_home_images"].'logo.png" alt="">';
			$Data[0]['car'] = $car;
			$Data[0]['basefare'] = $basefare;
			$Data[0]['distance'] = $distance;
			$Data[0]['time'] = $time;
			$Data[0]['total_amt'] = $total_amt;
			$Data[0]['fCommision'] = $fCommision;
			$Data[0]['payment_mode'] = $payment_mode;
			$Data[0]['ridenum'] = $ridenum;
			$sql = "SELECT * from configurations where vName = 'COPYRIGHT_TEXT'";
			$copy = $obj->MySQLSELECT($sql);
			//echo "copyright".$copy[0]['vValue'];
			$Data[0]['copyright'] = $copy[0]['vValue'];
			$start_time = $generalobj->DateTime($db_trip[0]['tStartDate'],12);
			$endtime = $generalobj->DateTime($db_trip[0]['tEndDate'],12);
		    $kms = $db_trip[0]['fDistance'];
			$Data[0]['start_time'] = $start_time;
			$Data[0]['endtime'] = $endtime;
			$Data[0]['kms'] = $kms;
      
      $disp_km_txt ='';
		if($APP_TYPE != 'UberX'){

			$disp_km_txt ='<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
				<span style="font-size:9px;text-transform:uppercase">'.$vLabel_admin_mail['LBL_KILOMETERS_TXT_ADMIN'].'</span><br><span style="font-size:13px;color:#111125;font-weight:normal">'.$Data[0]['kms'].'</span>
			</td>';
		}
		$email_con_location ='';
		if($APP_TYPE != 'UberX'){
			$email_con_location ='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
			<td rowspan="2" style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:17px!important;padding:3px 10px 10px 17px" align="left" valign="top">
				<img src="'.$tconfig["tsite_url"].'webimages/icons/help/route_line.png" style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left" height="80" width="13" class="CToWUd">
			</td>
		
			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:279px;line-height:16px;height:57px;padding:0 10px 10px 0" align="left" valign="top">
				<span style="font-size:15px;font-weight:500;color:#000000!important">
					<span class="aBn" data-term="goog_43159640" tabindex="0">
						<span class="aQJ">'.$Data[0]['start_time'].'</span>
					</span>
				</span>
				<br>
				<span style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">'.$Data[0]['slocation'].'</span>
			</td>
		</tr>
		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">

			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:279px;line-height:16px;height:auto;padding:0 0px 0 0" align="left" valign="top">
				<span style="font-size:15px;font-weight:500;color:#000000!important">
					<span class="aBn" data-term="goog_43159641" tabindex="0">
						<span class="aQJ">'.$Data[0]['endtime'].'</span>
					</span>
				</span><br>
				<span style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">'.$Data[0]['elocation'].'</span>
			</td>
		</tr>';

		}else{

			$email_con_location ='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left"><td rowspan="2" style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:17px!important;padding:3px 10px 10px 17px" align="left" valign="top">
				<img src="'.$tconfig["tsite_url"].'webimages/icons/help/green-lolo.png" style="outline:none;text-decoration:none;float:left;clear:both;display:block" align="left"  width="13" class="CToWUd">
			</td>			
		</tr>
		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">

			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:279px;line-height:16px;height:auto;padding:0 0px 0 0" align="left" valign="top">
				<span style="font-size:15px;font-weight:500;color:#000000!important">
					<span class="aBn" data-term="goog_43159641" tabindex="0">
						<span class="aQJ">'.$Data[0]['endtime'].'</span>
					</span>
				</span><br>
				<span style="font-size:11px;color:#999999!important;line-height:16px;text-decoration:none">'.$Data[0]['elocation'].'</span>
			</td>
		</tr>';
		}
			
			$tripDeleteByDriverStatus='';
			if($db_trip[0]['eCancelled']=="Yes"){
				$tripDeleteByDriverStatus = '<table style="border-spacing:0;border-collapse:collapse;vertical-align:middle;text-align:'.$txt_left.';width:auto;padding:0">
						<tbody>
							<tr style="vertical-align:middle;text-align:'.$txt_left.';width:100%;padding:0" align="left">
								
								<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:100%;padding:5px 5px 5px" align="left" valign="middle">
									<span style="padding-bottom:5px;display:inline-block">'.$vLabel_admin_mail['LBL_TRIP_CANCEL_DRIVER_REASON'].' '.$db_trip[0]['vCancelReason'].'</span>
								</td>
							</tr>
						</tbody>
					</table>';
			}
			
			$discount_area ="";
			if($db_trip[0]['fDiscount']!="" && $db_trip[0]['fDiscount'] != 0){
				$discount_fare=$generalobj->trip_currency($db_trip[0]['fDiscount']);
				$discount_area='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
				<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">Promo Code Discount</td>
				<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top"> - '.$discount_fare.'</td>
				</tr>';
			}
      
      $minimum_fare ="";
  		 if($db_trip[0]['fMinFareDiff']!="" && $db_trip[0]['fMinFareDiff'] > 0){
  			$minimum_fare_diff=$generalobj->trip_currency($db_trip[0]['fMinFareDiff']);
			$minimum_farePrice=$db_trip[0]['iBaseFare']+$db_trip[0]['fPricePerKM']+$db_trip[0]['fPricePerMin']+$db_trip[0]['fMinFareDiff'];
  			$minimum_fare='<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0;border-top:1px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;" align="left">
  			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top"> '.$generalobj->trip_currency($minimum_farePrice).' Minimum Fare</td>
  			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$minimum_fare_diff.'</td>
  			</tr>';
  		}

			# User Email below code
			$mailcont_member =
	'<div style="width:730px;!important;color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,
Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:19px;font-size:14px;margin:0;padding:0">
<table  style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;line-height:19px;font-size:14px;margin:0;padding:0"><tbody><tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left"><td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;padding:0" align="center" valign="top">
	<center style="width:100%;min-width:580px">
		<table style="border-color:#e3e3e3;border-style:solid;border-width:1px 1px 1px 1px;vertical-align:top;text-align:inherit;width:660px;margin:0 auto;padding:0"><tbody><tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left"><td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';padding:0" align="left" valign="top">
			<table style="vertical-align:top;text-align:'.$txt_left.';width:640px;margin:0 10px;padding:0">
				<tbody >
					<!--<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
						<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;padding:28px 0" align="left" valign="top" width="127">
							
							<span style="color:white;">'.$Data[0]['ProjectName1'].'</span>
						</td>
						<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;font-size:11px;color:#999999;line-height:15px;text-transform:uppercase;padding:30px 0 26px" align='.$txt_right.' valign="top">
							<span>'.$vLabel_admin_mail['LBL_RIDE_NUMBER_TXT_ADMIN'].':'.$Data[0]['ridenum'].'</span>
						</td>   
					</tr>   -->
				</tbody>
			</table>
				<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:640px;max-width:640px;border-radius:2px;background-color:#ffffff;margin:0 10px;padding:0" bgcolor="#ffffff">
					<tbody>
						<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
							<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:100%;padding:0" align="left" valign="top">
								<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#e3e3e3;border-bottom-style:solid;padding:0">
									<tbody>
										<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;background-color:rgb(250,250,250);padding:0" align="left" bgcolor="rgb(250,250,250)">
											<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:299px;border-radius:3px 0 0 0;background-color:#fafafa;padding:26px 10px 20px" align="left" bgcolor="#FAFAFA" valign="top">
												<span style="font-weight:bold;font-size:32px;color:#000;line-height:30px;padding-left:15px">
													'.$vLabel_admin_mail['LBL_PAYMENT_RECEIPT_TXT'].'
												</span>
											</td>
											<td style="word-break:break-word;vertical-align:top;text-align:'.$txt_right.';display:table-cell;font-size:11px;color:#999999;line-height:15px;text-transform:uppercase;padding:10px 0 26px" align='.$txt_right.' valign="top">
							<span>Ride Number:'.$Data[0]['ridenum'].'</span>
						</td> 
										</tr>
									</tbody>
								</table>
								<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;padding:0">
									<tbody>
										<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
											<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:300px;padding:25px 10px 25px 5px" align="left" valign="top">
												<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';margin-left:19px;padding:0">
													<tbody>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:300px;padding:0" align="left" valign="top">
																<!-- <img src="?ui=2&amp;ik=664899e0a6&amp;view=fimg&amp;th=14ff8dfb563a7ad2&amp;attid=0.1&amp;disp=emb&amp;realattid=ec4b5213f81c2da8_0.2.1&amp;attbid=ANGjdJ9KWDUTnGjon4TMCJyHlBLeY_vRzg1MhZ-u1502HfwLRWMrSuAhmayg-Qx2uhXI8uxweF0QWFvM1xK7HA12g_oLyrfBEcCcg1PScmONkBhQbiK3m8VI07TVhwg&amp;sz=w558-h434&amp;ats=1443003800594&amp;rm=14ff8dfb563a7ad2&amp;zw&amp;atsh=1" style="outline:none;text-decoration:none;float:none;clear:none;display:block;width:279px;min-height:217px;border-radius:3px 3px 0 0;border:1px solid #d7d7d7" align="none" height="217" width="279" class="CToWUd a6T" tabindex="0">-->
																<div class="a6S" dir="ltr" style="opacity: 0.01; left: 432.922px; top: 670px;">
																	<div id=":n0" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Download attachment map_32aba2dc-7679-4c0e-bea3-7f4d8e8f934a" data-tooltip-class="a1V" data-tooltip="Download">
																		<div class="aSK J-J5-Ji aYr"></div>
																	</div>
																	<div id=":n1" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Save attachment to Drive map_32aba2dc-7679-4c0e-bea3-7f4d8e8f934a" data-tooltip-class="a1V" data-tooltip="Save to Drive">
																		<div class="wtScjd J-J5-Ji aYr aQu">
																			<div class="T-aT4" style="display: none;">
																				<div></div>
																				<div class="T-aT4-JX"></div>
																			</div>
																		</div>
																	</div>
																</div>
															</td>
														</tr>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:279px;display:block;background-color:#fafafa;padding:20px 0;border-color:#e3e3e3;border-style:solid;border-width:1px 1px 0px" align="left" bgcolor="#FAFAFA">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:279px;padding:0" align="left" valign="top">
																<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:auto;padding:0">
																	<tbody>
																	'.$email_con_location.'
																	</tbody>
																</table>
															</td>
														</tr>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:279px;display:block;background-color:#fafafa;padding:0;border:1px solid #e3e3e3" align="left" bgcolor="#FAFAFA">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell!important;width:279px!important;padding:0" align="left" valign="top">
																<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;color:#959595;line-height:14px;padding:0">
																	<tbody>
																		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
																				<span style="font-size:9px;text-transform:uppercase">'.$vLabel_admin_mail['LBL_CAR_ADMIN'].'</span><br>
																				<span style="font-size:13px;color:#111125;font-weight:normal">'.$Data[0]['car'].'</span>
																			</td>
																			'.$disp_km_txt.'
																			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:center;display:table-cell!important;width:33%!important;line-height:16px;padding:6px 10px 10px" align="center" valign="top">
																				<span style="font-size:9px;text-transform:uppercase">'.$vLabel_admin_mail['LBL_TRIP_TIME_TXT_ADMIN'].'</span><br><span style="font-size:13px;color:#111125;font-weight:normal"><span class="aBn" data-term="goog_43159642" tabindex="0"><span class="aQJ">'.$Data[0]['time_taken'].'</span></span></span>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
											<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:300px;padding:10px" align="left" valign="top">
												<span style="display:block;padding:0px 8px 0 10px">
													<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%!important;padding:0">
														<tbody>
															<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
																	<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
																	</p>
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:center;display:table-cell;width:120px!important;font-size:11px;white-space:pre-wrap;padding:12px 10px 5px" align="center" valign="middle">'.$vLabel_admin_mail['LBL_FARE_BREAKDOWN'].'</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:auto!important;padding:12px 0 5px" align="left" valign="middle">
																	<p style="color:#222222;font-family:HelveticaNeue-Light,Helvetica Neue Light,Helvetica Neue,Helvetica,Arial,Lucida Grande,sans-serif;font-weight:normal;text-align:'.$txt_left.';line-height:0;font-size:14px;border-bottom-style:solid;border-bottom-width:1px;border-bottom-color:#e3e3e3;display:block;margin:0;padding:0" align="left">
																	</p>
																</td>
															</tr>
														</tbody>
													</table>
													<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';margin-top:15px;width:auto;padding:0">
														<tbody>';
														if($eFareType != 'Fixed')
														{
															$mailcont_member .='
															<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																	'.$vLabel_admin_mail['LBL_BASE_FARE_SMALL_TXT'].'
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['basefare'].'</td>
															</tr>
															<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																	'.$vLabel_admin_mail['LBL_DISTANCE_TXT'].'
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['distance'].'</td>
															</tr>

															<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																	'.$vLabel_admin_mail['LBL_TIME_TXT'].'
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$Data[0]['time'].'</td>
															</tr>';
														}
														else
														{
															$mailcont_member .= '<tr style="vertical-align:top;text-align:'.$txt_left.';border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px 4px 5px" align="left" valign="top">
																'.$vLabel_admin_mail['LBL_Total_Fare'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px 4px 5px" align='.$txt_right.' valign="top">'.$Data[0]['total_amt'].'</td>
														</tr>';
														}
														
														if($db_trip[0]['fWalletDebit'] > 0)
														{
															$mailcont_member .= '<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																	'.$vLabel_admin_mail['LBL_WALLET_DEBIT_MONEY'].'
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">- '.$generalobj->trip_currency($db_trip[0]['fWalletDebit']).'</td>
															</tr>';
														}
														if($db_trip[0]['fSurgePriceDiff'] > 0)
														{
														$mailcont_member .= '<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px" align="left" valign="top">
																'.$vLabel_admin_mail['LBL_SURGE_MONEY'].'
															</td>
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px" align='.$txt_right.' valign="top">'.$generalobj->trip_currency($db_trip[0]['fSurgePriceDiff']).'</td>
														</tr>';
														}

															$mailcont_member .= '
															

															<tr style="vertical-align:top;text-align:'.$txt_left.';border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;padding:4px 4px 5px" align="left" valign="top">
																	'.$vLabel_admin_mail['LBL_PLATFORM_FREES_TXT'].'
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:4px 4px 5px" align='.$txt_right.' valign="top">- '.$Data[0]['fCommision'].'</td>
															</tr>

															'.$discount_area.'
                              '.$minimum_fare.'
															<tr style="vertical-align:top;text-align:'.$txt_left.';font-weight:bold;width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#111125;padding:5px 4px 4px" align="left" valign="top">'.$vLabel_admin_mail['LBL_SUBTOTAL_TXT'].'</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;padding:5px 4px 4px" align='.$txt_right.' valign="top">'.$Data[0]['total_amt'].'</td>
															</tr>
															<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:300px;color:#808080;line-height:18px;padding:5px 4px" align="left" valign="top">
																	<span style="font-size:9px;line-height:7px">'.$vLabel_admin_mail['LBL_CHARGED_TXT'].'</span>
																	<br>

																	<img src="'.paymentimg($Data[0]['payment_mode']).'" style="outline:none;text-decoration:none;float:left;clear:both;display:block;width:40px!important;min-height:25px;margin-right:5px;margin-top:3px" align="left" height="12" width="17" class="CToWUd" >
																	<span style="font-size:13px">
																		'.$Data[0]['payment_mode'].'
																	</span>
																</td>
																<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_right.';display:table-cell;width:90px;white-space:nowrap;font-size:19px;font-weight:bold;line-height:30px;padding:20px 4px 5px" align='.$txt_right.' valign="top">
																	'.$Data[0]['total_amt'].'
																</td>
															</tr>
														</tbody>
													</table>
													'.$mailcont_member_trips.'
                          '.$mailcont_member_trips_img.'
												</span>
											</td>
										</tr>
									</tbody>
								</table>
								
								'.$tripDeleteByDriverStatus.'
								
								<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;border-bottom-width:1px;border-bottom-color:#f0f0f0;border-bottom-style:solid;padding:0">
									<tbody>
									<tr >
									<td valign="top" align="left" style="border-collapse: collapse ! important; vertical-align: top; text-align: left; display: inline-block; padding: 10px 0px 0px 10px;margin-top:10px; width: 48%;">'.$vLabel_admin_mail['LBL_DRIVER_TXT_ADMIN'].'</td>
									<td valign="top" align="left" style="border-collapse: collapse ! important; vertical-align: top; text-align: left; display: inline-block; width: 48%; padding-left: 10px;margin-top:10px;padding: 10px 0px 0px 10px;">'.$vLabel_admin_mail['LBL_PASSANGER_TXT_ADMIN'].'</td>
									</tr>
										<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
											<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:48%;padding:0px" align="left" valign="top">
												<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;display:inline-block;padding:0">
													<tbody>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:100%!important;line-height:15px;padding:5px 0px 0px 10px" align="left" valign="top">
																<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:auto;padding:0">
																	<tbody>
																		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:45px;padding:5px 0px 5px" align="left" valign="top">
																				<img src="'.$img.'" style="outline:none;text-decoration:none;float:left;clear:both;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;margin-left:15px;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7" align="left" height="45" width="45" class="CToWUd">
																			</td>
																			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:300px;padding:5px 5px 5px" align="left" valign="middle">
																				<span style="padding-bottom:5px;display:inline-block">'.$Data[0]['driver'].'</span><br/>
																				<span style="padding-bottom:5px;display:inline-block">'.$Data[0]['email'].'</span>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
											<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:48%;padding:0px" align="left" valign="top">
												<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:100%;max-width:640px;display:inline-block;padding:0">
													<tbody>
														<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
															<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:inline-block;width:100%!important;line-height:15px;padding:5px 0px 0px 10px" align="left" valign="top">
																<table style="border-spacing:0;border-collapse:collapse;vertical-align:top;text-align:'.$txt_left.';width:auto;padding:0">
																	<tbody>
																		<tr style="vertical-align:top;text-align:'.$txt_left.';width:100%;padding:0" align="left">
																			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:top;text-align:'.$txt_left.';display:table-cell;width:45px;padding:5px 0px 5px" align="left" valign="top">
																				<img src="'.$img1.'" style="outline:none;text-decoration:none;float:left;clear:both;display:inline-block;width:45px!important;min-height:45px!important;border-radius:50em;margin-left:15px;max-width:45px!important;min-width:45px!important;border:1px solid #d7d7d7" align="left" height="45" width="45" class="CToWUd">
																			</td>
																			<td style="word-break:break-word;border-collapse:collapse!important;vertical-align:middle;text-align:'.$txt_left.';display:table-cell;width:300px;padding:5px 5px 5px" align="left" valign="middle">
																				<span style="padding-bottom:5px;display:inline-block">'.$Data[0]['user'].'</span><br/>
																				<span style="padding-bottom:5px;display:inline-block">'.$Data[0]['uEmail'].'</span>
																			</td>
																		</tr>
																	</tbody>
																</table>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
			</tr>
			</tbody>
			</table>
			
		</table>
		</div>';
			//echo $mailcont_member;exit;
				 $sql="select vValue from configurations where vName='ADMIN_EMAIL'";
				$db_mail=$obj->MySQLSELECT($sql);
				 $maildata_member['details'] = $mailcont_member;
				$maildata_member['email'] = $db_mail[0]['vValue'];
        	#echo $maildata_member; exit;
		  return $generalobj->send_email_user("RIDER_INVOICE",$maildata_member,"","EN");
		}
	
	####################### for email receipt to admin end ##########################
		
?>		

<?php
	include_once('common.php');
	require_once(TPATH_CLASS .'savar/jalali_date.php');
	#echo"<pre>";print_r($_SESSION);exit;
	$script="Profile";
	$user = isset($_SESSION["sess_user"]) ? $_SESSION["sess_user"] : '';
	$success = isset($_REQUEST["success"]) ? $_REQUEST["success"] : '';
	$var_msg = isset($_REQUEST["var_msg"]) ? $_REQUEST["var_msg"] : '';
	$er = isset($_REQUEST["er"]) ? $_REQUEST["er"] : '';
	$new='';
	
	if(isset($_SESSION['sess_new'])){
		$new=$_SESSION['sess_new'];
		unset($_SESSION['sess_new']);
	} 
	$generalobj->check_member_login();
	// Start :: Get country name
	
$isManagerLogin = $_SESSION['sess_manager_login'];

$iCompanyId = $_SESSION['sess_iUserId'];
$action=(isset($_REQUEST['action'])?$_REQUEST['action']:'');

if($action != "" && $action == 'masterlogin') {
    $managerPassword = $_REQUEST['managerPassword'];
    $vManagerPassword = $generalobj->encrypt($managerPassword);
    
    
    $sql = "SELECT iCompanyId FROM company WHERE vManagerPassword = '{$vManagerPassword}' AND iCompanyId = '{$iCompanyId}' ";
	$db_comp = $obj->MySQLSelect($sql);
    
    #print_r($db_comp);
    #die($sql);
    
    if(is_array($db_comp) == true && count($db_comp) > 0)
    {
        $_SESSION['sess_manager_login'] = true;
        $isManagerLogin = $_SESSION['sess_manager_login'];
    }
    else
    {
        $isManagerLogin = false;
        $_SESSION['sess_manager_login'] = false;
        $isPasswordInvalid = true;
    }
    
}

?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width,initial-scale=1">
		<title><?php echo $SITE_NAME?> | <?php echo $langage_lbl['LBL_PROFILE_HEADER_PROFILE_TXT'];?></title>
		<!-- Default Top Script and css -->
		<?php include_once("top/top_script.php");?>
		<link rel="stylesheet" href="assets/css/bootstrap-fileupload.min.css" >
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
                    <h2 class="header-page"><?php echo $langage_lbl['LBL_REPORTS'];?></h2>
                    <?php if($isManagerLogin == false) : ?>
                    <div class="trips-page">
                        <form name="masterloginform" action="" method="post" class="form-signin login-form-left">
                            <input type="hidden" name="action" id="action" value="masterlogin" />
                            <?php if($isPasswordInvalid) echo 'Password is invalid' ; ?>
                            <b>
                                <label><?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']?></label>
                                <input name="managerPassword" placeholder="<?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']?>" class="login-input" id="managerPassword" value="" required="" type="password">
                            </b>
                            <b>
								<input type="submit" class="submit-but" value="<?php echo $langage_lbl['LBL_SIGN_IN_TXT'];?>" />
							</b>
                        </form>
                    </div>
                    <?php else : ?>
						<!-- profile page -->
						<div class="driver-profile-page">
							
							
							<div <?php if($_SESSION['sess_user'] == 'driver'){?> class="driver-profile-mid-part"  <?php }else{?> class='driver-profile-mid-part company-profile-part' <?php } ?>>
								<ul >
									<li <?php if($_SESSION['sess_user'] == 'driver'){?> class='driver-profile-mid-part-details' <?php }else{?> class='company-profile-mid-part-details' <?php } ?> >
									
										<div class="flipbox">
										<div class="driver-profile-mid-inner front">
											<div class="profile-icon"><i class="fa fa-money" aria-hidden="true"></i></div>
											<h3><?php echo $langage_lbl['LBL_Transaction_HISTORY']; ?></h3>
											<p></p>
											<span><a id="show-edit-address-div" class="hide-address-div hidev" href="company_wallet_report.php" target="_blank"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_DATATABLE_SHOW']; ?></a></span> 
										</div> 
										<div class="driver-profile-mid-inner back">
											<div class="profile-icon"><i class="fa fa-money" aria-hidden="true"></i></div>
											<h3><?php echo $langage_lbl['LBL_Transaction_HISTORY']; ?></h3>
											<p></p>
											<span><a id="show-edit-address-div-back" class="hide-address-div hidev" href="company_wallet_report.php" target="_blank"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_DATATABLE_SHOW']; ?></a></span> 
										</div> 
										</div> 
										
									</li>
									<li <?php if($_SESSION['sess_user'] == 'driver'){?> class='driver-profile-mid-part-details' <?php }else{?>class='company-profile-mid-part-details' <?php } ?>>
										<div class="flipbox">
										<div class="driver-profile-mid-inner front">
											<div class="profile-icon"><i class="fa fa-suitcase" aria-hidden="true"></i></div>
											<h3><?php echo $langage_lbl['LBL_COMPANY_TRIP_HEADER_TRIPS_TXT'];?></h3>
											<p></p>
											<span><a id="show-edit-password-div" class="hide-password-div hidev" href="payment_report.php" target="_blank"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_DATATABLE_SHOW'];?></a></span> 
										</div>
										<div class="driver-profile-mid-inner back">
											<div class="profile-icon"><i class="fa fa-suitcase" aria-hidden="true"></i></div>
											<h3><?php echo $langage_lbl['LBL_COMPANY_TRIP_HEADER_TRIPS_TXT'];?></h3>
											<p></p>
											<span><a id="show-edit-password-div-back" class="hide-password-div hidev" href="payment_report.php" target="_blank"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_DATATABLE_SHOW'];?></a></span> 
										</div>
										</div>
									</li>
								<!--	<li <?php /*if($_SESSION['sess_user'] == 'driver'){?> class='driver-profile-mid-part-details' <?php }else{?>class='company-profile-mid-part-details'<?php } ?>>
										<div class="driver-profile-mid-inner">
											<div class="profile-icon"><i class="fa fa-language" aria-hidden="true"></i></div>
											<h3><?php echo $langage_lbl['LBL_PROFILE_LANGUAGE_TXT'];?></h3>
											<p><?php echo  $lang_user ?></p>
											<?php if(count($db_lang)>1){?>
											<span><a id="show-edit-language-div" class="hide-language-div hidev"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_PROFILE_EDIT'];?></a></span> 
											<?php } */?>
										</div>
									</li> -->
									<?php
										
										if($_SESSION['sess_user'] == 'driver'){ ?> 
										<li <?php if($_SESSION['sess_user'] == 'driver'){?> class='driver-profile-mid-part-details' <?php }else{?>class='company-profile-mid-part-details' <?php } ?>>
											<div class="flipbox">
											<div class="driver-profile-mid-inner front">
												<div class="profile-icon"><i class="fa fa-money" aria-hidden="true"></i></div>
												<h3><?php echo $langage_lbl['LBL_BANK_DETAILS_TXT'];?></h3>
												<p><?php echo  $db_user[0]['vBankName'] ?></p>
												<?php if(count($db_lang)>=1){?>
												<span><a id="show-edit-bankdetail-div" class="hide-bankdetail-div hidev"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_PROFILE_EDIT'];?></a></span> 
												<?php } ?>
											</div>
											<div class="driver-profile-mid-inner back">
												<div class="profile-icon"><i class="fa fa-money" aria-hidden="true"></i></div>
												<h3><?php echo $langage_lbl['LBL_BANK_DETAILS_TXT'];?></h3>
												<p><?php echo  $db_user[0]['vBankName'] ?></p>
												<?php if(count($db_lang)>=1){?>
												<span><a id="show-edit-bankdetail-div-back" class="hide-bankdetail-div hidev"><i class="fa fa-link" aria-hidden="true"></i><?php echo $langage_lbl['LBL_PROFILE_EDIT'];?></a></span> 
												<?php } ?>
											</div>
											</div>
										</li>
									<?php } ?>
								</ul>
							</div>                           
							
							
							<!-- Password form -->                    
				
						</div>
						<?php endif; ?>
						<div style="clear:both;"></div>
					</div>
				</div>
				
				<!-- footer part -->
				<?php include_once('footer/footer_home.php');?>
				<!-- footer part end -->
				<!-- -->
				<div style="clear:both;"></div>
			</div>
			<!-- home page end-->
			<!-- Footer Script -->
			<?php include_once('top/footer_script.php');?>
			<link rel="stylesheet" href="assets/plugins/datepicker/css/datepicker.css" />
			<link rel="stylesheet" href="assets/validation/validatrix.css" />
			<script src="assets/js/jquery-ui.min.js"></script><script src="assets/plugins/datepicker/js/bootstrap-datepicker.js"></script>
			<script src="assets/plugins/jasny/js/bootstrap-fileupload.js"></script>
			<script src="assets/js/jquery.ui.datepicker-cc-fa.js"></script>
			
			<!-- End: Footer Script -->
			<script type="text/javascript">

			
			</script>
		</body>
	</html>

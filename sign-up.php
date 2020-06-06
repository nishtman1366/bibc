<?php
    include_once("common.php");


	$generalobj->go_to_home();
    $script="Driver Sign-Up";
    $sql="select * from  currency where eStatus='Active' and eDefault = 'Yes'";
    $db_currency=$obj->MySQLSelect($sql);
	//echo "<pre>";print_r($db_currency);exit;
    $sql = "SELECT * from country where eStatus = 'Active'" ;
    $db_code = $obj->MySQLSelect($sql);
	$meta_arr = $generalobj->getsettingSeo(5);
    $Mobile=$MOBILE_VERIFICATION_ENABLE;


	if(count($_POST) > 3)
	{
		$vFirstName = isset($_POST['vFirstName']) ? $_POST['vFirstName'] : '';
		$vLastName 	= isset($_POST['vLastName']) ? $_POST['vLastName'] : '';
		$vEmail 	= isset($_POST['vEmail']) ? $_POST['vEmail'] : '';
		$vPhone 	= isset($_POST['vPhone']) ? $_POST['vPhone'] : '';
		$vPassword 	= isset($_POST['vPassword']) ? $_POST['vPassword'] : '';
		$vRefCode 	= isset($_POST['vRefCode']) ? $_POST['vRefCode'] : '';
	}
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
   <!-- <title><?php echo $COMPANY_NAME?>| Signup</title>-->
	<title><?php echo $meta_arr['meta_title'];?></title>
	<meta name="keywords" value="<?php echo $meta_arr['meta_keyword'];?>"/>
	<meta name="description" value="<?php echo $meta_arr['meta_desc'];?>"/>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <link href="assets/css/checkbox.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/radio.css" rel="stylesheet" type="text/css" />
    <?php include_once("top/validation.php");?>
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
            <h2 class="header-page trip-detail"><?php echo $langage_lbl['LBL_SIGNUP_SIGNUP']; ?>
                <p><?php echo $langage_lbl['LBL_SIGN_UP_TELL_US_A_BIT_ABOUT_YOURSELF']; ?></p>
            </h2>
            <!-- trips detail page -->
            <form name="frmsignup" id="frmsignup" method="post" action="javascript:void(0);">
                <div class="driver-signup-page">
					<input type="hidden" name="vCurrencyDriver" value="<?php echo $db_currency[0]['vName']?>">
					<input type="hidden" name="vCountry" value="<?php echo $DEFAULT_COUNTRY_CODE_WEB?>">
                <?php
                if ($_REQUEST['error']) {
                ?>
                    <div class="row">
                        <div class="col-sm-12 alert alert-danger">
                             <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php echo $_REQUEST['var_msg']; ?>
                        </div>
                    </div>
                <?php
                    }
                ?>
                    <?php /*<h3><?php echo $langage_lbl['LBL_Contact_Info']; ?></h3> */ ?>
                    <p><?php echo $langage_lbl['LBL_IF_YOU_ARE_AN_INDIVIDUAL']; ?></p>
                    <p><?php echo $langage_lbl['LBL_IF_YOU_ARE_A_COMPANY']; ?></p>
                    <div class="individual-driver">
                        <h4><?php echo $langage_lbl['LBL_ARE_YOU_AN_INDIVIDUAL']; ?></h4>
                        <span>
                            <em><?php echo $langage_lbl['LBL_Member_Type:']; ?> </em>
                            <div class="radio-but">
                            <b>
                                <input id="r1" name="user_type" type="radio" value="driver"  onchange="show_company(this.value);" checked="checked">
                                <label for="r1"><?php echo $langage_lbl['LBL_Individual_Driver']; ?></label>
                            </b>
                            <b>
                                <input id="r2" name="user_type" type="radio" value="company" onChange="show_company(this.value);" class="required">
                                <label for="r2"><?php echo $langage_lbl['LBL_Company']; ?></label>
                            </b>
                            </div>
                        </span>

                    </div>
                    <div class="create-account">
                        <h3><?php echo $langage_lbl['LBL_SIGN_UP_CREATE_ACCOUNT']; ?></h3>
                        <span>
                            <strong id="emailCheck"><label><?php echo $langage_lbl['LBL_EMAIL_ID_TXT']; ?></label>
                            <input type="text" placeholder="<?php echo $langage_lbl['LBL_EMAIL_name@email.com']; ?>" name="vEmail" class="create-account-input" id="vEmail_verify"  value="<?php echo  $vEmail ?>" /></strong>
                            <strong><label><?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']; ?></label>
                            <input id="pass" type="password" name="vPassword" placeholder="<?php echo $langage_lbl['LLB_PASSWORD_Password: At least 5 characters']; ?>" class="create-account-input create-account-input1 required"  value="<?php echo  $vPassword ?>" /></strong>
                        </span>
                        <span>
                            <?php /*<input type="hidden" name="mobile_verification"  id="mobile_verification" value="<?php echo $Mobile;?>"> */ ?>
                            <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"  class="create-account-input" value="" />
                            <input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input" value=""  />
                        </span>
						<!--
                        <span class="c_country">
                            <strong>
                                <select name="vCountry" class="custom-select-new" onChange="changeCode(this.value); ">

                                    <?php /*for($i=0;$i<count($db_code);$i++) { ?>
                                    <option value="<?php echo $db_code[$i]['vCountryCode']?>" <?php if($db_code[$i]['vCountryCode']== $DEFAULT_COUNTRY_CODE_WEB){echo 'selected';}?>><?php echo $db_code[$i]['vCountry']?></option>
                                    <?php } */?>
                                </select>
                            </strong>
                        </span> -->

                         <span class="c_code_ph_no newrow new-sign-up">
                            <strong class="c_code"><input type="text"  name="vCode" readonly  class="create-account-input required " id="code" /></strong>
                            <strong class="ph_no" id="mobileCheck"><input type="text"  id="vPhone" placeholder="<?php echo $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>" class="create-account-input create-account-input1 required vPhone_verify" name="vPhone"  value="<?php echo  $vPhone ?>" onBlur="return validate_mobile_driver(this.value)"/></strong>
                        </span>
                         <?php

                        if($REFERRAL_SCHEME_ENABLE == 'Yes'){ ?>
                               <span class="new-sign-up-right"><strong id="refercodeCheck"><input id="vRefCode" type="text" name="vRefCode" placeholder="<?php echo $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?>" class="create-account-input create-account-input1 vRefCode_verify"  value="<?php echo  $vRefCode ?>" onBlur=" validate_refercode(this.value)"/></strong></span>

                        <?php }
                        ?>


                    </div>
                    <div class="create-account">
                        <h3 class="company" style="display: none;"><?php echo $langage_lbl['LBL_Company_Information']; ?></h3>
                        <h3 class="driver"><?php echo $langage_lbl['LBL_Driver_Information']; ?></h3>
                        <span class="driver">
                            <strong><label><?php echo $langage_lbl['LBL_FIRST_NAME_TXT']; ?></label>
                            <input name="vFirstName" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?>" id="vFirstName" value="<?php echo  $vFirstName ?>" /></strong>
                            <strong><label><?php echo $langage_lbl['LBL_LAST_NAME_TXT']; ?></label>
                            <input name="vLastName" type="text" class="create-account-input create-account-input1 required" placeholder="<?php echo $langage_lbl['LBL_SIGN_UP_LAST_NAME_HEADER_TXT']; ?>" id="vLastName"  value="<?php echo  $vLastName ?>"/></strong>
                        </span>
                        <span class="company" style="display: none;">
                            <strong><label><?php echo $langage_lbl['LBL_COMPANY1_NAME_TXT']; ?></label>
                            <input type="text" id="company_name" placeholder="<?php echo $langage_lbl['LBL_Company_name']; ?>" class="create-account-input required" name="vCompany"/></strong>
                        </span>
                        <span>
                            <strong><label><?php echo $langage_lbl['LBL_ADDRESS_NAME_TXT']; ?></label>
                            <input name="vCaddress" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_ADDRESS']; ?>"/></strong>
                            <strong><label><?php echo $langage_lbl['LBL_ADDRESS2_TXT']; ?></label>
                            <input name="vCadress2" type="text" class="create-account-input create-account-input1" placeholder="<?php echo $langage_lbl['LBL_ADDRESS']; ?> &#1583;&#1608;&#1605; (&#1575;&#1582;&#1578;&#1740;&#1575;&#1585;&#1740;)"/>
                            </strong>
                        </span>
                        <span>
                            <strong><label><?php echo $langage_lbl['LBL_CITY_TXT']; ?></label>
                            <input name="vCity" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_City']; ?>"/></strong>
                            <strong><label><?php echo $langage_lbl['LBL_ZIP_CODE_TXT']; ?></label>
                            <input name="vZip" type="text" class="create-account-input create-account-input1 required" placeholder="<?php echo $langage_lbl['LBL_ZIP_CODE']; ?>"/></strong>
                        </span>
                        <span>
                           <!-- <strong>
                            <label><?/*=$langage_lbl['LBL_SELECT_CURRANCY_TXT']; ?></label>
                                <select class="custom-select-new" name = 'vCurrencyDriver'>
                                    <?php for($i=0;$i<count($db_currency);$i++){ ?>
                                    <option value = "<?php echo  $db_currency[$i]['vName'] ?>" <?php if($db_currency[$i]['eDefault']=="Yes"){?>selected<?} ?>>
                                    <?php echo  $db_currency[$i]['vName'] ?>
                                    </option>
                                    <?php } */ ?>
                                </select>
                            </strong> -->
                            <b id="li_dob">
                                <strong>
								<?php echo $langage_lbl['LBL_Date_of_Birth']; ?></strong>
                                <select name="vDay" data="DD" class="custom-select-new">
                                    <option value=""><?php echo $langage_lbl['LBL_DATE_TXT']; ?></option>
                                    <?php for($i=1;$i<=31;$i++) {?>
                                    <option value="<?php echo $i?>">
                                    <?php echo $i?>
                                    </option>
                                    <?php }?>
                                </select>
                                <select data="MM" name="vMonth" class="custom-select-new">
                                    <option value=""><?php echo $langage_lbl['LBL_MONTH_TXT']; ?></option>
                                    <?php for($i=1;$i<=12;$i++) {?>
                                    <option value="<?php echo $i?>">
                                    <?php echo $i?>
                                    </option>
                                    <?php }?>
                                </select>
                                <select data="YYYY" name="vYear" class="custom-select-new">
                                    <option value=""><?php echo $langage_lbl['LBL_YEAR']; ?></option>
                                    <?php for($i=1300;$i<=1396;$i++) {?>
                                    <option value="<?php echo $i?>">
                                    <?php echo $i?>
                                    </option>
                                    <?php }?>
                                </select>
                            </b>
							<span style="display:none" id="birth_date"></span>
                        </span>
                        <span>
                            <abbr><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="http://par30taxi.ir/driver_terms/" target="_blank"><?php echo $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a>
                                <div class="checkbox-n">
                                    <input id="c1" name="remember-me" type="checkbox" class="required termscheckbox" value="remember">
                                    <label for="c1"></label>
                                </div>

                            </abbr>
                        </span>

                    <p><button type="submit" onClick="submit_form();" class="submit" name="SUBMIT"><?php echo $langage_lbl['LBL_BTN_SIGN_UP_SUBMIT_TXT']; ?></button></p>
                    </div>
                </div>
            </form>
            <div class="col-lg-12">
                <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title" id="H2"><?php echo $langage_lbl['LBL_PHONE_VERIFICATION']?></h4>
                            </div>
                            <div class="modal-body">
                                <form role="form" name="verification" id="verification">
                                    <p class="help-block"><?php echo $langage_lbl['LBL_DRIVR_VERI_TEXT']?></p>
                                    <div class="form-group">
                                        <label><?php echo $langage_lbl['LBL_ENTER_VERI_CODE']?></label>
                                        <input class="form-control" type="text" id="vCode1"/>
                                    </div>
                                    <p class="help-block" id="verification_error"></p>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onClick="check_verification('verify')">Verify</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- -->
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
    <script type="text/javascript">
    $('#verification').bind('keydown',function(e){
            if(e.which == 13){
                check_verification('verify'); return false;
            }
        });

		$("#vEmail_verify").bind("keypress click",function(){
			$(".required-label").remove();
		});

        function submit_form()
        {
			//validate_email($("#vEmail_verify"));
			//validatrix("PS");
			var lang_def ="<?php echo $_SESSION['sess_lang'];?>";
            if( validatrix(lang_def) ){

                var showbox='';
                var typecompany =   $('#r2').is(':checked');

                if(typecompany == 'true'){

                    showbox =1;
                }

                var typedriver =   $('#r1').is(':checked');

                if(typedriver == true){
                    showbox=2;
                }
               //alert(showbox); return false;
                if(showbox==2){

					var sr = 0;
						$("#frmsignup").find('select').each(function(){
							if($(this).attr('required')){
								if($(this).val() == ""){
									//alert($(this).attr('name'));
									sr = 1;
									document.getElementById("#birth_date").style.display='';
									return false;
								}
							}
						});

						if(sr == 0){
							var email = $("#vEmail_verify").val();
							validate_email(email);
						}
                }else{
					var email = $("#vEmail_verify").val();
					validate_email(email);
                    //document.frmsignup.submit();
                }

            }else{
                //console.log("Some fields are required");
                  $( ".required-active:first" ).focus();
                return false;
            }
            return false; //Prevent form submition
        }
        /*ajax for unique username*/
        function validate_email(id)
        {
            if(id == "")
            {
                document.frmsignup.submit();
                return true;
            }

			//if(id != "") {
            var request = $.ajax({
                 type: "POST",
                 url: 'ajax_validate_email.php',
                 data: 'id=' +id,
                 success: function (data)
                 {
                    if(data==0)
                    {
					 $('#vEmail_verify').addClass('required-active');
					 window.scrollTo(0,300);
					 $('#vEmail_verify').focus();
                     $('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_EMAIL_ALREADY_EXIST'];?></div>');
					 //$('#emailCheck').show();
                    // $('button[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
                    else if(data==1)
                    {
                        //var eml=/^[-.0-9a-zA-Z]+@[a-zA-z]+\.[a-zA-z]{2,3}$/;
                        //var eml=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
						var eml=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        result=eml.test(id);
                        if(result==true || /* Disable requered email by seyyed amir */ eml == "")
                        {
							$('#emailCheck').removeClass('required-active');
							$("#frmsignup").attr("action","signup_a.php");
							document.frmsignup.submit();
                        }
                        else
                        {
							$('#vEmail_verify').addClass('required-active');
							window.scrollTo(0,300);
							$('#vEmail_verify').focus();
							$('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ENTER_PROPER_EMAIL'];?></div>');

                              return false;
                        }
                    }
                    else if(data==2)
                    {
						$('#vEmail_verify').addClass('required-active');
						window.scrollTo(0,300);
						$('#vEmail_verify').focus();
						$('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_DELETED_ACCOUNT'];?></div>');
						// $('#emailCheck').html('Your Account is deleted please contact admin');
						//$('button[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
               }
            });
			/*}else {
				$('#vEmail_verify').addClass('required-active');
				window.scrollTo(0,300);
				$('#vEmail_verify').focus();
				$('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_REQUIRED_TEXT'];?></div>');
			}*/
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

		$(document).ready(function(){
			var code= '<?php echo $DEFAULT_COUNTRY_CODE_WEB?>';
			changeCode(code);
		});
		  /*ajax for unique username*/
        function validate_username(username)
        {
            var request = $.ajax({
                type: "POST",
                url: 'ajax_validate_username.php',
                data: 'id=' +username,
                success: function (data)
                {
                    if(data==0)
                    {
                      $('#username').html('<i class="icon icon-remove alert-danger alert"><?php echo $langage_lbl['LBL_USER_NAME_EXIST'];?></i>');
                      $('button[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
                    else if(data==1)
                    {
                        $('#username').html('<i class="icon icon-ok alert-success alert"> <?php echo $langage_lbl['LBL_VALID'];?></i>');
                        $('button[type="submit"]').removeAttr('disabled');
                    }
                }

            });
        }

        function validate_mobile(mobile)
        {
            //alert(mobile);
            var tel=/^[789]{1}[0-9]{9}$/;
            result=tel.test(mobile);
            //alert(result);
            if(result==true){
				$(".vPhone_verify").removeClass('required-active');
                $('button[type="submit"]').removeAttr('disabled');
            }
            else{
				$(".vPhone_verify").addClass('required-active');
                $('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ENTER_PROPER_PHONE'];?></div>');
                 $('button[type="submit"]').attr('disabled','disabled');
                  return false;
            }
        }

        $(document).ready(function(){

            $("#company").hide();
            $("#radio_1").prop("checked", true)
            $( "#company_name" ).removeClass( "required" );
             show_company('driver');

        });

        function show_company(user)
        {
            if(user=='company')
            {
                $(".company").show();
                $(".driver").hide();
                $("#li_dob").hide();
                $("#vRefCode").hide();
                $( "#vFirstName" ).removeClass( "required" );
                $( "#vLastName" ).removeClass( "required" );
                $("#div-phone").addClass( "required" );
                //$('.c_country').hide();
                //$('.c_code_ph_no').hide();
                $('#div-phone').show();
                $("#code").removeClass( "required" );
                $("#vPhone").removeClass( "required" );

            }
            else if(user=='driver')
            {
                $( "#company_name" ).removeClass( "required" );
                $( "#vFirstName" ).addClass( "required" );
                $( "#vLastName" ).addClass( "required" );
                $(".company").hide();
                $(".driver").show();
                $("#li_dob").show();
                $("#vRefCode").show();
                //$('.c_country').show();
                //$('.c_code_ph_no').show();
                $('#div-phone').hide();
                $("#div-phone").removeClass( "required" );

            }
        }
        function validate_refercode(id){

            if(id == ""){
                return true;
            }else{


                var request = $.ajax({
                    type: "POST",
                    url: 'ajax_validate_refercode.php',
                    data: 'refcode=' +id,
                    success: function (data)
                    {
                        if(data == 0){
						$("#referCheck").remove();
                        $(".vRefCode_verify").addClass('required-active');
						$('#refercodeCheck').append('<div class="required-label" id="referCheck" ><?php echo $langage_lbl['LBL_REFER_NOT_FOUND'];?></div>');
                        $('#vRefCode').attr("placeholder", "<?php echo $langage_lbl['LBL_REFERAL_CODE'];?>");
                        $('#vRefCode').val("");
                        return false;
                        }else{
                            var reponse = data.split('|');
                            $('#iRefUserId').val(reponse[0]);
                            $('#eRefType').val(reponse[1]);
                        }
                    }
                });

            }

        }
         function validate_mobile_driver(mobile)
        {
            if(mobile == ''){
				$(".vPhone_verify").addClass('required-active');
                $('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_REQUIRED_TEXT'];?></div>');
				$('button[type="submit"]').attr('disabled','disabled');
				return false;
            }
            var request = $.ajax({
                type: "POST",
                url: 'ajax_driver_mobile.php',
                data: 'id=' +mobile,
                success: function (data)
                {
                    if(data==0)
                    {
						$(".vPhone_verify").addClass('required-active');
						$('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ALREADY_EXIST'];?></div>');
                     $('button[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
                    else if(data==1)
                    {

                        var eml=/^[0-9]+$/;
                        result=eml.test(mobile);
                        if(result==true)
                        {
                        //$('#mobileCheck').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
                        $('button[type="submit"]').removeAttr('disabled');
                        }
                        else
                        {
                            $(".vPhone_verify").addClass('required-active');
							$('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ENTER_PROPER_PHONE'];?></div>');
                             $('button[type="submit"]').attr('disabled','disabled');
                              return false;
                        }
                    }
                }
            });
        }

        function check_verification(request_type)
        {
            if(request_type=='send'){
                code=$("#code").val();
            }
            else{
                code=$("#vCode1").val();
                if(code==''){
                    $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;padding:0px;"><?php echo $langage_lbl['LBL_ENTER_VERIFICATION_CODE'];?></i>');
                    return false;
                }
            }
            phone=$("#vPhone").val();

            email=$("#vEmail").val();
            name=$("#vFirstName").val();
            name+=' '+$("#vLastName").val();
            //alert(request_type);
            var request = $.ajax({
                type: "POST",
                url: 'ajax_driver_verification.php',
                dataType: "json",
                data: {'vPhone':phone,
                    'vCode':code,
                    'type':request_type,
                    'name':name,
                    'vEmail':email},
                success: function (data)
                {
                    console.log(data['code']); console.log(data['action']);


                    if(data['type']=='send'){
                        if(data['action']==0)
                        {
                            $("#mobileCheck").html('<i class="icon icon-remove alert-danger alert"><?php echo $langage_lbl['LBL_MOBILE_EXIST'];?></i>');
                            $("#mobileCheck").show();
                            $('button[type="submit"]').attr('disabled','disabled');
                            return false;
                        }
                        else{
                            return true;
                        }
                    }
                    else if(data['type']=='verify'){
                        if(data['0']==1){
                            $("#verification_error").html('');
                            document.frmsignup.submit();
                        }
                        else if(data['0']==0){
                            $("#verification_error").html('');
                            $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;" ><?php echo $langage_lbl['LBL_INVALID_VERI_CODE'];?></i>');
                        }
                        else{
                            $("#verification_error").html('');
                            $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;"><?php echo $langage_lbl['LBL_ERROR_VERIFICATION'];?></i>');
                        }
                    }
                }
            });
         }
    </script>
    <!-- End: Footer Script -->
</body>
</html>

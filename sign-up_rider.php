<?php
   include_once("common.php");
   $generalobj->go_to_home();
   $meta_arr = $generalobj->getsettingSeo(6);
   $sql = "SELECT * from language_master where eStatus = 'Active' and eDefault = 'Yes'" ;
   $db_lang = $obj->MySQLSelect($sql);
   $sql = "SELECT * from country where eStatus = 'Active'" ;
   $db_code = $obj->MySQLSelect($sql);
   //For Currency
   $sql="select * from currency where eStatus='Active' and eDefault = 'Yes'";
   $db_currency=$obj->MySQLSelect($sql);
   //echo "<pre>";print_r($db_lang);
   $script="Rider Sign-Up";

   $Mobile=$MOBILE_VERIFICATION_ENABLE;
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
    <script>
        /*function submit_form()
        {
            if( validatrix() ){
                //alert("Submit Form");
                document.frmsignup.submit();
            }else{
                console.log("Some fields are required");
                $( ".required-active:first" ).focus();
                return false;
            }
            return false; //Prevent form submition
        }*/
    </script>
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
            <h2 class="header-page trip-detail"><?php echo $langage_lbl['LBL_SIGN_UP']; ?>
                <p><?php echo $langage_lbl['LBL_TELL_US_A_BIT_ABOUT_YOURSELF']; ?></p>
            </h2>
            <!-- trips detail page -->
            <form name="frmsignup" id="frmsignup" method="post" action="signuprider_a.php">
				<input type="hidden" name="vCurrencyPassenger" value="<?php echo $db_currency[0]['vName']?>">
				<input type="hidden" name="vCountry" value="<?php echo $DEFAULT_COUNTRY_CODE_WEB?>">
				<input name="vLang" type="hidden" value="<?php echo $db_lang[0]['vCode'];?>" />
                <div class="driver-signup-page">
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
                    <div class="create-account line-dro">
                        <h3><?php echo $langage_lbl['LBL_CREATE_ACCOUNT']; ?></h3>
                        <span>
                            <strong id="emailCheck"><label><?php echo $langage_lbl['LBL_EMAIL_ID_TXT']?></label>
								<?php /*<input type="hidden" name="mobile_verification"  id="mobile_verification" value="<?php echo $Mobile;?>"> */ ?>
								<input type="text" placeholder="<?php echo $langage_lbl['LBL_PROFILE_RIDER_YOUR_EMAIL_ID']; ?>" name="vEmail" id="vEmail_verify" class="create-account-input" /></strong>
                            <strong><label><?php echo $langage_lbl['LBL_PASSENGER_TXT']?></label>
                            <input id="pass" type="password" name="vPassword" placeholder="<?php echo $langage_lbl['LBL_PASSWORD']; ?>" class="create-account-input create-account-input1 required" value="" /></strong>
                        </span>
                         <?php

                        if($REFERRAL_SCHEME_ENABLE == 'Yes'){ ?>
                         <span style="margin:0px;">
                         <strong id="refercodeCheck">
                         <input id="vRefCode" type="text" name="vRefCode" placeholder="<?php echo $langage_lbl['LBL_REFERAL_CODE']; ?>" class="create-account-input create-account-input1 vRefCode_verify" value=""  onBlur=" validate_refercode(this.value)"/>  </strong>
                            <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"  class="create-account-input required" value="" />
                            <input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input required" value=""  />
                       </span>
                         <?php }
                        ?>
                    </div>
                    <div class="create-account">
                        <h3><?php echo $langage_lbl['LBL_HEADER_PROFILE_TXT']; ?></h3>
                        <span>
                            <strong><label><?php echo $langage_lbl['LBL_FIRST_NAME_TXT']?></label>
                            <input name="vName" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_FIRST_NAME_HEADER_TXT']; ?>" id="vName"/></strong>
                            <strong><label><?php echo $langage_lbl['LBL_LAST_NAME_TXT']?></label>
                            <input name="vLastName" type="text" class="create-account-input create-account-input1 required" placeholder="<?php echo $langage_lbl['LBL_LAST_NAME_HEADER_TXT']; ?>" id="vLastName"/></strong>
                        </span>

                        <span class="c_code_ph_no">
                            <strong class="c_code"><label><?php echo $langage_lbl['LBL_CODE']?></label>
                            <input type="text"  name="vPhoneCode" readonly  class="create-account-input" id="code" /></strong>
                            <strong class="ph_no" id="mobileCheck"><label>&amp; <?php echo $langage_lbl['LBL_RIDER_Phone_Number']?></label>
                            <input type="text"  id="vPhone" placeholder="<?php echo $langage_lbl['LBL_777-777-7777']; ?>" class="create-account-input create-account-input1 required vPhone_verify" name="vPhone" onBlur="return validate_mobile(this.value)"/></strong>
                            <!-- <strong id="mobileCheck"></strong> -->
                        </span>
                        <span>
						 <!--
						<?php
						/*
						if(count($db_lang) <=1){ ?>

                            <input name="vLang" type="hidden" class="create-account-input" value="<?php echo $db_lang[0]['vCode'];?>" id="vName"/>
						<?php }else{ ?>
						 <strong>
                            <label><?php echo $langage_lbl['LBL_SELECT_LANGUAGE_TEXT']?></label>
                                <select name="vLang" class="custom-select-new ">
                                    <?php for($i=0;$i<count($db_lang);$i++) { ?>
                                    <option value="<?php echo $db_lang[$i]['vCode']?>" <?php if($db_lang[$i]['eDefault']=='Yes'){echo 'selected';}?>>
                                    <?php echo $db_lang[$i]['vTitle']?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </strong>
						<?php }
							*/
						?>
                           <strong>
                            <label>Select language</label>
                                <select name="vLang" class="custom-select-new ">
                                    <?/* for($i=0;$i<count($db_lang);$i++) { ?>
                                    <option value="<?php echo $db_lang[$i]['vCode']?>" <?php if($db_lang[$i]['eDefault']=='Yes'){echo 'selected';}?>>
                                    <?php echo $db_lang[$i]['vTitle']?>
                                    </option>
                                    <?php } */?>
                                </select>
                            </strong>
                            <strong>
                            <label><?/*=$langage_lbl['LBL_SELECT_CURRENCY']?></label>
                                <select class="custom-select-new " name = 'vCurrencyPassenger' required oninvalid="this.setCustomValidity('<?php echo $langage_lbl['LBL_PLEASE_SELECT_ITEM'];?>')" oninput="setCustomValidity('')">

                                    <?php for($i=0;$i<count($db_currency);$i++){ ?>
                                    <option value = "<?php echo  $db_currency[$i]['vName'] ?>" <?php if($vCurrencyPassenger==$db_currency[$i]['vName']){?>selected<?php } else if($db_currency[$i]['eDefault']=="Yes"){?>selected<?} ?>><?php echo  $db_currency[$i]['vName'] ?></option>
                                    <?php } */ ?>
                                </select>
                            </strong>-->
                        </span>
                        <span>
                            <abbr><?php echo $langage_lbl['LBL_Agree_to']; ?> <a href="http://par30taxi.ir/%d8%b4%d8%b1%d8%a7%db%8c%d8%b7-%d9%88-%d9%82%d9%88%d8%a7%d9%86%db%8c%d9%86-%d9%85%d8%b3%d8%a7%d9%81%d8%b1%db%8c%d9%86/" target="_blank"><?php echo $langage_lbl['LBL_TERMS_AND_CONDITION']; ?></a>
                                <div class="checkbox-n">
                                    <input id="c1" name="remember-me" type="checkbox" class="required termscheckbox" value="<?php echo $langage_lbl['LBL_remember']; ?>">
                                    <label for="c1"></label>
                                </div>

                            </abbr>
                        </span>
                    <p><button type="submit" onClick="return submit_form();" class="submit" name="SUBMIT"><?php echo $langage_lbl['LBL_BTN_SUBMIT_TXT']; ?></button></p>
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
                                    <p class="help-block"><?php echo $langage_lbl['LBL_RIDER_VERI_TEXT']?></p>
                                    <div class="form-group">
                                        <label><?php echo $langage_lbl['LBL_ENTER_VERI_CODE']?></label>
                                        <input class="form-control" type="text" id="vCode1"/>
                                    </div>
                                    <p class="help-block" id="verification_error"></p>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onClick="check_verification('verify')"><?php echo $langage_lbl['LBL_VERIFY']?></button>
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
    <script>
		 $('#verification').bind('keydown',function(e){
        if(e.which == 13){
            check_verification('verify'); return false;
        }
    });

        function submit_form()
        {
            if( validatrix() ){
				// var mobverify=$("#mobile_verification").val();
				// if(mobverify=='Yes')
				// {
				// check_verification('send');
                // $('#formModal').modal({backdrop: 'static', keyboard: false});
                // $('#formModal').modal('show');

					//document.frmsignup.submit();
					var email = $("#vEmail_verify").val();
					validate_email(email);

				// }
				// else
				// {
					 // document.frmsignup.submit();
				// }
            }else{
                console.log("Some fields are required");
                return false;
            }
            return false; //Prevent form submition
        }
function check_verification(request_type)
    {
        if(request_type=='send'){
            code=$("#code").val();
        }
        else{
            code=$("#vCode1").val();
            if(code==''){
                $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;padding:0px;"><?php echo $langage_lbl['LBL_ENTER_VERIFICATION_CODE']?></i>');
                return false;
            }
        }
        phone=$("#vPhone").val();

        email=$("#vEmail_verify").val();
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
               // console.log(data['code']); console.log(data['action']);


                if(data['type']=='send'){
                    if(data['action']==0)
                    {
                        $("#mobileCheck").html('<i class="icon icon-remove alert-danger alert"><?php echo $langage_lbl['LBL_MOBILE_EXIST']?></i>');
                        $("#mobileCheck").show();
                        $('input[type="submit"]').attr('disabled','disabled');
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
                        $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;" ><?php echo $langage_lbl['LBL_VERIFICATION_CODE_INVALID']?></i>');
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
    <script type="text/javascript">

		$("#vEmail_verify").bind("keypress click",function(){
			$(".required-label").remove();
			$("#vEmail_verify").removeClass('required-active');
		});
        function validate_email(id)
        {
            var request = $.ajax({
                 type: "POST",
                 url: 'ajax_rider_email.php',
                 data: 'id=' +id,
                 success: function (data)
                 {
                    if(data==0)
                    {
						 $('#vEmail_verify').addClass('required-active');
						 window.scrollTo(0,0);
						 $('#vEmail_verify').focus();
						 $('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_EMAIL_ALREADY_EXIST'];?></div>');
						 return false;
                    }
                    else if(data==1)
                    {
                        var eml=/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        result=eml.test(id);
                        if(result==true)
                        {
							$('#vEmail_verify').removeClass('required-active');
							//$("#frmsignup").attr("action","signup_a.php");
							document.frmsignup.submit();
                        }
                        else
                        {
                            $('#vEmail_verify').addClass('required-active');
							window.scrollTo(0,0);
							$('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ENTER_PROPER_EMAIL']?></div>');
							$( "#vEmail_verify" ).focus();
                            return false;
                        }
                    }
					else if(data == 2){
							$('#vEmail_verify').addClass('required-active');
							window.scrollTo(0,0);
							$('#emailCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_NOT_ACTIVE_CONTACTADMIN']?></div>');
							$( "#vEmail_verify" ).focus();
                            return false;
					}
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

		$(document).ready(function(){
			var code= '<?php echo $DEFAULT_COUNTRY_CODE_WEB?>';
			changeCode(code);
		});
        function validate_mobile(mobile)
        {
            if(mobile == ''){
				$(".vPhone_verify").addClass('required-active');
				$('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_REQUIRED_TEXT']?></div>');
				$('input[type="submit"]').attr('disabled','disabled');
                return false;
            }
            var request = $.ajax({
                type: "POST",
                url: 'ajax_rider_mobile.php',
                data: 'id=' +mobile,
                success: function (data)
                {
                    if(data==0)
                    {
						$(".vPhone_verify").addClass('required-active');
						$('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ALREADY_EXIST']?></div>');
						$('input[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
                    else if(data==1)
                    {

                        var eml=/^[0-9]+$/;
                        result=eml.test(mobile);
                        if(result==true)
                        {
                        $(".vPhone_verify").removeClass('required-active');
                        $('input[type="submit"]').removeAttr('disabled');
                        }
                        else
                        {
                            $(".vPhone_verify").addClass('required-active');
							$('#mobileCheck').append('<div class="required-label" ><?php echo $langage_lbl['LBL_ENTER_PROPER_PHONE']?></div>');
                             $('input[type="submit"]').attr('disabled','disabled');
                             return false;
                        }
                    }
                }
            });
        }
        function fbconnect()
        {
            javscript:window.location='fbconnect.php';
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
						$('#refercodeCheck').append('<div class="required-label" id="referCheck" ><?php echo $langage_lbl['LBL_REFER_CODE_NOT_FOUND']?></div>');
                        $('#vRefCode').attr("placeholder", "<?php echo $langage_lbl['LBL_REFERAL_CODE']?>");
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

    </script>
    <!-- End: Footer Script -->
</body>
</html>

<?php 
    include_once("common.php");
	$generalobj->go_to_home();
    $script="Driver Sign-Up";
    $sql="select * from  currency where eStatus='Active'";
    $db_currency=$obj->MySQLSelect($sql);
    $sql = "SELECT * from country where eStatus = 'Active'" ;
    $db_code = $obj->MySQLSelect($sql);
	$meta_arr = $generalobj->getsettingSeo(5);
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
            <form name="frmsignup" id="frmsignup" method="post" action="signup_a.php">
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
                            <input type="text" placeholder="<?php echo $langage_lbl['LBL_EMAIL_name@email.com']; ?>" name="vEmail" class="create-account-input required" onBlur=" validate_email(this.value)" id="vEmail_verify" value="" /></strong>
                            <strong><label><?php echo $langage_lbl['LBL_PASSWORD_LBL_TXT']; ?></label>
                            <input id="pass" type="password" name="vPassword" placeholder="<?php echo $langage_lbl['LLB_PASSWORD_Password: At least 5 characters']; ?>" class="create-account-input create-account-input1 required" value="" /></strong>
                        </span> 
                        <span>
                            <?php /*<input type="hidden" name="mobile_verification"  id="mobile_verification" value="<?php echo $Mobile;?>"> */ ?>
                            <input type="hidden" placeholder="" name="iRefUserId" id="iRefUserId"  class="create-account-input" value="" />
                            <input type="hidden" placeholder="" name="eRefType" id="eRefType" class="create-account-input" value=""  />
                        </span> 

                        <span class="c_country">
                            <strong>
                                <select name="vCountry" class="custom-select-new" onChange="changeCode(this.value); ">
                                    
                                    <?php for($i=0;$i<count($db_code);$i++) { ?>
                                    <option value="<?php echo $db_code[$i]['vCountryCode']?>" <?php if($db_code[$i]['vCountryCode']== $DEFAULT_COUNTRY_CODE_WEB){echo 'selected';}?>><?php echo $db_code[$i]['vCountry']?></option>
                                    <?php } ?>
                                </select>
                            </strong>
                        </span> 
                         
                         <span class="c_code_ph_no">
                            <strong class="c_code"><input type="text"  name="vCode" readonly  class="create-account-input required " id="code" /></strong>
                            <strong class="ph_no" id="mobileCheck"><input type="text"  id="vPhone" placeholder="<?php echo $langage_lbl['LBL_SIGNUP_777-777-7777']; ?>" class="create-account-input create-account-input1 required vPhone_verify" name="vPhone" onBlur="return validate_mobile_driver(this.value)"/></strong>
                        </span>
                         <?php 

                        if($REFERRAL_SCHEME_ENABLE == 'Yes'){ ?>
                               <span><strong id="refercodeCheck"><input id="vRefCode" type="text" name="vRefCode" placeholder="<?php echo $langage_lbl['LBL_SIGNUP_REFERAL_CODE']; ?>" class="create-account-input create-account-input1 vRefCode_verify" value="" onBlur=" validate_refercode(this.value)"/></strong></span> 

                        <?php }
                        ?>


                    </div>
                    <div class="create-account">
                        <h3 class="company" style="display: none;"><?php echo $langage_lbl['LBL_Company_Information']; ?></h3>
                        <h3 class="driver"><?php echo $langage_lbl['LBL_Driver_Information']; ?></h3>
                        <span class="driver">
                            <strong><label><?php echo $langage_lbl['LBL_FIRST_NAME_TXT']; ?></label>
                            <input name="vFirstName" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_SIGN_UP_FIRST_NAME_HEADER_TXT']; ?>" id="vFirstName"/></strong>
                            <strong><label><?php echo $langage_lbl['LBL_LAST_NAME_TXT']; ?></label>
                            <input name="vLastName" type="text" class="create-account-input create-account-input1 required" placeholder="<?php echo $langage_lbl['LBL_SIGN_UP_LAST_NAME_HEADER_TXT']; ?>" id="vLastName"/></strong>
                        </span> 
                        <span class="company" style="display: none;">
                            <strong><label><?php echo $langage_lbl['LBL_COMPANY1_NAME_TXT']; ?></label>
                            <input type="text" id="company_name" placeholder="<?php echo $langage_lbl['LBL_Company_name']; ?>" class="create-account-input required" name="vCompany"/></strong>
                        </span>
                        <span>
                            <strong><label><?php echo $langage_lbl['LBL_ADDRESS_NAME_TXT']; ?></label>
                            <input name="vCaddress" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_ADDRESS']; ?>"/></strong>
                            <strong><label><?php echo $langage_lbl['LBL_ADDRESS2_TXT']; ?></label>
                            <input name="vCadress2" type="text" class="create-account-input create-account-input1" placeholder="<?php echo $langage_lbl['LBL_ADDRESS']; ?> 2"/>
                            </strong>
                        </span> 
                        <span>
                            <strong><label><?php echo $langage_lbl['LBL_CITY_TXT']; ?></label>
                            <input name="vCity" type="text" class="create-account-input required" placeholder="<?php echo $langage_lbl['LBL_City']; ?>"/></strong>
                            <strong><label><?php echo $langage_lbl['LBL_ZIP_CODE_TXT']; ?></label>
                            <input name="vZip" type="text" class="create-account-input create-account-input1 required" placeholder="<?php echo $langage_lbl['LBL_ZIP_CODE']; ?>"/></strong>
                        </span>
                        <span>
                            <strong>
                            <label><?php echo $langage_lbl['LBL_SELECT_CURRANCY_TXT']; ?></label>
                                <select class="custom-select-new" name = 'vCurrencyDriver'>
                                    <?php for($i=0;$i<count($db_currency);$i++){ ?>
                                    <option value = "<?php echo  $db_currency[$i]['vName'] ?>" <?php if($db_currency[$i]['eDefault']=="Yes"){?>selected<?} ?>>
                                    <?php echo  $db_currency[$i]['vName'] ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </strong>
                            <b id="li_dob">
                                <strong>
								<?php echo $langage_lbl['LBL_Date_of_Birth']; ?></strong>
                                <select name="vDay" data="DD" class="custom-select-new">
                                    <option><?php echo $langage_lbl['LBL_DATE_TXT']; ?></option>
                                    <?php for($i=1;$i<=31;$i++) {?>
                                    <option value="<?php echo $i?>">
                                    <?php echo $i?>
                                    </option>
                                    <?php }?>
                                </select>
                                <select data="MM" name="vMonth" class="custom-select-new">
                                    <option><?php echo $langage_lbl['LBL_MONTH_TXT']; ?></option>
                                    <?php for($i=1;$i<=12;$i++) {?>
                                    <option value="<?php echo $i?>">
                                    <?php echo $i?>
                                    </option>
                                    <?php }?>
                                </select>
                                <select data="YYYY" name="vYear" class="custom-select-new">
                                    <option><?php echo $langage_lbl['LBL_YEAR']; ?></option>
                                    <?php for($i=1950;$i<=date("Y");$i++) {?>
                                    <option value="<?php echo $i?>">
                                    <?php echo $i?>
                                    </option>
                                    <?php }?>
                                </select>
                            </b>
                        </span> 
                        <span>
                            <abbr><?php echo $langage_lbl['LBL_SIGNUP_Agree_to']; ?> <a href="terms_condition.php" target="_blank"><?php echo $langage_lbl['LBL_SIGN_UP_TERMS_AND_CONDITION']; ?></a>
                                <div class="checkbox-n">
                                    <input id="c1" name="remember-me" type="checkbox" class="required" value="remember">
                                    <label for="c1"></label>
                                </div>
                                
                            </abbr> 
                        </span>
                    
                    <p><button type="submit" onClick="return submit_form();" class="submit" name="SUBMIT"><?php echo $langage_lbl['LBL_BTN_SIGN_UP_SUBMIT_TXT']; ?></button></p>
                    </div>
                </div>
            </form>
            <div class="col-lg-12">
                <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title" id="H2">Phone Verification</h4>
                            </div>
                            <div class="modal-body">
                                <form role="form" name="verification" id="verification">
                                    <p class="help-block">To complete the driver registration process, you must have to enter the verification code sent to your registered phone number. </p>
                                    <div class="form-group">
                                        <label>Enter Verification code below</label>
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
        function submit_form()
        {
			validate_email($("#vEmail_verify"));
            if( validatrix() ){

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


                    //var mobverify=$("#mobile_verification").val();           
                    // if(mobverify=='Yes')
                    // {                   

                        // check_verification('send');
                        // $('#formModal').modal({backdrop: 'static', keyboard: false});
                        // $('#formModal').modal('show');
                        // return false;
                        document.frmsignup.submit();
                    // }
                    // else
                    // {
                         //document.frmsignup.submit();
                    //}


                }else{

                    document.frmsignup.submit();

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
			if(id != "") {
            var request = $.ajax({
                 type: "POST",
                 url: 'ajax_validate_email.php',
                 data: 'id=' +id,
                 success: function (data)
                 {
                    if(data==0)
                    {
					 $('#vEmail_verify').addClass('required-active');
                     $('#emailCheck').append('<div class="required-label" >*Invalid Email, Already Exist.</div>');
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
                        if(result==true)
                        {
                        //$('#emailCheck').text('<i class="icon icon-ok alert-success alert" onclick="hide()"> Valid</i>');
						$('#emailCheck').removeClass('required-active');
                        //$('button[type="submit"]').removeAttr('disabled');
                        }
                        else
                        {
							$('#vEmail_verify').addClass('required-active');
							$('#emailCheck').append('<div class="required-label" >*Enter Proper Email.</div>');
                            //$('#emailCheck').text('Enter Proper Email');
                           // $('button[type="submit"]').attr('disabled','disabled');
                              return false;
                        }
                    }
                    else if(data==2)
                    {
						$('#vEmail_verify').addClass('required-active');
						$('#emailCheck').append('<div class="required-label" >*Your Account is deleted please contact admin.</div>');
						// $('#emailCheck').html('Your Account is deleted please contact admin');
						//$('button[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
               }
            });
			}else {
				$('#vEmail_verify').addClass('required-active');
				$('#emailCheck').append('<div class="required-label" >*This text field is required.</div>');
			}
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
                      $('#username').html('<i class="icon icon-remove alert-danger alert">User Name,Already Exist</i>');
                      $('button[type="submit"]').attr('disabled','disabled');

                     return false;
                    }
                    else if(data==1)
                    {
                        $('#username').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
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
                $('#mobileCheck').append('<div class="required-label" >*Enter Proper Mobile No.</div>');
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
						$('#refercodeCheck').append('<div class="required-label" id="referCheck" >*Refer code Not Found.</div>');
                        $('#vRefCode').attr("placeholder", "Referal Code (Optional)");
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
                $('#mobileCheck').append('<div class="required-label" >*This text field is required.</div>');
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
						$('#mobileCheck').append('<div class="required-label" >*Already Exist.</div>');
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
							$('#mobileCheck').append('<div class="required-label" >*Enter Proper Mobile No.</div>');
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
                    $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;padding:0px;">Please Enter verification code</i>');
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
                            $("#mobileCheck").html('<i class="icon icon-remove alert-danger alert">mobile no,Already Exist</i>');
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
                            $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;" >Invalid Verification code, please try again.</i>');
                        }
                        else{
                            $("#verification_error").html('');
                            $("#verification_error").html('<i class="icon icon-remove alert" style="display:inline-block;color:red;">Error in verification. please try again.</i>');
                        }
                    }
                }
            });
         }
    </script>
    <!-- End: Footer Script -->
</body>
</html>

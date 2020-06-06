<?
   include_once("common.php");
   $meta_arr = $generalobj->getsettingSeo(2);
  
   $sql = "SELECT * from language_master where eStatus = 'Active'" ;
   $db_lang = $obj->MySQLSelect($sql);
   $sql = "SELECT * from country where eStatus = 'Active'" ;
   $db_code = $obj->MySQLSelect($sql);
   //echo "<pre>";print_r($db_lang);
	$script="Contact Us";
   if($_POST)
{
  $Data['vFirstName'] = $_POST['vName'];
  $Data['vLastName'] = $_POST['vLastName'];
  $Data['eSubject'] =  stripslashes($_POST['vSubject']);
  $temp_var=str_replace('\r\n','<br>',$_POST['vDetail']);
  $Data['tSubject'] =  stripslashes("<br>".$temp_var);
  $Data['vEmail'] = $_POST['vEmail'];
  $Data['cellno'] = $_POST['vPhone'];
  $return = $generalobj->send_email_user("CONTACTUS",$Data);
  if($return){
    $success = 1;
    $var_msg = $langage_lbl['LBL_SENT_CONTACT_QUERY_SUCCESS_TXT'];
  }else{
    $error = 1;
    $var_msg = $langage_lbl['LBL_ERROR_OCCURED'];
  }
}
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!--<title><?php echo $COMPANY_NAME?> | Contact Us</title>-->
	<title><?php echo $meta_arr['meta_title'];?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
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
                <h2 class="header-page"><?php echo $langage_lbl['LBL_CONTACT_US_HEADER_TXT']; ?>
                    <p><?php echo $langage_lbl['LBL_WELCOME_TO']; ?> <?php echo $SITE_NAME?>, <?php echo $langage_lbl['LBL_CONTACT_US_SECOND_TXT']; ?>.</p>
                </h2>
                <!-- contact page -->
                <div style="clear:both;"></div>
                <?php if ($success ==1) { ?>
                        <div class="alert alert-success alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                            <?php echo  $var_msg ?>
                        </div>
                        <?php }
                        else if($error ==1)
                        {
                        ?>
                        <<div class="alert alert-danger alert-dismissable">
                            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button> 
                            <?php echo  $var_msg ?>
                        </div>
                    <?php }?>
                    <div style="clear:both;"></div>
                <form name="frmsignup" id="frmsignup" method="post" action="">
                    <div class="contact-form"> 
                    
                        <b>
                            <strong><input type="text" name="vName" placeholder="<?php echo $langage_lbl['LBL_CONTECT_US_FIRST_NAME_HEADER_TXT']; ?>" class="contact-input required" value="" /></strong>
                            <strong><input type="text" name="vLastName" placeholder="<?php echo $langage_lbl['LBL_CONTECT_US_LAST_NAME_HEADER_TXT']; ?>" class="contact-input required" value="" /></strong>
                            <strong><input type="text" placeholder="<?php echo $langage_lbl['LBL_CONTECT_US_EMAIL_LBL_TXT']; ?>" name="vEmail" value="" autocomplete="off" onChange="return validate_email(this.value)"  class="contact-input required"/></strong>
                            <strong><input type="text" placeholder="<?php echo $langage_lbl['LBL_CONTECT_US_777-777-7777'];?>" name="vPhone" class="contact-input required" onChange="return validate_mobile(this.value)"/></strong>
                        </b> 
                        <b>
                            <strong><input type="text" name="vSubject" placeholder="<?php echo $langage_lbl['LBL_ADD_SUBJECT_HINT_CONTACT_TXT']; ?>" class="contact-input required" /></strong>
                            <strong><textarea cols="61" rows="5" placeholder="<?php echo $langage_lbl['LBL_ENTER_DETAILS_TXT']; ?>" name="vDetail" class="contact-textarea required"></textarea></strong>
                        </b> 
                        <b>
                            <input type="submit" onClick="return submit_form();"  class="submit-but" value="<?php echo $langage_lbl['LBL_BTN_CONTECT_US_SUBMIT_TXT']; ?>" name="SUBMIT" />
                        </b> 
                    </div>
                </form>
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
    <script>
        function submit_form()
        {
            if( validatrix() ){
                //alert("Submit Form");
                document.frmsignup.submit();
            }else{
                console.log("Some fields are required");
                return false;
            }
            return false; //Prevent form submition
        }
    </script>
    <script type="text/javascript">
    function validate_email(id)
               {
                                var eml=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                                result=eml.test(id);
                                if(result==true)
                                {
                                $('#emailCheck').html('<i class="icon icon-ok alert-success alert"> <?php echo $langage_lbl['LBL_VALID'];?></i>');
                                $('input[type="submit"]').removeAttr('disabled');
                                }
                                else
                                {
                                    $('#emailCheck').html('<i class="icon icon-remove alert-danger alert"> <?php echo $langage_lbl['LBL_ENTER_PROPER_EMAIL'];?></i>');
                                     $('input[type="submit"]').attr('disabled','disabled');
                                      return false;
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

               function validate_mobile(mobile)
        {

                var eml=/^[0-9]+$/;
                                result=eml.test(mobile);
                                if(result==true)
                                {
                                $('#mobileCheck').html('<i class="icon icon-ok alert-success alert"> <?php echo $langage_lbl['LBL_VALID'];?></i>');
                                $('input[type="submit"]').removeAttr('disabled');
                                }
                                else
                                {
                                    $('#mobileCheck').html('<i class="icon icon-remove alert-danger alert"> <?php echo $langage_lbl['LBL_ENTER_PROPER_PHONE'];?></i>');
                                     $('input[type="submit"]').attr('disabled','disabled');
                                      return false;
                                }
        }


    </script>
    <!-- End: Footer Script -->
</body>
</html>

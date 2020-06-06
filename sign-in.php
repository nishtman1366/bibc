<?php
    include_once("common.php");
    $generalobj->go_to_home();
    $action = isset($_GET['action'])?$_GET['action']:'';
	$script="Login Main";
	$meta_arr = $generalobj->getsettingSeo(1);
?>
<!DOCTYPE html>
<html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!--<title><?php echo $SITE_NAME?> | Login Page</title>-->
    <title><?php echo $meta_arr['meta_title'];?></title>
    <!-- Default Top Script and css -->
    <?php include_once("top/top_script.php");?>
    <!-- End: Default Top Script and css-->
</head>
<body>
  <div id="main-uber-page">
    <!-- Left Menu -->
    <?php include_once("top/left_menu.php");?>
    <!-- End: Left Menu-->
    <!-- home page -->

        <!-- Top Menu -->
        <?php include_once("top/header_topbar.php");?>
        <!-- End: Top Menu-->
        <!-- contact page-->
        <div class="page-contant">
            <div class="page-contant-inner">
                <h2 class="header-page"><?php echo $langage_lbl['LBL_SIGN_IN_SIGN_IN_TXT'];?></h2>

				<div class="col-lg-6">
                <!-- login in page -->
                <div class="sign-in">
                    <div align="justify" dir="rtl" style="padding:0px;min-height:50px" class="sign-in-driver">
                        <h3 align="justify" dir="rtl"><?php echo $langage_lbl['LBL_SIGN_IN_DRIVER'];?></h3>
                          </div>
                </div>
              <div style="clear:both;"></div>

				<div style="margin:0px 0px 0px;"class="sign-in">
                    <div align="justify" dir="rtl" style="padding:0px;min-height:50px"class="sign-in-driver">
                        <p style="width:100%"><?php echo $langage_lbl['LBL_SIGN_NOTE1'];?></p>  </div>
                </div>
                <div style="clear:both;"></div>


				<div style="margin:0px 0px 0px;"class="sign-in">
                    <div align="justify" dir="rtl" style="padding:0px;min-height:50px"class="sign-in-driver">
                        <span style="margin:0px 0px 0px;"><a href="driver-login"><?php echo  $langage_lbl['LBL_DRIVER_SIGNIN'];?><!--img src="assets/img/arrow-white-right.png" alt="" /--></a></span>
                    </div>
                </div>
                <div style="clear:both;"></div>

<div style="margin:0px 0px 0px;"class="sign-in">

                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-driver">
                   </div>
                </div>
                <div style="clear:both;"></div>
				</div>


				<div  class="col-lg-6">
				<!-- login in page -->
                <div class="sign-in">
                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                    <h3 align="justify" dir="rtl"><?php echo $langage_lbl['LBL_RIDER'];?></h3>
                        </div>
                </div>
              <div style="clear:both;"></div>

				<div style="margin:0px 0px 0px;"class="sign-in">
                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                      <p style="width:100%"align="justify" dir="rtl"><?php echo $langage_lbl['LBL_SIGN_NOTE2'];?></p>
                       </div>
                </div>
                <div style="clear:both;"></div>


					<div style="margin:0px 0px 0px;"class="sign-in">

                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                       <span style="margin:0px 0px 0px;"><a href="rider-login"><?php echo $langage_lbl['LBL_RIDER_SIGNIN'];?><!--img src="assets/img/arrow-white-right.png" alt="" /--></a></span>
                    </div>
                </div>
                <div style="clear:both;"></div>

<div style="margin:0px 0px 0px;"class="sign-in">

                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                   </div>
                </div>
                <div style="clear:both;"></div>
				</div>

        <div  class="col-lg-6">
        <!-- login in page -->
                <div class="sign-in">
                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                    <h3 align="justify" dir="rtl">سازمان حمل و نقل</h3>
                        </div>
                </div>
              <div style="clear:both;"></div>

        <div style="margin:0px 0px 0px;"class="sign-in">
                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                      <p style="width:100%"align="justify" dir="rtl">سازمان حمل و نقل میتواند بر روی تمامی تاکسیرانی و آژانس های خود مدیریت داشته باشند</p>
                       </div>
                </div>
                <div style="clear:both;"></div>


          <div style="margin:0px 0px 0px;"class="sign-in">

                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                       <span style="margin:0px 0px 0px;"><a href="driver-login">ورود به عنوان سازمان حمل و نقل<!--img src="assets/img/arrow-white-right.png" alt="" /--></a></span>
                    </div>
                </div>
                <div style="clear:both;"></div>

        <div style="margin:0px 0px 0px;"class="sign-in">

                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-rider">
                   </div>
                </div>
                <div style="clear:both;"></div>
        </div>
        <div class="col-lg-6">
                <!-- login in page -->
                <div class="sign-in">
                    <div align="justify" dir="rtl" style="padding:0px;min-height:50px" class="sign-in-driver">
                        <h3 align="justify" dir="rtl">تاکسیرانی و آژانس ها</h3>
                          </div>
                </div>
              <div style="clear:both;"></div>

        <div style="margin:0px 0px 0px;"class="sign-in">
                    <div align="justify" dir="rtl" style="padding:0px;min-height:50px"class="sign-in-driver">
                        <p style="width:100%">تاکسیرانی و آژانس ها میتوانند بر روی تمامی مسافرین و رانندگان خود مدیریت داشته باشند</p>  </div>
                </div>
                <div style="clear:both;"></div>


        <div style="margin:0px 0px 0px;"class="sign-in">
                    <div align="justify" dir="rtl" style="padding:0px;min-height:50px"class="sign-in-driver">
                        <span style="margin:0px 0px 0px;"><a href="driver-login">ورود به عنوان تاکسیران و آژانس<!--img src="assets/img/arrow-white-right.png" alt="" /--></a></span>
                    </div>
                </div>
                <div style="clear:both;"></div>

        <div style="margin:0px 0px 0px;"class="sign-in">

                    <div align="justify" dir="rtl" style="min-height:50px"class="sign-in-driver">
                   </div>
                </div>
                <div style="clear:both;"></div>
        </div>
            </div>
        </div>

    <!-- home page end-->
    <!-- footer part -->
    <?php include_once('footer/footer_home.php');?>
      <!-- End:contact page-->
      <div style="clear:both;"></div>
    </div>
    <!-- footer part end -->
    <!-- Footer Script -->
    <?php include_once('top/footer_script.php');?>
    <!-- End: Footer Script -->
</body>
</html>

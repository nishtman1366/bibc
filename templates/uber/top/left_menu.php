<?php
$curr_url = basename($_SERVER['PHP_SELF']);
//include 'common.php' ;
$user = $_SESSION["sess_user"];
if ($user == 'driver') {
     $sql = "select * from register_driver where iDriverId = '" . $_SESSION['sess_iUserId'] . "'";
     $db_data = $obj->sql_query($sql);
     if ($db_data[0]['vImage'] == "NONE" || $db_data[0]['vImage'] == '' ) {
          $db_data[0]['img'] = "";
     } else {

       $db_data[0]['img'] = $tconfig["tsite_upload_images_driver"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
     }
}
if ($user == 'company') {
  $sql = "select * from company where iCompanyId = '" . $_SESSION['sess_iUserId'] . "'";
     $db_data = $obj->sql_query($sql);
// Mamad H . A . M (Start)
     $companyparent = 0;
     		$resultdb = mysqli_query($condbc,"SELECT COUNT(*) FROM `company` WHERE `iParentId` = '" . $_SESSION['sess_iUserId'] . "'");

                                                     while($rowdb = mysqli_fetch_array($resultdb))
                                                       {
                                                       $companyparent = $rowdb['COUNT(*)'];
     																									}
// Mamad H . A . M (End)


     if ($db_data[0]['vImage'] == "NONE" || $db_data[0]['vImage'] =='') {
         $db_data[0]['img'] = "";
     } else {
       $db_data[0]['img'] = $tconfig["tsite_upload_images_compnay"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImage'];
     }
}
if ($user == 'rider') {
     $sql = "select * from register_user where iUserId = '" . $_SESSION['sess_iUserId'] . "'";
     $db_data = $obj->sql_query($sql);
     if ($db_data[0]['vImgName'] != "NONE") {
          $db_data[0]['img'] = $tconfig["tsite_upload_images_passenger"] . '/' . $_SESSION['sess_iUserId'] . '/2_' . $db_data[0]['vImgName'];
     } else {
          $db_data[0]['img'] = "";
     }
}  //echo "<pre>";print_r($db_data);echo "</pre>";
?>
<span id="shadowbox" onClick="menuClose()"></span>
<nav>
  <button id="navBtnShow" onClick="menuOpen()">
  <div></div>
  <div></div>
  <div></div>
  </button>
  <ul id="listMenu">
    <span class="desktop">
        <div class="menu-logo">
		<section id="navBtn" class="navBtnNew navOpen" onClick="menuClose()">
		  <div></div>
		  <div></div>
		  <div></div>
		</section>
		<img src="assets/img/menu-logo.png" alt=""></div>
        <li><a href="index.php" class="<?php echo (isset($script) && $script == 'Home')?'active':'';?>"><?php echo $langage_lbl['LBL_HOME']; ?></a></li>
        <li><a href="http://k68.ir/about-us/" class="<?php echo (isset($script) && $script == 'About Us')?'active':'';?>"><?php echo $langage_lbl['LBL_ABOUT_US_TXT']; ?></a></li>
        <li><a href="http://k68.ir/karaj/" class="<?php echo (isset($script) && $script == 'Help Center')?'active':'';?>"><?php echo $langage_lbl['LBL_HELP_CENTER']; ?></a></li>
        <li><a href="http://k68.ir/contact-us/" class="<?php echo (isset($script) && $script == 'Contact Us')?'active':'';?>"><?php echo $langage_lbl['LBL_CONTACT_US_TXT']; ?></a></li>
        <?php
          if($user==""){
        ?>
        <li><a href="sign-in" class="<?php echo (isset($script) && $script == 'Login Main')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_LOGIN']; ?></a></li>
        <b>
          <a href="sign-up-rider" class="<?php echo (isset($script) && $script == 'Rider Sign-Up')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFTMENU_SIGN_UP_TO_RIDE']; ?></a>
          <a class="<?php echo (isset($script) && $script == 'Driver Sign-Up')?'active':'';?>" href="sign-up.php"><?php echo $langage_lbl['LBL_LEFTMENU_BECOME_A_DRIVER']; ?></a>
        </b>
        <?php
          }
          else {
        ?>
        <?php
            if($user != 'rider'){
        ?>
          <li><a href="profile" class="<?php echo (isset($script) && $script == 'Profile')?'active':'';?>"><?php echo $langage_lbl['LBL_MY_PROFILE_HEADER_TXT']; ?></a></li>
          <li><a href="logout"><?php echo $langage_lbl['LBL_LOGOUT']; ?></a></li>
        <?php
            }
            else if($user == 'rider'){
        ?>
          <li><a href="profile-rider"><?php echo $langage_lbl['LBL_MY_PROFILE_HEADER_TXT']; ?></a></li>
          <li><a href="logout"><?php echo $langage_lbl['LBL_LOGOUT']; ?></a></li>
        <?php
            }
          }
        ?>
    </span>

    <span class="mobile">
        <div class="menu-logo">
		<section id="navBtn" class="navBtnNew navOpen" onClick="menuClose()">
		  <div></div>
		  <div></div>
		  <div></div>
		</section>
		<img src="assets/img/menu-logo.png" alt=""></div>
        <!-- Top Menu Mobile -->
        <?php
              if($user == 'driver'){
          ?>
          <li><a href="profile" class="<?php echo (isset($script) && $script == 'Profile')?'active':'';?>"><?php echo $langage_lbl['LBL_MY_PROFILE_HEADER_TXT']; ?></a></li>
          <li><a href="vehicle" class="<?php echo (isset($script) && $script == 'Vehicle')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_VEHICLES']; ?></a></li>
          <li><a href="driver-trip" class="<?php echo (isset($script) && $script == 'Trips')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_TRIPS']; ?></a></li>
          <li><a href="payment-request" class="<?php echo (isset($script) && $script == 'Payment Request')?'active':'';?>"><?php echo $langage_lbl['LBL_PAYMENT']; ?></a></li>
          <?php
              }
              else if($user == 'company'){
          ?>
              <li><a href="profile" class="<?php echo (isset($script) && $script == 'Profile')?'active':'';?>"> <?php echo $langage_lbl['LBL_MY_PROFILE_HEADER_TXT']; ?></a></li>
              <li><a href="driverlist" class="<?php echo (isset($script) && $script == 'Driver')?'active':'';?>"><?php echo $langage_lbl['LBL_DRIVER']; ?></a></li>
              <li><a href="vehicle" class="<?php echo (isset($script) && $script == 'Vehicle')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_VEHICLES']; ?></a></li>
              <li><a href="company-trip" class="<?php echo (isset($script) && $script == 'Trips')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_TRIPS']; ?></a></li>
              <!-- Mamad H . A . M (Start) -->
  												<?php  if($companyparent != 0){ ?>
  																		 <li> <a href="ajans" class="<?php echo (isset($script) && $script == 'Ajansha')?'active':'';?>">آژانس های من</a></li>
  											                              <li> <a href="ajans" class="<?php echo (isset($script) && $script == 'Ajansha')?'active':'';?>">اپراتورها</a></li>
  												<?php }?>
  						<!-- Mamad H . A . M (End) -->
          <?php
              }
              else if($user == 'rider'){
          ?>
              <li><a href="profile-rider" class="<?php echo (isset($script) && $script == 'Profile')?'active':'';?>"><?php echo $langage_lbl['LBL_MY_PROFILE_HEADER_TXT']; ?></a></li>
              <li><a href="mytrip" class="<?php echo (isset($script) && $script == 'Trips')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_TRIPS']; ?></a></li>
         <?php
              }
          ?>
          <!-- End Top Menu Mobile -->
        <li><a href="index.php" class="<?php echo (isset($script) && $script == 'Home')?'active':'';?>"><?php echo $langage_lbl['LBL_HOME']; ?></a></li>
        <li><a href="about-us" class="<?php echo (isset($script) && $script == 'About Us')?'active':'';?>"><?php echo $langage_lbl['LBL_ABOUT_US_TXT']; ?></a></li>
        <li><a href="help-center" class="<?php echo (isset($script) && $script == 'Help Center')?'active':'';?>"><?php echo $langage_lbl['LBL_HELP_CENTER']; ?></a></li>
        <li><a href="contact-us" class="<?php echo (isset($script) && $script == 'Contact Us')?'active':'';?>"><?php echo $langage_lbl['LBL_CONTACT_US_TXT']; ?></a></li>
        <?php
          if($user==""){
        ?>
        <li><a href="sign-in" class="<?php echo (isset($script) && $script == 'Login Main')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFT_MENU_LOGIN']; ?></a></li>
        <b>
          <a href="sign-up-rider" class="<?php echo (isset($script) && $script == 'Rider Sign-Up')?'active':'';?>"><?php echo $langage_lbl['LBL_LEFTMENU_SIGN_UP_TO_RIDE']; ?></a>
          <a class="<?php echo (isset($script) && $script == 'Driver Sign-Up')?'active':'';?>" href="sign-up.php"><?php echo $langage_lbl['LBL_LEFTMENU_BECOME_A_DRIVER']; ?></a>
        </b>
        <?php
          }
          else {
            if($_SESSION['parent'] != "" && $_SESSION['parent'] != 0)
            {

              echo '<li><a href="logout">بازگشت به پنل</a></li>';

            }
            else {
              echo '<li><a href="logout">' . $langage_lbl['LBL_HEADER_LOGOUT'] . '</a></li>';
            }
        ?>

          <!--<li><a href="logout"><?php echo $langage_lbl['LBL_LOGOUT']; ?></a></li>-->
        <?php

          }
        ?>
    </span>
  </ul>
</nav>

<?php 
include 'common.php';
$generalobj->go_to_home();
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>" class="ie9"> <![endif]-->
<!--[if !IE]><!--><html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>"> <!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="UTF-8" />
<title><?php echo $SITE_NAME?> | Login Page</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta content="" name="keywords" />
<meta content="" name="description" />
<meta content="" name="author" />
<!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
<!-- GLOBAL STYLES -->
<!-- PAGE LEVEL STYLES -->
<link rel="stylesheet" href="assets/plugins/bootstrap/css/bootstrap.css" />
<link rel="stylesheet" href="assets/css/login.css" />
<link rel="stylesheet" href="assets/plugins/magic/magic.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
<!-- PAGE CONTENT -->
<div class="container">
  <div class="text-center"> <a href="index.php"> <img src="<?php echo $tconfig['tsite_img'];?>/logo.png" id="<?php echo $SITE_NAME?>" alt=" <?php echo $SITE_NAME?>" /> </a> </div>
  <div class="sign-in-heading" >
    <h3>LOG IN</h3>
  </div>
  <div class="tab-content">
    <div id="login" class="tab-pane active">
      <form action="index.html" class="form-signin2 form-login">
        <a href = "login_new.php?action=rider">LOG IN AS A RIDER</a> <a href = "login_new.php?action=driver" class="login-option-2">LOG IN AS A DRIVER / COMPANY</a><br>
      </form>
    </div>
  </div>
</div>
</div>
<!--END PAGE CONTENT -->
<!-- PAGE LEVEL SCRIPTS -->
<script src="assets/plugins/jquery-2.0.3.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.js"></script>
<script src="assets/js/login.js"></script>
<!--END PAGE LEVEL SCRIPTS -->
</body>
<!-- END BODY -->
</html>
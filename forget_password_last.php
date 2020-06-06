<?php 
	include_once 'common.php';
	$generalobj->go_to_home();
	$action = isset($_GET['action'])?$_GET['action']:'';
?>

<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>" class="ie9"> <![endif]-->
<!--[if !IE]><!--><html lang="en" dir="<?php echo (isset($_SESSION['eDirectionCode']) && $_SESSION['eDirectionCode'] != "")?$_SESSION['eDirectionCode']:'ltr';?>"> <!--<![endif]-->
	<!-- BEGIN HEAD -->
<head>
	<meta charset="UTF-8" />
	<title><?php echo $SITE_NAME?> | Forget Password</title>
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
		<div class="text-center">
		  <a href="index.php">
			<img src="<?php echo $tconfig['tsite_img'];?>/logo.png" id="<?php echo $SITE_NAME?>" alt=" <?php echo $SITE_NAME?>" />
		  </a>
		</div>
		<div class="sign-in-heading" >
			<h3>FORGOT PASSWORD</h3>
		</div>
		<div class="tab-content">
			<div id="login" class="tab-pane active">
				<form action="" class="form-signin" method = "post" id="login_box">
					<input type="hidden" name="action" value="<?echo $action?>"/>
					<p class="text-muted text-center btn-block btn btn-danger btn-rect" style="display:none;" id="errmsg" >
						Enter your username and password
					</p>
					<p class="text-muted text-center btn-block btn btn-danger btn-rect" style="display:none;" id="errmsg1">
						Your Email id is not Registered
					</p>
					<p class="text-muted text-center btn-block btn btn-danger btn-rect" style="display:none;" id="errmsg2">
						Invalid combination of username & Password
					</p>
					<br>
					EMAIL<br>
					<input type="text" placeholder="Email Address" class="form-control" name="vEmail" id="vEmail"/><br>
					<!--<a href="#" onclick="chkEnter();">RESET PASSWORD</a>-->
					<button class="btn text-muted text-center btn-info" type="button" onclick="chkValid('<?php echo $action?>');">RESET PASSWORD</button>
				</form>
			</div>	
			
			<div id="signup" class="tab-pane">
				<form action="index.html" class="form-signin">
					<p class="text-muted text-center btn-block btn btn-primary btn-rect">Please Fill Details To Register</p>
					<input type="text" placeholder="First Name" class="form-control" />
					<input type="text" placeholder="Last Name" class="form-control" />
					<input type="text" placeholder="Username" class="form-control" />
					<input type="email" placeholder="Your E-mail" class="form-control" />
					<input type="password" placeholder="password" class="form-control" />
					<input type="password" placeholder="Re type password" class="form-control" />
					<button class="btn text-muted text-center btn-success" type="submit">Register</button>
				</form>
			</div>
		</div>
		<div class="text-center">
			<ul class="list-inline">
				
				<li><a class="text-muted" href="forget_password.php" data-toggle="tab">Forgot Password</a></li>
				
			</ul>
		</div>
		<hr align="center">
		<div class="signup">
			<h6>Don't have an account?&nbsp;&nbsp;<a href="#">Sign Up</a></h6>
		</div>
		
	</div>
	
	<!--END PAGE CONTENT -->     
	
	<!-- PAGE LEVEL SCRIPTS -->
	<script src="assets/plugins/jquery-2.0.3.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.js"></script>
	<script src="assets/js/login.js"></script>
	<script>
	function chkEnter()
	{
	    alert("here");
	}
		function chkValid(action)
		{
			alert("here");
			return false;
			var id = document.getElementById("vEmail").value;
			if(id == '' || pass == '')
			{
				document.getElementById("errmsg").style.display = '';
				setTimeout(function() {document.getElementById('errmsg').style.display='none';},2000);
			}
			else
			{
				var request = $.ajax({  
					type: "POST",
					url: 'ajax_login_action.php',  
					data: $("#login_box").serialize(), 	   	 	  
					
					success: function(data)
					{ 
						if(data == 1){              
							//showNotification({type : 'error', message: '{/literal}{$smarty.const.LBL_ACC_NOT_ACTIVE}.{literal}'});
							//alert("Not Registered");
							document.getElementById("errmsg1").style.display = '';
							setTimeout(function() {document.getElementById('errmsg1').style.display='none';},2000);
							document.getElementById("vPassword").value = '';
							//window.location = 'login.php';
						}
						else if(data == 3)
						{
					       //alert("Invalid Email Id and Password");
						   document.getElementById("errmsg2").style.display = '';
						   setTimeout(function() {document.getElementById('errmsg2').style.display='none';},2000);
						   document.getElementById("vEmail").value = '';
						   document.getElementById("vPassword").value = '';
						   //window.location = 'login.php';
						}
						else{
							window.location = 'profile.php';                          
						}
					}
				});
				
				request.fail(function(jqXHR, textStatus) {
					alert( "Request failed: " + textStatus ); 
				});
				
			}
		}
		function forgotPass()
		{
			alert("reached");
			var id = document.getElementById("femail").value;
			alert(id);
			
			if(id == '')
			{
				document.getElementById("forgot1").style.display = '';
				setTimeout(function() {document.getElementById('forgot1').style.display='none';},2000);
				//return false;
			}
			var request = $.ajax({  
				type: "POST",
				url: 'fpass_action.php',  
				data: $("#frmforget").serialize(), 	   	 	  
				alert(data);
				success: function(data)
				{  
				alert(data);
				
					/*	  if(data == 1){              
						//showNotification({type : 'error', message: '{/literal}{$smarty.const.LBL_ACC_NOT_ACTIVE}.{literal}'});
						alert("account is not active");
						}
						else{
						alert("Successful Login");
						window.location = 'profile.php';                          
					}*/				
				}
			});
		}
		
	</script>
	<!--END PAGE LEVEL SCRIPTS -->
	
</body>
    <!-- END BODY -->
</html>


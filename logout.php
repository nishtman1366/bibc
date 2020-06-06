<?php
// Mamad H . A . M (Start)
	include_once("common.php");

	if($_SESSION['parent'] != "" && $_SESSION['parent'] != 0)
	{



		$resultdb2 = mysqli_query($condbc,"select * from company where `iCompanyId` = '" . $_SESSION['parent'] . "'");

																								while($rowdb2 = mysqli_fetch_array($resultdb2))
																									{
																									$result2['vName'] = $rowdb2['vName'];
																									$result2['iCompanyId'] = $rowdb2['iCompanyId'];
																									$result2['vEmail'] = $rowdb2['vEmail'];
																								}





			$_SESSION["sess_iCompanyId"] = $result2['iCompanyId'];
			$_SESSION["sess_vName"] = $result2['vName'];
			$_SESSION["sess_vEmail"] = $result2['vEmail'];
			//$_SESSION["sess_user"];
     		$_SESSION['sess_iUserId'] = $_SESSION['parent'];
unset($_SESSION['parent']);
		header("Location:ajans");
		header("Location:operators");
	}
	else
	{
		// Mamad H . A . M (End)
		unset($_SESSION['sess_iUserId']);
		unset($_SESSION["sess_iCompanyId"]);
		unset($_SESSION["sess_vName"]);
		unset($_SESSION["sess_vEmail"]);
		unset($_SESSION["sess_user"]);

		session_destroy();

		header("Location:sign-in.php");
		exit;
	}




?>

<?php 
include_once('../common.php');
if(isset($_POST['vCouponCode']))
{
	$vCouponCode=$_POST['vCouponCode'];

	$checkdata=" SELECT vCouponCode FROM coupon WHERE vCouponCode='$vCouponCode' ";

	$query=mysqli_query($condbc,$checkdata);

	if(mysql_num_rows($query)>0)
	{
	echo "Coupon Code Already Exist";
	}
	else
	{
	echo "OK";
	}
exit();
}
?>
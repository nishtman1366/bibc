<?php

class SavarFactor
{

	function __construct()
	{

	}

	public static function Create($userId, $amount,$usertype='')
	{
		global $obj;

		$userId = intval($userId);
		$amount = floatval($amount);

		if($userId == 0 || $amount == 0){
			return false;
		}

		####################################################################################
		# محاسبه شماره فاکتور جدید
		$sql_maxfactor = "SELECT MAX(`iFactorId`) as maxFactorId FROM `user_factor` WHERE 1 LIMIT 1";

		$dbResult = $obj->MySQLSelect($sql_maxfactor);
		#print_r($dbResult);
		$maxFactorId = intval($dbResult[0]['maxFactorId']);
		$maxFactorId++;

		$factorNumber = $userId .  '00' . $maxFactorId;

		$codeArray = str_split($factorNumber);

		$sum = 0;
		$index = 0;
		foreach($codeArray as $digit)
		{
			$index++;
			$sum += (((int)($digit)) + 1) * $index;
		}

		$factorNumber = $factorNumber * 10 + ($sum % 10);
		####################################################################################

		//96c7c7e429e62390673620306699c362
		$token = md5($factorNumber . md5($userId . $amount) . microtime() . $_SERVER['REQUEST_TIME']);

		$sql = "";
		if ($usertype == 'Driver') {

			$sql = "INSERT INTO `user_factor`(`iDriverId`, `iFactorNumber`, `iAmount`, `iBankName`, `iStatus`, `InsertDate`, `iToken`) VALUES (${userId},${factorNumber},${amount},'DEFAULT','CREATE',NOW(),'${token}')";
		}
		else {

			$sql = "INSERT INTO `user_factor`(`iUserId`, `iFactorNumber`, `iAmount`, `iBankName`, `iStatus`, `InsertDate`, `iToken`) VALUES (${userId},${factorNumber},${amount},'DEFAULT','CREATE',NOW(),'${token}')";
		}

		$data = $obj->MySQLInsert($sql);

		if($data === false)
		{
			return false;
		}

		return $token;
	}

	public static function GetByToken($token)
	{
		global $obj;

		$sql = "SELECT * FROM `user_factor` WHERE `iToken` like '${token}' LIMIT 1";

		$dbResult = $obj->MySQLSelect($sql);

		if(count($dbResult) > 0 )
			return $dbResult[0];
	}


	public static function ChangeStatus($fIdOrToken, $status)
	{
		global $obj;

		$status = substr($status,0,20);

		$fieldName = "iFactorId";
		if(strlen($fIdOrToken) == 32)
		{
			$fieldName = 'iToken';
			$fIdOrToken = "'${fIdOrToken}'";
		}

		$query = "UPDATE `user_factor` SET `iStatus`='${status}' WHERE `${fieldName}` = ${fIdOrToken} LIMIT 1";
		$obj->sql_query($query);
	}
}

?>

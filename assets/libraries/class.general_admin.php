<?php

class General_admin
{

    public function __construct()
    {
    }


    public function getCompanyDetails()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT * FROM company WHERE eStatus != 'Deleted' $cmp_ssql order by tRegistrationDate desc";
        $data = $obj->MySQLSelect($sql);
        return $data;
    }

    public function getDriverDetails($status = "")
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $ssl = "";
        if ($status != "" && $status == "active") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        } else if ($status != "" && $status == "inactive") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        }
        $sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getVehicleDetails()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT dv.*, m.vMake, md.vTitle,rd.vEmail, rd.vName, rd.vLastName, c.vName as companyFirstName, c.vLastName as companyLastName
				FROM driver_vehicle dv, register_driver rd, make m, model md, company c
				WHERE
				  dv.eStatus != 'Deleted'
				  AND dv.iDriverId = rd.iDriverId
				  AND dv.iCompanyId = c.iCompanyId
				  AND dv.iModelId = md.iModelId
				  AND dv.iMakeId = m.iMakeId" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getRiderDetails()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT * FROM register_user WHERE eStatus != 'Deleted'" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    public function getTripsDetails()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT * FROM trips WHERE 1=1" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data;
    }

    /* check admin is login or not */
    function check_member_login()
    {

        global $tconfig;
        $sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
        $sess_iGroupId = isset($_SESSION['sess_iGroupId']) ? $_SESSION['sess_iGroupId'] : '';
        if ($sess_iAdminUserId == "" && basename($_SERVER['PHP_SELF']) != "index.php") {
            header("Location:" . $tconfig["tsite_admin_url"] . "index.php");
        }
        //If GroupId == 2
        //echo basename($_SERVER['PHP_SELF']); die;
        if ($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
            header("Location:" . $tconfig["tsite_admin_url"] . "add_booking.php");
        } else if ($sess_iGroupId == '2' && basename($_SERVER['PHP_SELF']) != "cab_booking.php" && basename($_SERVER['PHP_SELF']) != "add_booking.php" && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "map.php" && basename($_SERVER['PHP_SELF']) != "invoice.php") {
            header("Location:" . $tconfig["tsite_admin_url"] . "add_booking.php");
        }
        //If GroupId == 3
        if ($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) == "dashboard.php") {
            header("Location:" . $tconfig["tsite_admin_url"] . "trip.php");
        } else if ($sess_iGroupId == '3' && basename($_SERVER['PHP_SELF']) != "trip.php" && basename($_SERVER['PHP_SELF']) != "referrer.php" && strpos(basename($_SERVER['PHP_SELF']), 'report') == false && basename($_SERVER['PHP_SELF']) != "admin_action.php" && basename($_SERVER['PHP_SELF']) != "invoice.php" && basename($_SERVER['PHP_SELF']) != "referrer_action.php" && basename($_SERVER['PHP_SELF']) != "export_driver_details.php") {
            header("Location:" . $tconfig["tsite_admin_url"] . "trip.php");
        }
    }

    /* if user is at login page */
    function go_to_home()
    {
        global $tconfig;

        $sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';

        $sess_user = isset($_SESSION['sess_user']) ? $_SESSION['sess_user'] : '';
        $url = "";
        if ($sess_iAdminUserId != "") {
            switch ($sess_user) {
                case 'driver':
                    $url = "profile.php";
                    break;
                case 'rider':
                    $url = "profile_rider.php";
                    break;
                case 'admin':
                    $url = "dashboard.php";
                    break;
                default:
                    $url = "dashboard.php";
                    break;
            }
        }


        if ($url != '' && basename($_SERVER['PHP_SELF']) != $url) {
            // if user is at same page
            echo '<script>window.location="' . $url . '";</script>';
            @header("Location:" . $url);
            exit;
        }
    }

    function getPostForm($POST_Arr, $msg = "", $action = "")
    {
        $str = '
			<html>
			<form name="frm1" action="' . $action . '" method=post>';
        foreach ($POST_Arr as $key => $value) {
            if ($key != "mode") {
                if (is_array($value)) {
                    foreach ($value as $kk => $vv)
                        $str .= '<br><input type="Hidden" name="Data[' . $kk . ']" value="' . stripslashes($vv) . '">';
                    $str .= '<br><input type="Hidden" name="' . $key . '[]" value="' . stripslashes($value[$i]) . '">';
                } else {
                    $str .= '<br><input type="Hidden" name="' . $key . '" value="' . stripslashes($value) . '">';
                }
            }
        }
        $str .= '<input type="Hidden" name=var_msg value="' . $msg . '">
			</form>
			<script>
			document.frm1.submit();
			</script>
			</html>';

        echo $str;
        exit;
    }

    function clearEmail($email)
    {
        if (SITE_TYPE == "Demo") {
            $mail = explode('.', $email);
            $output = substr($mail[0], 0, 2);
            return $output . '*****.' . $mail[count($mail) - 1];
        } else {
            return $email;
        }
    }

    function clearPhone($text)
    {
        if (SITE_TYPE == "Demo") {
            return substr_replace($text, "*****", 0, -2);
        } else {
            return $text;
        }
    }

    function remove_unwanted($day = 7)
    {


        global $tconfig, $obj;
        $later_date = date('Y-m-d H:i:s', strtotime("-" . $day . " day", strtotime(date('Y-m-d H:i:s'))));

        /***************** Delete Driver ***************************/

        $sql = "SELECT *
			FROM register_driver
			WHERE tRegistrationDate < '" . $later_date . "'";
        $data = $obj->MySQLSelect($sql);

        if (count($data) > 0) {
            $common_member = "SELECT iDriverId
				FROM register_driver
				WHERE tRegistrationDate < '" . $later_date . "'";

            $sql = "DELETE FROM driver_vehicle WHERE iDriverId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM trips WHERE iDriverId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM log_file WHERE iDriverId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM register_driver WHERE tRegistrationDate < '" . $later_date . "'";
            $db_sql = $obj->sql_query($sql);

        }

        /**********************************************Delete Rider ********************************************/

        $sql = "SELECT *
			FROM register_user
			WHERE tRegistrationDate < '" . $later_date . "'";
        $data_user = $obj->MySQLSelect($sql);
        if (count($data_user) > 0) {
            $common_member = "SELECT iUserId
				FROM register_user
				WHERE tRegistrationDate < '" . $later_date . "'";

            $sql = "DELETE FROM trips WHERE iUserId IN (" . $common_member . ")";
            $db_sql = $obj->sql_query($sql);

            $sql = "DELETE FROM register_user WHERE tRegistrationDate < '" . $later_date . "'";
            $db_sql = $obj->sql_query($sql);

        }


    }

    public function getTripStates($tripStatus = NULL)
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tStartDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($tripStatus != "") {
            if ($tripStatus == "on ride") {
                $ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
            } else if ($tripStatus == "cancelled") {
                $ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
            } else if ($tripStatus == "finished") {
                $ssl = " AND iActive = 'Finished' AND eCancelled='No'";
            } else {
                $ssl = "";
            }

            $sql = "SELECT * FROM trips WHERE 1" . $cmp_ssql . $ssl;
            $data = $obj->MySQLSelect($sql);
        }
        return $data;
    }

    public function getTotalEarns()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT SUM( `fCommision` ) AS total FROM trips WHERE 1" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        $result = $data[0]['total'];
        return $result;
    }

    public function getTripDateStates($time)
    {
        global $obj;
        $data = array();
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }
        $sql = "SELECT * FROM trips WHERE 1 " . $ssl . $cmp_ssql;

        //if($time == "year") die($sql);
        $data = $obj->MySQLSelect($sql);
        return $data;
    }

    public function getDriverDateStatus($time)
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }
        $sql = "SELECT rd.*, c.vCompany companyFirstName, c.vLastName companyLastName FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data;
    }

    public function getAllCashCountbyDriverId($id, $ssql)
    {
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(iFare) as totalAmount FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Cash' AND iDriverId = '" . $id . "'" . $ssql;

            // edit by seyyed amir
            $sql = "SELECT SUM(iFare) as totalAmount FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND iDriverId = '" . $id . "'" . $ssql;
            //////
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllCardCountbyDriverId($id, $ssql)
    {
        $total = '0.00';
        if ($id != "") {
            global $obj;
            $sql = "SELECT SUM(iFare) as totalAmount FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Card' AND iDriverId = '" . $id . "'" . $ssql;
            // edit by seyyed amir
            $sql = "SELECT SUM(fWalletDebit) as totalAmount FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND  iDriverId = '" . $id . "'" . $ssql;
            ////
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }

    public function getAllDiscountbyDriverId($id, $ssql)
    {
        $total = '0.00';
        if ($id != "") {
            global $obj;

            // edit by seyyed amir
            $sql = "SELECT SUM(fDiscount) as totalAmount FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND  iDriverId = '" . $id . "'" . $ssql;
            ////
            $data = $obj->MySQLSelect($sql);
            $total = ($data[0]['totalAmount'] != "") ? $data[0]['totalAmount'] : '0.00';
        }
        return number_format($total, 2);
    }


    // DISABLED by seyyed amir
    /*public function getTransforAmountbyDriverId ($id,$ssql)
    {
        $total = '0.00';
        if($id != "") {
            global $obj;
            //get Cash commision
            $sql = "SELECT SUM(fCommision) AS totalAmount FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Cash' AND iDriverId = '".$id."'".$ssql;
            $data = $obj->MySQLSelect($sql);
            $cashCommision = ($data[0]['totalAmount'] != "")?$data[0]['totalAmount']:'0.00';

            //get Card total with deduct commision
            $sql = "SELECT IFNULL( SUM( IFNULL( iFare, 0 ) ) , 0 ) - IFNULL( SUM( IFNULL( fCommision, 0 ) ) , 0 ) AS amounts FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND vTripPaymentMode='Card' AND iDriverId = '".$id."'".$ssql;
            $data = $obj->MySQLSelect($sql);
            $cardTotal = ($data[0]['amounts'] != "")?$data[0]['amounts']:'0.00';

            $total = number_format($cardTotal-$cashCommision,2);
        }
        return $total;
    }
    */


    public function getTransforAmountbyDriverId($id, $ssql)
    {
        $total = '0';
        if ($id != "") {
            global $obj;

            /*

            $sql = "SELECT SUM(fCommision) AS totalCommision FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND iDriverId = '".$id."'".$ssql;


            $data = $obj->MySQLSelect($sql);
            $totalCommision = ($data[0]['totalCommision'] != "")?$data[0]['totalCommision']:'0.00';
            */
            $sql = "SELECT IFNULL( SUM( IFNULL( fWalletDebit, 0 ) ) , 0 ) + IFNULL( SUM( IFNULL( fDiscount, 0 ) ) , 0 ) - IFNULL( SUM( IFNULL( fCommision, 0 ) ) , 0 ) AS amounts FROM trips WHERE eDriverPaymentStatus = 'Unsettelled' AND iDriverId = '" . $id . "'" . $ssql;
            $data = $obj->MySQLSelect($sql);
            $cardTotal = ($data[0]['amounts'] != "") ? $data[0]['amounts'] : '0';

            $total = $cardTotal;
        }
        return $total;
    }

    public function getCompanyDetailsDashboard()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(iCompanyId) as Total FROM company WHERE eStatus != 'Deleted' $cmp_ssql order by tRegistrationDate desc";
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function getDriverDetailsDashboard($status)
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $ssl = "";
        if ($status != "" && $status == "active") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        } else if ($status != "" && $status == "inactive") {
            $ssl = " AND rd.eStatus = '" . $status . "'";
        }
        $sql = "SELECT count(rd.iDriverId) as Total FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getVehicleDetailsDashboard()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(dv.iDriverVehicleId) as Total
				FROM driver_vehicle dv, register_driver rd, make m, model md, company c
				WHERE
				  dv.eStatus != 'Deleted'
				  AND dv.iDriverId = rd.iDriverId
				  AND dv.iCompanyId = c.iCompanyId
				  AND dv.iModelId = md.iModelId
				  AND dv.iMakeId = m.iMakeId" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getRiderDetailsDashboard()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(iUserId) as Total FROM register_user WHERE eStatus != 'Deleted'" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getTripsDetailsDashboard()
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $sql = "SELECT count(iTripId) as Total FROM trips WHERE 1=1" . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);

        return $data[0]['Total'];
    }

    public function getTripStatesDashboard($tripStatus = NULL)
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tStartDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($tripStatus != "") {
            if ($tripStatus == "on ride") {
                $ssl = " AND (iActive = 'On Going Trip' OR iActive = 'Active') AND eCancelled='No'";
            } else if ($tripStatus == "cancelled") {
                $ssl = " AND (iActive = 'Canceled' OR eCancelled='yes')";
            } else if ($tripStatus == "finished") {
                $ssl = " AND iActive = 'Finished' AND eCancelled='No'";
            } else {
                $ssl = "";
            }

            $sql = "SELECT count(iTripId) as Total FROM trips WHERE 1" . $cmp_ssql . $ssl;
            $data = $obj->MySQLSelect($sql);
        }
        return $data[0]['Total'];
    }

    public function getTripDateStatesDashboard($time)
    {
        global $obj;
        $data = array();
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And tEndDate > '" . WEEK_DATE . "'";
        }
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND tTripRequestDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }
        $sql = "SELECT count(iTripId) as Total FROM trips WHERE 1 " . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function getDriverDateStatusDashboard($time)
    {
        $cmp_ssql = "";
        if (SITE_TYPE == 'Demo') {
            $cmp_ssql = " And rd.tRegistrationDate > '" . WEEK_DATE . "'";
        }
        global $obj;
        $data = array();
        if ($time == "month") {
            $startDate = date('Y-m') . "-00 00:00:00";
            $endDate = date('Y-m') . "-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate . "' AND '" . $endDate . "'";
        } else if ($time == "year") {
            $startDate1 = date('Y') . "-00-00 00:00:00";
            $endDate1 = date('Y') . "-12-31 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate1 . "' AND '" . $endDate1 . "'";
        } else {
            $startDate2 = date('Y-m-d') . " 00:00:00";
            $endDate2 = date('Y-m-d') . " 23:59:59";
            $ssl = " AND rd.tRegistrationDate BETWEEN '" . $startDate2 . "' AND '" . $endDate2 . "'";
        }
        $sql = "SELECT count(rd.iDriverId) as Total FROM register_driver rd LEFT JOIN company c ON rd.iCompanyId = c.iCompanyId and c.eStatus != 'Deleted' WHERE  rd.eStatus != 'Deleted'" . $ssl . $cmp_ssql;
        $data = $obj->MySQLSelect($sql);
        return $data[0]['Total'];
    }

    public function set_hour_min($times)
    {
        $hour = 0;
        $second = 0;
        $minute = floor($times / 60);
        if ($times < 60) {
            $minute = 0;
        }
        if ($minute > 60) {
            $hour = floor($minute / 60);
            $minute = floor($minute % 60);
        } else {
            $second = floor($times % 60);
        }
        $ansdata = array("hour" => $hour, "minute" => $minute, "second" => $second);

        return $ansdata;
    }
}

?>

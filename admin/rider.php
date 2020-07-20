<?php
include_once('../common.php');
require_once(TPATH_CLASS . 'savar/jalali_date.php');
include_once('savar_check_permission.php');
if (checkPermission('RIDER') == false)
    die('you dont`t have permission...');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$iUserId = isset($_REQUEST['iUserId']) ? $_REQUEST['iUserId'] : '';
$res_id = isset($_REQUEST['res_id']) ? $_REQUEST['res_id'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$script = "Rider";

if ($iUserId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE register_user SET eStatus = '" . $status . "' WHERE iUserId = '" . $iUserId . "'";
        $obj->sql_query($query);
        $success = "1";
        $succe_msg = "Rider " . $status . " successfully.";
        header("Location:rider.php?action=view&success=1&succe_msg=" . $succe_msg);
    } else {
        header("Location:rider.php?success=2");
        exit;
    }
}

$sql = "select * from country";
$db_country = $obj->MySQLSelect($sql);

$sql = "select * from language_master where eStatus = 'Active'";
$db_lang = $obj->MySQLSelect($sql);
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
if ($action == 'delete' && $hdn_del_id != '') {
    //$query = "DELETE FROM `".$tbl_name."` WHERE iUserId = '".$id."'";
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE register_user SET eStatus = 'Deleted' WHERE iUserId = '" . $hdn_del_id . "'";
        $obj->sql_query($query);
        $action = "view";
        $success = "1";
        $succe_msg = "Rider deleted successfully.";
        header("Location:rider.php?action=view&success=1&succe_msg=" . $succe_msg);
        exit;
    } else {
        header("Location:rider.php?success=2");
        exit;
    }
}

if ($action == 'reset' && $res_id != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE register_user SET iTripId='0',vTripStatus='NONE',vCallFromDriver=' ' WHERE iUserId = '" . $res_id . "'";
        $obj->sql_query($query);
        $action = "view";
        $success = "1";
        $succe_msg = "Rider status reseted successfully.";
        header("Location:rider.php?action=view&success=1&succe_msg=" . $succe_msg);
        exit;
    } else {
        header("Location:rider.php?success=2");
        exit;
    }
}
/* $vName = isset($_POST['vName'])?$_POST['vName']:'';
    $vLname = isset($_POST['vLname'])?$_POST['vLname']:'';
    $vEmail = isset($_POST['vEmail'])?$_POST['vEmail']:'';
    $vPassword = isset($_POST['vPassword'])?$_POST['vPassword']:'';
    $vPhone = isset($_POST['vPhone'])?$_POST['vPhone']:'';
    $vCode = isset($_POST['vCode'])?$_POST['vCode']:'';
    $vCountry = isset($_POST['vCountry'])?$_POST['vCountry']:'';
    $vLang = isset($_POST['vLang'])?$_POST['vLang']:'';
    $vPass = $generalobj->encrypt($vPassword);
    $tbl_name = "register_user";

    if(isset($_POST['submit'])) {

    $q = "INSERT INTO ";
    $where = '';

    if($id != '' ){
    $q = "UPDATE ";
    $where = " WHERE `iUserId` = '".$id."'";
    }


    $query = $q ." `".$tbl_name."` SET
    `vName` = '".$vName."',
    `vLastName` = '".$vLname."',
    `vCountry` = '".$vCountry."',
    `vCode` = '".$vCode."',
    `vEmail` = '".$vEmail."',
    `vLoginId` = '".$vEmail."',
    `vPassword` = '".$vPass."',
    `vPhone` = '".$vPhone."',
    `vLang` = '".$vLang."',
    `iCompanyId` = '".$iCompanyId."'"
    .$where;

    $obj->sql_query($query);
    $id = ($id != '')?$id:mysql_insert_id();
    header("Location:rider.php?id=".$id.'&success=1');

} */

$cmp_ssql = "";
if (SITE_TYPE == 'Demo') {
    $cmp_ssql = " And tRegistrationDate > '" . WEEK_DATE . "'";
}

if ($action == 'view') {
    if ($_GET['pagerecord'] == '') {
        $sql = "SELECT * FROM register_user WHERE 1=1" . $cmp_ssql . ' AND eStatus != "Deleted" order by iUserId DESC limit 0,1000';
    } else {
        $sql = "SELECT * FROM register_user WHERE 1=1" . $cmp_ssql . ' AND eStatus != "Deleted" order by iUserId DESC limit ' . $_GET['pagerecord'] * 1000 . ',1000';
    }
    $data_drv = $obj->MySQLSelect($sql);


    $sql4 = "SELECT iUserId FROM register_user WHERE 1=1" . $cmp_ssql . ' AND eStatus != "Deleted" order by iUserId DESC ';

    $data_drv_recordcount = $obj->MySQLSelect($sql4);
}


if (isset($_REQUEST["Excel"])) {
    $db_name = "k68ir_DB"; //This is your database Name
//	$link = mysqli_connect("localhost", "savari", '$SAVAR%B$$A@*&H!JH%', $db_name) or die("Could not connect to server!");
    $conn2 = new mysqli('localhost', 'k68ir_DB', 'Kamelia.irir*****', $db_name);
    $conn2->set_charset("utf8");

    $setSql = "SELECT `iUserId`, `vName`, `vLastName`,`tRegistrationDate`, `vPhone` FROM `register_user` WHERE 1";
    $setRec = mysqli_query($conn2, $setSql);

    $columnHeader = '';
    $columnHeader = "Sr NO" . "\t" . "نام مسافر" . "\t" . "فامیلی مسافر" . "\t" . "تاریخ ثبت نام" . "\t" . "شماره موبایل" . "\t";

    $setData = '';

    while ($rec = mysqli_fetch_row($setRec)) {
        $rowData = '';
        foreach ($rec as $value) {

            $value = '"' . $value . '"' . "\t";
            $rowData .= $value;


        }
        $ccc = 0;
        $ccc2 = 0;
        $setData .= trim($rowData) . "\n";
    }


    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=rider.xls");
    header('Content-Transfer-Encoding: binary');
    header("Pragma: no-cache");
    header("Expires: 0");

    echo chr(255) . chr(254) . iconv("UTF-8", "UTF-16LE//IGNORE", $columnHeader . "\n" . $setData . "\n");

    exit();
}
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>ادمین | <?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?> </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>

    <?php include_once('global_files.php'); ?>
    <!-- <script>
        $(document).ready(function(){
        $("#show-add-form").click(function(){
        $("#show-add-form").hide(1000);
        $("#add-hide-div").show(1000);
        $("#cancel-add-form").show(1000);
        });

        });
        </script>
        <script>
        $(document).ready(function(){
        $("#cancel-add-form").click(function(){
        $("#cancel-add-form").hide(1000);
        $("#show-add-form").show(1000);
        $("#add-hide-div").hide(1000);
        });

        });

    </script>	-->

    <!--    jquery-autocomplete-master-->
    <link rel="stylesheet" href="../assets/css/amir.autocomplete.css"/>


</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53 ">

<!-- MAIN WRAPPER -->
<div id="wrap">
    <?php include_once('header2.php'); ?>
    <?php include_once('left_menu.php'); ?>

    <!--PAGE CONTENT -->
    <div id="content">
        <div class="inner">
            <div id="add-hide-show-div">
                <div class="row">
                    <div class="col-lg-12">
                        <h2>سوار / پیک</h2>
                        <a href="rider_action.php"><input type="button" id="show-add-form" value="افزودن سوار"
                                                          class="add-btn"></a>
                        <a class="add-btn" href="?Excel" style="text-align: center;">خروجی اکسل</a>
                        <input type="button" id="cancel-add-form" value="CANCEL" class="cancel-btn">
                    </div>
                </div>
                <hr/>
            </div>
            <?php if ($success == 1) { ?>
                <div class="alert alert-success alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    <?php echo isset($_REQUEST['succe_msg']) ? $_REQUEST['succe_msg'] : ''; ?>
                </div><br/>
            <?php } elseif ($success == 2) { ?>
                <div class="alert alert-danger alert-dismissable">
                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                    "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be
                    enabled on the main script we will provide you.
                </div><br/>
            <?php } ?>
            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading driver-neww1">
                                <b>جست و جو سوار</b>
                            </div>
                            <div class="panel-body">
                                <input class="form-control" type="text" id="searchPassenger" name="searchPassenger"
                                       style="width:18%;display:table-row-group;" placeholder="Passenger"
                                       value="<?php echo $searchPassenger ?>">

                                <input type="hidden" id="iUserId" name="iUserId" autocomplete="off"
                                       value="<?php echo $iUserId ?>">
                                <button type="button" id="btnShowRider" class="btn btn-default btn-sm">نمایش</button>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            </br>
            <div style="margin: 13px;margin-top: -6px;">
                تعداد رکورد در هر لیست<br>
                <select class="form-control" name='iCompanyId' id='iCompanyId' onchange="location = this.value;">
                    <!--<option value="map.php">--select--</option>-->
                    <?php $listrecord1 = 1;
                    $listrecord2 = count($data_drv_recordcount) / 1000;
                    for ($i = 0; $i < count($data_drv_recordcount) / 1000; $i++) {

                        if ($_GET['pagerecord'] == $i) {
                            echo '<option selected value ="rider.php?pagerecord=' . $i . '">' . $listrecord1 . ' to ' . $listrecord1 += 1000 . " </option>";
                        } else {
                            echo '<option value ="rider.php?pagerecord=' . $i . '">' . $listrecord1 . ' to ' . $listrecord1 += 1000 . " </option>";
                        }
                        //$listrecord1 += 1000;
                    } ?>
                </select>
            </div>


            <div class="table-list">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="panel panel-default">
                            <div class="panel-heading driver-neww1">
                                <b><?php echo $langage_lbl_admin['LBL_RIDER_NAME_TXT_ADMIN']; ?></b>
                                <div class="button-group driver-neww">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown"><span class="">Select Option</span> <span
                                                class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#" class="small" data-value="Active" tabIndex="-1"><input
                                                        type="checkbox" id="checkbox"
                                                        checked="checked"/>&nbsp;Active</a></li>
                                        <li><a href="#" class="small" data-value="Inactive" tabIndex="-1"><input
                                                        type="checkbox" id="checkbox"/>&nbsp;Inactive</a></li>

                                    </ul>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive" id="data_drv001">
                                    <table class="table table-striped table-bordered table-hover admin-td-button"
                                           id="dataTables-example">
                                        <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>ایمیل</th>
                                            <th>تاریخ ثبت نام</th>
                                            <th>موبایل</th>
                                            <th>وضعیت</th>
                                            <th align="center" style="text-align:center;">فعالیت</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        for ($i = 0; $i < count($data_drv); $i++) { ?>
                                            <tr class="gradeA">
                                                <td><?php echo $data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']; ?></td>
                                                <td><?php echo $generalobjAdmin->clearEmail($data_drv[$i]['vEmail']); ?></td>
                                                <td data-order="<?php echo $data_drv[$i]['iUserId']; ?>"><?php echo jdate('d-F-Y', strtotime($data_drv[$i]['tRegistrationDate'])); ?></td>
                                                <td class="center"><?php echo $generalobjAdmin->clearPhone($data_drv[$i]['vPhone']); ?></td>
                                                <td width="10%" align="center">
                                                    <?php if ($data_drv[$i]['eStatus'] == 'Active') {
                                                        $dis_img = "img/active-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Inactive') {
                                                        $dis_img = "img/inactive-icon.png";
                                                    } else if ($data_drv[$i]['eStatus'] == 'Deleted') {
                                                        $dis_img = "img/delete-icon.png";
                                                    } ?>
                                                    <img src="<?php echo $dis_img; ?>"
                                                         alt="<?php echo $data_drv[$i]['eStatus'] ?>">
                                                </td>
                                                <td class="veh_act" align="center" style="text-align:center;">
                                                    <?php if ($data_drv[$i]['eStatus'] != "Deleted") { ?>
                                                        <a href="rider_action.php?id=<?php echo $data_drv[$i]['iUserId']; ?>"
                                                           data-toggle="tooltip" title="Edit Rider">
                                                            <img src="img/edit-icon.png" alt="Edit">
                                                        </a>
                                                    <?php } ?>

                                                    <a href="rider.php?iUserId=<?php echo $data_drv[$i]['iUserId']; ?>&status=Active"
                                                       data-toggle="tooltip" title="Active Rider">
                                                        <img src="img/active-icon.png"
                                                             alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                    </a>
                                                    <a href="rider.php?iUserId=<?php echo $data_drv[$i]['iUserId']; ?>&status=Inactive"
                                                       data-toggle="tooltip" title="Inactive Rider">
                                                        <img src="img/inactive-icon.png"
                                                             alt="<?php echo $data_drv[$i]['eStatus']; ?>">
                                                    </a>
                                                    <?php if ($data_drv[$i]['eStatus'] != "Deleted") { ?>
                                                        <form name="delete_form" id="delete_form" method="post"
                                                              action="" onSubmit="return confirm_delete()"
                                                              class="margin0">
                                                            <input type="hidden" name="hdn_del_id" id="hdn_del_id"
                                                                   value="<?php echo $data_drv[$i]['iUserId']; ?>">
                                                            <input type="hidden" name="action" id="action"
                                                                   value="delete">
                                                            <button class="remove_btn001" data-toggle="tooltip"
                                                                    title="Delete Rider">
                                                                <img src="img/delete-icon.png" alt="Delete">
                                                            </button>
                                                        </form>

                                                        <form name="reset_form" id="reset_form" method="post" action=""
                                                              onSubmit="return confirm('Are you sure?you want to reset <?php echo $data_drv[$i]['vName'] . ' ' . $data_drv[$i]['vLastName']; ?> account?')"
                                                              class="margin0">
                                                            <input type="hidden" name="res_id" id="res_id"
                                                                   value="<?php echo $data_drv[$i]['iUserId']; ?>">
                                                            <input type="hidden" name="action" id="action"
                                                                   value="reset">
                                                            <button class="remove_btn001" data-toggle="tooltip"
                                                                    title="Reset Rider">
                                                                <img src="img/reset-icon.png" alt="Reset">
                                                            </button>
                                                        </form>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>

                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div> <!--TABLE-END-->
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
    <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->


<?php include_once('footer.php'); ?>
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="../assets/js/amir.autocomplete.js"></script>

<script>
    var options = ["Active"];

    $('.dropdown-menu a').on('click', function (event) {
        //alert(options);
        var $target = $(event.currentTarget),
            val = $target.attr('data-value'),
            $inp = $target.find('input'),
            idx;

        if ((idx = options.indexOf(val)) > -1) {

            options.splice(idx, 1);
            setTimeout(function () {
                $inp.prop('checked', false)
            }, 0);
        } else {
            options.push(val);
            setTimeout(function () {
                $inp.prop('checked', true)
            }, 0);
        }
        //alert(options);
        $(event.target).blur();

        //console.log( options );
        //alert(options);
        var request = $.ajax({
            type: "POST",
            url: 'change_rider_list.php',
            data: {result: JSON.stringify(options)},
            success: function (data) {
                $("#data_drv001").html(data);
                //document.getElementById("code").value = data;
                //window.location = 'profile.php';
            }
        });
        return false;
    });

    $(document).ready(function () {
        $('#dataTables-example').dataTable({
            "order": [[2, "desc"]]
        });

        $("#btnShowRider").click(function () {
            if ($("#iUserId").val() != '') {
//                        $('<a href="rider_action.php?id='+$("#iUserId").val()+'" target="_blank" title="Edit Rider">').appendTo("body").click().remove();

                window.open('rider_action.php?id=' + $("#iUserId").val());

                return false;
            }

        });
    });

    function confirm_delete() {
        var confirm_ans = confirm("Are You sure You want to Delete this Rider?");
        return confirm_ans;
        //document.getElementById(id).submit();
    }

    function changeCode(id) {
        var request = $.ajax({
            type: "POST",
            url: 'change_code.php',
            data: 'id=' + id,
            success: function (data) {
                document.getElementById("code").value = data;
                //window.location = 'profile.php';
            }
        });
    }
</script>
</body>
<!-- END BODY-->
</html>

<?php
include_once('../common.php');

$script = "FeeSettings";
if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$tbl_name = "SnapSettings";
$sql = "SELECT * FROM SnapSettings WHERE 1 ORDER BY `SnapSettings`.`id` ASC";
$db_data1 = $obj->MySQLSelect($sql);

$returnRate = $db_data1[0]['setting_value'];
$secRate = $db_data1[15]['setting_value'];
$delay1 = $db_data1[1]['setting_value'];
$delay2 = $db_data1[2]['setting_value'];
$delay3 = $db_data1[3]['setting_value'];
$delay4 = $db_data1[4]['setting_value'];
$delay5 = $db_data1[5]['setting_value'];
$delay6 = $db_data1[6]['setting_value'];
$delay7 = $db_data1[7]['setting_value'];
$delay8 = $db_data1[8]['setting_value'];
$delay9 = $db_data1[9]['setting_value'];
$delay10 = $db_data1[10]['setting_value'];
$delay11 = $db_data1[11]['setting_value'];
$delay12 = $db_data1[12]['setting_value'];
$delay13 = $db_data1[13]['setting_value'];
$delay14 = $db_data1[14]['setting_value'];

if (isset($_POST['submit'])) {

  $returnRate = isset($_REQUEST['returnRate']) ? $_REQUEST['returnRate'] : '1';
  $secRate = isset($_REQUEST['secRate']) ? $_REQUEST['secRate'] : '1';

  $where = " id = 1";
  $Data_update1['setting_value'] = $returnRate;
  $obj->MySQLQueryPerform("SnapSettings",$Data_update1,'update',$where);

  $where = " id = 16";
  $Data_update2['setting_value'] = $secRate;
  $obj->MySQLQueryPerform("SnapSettings",$Data_update2,'update',$where);

  for ($i=1; $i < 15; $i++) {

    $index = $i + 1;
    $where = " id = '$index'";
    $Data_update = [];
    $Data_update['setting_value'] = isset($_REQUEST['delay'.$i]) ? $_REQUEST['delay'.$i] : '1000';
    $obj->MySQLQueryPerform("SnapSettings",$Data_update,'update',$where);
  }
}

?>
<!DOCTYPE html>
<head>
<meta charset="UTF-8" />
<title>ادمین | تنظیمات نرخ
<?php echo  $action; ?>
</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<link href="assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
<?
include_once('global_files.php');
?>
<!-- On OFF switch -->
<link href="../assets/css/jquery-ui.css" rel="stylesheet" />
<link rel="stylesheet" href="../assets/plugins/switch/static/stylesheets/bootstrap-switch.css" />
</head>
<!-- END  HEAD-->
<!-- BEGIN BODY-->
<body class="padTop53">
<!-- MAIN WRAPPER -->
<div id="wrap">
  <?
    include_once('header.php');
    include_once('left_menu.php');
    ?>
  <!--PAGE CONTENT -->
  <div id="content">
    <div class="inner">
      <div class="row">
        <div class="col-lg-12">
          <h2>
            تنظمیات نرخ
          </h2>
         </div>
      </div>
      <hr />
      <div class="body-div">
        <div class="form-group"> <span style="color: red;font-size: small;" id="coupon_status"></span>
          <?php if ($success == 1) {?>
          <div class="alert alert-success alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
            با موفقیت ذخیره شد </div>
          <br/>
          <?} ?>
          <?php if ($success == 2) {?>
          <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
            "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you. </div>
          <br/>
          <?} ?>
          <?php if ($success == 3) {?>
          <div class="alert alert-danger alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
            مشکلی پیش آمده </div>
          <br/>
          <?} ?>
          <form method="post" action="" enctype="multipart/form-data" class="">

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">ضریب رفت و برگشت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="returnRate"  id="returnRate" value="<?php echo $returnRate; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">ضریب مقصد دوم</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="secRate"  id="secRate" value="<?php echo $secRate; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر۰  تا ۵ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay1"  id="delay1" value="<?php echo $delay1; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۵ تا ۱۰ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay2"  id="delay2" value="<?php echo $delay2; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۱۰ تا ۱۵ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay3"  id="delay3" value="<?php echo $delay3; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۱۵ تا ۲۰ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay4"  id="delay4" value="<?php echo $delay4; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۲۰ تا ۲۵ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay5"  id="delay5" value="<?php echo $delay5; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۲۵ تا ۳۰ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay6"  id="delay6" value="<?php echo $delay6; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۳۰ تا ۴۵ دقیقه</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay1"  id="delay7" value="<?php echo $delay7; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۴۵ دقیقه تا ۱ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay1"  id="delay8" value="<?php echo $delay8; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۱ تا ۱.۵ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay9"  id="delay9" value="<?php echo $delay9; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۱.۵ تا ۲ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay10"  id="delay10" value="<?php echo $delay10; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۲ تا ۲.۵ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay111"  id="delay11" value="<?php echo $delay11; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۲.۵ تا ۳ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay12"  id="delay12" value="<?php echo $delay12; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۳ تا ۳.۵ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay13"  id="delay13" value="<?php echo $delay13; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label><span class="green">توقف در مسیر ۳.۵ تا ۴ ساعت</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="delay14"  id="delay14" value="<?php echo $delay14; ?>" placeholder="مقدار" required>
                </div>
            </div>

            <div class="row coupon-action-n4">
              <div class="col-lg-12">
                <input type="submit" class="save btn-info" name="submit" id="submit" value="ذخیره"  >
              </div>
            </div>
          </form>
        </div>
        <div class="clear"></div>
      </div>
    </div>
  </div>
  <!--END PAGE CONTENT -->
</div>
<!--END MAIN WRAPPER -->
<?php include_once('footer.php');?>
<script src="../assets/plugins/switch/static/js/bootstrap-switch.min.js"></script>
<script>
          function coupon(dis){
            var bla = $('#fDiscount').val();
            if(dis == 'percentage')
            {
              if(bla > 100)
              {
                alert("Please Enter 1 to 100 Discount");
              }
            }
          }
  			function validate_coupon(username)
	        {
                 	var request = $.ajax({
	                type: "POST",
	                url: 'ajax_validate_coupon.php',
	                data: 'vCouponCode=' +username,
	                success: function (data)
	                {
	                    if(data==0)
	                    {
	                      $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> 	Coupon Code Already Exist</i>');
	                     $('input[type="submit"]').attr('disabled','disabled');

	                     return false;
	                    }
	                    else if(data==1)
	                    {
	                        $('#coupon_status').html('<i class="icon icon-ok alert-success alert"> Valid</i>');
	                        $('vCouponCode[type="submit"]').removeAttr('disabled');
	                    }
                      else if(data==2)
                      {
                          $('#coupon_status').html('<i class="icon icon-remove alert-danger alert"> Please Enter Coupon Code</i>');
                          $('vCouponCode[type="submit"]').removeAttr('disabled');
                      }
	                }

	            });
	        }

		  </script>
<!--link rel="stylesheet" media="all" type="text/css" href="../assets/js/dtp/jquery-ui.css" />
          <link rel="stylesheet" media="all" type="text/css" href="../assets/js/dtp/jquery-ui-timepicker-addon.css" />

          <script type="text/javascript" src="../assets/js/dtp/jquery-ui.min.js"></script>
          <script type="text/javascript" src="../assets/js/dtp/jquery-ui-timepicker-addon.js"></script-->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"
          type="text/javascript"></script>
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css"
          rel="Stylesheet"type="text/css"/>
<?php if ($action == 'Edit') { ?>
<script>
				window.onload = function () {
					showhidedate('<?php echo $eValidityType; ?>');
				};
			</script>
<?}else{
      ?>
<script>

  window.onload = function () {

          $('input:radio[name=eValidityType][value=Permanent]').attr('checked', true);
        };

</script>
<?php } ?>
<script type="text/javascript">

            /*$(function() {
                $("#dActiveDate").datepicker({
                  minDate: 0,
                  dateFormat: "yy-mm-dd",
                  showOn: "button",
                  buttonImage: "http://192.168.1.131/uber-app/web-new/assets/img/cal-icon.gif",
                  buttonImageOnly: true
                });
                $("#dExpiryDate").datepicker({
                        minDate: 0,
                  dateFormat: "yy-mm-dd",
                  showOn: "button",
                  buttonImage: "http://192.168.1.131/uber-app/web-new/assets/img/cal-icon.gif",
                  buttonImageOnly: true
                });

                $("#dActiveDate").on("dp.change", function (e) {
            $('#dExpiryDate').data("DateTimePicker").minDate(e.date);
        });
        $("#dActiveDate").on("dp.change", function (e) {
            $('#dExpiryDate').data("DateTimePicker").maxDate(e.date);
        });
              });*/

               $(function () {
                   $("#dActiveDate").datepicker({
                       numberOfMonths: 2,
                       dateFormat: "yy-mm-dd",
                       onSelect: function (selected) {
                           var dt = new Date(selected);
                           dt.setDate(dt.getDate() + 1);
                           $("#dExpiryDate").datepicker("option", "minDate", dt);
                       }
                   });
                   $("#dExpiryDate").datepicker({
                       numberOfMonths: 2,
                       dateFormat: "yy-mm-dd",
                       onSelect: function (selected) {
                           var dt = new Date(selected);
                           dt.setDate(dt.getDate() - 1);
                           $("#dActiveDate").datepicker("option", "maxDate", dt);
                       }
                   });
               });


            function showhidedate(val){
              if(val == "Defined"){
                 document.getElementById("date1").style.display='';
                 document.getElementById("date2").style.display='';
                   document.getElementById("dActiveDate").lang='*';
                   document.getElementById("dExpiryDate").lang='*';
                 }
                 else
                 {
                 document.getElementById("date1").style.display='none';
                 document.getElementById("date2").style.display='none';
                   document.getElementById("dActiveDate").lang='';
                   document.getElementById("dExpiryDate").lang='';

                   }
            }

function randomStringToInput(clicked_element)
{
    var self = $(clicked_element);
    var random_string = generateRandomString(6);
    $('input[name=vCouponCode]').val(random_string);

}
function generateRandomString(string_length)
{
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    var string = '';
    for(var i = 0; i <= string_length; i++)
    {
        var rand = Math.round(Math.random() * (characters.length - 1));
        var character = characters.substr(rand, 1);
        string = string + character;
    }
    return string;
}
</script>
</body>
</html>

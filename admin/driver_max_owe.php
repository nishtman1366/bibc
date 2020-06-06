<?php
include_once('../common.php');

$script = "MaxOwe";

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$file = "driver_max_owe.txt";
$max_owe = file_get_contents($file);

//echo '<pre>'; print_r($max_owe); exit;
$new_max_owe = isset($_REQUEST['max_owe']) ? $_REQUEST['max_owe'] : '10000';

if (isset($_POST['submit'])) {

  if (file_put_contents($file, $new_max_owe) > 0) {

    $max_owe = $new_max_owe;
    $success = 1;
  }
  else {

    $success = 3;
  }
}

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
<meta charset="UTF-8" />
<title>ادمین | حداکثر بدهی راننده
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
            حداکثر بدهی راننده
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
                <label>حد اکثر : (<?php echo $langage_lbl_admin['LBL_MAX_OWE_CURR']; ?>)<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="max_owe"  id="max_owe" value="<?php echo $max_owe; ?>" placeholder="value" required>
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

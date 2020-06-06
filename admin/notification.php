<?php
include_once('../common.php');
include_once('notification_manager.php');

$script = "Notification";

if (!isset($generalobjAdmin)) {
     require_once(TPATH_CLASS . "class.general_admin.php");
     $generalobjAdmin = new General_admin();
}
$generalobjAdmin->check_member_login();

$message['title'] = isset($_REQUEST['message_title']) ? $_REQUEST['message_title'] : '';
$message['excerpt'] = isset($_REQUEST['message_excerpt']) ? $_REQUEST['message_excerpt'] : '';
$message['body'] = isset($_REQUEST['message_body']) ? $_REQUEST['message_body'] : ' ';
$message['image'] = isset($_REQUEST['image_url']) ? $_REQUEST['image_url'] : '';
$message['tokenId']        = isset($_REQUEST['tokenId']) ? $_REQUEST['tokenId'] : '';
$message['type'] = 'APPMESSAGE';
$message['iGcmRegId']       = isset($_REQUEST['iGcmRegId']) ? $_REQUEST['iGcmRegId'] : '';

if (isset($_POST['submit'])) {

    $res = send($message,$_POST['iCompanyId']);
    if ($res == 1) {

      $success = 1;
    }
    else {

      $success = 3;
    }
    $success = 1;

    //echo '<pre>'; print_r($message); exit;
    //echo 'tokenId::::'.$tokenId;
    //echo ':::::::tokenIdTemp:::::'.$tokenIdTemp;

    // if($tokenId != "")
    // {
    //     $fcmData = $message;
    //     $res = sendFCM($tokenId ,$fcmData);
    //     //$res = sendFCM("dBAvvDsKe3o:APA91bG_L2wTaFSn4EgE_dK9j58vaRkigule1FlRLBduUrxXIj5FF95yA6b7NnwlYwP95TEgQroSq3Uwz54P7W_ZYd_cGsQPHGZRx5MXtKuuLqSHXxuGU-iMvxBDiiQw6CrlevEaocXt" ,$fcmData);
    //     //echo '<pre>'; print_r($res); exit;
    //     //{"message_id":7290341092924295890}
    //
    //     $resObj = json_decode($res,true);
    //     if(isset($resObj['message_id']))
    //     {
    //         $success = 1;
    //     }
    //     else
    //     {
    //         $success = 3;
    //     }
    // }

}

$sql2 = "select * FROM register_driver WHERE 1 AND eStatus='active' ORDER BY vName ASC";
$db_drivers = $obj->MySQLSelect($sql2);
// for Edit

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD-->
<head>
<meta charset="UTF-8" />
<title>ارسال اعلان | ادمین
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
            ارسال اعلان
          </h2>
         </div>
      </div>
      <hr />
      <div class="body-div">
        <div class="form-group"> <span style="color: red;font-size: small;" id="coupon_status"></span>
          <?php if ($success == 1) {?>
          <div class="alert alert-success alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">x</button>
            اعلان با موفقیت ارسال شد </div>
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
            مشکل در ارسال اعلان </div>
          <br/>
          <?} ?>
          <form method="post" action="" enctype="multipart/form-data" class="">

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label>عنوان : <span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="message_title"  id="message_title" value="" placeholder="عنوان" required>
                </div>

            </div>


             <div class="row">
              <div class="col-lg-12">
                <label>گزیده :<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <textarea name="message_excerpt" rows="2" cols="40" class="form-control" id="message_excerpt" placeholder="توضیحات" required maxlength="150"></textarea>
              </div>
            </div>

            <div class="row">
              <div class="col-lg-12">
                <label>پیام :<span class="red"> </span></label>
              </div>
              <div class="col-lg-6">
                <textarea name="message_body" rows="5" cols="40" class="form-control" id="message_body" placeholder="متن پیام" ></textarea>
              </div>
            </div>

            <div class="row coupon-action-n1">
              <div class="col-lg-12">
                <label>آدرس عکس (ویژگی):</label>
              </div>
              <div class="col-lg-6">
                <input type="text" class="form-control" name="image_url"  id="image_url" value="" placeholder="http://">
                </div>
            </div>

            <div class="row coupon-action-n3">
              <div class="col-lg-12">
                <label>نوع اپ<span class="red"> *</span></label>
              </div>
              <div class="col-lg-6">
                <select id="tokenId" name="tokenId" class="form-control " required>
                  <option value="">انتخاب نوع اپ</option>
                  <option value="/topics/global">تمام اپ ها</option>
                  <option value="/topics/driver">اپ راننده</option>
                  <option value="/topics/passenger">اپ مسافر</option>
                  <option value="/topics/indivisual">یک راننده</option>
                  <option value="/topics/company">شرکت ها</option>
                </select>
              </div>
            </div>

            <div class="row coupon-action-n6">
              <div class="col-lg-12">
                <label>انتخاب راننده<span class="red"> </span></label>
              </div>
              <div class="col-lg-6">
                    <?php if(!empty($db_drivers)) { ?>
				    	<span><select class="form-control form-control-select" name='iGcmRegId' id="iGcmRegId" >
				    	<option value="" >انتخاب <?php echo $langage_lbl_admin['LBL_DRIVER_TXT_ADMIN'];?></option>
				    	<?php foreach ($db_drivers as $db_driver) { ?>
				    		<option value="<?php echo $db_driver['iGcmRegId']; ?>"><?php echo $db_driver['vName'].' '.$db_driver['vLastName']; ?></option>
				    				<?php } ?>
				    	</select></span><span>
                <input type="text"  title="Enter Mobile Number." class="form-control add-book-input" name="search_driver_ajax2"  id="search_driver_ajax2" value="<?php echo  $vPhone; ?>" placeholder="نام یا نام خانوادگی یا شماره موبایل راننده را وارد کنید"  style="">
              <a class="btn btn-sm btn-info" id="search_driver_ajax" >جست و جو</a>
              </span>

				        <?php }else { ?>
				        <div class="row show_drivers_lists">
				        <div class="col-lg-12">
				        <h5>راننده ای موجود نیست</h5>
			            </div>
			            </div>
			        <?php } ?>
              </div>
            </div>




            <div class="row">
              <div class="col-lg-12">
                <label>شرکت ها :<span class="red"> </span></label>
              </div>
              <div class="col-lg-6">
                <select class="form-control" name = 'iCompanyId' id = 'iCompanyId' >
                  <option value="0">--select--</option>
                  <?
                  $sql = "select * from company WHERE eStatus != 'Deleted'";
                  $db_company = $obj->MySQLSelect($sql); for ($i = 0; $i < count($db_company); $i++) {
                    if($db_company[$i]['iCompanyId'] == $_GET['id'])
                    {
                    echo '<option selected value ="' . $db_company[$i]['iCompanyId'] . '">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
                   }
                   else {
                    echo '<option value ="' . $db_company[$i]['iCompanyId'] . '">' . $db_company[$i]['vName'] . " " . $db_company[$i]['vLastName'] . " (" . $db_company[$i]['vCompany'] . ')' . " </option>";
                   }
    } ?>
                </select>
              </div>
            </div>














            <div class="row coupon-action-n4">
              <div class="col-lg-12">
                <input type="submit" class="save btn-info" name="submit" id="submit" value="Send Notification"  >
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







$('#search_driver_ajax').on('click', function () {
  $('#iGcmRegId')
      .find('option')
      .remove()

  ;
    var phone = $('#search_driver_ajax2').val();
    $.ajax({
        type: "POST",
        url: 'search_driver_ajax.php',
        data: 'phone=' + phone,
        success: function (dataHtml)
        {
console.log(dataHtml);
            if (dataHtml != "" || dataHtml != ":::~" || dataHtml != " ") {
              var result;
                var result1 = dataHtml.split('~');
                //alert(result1.length);
                for(i = 0; i<result1.length-1;i++)
                {
                  result = result1[i].split(':');
                  //alert(result1[1]);
                                          $('#iGcmRegId')
                                              .find('option')
                                                              .end()
                                              .append('<option value="' + result[2] + '">' + result[0] + ' ' + result[1] + '</option>')
                                              .val(result[2])
                                          ;
                }


  }else {
    $('#iGcmRegId')
        .find('option')
        .remove()
        .end()
        .append('<option value="داده ای یافت نشد">داده ای یافت نشد</option>')
        .val('داده ای یافت نشد')
    ;
}
}
});
});






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

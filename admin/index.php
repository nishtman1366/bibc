<?php
include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
$generalobjAdmin->go_to_home();
?>

<?php
exit();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title> <?php echo $langage_lbl['LBL_ADMIN_LOGIN_HEADER1']; ?> </title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <link rel="icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/bootstrap.css"/>
    <link rel="stylesheet" href="css/login.css"/>
    <link rel="stylesheet" href="css/style.css"/>
    <link rel="stylesheet" href="../assets/css/animate/animate.min.css"/>
    <link rel="stylesheet" href="../assets/plugins/magic/magic.css"/>
    <link rel="stylesheet" href="css/font-awesome.css"/>
    <link rel="stylesheet" href="../assets/plugins/font-awesome-4.6.3/css/font-awesome.min.css"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="nobg loginPage">
<div class="topNav">
    <div class="userNav">
        <ul>
            <li><a href="../index.php" title=""><i
                            class="icon-reply"></i><span><?php echo $langage_lbl['BTN_ADMIN_MAIN_WEBSITE']; ?></span></a>
            </li>
            <li><a href="../rider" title=""><i
                            class="icon-user"></i><span><?php echo $langage_lbl['BTN_ADMIN_RIDER_SIGN_IN_LINK']; ?></span></a>
            </li>
            <li><a href="../driver" title=""><i
                            class="icon-comments"></i><span><?php echo $langage_lbl['BTN_ADMIN_DRIVER_SIGN_IN_LINK']; ?></span></a>
            </li>
        </ul>
    </div>
</div>
<!-- PAGE CONTENT -->
<div class="container animated fadeInDown">
    <div class="text-center"><img src="../assets/img/logo.png" id="Admin" alt=" Admin"/></div>
    <div class="tab-content ">
        <div id="login" class="tab-pane active">
            <form action="" class="form-signin" method="post" id="login_box" onSubmit="return chkValid();">
                <p style="display:none;" class="btn-block btn btn-rect btn-success" id="success"></p>
                <p style="display:none;" class="btn-block btn btn-rect btn-danger text-muted text-center"
                   id="errmsg"></p>
                <br>
                <p class="head_login_005"><?php echo $langage_lbl['LBL_ADMIN_LOGIN_HEADER']; ?></p>
                <input type="text" placeholder="Email Address" class="form-control" name="vEmail" id="vEmail"
                       required <?php if (SITE_TYPE == 'Demo') {
                    echo "Value='demo@demo.com'";
                } ?>/>
                <input type="password" placeholder="Password" class="form-control" name="vPassword" id="vPassword"
                       required <?php if (SITE_TYPE == 'Demo') {
                    echo "Value='123456'";
                } ?>/>
                <input type="submit" class="btn text-muted text-center btn-default"
                       value=<?php echo $langage_lbl['BTN_ADMIN_SIGN_IN']; ?>/>
                <br>
            </form>
            <?php if (SITE_TYPE == 'Demo') { ?>
                <div class="admin-home-tab">
                    <ul class="nav nav-tabs">
                        <li class="active" onclick="setCredentials('1');"><a data-toggle="tab" href="#super001">Super
                                Administrator</a></li>
                        <li onclick="setCredentials('2');"><a data-toggle="tab" href="#dispatch001">Dispatcher
                                Administrator</a></li>
                        <li onclick="setCredentials('3');"><a data-toggle="tab" href="#billing001">Billing
                                Administrator</a></li>
                    </ul>

                    <div class="tab-content">
                        <div id="super001" class="tab-pane active">
                            <h3> Use below Detail for Demo Version</h3>

                            <p><b>User Name:</b> demo@demo.com</p>
                            <p><b>Password:</b> 123456 00000</p>
                            <p>Super Administrator can manage whole system and other user's rights too.</p>
                        </div>
                        <div id="dispatch001" class="tab-pane">
                            <h3> Use below Detail for Demo Version</h3>

                            <p><b>User Name:</b> demo2@demo.com</p>
                            <p><b>Password:</b> 123456 </p>
                            <p>Call Center Panel / Administrator Dispatcher Panel / Manual Taxi Booking Panel. This
                                panel allows one to see all taxi's on map using God's View. And book taxi's for
                                customer's who would call to book a taxi.</p>
                        </div>
                        <div id="billing001" class="tab-pane">
                            <h3> Use below Detail for Demo Version</h3>

                            <p><b>User Name:</b> demo3@demo.com</p>
                            <p><b>Password:</b> 123456 </p>
                            <p>This use will have access to reports only. Will be used by Accounts Team to manage
                                finances and see profits/revenue.</p>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            <?php } ?>
        </div>
        <div id="forgot" class="tab-pane">
            <form class="form-signin" method="post" id="frmforget">
                <input type="email" required="required" placeholder="Your E-mail" class="form-control" id="femail"/>
                <br/>
                <button class="btn text-muted text-center btn-success" type="submit" onClick="forgotPass();">Recover
                    Password
                </button>
            </form>
        </div>
    </div>
</div>
<!--END PAGE CONTENT -->
<!-- PAGE LEVEL SCRIPTS -->
<script src="../assets/plugins/jquery-2.0.3.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.js"></script>
<script src="../assets/js/login.js"></script>
<script>

    function setCredentials(tpd) {
        if (tpd == 2) {
            $("#vEmail").val('demo2@demo.com');
            $("#vPassword").val('123456');
        } else if (tpd == 3) {
            $("#vEmail").val('demo3@demo.com');
            $("#vPassword").val('123456');
        } else {
            $("#vEmail").val('demo@demo.com');
            $("#vPassword").val('123456');
        }
    }

    $('input').keyup(function () {
        $this = $(this);
        if ($this.val().length == 1) {
            var x = new RegExp("[\x00-\x80]+"); // is ascii

            //alert(x.test($this.val()));

            var isAscii = x.test($this.val());
            if (isAscii) {
                $this.attr("dir", "ltr");
            } else {
                $this.attr("dir", "rtl");
            }
        }

    });

    function change_heading(heading, addClass, removeClass) {
        document.getElementById("login").innerHTML = heading;
        document.getElementById(addClass).className = "tab-pane";
        document.getElementById(removeClass).className = "tab-pane active";
    }

    function chkValid() {
        var id = document.getElementById("vEmail").value;
        var pass = document.getElementById("vPassword").value;

        if (id == '' || pass == '') {
            document.getElementById("errmsg").style.display = '';
            setTimeout(function () {
                document.getElementById('errmsg').style.display = 'none';
            }, 2000);
        } else {
            let serializeInfo = $("#login_box").serialize();
            let info = {vEmail: id, vPassword: pass};
            var request = $.ajax({
                type: "POST",
                url: 'ajax_login_action.php',
                data: serializeInfo,

                success: function (data) {// alert(data);
                    if (data == 1) {
                        document.getElementById("errmsg").innerHTML = 'You are not active.Please contact administrator to activate your account.';
                        document.getElementById("errmsg").style.display = '';
                        return false;
                    } else if (data == 2) {
                        console.log('let`s login');
                        document.getElementById("errmsg").style.display = 'none';
                        window.location = "dashboard.php";

                        return true; // success registration
                    } else if (data == 3) {
                        document.getElementById("errmsg").innerHTML = 'Invalid combination of username & Password';
                        document.getElementById("errmsg").style.display = '';
                        return false;

                    } else {
                        document.getElementById("errmsg").innerHTML = 'Invalid Email or Password';
                        document.getElementById("errmsg").style.display = '';
                        return false;
                    }
                }
            });

            request.fail(function (jqXHR, textStatus) {
                console.log(jqXHR);
                alert("Request failed: " + textStatus);
            });

        }
        return false;
    }

    function forgotPass() {
        var id = document.getElementById("femail").value;
        if (id == '') {

            document.getElementById("errmsg_email").style.display = '';
            document.getElementById("errmsg_email").innerHTML = 'Please enter Email Address';
            return false;
        } else {

            var request = $.ajax({
                type: "POST",
                url: 'ajax_fpass_action.php',
                data: $("#frmforget").serialize(),
                beforeSend: function () {
                    alert(data);
                },
                success: function (data) {
                    if (data == 1) {
                        document.getElementById("page_title").innerHTML = "Login";
                        document.getElementById("forgot").className = "tab-pane";
                        document.getElementById("login").className = "tab-pane active";
                        document.getElementById("success").innerHTML = 'Your Password has been sent Successfully.';
                        document.getElementById("success").style.display = '';
                        return false;
                    } else if (data == 0) {
                        document.getElementById("errmsg_email").innerHTML = 'Error in Sending Password.';
                        document.getElementById("errmsg_email").style.display = '';
                        return false;

                    } else if (data == 3) {
                        document.getElementById("errmsg_email").innerHTML = 'Sorry ! The Email address you have entered is not found.';
                        document.getElementById("errmsg_email").style.display = '';
                        return false;
                    }
                    return false;
                }
            });
            request.fail(function (jqXHR, textStatus) {
                alert("Request failed: " + textStatus);
                return false;
            });

            return false;
        }
        return false;
    }

</script>
<!--END PAGE LEVEL SCRIPTS -->
</body>
<!-- END BODY -->
</html>

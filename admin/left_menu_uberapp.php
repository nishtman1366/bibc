<?php
include_once('savar_check_permission.php');

$tbl_name = "administrators";
$sess_iAdminUserId = isset($_SESSION['sess_iAdminUserId']) ? $_SESSION['sess_iAdminUserId'] : '';
$sql = "SELECT * FROM " . $tbl_name . " WHERE iAdminId = '" . $sess_iAdminUserId . "'";
$db_data1 = $obj->MySQLSelect($sql);
$vAccessOptionsMenu = "";
if (count($db_data1) > 0) {
    $vAccessOptionsMenu = $db_data1[0]['vAccessOptions'];
}
?>
<!-- MENU SECTION -->
<div id="sidebar" class="m-0 p-0 bg-dark position-fixed h-100">
    <div class="sidebar-header">
        <div class="user-pic">
            <img class="img-responsive img-rounded" src="<?php assets('images/icons/user.png'); ?>" alt="">
        </div>
        <div class="user-info">
            <p><?php echo $_SESSION['sess_vAdminFirstName'] . " " . $_SESSION['sess_vAdminLastName']; ?></p>
            <a href="logout.php" style="font-size: .9rem">خروج از سیستم</a>
        </div>
    </div>
    <div class="sidebar-content">
        <ul>
            <?php if (checkPermission('DASHBOARD') && (strpos($vAccessOptionsMenu, 'a0') !== false)) : ?>
                <li class="<?php echo(!isset($script) ? 'active' : ''); ?>">
                    <a href="<?php echo adminUrl(); ?>">
                        <i class="fa fa-dashboard"></i>داشبورد مدیریت
                    </a>
                </li>
            <?php endif; ?>
            <?php if (checkPermission('ADMIN_USERS') && (strpos($vAccessOptionsMenu, 'a1') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'Admin') ? 'active' : ''; ?>"><a
                            href="<?php echo adminUrl('administrators') ?>"><i
                                class="fa fa-user"></i> مدیریت کاربران </a></li>
            <?php endif; ?>
            <?php if (checkPermission('COMPANY') && (strpos($vAccessOptionsMenu, '2a') !== false && $vAccessOptions[strpos($vAccessOptions, '2a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'Company') ? 'active' : ''; ?>"><a
                            href="<?php echo adminUrl('companies') ?>"><i
                                class="fa fa-building"
                                aria-hidden="true"></i> <?php echo $langage_lbl['LBL_ADMIN_MENU_COMPANY']; ?> </a></li>
            <?php endif; ?>
            <?php if (checkPermission('AREA') && (strpos($vAccessOptionsMenu, '3a') !== false && $vAccessOptions[strpos($vAccessOptions, '3a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'Area') ? 'active' : ''; ?>">
                    <a href="<?php echo adminUrl('area'); ?>">
                        <i class="fa fa-flag" aria-hidden="true"></i> <?php echo $langage_lbl['LBL_ADMIN_MENU_AREA']; ?>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (checkPermission('DRIVER') && (strpos($vAccessOptionsMenu, '4a') !== false && $vAccessOptions[strpos($vAccessOptions, '4a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'Driver') ? 'active' : ''; ?>"><a
                            href="<?php echo adminUrl('drivers'); ?>"><i
                                class="fa fa-group"></i> <?php echo $langage_lbl['LBL_DRIVER_TXT_ADMIN']; ?></a></li>
            <?php endif; ?>
            <?php if (checkPermission('DRIVER_MAX_OWE') && (strpos($vAccessOptionsMenu, '5a') !== false && $vAccessOptions[strpos($vAccessOptions, '5a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'DRIVER_MAX_OWE') ? 'active' : ''; ?>"><a
                            href="<?php echo adminUrl('drivers', ['op' => 'max_owe']); ?>"><i
                                class="fa fa-money"></i> <?php echo $langage_lbl['LBL_DRIVER_MAX_OWE_TXT_ADMIN']; ?></a></li>
            <?php endif; ?>

            <?php if (checkPermission('PET_TYPE')) : ?>
                <?php if ($APP_TYPE == 'UberX') { ?>

                    <li class="<?php echo (isset($script) && $script == 'PetType') ? 'active' : ''; ?>"><a
                                href="javascript:void(0);" data-parent="#component-nav1" data-toggle="collapse"
                                class="accordion-toggle" data-target="#component-driver-nav1"><i
                                    class="icon-group"></i> <?php echo $langage_lbl['LBL_PET_TYPE']; ?> <span
                                    class="pull-right">
				<i class="icon-angle-left"></i>
			</span></a>
                        <ul class="<?php echo (isset($script) && $script == 'PetType') ? 'in' : 'collapse'; ?>"
                            id="component-driver-nav1">
                            <li><a href="pettype.php"><i
                                            class="icon-angle-right"></i> <?php echo $langage_lbl['LBL_PET_TYPE']; ?>  </a>
                            </li>
                            <li><a href="user_pets.php"><i
                                            class="icon-angle-right"></i> <?php echo $langage_lbl['LBL_USER_PETS_ADMIN']; ?>
                                </a></li>

                        </ul>
                    </li>
                <?php } ?>
            <?php endif; ?>
            <?php if (checkPermission('VEHICLE') && (strpos($vAccessOptionsMenu, '6a') !== false && $vAccessOptions[strpos($vAccessOptions, '6a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'Vehicle') ? 'active' : ''; ?>"><a
                            href="<?php echo adminUrl('vehicles'); ?>"><i class="fa fa-cab"
                                                                          aria-hidden="true"></i> <?php echo $langage_lbl['LBL_VEHICLE_TXT_ADMIN']; ?>
                    </a></li>
            <?php endif; ?>

            <?php if (checkPermission('VEHICLE_TYPE') && (strpos($vAccessOptionsMenu, '7a') !== false && $vAccessOptions[strpos($vAccessOptions, '7a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'VehicleType') ? 'active' : ''; ?>"><a
                            href="<?php echo adminUrl('vehicleTypes'); ?>"><i class="fa fa-yelp"
                                                                              aria-hidden="true"></i> <?php echo $langage_lbl['LBL_VEHICLE_TYPE_SMALL_TXT']; ?>
                    </a></li>
            <?php endif; ?>

            <?php if ((strpos($vAccessOptionsMenu, 'a2') !== false)) : ?>
                <li><a href="fee_settings.php"><i class="fa fa-dollar" aria-hidden="true"></i> <?php echo "تنظیمات نرخ"; ?>
                    </a></li>
            <?php endif; ?>

            <?php if (checkPermission('PACKAGE_TYPE') && (strpos($vAccessOptionsMenu, '8a') !== false && $vAccessOptions[strpos($vAccessOptions, '8a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'PahckageType') ? 'active' : ''; ?>"><a
                            href="package_type.php"><i class="fa fa-server"
                                                       aria-hidden="true"></i> <?php echo $langage_lbl['LBL_VEHICLE_PACKAGE_TYPE_TXT']; ?>
                    </a></li>
            <?php endif; ?>
            <?php if (checkPermission('VEHICLE_CATEGORY')) : ?>
                <?php if ($APP_TYPE == 'UberX') { ?>
                    <li class="<?php echo (isset($script) && $script == 'VehicleCategory') ? 'active' : ''; ?>"><a
                                href="vehicle_category.php"><i class="fa fa-plus-square"
                                                               aria-hidden="true"></i> <?php echo $langage_lbl['LBL_VEHICLE_CATEGORY_TXT']; ?>
                        </a></li>
                <?php } ?>
            <?php endif; ?>
            <?php if (checkPermission('RIDER') && (strpos($vAccessOptionsMenu, '9a') !== false && $vAccessOptions[strpos($vAccessOptions, '9a') - 1] != '1')) : ?>
                <li class="<?php echo (isset($script) && $script == 'Rider') ? 'active' : ''; ?>"><a href="rider.php"><i
                                class="fa fa-users"></i> سوار / پیک </a></li>
            <?php endif; ?>
            <?php if (checkPermission('BOOKING') && (strpos($vAccessOptionsMenu, '10a') !== false)) : ?>

                <?php if (RIIDE_LATER == 'YES') { ?>
                    <li class="<?php echo (isset($script) && $script == 'booking') ? 'active' : ''; ?>"><a
                                href="add_booking.php"><i
                                    class="fa fa-taxi"></i> <?php echo $langage_lbl['LBL_MANUAL_TAXIـDISPATCH_ADMIN']; ?>
                        </a></li>
                <?php } ?>
            <?php endif; ?>
            <?php if (checkPermission('TRIPS') && (strpos($vAccessOptionsMenu, '11a') !== false)) : ?>

                <li class="<?php echo (isset($script) && $script == 'Trips') ? 'active' : ''; ?>"><a href="trip.php"><i
                                class="fa fa-exchange"
                                aria-hidden="true"></i> <?php echo $langage_lbl['LBL_TRIPS_TXT_ADMIN']; ?>  </a></li>
            <?php endif; ?>
            <?php if (checkPermission('CAB_BOOKING') && (strpos($vAccessOptionsMenu, '12a') !== false)) : ?>

                <?php if (RIIDE_LATER == 'YES') { ?>
                    <li class="<?php echo (isset($script) && $script == 'CabBooking') ? 'active' : ''; ?>"><a
                                href="cab_booking.php"><i class="icon-book"
                                                          aria-hidden="true"></i> <?php echo $langage_lbl['LBL_RIDE_LATER_BOOKINGS_ADMIN']; ?>
                        </a></li>
                <?php } ?>
            <?php endif; ?>
            <?php if (checkPermission('COUPON') && (strpos($vAccessOptionsMenu, '13a') !== false)) : ?>

                <li class="<?php echo (isset($script) && $script == 'Coupon') ? 'active' : ''; ?>"><a href="coupon.php"><i
                                class="fa fa-product-hunt"
                                aria-hidden="true"></i> <?php echo $langage_lbl['LBL_PROMO_TXT_ADMIN']; ?> </a></li>

            <?php endif; ?>
            <?php if (checkPermission('REFERRALS') && (strpos($vAccessOptionsMenu, '14a') !== false)) : ?>

                <li class="<?php echo (isset($script) && $script == 'Referrals') ? 'active' : ''; ?>"><a
                            href="referral.php"><i class="fa fa-product-hunt"
                                                   aria-hidden="true"></i> <?php echo $langage_lbl['LBL_REFFERALS_TXT_ADMIN']; ?>
                    </a></li>

            <?php endif; ?>
            <?php if (checkPermission('MAP') && (strpos($vAccessOptionsMenu, '15a') !== false)) : ?>


                <li class="<?php echo (isset($script) && $script == 'Map') ? 'active' : ''; ?>"><a href="map.php"><i
                                class="icon-map-marker"
                                aria-hidden="true"></i><?php echo $langage_lbl['LBL_GODS_TXT_ADMIN']; ?> </a></li>
            <?php endif; ?>

            <?php if (checkPermission('HEAT_MAP') && (strpos($vAccessOptionsMenu, '16a') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'Heat Map') ? 'active' : ''; ?>"><a
                            href="heatmap.php"><i class="fa fa-header"
                                                  aria-hidden="true"></i><?php echo $langage_lbl['LBL_HEAT_TXT_ADMIN']; ?>
                    </a></li>
            <?php endif; ?>

            <?php if ((strpos($vAccessOptionsMenu, '17a') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'Review') ? 'active' : ''; ?>"><a href="review.php"><i
                                class="icon-comments"></i> <?php echo $langage_lbl['LBL_REVIEWS_TXT_ADMIN']; ?> </a></li>
            <?php endif; ?>

            <?php if ((strpos($vAccessOptionsMenu, '18a') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'Notification') ? 'active' : ''; ?>"><a
                            href="notification.php"><i
                                class="icon-comments"></i><?php echo $langage_lbl['LBL_PUSH_TXT_ADMIN']; ?> </a></li>
            <?php endif; ?>


            <!--Pm In App-->

            <?php if ((strpos($vAccessOptionsMenu, '18a') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'pminapp') ? 'active' : ''; ?>"><a href="pminapp.php"><i
                                class="icon-comments"></i> ارسال پیام </a></li>
            <?php endif; ?>
            <!--Pm In App-->


            <!--Pm In App-->

            <?php if ((strpos($vAccessOptionsMenu, '18a') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'driver_On_Off') ? 'active' : ''; ?>"><a
                            href="driver_On_Off.php"><i class="fa fa-exchange"></i>ساعات کارکرد راننده</a></li>
            <?php endif; ?>
            <!--Pm In App-->


            <?php if ((strpos($vAccessOptionsMenu, '18a') !== false)) : ?>
                <li class="<?php echo (isset($script) && $script == 'darkhastsafarranandeh') ? 'active' : ''; ?>"><a
                            href="driver_request.php"><i class="fa fa-exchange"></i> درخواست سفر راننده </a></li>
            <?php endif; ?>


            <?php if (checkPermission('REPORTS') && (strpos($vAccessOptionsMenu, '19a') !== false)) : ?>
                <li class="panel <?php echo (isset($script) && ($script == 'Payment Report' || $script == 'referrer' || $script == 'Wallet Report' || $script == 'Driver Log Report' || $script == 'CancelledTrips' || $script == 'Driver Accept Report' || $script == 'Driver Payment Report' || $script == 'Driver Trip Detail')) ? 'active' : ''; ?>">
                    <a href="javascript:void(0);" data-parent="#menu" data-toggle="collapse" class="accordion-toggle"
                       data-target="#component-nav-report">
                        <i class="icon-cogs"> </i> <?php echo $langage_lbl['LBL_REPORTS_TXT_ADMIN']; ?>

                        <span class="pull-right">
						<i class="icon-angle-left"></i>
					</span>

                    </a>
                    <ul class="<?php echo (isset($script) && ($script == 'Payment Report' || $script == 'referrer' || $script == 'Company Wallet Report' || $script == 'Wallet Report' || $script == 'Driver Payment Report' || $script == 'Driver Accept Report' || $script == 'CancelledTrips' || $script == 'Driver Log Report' || $script == 'Driver Trip Detail')) ? 'in' : 'collapse'; ?>"
                        id="component-nav-report">
                        <li class="<?php echo (isset($script) && $script == 'Payment Report') ? 'active' : ''; ?>"><a
                                    href="payment_report.php"><i
                                        class="icon-money"></i> <?php echo $langage_lbl['LBL_PAYREPORTS_ADMIN']; ?> </a>
                        </li>
                        <?php

                        if ($REFERRAL_SCHEME_ENABLE == 'Yes') { ?>

                            <li class="<?php echo (isset($script) && $script == 'referrer') ? 'active' : ''; ?>"><a
                                        href="referrer.php"><i class="fa fa-hand-peace-o"
                                                               aria-hidden="true"></i> <?php echo $langage_lbl['LBL_REFREPORTS_ADMIN']; ?>
                                </a></li>
                        <?php } ?>

                        <?php if ($WALLET_ENABLE == 'Yes') { ?>

                            <li class="<?php echo (isset($script) && $script == 'Wallet Report') ? 'active' : ''; ?>"><a
                                        href="wallet_report.php"><i class="fa fa-google-wallet"
                                                                    aria-hidden="true"></i> <?php echo $langage_lbl['LBL_UWREPORTS_ADMIN']; ?>
                                </a></li> <?php } ?>

                        <li class="<?php echo (isset($script) && $script == 'Driver Payment Report') ? 'active' : ''; ?>"><a
                                    href="driver_pay_report.php"><i
                                        class="icon-money"></i> <?php echo $langage_lbl['LBL_DPREPORTS_ADMIN']; ?> </a></li>

                        <li class="<?php echo (isset($script) && $script == 'Company Wallet Report') ? 'active' : ''; ?>"><a
                                    href="company_wallet_report.php"><i
                                        class="icon-money"></i> <?php echo $langage_lbl['LBL_COMREPORTS_ADMIN']; ?> </a>
                        </li>

                        <li class="<?php echo (isset($script) && $script == 'CancelledTrips') ? 'active' : ''; ?>"><a
                                    href="cancelled_trip.php"><i class="fa fa-exchange"
                                                                 aria-hidden="true"></i> <?php echo $langage_lbl['LBL_CTREPORTS_ADMIN']; ?>
                            </a></li>

                        <li class="<?php echo (isset($script) && $script == 'Driver Trip Detail') ? 'active' : ''; ?>"><a
                                    href="driver_trip_detail.php"><i
                                        class="fa fa-taxi"></i> <?php echo $langage_lbl['LBL_TTVREPORTS_ADMIN']; ?> </a>
                        </li>
                        <?php /*<li class="<?php echo (isset($script) && $script == 'Driver Registration Report')?'active':'';?>"><a href="driver_registration_report.php"><i class="icon-money"></i> Driver Report</a></li>

					<!-- <li class="<?php echo (isset($script) && $script == 'Driver Log Report')?'active':'';?>"><a href="driver_log_report.php"><i class="glyphicon glyphicon-list-alt"></i> Driver Log Report</a></li> -->

					<!--<li class="<?php echo (isset($script) && $script == 'Driver Accept Report')?'active':'';?>"><a href="ride_acceptance_report.php"><i class="icon-group"></i> Ride Acceptance Report </a></li>-->

					<li class="<?php echo (isset($script) && $script == 'Passenger Registration Report')?'active':'';?>"><a href="passenger_registration_report.php"><i class="icon-money"></i> Passenger Report</a></li>

					<li class="<?php echo (isset($script) && $script == 'Finished Rides Report')?'active':'';?>"><a href="finished_rides_report.php"><i class="icon-money"></i> Finished Rides Report</a></li>

					<li class="<?php echo (isset($script) && $script == 'Cancelled Rides Report')?'active':'';?>"><a href="cancelled_rides_report.php"><i class="icon-money"></i> Cancelled Rides Report</a></li>

					<li class="<?php echo (isset($script) && $script == 'Rides By Month Report')?'active':'';?>"><a href="rides_by_month_report.php"><i class="icon-money"></i> Rides By Month Report</a></li>

					<li class="<?php echo (isset($script) && $script == 'Online Report')?'active':'';?>"><a href="online_report.php"><i class="icon-money"></i> Online Report</a></li>

					<li class="<?php echo (isset($script) && $script == 'Offline Report')?'active':'';?>"><a href="offline_report.php"><i class="icon-money"></i> Offline Report</a></li> */ ?>

                    </ul>
                </li>
            <?php endif; ?>
            <?php if (checkPermission('SETTINGS') && (strpos($vAccessOptionsMenu, '20a') !== false)) : ?>

                <li class="panel <?php echo (isset($script) && ($script == 'Settings' || $script == 'Language Settings')) ? 'active' : ''; ?>">
                    <a href="javascript:void(0);" data-parent="#menu" data-toggle="collapse" class="accordion-toggle"
                       data-target="#component-nav">
                        <i class="icon-cogs"> </i> <?php echo $langage_lbl['LBL_DASHBOAR_SETTINGS']; ?>

                        <span class="pull-right">
					<i class="icon-angle-left"></i>
				</span>
                        <!-- &nbsp; <span class="label label-default">10</span>&nbsp; -->
                    </a>
                    <ul class="<?php echo (isset($script) && $script == 'Settings' || $script == 'Language Settings') ? 'in' : 'collapse'; ?>"
                        id="component-nav">
                        <li><a href="general.php"><i class="icon-angle-right"></i>عمومی</a></li>
                        <li><a href="email_template.php"><i class="icon-angle-right"></i>قالب ایمیل ها</a></li>
                        <li><a href="javascript:void(0);" data-parent="#component-nav" data-toggle="collapse"
                               class="accordion-toggle" data-target="#language-nav"><i class="icon-angle-right"></i> عناوین
                                زبانی
                                <span class="pull-right">
						<i class="icon-angle-left"></i>
					</span></a>
                            <ul class="<?php echo (isset($script) && $script == 'Language Settings') ? 'in' : 'collapse'; ?>"
                                id="language-nav">
                                <li><a href="languages.php"><i
                                                class="icon-angle-right"></i><?php echo $langage_lbl['LBL_LLTYPE1_ADMIN']; ?>
                                    </a></li>
                                <li><a href="languages_admin.php"><i
                                                class="icon-angle-right"></i><?php echo $langage_lbl['LBL_LLTYPE2_ADMIN']; ?>
                                    </a></li>
                            </ul>
                        </li>
                        <?php if ($APP_TYPE != 'UberX') { ?>
                            <li><a href="make.php"><i
                                            class="icon-angle-right"></i> <?php echo $langage_lbl['LBL_CAR_MAKE_ADMIN']; ?>
                                </a></li>
                            <li><a href="model.php"><i
                                            class="icon-angle-right"></i><?php echo $langage_lbl['LBL_CAR_MODEL_ADMIN']; ?>
                                </a></li>
                        <?php } ?>

                        <li><a href="country.php"><i
                                        class="icon-angle-right"></i> <?php echo $langage_lbl['LBL_COUNTRY_TXT']; ?></a>
                        </li>
                        <li><a href="page.php"><i class="icon-angle-right"></i> <?php echo $langage_lbl['LBL_PAGES_TXT']; ?>
                            </a></li>
                        <li><a href="currency.php"><i
                                        class="icon-angle-right"></i><?php echo $langage_lbl['LBL_CURRENCY_TXT']; ?></a>
                        </li>
                        <li><a href="faq.php"><i class="icon-angle-right"></i><?php echo $langage_lbl['LBL_FAQs']; ?></a>
                        </li>
                        <li><a href="faq_categories.php"><i
                                        class="icon-angle-right"></i><?php echo $langage_lbl['LBL_FAQ_CATEGORY']; ?></a>
                        </li>
                        <li><a href="app_screenshot.php"><i
                                        class="icon-angle-right"></i></i> <?php echo $langage_lbl['LBL_SHOTS_TXT']; ?></a>
                        </li>
                        <li><a href="home_driver.php"><i
                                        class="icon-angle-right"></i><?php echo $langage_lbl['LBL_DRIVERS_NAME_ADMIN']; ?>
                            </a></li>
                        <li><a href="seo_setting.php"><i
                                        class="icon-angle-right"></i><?php echo $langage_lbl['LBL_SEOS_TXT_ADMIN']; ?></a>
                        </li>
                        <li><a href="banner.php"><i
                                        class="icon-angle-right"></i><?php echo $langage_lbl['LBL_BANNER_TXT_ADMIN']; ?></a>
                        </li>
                    </ul>
                </li>

            <?php endif; ?>
        </ul>
    </div>
    <div class="sidebar-footer"></div>

</div>
<!--END MENU SECTION -->
<script>
    $('.sidebar-toggle').click(function () {
        $("#left").toggleClass("sidebar_hide");
        if ($("#left").hasClass("sidebar_hide")) {
            $("#left").addClass("sidebar-minize");
            $("#left").addClass("sidebar-collapse");
            //setMenuEnable(0);
        } else {
            $("#left").removeClass("sidebar-minize");
            $("#left").removeClass("sidebar-collapse");
            //setMenuEnable(1);
        }
    });
</script>

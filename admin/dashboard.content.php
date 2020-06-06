<div id="content">

    <div class="container-fluid">
        <div class="row row-md">
            <div class="col-lg-3 col-md-6 col-xs-12">
                <div class="box box-block bg-white tile tile-1 mb-2">
                    <div class="t-icon right"><span class="bg-danger"></span><i class="ti-rocket"></i></div>
                    <div class="t-content">
                        <h6 class="text-uppercase mb-1">تعداد کل سفر ها</h6>
                        <h1 class="mb-1"><?php echo count($db_recent_trips); ?></h1>
<!--<span class="tag tag-danger mr-0-5"> 0.75% </span>
<span class="text-muted font-90">% down from cancelled Request</span>-->
</div>
</div>
</div>
<div class="col-lg-3 col-md-6 col-xs-12">
    <div class="box box-block bg-white tile tile-1 mb-2">
        <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
        <div class="t-content">
            <h6 class="text-uppercase mb-1">درامد</h6>
            <h1 class="mb-1">&#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $revenue; ?></h1>
            <i class="fa fa-caret-up text-success mr-0-5"></i><span>از <?php echo $count; ?> سفر</span>
        </div>
    </div>
</div>
<div class="col-lg-3 col-md-6 col-xs-12">
    <div class="box box-block bg-white tile tile-1 mb-2">
        <div class="t-icon right"><span class="bg-primary"></span><i class="ti-view-grid"></i></div>
        <div class="t-content">
            <h6 class="text-uppercase mb-1">انواع خودرو</h6>
            <h1 class="mb-1"><?php echo count($db_vehicle_type); ?></h1>
        </div>
    </div>
</div>
<!-- <div class="col-lg-3 col-md-6 col-xs-12">
<div class="box box-block bg-white tile tile-1 mb-2">
<div class="t-icon right"><span class="bg-warning"></span><i class="ti-archive"></i></div>
<div class="t-content">
<h6 class="text-uppercase mb-1">admin.dashboard.total_rides</h6>
<h1 class="mb-1">84</h1>
<i class="fa fa-caret-down text-danger mr-0-5"></i><span>for  0.75%  Rides</span>
</div>
</div>
</div> -->
<div class="col-lg-3 col-md-6 col-xs-12">
    <div class="box box-block bg-white tile tile-1 mb-2">
        <div class="t-icon right"><span class="bg-success"></span><i class="ti-bar-chart"></i></div>
        <div class="t-content">
            <h6 class="text-uppercase mb-1">سفرهای رزرو شده</h6>
            <h1 class="mb-1"><?php echo $scheduled_count; ?></h1>
        </div>
    </div>
</div>
</div>
<div class="row row-md">
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="box box-block bg-white tile tile-1 mb-2">
            <div class="t-icon right"><span class="bg-primary"></span><i class="ti-view-grid"></i></div>
            <div class="t-content">
                <h6 class="text-uppercase mb-1">سفرهای لغو شده</h6>
                <h1 class="mb-1"><?php echo $trips_Canceled_count; ?></h1>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="box box-block bg-white tile tile-1 mb-2">
            <div class="t-icon right"><span class="bg-danger"></span><i class="ti-bar-chart"></i></div>
            <div class="t-content">
                <h6 class="text-uppercase mb-1">سفرهای فعال</h6>
                <h1 class="mb-1"><?php echo $trips_active_count; ?></h1>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="box box-block bg-white tile tile-1 mb-2">
            <div class="t-icon right"><span class="bg-success"></span><i class="ti-user"></i></div>
            <div class="t-content">
                <h6 class="text-uppercase mb-1">راننده ها</h6>
                <h1 class="mb-1"><?php echo count($db_driver); ?></h1>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-xs-12">
        <div class="box box-block bg-white tile tile-1 mb-2">
            <div class="t-icon right"><span class="bg-warning"></span><i class="ti-rocket"></i></div>
            <div class="t-content">
                <h6 class="text-uppercase mb-1">شرکت ها</h6>
                <h1 class="mb-1"><?php echo count($db_company); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="row row-md mb-2">
    <div class="col-md-4">
        <div class="box bg-white">
            <div class="box-block clearfix">
                <h5 class="float-xs-left">جزئیات کیف پول</h5>
                <div class="float-xs-right">
                    <!-- <button class="btn btn-link btn-sm text-muted" type="button"><i class="ti-close"></i></button> -->
                </div>
            </div>
            <table class="table mb-md-0">
                <tbody>
                <tr>
                    <th scope="row">اعتبار رانندگان</th>
                    <td class="text-success">
                        &#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $db_Credit_Driver_LBL; ?></td>
                </tr>
                <tr>
                    <th scope="row">بدهی رانندگان</th>
                    <td class="text-success">
                        &#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $db_Debit_Driver_LBL; ?></td>

                </tr>

                <tr>
                    <th scope="row">اعتبار مسافرها</th>

                    <td class="text-danger">
                        &#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $db_Credit_Rider_LBL; ?></td>
                </tr>

                <tr>
                    <th scope="row">بدهی مسافرها</th>
                    <td class="text-success">
                        &#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $db_Debit_Rider_LBL; ?></td>

                </tr>
                <tr>
                    <th scope="row">کمیسیون</th>
                    <td class="text-danger">
                        &#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $db_trips_Commission_LBL; ?></td>
                </tr>
                <tr>
                    <th scope="row">تخفیف</th>
                    <td class="text-success">
                        &#1578;&#1608;&#1605;&#1575;&#1606;<?php echo $db_trips_fDiscount_LBL; ?></td>
                </tr>

                <!-- <tr>
                <th scope="row">Tips</th>
                <td class="text-danger">&#1578;&#1608;&#1605;&#1575;&#1606;0</td>
            </tr> -->
                <!-- <tr>
                <th scope="row text-right">Total</th>
                <td>&#1578;&#1608;&#1605;&#1575;&#1606;165.87</td>
            </tr> -->
                </tbody>
            </table>
        </div>
    </div>


    <div class="col-md-8">
        <div class="box bg-white">
            <div class="box-block clearfix">
                <h5 class="float-xs-left">سفرهای اخیر</h5>
                <div class="float-xs-right">
                    <button class="btn btn-link btn-sm text-muted" type="button"><i class="ti-close"></i>
                    </button>
                </div>
            </div>
            <table class="table mb-md-0">
                <tbody>

                <?php for ($i = count($db_recent_trips) - 1; $i >= 0; $i--) { ?>
                    <?php if ($db_recent_trips[$i]['iActive'] == 'Finished' || $db_recent_trips[$i]['iActive'] == 'Canceled' || $db_recent_trips[$i]['iActive'] == 'Active' || $db_recent_trips[$i]['iActive'] == 'On Going Trip') { ?>
                        <tr>
                            <th scope="row"><? $i + 1 ?></th>
                            <td><? $sql = "select * from register_user WHERE iUserId = '" . $db_recent_trips[$i]['iUserId'] . "'";
                                $db_user2 = $obj->MySQLSelect($sql);
                                ?>
                                <?php echo $db_user2[0]['vName'] . ' ' . $db_user2[0]['vLastName']; ?>
                            </td>
                            <td>
                                <a class="text-primary"
                                   href="https://k68.ir/app/admin/invoice.php?iTripId=<?php echo $db_recent_trips[$i]['iTripId']; ?>"><span
                                        class="underline">نمایش اطلاعات سفر</span></a>

                            </td>
                            <td>
							<span class="text-muted">
								<?php if ($db_recent_trips[$i]['iActive'] == 'Finished') { ?>

                                    <?php if (round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tEndDate']))) / (60 * 60)) > 0) { ?>
                                        <?php echo
                                        round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tEndDate']))) / (60 * 60));
                                        ?> ساعت گذشته
                                        <?
                                    } else { ?>
                                        <?php echo
                                        round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tEndDate']))) / (60));
                                        ?> دقیقه گذشته
                                        <?
                                    } ?>

                                    <?
                                } else { ?>
                                    <?php if ($db_recent_trips[$i]['iActive'] == 'Active') { ?>
                                        <?php if (round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tTripRequestDate']))) / (60 * 60)) > 0) { ?>
                                            <?php echo
                                            round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tTripRequestDate']))) / (60 * 60));
                                            ?> ساعت گذشته
                                            <?
                                        } else { ?>
                                            <?php echo
                                            round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tTripRequestDate']))) / (60));
                                            ?> دقیقه گذشته
                                            <?
                                        } ?>

                                        <?
                                    } else { ?>
                                        <?php if ($db_recent_trips[$i]['iActive'] == 'On Going Trip') { ?>
                                            <?php if (round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tStartDate']))) / (60 * 60)) > 0) { ?>
                                                <?php echo
                                                round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tStartDate']))) / (60 * 60));
                                                ?> ساعت گذشته
                                                <?
                                            } else { ?>
                                                <?php echo
                                                round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tStartDate']))) / (60));
                                                ?> دقیقه گذشته
                                                <?
                                            } ?>

                                            <?
                                        } else { ?>
                                            <?php if ($db_recent_trips[$i]['iActive'] == 'Canceled') { ?>
                                                <?php if (round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tTripRequestDate']))) / (60 * 60)) > 0) { ?>
                                                    <?php echo
                                                    ceil((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tTripRequestDate']))) / (60 * 60));
                                                    ?> ساعت گذشته
                                                    <?
                                                } else { ?>
                                                    <?php echo
                                                    round((abs(strtotime(date("Y-m-d H:i:s")) - strtotime($db_recent_trips[$i]['tTripRequestDate']))) / (60));
                                                    ?> دقیقه گذشته
                                                    <?
                                                } ?>
                                                <?
                                            } ?>
                                            <?
                                        } ?>
                                        <?
                                    } ?>
                                    <?
                                } ?>

																						</span>
                            </td>
                            <td>
                                <?php if ($db_recent_trips[$i]['iActive'] == 'Finished') { ?>
                                    <span class="tag tag-success">تکمیل شده</span>
                                    <?
                                } else { ?>
                                    <?php if ($db_recent_trips[$i]['iActive'] == 'Canceled') { ?>
                                        <span class="tag tag-danger">لغو شده</span>
                                        <?
                                    } else { ?>
                                        <?php if ($db_recent_trips[$i]['iActive'] == 'On Going Trip') { ?>
                                            <span class="tag tag-success">در حال سفر</span>
                                            <?
                                        } else { ?>
                                            <span class="tag tag-danger" style="background-color: #f59345;">در انتظار</span>
                                            <?
                                        } ?>
                                        <?
                                    } ?>
                                    <?
                                } ?>
                            </td>
                        </tr>
                        <?
                    }
                } ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

</div>





























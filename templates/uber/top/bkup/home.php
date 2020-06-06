<script type="text/javascript" src="assets/js/amazingcarousel.js"></script>
<script type="text/javascript" src="assets/js/initcarousel.js"></script>

<?php
	if ($_SESSION['sess_user'] == 'company') {
			$link_page="profile";
		}
	if($_SESSION['sess_user'] == 'driver'){
			$link_page="profile";
		}
	if($_SESSION['sess_user'] == 'rider'){
			$link_page="profile-rider";
		}
	?>
<!-- Second Section -->
        <div class="home-hero-page">
            <div class="home-hero-page-left" style="background: rgba(0, 0, 0, 0) url('<?php echo $tconfig["tsite_upload_page_images"].$homepage_banner["vImage"];?>') no-repeat scroll center top / cover ">
                <div class="home-hero-page-left-text"> 
                    <span>
						<?php if($_SESSION['sess_iUserId'] == ""){?>
							<a href="sign-up"><?php echo $langage_lbl['LBL_HOME_SIGN_UP'];?>&nbsp;&nbsp;<i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
							<p><?php echo $langage_lbl['LBL_HOME_DRIVER_COMPANY_TXT'];?></p>
						<?}else{?>
							 <a href="<?php echo $link_page?>"><?php echo $langage_lbl['LBL_GET_STARTED'];?>&nbsp;&nbsp;<i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
							<p><?php echo $langage_lbl['LBL_HOME_DRIVER_COMPANY_TXT_LOGIN'];?></p>
						<?}?>
                    </span> 
                </div>
            </div>
            <div class="home-hero-page-right">
                <div class="home-hero-page-right-text">
                    <span>
						<?php if($_SESSION['sess_iUserId'] == ""){?>
							<p><?php echo $langage_lbl['LBL_HOME_RIDING_TXT']; ?></p>
							<a href="sign-up-rider"> <?php echo $langage_lbl['LBL_HOME_SIGN_UP']; ?>&nbsp;&nbsp;<i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
						<?}else{?>
							<p><?php echo $langage_lbl['LBL_HOME_RIDING_TXT_LOGIN']; ?></p>
							<a href="<?php echo $link_page?>"> <?php echo $langage_lbl['LBL_GET_STARTED']; ?>&nbsp;&nbsp;<i class="fa fa-long-arrow-right" aria-hidden="true"></i></a>
						<?}?>
                    </span>
                </div>
				
            </div>
        </div>
        <!-- End: Second Section -->
        <!-- Third Section -->
        <div class="home-sldier">
            <div class="home-sldier-inner">
			<div id="amazingcarousel-container-7">
			<div id="amazingcarousel-7">
			<div class="amazingcarousel-list-container">
                <ul class="amazingcarousel-list">					
					<?php
					
						$sql = "SELECT * FROM home_screens WHERE eStatus ='Active' ORDER BY iDescOrd ASC";
						$db_data = $obj->MySQLSelect($sql);
						
						foreach($db_data as $image_detail){
						
							if(!empty($image_detail['vImageName'])){
								$filename1 = $tconfig['tsite_upload_apppage_images'].$image_detail['vImageName'];							
								if (file_exists($filename1)) {								
									
									?>
								 
									 <li class="amazingcarousel-item">
										<div class="amazingcarousel-item-container">
										<div class="amazingcarousel-image"><img src="<?php echo $tconfig['tsite_upload_apppage_images_panel'].$image_detail['vImageName'];?>" alt="<?php echo $image_detail['vImageName'];?>" /></div>
										</div>
									</li>
								 
								<?php }	
							}	
							
						} 
					?>	
					<!--<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image1['vImage']?>"  alt="<?php echo $image1['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image2['vImage']?>"  alt="<?php echo $image2['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image3['vImage']?>"  alt="<?php echo $image3['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image4['vImage']?>"  alt="<?php echo $image4['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image1['vImage']?>"  alt="<?php echo $image1['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image2['vImage']?>"  alt="<?php echo $image2['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image3['vImage']?>"  alt="<?php echo $image3['page_title']?>" /></div>
						</div>
					</li>
					<li class="amazingcarousel-item">
						<div class="amazingcarousel-item-container">
						<div class="amazingcarousel-image"><img src="assets/img/page/<?php echo $image4['vImage']?>"  alt="<?php echo $image4['page_title']?>" /></div>
						</div>
					</li> -->
                </ul>
				<div class="amazingcarousel-prev"></div>
				<div class="amazingcarousel-next"></div>
			</div>
			</div>
			</div>
		</div>
        </div>
        <div class="tap-app-ride">
            <div class="tap-app-ride-inner">
                <h2><?php echo $meta1['page_title'];?></h2>
                <?php echo $meta1['page_desc'];?>
                <div style="clear:both;"></div>
            </div>
        </div>
        <!-- End: Third Section -->
        <!-- Forth Section -->
        <div class="people-going-way">
            <div class="people-going-way-left" style="background : rgba(0, 0, 0, 0) url(<?php echo $tconfig['tsite_upload_page_images'].$meta2['vImage']?>) no-repeat scroll center top / cover ">&nbsp;</div>
            <div class="people-going-way-mid">
                <div class="people-going-way-mid-inner">
                    <h3><?php echo $meta2['page_title'];?></h3>
                    <?php echo $meta2['page_desc'];?>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="people-going-way-right" style="background : rgba(0, 0, 0, 0) url(<?php echo $tconfig['tsite_upload_page_images'].$meta2['vImage1']?>) no-repeat scroll center top / cover">&nbsp;</div>
        </div>
        <!-- End: Forth Section -->
        <!-- Fifth Section -->
        <div class="helping-cities">
            <div class="helping-cities-left" style="background : rgba(0, 0, 0, 0) url(<?php echo $tconfig['tsite_upload_page_images'].$meta3['vImage']?>) no-repeat scroll center top / cover">&nbsp;</div>
            <div class="helping-cities-mid">
                <div class="helping-cities-mid-inner">
                    <h3><?php echo $meta3['page_title'];?></h3>
                    <?php echo $meta3['page_desc'];?>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="helping-cities-right" style="background : rgba(0, 0, 0, 0) url(<?php echo $tconfig['tsite_upload_page_images'].$meta3['vImage1']?>) no-repeat scroll center top / cover">&nbsp;</div>
        </div>
        <!-- End: Fifth Section -->
        <!-- Sixth Section -->
        <div class="Safety-people">
            <div class="Safety-people-left">&nbsp;</div>
            <div class="Safety-people-right">
                <div class="Safety-people-right-inner">
                    <div class="Safety-people-text">
                        <h3><?php echo $meta4['page_title'];?></h3>
                    <?php echo $meta4['page_desc'];?>
                    </div>
                    <div class="Safety-people-img"><img src="<?php echo $tconfig['tsite_upload_page_images'].$meta4['vImage']?>" alt=""></div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
        <!-- End: Sixth Section -->
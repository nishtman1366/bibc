<div class="footer">
    <div class="footer-inner">
        <div class="footer-top-part">
            <div class="footer-box1">
                <div class="lang">
                    <select name="sess_language" id="sess_language" onchange="change_lang(this.value);" class="custom-select-new1">
                    <?php
                    $sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active'";
                    $db_lng_mst=$obj->MySQLSelect($sql);
                    foreach ($db_lng_mst as $key => $value) {
                        echo '
                            <option value="'.$value['vCode'].'"'.($_SESSION['sess_lang']==$value['vCode']?'selected':'').'>'.$value['vTitle'].'</option>
                        ';
                    }
                    ?>
            </select>
                </div>
                      <span>
                     
                    <a style="font-size:18px"href="https://t.me/savar_app"><i  class="fa fa-telegram"></i></a>
                 
                    <a style="font-size:18px" href="https://www.instagram.com/savar.ir/"><i class="fa fa-instagram"></i></a>
                    <a style="font-size:18px" href="mail to:info@savar.ir"><i class="fa fa-email"></i></a>
                   

 </span>  
            </div>
            <div class="footer-box2">
                <ul>
                    <li><a href="http://savar.ir/how/"><?php echo $langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="http://savar.ir/safety_ride/"><?php echo $langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>
                    <li><a href="http://savar.ir/driver_terms/"><?php echo $langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>
					
                    <!-- <li><a href="#">Blogs</a></li> -->
                </ul>
                <ul>
                    <li><a href="http://savar.ir/about-us/"><?php echo $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                    <li><a href="http://savar.ir/contact-us/"><?php echo $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
     
                    <li><a href="http://savar.ir/terms/"><?php echo $langage_lbl['LBL_LEGAL']; ?></a></li>
                </ul>
            </div>

            <div class="footer-box3"> 
                <span>
                    <a href="<?php echo $ANDROID_APP_LINK?>"><img src="assets/img/app-stor-img.png" alt=""></a>
                </span> 
                <span>
                    <a href="<?php echo $IPHONE_APP_LINK?>"><img src="assets/img/google-play-img.png" alt=""></a>
                </span> 
            </div>
        </div>
        <div class="footer-bottom-part"> 
            <span>&copy; <?php echo  $COPYRIGHT_TEXT ?></span>
            <!--<p><?php echo $langage_lbl['LBL_WEBSITE_DESIGN_AND_DEVELOPED_BY']; ?>: <a href="http://v3cube.com" target="_blank">v3cube.com</a></p>-->
        </div>
        <div style=" clear:both;"></div>
    </div>
</div>
<script>
function change_lang(lang){
    document.location='common.php?lang='+lang;
}
</script>


<script type="text/javascript">
    $(document).ready(function(){
        $(".custom-select-new1").each(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'>"+selectedOption+"</em>");
        });
        $(".custom-select-new1").change(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        });
    })
</script>

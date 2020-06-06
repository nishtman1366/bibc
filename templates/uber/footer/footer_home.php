<div class="footer">
  <div class="footer-top-part">
        <div class="footer-inner">
            <div class="footer-box1">
               <!-- <div class="lang" id="lang_open">
                <b><a href="javascript:void(0);"><?php echo $langage_lbl['LBL_LANGUAGE_SELECT']; ?></a></b>
                </div>
                <div class="lang-all" id="lang_box">
                    <ul>
                    <?php
                    $sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
                    $db_lng_mst=$obj->MySQLSelect($sql);
                    foreach ($db_lng_mst as $key => $value) {
                        $status_lang = "";
                        if($_SESSION['sess_lang']==$value['vCode']) {
                            $status_lang = "active";
                        } ?>
                    <li onclick="change_lang(this.id);" id="<?php echo $value['vCode']; ?>"><a href="javascript:void(0);" class="<?php echo $status_lang; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></a></li>
                    <?php } ?>
                   <li><a href="contact-us" ><?php echo $langage_lbl['LBL_LANG_NOT_FIND']; ?></a></li>
                    </ul>
                    </div>-->
             <span>

                    <a style="font-size:18px"href="https://t.me/k68"><i  class="fa fa-telegram"></i></a>

                    <a style="font-size:18px" href="https://www.instagram.com/par30taxigolestan"><i class="fa fa-instagram"></i></a>
                    <a style="font-size:18px" href="mail to:info@k68.ir"><i class="fa fa-email"></i></a>


 </span>

            </div>
            <div class="footer-box2">
                <ul>
                    <li><a href="http://k68.ir/%DA%86%DA%AF%D9%88%D9%86%D9%87-%D8%B3%D9%81%D8%B1-%DA%A9%D9%86%DB%8C%D8%AF/"><?php echo $langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="http://k68.ir/ghavanin/"><?php echo $langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>
                    <li><a href="http://k68.ir/driver_terms"><?php echo $langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>

                    <!-- <li><a href="#">Blogs</a></li> -->
                </ul>
                <ul>
                    <li><a href="http://k68.ir/about-me/"><?php echo $langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                    <li><a href="http://k68.ir/contact/"><?php echo $langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>

                    <li><a href="http://k68.ir/%D8%B4%D8%B1%D8%A7%DB%8C%D8%B7-%D9%88-%D9%82%D9%88%D8%A7%D9%86%DB%8C%D9%86-%D9%85%D8%B3%D8%A7%D9%81%D8%B1%DB%8C%D9%86/"><?php echo $langage_lbl['LBL_LEGAL']; ?></a></li>
                </ul>
            </div>
            <div class="footer-box3">

            </div>
            <div style="clear:both;"></div>
            </div>
        </div>
        <div class="footer-bottom-part">
                <div class="footer-inner">
            <span>&copy; <?php echo  $COPYRIGHT_TEXT ?></span>
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
		$("#lang_box").hide();
		$("#lang_open").click(function(){
			$("#lang_box").slideToggle();
		});
    })
</script>
<?php echo $GOOGLE_ANALYTICS;?>

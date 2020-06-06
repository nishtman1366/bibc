<?php
function PHPASSISTANT_CLEAN ($voroodisqlinject) {
  if($voroodisqlinject != "")
  {
     $voroodisqlinject = @trim($voroodisqlinject);
if (get_magic_quotes_gpc()) {
$voroodisqlinject = stripslashes($voroodisqlinject);
   }
 return addslashes($voroodisqlinject);
}
  }
?>

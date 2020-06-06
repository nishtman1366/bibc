<?php 
error_reporting(E_ALL);
/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */
//error_reporting(E_ALL);
ob_start();
session_start();


include_once('common.php');
require_once ( TPATH_BASE .DS.'assets'.DS.'libraries'.DS.'facebook.php' ); 
//var_dump($_SESSION);
//die();
// Create our Application instance (replace this with your appId and secret).
$sql="SELECT vValue FROM configurations WHERE vName='FACEBOOK_APP_ID'";
$db_appid=$obj->MySQLSelect($sql);

$sql="SELECT vValue FROM configurations WHERE vName='FACEBOOK_APP_SECRET_KEY'";
$db_key=$obj->MySQLSelect($sql);

$facebook = new Facebook(array(
    'appId'  => $db_appid[0]['vValue'],
    'secret' => $db_key[0]['vValue'],
));


include_once($tconfig["tsite_libraries_v"]."/Imagecrop.class.php");
$thumb = new thumbnail();
$temp_gallery = $tconfig["tsite_temp_gallery"];

include_once($tconfig["tsite_libraries_v"]."/SimpleImage.class.php");
$img = new SimpleImage();    

// Get User ID
$user = $facebook->getUser();

// We may or may not have this data based on whether the user is logged in.
//
// If we have a $user id here, it means we know the user is logged into
// Facebook, but we don't know if the access token is valid. An access
// token is invalid if the user logged out of Facebook.

//exit;
$ctype=$_REQUEST['ctype'];

if($ctype == ''){
    $ctype = "fblogin";
}

if($ctype == "fblogin"){
    if ($user) {
        try {
       
        // Proceed knowing you have a logged in user who's authenticated.
            $user_profile = $facebook->api('/me?fields=id,picture,first_name,last_name,email,location,hometown,gender');
            $db_user = array();
    		if($user_profile['email'] !=Null) {
                $sql = "SELECT iUserId,vImgName FROM register_user WHERE vEmail='".$user_profile['email']."' and eStatus != 'Deleted'";
    			$db_user = $obj->MySQLSelect($sql);
    		}

            if(count($db_user) > 0){
             
                $_SESSION['sess_iMemberId']=$db_user[0]['iUserId'];
                $_SESSION['sess_iUserId'] =$db_user[0]['iUserId'];
                $_SESSION["sess_vFirstName"]= isset($user_profile['first_name'])?ucfirst($user_profile['first_name']):'';
                $_SESSION["sess_vLastName"]= isset($user_profile['last_name'])?ucfirst($user_profile['last_name']):'';
                $_SESSION["sess_vEmail"]= isset($user_profile['email'])?$user_profile['email']:'';
                $_SESSION["sess_eGender"]= isset($user_profile['gender'])?$user_profile['gender']:'';

                $Photo_Gallery_folder =$tconfig["tsite_upload_images_passenger_path"]."/".$_SESSION['sess_iMemberId']."/";
    		
                unlink($Photo_Gallery_folder.$db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder."1_".$db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder."2_".$db_user[0]['vImgName']);
                unlink($Photo_Gallery_folder."3_".$db_user[0]['vImgName']);   
                unlink($Photo_Gallery_folder."4_".$db_user[0]['vImgName']);   
            
                if(!is_dir($Photo_Gallery_folder)) {                  
                    mkdir($Photo_Gallery_folder, 0777);
        	    }
    	              
                $baseurl =  "http://graph.facebook.com/".$user."/picture?type=large";
                $url = $user.".jpg";
                $image_name = system("wget --no-check-certificate -O ".$Photo_Gallery_folder.$url." ".$baseurl);
            
                if(is_file($Photo_Gallery_folder.$url)) {
             
                list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$url);           
                if($width < $height){
                    $final_width = $width;
                }else{
                    $final_width = $height;
                }       
                $img->load($Photo_Gallery_folder.$url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$url);
                $imgname = $generalobj->img_data_upload($Photo_Gallery_folder,$url,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],""); 
                }  
            
                $sql = "UPDATE register_user set vFbId='".$user."', vImgName='".$imgname."',eGender='".$_SESSION['sess_eGender']."' WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
                $obj->sql_query($sql); 

                $db_sql = "select * from register_user WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
                $db_data = $obj->MySQLSelect($db_sql);
                $_SESSION["sess_vImage"]= $db_data[0]['vImgName'];  
                $_SESSION["sess_user"]= 'rider';   

                if(SITE_TYPE=='Demo'){
                  $login_sql = "insert into member_log (iMemberId, eMemberType, eMemberLoginType,vIP) VALUES ('".$_SESSION["sess_iUserId"]."', 'Passenger', 'WebLogin','".$_SERVER['REMOTE_ADDR']."')";
                  $obj->sql_query($login_sql);
                }
            
                $link = $tconfig["tsite_url"]."profile_rider.php";
			
                header("Location:".$link);
                exit;
    
            }else{

              $sql = "select * from currency where eDefault = 'Yes'";
              $db_curr = $obj->MySQLSelect($sql);

              $curr = $db_curr[0]['vName'];

              $sql = "select * from language_master where eDefault = 'Yes'";
              $db_lang = $obj->MySQLSelect($sql);

              $lang = $db_lang[0]['vCode'];
			  $eReftype = "Rider";
			  $refercode = $generalobj->ganaraterefercode($eReftype);
			  $dRefDate  = Date('Y-m-d H:i:s');	
            if($user_profile['email'] != "") {

             $sql = "insert INTO register_user (vFbId,vName, vLastName, vEmail, eStatus,vImgName,eGender,vLang,vCurrencyPassenger,vRefCode,dRefDate) VALUES ('".$user."','".$user_profile['first_name']."', '".$user_profile['last_name']."', '".$user_profile['email']."', 'Active','".$user_profile['picture']['data']['url']."','".$user_profile['gender']."','".$lang."','".$curr."','".$refercode."','".$dRefDate."')";
              
                $iUserId =$obj->MySQLInsert($sql);

            } else {
               $sql = "INSERT INTO register_user (iFBId, vImgName, vName, vLastName, vEmail,eStatus,eGender,vLang,vCurrencyPassenger,vRefCode,dRefDate) VALUES ('".$user."','".$user_photo['picture']['data']['url']."', '".$user_profile['first_name']."', '".$user_profile['last_name']."', '".$user_profile['email']."','Active','".$user_profile['gender']."','".$lang."','".$curr."','".$refercode."','".$dRefDate."')";
                $iUserId =  $obj->MySQLInsert($sql);
            }
           
            $_SESSION['sess_iMemberId']= $iUserId ;
            $_SESSION['sess_iUserId'] =  $_SESSION['sess_iMemberId'] ;
            $_SESSION["sess_vFirstName"]=$user_profile['first_name'];
            $_SESSION["sess_vLastName"]=$user_profile['last_name'];
            $_SESSION["sess_vEmail"]=$user_profile['email'];  
            $_SESSION["sess_eGender"]=$user_profile['gender'];
            $_SESSION["sess_user"]= 'rider';   

    	   $Photo_Gallery_folder = $tconfig["tsite_upload_images_passenger_path"]."/". $iUserId . '/';
		   
    	   @unlink($Photo_Gallery_folder.$db_user[0]['vImgName']);
    	   @unlink($Photo_Gallery_folder."1_".$db_user[0]['vImgName']);
    	   @unlink($Photo_Gallery_folder."2_".$db_user[0]['vImgName']);
    	   @unlink($Photo_Gallery_folder."3_".$db_user[0]['vImgName']);   
    	   @unlink($Photo_Gallery_folder."4_".$db_user[0]['vImgName']);   
    
            if(!is_dir($Photo_Gallery_folder))
            {
                mkdir($Photo_Gallery_folder, 0777);
            }
  
            $baseurl =  "http://graph.facebook.com/".$user."/picture?type=large";
            $url = $user.".jpg";
            system("wget --no-check-certificate -O ".$Photo_Gallery_folder.$url." ".$baseurl);
          
            if(is_file($Photo_Gallery_folder.$url)) {
             
                list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$url);           
                if($width < $height){
                    $final_width = $width;
                }else{
                    $final_width = $height;
                }       
                $img->load($Photo_Gallery_folder.$url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$url);
                $imgname = $generalobj->img_data_upload($Photo_Gallery_folder,$url,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],""); 
                //$imgname = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");

            }  
 		     
            $sql = "UPDATE register_user set  vImgName='".$imgname."' WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
            $obj->sql_query($sql); 
            
             $db_sql = "select * from register_user WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
             $db_data = $obj->MySQLSelect($db_sql);
             $_SESSION["sess_vImage"]= $db_data[0]['vImage'];
             $_SESSION["sess_eGender"]=$db_data[0]['eGender'];
             
             
             
             /*$Data_not['iUserId'] = $id;
             $Data_not['eSuccessPublish'] = 'Yes';
             $Data_not['eSuccessUpdate'] = 'Yes';
             $Data_not['ePrivateMessage'] = 'Yes';
             $Data_not['eRatePassenger'] = 'Yes';
             $Data_not['eNewRating'] = 'Yes';
             $Data_not['eOtherInformation'] = 'Yes';
             $Data_not['dAddedDate'] = date("Y-m-d H:i:s");*/
            // $id_not = $obj->MySQLQueryPerform("member_email_notification",$Data_not,'insert');
            $link = $tconfig["tsite_url"]."profile_rider.php";
            //echo $link;
            header("Location:".$link);
            exit;
        }
      } catch (FacebookApiException $e) { 
        #print_r($e);exit;
        error_log($e);
        $user = null;
      }
    
    }
}

if($ctype == "fbphoto"){
    if ($user) {
    
      try {
        // Proceed knowing you have a logged in user who's authenticated.
       $user_profile = $facebook->api('/me?fields=id,picture');
    
        $sql = "SELECT iUserId,vImage FROM register_user WHERE vEmail='".$user_profile['email']."'";
        $db_user = $obj->MySQLSelect($sql);
    		
		 $Photo_Gallery_folder = $tconfig["tpanel_path"]."webimages/upload/documents/passenger/". $iUserId . '/';
		
	    @unlink($Photo_Gallery_folder.$db_user[0]['vImage']);
		  
		        
        /*$baseurl =  "http://graph.facebook.com/".$user."/picture?type=large";
        $url = $user.".jpg";
        $image_name =  system("wget -O ".$Photo_Gallery_folder.$url." ".$baseurl);*/
        if(!is_dir($Photo_Gallery_folder))
        {
	      mkdir($Photo_Gallery_folder, 0777);
	    }
	              
        $baseurl =  "http://graph.facebook.com/".$user."/picture?type=large";
        $url = $user.".jpg";
        $image_name =  system("wget --no-check-certificate -O ".$Photo_Gallery_folder.$url." ".$baseurl);
        
        if(is_file($Photo_Gallery_folder.$url))
        {
           include_once(TPATH_LIBRARIES."/SimpleImage.class.php");
           $img = new SimpleImage();           
           list($width, $height, $type, $attr)= getimagesize($Photo_Gallery_folder.$url);           
  
           if($width < $height){
              $final_width = $width;
           }else{
              $final_width = $height;
           }       
           $img->load($Photo_Gallery_folder.$url)->crop(0, 0, $final_width, $final_width)->save($Photo_Gallery_folder.$url);
           //$imgname = $generalobj->img_data_upload($Photo_Gallery_folder,$url,$Photo_Gallery_folder, $tconfig["tsite_upload_images_member_size1"], $tconfig["tsite_upload_images_member_size2"], $tconfig["tsite_upload_images_member_size3"],"");  
		    $imgname = $generalobj->fileupload($Photo_Gallery_folder,$image_object,$image_name,$prefix='', $vaildExt="pdf,doc,docx,jpg,jpeg,gif,png");
        }  
         @unlink($Photo_Gallery_folder.$url);
         
         $sql = "UPDATE register_user set vImage='".$imgname."' WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
         $obj->sql_query($sql); 
        
         $db_sql = "select * from register_user WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
         $db_data = $obj->MySQLSelect($db_sql);
         $_SESSION["sess_vImage"]= $db_data[0]['vImage'];		
        
        
	     header("Location:".$tconfig["tsite_url"]."profile-photo");
         exit;
    

      } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
      }
    
    }
}

if($ctype == "fbsocial"){

    if ($user) {
    
      try {
        // Proceed knowing you have a logged in user who's authenticated.
       $user_profile = $facebook->api('/me?fields=id,picture,username');
      
	     $fbusername= $user_profile['username'];
     
         $sql = "UPDATE register_user set iFBId='".$user."',vFBUsername='".$fbusername."' WHERE iUserId='".$_SESSION['sess_iMemberId']."'";
         $obj->sql_query($sql); 
              
         header("Location:social-sharrings");
         exit;
    
      } catch (FacebookApiException $e) {
        error_log($e);
        $user = null;
      }
    
    }

}

//echo $tconfig["tsite_url"].'/fbconnect.php?ctype='.$ctype;
//die();
// Login or logout url will be needed depending on current user state.
if ($user) {
  $logoutUrl = $facebook->getLogoutUrl();
  $user_friends = $facebook->api('/me/friends');
  //$friends_count = count($data['data']);
} else {
    $params = array(
      'scope' => 'email',
      'redirect_uri'=>$tconfig["tsite_url"].'fbconnect.php?ctype='.$ctype
    );
  $loginUrl = $facebook->getLoginUrl($params);
  header("Location:".$loginUrl);
  exit;  
}

?>





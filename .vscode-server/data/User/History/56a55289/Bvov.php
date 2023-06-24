<?php

if(isset($_POST['delete'])){
  $current_otp = ORM::for_table($config['db']['pre'].'otp')->where('phone', $find_otp['phone'])->find_many();
      foreach($current_otp as $myotp){
        $myotp->delete();
      }
      echo json_encode($current_otp);
      exit;
}
if(isset($_POST['send'])){
  $otp ='';
  foreach($_POST as $key=> $post){
    if($key == 'send' || $key == 'ref'){
      unset($_POST['send']);
      unset($_POST['ref']);
      $otp = implode($_POST);
    }
    if($post == ''){
      unset($_POST);
      HtmlTemplate::display('global/otp',array(
        'error'=> __("Please Reinput OTP")
      ));
      exit;
    }
  }

  $redirect_url = get_option('after_login_link');
  if(empty($redirect_url)){
      $redirect_url = $link['DASHBOARD'];
  }

  $ref = isset($_GET['ref'])? $_GET['ref'] : $redirect_url;
  $find_otp = ORM::for_table($config['db']['pre'].'otp')->where('otp_num', $otp)->find_one();
  $find_user = ORM::for_table($config['db']['pre'].'user')->where('phone', $find_otp['phone'])->find_one();
  $countOTP = ORM::for_table($config['db']['pre'].'otp')->where('otp_num', $find_otp['otp_num'])->count();
  if($countOTP <= 0){
    HtmlTemplate::display('global/otp',array(
      'ref' => $ref,
      'error'=> __("Incorrectly Entered OTP Code")
    ));
  }else{
    if(empty($find_user['id'])){
      HtmlTemplate::display('global/otp',array(
        'ref' => $ref,
        'error'=> __("User Not Found !")
      ));
    }else{

      $current_otp = ORM::for_table($config['db']['pre'].'otp')->where('phone', $find_otp['phone'])->find_many();
      foreach($current_otp as $myotp){
        $myotp->delete();
      }

      create_user_session($_SESSION['otp']['id'],$_SESSION['otp']['username'],$find_otp['phone'], $_SESSION['otp']['password'],$_SESSION['otp']['user_type']);
      update_lastactive();

      header("Location: " . $ref);

    }

  }


}


HtmlTemplate::display('global/otp');
exit;
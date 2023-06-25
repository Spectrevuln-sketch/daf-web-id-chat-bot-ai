<?php

if(checkloggedin())
{
    header("Location: ".$config['site_url']."dashboard");
    exit;
}


if(isset($_POST['forgot']))
{
    $_GET['forgot'] = $_POST['forgot'];
}
if(isset($_POST['r']))
{
    $_GET['r'] = $_POST['r'];
}
if(isset($_POST['e']))
{
    $_GET['e'] = $_POST['e'];
}
if(isset($_POST['t']))
{
    $_GET['t'] = $_POST['t'];
}

if(isset($_GET['ref']))
{
    $_GET['ref'] = htmlentities($_GET['ref']);
}
if(isset($_POST['ref']))
{
    $_POST['ref'] = htmlentities($_POST['ref']);
}

if(isset($_GET['r']))
{
    $_GET['r'] = htmlentities($_GET['r']);
}
if(isset($_POST['r']))
{
    $_POST['r'] = htmlentities($_POST['r']);
}

if(isset($_GET['t']))
{
    $_GET['t'] = htmlentities($_GET['t']);
}
if(isset($_POST['r']))
{
    $_POST['t'] = htmlentities($_POST['t']);
}

if(isset($_GET['e']))
{
    $_GET['e'] = htmlentities($_GET['e']);
}
if(isset($_POST['r']))
{
    $_POST['e'] = htmlentities($_POST['e']);
}

// Check if they are using a forgot password link
if(isset($_GET['forgot']))
{
    if(!isset($_GET['start']))
    {
        $check_forgot1 = ORM::for_table($config['db']['pre'].'user')
            ->select_many('id', 'forgot', 'username')
            ->where('email', $_GET['e'])
            ->find_one();

        if($_GET['forgot'] == $check_forgot1->forgot)
        {
            if($_GET['forgot'] == md5($_GET['t'].'_:_'.$_GET['r'].'_:_'.$_GET['e']))
            {
                // Check that the link hasn't timed out (30 minutes old)
                if($_GET['t'] > (time()-108000))
                {
                    $forgot_error = '';

                    if(isset($_POST['password']))
                    {
                        if( (strlen($_POST['password']) < 4) OR (strlen($_POST['password']) > 16) )
                        {
                            $forgot_error = __("Password must be between 4 and 20 characters long");
                        }
                        else
                        {
                            if($_POST['password'] == $_POST['password2'])
                            {
                                $password = $_POST["password"];
                                $pass_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 13]);

                                $forgot_update = ORM::for_table($config['db']['pre'].'user')->find_one($check_forgot1->id);
                                $forgot_update->set('forgot', '');
                                $forgot_update->set('password_hash', $pass_hash);
                                $forgot_update->save();

                                message(__("Success"),__("Your password has been changed, please go to login."), 'login');
                                exit;
                            }
                            else
                            {
                                $forgot_error = __("The passwords you entered did not match");
                            }
                        }
                    }

                    //Print Template 'Forgot Page'
                    HtmlTemplate::display('global/forgot', array(
                        'field_forgot' => $_GET['forgot'],
                        'field_r' => $_GET['r'],
                        'field_e' => $_GET['e'],
                        'field_t' => $_GET['t'],
                        'username' => $check_forgot1->username,
                        'forgot_error' => $forgot_error
                    ));
                }
                else
                {
                    $login_error = __("Forgot Password code has expired");
                }
            }
            else
            {
                $login_error = __("Invalid Forgot Password code");
            }
        }
        else
        {
            $login_error = __("Invalid Forgot Password code");
        }
    }
    //Print Template
    HtmlTemplate::display('global/login', array(
        'error' => $login_error
    ));
    exit;
}

// Check if they are trying to retrieve their email
if(isset($_POST['phone']))
{
    $redirect_url = get_option('after_login_link');
    if(empty($redirect_url)){
        $redirect_url = $link['LOGIN'];
    }
    $ref = isset($_GET['ref'])? $_GET['ref'] : $redirect_url;
    $phone ='';
    if(preg_match('/^[0]/', $_POST['phone']) == false){
        if(preg_match('/^(62)/', $_POST['phone'])){
            var_dump(preg_replace('/^(62)/','0', $_POST['phone']));
            $phone = preg_replace('/^(62)/','0', $_POST['phone']);
        }
        $phone =  '0'.$_POST['phone'];
    }else{
        $phone =  $_POST['phone'];
    }
    var_dump($_POST['phone']);
    var_dump($phone);

    // Lookup the email address
    $phone_info1 = check_phone_exists($phone);

    // Check if the email address exists
    if($phone_info1 != 0)
    {


        $phone_userid = get_user_id_by_phone($phone);
        // Send the email
        // send_forgot_email($_POST['email'],$email_userid);

        $user_current = ORM::for_table($config['db']['pre'].'user')->where('phone', $phone)->find_one();
        // Generate new Password and update
        $rand_string = rand_str();
        $pass_hash = password_hash($rand_string, PASSWORD_DEFAULT, ['cost' => 13]);
        $user_current->set([
            'password_hash'=> $pass_hash
        ]);
        $user_current->save();

        $waMessage = "Reset Password!

        This is your new password.

        ========== Credentials ==========

        Account Number: ".$user_current['phone']."
        Password: *".$rand_string."*

        ==============================
        Please change the password after login to secure your account.

        Thank you!";
        $sendWa = send_forgot_wa($user_current, $waMessage);
        unset($_POST['phone']);


        $login_error = '';
        // create_otp_session($sendWa['phone'], $sendWa['expired'], $user_current['id'],$user_current['username'],$user_current['password'],$user_current['user_type']);
        //Print Template
        HtmlTemplate::display('global/forgot_form', array(
            'success' => __("Please check your phone for the forgot password details"),
            'login_error' => $login_error,
            'ref' => $ref,
            'success_send_wa' => true
        ));
        // header("refresh:5;Location: " . $ref);
        exit;

    }
    else
    {
        $success = '';
        //Print Template
        HtmlTemplate::display('global/forgot_form', array(
            'success' => $success,
            'login_error' => __("Phone number does not exist")
        ));
        exit;
    }
}

if(isset($_GET['fstart']))
{
    $success = '';
    $login_error = '';
    //Print Template
    HtmlTemplate::display('global/forgot_form', array(
        'success' => $success,
        'login_error' => $login_error
    ));
    exit;
}

$redirect_url = get_option('after_login_link');
if(empty($redirect_url)){
    $redirect_url = $link['OTP'];
}

if(!isset($_POST['submit'])) {
    if(!isset($_GET['ref'])) {
        $_GET['ref'] = $redirect_url;
    }
    $error = '';
    //Print Template
    HtmlTemplate::display('global/login', array(
        'ref' => $_GET['ref'],
        'error' => $error
    ));
}
else
{
    $loggedin = userlogin($_POST['username'], $_POST['password']);

if(!is_array($loggedin))
    {

        $error = __("Username or Password not found");
        //Print Template
        HtmlTemplate::display('global/login', array(
            'ref' => $ref,
            'error' => $error
        ));
    }
    elseif($loggedin['status'] == 2)
    {
        $error = __("This account has been banned");
        //Print Template
        HtmlTemplate::display('global/login', array(
            'ref' => $ref,
            'error' => $error
        ));
    }else{
        $ref = isset($_GET['ref'])? $_GET['ref'] : $redirect_url;
        $user_current = ORM::for_table($config['db']['pre'].'user')->where('username', $_POST['username'])->find_one();
        $current_otp = ORM::for_table($config['db']['pre'].'otp')->where('phone', $user_current['phone'])->find_many();
        foreach($current_otp as $myotp){
          $myotp->delete();
        }
        $key='23bbc54a27dc91438a6f4b615a37754e9f85980d7ae241de';
        $phone_number = str_replace('-','', $user_current['phone']);
        $otp_code = random_int(100000, 999999);
        $expired = date('Y-m-d H:i:s', strtotime('+2 minutes'));

        $payload= [
            "phone_no"  => "+62".(int)$phone_number."" ,
            "key"       => $key,
            "message"   => 'Masukan kode otp berikut *'.$otp_code.'*',
            "skip_link" => True //
        ];
        $waSend = consume_third_party('http://116.203.191.58/api/send_message', 'POST', $auth_token=false, $token='', $payload);
        $insert_otp = ORM::for_table($config['db']['pre'].'otp')->create();
        $insert_otp->phone= $user_current['phone'];
        $insert_otp->otp_num= $otp_code;
        $insert_otp->expired= $expired;
        $insert_otp->save();
        create_otp_session($insert_otp['phone'], $insert_otp['expired'],$loggedin['id'],$loggedin['username'],$loggedin['password'],$loggedin['user_type']);
        header("Location: " . $ref);

    }



    // if(!is_array($loggedin))
    // {

    //     $error = __("Username or Password not found");
    //     //Print Template
    //     HtmlTemplate::display('global/login', array(
    //         'ref' => $ref,
    //         'error' => $error
    //     ));
    // }
    // elseif($loggedin['status'] == 2)
    // {
    //     $error = __("This account has been banned");
    //     //Print Template
    //     HtmlTemplate::display('global/login', array(
    //         'ref' => $ref,
    //         'error' => $error
    //     ));
    // }
    // else
    // {
    //     create_user_session($loggedin['id'],$loggedin['username'],$loggedin['password'],$loggedin['user_type']);
    //     update_lastactive();

    //     header("Location: " . $ref);
    // }
}
?>

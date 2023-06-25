<?php
overall_header(__("Forgot Password?"));
?>
<style>
    .country-number{
        font-style: normal;
    }
</style>
<div id="titlebar">
    <!-- <div class="container"> -->
        <!-- <div class="row"> -->
            <!-- <div class="col-md-12"> -->
                <!-- <h2><?php _e("Forgot Password?") ?></h2> -->
                <!-- Breadcrumbs -->
                <!-- <nav id="breadcrumbs" class="dark">
                    <ul>
                        <li><a href="<?php url("INDEX") ?>"><?php _e("Home") ?></a></li>
                        <li><?php _e("Forgot Password?") ?></li>
                    </ul>
                </nav> -->

            <!-- </div> -->
        <!-- </div> -->
    <!-- </div> -->
</div>
<div class="container">
    <div class="row">
        <div class="col-xl-5 offset-xl-3">
            <div class="login-register-page">
                <!-- Welcome Text -->
                <div class="welcome-text">
                    <h3><?php _e("Forgot Password?") ?></h3>
                </div>
                <?php
                if($success != ''){
                    echo '<span class="status-available">'.__("Confirmation mail sent.").'<br>
                        '._esc($success,false).'
                    </span>';
                }
                if($login_error != ''){
                    echo '<span class="status-not-available">'._esc($login_error,false).'</span>';
                }
                ?>
                <form method="post">
                    <div class="input-with-icon-left">
                        <i class="country-number">62</i>
                        <input type="number" class="input-text with-border" name="phone" id="phone"
                        placeholder="<?php _e("Phone Number") ?>" required/>
                    </div>
                    <button class="button full-width button-sliding-icon ripple-effect margin-top-10" name="submit" type="submit"><?php _e("Request Password") ?> <i class="icon-feather-arrow-right"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<div class="margin-top-70"></div>
<!-- <?php if($success_send_wa == true) :?> -->
        <?= var_dump($ref); ?>
<!-- <?php endif; ?> -->
<?php
overall_footer();
?>

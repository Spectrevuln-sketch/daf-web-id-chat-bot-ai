
<?php
require_once '../../includes.php';

$fetchuser = ORM::for_table($config['db']['pre'].'user')->find_one($_GET['id']);
$serializeBot=[];
$fetch_chat_bot = ORM::for_table($config['db']['pre'].'ai_chat_bots')->where('active', 1)->find_many();
if($fetchuser['chat_bot_id'] != null){

    $id_bots = unserialize($fetchuser['chat_bot_id']);
    foreach($id_bots as $id_bot){
        $arrBotData = ORM::for_table($config['db']['pre'].'ai_chat_bots')->where([
            'active'=> 1,
            'id'=> $id_bot
            ])->find_one();
            array_push($serializeBot, $arrBotData);

}
}

$fetchusername  = $fetchuser['username'];
$fetchuserpic     = $fetchuser['image'];
if($fetchuserpic == "")
    $fetchuserpic = "default_user.png";

?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<div class="slidePanel-content">
    <header class="slidePanel-header">

        <div class="slidePanel-overlay-panel">
            <div class="slidePanel-heading">
                <h2><?php _e('Edit User') ?></h2>
            </div>
            <div class="slidePanel-actions">
                <button id="post_sidePanel_data" class="btn-icon btn-primary" title="<?php _e('Save') ?>">
                    <i class="icon-feather-check"></i>
                </button>
                <button class="btn-icon slidePanel-close" title="<?php _e('Close') ?>">
                    <i class="icon-feather-x"></i>
                </button>
            </div>
        </div>
    </header>
    <div class="slidePanel-inner">
        <div id="post_error"></div>
        <form method="post" data-ajax-action="addEditUser" id="sidePanel_form">
            <div class="form-body">
                <input type="hidden" name="id" value="<?php _esc($_GET['id']);?>">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-2">
                            <img src="<?php _esc(SITEURL.'/storage/profile/'.$fetchuserpic); ?>" width="80" class="rounded" alt="">
                        </div>
                        <div class="col-md-10">
                            <label for="image"><?php _e("Profile Picture"); ?></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="image" name="image"
                                       accept="image/png, image/gif, image/jpeg">
                                <label class="custom-file-label" for="image"><?php _e("Choose file"); ?>...</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_fullname"><?php _e("Full Name"); ?></label>
                    <input id="id_fullname" type="text" class="form-control" name="name" value="<?php _esc($fetchuser['name']);?>">
                </div>
                <div class="form-group">
                    <label><?php _e("Status"); ?></label>
                    <select class="form-control" name="status">
                        <option value="0" <?php echo ($fetchuser['status'] == "0")? "selected" : "" ?>><?php _e("Active"); ?></option>
                        <option value="1" <?php echo ($fetchuser['status'] == "1")? "selected" : "" ?>><?php _e("Verify"); ?></option>
                        <option value="2" <?php echo ($fetchuser['status'] == "2")? "selected" : "" ?>><?php _e("Ban"); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e("Gender"); ?></label>
                    <select class="form-control" name="sex">
                        <option value="Male" <?php if($fetchuser['sex'] == "Male") { echo "selected"; }?>><?php _e("Male"); ?></option>
                        <option value="Female" <?php if($fetchuser['sex'] == "Female") { echo "selected"; }?>><?php _e("Female"); ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e("Country"); ?></label>
                    <select class="form-control" name="country">
                        <?php $country = get_country_list($fetchuser['country']);
                        foreach ($country as $value){
                            echo '<option value="'.$value['asciiname'].'" '.$value['selected'].'>'.$value['asciiname'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e("About Us"); ?></label>
                    <textarea name="about" rows="6" class="form-control tiny-editor" id="pageContent"><?php _esc($fetchuser['description']);?></textarea>
                </div>
                <h5 class="text-primary m-t-35"><?php _e("User Membership"); ?></h5>
                <hr>
                <div class="form-group">
                    <label><?php _e("Current Plan"); ?></label>
                    <select class="form-control" name="current_plan">
                        <option value="free" <?php echo ($fetchuser['group_id'] == "free")? "selected" : "" ?>><?php _e("Free"); ?></option>
                        <option value="trial" <?php echo ($fetchuser['group_id'] == "trial")? "selected" : "" ?>><?php _e("Trial"); ?></option>
                        <?php $rows = ORM::for_table($config['db']['pre'].'plans')
                            ->where('status', '1')
                            ->find_many();;
                        foreach ($rows as $row){
                            $selected = ($fetchuser['group_id'] == $row['id'])? "selected" : "";
                            echo '<option value="'.$row['id'].'" '.$selected.'>'.$row['name'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e("Trial Done"); ?></label>
                    <select class="form-control" name="plan_trial_done">
                        <option value="1" <?php echo get_user_option($_GET['id'], 'package_trial_done',0) == '1'?'selected':''; ?>><?php _e("Yes"); ?></option>
                        <option value="0" <?php echo get_user_option($_GET['id'], 'package_trial_done',0) == '0'?'selected':''; ?>><?php _e("No"); ?></option>
                    </select>
                </div>

                <div class="form-group plan_expiration_date">
                    <label for="id_exdate"><?php _e("Expiration Date"); ?></label>
                    <?php
                    $upgrades = ORM::for_table($config['db']['pre'].'upgrades')
                        ->select('upgrade_expires')
                        ->where('user_id',$_GET['id'])
                        ->find_one();
                    $default_expiration = date('Y-m-d', isset($upgrades['upgrade_expires'])?$upgrades['upgrade_expires']:time());
                    ?>
                    <input id="id_exdate" type="date" class="form-control" name="plan_expiration_date" value="<?php _esc($default_expiration); ?>">
                </div>
                <h5 class="text-primary m-t-35"><?php _e("Account Setting"); ?></h5>
                <hr>
                <div class="form-group">
                    <label><?php _e("Chat Bot AI"); ?></label>
                    <select id="chat_bot" class="form-control" name="chat_bot[]" multiple="multiple">
                    <?php foreach($fetch_chat_bot as $chat_bot): ?>
                        <?php if(in_array($chat_bot['id'], $serializeBot)): ?>
                            <option value="<?= $chat_bot['id']; ?>" selected><?= $chat_bot['name']; ?></option>
                            <?php else: ?>
                            <option value="<?= $chat_bot['id']; ?>"><?= $chat_bot['name']; ?></option>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_uname"><?php _e("Username"); ?></label>
                    <input id="id_uname" type="text" class="form-control" name="username" value="<?php _esc($fetchuser['username']);?>">
                </div>
                <div class="form-group">
                    <label for="id_email"><?php _e("Email address"); ?></label>
                    <input id="id_email" type="email" class="form-control" name="email" value="<?php _esc($fetchuser['email']);?>">
                </div>
                <div class="form-group">
                    <label for="id_pw2"><?php _e("New Password"); ?></label>
                    <input id="id_pw2" type="password" class="form-control" placeholder="<?php _e("New Password"); ?>" name="password">
                </div>
                <input type="hidden" name="submit">
            </div>

        </form>
    </div>
</div>
<script>
       $("#chat_bot").select2({

        placeholder: "Pilih Chatbot"

        });
    $('[name="current_plan"]').off().on('change',function () {
        if($(this).val() == 'free'){
            $('.plan_expiration_date').slideUp();
        }else{
            $('.plan_expiration_date').slideDown();
        }
    }).trigger('change');
</script>
<script src="../assets/plugins/tinymce/tinymce.min.js"></script>
<script src="../assets/js/script.js"></script>
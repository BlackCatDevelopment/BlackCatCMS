    <div class="content-20-80  gap-1">
        <div class="card">
            <div class="card-header">
                <?= CAT_Object::lang()->translate('Modify user'); ?>
            </div>
            <div class="card-main">
<?php
    if(count($data['users'])>0):
        foreach($data['users'] as $user):
?>
                <a href="" class="list-item"><?= $user['USER_NAME'] ?></a>
<?php
        endforeach;
    endif;
?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <?= CAT_Object::lang()->translate('Add user'); ?>
            </div>
            <div class="card-main">
<?php
    if(isset($data['errors']) && count($data['errors'])>0):
?>
    <div class="alert alert-error icon-alert">
<?php
        foreach($data['errors'] as $e):
?>
            <?= $e ?><br />
<?php
        endforeach;
?>
    </div>
<?php
    endif;
?>
                <form action="<?= CAT_ADMIN_URL ?>/users/add.php" method="post" enctype="multipart/form-data" class="two-column">
                    <input type="hidden" name="username_fieldname" id="username_fieldname" value="<?= $data['USERNAME_FIELDNAME'] ?>" />

                    <label for="<?= $data['USERNAME_FIELDNAME'] ?>" title="<?= $data['NEWUSERHINT'][0] ?>"><?= CAT_Object::lang()->translate('Username') ?></label>
                    <input type="text" name="<?= $data['USERNAME_FIELDNAME'] ?>" id="<?= $data['USERNAME_FIELDNAME'] ?>" maxlength="255" value="<?= $data['formdata']['username'] ?? '' ?>" placeholder="<?= CAT_Object::lang()->translate('Unique username'); ?>" />

                    <label for="display_name"><?= CAT_Object::lang()->translate('Display name') ?></label>
                    <input type="text" name="display_name" id="display_name" maxlength="255" value="<?= $data['formdata']['display_name'] ?? '' ?>" placeholder="<?= CAT_Object::lang()->translate('Optional display name'); ?>" />

                    <label for="email"><?= translate('Email') ?></label>
			        <input type="text" name="email" id="email" maxlength="255" value="<?= $data['formdata']['email'] ?? '' ?>" placeholder="<?= translate('Valid email address'); ?>" />

                    <label for="avatar"><?= translate('Avatar') ?></label>
                    <div>
                        <?= translate('Upload picture') ?>
                        <label for="avatar"><img src="http://via.placeholder.com/75" id="avatarpreview" title="<?= translate('Click here to upload') ?>" /></label>
			            <input type="file" accept="image/*" name="avatar" id="avatar" value="" />
<?php
    $dir = implode(DIRECTORY_SEPARATOR, [CAT_PATH, MEDIA_DIRECTORY, ".avatars"]);
    $pics = CAT_Helper_Directory::scanDirectory($dir, true, true, $dir.'/', [
        "jpg",
    ]);
    if($pics):
?>
                        - <?= translate('or') ?> -
                        <select id="avatarselector" name="avatarselector" title="<?= translate('Choose from existing') ?>">
                            <option value="">[<?= translate('Choose from existing') ?>]</option>
<?php
        foreach($pics as $pic):
?>
                            <option value="<?= $pic ?>"><?= $pic ?></option>
<?php
        endforeach;
?>
                        </select>
<?php
    endif;
?>
                    </div>

				    <?= translate('Activate user') ?>
                    <div class="slide">
                        <input type="checkbox" name="active" id="active" value="1"<?php if(!isset($data['formdata']['active']) || $data['formdata']['active']==1): ?> checked="checked"<?php endif; ?> />
                        <label for="active"></label>
                    </div>

        			<?php if(isset($data['HOME_FOLDERS']) && $data['HOME_FOLDERS'] === true): ?>
        			<label for="home_folder"><?= translate('Home folder') ?></label>
        			<select name="home_folder" id="home_folder">
        				<option value=""><?= translate('None') ?></option>
                        <?php foreach($data['home_folders'] as $homefolder): ?>
        				<option value="<?= $homefolder['FOLDER'] ?>"><?= $homefolder['NAME'] ?></option>
        				<?php endforeach; ?>
        			</select>
                    <?php endif; ?>

                    <hr class="grid-1-span-2" />

        			<div class="password_notification grid-1-span-2 alert alert-info hidden">
        				<?= translate('Please note: You should only enter values in those fields if you wish to change this users password') ?>
        			</div>

       				<label for="password"><?= translate('Password') ?></label>
       				<input type="password" name="password" id="password" value="" />

       				<label for="password2"><?= translate('Retype password') ?></label>
       				<input type="password" name="password2" id="password2" value="" />

                    <span title="<?= translate('Recommended: The user must change his password the next time he logs in.') ?>"><?= translate('One-time password') ?></span>
                    <div class="slide">
           				<input type="checkbox" name="otp" id="otp" value="1"<?php if(!isset($data['formdata']['otp']) || $data['formdata']['otp']==1): ?> checked="checked"<?php endif; ?> />
           				<label for="otp"></label>
                    </div>

                    <hr class="grid-1-span-2" />

	                <div class="grid-1-span-2 heading">
                        {translate('Groups')}
                    </div>
    				<div class="group-notification grid-1-span-2 alert alert-info">
                        <?= translate('You need to choose at least one group') ?></span><br />
                    </div>
                    <?php foreach($data['groups']['viewers'] as $group): ?>
                    <div><?= $group['NAME'] ?></div>
                    <div class="slide">
    				    <input type="checkbox" name="groups[]" id="group_<?= $group['VALUE'] ?>" value="<?= $group['VALUE'] ?>"<?php if($group['VALUE']==1 && !$data['is_admin']): ?> disabled="disabled"<?php endif; ?><?php if(isset($data['formdata']['groups']) && in_array($group['VALUE'],$data['formdata']['groups'])): ?> checked="checked"<?php endif; ?> />
    				    <label for="group_<?= $group['VALUE'] ?>"></label>
                    </div>
    				<?php endforeach; ?>

                    <hr class="grid-1-span-2" />

                    <input class="button button-active" type="submit" name="submit" value="<?= translate('Save') ?>" />
                    <input class="button" type="reset" name="reset_user" value="<?= translate('Reset') ?>" />
                </form>
            </div>
        </div>
    </div>

<?php
echo "FILE [",__FILE__,"] FUNC [",__FUNCTION__,"] LINE [",__LINE__,"]<br /><textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
print_r($data);
echo "</textarea><br />";
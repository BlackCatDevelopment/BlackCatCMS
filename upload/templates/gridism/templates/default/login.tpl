<?php
    $credit = 'Image by <a href="https://pixabay.com/users/briam-cute-11667349/?utm_source=link-attribution&amp;utm_medium=referral&amp;utm_campaign=image&amp;utm_content=4945658">Briam Cute</a> from <a href="https://pixabay.com//?utm_source=link-attribution&amp;utm_medium=referral&amp;utm_campaign=image&amp;utm_content=4945658">Pixabay</a>';
    $image = 'cats-ge35bda4c7_1280.png';
    # get random image
    $filename = __DIR__.'/../../images/loginscreen/credits.csv';
    $images = [];
    $f = fopen($filename, 'r');
    if ($f === false) {
    	#die('Cannot open the file ' . $filename);
    } else {
        while (($row = fgetcsv($f)) !== false) {
    	   $images[] = $row;
        }
        fclose($f);
        if(count($images)>0) {
            $elem = array_rand($images);
        }
        $credit = $images[$elem][1];
        $image = $images[$elem][0];
    }
    
?>
<!DOCTYPE html>
<html lang="<?= $data['LANGUAGE'] ?>">
<head>
    <?php require CAT_PATH.'/framework/functions.php'; get_page_headers( "backend", true, "login") ?>
</head>
<body>
    <div class="login-form-container">
        <div class="login-form">
            <div class="login-form-image" style="background-image: url(<?= CAT_URL ?>/templates/gridism/images/loginscreen/<?= $image ?>);">
                <div class="login-form-image-attribution">
                    <?= $credit ?>
                </div>
            </div>
            <div class="login-form-form">
                <div style="display:flex;width:100%;margin-bottom:1em;">
                    <div class="header" id="login-header"><?= translate('Sign in') ?></div>
                    <a href="<?= CAT_URL ?>" class="icon-home" id="home" title="<?= translate('Home') ?>"></a>
                    <a href="<?= CAT_URL ?>" class="icon-key" id="forgot" title="<?= translate('Forgot password') ?>"></a>
                </div>
                <div class="login-form-login open" id="login-form-login">
                    <form name="login" action="<?= $data['ACTION_URL'] ?>" method="post" class="one-column">
    					<input type="hidden" name="username_fieldname" value="<?= $data['USERNAME_FIELDNAME'] ?>" />
    					<input type="hidden" name="password_fieldname" value="<?= $data['PASSWORD_FIELDNAME'] ?>" />
                        <input type="hidden" name="cookie_fieldname" value="<?= $data['COOKIE_FIELDNAME'] ?>" />
<?php if($data['MESSAGE']): ?>
                        <div class="alert alert-error">
                            <?= $data['MESSAGE'] ?>
                        </div>
<?php endif; ?>
    					<label for="login_username"><?= translate('Username') ?> <span class="icon icon-info-circle" title="<?= translate('Required') ?>"></span></label>
    					<input required="required" type="text" maxlength="<?= $data['MAX_USERNAME_LEN'] ?>" name="<?= $data['USERNAME_FIELDNAME'] ?>" value="<?= $data['USERNAME'] ?>" id="<?= $data['USERNAME_FIELDNAME'] ?>" />

                        <label for="login_password"><?= translate('Password') ?> <span class="icon icon-info-circle" title="<?= translate('Required') ?>"></span></label>
                        <input required="required" type="password" maxlength="<?= $data['MAX_PASSWORD_LEN'] ?>" name="<?= $data['PASSWORD_FIELDNAME'] ?>" id="<?= $data['PASSWORD_FIELDNAME'] ?>" />

                        <div id="otp-fields" style="display:<?php if(isset($data['otp']) && $data['otp']): ?>grid<?php else: ?>none<?php endif; ?>">
                            <p class="alert alert-info">
                                <?= translate('Please enter your initial password as well as your new chosen password'); ?>
                            </p>
                            <label for="login_password1"><?= translate('New password') ?> <span class="icon icon-info-circle" title="<?= translate('Required') ?>"></span></label>
                            <input type="password" maxlength="<?= $data['MAX_PASSWORD_LEN'] ?>" name="<?= $data['PASSWORD_FIELDNAME'].'_1' ?>" id="<?= $data['PASSWORD_FIELDNAME'].'_1' ?>" />

                            <label for="login_password2"><?= translate('Retype new password') ?> <span class="icon icon-info-circle" title="<?= translate('Required') ?>"></span></label>
                            <input type="password" maxlength="<?= $data['MAX_PASSWORD_LEN'] ?>" name="<?= $data['PASSWORD_FIELDNAME'].'_2' ?>" id="<?= $data['PASSWORD_FIELDNAME'].'_2' ?>" />
                        </div>

                        <p class="alert alert-info" title="<?= translate('Required') ?>">
                            <?= translate('A technical cookie is required for backend login.') ?><br />
                            <label><input required="required" type="checkbox" name="<?= $data['COOKIE_FIELDNAME'] ?>" id="<?= $data['COOKIE_FIELDNAME'] ?>" style="vertical-align:bottom" />
                            <?= translate('allow') ?></label>
                        </p>

                        <button type="submit" name="submit_login" class=""> {translate('Login')}</button>
                    </form>
                </div>
                <div class="login-form-forgot closed" id="login-form-forgot" aria-hidden="true">
                	<form name="forgot_pass" action="{$CAT_ADMIN_URL}/login/forgot/ajax_forgot.php" method="post" class="one-column">
    					<label for="forgot">{translate('Email')} <span class="icon icon-info-circle" title="<?= translate('Required') ?>"></span></label>
    					<input type="text" maxlength="255" name="email" value="<?= $data['EMAIL'] ?? '' ?>" id="forgot" />
    					<button type="submit" name="submit_email" class=""> {translate('Send details')}</button>
            		</form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
    //<![CDATA[
    const signin_header = "<?= translate('Sign in') ?>";
    const forgot_header = "<?= translate('Forgot password') ?>";
    const header_div = document.getElementById("login-header");
    const loginf = document.getElementById("login-form-login");
    const forgotf = document.getElementById("login-form-forgot");


    function toggle_form() {
        if(header_div) {
            if(header_div.innerHTML == signin_header) {
                header_div.innerHTML = forgot_header;
            } else {
                header_div.innerHTML = signin_header;
            }
        }
        // toggle hidden class
        let vis = loginf.classList.contains("closed") ? forgotf : loginf;
        let invis = (vis == forgotf) ? loginf : forgotf;
        vis.classList.remove("open");
        vis.classList.add("closed");
        vis.setAttribute('aria-hidden', 'true');
        setTimeout(function() {
            invis.classList.add("open");
            invis.classList.remove("closed");
            invis.removeAttribute("aria-hidden");
        }, 100);
    }
    const username = document.getElementById("<?= $data['USERNAME_FIELDNAME'] ?>");
    username.addEventListener("change", e => {
        fetch("<?= CAT_ADMIN_URL ?>/login/ajax_check_otp.php?username="+e.target.value, {method: "GET"})
            .then(response => response.json())
            .then(data => {
                if(data.otp==1) {
                    let div = document.getElementById("otp-fields");
                    if(div) {
                        div.style.display = "grid";
                    }
                }
            })
            .catch(error => {
                console.log("fetch error",error);
                // handle the error
            });
    });
    const forgot = document.getElementById("forgot");
    forgot.addEventListener("click", e => {
        e.preventDefault();
        e.target.classList.toggle("highlight");
        toggle_form();
    });
    //]]>
    </script>
</body>
</html>

<?php
echo "FILE [",__FILE__,"] FUNC [",__FUNCTION__,"] LINE [",__LINE__,"]<br /><textarea style=\"width:100%;height:200px;color:#000;background-color:#fff;\">";
print_r($data);
echo "</textarea><br />";
?>
<?php

define('CAT_LOGIN_PHASE',1);

require __DIR__.'/../../framework/class_secure_header.php';

header('Content-type: application/json');

$result = [
    'success' => false,
    'otp' => false
];

$username = filter_input(INPUT_GET,'username',FILTER_SANITIZE_SPECIAL_CHARS);

if(CAT_Users::checkUsernameExists($username)===true) {
    $result['otp'] = CAT_Users::checkOTP($username);
    $result['success'] = true;
}

echo json_encode($result);
exit;
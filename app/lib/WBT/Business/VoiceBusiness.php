<?php

namespace WBT\Business;

require_once 'Cloopen/REST_API.php';


/**
 * 语音呼叫相关的业务
 */class VoiceBusiness
{
    public static function call( $phoneNo, $message = "" )
    {
        $main_account = 'aaf98fda40816c6901409ee05327059a';
        $main_token   = '0172a9e5fe4d49629c8d22c310a446ff';
        $app_id       = 'aaf98f894081692201409ee3837905ab';
        $sub_account  = 'aaf98f894081692201409ee3838205ac';
        $rest         = new \REST($main_account, $main_token, $app_id);

        $result = $rest->LandingCall( $phoneNo, $message, 3 );

        log_info("[VOICE_CALL][phone:$phoneNo][message:$message][result:$result]");
    }

    public static function codeCall( $phoneNo, $verifyCode )
    {
        $main_account = 'aaf98fda40816c6901409ee05327059a';
        $main_token   = '0172a9e5fe4d49629c8d22c310a446ff';
        $app_id       = 'aaf98f894081692201409ee3837905ab';
        $sub_account  = 'aaf98f894081692201409ee3838205ac';
        $rest         = new \REST($main_account, $main_token, $app_id);

        $result = $rest->VoiceVerifyCode( $verifyCode, 3, $phoneNo, $respUrl= null );

        log_info("[VOICE_CALL][phone:$phoneNo][message:$verifyCode][result:$result]");
    }
}

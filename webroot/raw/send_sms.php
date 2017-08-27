<?php
require_once '../../lib/Bluefin/bluefin.php';

require_once 'Cloopen/REST_API.php';


use Bluefin\App;

process();


// 如果是多个号码，号码之间用逗号分开
function send($phone, $msg)
{
    // 创建REST对象实例
    $main_account = 'aaf98fda40816c6901409ee05327059a';
    $main_token = '0172a9e5fe4d49629c8d22c310a446ff';
    $app_id = 'aaf98fda40e7483d0140e94e831e0026';
    $sub_account = '0000000040df770f0140f27e510f04b9';
    $rest = new \REST($main_account,$main_token,$app_id);

    //0（普通短信）、1（长短信）
    $msgType =  1 ;

    // 发送短信
    $result = $rest->SendSMS($phone,$msg,$msgType,$sub_account);
    log_debug("sms_result:", $result);
    if($result == NULL )
    {
        log_error("send sms failed. result =null");
        return false;
    }
    // 解析XML
    $xml = simplexml_load_string(trim($result," \t\n\r"));
    if($xml->statusCode!=0)
    {
        log_error( "error code :" . $xml->statusCode);
        return false;
    }
    else
    {
        return true;
    }
}

function process()
{
    log_debug('post data received');
    $token = $_GET['token'];
    if ($token != 'ffadksfads2kfal45dwiewf5kad78v23xk')
    {
        log_error('token error');
        exit;
    }

    $phone = $_POST['phone'];
    $msg = $_POST['msg'];

    fastcgi_finish_request();

    send($phone, $msg);
}


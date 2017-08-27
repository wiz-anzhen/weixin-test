<?php

namespace WBT\Business;
use Snoopy\Snoopy;

//require_once 'Cloopen/REST_API.php';
require_once 'Cloopen/CCPRestSDK.php';

class SmsBusiness
{
    public static function sendBySinaSae($mobile, $msg)
    {
        $res = array('errno' => 0);
        $snoopy = new Snoopy();
        //todo : 部分内容写到配置文件中
        define('SMS_API_URL','http://weibotuisms.sinaapp.com/send.php');
        define('SMS_TOKEN','ffadksfads2kfal45dwiewf5kad78v23xk');

        $url = SMS_API_URL . '?token=' . SMS_TOKEN . '&mobile=' . $mobile . '&msg=' . $msg; // todo : url zhuanma
        // echo $url;
        // return json_decode(file_get_contents($url));

        $submit_vars = array();
        $submit_vars['mobile'] = $mobile;
        $submit_vars['msg'] = $msg;
        $submit_vars['token'] = SMS_TOKEN;

        if($snoopy->submit(SMS_API_URL, $submit_vars))
        {
            $response = trim($snoopy->results);

            $response = (array)json_decode($response);
            if(isset($response['errno']))
            {
                $res = $response;
            }else
            {
                $res['errno'] = 2;
                $res['error'] = 'sms api response format error.';
            }
        }else
        {
            $res['errno'] = 2;
            $res['error'] = 'sms api  error.';
        }
        log_info("[SMS_SEND][errno:{$res['errno']}][mobile:$mobile][msg:$msg]");

        return $res;
    }

    public static function send($mobile, $msg)
    {
        return self::sendBySinaSae($mobile, $msg);

        /*  $res = array('errno' => 0);
          $host = get_host();

          if($host == 'http://:')
          {
              $host = 'http://canyin.weibotui.com';
          }
          $postUrl = $host . '/raw/send_sms.php?token=ffadksfads2kfal45dwiewf5kad78v23xk';
          _curl_post($postUrl, [ 'phone' => $mobile, 'msg'  => $msg, ]);
          log_info("SEND_SMS.[phone:$mobile][msg:$msg]");
          return $res;
        */
    }

    public static function sendSMS($mobile, $msg)
    {
        return self::sendBySinaSae($mobile, $msg);
        /*
        $res = array('errno' => 0);
        $postUrl = 'http://canyin.weibotui.com/raw/send_sms.php?token=ffadksfads2kfal45dwiewf5kad78v23xk';
        _curl_post($postUrl, [ 'phone' => $mobile, 'msg'  => $msg, ]);
        log_info("SEND_SMS.[phone:$mobile][msg:$msg]");
        return $res;
        */
    }

    public static function createSubAccount($subAccountName)
    {
        // 创建REST对象实例
        $main_account = 'aaf98fda40816c6901409ee05327059a';
        $main_token = '0172a9e5fe4d49629c8d22c310a446ff';
        $app_id = '0000000041a55b8f0141a6372e55004d';
        $rest = new \REST($main_account,$main_token,$app_id);


        $result = $rest->CreateSubAccount($subAccountName);
        echo $result;
    }






    function  sendTemplateSMS($to,$datas,$verifyCodeID)
    {
        // 初始化REST SDK
        //global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
        //主帐号
        $accountSid = 'aaf98fda40816c6901409ee05327059a';
        //主帐号Token
        $accountToken= '0172a9e5fe4d49629c8d22c310a446ff';
        //应用Id
        $appId='8a48b5514767145d01476cb4da740236';
        //请求地址，格式如下，不需要写https://
        $serverIP='sandboxapp.cloopen.com';
        //请求端口
        $serverPort='8883';
        //REST版本号
        $softVersion='2013-12-26';
        $rest = new \REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);
        // 发送模板短信
        $res = array('errno' => 0);
        $result = $rest->sendTemplateSMS($to,$datas,$verifyCodeID);
        if($result == NULL ) {
            $res['errno'] = 1;
            $res['error'] = 'result error!';
            return $res;
        }
        if($result->statusCode!=0) {
            $res['errno'] = 1;
            $res['error'] = 'error code :'.$result->statusCode . "error msg :" . $result->statusMsg;
            return $res;
            //TODO 添加错误处理逻辑
        }else{
            //TODO 添加成功处理逻辑
            return $res;
        }

    }

    public static function sendTemplate($phone, $verifyCode,$verifyCodeID)
    {
        return self::sendTemplateSMS($phone, array($verifyCode),$verifyCodeID);
    }
}
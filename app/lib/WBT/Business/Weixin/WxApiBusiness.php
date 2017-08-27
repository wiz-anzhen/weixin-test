<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\MpUser;
use WBT\Business\ConfigBusiness;

class WxApiBusiness extends BaseBusiness
{

    private  static function getToken($mpUserID)
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $appID = $mpUser->getAppID();
        $appSecret = $mpUser->getAppSecret();

        $url =  'https://api.weixin.qq.com/cgi-bin/token';

        $submit_vars = array();
        $submit_vars['grant_type'] = 'client_credential';
        $submit_vars['appid']      = $appID;
        $submit_vars['secret']     = $appSecret;
        log_debug('vars==============',$submit_vars);
        $result = _curl_post($url, $submit_vars);

        $result = (array)json_decode($result);
        if (isset($result['access_token']))
        {
            $accessToken = $result['access_token'];
            log_debug("[accessToken:$accessToken]");
            return $accessToken;
        }
        log_warn("result = ", $result);

        return false;
    }

    public static function sentTextMessage($mpUserID, $wxUserID, $text)
    {
        $accessToken = self::getAccessToken($mpUserID);

        if(empty($accessToken))
        {
            return false;
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='. $accessToken;

        $content['touser'] = $wxUserID;
        $content['msgtype'] = 'text';
        $content['text']['content'] = $text;

        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $res = _curl_post($url, $content);
        $res = (array)json_decode($res);
        if($res && ($res['errcode'] == 0 || $res['errcode'] == 45015))
        {
            return true;
        }

        log_warn("[mpUserID:$mpUserID][wxUserID:$wxUserID]res = ", $res);

        return false;
    }
//发送图片给用户
    public static function sentImgMessage($mpUserID, $wxUserID, $filePath)
    {
        $accessToken = self::getAccessToken($mpUserID);

        if(empty($accessToken))
        {
            return false;
        }
        //获取mediaID
        $type = "image";
        $filePath = explode(".com/",$filePath);
        $fileData = array("media" => "@".$filePath[1]);
        log_debug("000000000000000",$filePath);
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=".$accessToken."&type=".$type;
        $res = _curl_post($url, $fileData);
        $res = (array)json_decode($res);
        log_debug("000000000000000999999999",$res);
        if($res['errcode'] == 40006)
        {
            return false;
        }

        //发送图片给用户
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='. $accessToken;
        $content['touser'] = $wxUserID;
        $content['msgtype'] = $type;
        $content['image']['media_id'] = $res["media_id"];

        $content = json_encode($content, JSON_UNESCAPED_UNICODE);
        $res = _curl_post($url, $content);
        $res = (array)json_decode($res);
        if($res && ($res['errcode'] == 0 || $res['errcode'] == 45015))
        {
            return true;
        }

        log_warn("[mpUserID:$mpUserID][wxUserID:$wxUserID]res = ", $res);

        return false;
    }

    //获取语音消息地址
    public static function getVoiceImgMessage($mpUserID,$mediaID)
    {
        $accessToken = self::getAccessToken($mpUserID);

        if(empty($accessToken))
        {
            return false;
        }
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$accessToken."&media_id=".$mediaID;
        $fileInfo = self::downLoadWeiXinFile($url);
        $userMessage = self::saveWeiXinFile($fileInfo["body"]);
        log_debug("======================".$userMessage);
        return $userMessage;
    }
//下载微信音频文件
    public static function downLoadWeiXinFile($url)
    {
       $ch = curl_init($url);
       curl_setopt($ch ,CURLOPT_HEADER,0);
       curl_setopt($ch ,CURLOPT_NOBODY,0);
       curl_setopt($ch ,CURLOPT_SSL_VERIFYHOST,FALSE);
       curl_setopt($ch ,CURLOPT_SSL_VERIFYPEER,FALSE);
       curl_setopt($ch ,CURLOPT_RETURNTRANSFER,1);
       $package= curl_exec($ch);
       $httpInfo = curl_getinfo($ch);
       curl_close($ch);
       $voice = array_merge(array("header" => $httpInfo),array("body" => $package));
       return $voice;

    }
//保存微信音频文件
    public static function saveWeiXinFile($fileInfo)
    {
        $date_time = date("Ymd");
        $upload_dir =  WEB_ROOT."/ueditor/php/voice_date";
        if(!is_dir($upload_dir))
        {
            mkdir($upload_dir , 0755);
        }
        $upload_dir= $upload_dir."/".$date_time;
        if(!is_dir($upload_dir)){
            mkdir($upload_dir , 0755);
        }
        date_default_timezone_set('PRC');
        $timStamp = date("His");
        $rand = sprintf("%06d",rand());
        $task_no =  $timStamp.$rand;
        $toFile = $upload_dir.'/' .$task_no.'.'. "amr";
        log_debug("======================".$toFile);
        $localFile = fopen($toFile,"w");
        if(false !== $localFile)
        {
            if(false !== fwrite($localFile,$fileInfo))
            {
                fclose($localFile);
            }
        }
        $voiceUrl = explode("webroot/",$toFile);
        $host = \WBT\Business\ConfigBusiness::getHost();
        $voiceUrl = $host."/".$voiceUrl[1];
        return $voiceUrl;
    }

    public static function getHeadImgUrl($mpUserID,$wxUserID,&$nickname,&$city,&$province,&$gender)
    {
        $accessToken = self::getAccessToken($mpUserID);

        if(empty($accessToken))
        {
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=".$wxUserID."&lang=zh_CN";
        $res = _curl_get($url);
        $res = (array)json_decode($res);

        if(!strict_in_array("errcode",$res))
        {
                $nickname = $res['nickname'];
                $city = $res['city'];
                $province = $res['province'];
                if($res['sex'] == 1)
                {
                    $gender = "男";
                }
                else
                {
                    $gender = "女";
                 }
                return $res['headimgurl'];

        }
        else
        {
            log_warn("[mpUserID:$mpUserID][wxUserID:$wxUserID]res = ", $res);
            return false;
        }

    }

    public static function sentDeliverNotify($mpUserID,$postData)
    {
        $accessToken = self::getAccessToken($mpUserID);

        if(empty($accessToken))
        {
            return false;
        }
        $url = "https://api.weixin.qq.com/pay/delivernotify?access_token=".$accessToken;
        $postData = json_encode($postData, JSON_UNESCAPED_UNICODE);
        $res = _curl_post($url, $postData);
        $res = (array)json_decode($res);
        if($res && ($res['errcode'] == 0))
        {
            return true;
        }
        log_debug("[mpUserID:$mpUserID]res = ", $res);
        return false;
    }

    public static function sentTemplateMessage($mpUserID,$postData)
    {
        $accessToken = self::getAccessToken($mpUserID);
        log_debug('accessToken==================================',$accessToken);
        if(empty($accessToken))
        {
            return false;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$accessToken;
        $postData = urldecode(json_encode($postData, JSON_UNESCAPED_UNICODE));
        $res = _curl_post($url, $postData);
        $res = (array)json_decode($res);
        if($res && ($res['errcode'] == 0))
        {
            return true;
        }
        log_debug("[mpUserID:$mpUserID]res = ", $res);
        return false;
    }

    public static function sentFeedBack($mpUserID,$wxUserId,$feedBackId)
    {
        $accessToken = self::getAccessToken($mpUserID);

        if(empty($accessToken))
        {
            return false;
        }
        $url = "https://api.weixin.qq.com/payfeedback/update?access_token=" . $accessToken . "&openid=" .$wxUserId ."&feedbackid=".$feedBackId;
        $res = _curl_get($url);
        $res = (array)json_decode($res);
        if($res && ($res['errcode'] == 0))
        {
            return true;
        }
        log_debug("[mpUserID:$mpUserID]res = ", $res);
        return false;
    }

    public static function getAccessToken($mpUserID)
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID=>$mpUserID]);
        //获取当前时间戳
        $currentTime = time();
        $accessToken = $mpUser->getAccessToken();
        if(!empty($accessToken))
        {
            $accessTokenUpdateTime = $mpUser->getAccessTokenUpdateTime();
            $diff = $currentTime-$accessTokenUpdateTime;
            // 微信token的有效时间是2小时，我们这里每1小时更新一次
            if($diff > 3600)
            {
                $accessToken = self::getToken($mpUserID);
                $mpUser->setAccessToken($accessToken)
                       ->setAccessTokenUpdateTime($currentTime)
                       ->update();
                return $accessToken;
            }
            else
            {
                return $accessToken;
            }
        }
        else
        {
            $accessToken = self::getToken($mpUserID);
            $mpUser->setAccessToken($accessToken)
                   ->setAccessTokenUpdateTime($currentTime)
                   ->update();
            return $accessToken;
        }
    }

    public static function getCode($mpUserID,$backUrl)
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID=>$mpUserID]);
        $appID = $mpUser->getAppID();
        $redirectUri = sprintf("%s/wx_user/getwd/getcode",ConfigBusiness::getHost());
        $redirectUri = urlencode($redirectUri);
        $state = $mpUserID . '|' . $backUrl  ;
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appID.'&redirect_uri='.$redirectUri.'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';
        header("Location:".$url);
    }

    public static function getWxUserID($code,$mpUserID)
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID=>$mpUserID]);
        $appID = $mpUser->getAppID();
        $secret = $mpUser->getAppSecret();
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appID.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
        $res = _curl_get($url);
        $res = (array)json_decode($res);

        if(!strict_in_array("errcode",$res))
        {
            if(!empty($res['openid']))
            {
                return $res['openid'];
            }
        }

        log_warn("[mpUserID:$mpUserID][code:$code]res = ", $res);
        return false;

    }


}
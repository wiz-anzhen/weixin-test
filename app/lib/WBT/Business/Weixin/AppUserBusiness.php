<?php

namespace WBT\Business\Weixin;

use Bluefin\App;
use Bluefin\Data\Database;
use Bluefin\Data\DbClauseOr;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\ConfigBusiness;
use WBT\Business\SmsBusiness;
use WBT\Business\VoiceBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\AppUser;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUserExt;

// 普通微信用户相关的一些操作
class AppUserBusiness extends BaseBusiness
{
    public static function generateVipNo( MpUser &$mpUser )
    {

        $maxVipNO   = $mpUser->getMaxVipNo() + 1;
        $maxVipNO = jump4and7($maxVipNO);

        $vipNo = $maxVipNO . self::getVipNoRandNumber();
        $vipNo = intval( $vipNo );
        $mpUser->setMaxVipNo( $maxVipNO )->save();
        return $vipNo;
    }


    // 生成点会员号的随机部分
    public static function getVipNoRandNumber()
    {
        $str='01235689'; // 餐馆忌讳4、7
        $r1 = rand( 0, 7 );
        $r2 = rand( 0, 7 );
        $r3 = rand( 0, 7 );

        return $str[$r1] . $str[$r2] . $str[$r3];
    }

    // 在用户微信浏览器植入cookie，以便使用二维码积分功能
    public static function setUserCookie($mpUserID, $wxUserID)
    {
        $key = 'fcrm' . $mpUserID;
        $value = $wxUserID;
        $path = '/';
        $expire = time() + 3600 * 24 * 30 * 3; // cookie 过期时间是三个月
        $ret = setcookie($key, $value, $expire, $path);
        log_debug("COOKIE [key:$key][value:$value][ret:$ret]");
    }


    public static function getUserCookie($mpUserID)
    {
        log_debug('server', $_SERVER);
        $value = null;
        $key = 'fcrm' . $mpUserID;
        if (isset($_COOKIE[$key]))
        {
            $value = $_COOKIE[$key];
        }
        log_debug("COOKIE [key:$key][value:$value]");


        return $value;
    }


    public static function setCookieNick($mpUserID, $nick)
    {
        $key = 'nick' . $mpUserID;
        $value = $nick;
        $path = '/';
        $expire = time() + 3600 * 24 * 30 * 12; // cookie 过期时间是1年
        $ret = setcookie($key, $value, $expire, $path);
        log_debug("COOKIE [key:$key][value:$value][ret:$ret]");
    }


    public static function getCookieNick($mpUserID)
    {
        log_debug('server', $_SERVER);
        $value = null;
        $key = 'nick' . $mpUserID;
        if (isset($_COOKIE[$key]))
        {
            $value = $_COOKIE[$key];
        }
        log_debug("COOKIE [key:$key][value:$value]");

        return $value;
    }


    public static function getWxUserIP()
    {
        if ($_SERVER["HTTP_X_FORWARDED_FOR"])
        {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        elseif ($_SERVER["HTTP_CLIENT_IP"])
        {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        elseif ($_SERVER["REMOTE_ADDR"])
        {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        elseif (getenv("HTTP_X_FORWARDED_FOR"))
        {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }
        elseif (getenv("HTTP_CLIENT_IP"))
        {
            $ip = getenv("HTTP_CLIENT_IP");
        }
        elseif (getenv("REMOTE_ADDR"))
        {
            $ip = getenv("REMOTE_ADDR");
        }
        else
        {
            $ip = "127.0.0.1";
        }
        return $ip;
    }



    // 微信会话中初始wxUser，如果数据库中没有该用户记录，保存记录到数据库
    public static function initWxUserForSession(MpUser &$mpUser, $wxUserID)
    {
        $mpUserID = $mpUser->getMpUserID();
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        if ($wxUser->isEmpty())
        {
            $wxUser->setWxUserID($wxUserID)
                ->setMpUserID($mpUserID)
                ->setCreateTime(time())
                ->setIsAdmin(0)
                ->setVipNo(self::generateVipNo($mpUser))
                ->setLastAccessYmd(self::getCurrentYearMonthDay())
                ->setIsFans(1);

            //ReportBusiness::uvPlusOne($mpUserID);
            $wxUser->insert(true);
        }
        else
        {
            $shouldUpdate = false;
            // 更新uv
            if($wxUser->getLastAccessYmd() != self::getCurrentYearMonthDay())
            {
                $wxUser->setLastAccessYmd(self::getCurrentYearMonthDay());
                //ReportBusiness::uvPlusOne($mpUserID);
                $shouldUpdate = true;
            }

            $vipNo = $wxUser->getVipNo();
            if(empty($vipNo))
            {
                $wxUser->setVipNo(self::generateVipNo($mpUser));
                $shouldUpdate = true;
            }

            if($shouldUpdate)
            {
                $wxUser->update();
            }
        }
        // 更新用户头像及昵称
        $headPic = $wxUser->getHeadPic();
        log_debug("====================".$headPic);
        $nickname = $wxUser->getNick();
        if(empty($headPic) || empty($nickname))
        {
            $headPic = WxApiBusiness::getHeadImgUrl($mpUserID,$wxUserID,$nickname);
            if(!empty($headPic))
            {
                $wxUser->setHeadPic($headPic)->update();
            }

            if(!empty($nickname))
            {
                $wxUser->setNick($nickname)->update();
            }
        }


        return $wxUser;
    }

    public static function hasNick(WxUser &$wxUser)
    {
        $nick = $wxUser->getNick();
        return !empty($nick);
    }



    public static function getOrderListUrl(MpUser &$mpUser, WxUser &$wxUser)
    {
        $host =     App::getInstance()->getContext('root');

        return sprintf('%swx_user/order/history?mp_user_id=%d&wx_user_id=%s',
            $host, $mpUser->getMpUserID(), $wxUser->getWxUserID());
    }

    public static function saveNick(WxUser &$wxUser, $nick)
    {
        $res = ['errno' => 0];
        $nick = trim($nick);
        $MAX_NIKE_LEN = 64;

        if(strlen($nick) > $MAX_NIKE_LEN)
        {
            $res['errno'] = 1;
            $error = '称呼太长，请回复一个新的称呼：';
            $res['error'] = $error;
            return $res;
        }

        if(empty($nick))
        {
            $res['errno'] = 1;
            $error = '称呼不能为空,请回复一个新的称呼：';
            $res['error'] = $error;
            return $res;
        }

        if($nick != $wxUser->getNick())
        {
            $wxUser->setNick($nick)->save();
        }


        return $res;
    }



    public static function saveNickForWeb($wxUserID, $nick)
    {
        $res = ['errno' => 0];
        $nick = trim($nick);
        $MAX_NIKE_LEN = 64;

        if(strlen($nick) > $MAX_NIKE_LEN)
        {
            $res['errno'] = 1;
            $error = '称呼太长';
            $res['error'] = $error;
            return $res;
        }

        if(empty($nick))
        {
            $res['errno'] = 1;
            $error = '称呼不能为空';
            $res['error'] = $error;
            return $res;
        }

        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        if($wxUser->isEmpty())
        {
            $res['errno'] = 1;
            $error = '称呼修改失败';
            $res['error'] = $error;
            return $res;
        }

        if($nick != $wxUser->getNick())
        {
            $wxUser->setNick($nick)->save();
        }

        return $res;
    }

    //获取nickName
    public static function getNickName($wxUserID)
    {
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $nickName = $wxUser->getNick();
        return $nickName;
    }


    public static function setCookieToken($mpUserID, $token)
    {
        $key = 'token' . $mpUserID;
        $value = $token;
        $path = '/';
        $expire = time() + 3600 * 24 * 30 * 12; // cookie 过期时间是1年
        setcookie($key, $value, $expire, $path);
        //log_debug("COOKIE [key:$key][value:$value][ret:$ret]");
    }


    public static function getCookieToken($mpUserID)
    {
        $value = null;
        $key = 'token' . $mpUserID;
        if (isset($_COOKIE[$key]))
        {
            $value = $_COOKIE[$key];
        }
        //log_debug("COOKIE [key:$key][value:$value]");

        return $value;
    }

    public static function setCookieWxUserID($mpUserID,$wxUserID,$expireTime)
    {
        $key = 'wxuserid'.$mpUserID;
       // $value = trim(encrypt($wxUserID));
        $value = $wxUserID;
        $path = '/';
        $expire = time() + $expireTime ;
        setcookie($key, $value, $expire, $path);
    }

    public static function getCookieWxUserID($mpUserID)
    {
        $value = null;
        $key = 'wxuserid'.$mpUserID;
        if (isset($_COOKIE[$key]))
        {
            //$value = decrypt($_COOKIE[$key]);
            $value = $_COOKIE[$key];
        }

        return trim($value);
    }

       //获取某个地址的经纬度
    public static function getAddressPrecision($address)
    {
        $address = (string)$address;
        if (!is_string($address))die("All Addresses must be passed as a string");
        $_url = sprintf('http://maps.google.com/maps?output=js&q;=%s',rawurlencode($address));
        $_result = false;
        if($_result = file_get_contents($_url)) {
            if(strpos($_result,'errortips') > 1 || strpos($_result,'Did you mean:') !== false) return false;
            preg_match('!center:\s*{lat:\s*(-?\d+\.\d+),lng:\s*(-?\d+\.\d+)}!U', $_result, $_match);
            $precision['lat'] = $_match[1];
            $precision['long'] = $_match[2];
        }
        return $precision;
    }

    //获取某个地址的经纬度
    public static function getPrecision($address,$city)
    {
        $address = urlencode($address);
        $city = urlencode($city);
        $json=file_get_contents("http://api.map.baidu.com/geocoder/v2/?ak=727313d893509fbad8adb4d9e4092fcb&output=json&address=".$address."&city=".$city);

        $information=json_decode($json);
        $array=array('errorno' => '1');
        if(isset($information->result->location) && !empty($information->result->location))
        {
            $array=array(
                'lng'=>$information->result->location->lng,
                'lat'=>$information->result->location->lat,
                'errorno'=>'0'
            );
        }
        return $array;
    }

    /**
     *求两个已知经纬度之间的距离,单位为米
     *@param lng1,lng2 经度
     *@param lat1,lat2 纬度
     *@return float 距离，单位米
     *@author www.Alixixi.com
     **/
    public static function getDistance($lng1,$lat1,$lng2,$lat2)
    {
        //将角度转为狐度
        $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;
        $b=$radLng1-$radLng2;
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
        return $s;
    }

    public static function getMpUserName( $mpUserID )
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        if ($mpUser->isEmpty()) {
            return NULL;
        }
        return $mpUser->getMpName();
    }

    //获取用户列表
    public static function getUserList($mpUserID, $ranking, &$paging, $outputColumns)
    {
        $condition = [WxUser::MP_USER_ID => $mpUserID];
        $selection = ['*'];
        $grouping = null;

        $userList = WxUser::fetchRowsWithCount(
            $selection,
            $condition,
            $grouping,
            $ranking,
            $paging,
            $outputColumns
        );
        return $userList;
    }

    public static function getAppUserList( array $condition, array &$paging = NULL, $ranking,  array $outputColumns = NULL )
    {
        return AppUser::fetchRowsWithCount( [ '*' ], $condition, NULL, $ranking, $paging,$outputColumns );
    }


    // 电话号码是否被占用
    public static function isPhoneUsed($mpUserID, $wxUserID, $phone)
    {
        $wxUser = new WxUser([WxUser::MP_USER_ID => $mpUserID, WxUser::PHONE => $phone]);
        if($wxUser->isEmpty())
        {
            return false;
        }

        // 自己的号
        if($wxUser->getWxUserID() == $wxUserID )
        {
            return false;
        }

        // 其它情况，被占用
        return true;
    }


    // 产生验证码，并保存到数据库
    public static function generatePhoneVerifyCode($wxUserID, $phone)
    {
        $wxUserExt = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserID]);
        if(!$wxUserExt->isEmpty())
        {
            $now = time();
            $lastCodeGenerateTime = $wxUserExt->getPhoneVerifyCodeGenerateTime();
            if(empty($lastCodeGenerateTime))
            {
                $lastCodeGenerateTime =  0;
            }
            else
            {
                $lastCodeGenerateTime =  strtotime($lastCodeGenerateTime);
            }

            // 一分钟之内，不产生新的验证码
            if($now - $lastCodeGenerateTime < 60 )
            {
                return $wxUserExt->getPhoneVerifyCode();
            }
        }


        $str         = '123456789';
        $maxStrIndex = strlen( $str ) - 1;
        $code       = '';
        $codeLen    = 3;

        for ($i = 0; $i < $codeLen; ++$i)
        {
            $r = rand( 0, $codeLen );
            $code .= $str[$r];
        }

        $wxUserExt = new WxUserExt();
        $wxUserExt->setPhone($phone)
            ->setPhoneVerifyCode($code)
            ->setPhoneVerifyCodeGenerateTime(time())
            ->setWxUserID($wxUserID)
            ->insert(true);

        return $code;
    }

    // 检查验证码是否正确，正确返回true,并更新WxUser表，否则返回false
    public static function checkPhoneVerifyCode($wxUserID, $code)
    {
        $wxUserExt = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserID]);
        if($wxUserExt->isEmpty())
        {
            return false;
        }

        $codeOK = ($wxUserExt->getPhoneVerifyCode() == $code) && (!empty($code)) ;
        $timeOK = time() - strtotime( $wxUserExt->getPhoneVerifyCodeGenerateTime()) < 60 * 30 ; // 30分钟内验证码有效

        if($codeOK && $timeOK)
        {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
            if($wxUser->isEmpty())
            {
                log_warn("wxUser is empty.[wxUserID: $wxUserID]");
                return false;
            }
            else
            {
                $phone = $wxUserExt->getPhone();
                // 11位手机号
                if(strlen($phone) == 11)
                {
                    $wxUser->setPhone($wxUserExt->getPhone())->save();
                    return true;
                }
            }
        }

        return false;
    }

    // 检查验证码是否正确，正确返回true,并更新WxUser表，否则返回false
    public static function checkPhoneVerifyCodeRegister($wxUserID, $code)
    {
        $wxUserExt = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserID]);
        if($wxUserExt->isEmpty())
        {
            return false;
        }

        $codeOK = ($wxUserExt->getPhoneVerifyCode() == $code) && (!empty($code)) ;
        $timeOK = time() - strtotime( $wxUserExt->getPhoneVerifyCodeGenerateTime()) < 60 * 30 ; // 30分钟内验证码有效

        if($codeOK && $timeOK)
        {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
            if($wxUser->isEmpty())
            {
                log_warn("wxUser is empty.[wxUserID: $wxUserID]");
                return false;
            }
            else
            {
                $phone = $wxUserExt->getPhone();
                // 11位手机号
                if(strlen($phone) == 11)
                {
                    $wxUser->setPhone($wxUserExt->getPhone())->save();
                    return true;
                }
            }
        }

        return false;
    }

    public static function checkAndBindMemberPhone($communityId,$mpUserID, $wxUserID, $phone, $code)
    {
        $expr = sprintf("`phone1` = %s or `phone2` = %s or `phone3`=%s", $phone, $phone, $phone);
        $dbCondition = new \Bluefin\Data\DbCondition($expr);

        $condition = [$dbCondition,HouseMember::COMMUNITY_ID => $communityId];

        $houseMember = new HouseMember($condition);

        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        if($wxUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '无效的微信用户'];
        }


        if ($houseMember->isEmpty())
        {
            return ['errno' => 1, 'error' => sprintf('未在系统中找到您的手机号，请联系物业更新您的联系电话', $phone)];
        }

        $wx_user_ext = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserID]);
        $phoneVerifyCode = $wx_user_ext->getPhoneVerifyCode();
        if ( $phoneVerifyCode != $code)
        {
            return ['errno' => 1, 'error' => '您输入的四位验证码不正确，请重新输入'];
        }

        $wxUser->setPhone($phone)
            ->setNick($houseMember->getName())
            ->setCurrentCommunityID($communityId)->setBirth($houseMember->getBirthday())->setAddress($houseMember->getHouseAddress())->setIdentifyTime(time())
            ->update();

        $houseMember->setWxUserID($wxUserID)->setVerifyTime(time())->update();
        return ['errno' => 0];
    }

    public static function checkAndBindMemberPhoneRegister($communityId,$mpUserID, $wxUserID, $phone, $code)
    {
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        if($wxUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '无效的微信用户'];
        }


        $wx_user_ext = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserID]);
        $phoneVerifyCode = $wx_user_ext->getPhoneVerifyCode();
        if ( $phoneVerifyCode != $code)
        {
            return ['errno' => 1, 'error' => '您输入的四位验证码不正确，请重新输入'];
        }

        $wxUser->setPhone($phone)->setRegisterTime(time())->update();

        return ['errno' => 0];
    }

    public static function checkPhoneExist($communityId,$wxUserId, $mpUserId, $phone,$type)
    {
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityPhone = $community->getPhone();
        $expr = sprintf("`phone1` = %s or `phone2` = %s or `phone3`=%s", $phone, $phone, $phone);
        $dbCondition = new \Bluefin\Data\DbCondition($expr);

        $condition = [$dbCondition,HouseMember::COMMUNITY_ID => $communityId];

        $houseMember = new HouseMember($condition);
        if($houseMember->isEmpty()){
            return ['errno' => 2, 'error' => "$communityPhone"];
        }

        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId, WxUser::CURRENT_COMMUNITY_ID => $communityId]);
        if(!$wxUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '你的账号已经被认证过'];
        }
        //获取短信验证码配置值
        $verifyCodeID = ConfigBusiness::mpUserConfig($mpUserId);

        if(!$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID])
        {
            return ['errno' => 1, 'error' => '系统验证码id有误'];
        }
        if (!$houseMember->isEmpty())
        {
            $verifyCode = self::generateVerifyPhoneCode();
            $wx_user_ext = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserId]);


            $currentTime = time();
            $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
            if($wx_user_ext->isEmpty())
            {
                $wx_user_ext->setPhoneVerifyCode($verifyCode)
                    ->setWxUserID($wxUserId)
                    ->setPhone($phone)
                    ->setPhoneVerifyCodeGenerateTime($currentTime)
                    ->insert();
                if($type == 'sms')
                {
                   SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
                }
                else
                {
                    VoiceBusiness::codeCall($phone, $verifyCode);
                }
            }
            else
            {
                $verifyCodeData = $wx_user_ext->getPhoneVerifyCode();
                $lastPhoneVerifyCodeGenerateTimeData = $wx_user_ext->getPhoneVerifyCodeGenerateTime();
                //数据库存入数据和当前时间差
                $timeDiff = floor($currentTime - strtotime($lastPhoneVerifyCodeGenerateTimeData));
                // 20分钟内发的验证码是相同的
                if($timeDiff > 1200)
                {
                    $wx_user_ext->setPhoneVerifyCode($verifyCode)
                    ->setPhoneVerifyCodeGenerateTime($currentTime)
                    ->update();
                    if($type == 'sms')
                    {
                        SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
                    }
                    else
                    {
                        VoiceBusiness::codeCall($phone, $verifyCode);
                    }
                }
                else
                {
                    $verifyCode=$verifyCodeData;
                    if($type == 'sms')
                    {
                         SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
                    }
                    else
                    {
                        VoiceBusiness::codeCall($phone, $verifyCode);
                    }
                }

            }
            return ['errno' => 0];
        }
        else
        {
            return
                ['errno' => 2, 'error' => "$communityPhone"];
        }
    }
    public static function checkPhoneExistRegister($communityId,$wxUserId, $mpUserId, $phone,$type)
    {
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId, WxUser::PHONE => $phone]);
        if(!$wxUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '你的账号已经被注册过'];
        }
        //获取短信验证码配置值
        $verifyCodeID = ConfigBusiness::mpUserConfig($mpUserId);

        if(!$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID])
        {
            return ['errno' => 1, 'error' => '系统验证码id有误'];
        }

        $verifyCode = self::generateVerifyPhoneCode();
        $wx_user_ext = new WxUserExt([WxUserExt::WX_USER_ID => $wxUserId]);


        $currentTime = time();
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        if($wx_user_ext->isEmpty())
        {
            $wx_user_ext->setPhoneVerifyCode($verifyCode)
                ->setWxUserID($wxUserId)
                ->setPhone($phone)
                ->setPhoneVerifyCodeGenerateTime($currentTime)
                ->insert();
            if($type == 'sms')
            {
                SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
            }
            else
            {
                VoiceBusiness::codeCall($phone, $verifyCode);
            }
        }
        else
        {
            $verifyCodeData = $wx_user_ext->getPhoneVerifyCode();
            $lastPhoneVerifyCodeGenerateTimeData = $wx_user_ext->getPhoneVerifyCodeGenerateTime();
            //数据库存入数据和当前时间差
            $timeDiff = floor($currentTime - strtotime($lastPhoneVerifyCodeGenerateTimeData));
            // 20分钟内发的验证码是相同的
            if($timeDiff > 1200)
            {
                $wx_user_ext->setPhoneVerifyCode($verifyCode)
                    ->setPhoneVerifyCodeGenerateTime($currentTime)
                    ->update();
                if($type == 'sms')
                {
                    SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
                }
                else
                {
                    VoiceBusiness::codeCall($phone, $verifyCode);
                }
            }
            else
            {
                $verifyCode=$verifyCodeData;
                if($type == 'sms')
                {
                    SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
                }
                else
                {
                    VoiceBusiness::codeCall($phone, $verifyCode);
                }
            }

        }
        return ['errno' => 0];
    }

    public static function generateVerifyPhoneCode()
    {
        $str         = '123456789';
        $maxStrIndex = strlen( $str ) - 1;
        $code       = '';
        $codeLen    = 4;

        for ($i = 0; $i < $codeLen; ++$i)
        {
            $r = rand( 0, $maxStrIndex );
            $code .= $str[$r];
        }

        return $code;
    }



    public static function isMember(WxUser &$wxUser)
    {
        $currentCommunityID =  $wxUser->getCurrentCommunityID();
       // if(!empty($currentCommunityID))
        {
            $hs = new HouseMember([HouseMember::COMMUNITY_ID => $currentCommunityID,
                                   HouseMember::WX_USER_ID => $wxUser->getWxUserID()]);

            // 有脏数据，更新$currentCommunityID\
            if($hs->isEmpty())
            {
                $currentCommunityID = 0;
                $hs = new HouseMember([HouseMember::WX_USER_ID => $wxUser->getWxUserID()]);
                if(!$hs->isEmpty())
                {
                    $currentCommunityID = $hs->getCommunityID();
                }
                $wxUser->setCurrentCommunityID($currentCommunityID)->save();
            }
        }

        return !empty($currentCommunityID);
    }


    public static function getWxUserIDByVipNo($mpUserID, $vipNo)
    {
        $wxUser = new WxUser([WxUser::MP_USER_ID => $mpUserID, WxUser::VIP_NO => $vipNo]);
        if($wxUser->isEmpty())
        {
            return false;
        }

        return $wxUser->getWxUserID();
    }
}


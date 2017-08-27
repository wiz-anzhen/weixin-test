<?php
namespace WBT\Business;

use Bluefin\App;
use MP\Model\Mp\MpUserConfig;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\CommunityConfigType;
use MP\Model\Mp\CommunityConfig;



class ConfigBusiness
{
    //公众账号配置

    public static function mpUserConfig($mpUserID)
    {
        $mpUserConfig = MpUserConfig::fetchRows([ '*' ], [MpUserConfig::MP_USER_ID => $mpUserID]);
        $mpUserConfigValue = [];
        foreach($mpUserConfig as $config)
        {
            $mpUserConfigValue[$config[MpUserConfig::CONFIG_TYPE]] = $config[MpUserConfig::CONFIG_VALUE];
        }
        log_debug("==================",$mpUserConfigValue);
        return $mpUserConfigValue;
    }
   //小区配置
    public static function communityConfig($communityID)
    {
        $communityConfig = CommunityConfig::fetchRows([ '*' ], [CommunityConfig::COMMUNITY_ID => $communityID]);
        $config = [];
        foreach($communityConfig as $value)
        {
            $config[$value[CommunityConfig::CONFIG_TYPE]] = $value[CommunityConfig::CONFIG_VALUE];
        }
        return $config;
    }

    /**
     * 获取host
     */
    public static function getHost()
    {
        $rcrmConfig = _C('config.util');
        $host =  $rcrmConfig['host'];

        return $host;
    }


    /**
     * 获取mail host
     */
    public static function getMailHost()
    {
        $rcrmConfig = _C('config.util');
        $host =  $rcrmConfig['mail_host'];

        return $host;
    }

    /**
     * 获取paging
     */
    public static function getPaging($paging)
    {
        $rcrmConfig = _C('config.util');
        if($paging == "page_count_small")
        {
            $paging =  $rcrmConfig['page_count_small'];
        }
        else if($paging == "page_count_mid")
        {
            $paging =  $rcrmConfig['page_count_mid'];
        }
        else if($paging == "page_count_big")
        {
            $paging =  $rcrmConfig['page_count_big'];
        }
        else
        {
            $paging =  $rcrmConfig['page_count_small'];
        }
        return $paging;
    }
    public static function csAnswerEnabled($mpUserID)
    {
        $csConfig = ConfigBusiness::mpUserConfig($mpUserID);
        if(!isset($csConfig[MpUserConfigType::CS_ANSWER]))
        {
            return false;
        }
        return intval($csConfig[MpUserConfigType::CS_ANSWER]);
    }

    public static function getCsClickWxMenuHint($mpUserID)
    {
        $csConfig = ConfigBusiness::mpUserConfig($mpUserID);
        if(!isset($csConfig[MpUserConfigType::CS_CLICK_WX_MENU_HINT]))
        {
            return '请点击底部菜单';
        }
        return $csConfig[MpUserConfigType::CS_CLICK_WX_MENU_HINT];
    }

    public static function getCookieExpireTime()
    {
        $rcrmConfig = _C('config.util');
        $cookieExpireTime =  $rcrmConfig['cookie_expire_time'];
        if(empty($cookieExpireTime))
        {
            $cookieExpireTime = 3600 * 24 * 30;
        }

        return $cookieExpireTime;
    }

    public static function getPartnerKey()
    {
        $rcrmConfig = _C('config.util');
        $partnerKey = $rcrmConfig['partner_key'];
        return $partnerKey;
    }

    public static function getPartnerId()
    {
        $rcrmConfig = _C('config.util');
        $partnerId = $rcrmConfig['partner_id'];
        return $partnerId;
    }

    public static function getPaySignKey()
    {
        $rcrmConfig = _C('config.util');
        $paySignKey = $rcrmConfig['pay_sign_key'];
        return $paySignKey;
    }

    public static function getAppId()
    {
        $rcrmConfig = _C('config.util');
        $appId = $rcrmConfig['app_id'];
        return $appId;
    }

    public static function getEmailTechnology()
    {
        $rcrmConfig = _C('config.util');
        $emailTechnology = $rcrmConfig['email_technology'];
        return $emailTechnology;
    }

    public static function getCwbMpUserID()
    {
        $rcrmConfig = _C('config.util');
        $cwbMpUserID = $rcrmConfig['cwb_mp_user_id'];
        return $cwbMpUserID;
    }
}
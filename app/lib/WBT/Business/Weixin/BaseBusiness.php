<?php

namespace WBT\Business\Weixin;


class BaseBusiness
{
    public static function getCurrentYearMonthDay()
    {
        return intval( date( 'Ymd' ) );
    }

    public static function getHost()
    {
        $host =   \Bluefin\App::getInstance()->getContext('root');
        if(substr($host, -1) == '/')
        {
            $host = substr($host,0, strlen($host) -1);
        }

        return $host;
    }

    public static function generateRandomToken()
    {
        return md5( uniqid( mt_rand(), true ) );
    }

    public static function isInWeixinEnv()
    {
        return (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") !== false );
    }

}
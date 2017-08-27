<?php

namespace WBT\Business;
use WBT\Business\ConfigBusiness;

require_once 'PushMessage/AndroidIOSPush.php';


/**
 * 百度云推送相关的业务
 */
class PushBusiness
{
    /**
     * 百度推送消息 负责推送IOS端
     * @param  baiduuserid  百度推userid
     *         unreadmsg 未读消息数量
     *         message_type 消息类型  0为消息  1为通知
     *         IOS端只能推送通知
     */
    public static function baiduSendIOSMessage($baiduUserId,$unReadMsg)
    {
        $push = new \AndroidIOSPush();
        $message_type = 1;
        $res = $push->push_ios($unReadMsg,'',$baiduUserId,'',$message_type);
        return $res;
    }
}

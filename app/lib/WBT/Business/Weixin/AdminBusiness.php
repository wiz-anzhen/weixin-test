<?php

namespace WBT\Business\Weixin;

use Bluefin\App;
use MP\Model\Mp\WxUser;

use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\WxApiBusiness;

use WBT\Business\Weixin\BaseBusiness;


class AdminBusiness extends BaseBusiness
{
    // 给管理员发送文本消息
    public static  function sendTextMessageToAdmin($VipNo, $text)
    {
        $mpUserID = 12346; // spm的mp_user_id是12346
        $wxUserID = WxUserBusiness::getWxUserIDByVipNo($mpUserID, $VipNo);
        if(empty($wxUserID))
        {
            log_error("invalid $VipNo($VipNo)");
            return false;
        }

        if(! \WBT\Business\Weixin\WxApiBusiness::sentTextMessage($mpUserID, $wxUserID, $text))
        {
            log_error('发送消息失败. msg:', $text);
            return false;
        }
        return true;
    }
}

<?php

namespace WBT\Business\Weixin;


use MP\Model\Mp\WxUser;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;

class SendTemplateBusiness extends BaseBusiness
{
    public static function sendNewNotify( $mpUserID,$name,$boundName)
    {
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id

        $host =  ConfigBusiness::getHost();//获取主机名
        $wxUserIDs = [113998,102118,190962];
        foreach($wxUserIDs as $value)
        {log_debug("=======================",$wxUserIDs);
            $wxUser = new WxUser([WxUser::VIP_NO => $value]);
            $url = "";
            $first = $name."添加供应商".$boundName;
            $nick = $name;
            $content = "请您赶快去查看";


            $template = array( 'touser' => $wxUser->getWxUserID(),
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode($first),'color' =>"#cf3134", ),
                    'keyword1' => array('value' => urlencode("添加供应商"),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode("通知"),'color' =>"#222", ),
                    'remark' => array('value' => urlencode($content) ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);

        }


    }

}
<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-7-4
 * Time: 下午12:56
 */
require_once '../../../lib/Bluefin/bluefin.php';
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\UserNotify;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Business\ConfigBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\UserNotifySendStatus;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\IndustryType;

sendTemplate();

function sendTemplate()
{
    //找出所有待发布的模板消息
    $userNotifyIDs = UserNotify::fetchColumn([UserNotify::USER_NOTIFY_ID],[UserNotify::SEND_STATUS => UserNotifySendStatus::SEND_WAIT]);

    foreach($userNotifyIDs as $id)
    {
        if(!empty($id))
        {
            $userNotify = new UserNotify([UserNotify::USER_NOTIFY_ID => $id]);
            $userNotify->setSendStatus(UserNotifySendStatus::SEND_PROCESS)->update();
        }
    }

    foreach($userNotifyIDs as $id)
    {
        if(!empty($id))
        {
            $userNotify = new UserNotify([UserNotify::USER_NOTIFY_ID => $id]);
            $mpUserID = $userNotify->getMpUserID();
            $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
            $industry = $mpUser->getIndustry();
            $communityID = $userNotify->getCommunityID();
            //取出微信id
            $userNotifySendRange = $userNotify->getSendRange();
            $wxUserIDs = "";
            if($userNotifySendRange == UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY or $userNotifySendRange == UserNotifySendRangeType::SEND_BY_HOUSE_NO)
            {
                $wxUserIDs =  UserNotifyBusiness::getWxUserId($userNotify->getSendRange(),$communityID,$userNotify->getSendNo(),$userNotify->getMpUserID());

            }
            elseif($userNotifySendRange == UserNotifySendRangeType::SEND_CUSTOMER)
            {
                $specialistGroup = $userNotify->getSpecialistGroup();
                $specialistGroup = explode(",",$specialistGroup);log_debug("================",$specialistGroup);
                $specialistGroupNew = [];
                foreach($specialistGroup as $value)
                {
                    $customerSpecialistGroup = new \MP\Model\Mp\CustomerSpecialistGroup([\MP\Model\Mp\CustomerSpecialistGroup::GROUP_NAME => $value]);
                    $specialistGroupNew[] = $customerSpecialistGroup->getCustomerSpecialistGroupID();
                }
                log_debug("================",$specialistGroupNew);
                $wxUserIDs = [];
                foreach($specialistGroupNew as $value)
                {
                    $process =  \MP\Model\Mp\CustomerSpecialist::fetchColumn([\MP\Model\Mp\CustomerSpecialist::WX_USER_ID],[\MP\Model\Mp\CustomerSpecialist::COMMUNITY_ID => $communityID,\MP\Model\Mp\CustomerSpecialist::VALID => 1,\MP\Model\Mp\CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $value]);
                    foreach($process as $v)
                    {
                        array_push($wxUserIDs,$v);
                    }
                }
                log_debug("====================",$wxUserIDs);

            }
            elseif($userNotifySendRange == UserNotifySendRangeType::SEND_TOTAL)
            {
                //关注总数量
                $wxUserIDs = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::MP_USER_ID => $mpUserID,WxUser::IS_FANS => 1]);
            }
            elseif($userNotifySendRange == UserNotifySendRangeType::SEND_TOTAL_VERIFY)
            {
                //认证
                $expr = "`current_community_id` > 0";
                $dbCondition = new \Bluefin\Data\DbCondition($expr);
                $condition = [$dbCondition, WxUser::MP_USER_ID => $mpUserID];
                $wxUserIDs =  WxUser::fetchColumn([WxUser::WX_USER_ID],$condition);

            }
            elseif($userNotifySendRange == UserNotifySendRangeType::SEND_TOTAL_UN_VERIFY)
            {
               //未认证已关注
               $wxUserIDs =  WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::MP_USER_ID => $mpUserID,WxUser::CURRENT_COMMUNITY_ID => 0,WxUser::IS_FANS => 1]);

            }
            $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
            $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
            if($industry == IndustryType::FIANCE)
            {
                $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_FIANCE_NOTIFY_ID];//金融业通知模板id
            }
            foreach($wxUserIDs as $value)
            {
                $wxUser = new WxUser([WxUser::WX_USER_ID => $value]);
                $customer = new \MP\Model\Mp\CustomerSpecialist([\MP\Model\Mp\CustomerSpecialist::WX_USER_ID => $value,\MP\Model\Mp\CustomerSpecialist::VALID => 1]);
                $name = $customer->getName();
                $phone = $customer->getPhone();
                $customerSpecialistGroupID = $customer->getCustomerSpecialistGroupID();
                $group = new \MP\Model\Mp\CustomerSpecialistGroup([\MP\Model\Mp\CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $customerSpecialistGroupID]);
                $groupName = $group->getGroupName();

                $url = $userNotify->getContentUrl();

                if($userNotifySendRange == UserNotifySendRangeType::SEND_CUSTOMER)
                {
                    $url = $url."&name_send=".$name."&phone_send=".$phone."&group_name_send=".$groupName;
                }
                $infoTitle = $userNotify->getTitle();
                $infoId = $userNotify->getInfoid();
                $remark = $userNotify->getDescription();
                $template = array( 'touser' => $value,
                    'template_id' => "$templateID",
                    'url' => $url,
                    'topcolor' => "#62c462",
                    'data'   => array(
                        'first' => array('value' => urlencode($infoTitle),'color' =>"#222", ),
                        'keyword1' => array('value' => urlencode($infoId),'color' =>"#222", ),
                        'keyword2' => array('value' => urlencode($remark),'color' =>"#222", ),
                        'remark' => array('value' => urlencode("") ,
                            'color' =>"#222" ,))
                );


                if($industry == IndustryType::FIANCE)
                {
                    $date = date("Y-m-d H:i:s");
                    $template = array( 'touser' => $value,
                        'template_id' => "$templateID",
                        'url' => $url,
                        'topcolor' => "#62c462",
                        'data'   => array(
                            'first' => array('value' => urlencode(""),'color' =>"#222", ),
                            'keyword1' => array('value' => urlencode($infoTitle),'color' =>"#222", ),
                            'keyword2' => array('value' => urlencode($infoId),'color' =>"#222", ),
                            'keyword3' => array('value' => urlencode($date),'color' =>"#222",),
                            'remark' => array('value' => urlencode($remark) ,
                                'color' =>"#222" ,))
                    );
                }
                WxApiBusiness::sentTemplateMessage($mpUserID,$template);
            }
            $userNotify->setSendStatus(UserNotifySendStatus::SEND_FINISHED)->update();
            log_debug("====================",$wxUserIDs);
        }

    }
}
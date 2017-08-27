<?php
use MP\Model\Mp\UserNotify;
use MP\Model\Mp\PushMessage;
use WBT\Business\App\PushMessageBusiness;
use MP\Model\Mp\UserNotifySendRangeType;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\IndustryType;
use WBT\Business\UserBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\UserNotifySendStatus;
use MP\Model\Mp\AppUser;
use WBT\Business\PushBusiness;
require_once 'MpUserServiceBase.php';
class PushMessageService extends MpUserServiceBase
{
    // 修改模板消息
    public function update()
    {
        $id   = $this->_app->request()->getQueryParam( PushMessage::PUSH_MESSAGE_ID );
        $data = $this->_app->request()->getArray( [
            PushMessage::TITLE ,
            PushMessage::CONTENT,
            PushMessage::INFOID ,
            PushMessage::SEND_NO ,
            PushMessage::SEND_RANGE ,
        ] );

        return PushMessageBusiness::update( $id, $data );
    }

    // 插入模板消息
    public function insert()
    {
        $request = $this->_app->request();
        //$infoidTitle = $this->_app->request()->getQueryParam( "infoid_title" );
        $sentType = $this->_app->request()->getQueryParam( "send_type" );
        $data    = $request->getArray( [
            PushMessage::TITLE ,
            PushMessage::CONTENT ,
            PushMessage::INFOID ,
            PushMessage::SEND_RANGE ,
            PushMessage::MP_USER_ID,
            PushMessage::COMMUNITY_ID,
        ] );
        $data[PushMessage::SEND_TYPE] = $sentType;
        return PushMessageBusiness::insert( $data );
    }

    // 删除模板消息
    public function delete()
    {
        $id = $this->_app->request()->get( PushMessage::PUSH_MESSAGE_ID );
        return PushMessageBusiness::delete( $id );

    }


    //复制模板消息
    public function copy()
    {
        $id = $this->_app->request()->get( PushMessage::PUSH_MESSAGE_ID );
        $from= $this->_app->request()->get( "from" );
        return PushMessageBusiness::copy( $id,$from );

    }

    //模板消息消息过程

    public function send()
    {
        $id = $this->_app->request()->get( PushMessage::PUSH_MESSAGE_ID  );
        $pushMessage = new PushMessage([PushMessage::PUSH_MESSAGE_ID=>$id]);
        $data[PushMessage::SEND_TIME] = time();
        $data[ PushMessage::SEND_AUTHOR] = UserBusiness::getLoginUsername();
        $communityId= $this->_app->request()->get( PushMessage::COMMUNITY_ID );
        $from= $this->_app->request()->get( "from" );
        //from app_c 对小区用户推送  from= app_mp 对所有公共账号下用户发送
//   sss
        if($from == "app_mp")
        {
            $condition = [];
        }
        else if($from == 'app_c')
        {
            $condition = [AppUser::CURRENT_COMMUNITY_ID=>$communityId];
        }

        $data[ PushMessage::SEND_STATUS] = UserNotifySendStatus::SEND_FINISHED;

        /*
         * 发送之
         * 如果用户量太大 ,以后模仿微信模板消息发送,放入crontab中进行消息推送
         */
        //1 提取该社区用户 baidu userid

        $userIdArray = AppUser::fetchColumn(AppUser::BAIDU_USER_ID,$condition);
        log_debug('useridarray==========',$userIdArray);
        foreach($userIdArray as $userId)
        {
            if(!empty($userId))
            {
                log_debug("userid=================",$userId);
                log_debug("content========",$pushMessage->getContent());
                $res = PushBusiness::baiduSendIOSMessage($userId,$pushMessage->getContent());
                log_debug('res=====',$res);
            }
        }
        PushMessageBusiness::update( $id, $data );
    }

    // 预览模板消息
    public function preview()
    {
        $id   = $this->_app->request()->getQueryParam( UserNotify::USER_NOTIFY_ID );
        $userNotify = new UserNotify([UserNotify::USER_NOTIFY_ID => $id]);
        $vipNo   = $this->_app->request()->get("vip_no" );

        $wxUser = new WxUser([WxUser::VIP_NO => $vipNo]);
        $wxUserID = $wxUser->getWxUserID();
        if($wxUser->isEmpty())
        {
            return [ 'errno' => 1, 'error' => '请确认您填写的会员号' ];
        }
        $mpUserID =  $wxUser->getMpUserID();
        $mpUser =  new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $industry = $mpUser->getIndustry();
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_SERVICE_NOTIFY_ID];//通知模板id

        if($industry == IndustryType::FIANCE)
        {
            $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_FIANCE_NOTIFY_ID];//金融业通知模板id
        }

        $customer = new \MP\Model\Mp\CustomerSpecialist([\MP\Model\Mp\CustomerSpecialist::WX_USER_ID => $wxUserID,\MP\Model\Mp\CustomerSpecialist::VALID => 1]);
        $name = $customer->getName();
        $phone = $customer->getPhone();
        $customerSpecialistGroupID = $customer->getCustomerSpecialistGroupID();
        $group = new \MP\Model\Mp\CustomerSpecialistGroup([\MP\Model\Mp\CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $customerSpecialistGroupID]);
        $groupName = $group->getGroupName();

        $url = $userNotify->getContentUrl();
        $userNotifySendRange = $userNotify->getSendRange();
        if($userNotifySendRange == UserNotifySendRangeType::SEND_CUSTOMER)
        {
            $url = $url."&name_send=".$name."&phone_send=".$phone."&group_name_send=".$groupName;
        }
        $infoTitle = $userNotify->getTitle();
        $infoId = $userNotify->getInfoid();
        $remark = $userNotify->getDescription();
        $template = array( 'touser' => $wxUserID,
            'template_id' => "$templateID",
            'url' => $url,
            'topcolor' => "#62c462",
            'data'   => array('first' => array('value' => urlencode("点击查看详情"),'color' =>"#222", ),
                'infotitle' => array('value' => urlencode($infoTitle),'color' =>"#222", ),
                'infoId' => array('value' => urlencode($infoId),'color' =>"#222", ),
                'statusType' => array('value' => urlencode('摘要'),'color' =>"#222",),
                'status' => array('value' => urlencode($remark),'color' =>"#222",),
                'remark' => array('value' => urlencode("") ,
                    'color' =>"#222" ,))
        );

        if($industry == IndustryType::FIANCE)
        {
            $date = date("Y-m-d H:i:s");
            $template = array( 'touser' => $wxUserID,
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

        $userNotify->setSendStatus(UserNotifySendStatus::SEND_NO)->setSendTime(time())->setSendAuthor(UserBusiness::getLoginUsername())->update();
        return [ 'errno' => 0];
    }
}
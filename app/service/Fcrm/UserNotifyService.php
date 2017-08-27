<?php


use MP\Model\Mp\UserNotify;
use MP\Model\Mp\UserNotifySendRangeType;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\IndustryType;
use WBT\Business\UserBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\UserNotifySendStatus;
require_once 'MpUserServiceBase.php';
class UserNotifyService extends MpUserServiceBase
{
    // 修改模板消息
    public function update()
    {
        $id   = $this->_app->request()->getQueryParam( UserNotify::USER_NOTIFY_ID );
        $infoidTitle = $this->_app->request()->getQueryParam( "infoid_title" );
        $data = $this->_app->request()->getArray( [
            UserNotify::TITLE ,
            UserNotify::DESCRIPTION ,
            UserNotify::CONTENT_URL ,
            UserNotify::INFOID ,
            UserNotify::SEND_NO ,
            UserNotify::SEND_RANGE ,
            UserNotify::SPECIALIST_GROUP,
        ] );

        if(empty($data[UserNotify::INFOID]))
        {
            return [ 'errno' => 1, 'error' => $infoidTitle."不能为空"];
        }
        if($data[UserNotify::SEND_RANGE] == UserNotifySendRangeType::SEND_BY_HOUSE_NO and $data[UserNotify::SEND_NO] == "" )
        {
            return [ 'errno' => 1, 'error' => '请您填写指定房间编号' ];
        }
        $data[UserNotify::SEND_NO]    = str_replace( '，', ',', $data[UserNotify::SEND_NO]);
        return UserNotifyBusiness::update( $id, $data );
    }

   // 插入模板消息
    public function insert()
    {
        $request = $this->_app->request();
        $infoidTitle = $this->_app->request()->getQueryParam( "infoid_title" );
        $data    = $request->getArray( [
            UserNotify::TITLE ,
            UserNotify::DESCRIPTION ,
            UserNotify::CONTENT_URL ,
            UserNotify::INFOID ,
            UserNotify::SEND_NO ,
            UserNotify::SEND_RANGE ,
            UserNotify::MP_USER_ID,
            UserNotify::COMMUNITY_ID,
            UserNotify::SPECIALIST_GROUP,
            UserNotify::SEND_TYPE,
        ] );
        if(empty($data[UserNotify::INFOID]))
        {
            return [ 'errno' => 1, 'error' => $infoidTitle."不能为空"];
        }
        if($data[UserNotify::SEND_RANGE] == UserNotifySendRangeType::SEND_BY_HOUSE_NO and $data[UserNotify::SEND_NO] == "" )
        {
            return [ 'errno' => 1, 'error' => '请您填写指定房间编号' ];
        }
        $data[UserNotify::SEND_NO]    = str_replace( '，', ',', $data[UserNotify::SEND_NO]);
        return UserNotifyBusiness::insert( $data );
    }

     // 删除模板消息
    public function delete()
    {
        $id = $this->_app->request()->get( UserNotify::USER_NOTIFY_ID );
        return UserNotifyBusiness::delete( $id );

    }


     //复制模板消息
    public function copy()
    {
        $id = $this->_app->request()->get( UserNotify::USER_NOTIFY_ID );
        $from= $this->_app->request()->get( "from" );
        log_debug("======================".$id);
        return UserNotifyBusiness::copy( $id,$from );

    }

    //模板消息消息过程

    public function send()
    {
        $id = $this->_app->request()->get( UserNotify::USER_NOTIFY_ID  );
        $data[UserNotify::SEND_TIME] = time();
        $data[ UserNotify::SEND_AUTHOR] = UserBusiness::getLoginUsername();

        $from= $this->_app->request()->get( "from" );
        if($from == "mp" or $from == "community")
        {
            $data[ UserNotify::SEND_STATUS] = UserNotifySendStatus::SEND_WAIT;
        }
        else
        {
            $data[ UserNotify::SEND_STATUS] = UserNotifySendStatus::SEND_FINISHED;
        }
        UserNotifyBusiness::update( $id, $data );
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
        $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id

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
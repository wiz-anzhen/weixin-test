<?php

use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\Bill;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\OwnerLessee;
use MP\Model\Mp\AppUser;
// 普通用户账户设置相关api
// todo : 安全方面，增加token，
class AppuserInfoService extends Service
{
    public function sendCode()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $phone    = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $type = App::getInstance()->request()->get('type');

        $ret = WxUserBusiness::checkPhoneExist($communityId,$wxUserId, $mpUserId, $phone,$type);
        return $ret;
    }
    public function sendCodeRegister()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $phone    = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $type = App::getInstance()->request()->get('type');

        $ret = WxUserBusiness::checkPhoneExistRegister($communityId,$wxUserId, $mpUserId, $phone,$type);
        return $ret;
    }
    //向业主发送模板消息
    public function sendOwnerTemplate()
    {
        $wxUserIDLessee = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $ownerPhone    = App::getInstance()->request()->get('owner_phone');
        $lesseeName = App::getInstance()->request()->get('lessee_name');
        $lesseePhone = App::getInstance()->request()->get('lessee_phone');
        $wxUser = new WxUser([WxUser::PHONE => $ownerPhone ,WxUser::MP_USER_ID => $mpUserID]);
        $wxUserIDOwner = $wxUser->getWxUserID();
        if(!empty($wxUserIDOwner))
        {
            $mpUserConfig= \WBT\Business\ConfigBusiness::mpUserConfig($mpUserID);
            $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_SERVICE_NOTIFY_ID];//通知模板id
            $url = \WBT\Business\ConfigBusiness::getHost();
            $url = $url."/wx_user/send_owner/index?wx_user_id_owner=".$wxUserIDOwner."&lessee_name=".$lesseeName."&wx_user_id_lessee=".$wxUserIDLessee."&lessee_phone=".$lesseePhone."&mp_user_id=".$mpUserID;

            $infoTitle = "请您帮助".$lesseeName."认证";
            $infoId = "智慧生活服务平台".date("Ymd");
            $remark = "我们将为小区内所有用户提供我们最真诚的服务";
            $template = array( 'touser' => $wxUserIDOwner,
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
            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
            return ['errno' => 0];
        }

        if(empty($wxUserIDOwner))
        {
            return ['errno' => 1, 'error' => '系统中不存在该业主，请联系物业查实此手机号。'];
        }
    }
    //向验证用户发送模板消息
    public function sendLesseeTemplate()
    {
        $wxUserIDLessee = App::getInstance()->request()->get('wx_user_id_lessee');
        $wxUserIDOwner = App::getInstance()->request()->get('wx_user_id_owner');
        $ownerLessee = new OwnerLessee();
        $ownerLessee->setLesseeWxUserID($wxUserIDLessee)->setOwnerWxUserID($wxUserIDOwner)->insert();
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $option = App::getInstance()->request()->get('option');
        $lesseeName = App::getInstance()->request()->get('lessee_name');
        $lesseePhone = App::getInstance()->request()->get('lessee_phone');
        $address = App::getInstance()->request()->get('address');
        $type = App::getInstance()->request()->get('type');

        $mpUserConfig= \WBT\Business\ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_SERVICE_NOTIFY_ID];//通知模板id
        $host = \WBT\Business\ConfigBusiness::getHost();
        if($option == "agree")
        {
            $url = $host."/wx_user/profile/spm_setting?mp_user_id=".$mpUserID;
            $infoTitle = "恭喜，您已通过验证，现在即可访问业主专属内容。点击进入个人设置页面编辑个人信息";
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserIDLessee]);
            $house = new HouseMember([HouseMember::HOUSE_ADDRESS => $address,HouseMember::MP_USER_ID => $mpUserID]);
            $communityID = $house->getCommunityID();
            $wxUser->setCurrentCommunityID($communityID)->setPhone($lesseePhone)->update();
            //插入新的业主信息
            $houseMember = new HouseMember();
            $houseMember->setMpUserID($house->getMpUserID())->setCommunityID($house->getCommunityID())->setWxUserID($wxUserIDLessee)->setHouseAddress($address)->setHouseArea($house->getHouseArea())->setHouseNo($house->getHouseNo())->setVerifyTime(time())->setPhone1($lesseePhone)->setName($lesseeName)->setMemberType($type)->insert();
        }
        else
        {
            $url = $host."/wx_user/user_info/index?mp_user_id=".$mpUserID;
            $infoTitle = "请您与业主联系，业主暂时未通过您的请求";
        }

        $infoId = "智慧生活服务平台".date("Ymd");
        $remark = "我们将为小区内所有用户提供我们最真诚的服务";
        $template = array( 'touser' => $wxUserIDLessee,
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

        WxApiBusiness::sentTemplateMessage($mpUserID,$template);

    }

    public function verifyCode()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $mobilePhone    = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $code     = App::getInstance()->request()->get('code');

        return WxUserBusiness::checkAndBindMemberPhone($communityId,$mpUserId, $wxUserId, $mobilePhone, $code);
    }

    public function verifyCodeRegister()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $mobilePhone    = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $code     = App::getInstance()->request()->get('code');

        return WxUserBusiness::checkAndBindMemberPhoneRegister($communityId,$mpUserId, $wxUserId, $mobilePhone, $code);
    }

    public function checkPhoneExist()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $phone    = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $type = App::getInstance()->request()->get('type');

        return WxUserBusiness::checkPhoneExist($communityId,$wxUserId, $mpUserId, $phone,$type);
    }

    public function checkPhoneCode()
    {
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $phone = App::getInstance()->request()->get('phone');
        $code = App::getInstance()->request()->get('code');
        $communityId = App::getInstance()->request()->get('community_id');

        return WxUserBusiness::checkAndBindMemberPhone($communityId,$mpUserID, $wxUserID, $phone, $code);
    }

    public function saveEmail()
    {
        $phone = App::getInstance()->request()->get('phone');
        $email    = App::getInstance()->request()->get('email');
        log_debug("==========================".$email);
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if ($appUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '非法的用户请求，phone 不存在'];
        }
        try
        {
            $appUser->setEmail($email)->update();
        }
        catch (\Exception $e)
        {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0];
    }

    public function saveBirth()
    {
        $phone = App::getInstance()->request()->get('phone');
        $birth    = App::getInstance()->request()->get('birth');

        $houseMember = new HouseMember([HouseMember::PHONE1 => $phone]);
        $houseNoArray = HouseMember::fetchColumn(HouseMember::HOUSE_NO,[HouseMember::PHONE1 => $phone]);

        $appUser = new AppUser([AppUser::PHONE=>$phone]);

        if ($houseMember->isEmpty())
        {
            return ['errno' => 1, 'error' => '非法的用户请求，wx_user_id 不存在'];
        }

        try
        {
            foreach($houseNoArray as $key => $value)
            {
                $houseMember = new HouseMember([HouseMember::HOUSE_NO => $value]);
                $houseMember->setBirthday($birth)->update();
            }

        }
        catch (\Exception $e)
        {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        if ($appUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '非法的用户请求，该用户手机号不存在'];
        }
        try
        {
            $appUser->setBirth($birth)->update();
        }
        catch (\Exception $e)
        {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public function changeCurrentCommunityId()
    {
        $phone = App::getInstance()->request()->get('phone');
        $currentCommunityId = App::getInstance()->request()->get('community_id');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        //$appUser->setCurrentCommunityID($currentCommunityId)->update();
        try
        {
            $appUser->setCurrentCommunityID($currentCommunityId)->update();
        }
        catch (\Exception $e)
        {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public function saveCsId()
    {
        $phone = App::getInstance()->request()->get('phone');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $csId    = App::getInstance()->request()->get('cs_id');
        $csGroupId    = App::getInstance()->request()->get('cs_group_id');
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $csId]);
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupId]);
        $houseMember = new HouseMember([HouseMember::PHONE1 => $phone,HouseMember::COMMUNITY_ID => $appUser->getCurrentCommunityID()]);
        $houseMember->setCurrentCsID($csId)->setCurrentCsGroupID($csGroupId)->setCsName($cs->getName())->setCsGroupName($csGroup->getGroupName())->update();
        $name = $appUser->getNick();
        $userMessage = $name."已添加您为客服专员";

        if($cs->getWxUserID() != "" and $cs->getValid() == 1)
        {
            $mpUserConfig= \WBT\Business\ConfigBusiness::mpUserConfig($cs->getMpUserID());
            $templateID = $mpUserConfig[\MP\Model\Mp\MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
            $template = array( 'touser' => $cs->getWxUserID(),
                               'template_id' => "$templateID",
                               'url' => "",
                               'topcolor' => "#62c462",
                               'data'   => array('first' => array('value' => urlencode(""),
                                                                  'color' =>"#222", ),
                                                 'keyword1' => array('value' => urlencode($userMessage),'color' =>"#222", ),
                                                 'keyword2' => array('value' => urlencode(date("Y-m-d H:i:s")),
                                                                     'color' =>"#222", ),
                                                 'remark' => array('value' => urlencode("") ,
                                                                   'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($cs->getMpUserID(),$template);
        }
    }

    public function returnCsId()
    {
        $csGroupId    = App::getInstance()->request()->get('cs_group_id');
        $condition = [CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupId,CustomerSpecialist::VALID => "1"];
        $cs = CustomerSpecialist::fetchRows(['*'],$condition);
        $cs = json_encode($cs);
        return "$cs";


    }
    public function returnCs()
    {
        $csGroupId    = App::getInstance()->request()->get('cs_group_id');
        $condition = [CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupId,CustomerSpecialist::VALID => "1"];
        $cs = CustomerSpecialist::fetchRows(['*'],$condition);
        return $cs;
    }

    public function returnGroup()
    {
        $groupName    = App::getInstance()->request()->get('group_name');
        $group = new \MP\Model\Mp\CustomerSpecialistGroup([\MP\Model\Mp\CustomerSpecialistGroup::GROUP_NAME => $groupName]);
        $csGroupId = null;
        if(!$group->isEmpty())
        {
            $csGroupId = $group -> getCustomerSpecialistGroupID();
        }
        $condition = [CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupId,CustomerSpecialist::VALID => "1"];
        $cs = CustomerSpecialist::fetchRows(['*'],$condition);
        log_debug("55555555555",$cs);
        return $cs;
    }

    public function quit()
    {
        $phone = App::getInstance()->request()->get('phone');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $appUser->setIsQuit(1)->update();
        return ['errno' => 0];
    }

    public function login()
    {
        $phone = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $expr = sprintf("`phone1` = '%s' or `phone2` = '%s' or `phone3`='%s'",$phone,$phone,$phone);
        $con = new \Bluefin\Data\DbCondition($expr);
        $condition[] = $con;
        $condition[HouseMember::COMMUNITY_ID] = $communityId;
        $hs = new HouseMember($condition);
        if($hs->isEmpty())
        {
            return ['errno' => 1,'error' => '你还不是本小区验证用户，请填写手机号码进行验证'];
        }
        else
        {
            $appUser->setIsQuit(0)->setCurrentCommunityID($communityId)->update();
            return ['errno' => 0];
        }

    }

}

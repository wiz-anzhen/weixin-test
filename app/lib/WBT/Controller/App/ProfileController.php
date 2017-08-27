<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\Community;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\ConfigBusiness;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\AppUser;
class ProfileController extends BaseController
{
     public function indexAction()
    {
        /*$wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());*/
        $mpUserID = $this->_request->get('mp_user_id');
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $this->_view->set('mp_user', $mpUser->data());

        $directories = [
            [ 'title' => '个人签名：', 'group_end' => 1, 'content' => '', 'type' => 'text'],
            [ 'title' => '优惠券包', 'group_end' => 0, 'target' => '', 'type' => 'link'],
            [ 'title' => '积分', 'group_end' => 0, 'content' => '3600', 'type' => 'text'],
            [ 'title' => '账户余额', 'group_end' => 1, 'content' => '1000', 'type' => 'text'],
            [ 'title' => '物业缴费通知', 'group_end' => 0, 'target' => '', 'type' => 'link'],
        ];
        $this->_view->set( 'directories', $directories);
    }

    public function settingAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $data = [
            'head_pic' => $wxUser->getHeadPic(),
            'name' => $wxUser->getNick(),
            'gender' => ($wxUser->getGender() == 'male') ? '男' : '女',
            'address' => $wxUser->getAddress(),
            'idiograph' => $wxUser->getIdiograph(),
        ];

        if (empty($data['name'])) $data['name'] = '未认证';
        if (empty($data['gender'])) $data['gender'] = '未认证';
        if (empty($data['address'])) $data['address'] = '未认证    ';
        if (empty($data['idiograph'])) $data['idiograph'] = '未填写';

        $this->_view->appendData( $data);
    }

    public function spmSettingAction()
    {
        $mpUserID = $this->_request->get('mp_user_id');
        $this->_view->set('mp_user_id', $mpUserID);
        $phone = $this->_request->get('phone');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('app_user', $appUser->data());
        if(!$appUser->isEmpty())
        {
            $currentCommunityID = $appUser->getCurrentCommunityID();
            $this->_view->set('current_community_id', $currentCommunityID);

            $houseMember = new HouseMember([HouseMember::PHONE1=>$phone,HouseMember::COMMUNITY_ID => $currentCommunityID]);
            $this->_view->set('house_member', $houseMember->data());

            $communityIdArray = HouseMember::fetchColumn(HouseMember::COMMUNITY_ID,[HouseMember::PHONE1 => $phone]);
            $condition        = [HouseMember::COMMUNITY_ID => $communityIdArray];
            $community = Community::fetchRows([Community::NAME,HouseMember::COMMUNITY_ID],$condition);
            $this->_view->set('community_list', $community);

            $appUserBirthday = $appUser->getBirth();
            $year = substr($appUserBirthday,0,4);
            $month = substr($appUserBirthday,4,2);
            $day = substr($appUserBirthday,-2);
            $appUserBirthday = $year."年".$month."月".$day."日";
            $this->_view->set("app_user_birthday",$appUserBirthday);
            $wx_user = new WxUser([WxUser::WX_USER_ID=>$houseMember->getWxUserID()]);
            $head_pic = $appUser->getHeadPic();
            $name = $appUser->getName();
            $nick = $appUser->getNick();
            $vipNo = $appUser->getVipNo();
            if(empty($head_pic))
            {
                $head_pic = $wx_user->getHeadPic();
            }
            if(empty($name))
            {
                $name = $wx_user->getName();
            }
            if(empty($nick))
            {
                $nick = $wx_user->getNick();
            }
            if(empty($vipNo))
            {
                $vipNo = $wx_user->getVipNo();
            }
            $data = [
                'head_pic' => $head_pic,
                'vip_no' => $vipNo,
                'nick' => $nick,
                'name' => $name,
                'gender' => ($appUser->getGender() == 'male') ? '男' : '女',
                'address' => $appUser->getAddress(),
                'city' => $appUser->getCity(),
                'community_name' => $appUser->getCommunityName(),
                'idiograph' => $appUser->getIdiograph(),
            ];
            if (empty($data['vip_no'])) $data['vip_no']           = '';
            if (empty($data['nick'])) $data['nick']           = '未设置';
            if (empty($data['name'])) $data['name']           = '未设置';
            if (empty($data['gender'])) $data['gender']       = '未设置';
            if (empty($data['address'])) $data['address']     = '未设置';
            if (empty($data['city'])) $data['city']     = '未设置';
            if (empty($data['community_name'])) $data['community_name']     = '未设置';
            if (empty($data['idiograph'])) $data['idiograph'] = '未设置';
            $this->_view->set('nick', $data['nick']);
            $this->_view->set('name', $data['name']);
            $this->_view->set('vip_no', $data['vip_no']);
            $this->_view->set('head_pic', $data['head_pic']);
            $this->_view->appendData($data);

            $isOwner = false;

            if (!$houseMember ->isEmpty()) {
                $memberType = $houseMember->getMemberType();
                if ($memberType == "owner") {
                    $isOwner = true;
                }
            }
            $this->_view->set('is_owner', $isOwner);

            $cs = new CustomerSpecialist([CustomerSpecialist::PHONE => $phone,CustomerSpecialist::COMMUNITY_ID => $currentCommunityID]);
            if(!($cs->isEmpty()))
            {
                $this->_view->set('cs', true);
                $csData = $cs->data();
                $this->_view->set('cs', $csData);
            }
            $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
            $csVisible = $mpUser->getCsVisible();
            $this->_view->set('cs_visible', $csVisible);
        }
        else
        {
            $this->_redirectToErrorPage("无效的APP用户");
        }





    }

    public function changeEmailAction()
    {
        $phone = $this->_request->get('phone');
        $appUser =  new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('app_user', $appUser->data());

    }
    public function changeNameAction()
    {
        $phone = $this->_request->get('phone');
        $appUser =  new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('app_user', $appUser->data());

    }

    public function changeBirthAction()
    {
        $phone = $this->_request->get('phone');
        $appUser =  new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('app_user', $appUser->data());
        $appUserBirthday = $appUser->getBirth();
        $year = substr($appUserBirthday,0,4);
        $month = substr($appUserBirthday,4,2);
        $day = substr($appUserBirthday,-2);
        $this->_view->set("year",$year);
        $this->_view->set("month",$month);
        $this->_view->set("day",$day);
        $year_list = range("1930","2000");
        $month_list = range("1","12");
        $day_list = range("1","31");
        $this->_view->set("year_list",$year_list);
        $this->_view->set("month_list",$month_list);
        $this->_view->set("day_list",$day_list);

    }

    public function changeCustomerIdAction()
    {
        $currentCsId = $this->_request->get("current_cs_id");
        $phone = $this->_request->get('phone');
        $appUser =  new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('app_user', $appUser->data());
        $this->_view->set('current_cs_id', $currentCsId);
        if(!empty($currentCsId))
        {
            $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $currentCsId]);
            $csGroupId = $cs->getCustomerSpecialistGroupID();
            $this->_view->set('cs_group_id', $csGroupId);
        }
        $communityId = $appUser->getCurrentCommunityID();
        $condition =[CustomerSpecialistGroup::COMMUNITY_ID => $communityId];
        $csGroup =  CustomerSpecialistGroup::fetchRows( [ '*' ],$condition);
        $this->_view->set('cs_group', $csGroup);

        $condition = [CustomerSpecialist::VALID => "1",CustomerSpecialist::COMMUNITY_ID => $communityId];
        $cs = CustomerSpecialist::fetchRows([ '*' ],$condition);
        $this->_view->set('cs', $cs);

    }

}
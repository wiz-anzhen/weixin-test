<?php

namespace WBT\Controller\WxUser;

use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\Community;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\ConfigBusiness;
use WBT\Controller\WxUserControllerBase;
use Common\Helper\BaseController;
class ProfileController extends WxUserControllerBase
{
     public function indexAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());
        $mpUser = new MpUser([MpUser::MP_USER_ID => $wxUser->getMpUserID()]);
        $this->_view->set('mp_user', $mpUser->data());

        $directories = [
            [ 'title' => '个人签名：', 'group_end' => 1, 'content' => $wxUser->getIdiograph(), 'type' => 'text'],
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
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());
        $this->_view->set('mp_user_id', $wxUser->getMpUserID());


        $currentCommunityID = $wxUser->getCurrentCommunityID();
        $this->_view->set('current_community_id', $currentCommunityID);

        $houseMember = new HouseMember([HouseMember::WX_USER_ID => $this->_wxUserID,HouseMember::COMMUNITY_ID => $currentCommunityID]);
        $this->_view->set('house_member', $houseMember->data());

        $communityIdArray = HouseMember::fetchColumn(HouseMember::COMMUNITY_ID,[HouseMember::WX_USER_ID => $this->_wxUserID]);
        $condition        = [HouseMember::COMMUNITY_ID => $communityIdArray];
        $community = Community::fetchRows([Community::NAME,HouseMember::COMMUNITY_ID],$condition);
        $this->_view->set('community_list', $community);

        $wxUserBirthday = $wxUser->getBirth();
        $year = substr($wxUserBirthday,0,4);
        $month = substr($wxUserBirthday,4,2);
        $day = substr($wxUserBirthday,-2);
        $wxUserBirthday = $year."年".$month."月".$day."日";
        $this->_view->set("wx_user_birthday",$wxUserBirthday);
        $data = [
            'head_pic' => $wxUser->getHeadPic(),
            'name' => $wxUser->getNick(),
            'gender' => ($wxUser->getGender() == 'male') ? '男' : '女',
            'address' => $wxUser->getAddress(),
            'idiograph' => $wxUser->getIdiograph(),
        ];

        if (empty($data['name'])) $data['name']           = '未认证';
        if (empty($data['gender'])) $data['gender']       = '未认证';
        if (empty($data['address'])) $data['address']     = '未认证';
        if (empty($data['idiograph'])) $data['idiograph'] = '未填写';

        $this->_view->appendData($data);

        $isOwner = false;

        if (!$houseMember ->isEmpty()) {
            $memberType = $houseMember->getMemberType();
            if ($memberType == "owner") {
                $isOwner = true;
            }
        }
        $this->_view->set('is_owner', $isOwner);

        $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $this->_wxUserID,CustomerSpecialist::COMMUNITY_ID => $currentCommunityID]);
        if(!($cs->isEmpty()))
        {
            $this->_view->set('cs', true);
            $csData = $cs->data();
            $this->_view->set('cs', $csData);
        }
        $mpUserId = $wxUser->getMpUserID();
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $csVisible = $mpUser->getCsVisible();
        $industry = $mpUser->getIndustry();
        $this->_view->set('industry', $industry);
        $this->_view->set('cs_visible', $csVisible);

    }

    public function changeEmailAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());

    }

    public function changeNameAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());

    }

    public function changeBirthAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserBirthday = $wxUser->getBirth();
        $year = substr($wxUserBirthday,0,4);
        $month = substr($wxUserBirthday,4,2);
        $day = substr($wxUserBirthday,-2);
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
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('current_cs_id', $currentCsId);
        if(!empty($currentCsId))
        {
            $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $currentCsId]);
            $csGroupId = $cs->getCustomerSpecialistGroupID();
            $this->_view->set('cs_group_id', $csGroupId);
        }
        $communityId = $wxUser->getCurrentCommunityID();
        $condition =[CustomerSpecialistGroup::COMMUNITY_ID => $communityId];
        $csGroup =  CustomerSpecialistGroup::fetchRows( [ '*' ],$condition);
        $this->_view->set('cs_group', $csGroup);

        $condition = [CustomerSpecialist::VALID => "1",CustomerSpecialist::COMMUNITY_ID => $communityId];
        $cs = CustomerSpecialist::fetchRows([ '*' ],$condition);
        $this->_view->set('cs', $cs);

    }

}
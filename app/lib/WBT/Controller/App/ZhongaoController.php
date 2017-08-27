<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Controller\WxUserControllerBase;

class ZhongaoController extends BaseController
{
    public function indexAction()
    {
        $wxUserId = $this->_request->get( 'wx_user_id' );

        $this->_view->set('wx_user_id', $wxUserId);
    }

    public function mineAction()
    {
        $this->_certifiedUserRequired();
        $wxUserId = $this->_request->get('wx_user_id');
        $this->_view->set('wx_user_id', $wxUserId);
    }

    public function myHouseAction()
    {
        $wxUserId = $this->_request->get( 'wx_user_id' );

        $this->_view->set('wx_user_id', $wxUserId);
    }

    public function userAction()
    {
        $currentHouseMemberId  = $this->_request->get('house_member_id');
        $phone  = $this->_request->get('phone');
        $houseMember = new HouseMember([HouseMember::PHONE1 => $phone,HouseMember::MEMBER_TYPE => HouseMemberType::OWNER]);
        if ($houseMember->isEmpty())
        {
            exit('对不起，你还不是本社区的业主或住户，不能执行该操作');
        }
// houseId 为空
        $condition = [HouseMember::PHONE1 => $phone,HouseMember::MEMBER_TYPE => HouseMemberType::OWNER];
        $query = HouseMember::fetchRows([HouseMember::HOUSE_MEMBER_ID], $condition);
        if (count($query) == 0) {
            exit('没找到属于你的房子。。。');
        }
        // 我的房子
        $houseMemberId = [];
        foreach($query as $houseMember) {
            $houseMemberId[] = $houseMember[HouseMember::HOUSE_MEMBER_ID];
        }

        $this->_view->set('current_house_id', $currentHouseMemberId);
        $this->_view->set('phone', $phone);

        $query = HouseMember::fetchRows(['*'], [HouseMember::HOUSE_MEMBER_ID => $houseMemberId]);
        if (count($query) == 0) {
            exit('没有找到属于你的房子。。。');
        }
        $this->_view->set('my_houses', $query);


        // 已添加的人
        if (!in_array($currentHouseMemberId, $houseMemberId)) {
            $currentHouseMemberId = $houseMemberId[0];
        }
        $houseMember = new HouseMember([HouseMember::HOUSE_MEMBER_ID => $currentHouseMemberId]);
        $address = $houseMember->getHouseAddress();
        $condition = [HouseMember::HOUSE_ADDRESS => $address,HouseMember::ADD_BY   => $phone];
        $addedMembers = HouseMember::fetchRows(['*'], $condition);
        $this->_view->set('added_members', $addedMembers);
    }
}
<?php

namespace WBT\Controller\MpAdmin;

/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-3-25
 * Time: 下午3:11
 * 房屋录入
 */

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\DirectoryType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Directory;
use MP\Model\Mp\Bill;
use MP\Model\Mp\WxUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\BillBusiness;
use WBT\Business\Weixin\HouseMemberBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\IndustryType;


class FunctionController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "";
        parent::_init();
    }
    //显示
    public function listAction()
    {
        $mpUserID = $this->_request->get(MpUser::MP_USER_ID);
        $this->_view->set(MpUser::MP_USER_ID, $mpUserID);
        $mpUser        = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $communityId   = $this->_request->get('community_id');
        $community     = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set("community_name", $communityName);
        $this->_view->set("community_type_all", $community->getCommunityType());
        $this->_view->set('mp_name', $mpUser->getMpName());
        $this->_view->set('user_id', $userId = UserBusiness::getLoginUser()->getUserID());
        $this->_view->set('community_id', $communityId);
    }

}
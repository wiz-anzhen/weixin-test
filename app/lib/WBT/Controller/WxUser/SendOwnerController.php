<?php

namespace WBT\Controller\WxUser;

use Common\Helper\BaseController;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\Community;
use MP\Model\Mp\OwnerLessee;

class SendOwnerController extends WxUserControllerBase
{
    public function indexAction()
    {
        $wxUserIDOwner = $this->_request->get('wx_user_id_owner');
        $wxUserIDLessee = $this->_request->get('wx_user_id_lessee');
        $ownerLessee = new OwnerLessee([OwnerLessee::OWNER_WX_USER_ID => $wxUserIDOwner,OwnerLessee::LESSEE_WX_USER_ID  => $wxUserIDLessee]);
        if($ownerLessee->isEmpty())
        {
            $this->_view->set('owner_lessee', true);
        }
        $this->_view->set('wx_user_id_lessee', $wxUserIDLessee);
        $this->_view->set('wx_user_id_owner', $wxUserIDOwner);

        $houseAddress = HouseMember::fetchRows([ '*' ],[HouseMember::WX_USER_ID => $wxUserIDOwner]);
        foreach($houseAddress as $key =>$value)
        {
            $community = new Community([Community::COMMUNITY_ID => $value[Community::COMMUNITY_ID]]);
            $houseAddress[$key]["community_name"] = $community->getName();
        }
        log_debug("======================",$houseAddress);
        $this->_view->set('house_address', $houseAddress);
        $houseMemberType = HouseMemberType::getDictionary();
        $this->_view->set('house_type', $houseMemberType);
        $lesseeName = $this->_request->get( 'lessee_name' );
        $this->_view->set('lessee_name', $lesseeName);
        $lesseePhone = $this->_request->get( 'lessee_phone' );
        $this->_view->set('lessee_phone', $lesseePhone);


    }

}

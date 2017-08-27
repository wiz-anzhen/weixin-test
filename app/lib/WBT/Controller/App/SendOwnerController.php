<?php

namespace WBT\Controller\WxUser;

use Common\Helper\BaseController;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;

class SendOwnerController extends WxUserControllerBase
{
    public function indexAction()
    {
        $wxUserIDOwner = $this->_request->get('wx_user_id_owner');
        $wxUserIDLessee = $this->_request->get('wx_user_id_lessee');
        $this->_view->set('wx_user_id_lessee', $wxUserIDLessee);

        $houseAddress = HouseMember::fetchColumn([HouseMember::HOUSE_ADDRESS],[HouseMember::WX_USER_ID => $wxUserIDOwner]);
        $this->_view->set('house_address', $houseAddress);
        $houseMemberType = HouseMemberType::getDictionary();
        $this->_view->set('house_type', $houseMemberType);
        $lesseeName = $this->_request->get( 'lessee_name' );
        $this->_view->set('lessee_name', $lesseeName);
        $lesseePhone = $this->_request->get( 'lessee_phone' );
        $this->_view->set('lessee_phone', $lesseePhone);

    }

}

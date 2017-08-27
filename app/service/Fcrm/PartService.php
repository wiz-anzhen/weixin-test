<?php

use MP\Model\Mp\Store;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Product;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\PartBusiness;
use MP\Model\Mp\UserNotify;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\IndustryType;
use WBT\Business\UserBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\Part;

require_once 'MpUserServiceBase.php';

class PartService extends MpUserServiceBase
{
    // 商城
    public function partUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Part::PART_ID  );
        $data = $this->_app->request()->getArray( [ Part::BOUND_STORE_ID,Part::COMMENT,Part::TITLE ] );

        return PartBusiness::partUpdate( $id, $data );
    }

    public function partInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Part::MP_USER_ID,  Part::COMMUNITY_ID, Part::TITLE, Part::COMMENT, Part::BOUND_STORE_ID] );

        return PartBusiness::partInsert( $data );
    }

    public function partDelete()
    {
        $id = $this->_app->request()->get( Part::PART_ID );

        return PartBusiness::partDelete( $id );


    }

}
<?php

use MP\Model\Mp\Store;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Product;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\RestaurantBusiness;
use MP\Model\Mp\UserNotify;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\IndustryType;
use WBT\Business\UserBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\UserNotifySendStatus;

require_once 'MpUserServiceBase.php';

class RestaurantService extends MpUserServiceBase
{
    // 商城
    public function restaurantUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Restaurant::RESTAURANT_ID );
        $data = $this->_app->request()->getArray( [ Restaurant::TITLE, Restaurant::COMMENT, ] );

        return RestaurantBusiness::restaurantUpdate( $id, $data );
    }

    public function restaurantInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Restaurant::MP_USER_ID, Restaurant::TITLE, Restaurant::COMMENT, Restaurant::COMMUNITY_ID] );

        return RestaurantBusiness::restaurantInsert( $data );
    }

    public function restaurantDelete()
    {
        $id = $this->_app->request()->get( Restaurant::RESTAURANT_ID );

        return RestaurantBusiness::restaurantDelete( $id );


    }

}
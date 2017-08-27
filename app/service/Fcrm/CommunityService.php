<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\Community;
use WBT\Business\Weixin\CommunityBusiness;
use WBT\Business\Weixin\WxUserBusiness;

class CommunityService extends MpUserServiceBase
{

    public function communityUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Community::COMMUNITY_ID );
        $mpUserID  = $this->_app->request()->getQueryParam( Community::MP_USER_ID );
        $data = $this->_app->request()->getArray( [
            Community::NAME,
            Community::PHONE,
            Community::ADMIN_EMAIL,
            Community::ADMIN_CC_EMAIL,
            Community::IS_VIRTUAL,
            Community::IS_APP,
            Community::VALID,
            Community::COMMENT,
            Community::BILL_NAME,
            Community::ADDRESS,
            Community::PROVINCE,
            Community::CITY,
            Community::AREA,
            Community::BILL_COMMENT,
            Community::COMMUNITY_TYPE,
        ] );

        if(empty($data[Community::COMMUNITY_TYPE]))
        {
            $data[Community::COMMUNITY_TYPE] = \MP\Model\Mp\CommunityType::NONE;
        }
        $address = $data[Community::ADDRESS];
        $city = $data[Community::CITY];
        if($data[Community::IS_VIRTUAL] == 0 or $data[Community::IS_APP] == 1 )
        {
            if(empty($address) or empty($city))
            {
                return['errno' => 1,'error' => '请填写城市和详细地址'];
            }
        }
        $expr = sprintf("`community_id` != '%s' ",$id);

        $dbCondition = new \Bluefin\Data\DbCondition($expr);

        $condition = [$dbCondition,Community::NAME => $data[Community::NAME],Community::MP_USER_ID => $mpUserID];
        $communityCount = Community::fetchCount($condition);

        if($communityCount >= 1)
        {
            return['errno' => 1,'error' => '对不起，'.$data[Community::NAME]."已存在，请重新输入社区名称"];
        }
        $precision =  WxUserBusiness::getPrecision($address,$city);
        $data[Community::LONGITUDE] = $precision['lng'];
        $data[Community::LATITUDE] = $precision['lat'];
        return CommunityBusiness::communityUpdate( $id, $data );
    }

    public function communityInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            Community::MP_USER_ID,
            Community::NAME,
            Community::PHONE,
            Community::ADMIN_EMAIL,
            Community::ADMIN_CC_EMAIL,
            Community::IS_VIRTUAL,
            Community::IS_APP,
            Community::COMMENT,
            Community::BILL_NAME,
            Community::ADDRESS,
            Community::PROVINCE,
            Community::CITY,
            Community::AREA,
            Community::BILL_COMMENT,
            Community::COMMUNITY_TYPE,
        ] );
        $address = $data[Community::ADDRESS];
        $city = $data[Community::CITY];
        if($data[Community::IS_VIRTUAL] == 0 or $data[Community::IS_APP] == 1 )
        {
            if(empty($address) or empty($city))
            {
                return['errno' => 1,'error' => '请填写城市和详细地址'];
            }
        }
        $community = new Community([Community::NAME => $data[Community::NAME],Community::MP_USER_ID => $data[Community::MP_USER_ID]]);
        if(!$community->isEmpty())
        {
            return['errno' => 1,'error' => '对不起，'.$data[Community::NAME]."已存在，请重新输入社区名称"];
        }
        $precision =  WxUserBusiness::getPrecision($address,$city);
        $data[Community::LONGITUDE] = $precision['lng'];
        $data[Community::LATITUDE] = $precision['lat'];
        return CommunityBusiness::communityInsert( $data );
    }

    public function communityDelete()
    {
        $id = $this->_app->request()->get( Community::COMMUNITY_ID );

        return CommunityBusiness::communityDelete( $id );
    }
}
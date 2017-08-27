<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Community;
use MP\Model\Mp\Directory;
use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use WBT\Business\Weixin\SendTemplateBusiness;

class RestaurantBusiness extends BaseBusiness
{
    public static function getList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return Restaurant::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function restaurantInsert( $data )
    {
        $restaurant = new Restaurant([Restaurant::TITLE =>  $data[Restaurant::TITLE],Restaurant::MP_USER_ID => $data[Restaurant::MP_USER_ID]]);
        if(!$restaurant->isEmpty())
        {
            return [ 'errno' => 1, 'error' => $data[Restaurant::TITLE].'已存在，请重新输入' ];
        }
        $obj = new Restaurant();
        $obj->apply( $data );
        $obj->insert();

        $communityRestaurant = new Community([Community::COMMUNITY_ID => $data[Store::COMMUNITY_ID]]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_TOTAL)
        {
            $communityAdminData =  CommunityAdmin::fetchRows(['*'],[CommunityAdmin::COMMUNITY_ID => $communityRestaurant->getCommunityID()]);

            $community = new Community([Community::NAME => $data[Restaurant::TITLE],Community::MP_USER_ID => $data[Restaurant::MP_USER_ID]]);
            if($community->isEmpty())
            {
                $community = new Community();
                $community->setName($data[Restaurant::TITLE])->setMpUserID($data[Restaurant::MP_USER_ID])->setIsVirtual(0)->setCommunityType(CommunityType::PROCUREMENT_RESTAURANT)->setCity("待填")->setAddress("待填")->insert();
                foreach($communityAdminData as $key => $value)
                {
                    $communityAdmin = new CommunityAdmin();
                    $communityAdmin->setUsername($value[CommunityAdmin::USERNAME])->setPower($value[CommunityAdmin::POWER])->setAdminUsername($value[CommunityAdmin::ADMIN_USERNAME])->setComment($value[CommunityAdmin::COMMENT])->setMpUserID($value[CommunityAdmin::MP_USER_ID])->setCommunityID($community->getCommunityID())->insert();
                }
                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '223',Directory::COMMUNITY_ID => $community->getCommunityID(),Directory::MP_USER_ID => $community->getMpUserID()]);
            }
            else
            {
                return [ 'errno' => 1, 'error' => $data[Restaurant::TITLE].'已存在，请重新输入' ];
            }
            $data[Store::BOUND_COMMUNITY_ID] = $community->getCommunityID();

        }


        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }


    public static function restaurantUpdate( $id, $data )
    {
        $obj = new Restaurant([ Restaurant::RESTAURANT_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $expr = sprintf("`restaurant_id` != '%s' ",$id);
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition = [$dbCondition,Restaurant::TITLE =>  $data[Restaurant::TITLE],Restaurant::MP_USER_ID => $obj->getMpUserID()];
        $restaurantCounts = Restaurant::fetchCount($condition);
        if($restaurantCounts >= 1)
        {
            return [ 'errno' => 1, 'error' => $data[Store::TITLE].'已存在，请重新输入' ];
        }

        $communityRestaurant = new Community([Community::COMMUNITY_ID => $obj->getCommunityID()]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_TOTAL)
        {
            $communityAdminData =  CommunityAdmin::fetchRows(['*'],[CommunityAdmin::COMMUNITY_ID => $communityRestaurant->getCommunityID()]);

            $community = new Community([Community::NAME => $data[Restaurant::TITLE],Community::MP_USER_ID => $obj->getMpUserID()]);
            if($community->isEmpty())
            { 
                $community = new Community();
                $community->setName($data[Restaurant::TITLE])->setMpUserID($obj->getMpUserID())->setIsVirtual(1)->setCommunityType(CommunityType::PROCUREMENT_RESTAURANT)->setCity("待填")->setAddress("待填")->insert();

                foreach($communityAdminData as $key => $value)
                {
                    $communityAdmin = new CommunityAdmin();
                    $communityAdmin->setUsername($value[CommunityAdmin::USERNAME])->setPower($value[CommunityAdmin::POWER])->setAdminUsername($value[CommunityAdmin::ADMIN_USERNAME])->setComment($value[CommunityAdmin::COMMENT])->setMpUserID($value[CommunityAdmin::MP_USER_ID])->setCommunityID($community->getCommunityID())->insert();
                }

                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '223',Directory::COMMUNITY_ID => $community->getCommunityID(),Directory::MP_USER_ID => $community->getMpUserID()]);
            }
            else
            { return [ 'errno' => 1, 'error' => $data[Restaurant::TITLE].'已存在，请重新输入' ];
            }

            $data[Store::BOUND_COMMUNITY_ID] = $community->getCommunityID();
        }

        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function restaurantDelete( $id )
    {
        $obj = new Restaurant([ Restaurant::RESTAURANT_ID => $id ]);
        log_debug("=====================",$obj->getBoundCommunityID());
        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->delete();
        return [ 'errno' => 0 ];
    }

}
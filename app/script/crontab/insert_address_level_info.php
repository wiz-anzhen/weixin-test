<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-8-13
 * Time: 下午3:07
 */
require_once '../../../lib/Bluefin/bluefin.php';

use MP\Model\Mp\HouseMember;
use MP\Model\Mp\AddressLevelInfo;
use MP\Model\Mp\Community;

insert_address_level_info();

function insert_address_level_info()
{
    $communityIds = Community::fetchColumn(Community::COMMUNITY_ID);
    $communityIds = array_unique($communityIds);
    log_debug("===================",$communityIds);
    foreach($communityIds as $communityId)
    {
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $mpUserID = $community->getMpUserID();
        $houseMemberAddressArray = HouseMember::fetchColumn(HouseMember::HOUSE_ADDRESS,[
            HouseMember::MP_USER_ID=>$mpUserID,
            HouseMember::COMMUNITY_ID=>$communityId
        ]);

        $expr = "address_level_info_id is not null";
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition = [$dbCondition,'community_id' => $communityId];
        $addLevelInfo = new AddressLevelInfo($condition);
        if(!($addLevelInfo->isEmpty()))
        {
            $addLevelInfo->delete($condition);
        }

        foreach($houseMemberAddressArray as $key => $value)
        {

            if(strpos($value,'->'))
            {
                $addressLevelArray = explode("->",$value);

                for($k=1;$k<count($addressLevelArray);$k++)
                {
                    $addressLevelInfo = new AddressLevelInfo();
                    if($k==1)
                    {
                        $addInfoId = 0;
                    }
                    $addInfo = new AddressLevelInfo([
                        AddressLevelInfo::LEVEL => $k,
                        AddressLevelInfo::COMMUNITY_ID => $communityId,
                        AddressLevelInfo::ADD_INFO => $addressLevelArray[$k],
                        AddressLevelInfo::PARENT_ID => $addInfoId
                    ]);

                    if($addInfo->isEmpty())
                    {
                        $addressLevelInfo->setMpUserID($mpUserID)
                            ->setCommunityID($communityId)
                            ->setAddInfo($addressLevelArray[$k])
                            ->setLevel($k)
                            ->setParentID($addInfoId)
                            ->insert(true);

                        $addInfoId = $addressLevelInfo->getAddressLevelInfoID();
                    }
                    else
                    {
                        $addInfoId = $addInfo->getAddressLevelInfoID();
                    }
                }
            }
        }
    }

}
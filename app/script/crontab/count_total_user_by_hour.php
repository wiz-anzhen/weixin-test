<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-8-13
 * Time: 下午3:07
 */
require_once '../../../lib/Bluefin/bluefin.php';

use MP\Model\Mp\HouseMember;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\AppUser;
use MP\Model\Mp\TotalUser;
count_total_user();

function count_total_user()
{
    //1计算用户总数 总数包括3个方面   社区用户数+微信用户数+app用户数
    $community_total_user = 0;
    $weixin_total_user = 0;
    $app_total_user = 0;
    //先将mp_user表中有效的mp公共账号 mp_user_id取出.
    $mpUserIdArray =MpUser::fetchColumn(MpUser::MP_USER_ID,[MpUser::VALID=>1]);

    foreach($mpUserIdArray as $mpUserID)
    {
        //先取社区用户数
        $mpTotalUser = HouseMember::fetchCount([HouseMember::MP_USER_ID=>$mpUserID]);
        $community_total_user += $mpTotalUser;
        //取微信用户数
        $weixinTotalUser = WxUser::fetchCount([WxUser::MP_USER_ID=>$mpUserID]);
        $weixin_total_user += $weixinTotalUser;
    }
    //取app用户数
    $appTotalUser = AppUser::fetchCount([]);
    $app_total_user += $appTotalUser;
    //
    $totalUserNum = $community_total_user+$weixin_total_user+$app_total_user;
    //2,计算当前时间段用户活跃度 包括app与微信用户活跃度
    //获取当前时间段的前一个小时
    $hour = intval(date('H',time()));
    $currentHour = $hour-1;
    if($currentHour == -1)
    {
        $currentHour = 23;
        $ee = mktime (0,0,0,date("m") ,date("d")-1,date("Y"));
        $ymd = date("Y-m-d",($ee));
    }else{
        $ymd = date('Y').'-'.date('m').'-'.date('d');
    }

    //计算微信用户时间段里的活跃度
    $expr = "last_access >= '" . $ymd ." ".$currentHour.":00:00' and last_access <= '" . $ymd ." ".$currentHour.":59:59'";
    $dbCondition = new \Bluefin\Data\DbCondition($expr);
    $condition[] = $dbCondition;
    $weixinActiveUser = WxUser::fetchCount($condition);
    //计算app用户时间段里的活跃度
    $appActiveUser = AppUser::fetchCount($condition);
    log_debug('weixinActiveUser===========',$weixinActiveUser);
    log_debug('appActiveUser==============',$appActiveUser);
    $activeUserNum = $weixinActiveUser+$appActiveUser;
    $totalUser = new TotalUser();
    $totalUser->setTotalUserNum($totalUserNum)
              ->setActiveUserNum($activeUserNum)
              ->setInsertHour($currentHour)
              ->setInsertTime(date('Y-m-d H:i:s',time()))
              ->insert();

    /*$communityIds = Community::fetchColumn(Community::COMMUNITY_ID);
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
    }*/

}
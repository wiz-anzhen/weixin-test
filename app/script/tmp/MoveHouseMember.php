<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 14-6-30
 * Time: 下午5:17
 */
require_once '../../../lib/Bluefin/bluefin.php';

use Bluefin\App;
use Bluefin\Controller;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;


moveHouseMember();

function moveHouseMember()
{
    $wxUserIDs = WxUser::fetchColumn([WxUser::WX_USER_ID]);
    foreach($wxUserIDs as $value)
    {
        $wxUser = new WxUser([WxUser::WX_USER_ID => $value]);
        $csID = $wxUser->getCurrentCsID();
        $csGroupID = $wxUser->getCurrentCsGroupID();
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $csID]);
        $csName = $cs->getName();
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);
        $csGroupName = $csGroup->getGroupName();
        $houseMemberIDs = HouseMember::fetchColumn([HouseMember::HOUSE_MEMBER_ID],[HouseMember::WX_USER_ID => $value]);
        foreach($houseMemberIDs as  $v)
        {
            $houseMember = new HouseMember([HouseMember::HOUSE_MEMBER_ID => $v]);
            $houseMember->setCsGroupName($csGroupName)->setCsName($csName)->setCurrentCsGroupID($csGroupID)->setCurrentCsID($csID)->update();
        }

    }
}

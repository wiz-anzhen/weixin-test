<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\MemberRelation;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\AppUser;

class ZhongaoBusiness extends BaseBusiness
{
    /*
     * @param $wxUserId 操作人的微信ID，即业主
     */
    public static function removeMember($houseMemberId)
    {
        $houseMember = new HouseMember([HouseMember::HOUSE_MEMBER_ID => $houseMemberId]);


        if ($houseMember->isEmpty())
        {
            log_debug("Could not find Store($houseMemberId)");

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try
        {
            $houseMember->delete();
        }
        catch (\Exception $e)
        {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0];
    }

    public static function addMember($wxUserId, $houseMemberId, $name, $phone1, $memberType)
    {
        if (strlen($name) < 2)
        {
            return ['errno' => 1, 'error' => '姓名太短了'];
        }
        if (!preg_match('/^1\d{10}$/', $phone1))
        {
            return ['errno' => 1, 'error' => '请严格填写11位电话号码'];
        }
        if (!in_array($memberType, HouseMemberType::getValues()))
        {
            return ['errno' => 1, 'error' => '错误的业主关系类型'];
        }

        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if ($wxUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '非法的用户'];
        }
        if (HouseMember::fetchCount([HouseMember::ADD_BY => $wxUserId, HouseMember::HOUSE_MEMBER_ID => $houseMemberId]) >= 5)
        {
            return ['errno' => 1, 'error' => '最多只能添加5个住户'];
        }


        $houseMemberOwner = new HouseMember([HouseMember::WX_USER_ID => $wxUserId, HouseMember::HOUSE_MEMBER_ID => $houseMemberId]);
        $mpUserId         = $houseMemberOwner->getMpUserID();
        $communityId      = $houseMemberOwner->getCommunityID();
        $houseAddress     = $houseMemberOwner->getHouseAddress();
        $houseArea        = $houseMemberOwner->getHouseArea();
        $houseMemberType  = $houseMemberOwner->getMemberType();
        $houseMember      = new HouseMember();

        $houseMember->setHouseArea($houseArea)->setPhone1($phone1)->setHouseNo($houseMemberOwner->getHouseNo())->setHouseAddress($houseAddress)->setName($name)->setAddBy($wxUserId)->setMemberType($memberType)->setMpUserID($mpUserId)->setCommunityID($communityId)->setAddType($houseMemberType)->insert(true);

        return ['errno' => 0];
    }

    public static function appAddMember($phone,$houseMemberId, $name, $phone1, $memberType)
    {
        if (strlen($name) < 2)
        {
            return ['errno' => 1, 'error' => '姓名太短了'];
        }
        if (!preg_match('/^1\d{10}$/', $phone1))
        {
            return ['errno' => 1, 'error' => '请严格填写11位电话号码'];
        }
        if (!in_array($memberType, HouseMemberType::getValues()))
        {
            return ['errno' => 1, 'error' => '错误的业主关系类型'];
        }

        $appUser = new AppUser([AppUser::PHONE => $phone]);
        if ($appUser->isEmpty())
        {
            return ['errno' => 1, 'error' => '非法的用户'];
        }
        if (HouseMember::fetchCount([HouseMember::ADD_BY => $phone, HouseMember::HOUSE_MEMBER_ID => $houseMemberId]) >= 5)
        {
            return ['errno' => 1, 'error' => '最多只能添加5个住户'];
        }


        $houseMemberOwner = new HouseMember([HouseMember::WX_USER_ID => $phone, HouseMember::HOUSE_MEMBER_ID => $houseMemberId]);
        $mpUserId         = $houseMemberOwner->getMpUserID();
        $communityId      = $houseMemberOwner->getCommunityID();
        $houseAddress     = $houseMemberOwner->getHouseAddress();
        $houseArea        = $houseMemberOwner->getHouseArea();
        $houseMemberType  = $houseMemberOwner->getMemberType();
        $houseMember      = new HouseMember();

        $houseMember->setHouseArea($houseArea)->setPhone1($phone1)->setHouseNo($houseMemberOwner->getHouseNo())->setHouseAddress($houseAddress)->setName($name)->setAddBy($phone)->setMemberType($memberType)->setMpUserID($mpUserId)->setCommunityID($communityId)->setAddType($houseMemberType)->insert(true);

        return ['errno' => 0];
    }
}
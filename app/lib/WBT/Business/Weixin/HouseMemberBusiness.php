<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Community;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\WxUser;

class HouseMemberBusiness extends BaseBusiness
{

    //列表的显示
    public static function getHouseMemberList(array $condition, array &$paging = null, $ranking, array $outputColumns = null)
    {
        return HouseMember::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    // 获取认证业主数
    public static function getCommunityVerifyYezhuCount($communityID)
    {
        $wxUserCondition = new \Bluefin\Data\DbCondition('wx_user_id is not null');

        $condition = [HouseMember::COMMUNITY_ID => $communityID ,
                      HouseMember::MEMBER_TYPE => \MP\Model\Mp\HouseMemberType::OWNER,
            $wxUserCondition];

        return HouseMember::fetchCount($condition);
    }



    // 获取住户总数，住户包括业主、业主添加的租户和亲戚朋友
    public static function getCommunityZhuhuCount($communityID)
    {
        $condition = [HouseMember::COMMUNITY_ID => $communityID,];
        return HouseMember::fetchCount($condition);
    }

    // 获取认证住户总数
    public static function getCommunityVerifyZhuhuCount($communityID)
    {
        $wxUserCondition = new \Bluefin\Data\DbCondition('wx_user_id is not null');
        $condition = [HouseMember::COMMUNITY_ID => $communityID ,$wxUserCondition];
        return HouseMember::fetchCount($condition);
    }
    // 获取业主总数
    public static function getCommunityYezhuCount($communityID)
    {

        $condition = [HouseMember::COMMUNITY_ID => $communityID ,
                      HouseMember::MEMBER_TYPE => \MP\Model\Mp\HouseMemberType::OWNER];

        return HouseMember::fetchCount($condition);
    }


    // 获取认证业主数
    public static function getMpUserVerifyYezhuCount($mpUserID)
    {
        $wxUserCondition = new \Bluefin\Data\DbCondition('wx_user_id is not null');

        $condition = [HouseMember::MP_USER_ID => $mpUserID ,
                      HouseMember::MEMBER_TYPE => \MP\Model\Mp\HouseMemberType::OWNER,
                      $wxUserCondition];


        return HouseMember::fetchCount($condition);
    }



    // 获取业主总数
    public static function getMpUserYezhuCount($mpUserID)
    {

        $condition = [HouseMember::MP_USER_ID => $mpUserID ,
                      HouseMember::MEMBER_TYPE => \MP\Model\Mp\HouseMemberType::OWNER];
        return HouseMember::fetchCount($condition);
    }

    // 获取住户总数，住户包括业主、业主添加的租户和亲戚朋友
    public static function getMpUserZhuhuCount($mpUserID)
    {
        $condition = [HouseMember::MP_USER_ID => $mpUserID];
        return HouseMember::fetchCount($condition);
    }

    // 获取认证住户总数
    public static function getMpUserVerifyZhuhuCount($mpUserID)
    {
        $wxUserCondition = new \Bluefin\Data\DbCondition('wx_user_id is not null');
        $condition = [HouseMember::MP_USER_ID => $mpUserID , $wxUserCondition];
        return HouseMember::fetchCount($condition);
    }


//
    //数据的录入
    public static function insert($data)
    {
        $obj = new HouseMember();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }
    //修改
    public static function update( $id,$communityId,$data )
    {
        $obj = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $id ,HouseMember::COMMUNITY_ID => $communityId]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find TopDirectory($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $obj->apply( $data );

        try {
            $obj->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }
    //认证
    public static function check( $data )
    {
        $wxUser = new WxUser([WxUser::VIP_NO => $data['vip_no']]);
        $houseMember = new HouseMember([HouseMember::HOUSE_MEMBER_ID => $data['house_member_id']]);
        if(!$wxUser->isEmpty())
        {
          $wxUser->setCurrentCommunityID($data['community_id'])->setPhone($data['phone1'])->setNick($data['name'])->setAddress($data['house_address'])->update();
          $wxUserID = $wxUser->getWxUserID();
          $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $data['cs']]);
          $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $data['cs_group']]);
          $houseMember->setWxUserID($wxUserID)->setVerifyTime(time())->setCurrentCsID($data['cs'])->setCurrentCsGroupID($data['cs_group'])->setCsName($cs->getName())->setCsGroupName($csGroup->getGroupName())->update();
        }
        else
        {
            return ['errno' => 1, 'error' => '没有找到对应的会员号，无法认证'];

        }
        return ['errno' => 0];
    }
    //删除
    public static function delete( $houseMemberId,$communityId )
    {
        $houseMember = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $houseMemberId,HouseMember::COMMUNITY_ID => $communityId ]);
        $wxUserID = $houseMember->getWxUserID();
        if ($houseMember->isEmpty()) {
            log_debug( "Could not find Directory($houseMemberId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $houseMember->delete();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        if(!empty($wxUserID))
        {
            // 在后台删除业主后，系统会自动选择此微信账号下其他的communityID，把它改为微信账号下的current_communityid
            $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID]);
            $currentCommunityId = 0;
            if(!$house->isEmpty())
            {
                $currentCommunityId = $house->getCommunityID();
            }
            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
            $wxUser->setCurrentCommunityID($currentCommunityId)->update();
        }
        return ['errno' => 0];
    }

    //重置
    public static function reset( $houseMemberId,$communityId )
    {
        $houseMember = new HouseMember([ HouseMember::HOUSE_MEMBER_ID => $houseMemberId,HouseMember::COMMUNITY_ID => $communityId ]);
        $wxUserID = $houseMember->getWxUserID();
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        if($wxUser->isEmpty())
        {
            log_debug( "Could not find wx_user($wxUserID)" );

            return ['errno' => 1, 'error' => '找不到该用户'];
        }
        if ($houseMember->isEmpty()) {
            log_debug( "Could not find Directory($houseMemberId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $houseMember->setWxUserID('')->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        if(!empty($wxUserID))
        {
            // 在后台删除业主后，系统会自动选择此微信账号下其他的communityID，把它改为微信账号下的current_communityid
            $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID]);
            $currentCommunityId = 0;
            if(!$house->isEmpty())
            {
                $currentCommunityId = $house->getCommunityID();
            }
            try{
                $wxUser->setCurrentCommunityID($currentCommunityId)->update();
            }catch (\Exception $e){
                return ['errno' => 1, 'error' => $e->getMessage()];
            }

        }
        return ['errno' => 0];
    }
    //删除
    public static function deleteAll( $mpUserId,$communityId )
    {
        $houseMember = new HouseMember([ HouseMember::MP_USER_ID => $mpUserId,HouseMember::COMMUNITY_ID => $communityId ]);

        if ($houseMember->isEmpty()) {
            log_debug( "Could not find Directory($mpUserId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $houseMember->delete([HouseMember::COMMUNITY_ID => $communityId]);
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }


    public static function insertFromExcel($data, $mpUserID, $communityId)
    {
        if($data['errno'] != 0)
        {
            return false;
        }
        //循环遍历每个业主，进行操作
        try
        {
            foreach ($data['owner'] as $owner)
            {
                if(empty($owner))
                {
                    continue;
                }

                $nameArray = explode('/',$owner['name']);

                foreach($nameArray as $name)
                {
                    $house = new HouseMember();
                    $house->setMpUserID($mpUserID)
                        ->setCommunityID($communityId)
                        ->setHouseNo($owner['house_no'])
                        ->setHouseAddress($owner['house_address'])
                        ->setName($name)
                        ->setBirthday(($owner['birthday']))
                        ->setPhone1($owner['phone1'])
                        ->setPhone2($owner['phone2'])
                        ->setPhone3($owner['phone3'])
                        ->insert(true);
                }


            }
            return true;
        }
        catch (\Exception $e)
        {
            log_error("exception:",$e->getMessage());
            return false;
        }
    }


    public static function getSelectByOrderTimeCondition($timeVerifyStart, $timeVerifyEnd)
    {
        $yearE = substr($timeVerifyEnd,0,4);
        $monthE = substr($timeVerifyEnd,4,2);
        $dayE = substr($timeVerifyEnd,6,2);
        $yearS = substr($timeVerifyStart,0,4);
        $monthS = substr($timeVerifyStart,4,2);
        $dayS = substr($timeVerifyStart,6,2);

        $newTimeStart = $yearS . "-" . $monthS . "-" . $dayS . " " . "00:00:00";
        $newTimeEnd = $yearE . "-" . $monthE . "-" . $dayE . " " . "23:59:59";
        $exprWx = sprintf("`verify_time` >= '%s' and `verify_time` <= '%s'",$newTimeStart,$newTimeEnd);

        $con = new \Bluefin\Data\DbCondition($exprWx);
        return $con;
    }

    public static function checkSmallFlowNo($communityID,$data)
    {
        $houseMemberNo = HouseMember::fetchColumn([HouseMember::HOUSE_NO],[HouseMember::COMMUNITY_ID => $communityID]);
        $length = [];
        foreach($houseMemberNo as $value)
        {
            $length[] = strlen($value);
        }
        $count = array_count_values($length);
        $max = 0;
        $strLength = "";
        foreach($count as $key => $value)
        {
            if($value > $max)
            {
               $max = $value;
               $strLength = $key;
            }
        }

        $smallFlowNo = explode("\n",$data);
        $check = false;
        foreach($smallFlowNo as $no)
        {
            if(!empty($no))
            {
                $smallFlowNo = explode(",",$no);
                $smallFlowNoStart = $smallFlowNo[0];
                $smallFlowNoEnd = $smallFlowNo[1];
                if(strlen($smallFlowNoStart) !=$strLength or strlen($smallFlowNoEnd) !=$strLength)
                {
                    $check = true;
                    break;
                }
            }

        }
        if($check == false)
        {
            return['errno' => 0];
        }
        else
        {
            return ['errno' => 1, 'error' => "您填写的房间编号有误，房间编号应由".$strLength."位数字或字母组成"];
        }
    }

    public static function certify($wxUserID,$memberNumber,$phone,$name,$type,$address)
    {
        //客服专员信息
        $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $wxUserID]);
        $csID = $cs->getCustomerSpecialistID();
        $csGroupID = $cs->getCustomerSpecialistGroupID();
        $communityID = $cs->getCommunityID();
        $mpUserID = $cs->getMpUserID();
        $csName = $cs->getName();
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);
        $csGroupName = $csGroup->getGroupName();
        //判断住址
        $community = new Community([Community::COMMUNITY_ID => $communityID]);
        $communityName = $community->getName();
        $address = $communityName."->".$address;
        $houseMember = new HouseMember([HouseMember::HOUSE_ADDRESS => $address]);
        $houseMemberNo = $houseMember->getHouseNo();
        $houseMemberArea = $houseMember->getHouseArea();
        if($houseMember->isEmpty())
        {
            return ['errno' => 1, 'error' => "您选择的地址有误请重新选择"];
        }

        //用户信息
        $wxUser = new WxUser([WxUser::VIP_NO => $memberNumber,WxUser::MP_USER_ID => $mpUserID,WxUser::CURRENT_COMMUNITY_ID => $communityID]);
        if(!($wxUser->isEmpty()))
        {
            return ['errno' => 1, 'error' => "您输入的会员号已经被认证过，请您仔细核对"];
        }
        $wxUser = new WxUser([WxUser::VIP_NO => $memberNumber,WxUser::MP_USER_ID => $mpUserID]);

        if($wxUser->isEmpty())
        {
            return ['errno' => 1, 'error' => "您输入的会员号有误请重新选择"];
        }
        $houseMember = new HouseMember([HouseMember::NAME => $name,HouseMember::HOUSE_ADDRESS => $address]);
        $houseMemberWxUserID = $wxUser->getWxUserID();
        $houseMemberPhone1 = $houseMember->getPhone1();
        $houseMemberPhone2 = $houseMember->getPhone2();
        $houseMemberPhone3 = $houseMember->getPhone3();
        //当数据库中存在此用户，比对用户填写信息是否与数据库一样，如果一样比对住户类型；
        if(!$houseMember->isEmpty())
        {
           if($houseMember->getMemberType() == $type )
           {
               $houseMember->setWxUserID($houseMemberWxUserID)->setCsGroupName($csGroupName)->setCurrentCsGroupID($csGroupID)->setCsName($csName)->setCurrentCsID($csID)->setVerifyTime(time())->update();
               $wxUser->setPhone($phone)->setNick($name)->setCurrentCommunityID($communityID)->setBirth($houseMember->getBirthday())->update();
             if(empty($houseMemberPhone1) and empty($houseMemberPhone2) and empty($houseMemberPhone3) )
             {
                 $houseMember->setPhone1($phone)->update();
             }
           }
           else
           {
               return ['errno' => 1, 'error' => "您选择的业主类型与系统信息不符，请重新选择"];
           }
        }
        //当数据库中不存在用户数据
        else
        {
           if($type == HouseMemberType::OWNER)
           {
               return ['errno' => 1, 'error' => "您可以选择除业主类型其他类型"];
           }
           else
           {
               $houseMember = new HouseMember();
               $houseMember->setWxUserID($houseMemberWxUserID)->setCsGroupName($csGroupName)->setCurrentCsGroupID($csGroupID)->setCsName($csName)->setCurrentCsID($csID)->setVerifyTime(time())->setPhone1($phone)->setName($name)->setMemberType($type)->setHouseAddress($address)->setMpUserID($mpUserID)->setHouseNo($houseMemberNo)->setCommunityID($communityID)->setHouseArea($houseMemberArea)->insert();
               $wxUser->setPhone($phone)->setNick($name)->setCurrentCommunityID($communityID)->update();
           }
        }
        return ['errno' => 0 ];
    }
}
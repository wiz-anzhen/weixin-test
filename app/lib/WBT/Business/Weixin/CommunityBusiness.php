<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Community;
use MP\Model\Mp\CommunityReport;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\Directory;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\MpUser;


class CommunityBusiness extends BaseBusiness
{
    public static function getCommunityList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return Community::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function communityInsert( $data )
    {
        $obj = new Community();
        $obj->apply( $data );
        $obj->insert();

        $mpUserID = $obj->getMpUserID();
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $industry = $mpUser->getIndustry();
        if($industry == IndustryType::PROCUREMENT)
        {
            if($data[Community::COMMUNITY_TYPE] != CommunityType::PROCUREMENT_SUPPLY)
            {
                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '223',Directory::COMMUNITY_ID => $obj->getCommunityID() ,Directory::MP_USER_ID => $obj->getMpUserID()]);
            }

            if($data[Community::COMMUNITY_TYPE] == CommunityType::PROCUREMENT_SUPPLY)
            {
                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '224',Directory::COMMUNITY_ID => $obj->getCommunityID() ,Directory::MP_USER_ID => $obj->getMpUserID()]);
            }
        }


        return [ 'errno' => 0 ];
    }

    public static function communityUpdate( $id, $data )
    {
        $obj = new Community([ Community::COMMUNITY_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->apply( $data );

        try {
            $obj->update();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function communityDelete( $id )
    {
        $obj = new Community([ Community::COMMUNITY_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

//set  count




    public static function setCounts($communityID,$mpUserID,$ymd)

    {
        $zhuhuVerify = HouseMemberBusiness::getCommunityVerifyZhuhuCount($communityID);
        $yezhuVerify = HouseMemberBusiness::getCommunityVerifyYezhuCount($communityID);
        $zhuhuCount = HouseMemberBusiness::getCommunityZhuhuCount($communityID);
        $yezhuCount = HouseMemberBusiness::getCommunityYezhuCount($communityID);
        CommunityBusiness::setCommunityReportCount($mpUserID,$communityID,$ymd, $zhuhuCount,$yezhuCount,$zhuhuVerify,$yezhuVerify);


        return true;
    }

    public static function setCommunityReportCount($mpUserID,$communityID,$ymd, $zhuhuCount,$yezhuCount,$zhuhuVerify,$yezhuVerify)
    {
        $communityReport = new CommunityReport([CommunityReport::MP_USER_ID => $mpUserID,CommunityReport::COMMUNITY_ID => $communityID, CommunityReport::YMD => $ymd]);
        $data = [
            CommunityReport::MP_USER_ID => $mpUserID,
            CommunityReport::COMMUNITY_ID => $communityID,
            CommunityReport::YMD => $ymd,
            CommunityReport::ZHUHU_COUNT => $zhuhuCount,
            CommunityReport::YEZHU_COUNT => $yezhuCount,
            CommunityReport::ZHUHU_VERIFY => $zhuhuVerify,
            CommunityReport::YEZHU_VERIFY => $yezhuVerify,
        ];
        if($communityReport->isEmpty())
        {
            $communityReport = new CommunityReport();
            $communityReport->apply( $data );
            try {
                $communityReport->insert();
            } catch (\Exception $e) {
                return ['errno' => 1, 'error' => $e->getMessage()];
            }
        }
        else
        {
            $communityReport->apply( $data );
            try {
                $communityReport->update();
            } catch (\Exception $e) {
                return ['errno' => 1, 'error' => $e->getMessage()];
            }
        }

        return true;
    }

    public static function getCommunityName($communityID)
    {
        $c = new Community([Community::COMMUNITY_ID => $communityID]);
        if($c->isEmpty())
        {
            return '';
        }
        return $c->getName();
    }

    public static function  getCommunityAdminEmail(Community $community, &$mailAddr,  &$ccMailAddr)
    {
        $mailAddr = [];
        $ccMailAddr = [];

        if($community->isEmpty())
        {
            log_error("empty community");
            return;
        }

        $addr = $community->getAdminEmail();
        $addr = str_replace('，',',',$addr);

        $addrArr = explode(',', $addr);
        foreach($addrArr as $a)
        {
            $a = trim($a);
            if(!empty($a))
            {
                $mailAddr[] = $a;
            }
        }


        $addr = $community->getAdminCcEmail();
        $addr = str_replace('，',',',$addr);
        $addrArr = explode(',', $addr);
        foreach($addrArr as $a)
        {
            $a = trim($a);
            if(!empty($a))
            {
                $ccMailAddr[] = $a;
            }
        }

    }
    public static  function updateCommunityValid($mpUserID,$data)
    {
        //$obj = new Community([ Community::MP_USER_ID => $mpUserID ]);
        $communityIdArray = Community::fetchColumn(Community::COMMUNITY_ID,[Community::MP_USER_ID=>$mpUserID]);

        if (empty($communityIdArray)) {
            log_debug( "Could not find community($mpUserID)" );

            return false;
        }

        foreach($communityIdArray as $communityId)
        {
            $community = new Community([Community::COMMUNITY_ID=>$communityId]);
            $community->setValid($data);
            try {
                $community->update();
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }
}
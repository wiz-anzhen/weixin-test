<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Directory;
use MP\Model\Mp\DirectoryCommonType;
use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\DirectoryDailyTraffic;
use MP\Model\Mp\DirectoryWxUserVisit;
use WBT\Business\ConfigBusiness;


class DirectoryBusiness extends BaseBusiness
{
    public static function getListTop( array $condition, array &$paging = null, $ranking, array $outputColumns = null )
    {
        return TopDirectory::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function insertTop( $data )
    {
        $obj = new TopDirectory();
        $obj->apply( $data );

        if($obj->insertInRestraintOfUniqueKey())
        {
            return [ 'errno' => 0 ];
        }
       else
       {
            return [ 'errno' => 1, 'error' => "请选择其他一级目录编号，本小区已有相同的目录编号" ];
        }

    }

    public static function copyTop( $data )
    {
        log_debug("===================================",$data);
        $topDirectory = new TopDirectory([TopDirectory::TOP_DIRECTORY_ID => $data[TopDirectory::TOP_DIRECTORY_ID]]);
        if ($topDirectory->isEmpty()) {
            return [ 'errno' => 1, 'error' => '找不到该ID对应的目录，请核对id' ];
        }
        //复制商城
        $newTopDirectory = new TopDirectory();
        if($newTopDirectory->setMpUserID($data[TopDirectory::MP_USER_ID])->setCommunityID($data[TopDirectory::COMMUNITY_ID])->setTitle($topDirectory->getTitle())->setDirectoryBackgroundImg($topDirectory->getDirectoryBackgroundImg())->setDirectoryTopImg($topDirectory->getDirectoryTopImg())->setDirectoryTopImgSecond($topDirectory->getDirectoryTopImgSecond())->setDirectoryTopImgThird($topDirectory->getDirectoryTopImgThird())->setPowerType($topDirectory->getPowerType())->setTopDirNo($topDirectory->getTopDirNo())->setUrlType($topDirectory->getUrlType())->insertInRestraintOfUniqueKey())
        {

            return [ 'errno' => 0 ];
        }
        else
        {
            return [ 'errno' => 1, 'error' => "请选择其他一级目录编号，本小区已有相同的目录编号" ];
        }

    }

    public static function updateTop( $id,$communityId,$data )
    {
        $obj = new TopDirectory([ TopDirectory::TOP_DIRECTORY_ID => $id ,TopDirectory::COMMUNITY_ID => $communityId]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find TopDirectory($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $obj->apply( $data );

        if($obj->updateInRestraintOfUniqueKey())
        {
            return [ 'errno' => 0 ];
        }
        else
        {
            return [ 'errno' => 1, 'error' => "请选择其他一级目录编号，本小区已有相同的目录编号" ];
        }

    }

    public static function deleteTop( $id,$communityId )
    {
        $obj = new TopDirectory([ TopDirectory::TOP_DIRECTORY_ID => $id ,TopDirectory::COMMUNITY_ID => $communityId]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find TopDirectory($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public static function getList( array $condition, array &$paging = null, $ranking, array $outputColumns = null )
    {
        return Directory::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getListTraffic( array $condition, array &$paging = null, $ranking, array $outputColumns = null )
    {
        return DirectoryDailyTraffic::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function insert( $data )
    {
        $directory = new Directory();
        $directory->apply( $data );
        try {
            $directory->insert();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0];
    }


    public static function update( $directoryId,$communityId,$data )
    {
        $directory = new Directory([ Directory::DIRECTORY_ID => $directoryId,Directory::COMMUNITY_ID => $communityId ]);

        if ($directory->isEmpty()) {
            log_debug( "Could not find Directory($directoryId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $directory->apply( $data );

        try {
            $directory->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }

    public static function smallFlowSet( $directoryId,$communityId,$data )
    {
        $directory = new Directory([ Directory::DIRECTORY_ID => $directoryId,Directory::COMMUNITY_ID => $communityId ]);

        if ($directory->isEmpty()) {
            log_debug( "Could not find Directory($directoryId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        $directory->setSmallFlowNo($data)->update();
        return ['errno' => 0];
    }

    public static function delete( $directoryId,$communityId )
    {
        $directory = new Directory([ Directory::DIRECTORY_ID => $directoryId,Directory::COMMUNITY_ID => $communityId ]);

        if ($directory->isEmpty()) {
            log_debug( "Could not find Directory($directoryId)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }
        try {
            $directory->delete();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }
    // 更新目录统计数据
    public static function trafficUpdate( $wxUserId,$mpUserId,$directoryId,$communityId )
    {
        $currentDate = get_current_ymd();
        $directoryDailyTraffic = new DirectoryDailyTraffic([DirectoryDailyTraffic::DIRECTORY_ID => $directoryId,DirectoryDailyTraffic::YMD => $currentDate]);
        //更新二级目录访问量统计
        if($directoryDailyTraffic->isEmpty())
        {
            $directoryDailyTraffic->setDirectoryID($directoryId)->setMpUserID($mpUserId)->setCommunityID($communityId)->setYmd($currentDate)->setPv(1)->setUv(1)->insert();
        }
        else
        {
            $pv = $directoryDailyTraffic->getPv();
            $pv = $pv+1;
            $directoryWxUserVisit = new DirectoryWxUserVisit([DirectoryWxUserVisit::WX_USER_ID => $wxUserId,DirectoryWxUserVisit::DIRECTORY_ID => $directoryId,DirectoryWxUserVisit::LAST_ACCESS_YMD => $currentDate]);
            $uv = $directoryDailyTraffic->getUv();
            if($directoryWxUserVisit->isEmpty())
            {
                $uv = $uv+1;
                $directoryDailyTraffic->setUv($uv)->update();
            }
            $directoryDailyTraffic->setPv($pv)->update();
        }
        //更新二级目录用户访问表
        $directoryWxUserVisit = new DirectoryWxUserVisit([DirectoryWxUserVisit::WX_USER_ID => $wxUserId,DirectoryWxUserVisit::DIRECTORY_ID => $directoryId]);
        if($directoryWxUserVisit->isEmpty())
        {
            $directoryWxUserVisit = new DirectoryWxUserVisit();
            $directoryWxUserVisit->setDirectoryID($directoryId)->setWxUserID($wxUserId)->setLastAccessYmd($currentDate)->insert();
        }
        else
        {
            $lastAccessYmd = $directoryWxUserVisit->getLastAccessYmd();
            if($lastAccessYmd != $currentDate)
            {
                $directoryWxUserVisit->setLastAccessYmd($currentDate)->update();
            }
        }
    }

    public static function getContent($type,$contentValue)
    {
        $content = "";
        $host = ConfigBusiness::getHost();
        switch($type)
        {
            case DirectoryCommonType::TEXT:
                $content = $contentValue;
                break;
            case DirectoryCommonType::LINK:
                $content = $contentValue;
                break;
            case DirectoryCommonType::USER_BILL_LIST:
                $content = $host."/wx_user/user_info/bill_list";
                break;
            case DirectoryCommonType::USER_SETTING:
                $content = $host."/wx_user/profile/spm_setting";
                break;
            case DirectoryCommonType::USER_ORDER:
                $content = $host."/wx_user/order/person";
                break;
            case DirectoryCommonType::USER_VIP_CARD:
                $content = $host."/wx_user/profile/index";
                break;
            case DirectoryCommonType::USER_CS_CERTIFY:
                $content = $host."/wx_user/house_member/certify";
                break;
            default :
                break;
        }
        return $content;
    }

    public static function getDirectoryTraffic($directoryID)
    {
        $directoryDailyTraffic = DirectoryDailyTraffic::fetchRows(['*'],[DirectoryDailyTraffic::DIRECTORY_ID => $directoryID]);
        $totalUv = [];
        $totalPv = [];
        foreach($directoryDailyTraffic as $value)
        {
            $totalUv[] = $value[DirectoryDailyTraffic::UV];
            $totalPv[] = $value[DirectoryDailyTraffic::PV];
        }
        $totalUv = array_sum($totalUv);
        if(!isset($totalUv))
        {
            $totalUv = 0;
        }
        $totalPv = array_sum($totalPv);
        if(!isset($totalPv))
        {
            $totalPv = 0;
        }
        $currentDate = get_current_ymd();
        $directoryDailyTraffic = new DirectoryDailyTraffic([DirectoryDailyTraffic::DIRECTORY_ID => $directoryID,DirectoryDailyTraffic::YMD => $currentDate]);
        $currentUv = $directoryDailyTraffic->getUv();
        if(!isset($currentUv))
        {
            $currentUv = 0;
        }
        $currentPv = $directoryDailyTraffic->getPv();
        if(!isset($currentPv))
        {
            $currentPv = 0;
        }
        return $directoryDailyTraffic = [
            "total_uv" => $totalUv,
            "total_pv" => $totalPv,
            "current_uv" => $currentUv,
            "current_pv" => $currentPv
        ];
    }

    public static function checkLength($smallFlowNoStart,$smallFlowNoEnd,$hNo)
    {
        $arrayLength = [strlen($smallFlowNoStart),strlen($smallFlowNoEnd),strlen($hNo)];
        $maxLength = max($arrayLength);
        $array = [$smallFlowNoStart,$smallFlowNoEnd,$hNo];
        $ret = [];
        foreach($array as $value)
        {
            $dif = $maxLength-strlen($value);
            if($dif > 0)
            {
                for($i=1;$i<=$dif;$i++)
                {
                  $value = "0".$value;
                }
            }
            $ret[] = $value;
        }
        return $ret;

    }
}
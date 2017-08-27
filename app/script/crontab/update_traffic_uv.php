<?php

require_once '../../../lib/Bluefin/bluefin.php';

use Bluefin\App;


use MP\Model\Mp\Directory;
use MP\Model\Mp\DirectoryDailyTraffic;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpArticleDailyTraffic;



update_traffic_uv();


function update_traffic_uv()
{
    $currentDate = date('Ymd');
    $mpUserID =  MpUser::fetchColumn([MpUser::MP_USER_ID],[MpUser::VALID => 1]);
    $directoryIds = Directory::fetchColumn([Directory::DIRECTORY_ID],[Directory::MP_USER_ID => $mpUserID]);
    foreach($directoryIds as $directoryId)
    {
        $directoryDailyTraffic = new DirectoryDailyTraffic([DirectoryDailyTraffic::DIRECTORY_ID => $directoryId,DirectoryDailyTraffic::YMD => $currentDate]);
        if($directoryDailyTraffic->isEmpty())
        {
            $directory = new Directory([Directory::DIRECTORY_ID => $directoryId]);
            $directoryDailyTraffic->setDirectoryID($directoryId)->setCommunityID($directory->getCommunityID())->setMpUserID($directory->getMpUserID())->setPv(0)->setUv(0)->setYmd($currentDate)->insert();
        }
    }
    $mpArticleIds = MpArticle::fetchColumn([MpArticle::MP_ARTICLE_ID],[MpArticle::MP_USER_ID => $mpUserID]);
    foreach($mpArticleIds as $mpArticleId)
    {
        $mpArticleDailyTraffic = new MpArticleDailyTraffic([MpArticleDailyTraffic::MP_ARTICLE_ID => $mpArticleId,MpArticleDailyTraffic::YMD => $currentDate]);
        if($mpArticleDailyTraffic->isEmpty())
        {
            $mpArticle = new MpArticle([MpArticle::MP_ARTICLE_ID => $mpArticleId]);
            $mpArticleDailyTraffic->setMpArticleID($mpArticleId)->setCommunityID($mpArticle->getCommunityID())->setMpUserID($mpArticle->getMpUserID())->setPv(0)->setYmd($currentDate)->insert();
        }
    }
}





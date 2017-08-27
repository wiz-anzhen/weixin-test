<?php

namespace WBT\Business\Weixin;

use WBT\Business\MailBusiness;
use MP\Model\Mp\Report;
use MP\Model\Mp\MpFansCount;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;

class ReportBusiness extends BaseBusiness
{

    public static function sendMailToMultiRecipients( $recipients, $subject, $htmlContent )
    {
        if (!is_array( $recipients )) $recipients = [ $recipients ];

        $failedCount = 0;
        foreach ($recipients as $recipient)
        {
            if (!MailBusiness::sendMail( $recipient, $subject, $htmlContent ))
            {
                log_debug($recipient . "\n" . $subject . "\n" . $htmlContent);
                $failedCount++;
            }
        }

        return $failedCount;
    }

    public static function getCurrentFansTotalCount($mpUserID)
    {
        $mpFansCount = new MpFansCount([MpFansCount::MP_USER_ID => $mpUserID]);
        if($mpFansCount->isEmpty())
        {
            return 0;
        }
        else
        {
            return $mpFansCount->getFansCount();
        }
    }

    public static function initReport($mpUserID)
    {
        $ymd = self::getCurrentYearMonthDay();
        $report = new Report([Report::MP_USER_ID => $mpUserID, Report::YMD => $ymd]);
        if($report->isEmpty())
        {
            $fansTotalCount = self::getCurrentFansTotalCount($mpUserID);
            $report->setMpUserID($mpUserID)
                ->setYmd($ymd)
                ->setFansTotalCount($fansTotalCount)
                ->setFollowedCount(0)
                ->setNetIncreaseFansCount(0)
                ->setUnfollowedCount(0)
                ->setPv(0)
                ->setUv(0)
                ->insertOnDuplicateUpdate();
        }

        return $report;
    }

    /*
     * fans_total_count: int|+10|comment='粉丝数'|=0
    net_increase_fans_count: int|+10|comment='净增粉丝数'|=0
    followed_count: int|+10|comment='被关注的次数'|=0
    unfollowed_count: int|+10|comment='被取消关注的次数'|=0
    uv: int|+10|comment='活跃用户数'|=0
    pv: int|+10|comment='访问量'|=0
     * */

    public static function setReportCount($mpUserID,$ymd, $zhuhuCount,$yezhuCount,$zhuhuVerify,$yezhuVerify)
    {
        $report = new Report([Report::MP_USER_ID => $mpUserID, Report::YMD => $ymd]);
        $data = [Report::MP_USER_ID => $mpUserID,
                Report::YMD => $ymd,Report::FANS_TOTAL_COUNT => $report->getFansTotalCount(),
            Report::NET_INCREASE_FANS_COUNT => $report->getNetIncreaseFansCount(),
            Report::FOLLOWED_COUNT => $report->getFollowedCount(),
            Report::UV => $report->getUv(),
            Report::PV => $report->getPv(),
            Report::ZHUHU_COUNT => $zhuhuCount,
            Report::YEZHU_COUNT => $yezhuCount,
            Report::ZHUHU_VERIFY => $zhuhuVerify,
            Report::YEZHU_VERIFY => $yezhuVerify,
        ];
        $report->apply( $data );
        try {
            $report->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return true;
    }


    public static function processSubscribeEvent(MpUser &$mpUser, WxUser &$wxUser)
    {
        $mpUserID = $mpUser->getMpUserID();
        $ymd = self::getCurrentYearMonthDay();

        $mpFansCount = new MpFansCount([MpFansCount::MP_USER_ID => $mpUserID]);
        $totalFansCount = 0;
        $newCount = 0;
        if($mpFansCount->isEmpty())
        {
            $totalFansCount = 1;
            $mpFansCount->setMpUserID($mpUserID)
                ->setFansCount($totalFansCount)
                ->insert();
        }
        else
        {
            $totalFansCount = $mpFansCount->getFansCount() + 1;
            $mpFansCount->setFansCount($totalFansCount)
                ->save();
        }


        $report = new Report([Report::MP_USER_ID => $mpUserID, Report::YMD => $ymd]);
        if($report->isEmpty())
        {
            $report->setMpUserID($mpUserID)
                ->setYmd($ymd)
                ->setFansTotalCount($totalFansCount)
                ->setFollowedCount(1)
                ->setNetIncreaseFansCount(1)
                ->setUnfollowedCount(0)
                ->insert();
        }
        else
        {
            $report->setFansTotalCount($totalFansCount)
                ->setNetIncreaseFansCount($report->getNetIncreaseFansCount() + 1)
                ->setFollowedCount($report->getFollowedCount() + 1)
                ->save();
        }
    }

    public static function processUnsubscribeEvent(MpUser &$mpUser, WxUser &$wxUser)
    {
        $mpUserID = $mpUser->getMpUserID();
        $ymd = self::getCurrentYearMonthDay();

        $mpFansCount = new MpFansCount([MpFansCount::MP_USER_ID => $mpUserID]);
        $totalFansCount = 0;
        $newCount = 0;
        if($mpFansCount->isEmpty())
        {
            $totalFansCount = 0;
            $mpFansCount->setMpUserID($mpUserID)
                ->setFansCount($totalFansCount)
                ->insert();
        }
        else
        {
            $totalFansCount = $mpFansCount->getFansCount() - 1;
            $mpFansCount->setFansCount($totalFansCount)
                ->save();
        }


        $report = new Report([Report::MP_USER_ID => $mpUserID, Report::YMD => $ymd]);
        if($report->isEmpty())
        {
            $report->setMpUserID($mpUserID)
                ->setYmd($ymd)
                ->setFansTotalCount($totalFansCount)
                ->setFollowedCount(0)
                ->setNetIncreaseFansCount(-1)
                ->setUnfollowedCount(1)
                ->insert();
        }
        else
        {
            $report->setFansTotalCount($totalFansCount)
                ->setNetIncreaseFansCount($report->getNetIncreaseFansCount() - 1)
                ->setUnfollowedCount($report->getUnfollowedCount() + 1)
                ->save();
        }
    }




    // uv加1
    public static function uvPlusOne($mpUserID)
    {
        $report = self::initReport($mpUserID);
        $report->setUv($report->getUv() + 1 )->save();
    }

    // pv加1
    public static function pvPlusOne($mpUserID)
    {
        $report = self::initReport($mpUserID);
        $report->setPv($report->getPv() + 1 )->save();
    }


    //查询report list
    public static function getReportList($mpUserID, $ranking, &$paging, $outputColumns)
    {
        $condition = [Report::MP_USER_ID => $mpUserID];
        $selection = ['*'];
        $grouping = null;

        $report = Report::fetchRowsWithCount(
            $selection,
            $condition,
            $grouping,
            $ranking,
            $paging,
            $outputColumns
        );
        return $report;
    }


}
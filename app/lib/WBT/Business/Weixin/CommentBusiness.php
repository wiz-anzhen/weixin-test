<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\WxUserFeedback;

class CommentBusiness extends BaseBusiness
{
    //意见反馈
    public static function submitFeedback( MpUser &$mpUser, WxUser &$wxUser,$feedback)
    {
        $res = ['errno' => 0];
        $wxUserFeedback = new WxUserFeedback();
        $wxUserFeedback->setWxUserID($wxUser->getWxUserID())
                       ->setMpUserID($mpUser->getMpUserID())
                       ->setContent($feedback)
                       ->insert();

        // 更新报表中的意见反馈数
        //ReportBusiness::sendCommentToAdmin($mpUser, $wxUser, $feedback);

        return $res;
    }


    public static function getFeedbackList($mpUserID, $ranking, &$paging, $outputColumns)
    {
        $condition = [WxUserFeedback::MP_USER_ID => $mpUserID];
        $selection = ['*'];
        $grouping = null;

        $wxUserFeedback = WxUserFeedback::fetchRowsWithCount(
            $selection,
            $condition,
            $grouping,
            $ranking,
            $paging,
            $outputColumns
        );
        return $wxUserFeedback;
    }
}
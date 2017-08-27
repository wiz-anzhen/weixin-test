<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\ArticleComment;
use MP\Model\Mp\LikeArticle;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpArticleDailyTraffic;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;

use WBT\Business\MailBusiness;
use WBT\Business\UserBusiness;


class MpArticleBusiness
{
    public static function getDetail($articleID)
    {
        $mpArticle = new MpArticle([MpArticle::MP_ARTICLE_ID => $articleID]);

        return [MpArticle::TITLE         => $mpArticle->getTitle(), MpArticle::MP_USER_ID    => $mpArticle->getMpUserID(), MpArticle::CONTENT       => $mpArticle->getContent(), MpArticle::SHARE_DESC    => $mpArticle->getShareDesc(), MpArticle::SHOW_LIKE     => $mpArticle->getShowLike(), MpArticle::MP_ARTICLE_ID => $mpArticle->getMpArticleID(),];
    }

    public static function getList(array $condition, array &$paging = NULL, $ranking, array $outputColumns = NULL)
    {

        return MpArticle::fetchRowsWithCount(['*'], $condition, NULL, $ranking, $paging, $outputColumns);
    }

    //编辑
    public static function update($mpArticleId, array $data)
    {
        $mpArticle = new MpArticle([MpArticle::MP_ARTICLE_ID => $mpArticleId]);
        $lastModifyTime = date('Y-m-d H:i:s',time());
        $lastModifyAuthor = UserBusiness::getLoginUsername();
        $data[MpArticle::LAST_MODIFY_TIME] = $lastModifyTime;
        $data[MpArticle::LAST_MODIFY_AUTHOR] = $lastModifyAuthor;
        if ($mpArticle->isEmpty())
        {
            log_warn("Could not find MpArticle($mpArticleId)");
            return FALSE;
        }

        $mpArticle->apply($data)->update();

        return TRUE;
    }

    public static function remove($mpUserId, $mpArticleId)
    {
        $mpArticle = new MpArticle([MpArticle::MP_USER_ID => $mpUserId, MpArticle::MP_ARTICLE_ID => $mpArticleId]);
        if ($mpArticle->isEmpty())
        {
            log_warn("Could not find MpArticle($mpArticleId)");

            return FALSE;
        }
        $mpArticle->delete();

        return TRUE;
    }

    public static function insert($communityId, $mpUserId)
    {
        do
        {
            $newMpArticleId = self::genMpArticleId();
            $mpArticle      = new MpArticle([MpArticle::MP_ARTICLE_ID => $newMpArticleId]);
            if (!$mpArticle->isEmpty())
            {
                continue;
            }
            $lastModifyTime = date('Y-m-d H:i:s',time());//最后修改时间
            $lastModifyAuthor = UserBusiness::getLoginUsername();//最后修改人
            $mpArticle->setMpArticleID($newMpArticleId)->setMpUserID($mpUserId)->setCommunityID($communityId)->setTitle('')->setContent('')->setShowLike(0)->setLikeCount(0)->setShareDesc('')->setLastModifyAuthor($lastModifyAuthor)->setLastModifyTime($lastModifyTime)->insert();

            return $newMpArticleId;
        }
        while (1);
    }

    private static function genMpArticleId()
    {
        $str = '0123456789';
        $r1  = rand(0, 9);
        $r2  = rand(0, 9);
        $r3  = rand(0, 9);
        $r4  = rand(0, 9);
        $r5  = rand(0, 9);
        $r6  = rand(0, 9);
        $r7  = rand(0, 9);

        return md5($str[$r1] . $str[$r2] . $str[$r3] . $str[$r4] . $str[$r5] . $str[$r6] . $str[$r7]);
    }

    public static function likeArticle($wxUserId, $mpArticleId, $mpUserId, $communityID)
    {
        $condition = [LikeArticle::MP_USER_ID    => $mpUserId, LikeArticle::WX_USER_ID    => $wxUserId, LikeArticle::MP_ARTICLE_ID => $mpArticleId];
        if (LikeArticle::fetchCount($condition) > 0)
        {
            return ['errno' => 1, 'error' => '您已喜欢过该文章'];
        }

        $likeArticle = new LikeArticle();
        $likeArticle->setMpUserID($mpUserId)->setWxUserID($wxUserId)->setMpArticleID($mpArticleId)->insert();
        $mpArticle = new MpArticle([MpArticle::MP_ARTICLE_ID => $mpArticleId]);
        if ($mpArticle->isEmpty())
        {
            return ['errno' => 1, 'error' => '文章不存在'];
        }
        $mpArticle->setLikeCount($mpArticle->getLikeCount() + 1)->update();
        return ['errno' => 0];
    }

    public static function likeCount($mpArticleId)
    {
        return LikeArticle::fetchCount([LikeArticle::MP_ARTICLE_ID => $mpArticleId]);
    }

    public static function hadLiked($wxUserId, $mpArticleId, $mpUserId)
    {
        $condition = [LikeArticle::MP_USER_ID    => $mpUserId, LikeArticle::WX_USER_ID    => $wxUserId, LikeArticle::MP_ARTICLE_ID => $mpArticleId];
        if (LikeArticle::fetchCount($condition) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function commentArticle($wxUserId, $mpArticleId, $mpUserId, $comment, $communityID)
    {
        $articleComment = new ArticleComment();

        $recipients   = [];
        $recipientsCc = [];
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        $communityID = $wxUser->getCurrentCommunityID();
        $community = new Community([Community::COMMUNITY_ID => $communityID]);

        \WBT\Business\Weixin\CommunityBusiness::getCommunityAdminEmail($community, $recipients, $recipientsCc);

        $communityName = $community->getName();
        $mpUser        = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $mpName        = $mpUser->getMpName();

        // 邮件通知小区管理员
        $articleTitle = '未知文章标题';
        $mpArticle    = new MpArticle([MpArticle::MP_ARTICLE_ID => $mpArticleId]);
        if (!$mpArticle->isEmpty())
        {
            $articleTitle = $mpArticle->getTitle();
        }

        $wxUser  = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        $name    = $wxUser->getNick();
        $tel     = $wxUser->getPhone();
        $title   = "[用户反馈][$mpName][$communityName]$articleTitle";
        $content = "您好！<br/>";
        $content .= "本信息来自" . $mpName . "微信帐号<br/><br/>";
        $content .= "您有一个新的意见反馈，详情如下：<br/>";
        $content .= "文章标题：" . $articleTitle . "<br/>";
        $content .= "用户：" . $name . "<br/>";
        $content .= "联系方式：" . $tel . "<br/>";
        $content .= "意见反馈时间：" . date('Y/m/d H:i') . "<br/>";
        $content .= "反馈的意见：" . $comment . "<br/><br/>";
        $content .= "特此通知！<br/><br/>";
        $content .= "$mpName<br/>";
        $content .= "此信为系统自动邮件，请不要直接回复。";

        MailBusiness::sendMailAsyn($recipients, $recipientsCc, $title, $content);

        $articleComment->setWxUserID($wxUserId)
            ->setMpUserID($mpUserId)
            ->setMpArticleID($mpArticleId)
            ->setComment($comment)
            ->setMailRecipients(implode(',', $recipients))
            ->setMailContent($content)->insert();
        $name = ['name' => $name];
        $res = ['errno' => 0];
        $head = ['head' => $wxUser->getHeadPic()];
        $currentTime = date('Y-m-d H:i:s',time());
        $time = CsChatBusiness::getCommentTime($currentTime);
        $time = ['time' => $time];
        $comment = ['comment' => $comment];
        $ret = array_merge($res,$time,$name,$head,$comment);
        return $ret;
    }
    // 更新目录统计数据
    public static function trafficUpdate($mpArticleId,$mpUserId,$communityID )
    {
        $currentDate = get_current_ymd();
        $mpArticleDailyTraffic = new MpArticleDailyTraffic([MpArticleDailyTraffic::MP_ARTICLE_ID => $mpArticleId,MpArticleDailyTraffic::YMD => $currentDate]);
        //更新素材管理访问量统计
        if($mpArticleDailyTraffic->isEmpty())
        {
            $mpArticleDailyTraffic->setMpArticleID($mpArticleId)->setMpUserID($mpUserId)->setCommunityID($communityID)->setYmd($currentDate)->setPv(1)->insert();
        }
        else
        {
            $pv = $mpArticleDailyTraffic->getPv();
            $pv = $pv+1;
            $mpArticleDailyTraffic->setPv($pv)->update();
        }
    }

    public static function getListTraffic( array $condition, array &$paging = null, $ranking, array $outputColumns = null )
    {
        return MpArticleDailyTraffic::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
}

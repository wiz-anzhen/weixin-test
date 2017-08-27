<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\ArticleType;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\MpArticle;

class ChannelBusiness extends BaseBusiness
{
    public static function getChannelList( array $condition, array &$paging = null, $ranking,
                                           array $outputColumns = null )
    {
        return Channel::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function insert( $communityId,$mpUserId, $title )
    {
        $channel = new Channel();

        return $channel->setMpUserID( $mpUserId )
            ->setTitle( $title )->setCommunityID($communityId)
            ->insert();
    }

    public static function update( $communityId,$channelId, $title )
    {
        $channel = new Channel([Channel::CHANNEL_ID => $channelId,Channel::COMMUNITY_ID => $communityId]);
        if (!$channel->isEmpty())
        {
            $channel->setTitle($title)->update();
            return TRUE;
        }
        return false;
    }

    public static function remove($channelId,$communityId)
    {
        $channel = new Channel([Channel::CHANNEL_ID => $channelId,Channel::COMMUNITY_ID => $communityId]);
        if (!$channel->isEmpty())
        {
            $channel->delete();
            return true;
        }
        return false;
    }

    public static function getChannelArticleList( $condition, $ranking, $paging, $outputColumns )
    {
        $rets = ChannelArticle::fetchRows( [ '*' ], $condition,null, $ranking, $paging, $outputColumns );
        return $rets;
    }

    public static function getChannelArticleListForFrontEnd( $channelId, $view, $ranking, $paging, $outputColumns )
    {
        $rows = ChannelArticle::fetchRows( [ '*' ], [ ChannelArticle::CHANNEL_ID => $channelId ],
            null, $ranking, $paging, $outputColumns );


        foreach($rows as $key => $ret)
        {
            $releaseDate = strtotime($ret[ChannelArticle::RELEASE_DATE]);
            if($ret[ChannelArticle::ARTICLE_TYPE] == ArticleType::ARTICLE_OURS)
            {
                $rows[$key][ChannelArticle::ARTICLE_URL] = sprintf("%s/wx_user/channel/ours?channel_article_id={$ret['channel_article_id']}",get_host());

            }
            else
            {
                $rows[$key][ChannelArticle::ARTICLE_URL] = $ret[ChannelArticle::ARTICLE_URL];
            }
            if ($ret[ChannelArticle::KEEP_TOP] == 0)
            {
                $rows[$key]['mmdd'] = date('md', $releaseDate);
            }
            else
            {
                $rows[$key]['mmdd'] = 'keep_top';
            }
            if ($view == 2)
            {
                $rows[$key]['day'] = date('j', $releaseDate);
            }
            else
            {
                $rows[$key]['day'] = date('d', $releaseDate);
            }

            $cnMonth = [ '', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二' ];
            if ($view == 2)
            {
                $rows[$key]['month_cn'] = date('n', $releaseDate);
            }
            else
            {
                $rows[$key]['month_cn'] = $cnMonth[date('n', $releaseDate)];
            }
        }

        return $rows;
    }

    public static function updateChannelArticle($communityId,$channelArticleId, $articleDesc, $releaseDate, $shareUrl, $articleTitle,$articleType, $articleDetail, $articleUrl, $keepTop )
    {
        $keepTop = $keepTop == 1 ? 1 : 0;

        $channelArticle = new ChannelArticle([ChannelArticle::CHANNEL_ARTICLE_ID => $channelArticleId,Channel::COMMUNITY_ID => $communityId]);
        if (!$channelArticle->isEmpty())
        {
            try
            {
                $channelArticle->setArticleType(ArticleType::ARTICLE_THIRD_PARTY)
                    ->setArticleDesc($articleDesc)
                    ->setShareUrl($shareUrl)
                    ->setArticleTitle($articleTitle)
                    ->setArticleType($articleType)
                    ->setArticleDetail($articleDetail)
                    ->setArticleUrl($articleUrl)
                    ->setReleaseDate($releaseDate)
                    ->setKeepTop($keepTop)->setCommunityID($communityId)
                    ->update();
            }
            catch(\Exception $e)
            {
                return false;
            }
            return TRUE;
        }
        return false;
    }

    public static function removeChannelArticle($communityId,$channelArticleId)
    {
        $channelArticle = new ChannelArticle([ChannelArticle::CHANNEL_ARTICLE_ID => $channelArticleId,Channel::COMMUNITY_ID => $communityId]);
        if (!$channelArticle->isEmpty())
        {
            $channelArticle->delete();
            return true;
        }
        return false;
    }

    public static function insertChannelArticle( $communityId,$mpUserId,$channelId, $articleDesc, $releaseDate, $shareUrl, $articleTitle, $articleDetail, $articleType, $articleUrl, $keepTop)
    {
        $keepTop = $keepTop == 1 ? 1 : 0;
        $channelArticle = new ChannelArticle();

        $channelArticle->setMpUserID($mpUserId)->setChannelID($channelId)->setReleaseDate($releaseDate)->setArticleType(ArticleType::ARTICLE_THIRD_PARTY);
        try
        {
            $channelArticle->setMpUserID($mpUserId)->setArticleDesc($articleDesc)
                ->setShareUrl($shareUrl)
                ->setArticleTitle($articleTitle)
                ->setArticleDetail($articleDetail)
                ->setArticleType($articleType)
                ->setArticleUrl($articleUrl)
                ->setKeepTop($keepTop)->setCommunityID($communityId)
                ->insert();
        }
        catch(\Exception $e)
        {
            return false;
        }
        return true;
    }

    public static function updateKeepTop($communityId,$channelId)
    {
            $article = new ChannelArticle([ChannelArticle::KEEP_TOP => "1",ChannelArticle::COMMUNITY_ID => $communityId,ChannelArticle::CHANNEL_ID => $channelId]);
            if (!$article->isEmpty()) {
                return $article->setKeepTop("0")->update();
            }
        return true;
    }
    public static function getArticleUrl($url,$mpUserID, $channelID, $communityID, $channelArticleID)
    {
        //"/wx_user/channel/ours?mp_user_id={$row['mp_user_id']}&channel_id={$row['channel_id']}&community_id={$row['community_id']}"
        $host = get_host();
        return sprintf("%s/%s?mp_user_id=%s&channel_id=%s&community_id=%s&channel_article_id=%s", $host, $url, $mpUserID, $channelID, $communityID, $channelArticleID);

    }
}
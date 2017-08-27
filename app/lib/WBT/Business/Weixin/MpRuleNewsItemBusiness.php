<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\MpRuleNewsItem;

class MpRuleNewsItemBusiness
{
    public static function getMpRuleNewsItemList( array $condition, array &$paging = NULL, $ranking,
                                                  array $outputColumns = NULL ) {

        return MpRuleNewsItem::fetchRowsWithCount( [ '*' ], $condition, NULL, $ranking, $paging, $outputColumns );
    }

    //编辑
    public static function update($mpUserId, $mpRuleNewsItemId, $title, $description, $picUrl, $url, $top_dir_no, $sortNo ) {
        $mpRuleNewsItem = new MpRuleNewsItem([MpRuleNewsItem::MP_USER_ID => $mpUserId,
                                             MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $mpRuleNewsItemId]);

        if ($mpRuleNewsItem->isEmpty()) {
            log_warn( "Could not find MpRuleNewsItem({$mpRuleNewsItemId})" );

            return FALSE;
        }

        $mpRuleNewsItem->setTitle( $title );
        $mpRuleNewsItem->setDescription( $description );
        $mpRuleNewsItem->setPicUrl( $picUrl );
        $mpRuleNewsItem->setTopDirNo($top_dir_no);
        $mpRuleNewsItem->setUrl( $url )->setSortNo($sortNo);
        $mpRuleNewsItem->save();

        return TRUE;
    }

    public static function insert( $mpUserId, $title, $description, $pic_url, $url, $top_dir_no, $sortNo) {
        $mpRuleNewsItem = new MpRuleNewsItem();

        $title       = trim( $title );
        $description = trim( $description );

        $mpRuleNewsItem->setMpUserID( $mpUserId )
            ->setTitle( $title )
            ->setDescription( $description )
            ->setPicUrl( $pic_url )
            ->setUrl( $url )
            ->setTopDirNo($top_dir_no)
            ->setSortNo($sortNo);

        $mpRuleNewsItem->insert();

        return $mpRuleNewsItem->getMpRuleNewsItemID();
    }

    public static function remove($mpUserId, $mpRuleNewsItemId ) {
        $mpRuleNewsItem = new MpRuleNewsItem([MpRuleNewsItem::MP_USER_ID => $mpUserId,
                                             MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $mpRuleNewsItemId]);
        if ($mpRuleNewsItem->isEmpty()) {
            log_warn( "Could not find MpRuleNewsItem($mpRuleNewsItemId)" );

            return FALSE;
        }
        $mpRuleNewsItem->delete();

        return TRUE;
    }
}
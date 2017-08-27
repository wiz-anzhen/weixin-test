<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\ArticleType;
use MP\Model\Mp\Cart;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\CartDetail;

class CartDetailBusiness extends BaseBusiness
{
    public static function getCartDetailList( array $condition, array &$paging = null, $ranking,
                                        array $outputColumns = null )
    {
        return CartDetail::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
}
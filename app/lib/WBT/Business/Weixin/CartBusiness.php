<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\ArticleType;
use MP\Model\Mp\Cart;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\MpArticle;

class CartBusiness extends BaseBusiness
{
    public static function getCartList( array $condition, array &$paging = null, $ranking,
                                             array $outputColumns = null )
    {
        return Cart::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
}
<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\ProductComment;

class ProductCommentBusiness extends BaseBusiness
{
    public static function getProductCommentList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return ProductComment::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }


}
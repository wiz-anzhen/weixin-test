<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\WxPayRecord;


class WxPayRecordBusiness extends BaseBusiness
{
    public static function getList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return WxPayRecord::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

}
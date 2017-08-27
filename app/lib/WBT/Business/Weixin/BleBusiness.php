<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 15-2-10
 * Time: 下午5:00
 */

namespace WBT\Business\Weixin;

use MP\Model\Mp\BeaconSetting;
class BleBusiness {
    public static function getList( array $condition, array &$paging = null, $ranking,
                                    array $outputColumns = null )
    {
        return BeaconSetting::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
} 
<?php

namespace WBT\Business\Weixin;


use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use MP\Model\Mp\Order;
use MP\Model\Mp\OrderChangeLog;
use MP\Model\Mp\OrderDetail;
use MP\Model\Mp\OrderStatus;
use MP\Model\Mp\Product;
use MP\Model\Mp\Store;
use WBT\Business\UserBusiness;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\WxUser;

class OrderDetailBusiness extends BaseBusiness
{
    public static function getList( array $condition, &$paging, $ranking,
                                    array $outputColumns = null )
    {
        return OrderDetail::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
}
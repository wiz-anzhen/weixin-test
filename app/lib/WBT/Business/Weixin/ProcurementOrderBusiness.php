<?php

namespace WBT\Business\Weixin;


use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\ProcurementOrderChangeLog;
use MP\Model\Mp\OrderDetail;
use MP\Model\Mp\OrderStatus;
use MP\Model\Mp\Product;
use MP\Model\Mp\Store;
use WBT\Business\UserBusiness;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\WxUser;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\WxPayRecord;

class ProcurementOrderBusiness extends BaseBusiness
{
    public static function getList( array $condition, &$paging, $ranking,
                                    array $outputColumns = null )
    {
        return ProcurementOrder::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getListChangeLog( array $condition, &$paging, $ranking,
                                             array $outputColumns = null )
    {
        return ProcurementOrderChangeLog::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getListDetail( array $condition, &$paging, $ranking,
                                          array $outputColumns = null )
    {
        return OrderDetail::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function orderUpdate( $id, $data_status )
    {
        $obj = new ProcurementOrder([ ProcurementOrder::ORDER_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Order($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $data_status=explode("|",$data_status);
        $change_time= time();
        $obj->setStatus($data_status[1])->update();

        $change_log = new ProcurementOrderChangeLog();
        $data=[ProcurementOrderChangeLog::ORDER_ID=>$id,ProcurementOrderChangeLog::OPERATOR=>UserBusiness::getLoginUsername(),
            ProcurementOrderChangeLog::COMMENT=>'无',ProcurementOrderChangeLog::CHANGE_TIME=>$change_time,
            ProcurementOrderChangeLog::STATUS_BEFORE=>$data_status[0],ProcurementOrderChangeLog::STATUS_AFTER=>$data_status[1],
        ];

        $change_log->apply( $data );

        $change_log->insert();

        return [ 'errno' => 0 ];
    }

    public static function getSelectByOrderTimeCondition($OrderTimeStart, $OrderTimeEnd)
    {
        $yearE = substr($OrderTimeEnd,0,4);
        $monthE = substr($OrderTimeEnd,4,2);
        $dayE = substr($OrderTimeEnd,6,2);
        $yearS = substr($OrderTimeStart,0,4);
        $monthS = substr($OrderTimeStart,4,2);
        $dayS = substr($OrderTimeStart,6,2);

        $newTimeStart = $yearS . "-" . $monthS . "-" . $dayS . " " . "00:00:00";
        $newTimeEnd = $yearE . "-" . $monthE . "-" . $dayE . " " . "23:59:59";
        $exprWx = sprintf("`create_time` >= '%s' and `create_time` <= '%s'",$newTimeStart,$newTimeEnd);

        $con = new \Bluefin\Data\DbCondition($exprWx);
        return $con;
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 15-2-6
 * Time: 上午11:53
 */

namespace WBT\Business\Weixin;


use MP\Model\Mp\TotalUser;
use Bluefin\Data\Database;
use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\Community;
class TotalUserBusiness extends BaseBusiness {
    public static function getTotalUserList($condition, $ranking, &$paging, $outputColumns)
    {
        return TotalUser::fetchRowsWithCount(['*'], $condition, null, $ranking, $paging, $outputColumns);
    }

    //获取统计用户数数据
    public static function getCountData()
    {
        $condition = [];

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }


        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 1;
        $outputColumns = TotalUser::s_metadata()->getFilterOptions();
        $ranking       = [ TotalUser::TOTAL_USER_ID =>true ];
        $data         = self::getTotalUserList($condition, $ranking, $paging, $outputColumns );
        $countData['total_user_num'] = $data[0]['total_user_num'];
        $countData['hour'] = $data[0]['insert_hour'];
        $ee = mktime (0,0,0,date("m") ,date("d"),date("Y"));
        $ymd = date("Y-m-d",($ee));
        $expr = "insert_time >= '" . $ymd ." 00:00:00' and insert_time <= '" . $ymd ." 23:59:59'";
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition[] = $dbCondition;
        $paging = [];
        $data         = self::getTotalUserList($condition, $ranking, $paging, $outputColumns );
        $totalActiveUser = 0;
        foreach($data as $count)
        {
            $totalActiveUser += $count['active_user_num'];
        }
        $countData['active_user_num'] = $totalActiveUser;
        $ee = mktime (0,0,0,date("m") ,date("d")-1,date("Y"));
        $ymd = date("Y-m-d",($ee));
        $expr = "insert_time >= '" . $ymd ." 00:00:00' and insert_time <= '" . $ymd ." 23:59:59'";
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition1[] = $dbCondition;
        $data         = self::getTotalUserList($condition1, $ranking, $paging, $outputColumns );
        $yesterdayActiveUser = 0;
        if(empty($data))
        {
            $yesterdayActiveUser = 0;
        }
        else
        {
            foreach($data as $pie)
            {
                $yesterdayActiveUser += $pie['active_user_num'];
            }

        }
        $countData['yesterday_active_user_num'] = $yesterdayActiveUser;
        return $countData;
    }
    public static function getProcurementData($mpUserId)
    {
        $totalPrice = ProcurementOrder::fetchColumn(ProcurementOrder::TOTAL_PRICE,
                                                    [ProcurementOrder::MP_USER_ID=>$mpUserId,
                                                     ProcurementOrder::STATUS=>'finished']);
        //当前餐厅累计采购总额
        $current_total_price = 0;
        foreach($totalPrice as $value)
        {
            $current_total_price += $value;
        }
        $procurementData['current_total_price'] = $current_total_price;
        //今天餐厅累计采购总额
        $ee = mktime (0,0,0,date("m") ,date("d"),date("Y"));
        $ymd = date("Y-m-d",($ee));
        $expr = "create_time >= '" . $ymd ." 00:00:00' and create_time <= '" . $ymd ." 23:59:59'";
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition= [$dbCondition,
            ProcurementOrder::MP_USER_ID=>$mpUserId,
            ProcurementOrder::STATUS=>'finished'];
        $totalPrice = ProcurementOrder::fetchColumn(ProcurementOrder::TOTAL_PRICE,$condition);
        $today_total_price = 0;
        foreach($totalPrice as $value)
        {
            $today_total_price += $value;
        }
        $procurementData['today_total_price'] = $today_total_price;
        //昨天餐厅采购总额
        $ee = mktime (0,0,0,date("m") ,date("d")-1,date("Y"));
        $ymd = date("Y-m-d",($ee));
        $expr = "create_time >= '" . $ymd ." 00:00:00' and create_time <= '" . $ymd ." 23:59:59'";
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition = [$dbCondition,
            ProcurementOrder::MP_USER_ID=>$mpUserId,
            ProcurementOrder::STATUS=>'finished'];
        $totalPriceYes = ProcurementOrder::fetchColumn(ProcurementOrder::TOTAL_PRICE,$condition);
        $yesterday_total_price = 0;
        foreach($totalPriceYes as $value)
        {
            $yesterday_total_price += $value;
        }
        $procurementData['yesterday_total_price'] = $yesterday_total_price;
        //当前餐厅累计总数
        $restaurant_count = Community::fetchCount([Community::MP_USER_ID=>$mpUserId,
                                             Community::IS_VIRTUAL=>0,
                                             Community::VALID=>1,
                                             Community::COMMUNITY_TYPE=>'procurement_restaurant']);
        $procurementData['restaurant_count'] = $restaurant_count;
        //当前供应商累计总数
        $supply_count = Community::fetchCount([Community::MP_USER_ID=>$mpUserId,
            Community::IS_VIRTUAL=>0,
            Community::VALID=>1,
            Community::COMMUNITY_TYPE=>'procurement_supply']);
        $procurementData['supply_count'] = $supply_count;
        return $procurementData;
    }
} 
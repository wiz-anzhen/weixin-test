<?php

use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\Store;
use MP\Model\Mp\Product;
use MP\Model\Mp\OrderChangeLog;
use WBT\Business\Weixin\ProcurementOrderBusiness;
use WBT\Business\Weixin\OrderDetailBusiness;
use WBT\Business\Weixin\OrderBusiness;
use MP\Model\Mp\ProcurementOrderDetail;
use MP\Model\Mp\Part;
use MP\Model\Mp\ProcurementOrderStatus;
set_include_path( LIB . '/PHPExcel' . PATH_SEPARATOR . get_include_path() );
require_once LIB . '/PHPExcel/PHPExcel.php';
require_once 'MpUserServiceBase.php';


class ProcurementOrderService extends MpUserServiceBase
{
//改变订单状态
    public function OrderUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( 'order_id' );
        $old_status  = $this->_app->request()->getQueryParam( 'old_status' );
        $data = $this->_app->request()->get( ProcurementOrder::STATUS );
        $data_status=$old_status."|".$data;

        return ProcurementOrderBusiness::orderUpdate( $id, $data_status );
    }

    public function returnSupply()
    {
        $id   = $this->_app->request()->get( 'store_id' );
        log_debug("========================".$id);
        $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $id,Store::IS_DELETE => "0"]);
        $partData = Part::fetchRows(['*'],[Part::COMMUNITY_ID => $id]);
        log_debug("========================",$supplyData);
        log_debug("========================",$partData);
        $totalData = [];
        $totalData['store'] = $supplyData;
        $totalData['part'] = $partData;
        return $totalData;
    }

    public function orderDownload()
    {
        $mpUserID = $this->_app->request()->getQueryParam( 'mp_user_id' );
        $communityID = $this->_app->request()->getQueryParam( 'community_id' );

        $orderID = $this->_app->request()->getQueryParam( 'c_order_id' );
        $status = $this->_app->request()->getQueryParam( 'c_status' );
        $name = $this->_app->request()->getQueryParam( 'c_customer_name' );
        $storeID = $this->_app->request()->getQueryParam( 'store_id' );
        $partIDc = $this->_app->request()->getQueryParam( 'part_id' );
        $orderTimeStart = $this->_app->request()->getQueryParam( 'o_time_start' );
        $orderTimeEnd = $this->_app->request()->getQueryParam( 'o_time_end' );
        $dataTotalPrice = $this->_app->request()->getQueryParam( 'data_total_price' );


        $communityType = $this->_app->request()->getQueryParam( 'community_type' );
        if($communityType == 'procurement_supply')
        {
            $condition[ProcurementOrder::BOUND_COMMUNITY_ID] = $communityID;
            if (!empty($storeID)) $condition[ProcurementOrder::BOUND_STORE_ID] = $storeID;
        }
        else
        {
            $condition[ProcurementOrder::COMMUNITY_ID] = $communityID;
            if (!empty($storeID)) $condition[ProcurementOrder::STORE_ID] = $storeID;
        }

        $condition[ProcurementOrder::MP_USER_ID] = $mpUserID;
        if (!empty($orderID)) $condition[ProcurementOrder::ORDER_ID] = $orderID;
        if (!empty($name)) $condition[ProcurementOrder::CUSTOMER_NAME] = $name;

        if(!empty($status)) $condition[ProcurementOrder::STATUS] = $status;

        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = ProcurementOrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        if($status == 'all')
        {
            unset($condition[ProcurementOrder::STATUS]);
        }

        log_debug("====================",$condition);
        $ranking =[ProcurementOrderDetail::ORDER_ID => True];
        $dataTotal = ProcurementOrderBusiness::getList( $condition, $paging=[], $ranking, NULL );
        log_debug("====================",$dataTotal);

        //处理订单信息
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('订单统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
//标题栏：订单编号 、订单状态、收货人姓名、收货人电话、 客服组、客服专员、商品名称、商品销售价、商品提成、商品数量

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '订单编号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '餐厅' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '供应商' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '档口' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4) . $row, '商品名称' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '商品单位' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6) . $row, '商品销售价' );

        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7) . $row, '商品数量' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8) . $row, '此订单总计' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, '订单状态' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 10 ) . $row, '订单创建日期' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 11 ) . $row, '订单创建时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 12 ) . $row, '下单者' );

        foreach ($dataTotal as $data)
        {
            $createDate = $data[ProcurementOrder::CREATE_TIME];
            $createYmd = substr($createDate ,0,10);
            $createTime = substr($createDate ,10);
            $firstOrderName = $data[ProcurementOrder::CUSTOMER_NAME];
            $supply = new Store([Store::STORE_ID => $data[ProcurementOrder::STORE_ID]]);
            $supplyTitle = $supply->getTitle();

            $restaurant = new Store([Store::STORE_ID => $data[ProcurementOrder::BOUND_STORE_ID]]);
            $restaurantTitle = $restaurant->getTitle();

            $newOrderID = $data[ProcurementOrder::ORDER_ID];
            $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $newOrderID]);



            $paging = [];
            if(empty($partIDc))
            {
                $con=[ProcurementOrderDetail::ORDER_ID => $newOrderID];
            }
            else
            {
                $con=[ProcurementOrderDetail::ORDER_ID => $newOrderID,ProcurementOrderDetail::PART_ID => $partIDc];
            }
            log_debug("99999999999999999999",$con);
            $ranking =[ProcurementOrderDetail::ORDER_ID => True];
            $arrDetail = ProcurementOrderDetail::fetchRows( [ '*' ], $con, null, $ranking, $paging, $outputColumns );

            foreach($arrDetail as $key => $v)
            {
                $partID = $v[ProcurementOrderDetail::PART_ID];
                $part = new Part([Part::PART_ID => $partID]);
                $partTitle = $part->getTitle();
                $price = $v[ProcurementOrderDetail::PRICE];
                $title = $v[ProcurementOrderDetail::TITLE];
                $count = $v[ProcurementOrderDetail::COUNT];
                $productUnit = \MP\Model\Mp\ProductUnitType::getDisplayName($v[ProcurementOrderDetail::PRODUCT_UNIT]);
                $dataProgress = explode("/",$productUnit);



                $row++;
                $objPHPExcel->getActiveSheet()
                    ->getCell(PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row)
                    ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setValueExplicit($data[ProcurementOrder::ORDER_ID]);

                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $restaurantTitle);

                $objPHPExcel->getActiveSheet()
                    ->getCell(PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row)
                    ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)->setValueExplicit($supplyTitle);
                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $partTitle);
                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $title);

                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $productUnit );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $price );

                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $count );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, (float)$count*(float)$price );

                $objPHPExcel->getActiveSheet()
                    ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row,
                        ProcurementOrderStatus::getDisplayName($data[ProcurementOrder::STATUS]));
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 10 ) . $row, $createYmd );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 11 ) . $row, $createTime );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 12 ) . $row, $firstOrderName );
            }
        }

        //$objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . ($row+1), "合计:".$dataTotalPrice );
        $filename = '微餐厅餐饮采购订单数据'.date("Y-m-d H：i：s");

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache - Control:must - revalidate, post - check = 0, pre - check = 0" );
        header( "Content-Type:application/force-download" );
        header( "Content-Type:application/vnd.ms-execl" );
        header( "Content-Type:application/octet-stream" );
        header( "Content-Type:application/download" );;
        header( 'Content-Disposition:attachment;filename="' . $filename . '.xlsx"' );
        header( "Content-Transfer-Encoding:binary" );
        $objWriter->save( 'php://output' );
    }

    public function orderDownloadTotal()
    {
        $mpUserID = $this->_app->request()->getQueryParam( 'mp_user_id' );
        $communityID = $this->_app->request()->getQueryParam( 'community_id' );

        $orderID = $this->_app->request()->getQueryParam( 'c_order_id' );
        $status = $this->_app->request()->getQueryParam( 'c_status' );
        $name = $this->_app->request()->getQueryParam( 'c_customer_name' );
        $storeID = $this->_app->request()->getQueryParam( 'store_id' );
        $partIDc = $this->_app->request()->getQueryParam( 'part_id' );
        $supplyID = $this->_app->request()->getQueryParam( 'supply_id' );
        $orderTimeStart = $this->_app->request()->getQueryParam( 'o_time_start' );
        $orderTimeEnd = $this->_app->request()->getQueryParam( 'o_time_end' );
        $dataTotalPrice = $this->_app->request()->getQueryParam( 'data_total_price' );


        $communityType = $this->_app->request()->getQueryParam( 'community_type' );


        $condition[ProcurementOrder::MP_USER_ID] = $mpUserID;
        if (!empty($orderID)) $condition[ProcurementOrder::ORDER_ID] = $orderID;
        if (!empty($name)) $condition[ProcurementOrder::CUSTOMER_NAME] = $name;
        if (!empty($storeID)) $condition[ProcurementOrder::COMMUNITY_ID] = $storeID;
        if (!empty($supplyID)) $condition[ProcurementOrder::STORE_ID] = $supplyID;
        if(!empty($status)) $condition[ProcurementOrder::STATUS] = $status;

        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = ProcurementOrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        if($status == 'all')
        {
            unset($condition[ProcurementOrder::STATUS]);
        }

        log_debug("====================",$condition);

        $dataTotal = ProcurementOrderBusiness::getList( $condition, $paging=[], NULL, NULL );
        log_debug("====================",$dataTotal);

        //处理订单信息
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('订单统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
//标题栏：订单编号 、订单状态、收货人姓名、收货人电话、 客服组、客服专员、商品名称、商品销售价、商品提成、商品数量

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '订单编号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '餐厅' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '供应商' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '档口' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4) . $row, '商品名称' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '商品单位' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6) . $row, '商品销售价' );

        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7) . $row, '商品数量' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8) . $row, '此订单总计' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, '订单状态' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 10 ) . $row, '订单创建日期' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 11 ) . $row, '订单创建时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 12 ) . $row, '下单者' );

        foreach ($dataTotal as $data)
        {
            $createDate = $data[ProcurementOrder::CREATE_TIME];
            $createYmd = substr($createDate ,0,10);
            $createTime = substr($createDate ,10);
            $firstOrderName = $data[ProcurementOrder::CUSTOMER_NAME];
            $supply = new Store([Store::STORE_ID => $data[ProcurementOrder::STORE_ID]]);
            $supplyTitle = $supply->getTitle();

            $restaurant = new Store([Store::STORE_ID => $data[ProcurementOrder::BOUND_STORE_ID]]);
            $restaurantTitle = $restaurant->getTitle();

            $newOrderID = $data[ProcurementOrder::ORDER_ID];
            $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $newOrderID]);



            $paging = [];
            if(empty($partIDc))
            {
                $con=[ProcurementOrderDetail::ORDER_ID => $newOrderID];
            }
            else
            {
                $con=[ProcurementOrderDetail::ORDER_ID => $newOrderID,ProcurementOrderDetail::PART_ID => $partIDc];
            }

            $ranking =[ProcurementOrderDetail::ORDER_ID => True];
            $arrDetail = ProcurementOrderDetail::fetchRows( [ '*' ], $con, null, $ranking, $paging, $outputColumns );

            foreach($arrDetail as $key => $v)
            {
                $partID = $v[ProcurementOrderDetail::PART_ID];
                $part = new Part([Part::PART_ID => $partID]);
                $partTitle = $part->getTitle();
                $price = $v[ProcurementOrderDetail::PRICE];
                $title = $v[ProcurementOrderDetail::TITLE];
                $count = $v[ProcurementOrderDetail::COUNT];
                $productUnit = \MP\Model\Mp\ProductUnitType::getDisplayName($v[ProcurementOrderDetail::PRODUCT_UNIT]);
                $dataProgress = explode("/",$productUnit);

                log_debug("[$title],[$count],[$price],");

                $row++;
                $objPHPExcel->getActiveSheet()
                    ->getCell(PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row)
                    ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setValueExplicit($data[ProcurementOrder::ORDER_ID]);

                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $restaurantTitle);

                $objPHPExcel->getActiveSheet()
                    ->getCell(PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row)
                    ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)->setValueExplicit($supplyTitle);
                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $partTitle);
                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $title);

                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $productUnit );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $price );

                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $count );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, (float)$count*(float)$price );

                $objPHPExcel->getActiveSheet()
                    ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row,
                        ProcurementOrderStatus::getDisplayName($data[ProcurementOrder::STATUS]));
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 10 ) . $row, $createYmd );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 11 ) . $row, $createTime );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 12 ) . $row, $firstOrderName );
            }
        }

        //$objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . ($row+1), "合计:".$dataTotalPrice );
        $filename = '微餐厅餐饮采购订单数据'.date("Y-m-d H：i：s");

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache - Control:must - revalidate, post - check = 0, pre - check = 0" );
        header( "Content-Type:application/force-download" );
        header( "Content-Type:application/vnd.ms-execl" );
        header( "Content-Type:application/octet-stream" );
        header( "Content-Type:application/download" );;
        header( 'Content-Disposition:attachment;filename="' . $filename . '.xlsx"' );
        header( "Content-Transfer-Encoding:binary" );
        $objWriter->save( 'php://output' );
    }
}
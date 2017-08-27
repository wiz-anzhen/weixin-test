<?php

use MP\Model\Mp\Order;
use MP\Model\Mp\OrderChangeLog;
use WBT\Business\Weixin\OrderBusiness;
use WBT\Business\Weixin\OrderDetailBusiness;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
set_include_path( LIB . '/PHPExcel' . PATH_SEPARATOR . get_include_path() );
require_once LIB . '/PHPExcel/PHPExcel.php';
require_once 'MpUserServiceBase.php';


class OrderService extends MpUserServiceBase
{
//改变订单状态
    public function OrderUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( 'order_id' );
        $old_status  = $this->_app->request()->getQueryParam( 'old_status' );
        $data = $this->_app->request()->get( Order::STATUS );
        $data_status=$old_status."|".$data;

        return OrderBusiness::orderUpdate( $id, $data_status );
    }
//改变订单状态
    public function OrderStatusUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Order::ORDER_ID );
        $new_status  = $this->_app->request()->getQueryParam( Order::STATUS );
        $old_status  = $this->_app->request()->getQueryParam( 'old_status' );
        $status=$new_status."|".$old_status;
        $check = $this->_app->request()->getQueryParam( 'check' );
        return OrderBusiness::OrderStatusUpdate( $id,$status,$check);
    }

    public function OrderUpdateComment()
    {
        $id   = $this->_app->request()->getQueryParam( 'order_id' );
        $reject   = $this->_app->request()->getQueryParam( 'reject' );
        $comment = $this->_app->request()->get( Order::COMMENT );
        $reason = $this->_app->request()->get( Order::REASON );
        return OrderBusiness::orderUpdateComment( $id,$reject, $comment ,$reason);
    }

    public function orderDownload()
    {
        $mpUserID = $this->_app->request()->getQueryParam( 'mp_user_id' );
        $communityID = $this->_app->request()->getQueryParam( 'community_id' );

        $orderID = $this->_app->request()->getQueryParam( 'c_order_id' );

        $tel = $this->_app->request()->getQueryParam( 'c_tel' );
        $csGroupID = $this->_app->request()->getQueryParam( 'c_cs_group_id' );
        $csID = $this->_app->request()->getQueryParam( 'c_cs_id' );
        $status = $this->_app->request()->getQueryParam( 'c_status' );
        $name = $this->_app->request()->getQueryParam( 'c_customer_name' );

        $timeStart = $this->_app->request()->getQueryParam( 'c_time_start' );
        $timeEnd = $this->_app->request()->getQueryParam( 'c_time_end' );

        $orderTimeStart = $this->_app->request()->getQueryParam( 'o_time_start' );
        $orderTimeEnd = $this->_app->request()->getQueryParam( 'o_time_end' );

        $payMethod = $this->_app->request()->getQueryParam( 'pay_method' );
        $payFinished = $this->_app->request()->getQueryParam( 'pay_finished' );


        $condition[Order::MP_USER_ID] = $mpUserID;
        $condition[Order::COMMUNITY_ID] = $communityID;
        if (!empty($orderID)) $condition[Order::ORDER_ID] = $orderID;


        if (!empty($tel)) $condition[Order::TEL] = $tel;
        if (!empty($name)) $condition[Order::CUSTOMER_NAME] = $name;
        if(!empty($status)) $condition[Order::STATUS] = $status;
        if(!empty($csGroupID)) $condition[Order::CS_GROUP_ID] = $csGroupID;
        if(!empty($csID)) $condition[Order::CS_ID] = $csID;

        if (!empty($payMethod))
        {
            if($payMethod == "online")
            {
                $expr = "pay_method != cash_pay";
                $con = new \Bluefin\Data\DbCondition($expr);
                $condition[] = $con;
            }
            else
            {
                $condition[Order::PAY_METHOD] = $payMethod;
            }
        }

        if($payFinished != "all" and $payFinished != '')
        {
            $condition[Order::PAY_FINISHED] = $payFinished;
        }

        if(!empty($timeStart) && !empty($timeEnd))
        {
            $condition[] = OrderBusiness::getSelectByTimeCondition($timeStart, $timeEnd);
        }
        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        if($status == 'all')
        {
            unset($condition[Order::STATUS]);
        }

        log_debug("====================",$condition);
        $data = OrderBusiness::getList( $condition, $paging=NULL, NULL, NULL );
        $newData = [];
        foreach($data as $value)
        {
            $process = [];//过程中使用的元素
            $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $value[Order::CS_ID]]);
            $csName = $cs->getName();
            $process["cs_name"] = $csName;
            $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $value[Order::CS_GROUP_ID]]);
            $csGroupName = $csGroup->getGroupName();
            $process["cs_group_name"] = $csGroupName;
            $newData[] = array_merge($value,$process);
        }
        log_debug("===============",$newData);
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('订单统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
//标题栏：订单编号 、订单状态、收货人姓名、收货人电话、 客服组、客服专员、商品名称、商品销售价、商品提成、商品数量

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '订单编号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '订单状态' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '收货人姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '收货人电话' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '收货人地址' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '客服组' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, '客服专员' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, '商品名称' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, '商品销售价' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, '商品提成' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 10) . $row, '商品数量' );

        foreach ($newData as $data)
        {
            $newOrderID = $data[Order::ORDER_ID];
            $order = new Order([Order::ORDER_ID =>$newOrderID]);


            $pag=null;
            $con=[\MP\Model\Mp\OrderDetail::ORDER_ID => $newOrderID];
            $arrDetail = OrderDetailBusiness::getList($con,$pag,null,null);
            $value = [];
            foreach($arrDetail as $key=>$v)
            {
                $value[] = $v[\MP\Model\Mp\OrderDetail::PRODUCT_ID];
            }
            $conPro = [\MP\Model\Mp\Product::PRODUCT_ID =>$value];
            $arrPro = \WBT\Business\Weixin\ProductBusiness::getList($conPro,$pag,null,null);
            foreach($arrPro as $key => $v)
            {
                $commissions = $v[\MP\Model\Mp\Product::COMMISSIONS];
                $orderDetail = new \MP\Model\Mp\OrderDetail([\MP\Model\Mp\OrderDetail::ORDER_ID => $newOrderID,\MP\Model\Mp\OrderDetail::PRODUCT_ID =>$v[\MP\Model\Mp\Product::PRODUCT_ID]]);
                $count = $orderDetail->getCount();
                $price = $orderDetail->getPrice();
                $title =$orderDetail->getTitle();
                log_debug("[$title],[$count],[$price],[$commissions]");

                $row++;
                $objPHPExcel->getActiveSheet()
                    ->getCell(PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row)
                    ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)
                    ->setValueExplicit($data[Order::ORDER_ID]);

                $objPHPExcel->getActiveSheet()
                    ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row,
                        \MP\Model\Mp\OrderStatus::getDisplayName($data[Order::STATUS]));

                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, $data[Order::CUSTOMER_NAME]);

                $objPHPExcel->getActiveSheet()
                    ->getCell(PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row)
                    ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)->setValueExplicit($data[Order::TEL]);

                $objPHPExcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $data[Order::ADDRESS]);


                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $data['cs_group_name'] );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $data['cs_name'] );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $title );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, "$price"."元" );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, $commissions );
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 10 ) . $row, $count );
            }
        }

        $filename = '订单统计';

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
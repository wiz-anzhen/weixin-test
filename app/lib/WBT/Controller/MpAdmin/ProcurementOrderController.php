<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Data\Database;
use Bluefin\Data\DbCondition;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\ProductComment;
use MP\Model\Mp\ProductUnitType;
use MP\Model\Mp\Store;
use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\ProcurementOrderChangeLog;
use MP\Model\Mp\ProcurementOrderDetail;
use MP\Model\Mp\ProcurementOrderStatus;
use MP\Model\Mp\PayMethod;
use MP\Model\Mp\Product;
use MP\Model\Mp\Community;
use MP\Model\Mp\OrderChangeLog;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\ReasonType;
use MP\Model\Mp\WxUser;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\OrderBusiness;
use WBT\Business\Weixin\ProcurementOrderBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Part;
use MP\Model\Mp\ProcurementOrderChangeDetail;

class ProcurementOrderController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "order";
        parent::_init();
    }
   //显示订单列表
    public function listAction()
    {
        $paging = []; // 先初始化为空
        $outputColumns = ProcurementOrder::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 20;

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $this->_view->set( "community_type", $communityType);

        $storeData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityId,Store::IS_DELETE => 0]);
        $this->_view->set( "store_data", $storeData );
        $partData = Part::fetchRows(['*'],[Part::COMMUNITY_ID => $communityId]);
        $this->_view->set( "part_data", $partData );

        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $partID = $storeID = $orderId = $name = $status = $statusSo = $orderTimeStart = $orderTimeEnd = null;

            $orderId = $this->_request->get( 'order_id' );
            $name    = $this->_request->get( 'customer_name' );
            $storeID    = $this->_request->get( 'store_id' );
            $partID    = $this->_request->get( 'part_id' );
            $status  = $this->_request->get( 'status' );
            $statusSo  = $this->_request->get( 'status_so' );
            $paging[Database::KW_SQL_PAGE_INDEX]    = $this->_request->get( 'page' );
            $orderTimeStart = $this->_request->get('order_time_start');
            $orderTimeEnd = $this->_request->get('order_time_end');



        $this->_view->set('orderId',$orderId);
        $this->_view->set('customer_name',$name);
        $this->_view->set('store_id',$storeID);
        $this->_view->set('part_id',$partID);
        $status = strict_in_array($status, ['all','chef_verify','supply_verify','examine','supply_examine','finished','refund','refund_finished']) ? $status : 'all';
        $this->_view->set('status', $status);
        $this->_view->set('status_so', $statusSo);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);


        $pageArr    = $this->_request->get( '*PAGING*' );
        $page = $pageArr['page'];
        if (!empty($page))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = intval($page);
        }
        else
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $this->_view->set('page', $paging[Database::KW_SQL_PAGE_INDEX]);


        // order
        $outputColumns = ProcurementOrder::s_metadata()->getFilterOptions();
        $ranking       = array( ProcurementOrder::ORDER_ID => TRUE );
        $condition = [];

        $condition[ProcurementOrder::MP_USER_ID] = $mpUserId;

        //“查看是供应商还是餐厅”
        if($communityType == CommunityType::PROCUREMENT_RESTAURANT)
        {
            $condition[ProcurementOrder::COMMUNITY_ID] = $communityId;
        }
        elseif($communityType == CommunityType::PROCUREMENT_SUPPLY)
        {
            $condition[ProcurementOrder::BOUND_COMMUNITY_ID] = $communityId;
        }



        if (!empty($orderId))
        {
            $condition[ProcurementOrder::ORDER_ID] = $orderId;
        }

        if (!empty($name))
        {
            $condition[ProcurementOrder::CUSTOMER_NAME] = $name;
        }

        if (!empty($storeID))
        {
            if($communityType == CommunityType::PROCUREMENT_RESTAURANT)
            {
                $condition[ProcurementOrder::STORE_ID] = $storeID;
            }
            elseif($communityType == CommunityType::PROCUREMENT_SUPPLY)
            {
                $condition[ProcurementOrder:: BOUND_STORE_ID ] = $storeID;
            }

        }



        if(!empty($statusSo))
        {
            $condition[ProcurementOrder::STATUS] = $statusSo;
        }
        else
        {
           $condition[ProcurementOrder::STATUS] = $status;
        }

        $this->_view->set('c_status', $condition[ProcurementOrder::STATUS]);

        if(!empty($orderTimeStart))
        {
            if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
                $this->_view->set("o_time_end_current",$orderTimeEnd);
            }

            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }

        if($condition[ProcurementOrder::STATUS] == 'all')
        {
            unset($condition[ProcurementOrder::STATUS]);
        }



        $pagingOrder = [];
        $data = ProcurementOrder::fetchRows( [ '*' ], $condition, null, $ranking, $pagingOrder, $outputColumns );
        $dataTotalPrice = "";
        log_debug("================================",$data);
        // order_detail
        $orderIds = [];
        if (count($data) > 0) {
            foreach($data as $item)
            {
                $orderIds[] = $item[ProcurementOrder::ORDER_ID];
                //$dataTotalPrice += $item[ProcurementOrder::TOTAL_PRICE];
            }
        }

        $orderDetails = [];
        if (count($orderIds) > 0)
        {
            $conditionDetail = [];
            $conditionDetail[ProcurementOrderDetail::ORDER_ID] = $orderIds;
            if($communityType == CommunityType::PROCUREMENT_RESTAURANT)
            {
            }
            elseif($communityType == CommunityType::PROCUREMENT_SUPPLY)
            {
                $partID = null;
            }
            if (!empty($partID))
            {
                $conditionDetail[ProcurementOrderDetail::PART_ID] = $partID;
            }
            $orderDetails = ProcurementOrderDetail::fetchRowsWithCount( [ '*' ], $conditionDetail, null, $ranking, $paging, $outputColumns );
            $dataTotalPrice = '';
            foreach($orderDetails as $key => $value)
            {
                $price = $value[ProcurementOrderDetail::PRICE]*$value[ProcurementOrderDetail::COUNT];
                $dataTotalPrice += $price;
            }
            $this->_view->set( "data_total_price", $dataTotalPrice);

        }

        log_debug("================================",$orderDetails);

        $power = $this->checkChangePower("order_rw","order_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('order_rw', $checkReadPower);
        $shownColumns  = [
            'order_id'        => [  Table::COLUMN_TITLE       => '订单号',
                                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                                    Table::COLUMN_FUNCTION   => function(array $row)use($communityType)
                                    {
                                        $orderID=$row[ProcurementOrder::ORDER_ID];
                                        $partID=$row[ProcurementOrderDetail::PART_ID];
                                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                                        if($communityType == CommunityType::PROCUREMENT_RESTAURANT)
                                        {
                                            $communityID = $order->getCommunityID();
                                        }
                                        elseif($communityType == CommunityType::PROCUREMENT_SUPPLY)
                                        {
                                            $communityID = $order->getBoundCommunityID();
                                        }

                                            $mpUserID=$order->getMpUserID();
                                            $orderID_a=substr($orderID,0,-9);
                                            $orderID_b=substr($orderID,-9);
                                            $orderID_c=$orderID_a."<br>".$orderID_b;//截取字符串节省空间
                                     return "<a target=\"_blank\"   href=\"/mp_admin/procurement_order/detail?mp_user_id={$mpUserID}&part_id={$partID}&order_id={$orderID}&community_id={$communityID}&type_procurement=single\"  >{$orderID_c}</a>";
                                        }
            ],
            ProcurementOrder::CREATE_TIME    => [ Table::COLUMN_TITLE      => '创建时间',
                                                    Table::COLUMN_CELL_STYLE => 'width:8%;text-align:center;',                                                                    Table::COLUMN_FUNCTION => function(array $row)
                                                    {
                                                         $orderID=$row[ProcurementOrder::ORDER_ID];
                                                         $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                                                         return $order->getCreateTime();
                                                    }],
            'customer_name'    => [ Table::COLUMN_TITLE      => '下单者姓名',
                                      Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                                      Table::COLUMN_FUNCTION => function(array $row)
                                          {
                                              $orderID=$row[ProcurementOrder::ORDER_ID];
                                              $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                                              return $order->getCustomerName();

                                          }],


            'status'           => [ Table::COLUMN_TITLE      => '交易状态',
                                    Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                                    Table::COLUMN_FUNCTION => function(array $row)
                                    {
                                            $orderID=$row[ProcurementOrder::ORDER_ID];
                                            $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                                            $status = ProcurementOrderStatus::getDisplayName($order->getStatus());
                                            return $status;
                                       }],
            'store_id'           => [ Table::COLUMN_TITLE      => '供应商',
                                        Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                                         Table::COLUMN_FUNCTION => function (array $row)
                                             {
                                                 $orderID=$row[ProcurementOrder::ORDER_ID];
                                                 $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                                                 $store = new Store([Store::STORE_ID => $order->getStoreID()]);
                                                 return $store->getTitle();
                                             }],
            'part_id'           => [ Table::COLUMN_TITLE      => '档口',
                                      Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                                      Table::COLUMN_FUNCTION => function (array $row)
                                      {
                                              $partID=$row[ProcurementOrderDetail::PART_ID];
                                              $part = new Part([Part::PART_ID => $partID]);
                                               return $part->getTitle();
                                       }],
            'bound_store_id'           => [ Table::COLUMN_TITLE      => '餐厅',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        $community = new Community([Community::COMMUNITY_ID =>$order->getCommunityID() ]);
                        return $community->getName();
                    }],
            'title'           => [ Table::COLUMN_TITLE      => '产品名称',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;'],
            'price'           => [ Table::COLUMN_TITLE      => '单价',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;'],
            'count'           => [ Table::COLUMN_TITLE      => '数量',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                ],
            'total_price'           => [ Table::COLUMN_TITLE      => '总价',
                                           Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                                           Table::COLUMN_FUNCTION => function (array $row)
                                          {
                                                   $totalPrice = (float)$row['price']*(float)$row['count'];
                                                   return $totalPrice;
                                          }],


            ProcurementOrder::COMMENT => [ Table::COLUMN_TITLE => "备注" ,
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',                                                                    Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        return $order->getComment();
                    }],

        ];

        if($checkReadPower)
        {
            $shownColumns['change'] = [ Table::COLUMN_TITLE      =>'操作',
                                        Table::COLUMN_CELL_STYLE  => 'width:8%',
                                        Table::COLUMN_FUNCTION =>function(array $row)use($power)
                                            {
                                                $order_id=$row[ProcurementOrder::ORDER_ID];
                                                $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $order_id]);
                                                $old_status=$order->getStatus();
                                                $community_id = $row[ProcurementOrder::COMMUNITY_ID];
                                                $mp_user_id=$row[ProcurementOrder::MP_USER_ID];
                                               // $print="<a href=\"/mp_admin/order/print?mp_user_id={$mp_user_id}&order_id={$order_id}&community_id={$community_id}\"  target=\"_blank\" >打印</a>";
                                                $link= new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/procurement_order_dialog/order_update?order_id={$order_id}&old_status={$old_status}&community_id={$community_id}',function() { reloadPage();});");
                                                return $link;
                                            }   ];
        }

        $table               = Table::fromDbData( $orderDetails, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );
    }

    //订单详情页面
    public function detailAction()
    {
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $this->_view->set( "community_type", $community->getCommunityType() );

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        if($mpUser->getIndustry() == IndustryType::RESTAURANT)
        {
            $this->_view->set( 'restaurant', true );
        }

        $typeProcurement = $this->_request->get( 'type_procurement');
        $this->_view->set( 'type_procurement', $typeProcurement );

        $orderId =  $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderId );
        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderId]);
        $this->_view->set( 'address', $order->getAddress() );
        $this->_view->set( 'tel', $order->getTel() );
        $this->_view->set( 'total_price', $order->getTotalPrice() );
        $this->_view->set( 'total_num', $order->getTotalNum() );
        $storeID = $order->getStoreID();
        $store = new Store([Store::STORE_ID => $storeID]);
        $this->_view->set( 'store_name', $store->getTitle() );
        $storeBound = new Store([Store::STORE_ID => $order->getBoundStoreID()]);
        $this->_view->set( 'store_bound_name', $storeBound->getTitle() );
        $this->_view->set( 'name', $order->getCustomerName() );

        $this->_view->set( 'status', $order->getStatus() );
        $this->_view->set( 'refund_describe', $order->getRefundDescribe() );
        $this->_view->set( 'refund_img_first', $order->getRefundImgFirst() );
        $this->_view->set( 'refund_img_second', $order->getRefundImgSecond() );
        $this->_view->set( 'refund_img_third', $order->getRefundImgThird() );

        $outputColumns = ProcurementOrder::s_metadata()->getFilterOptions();
        $ranking      = array( ProcurementOrderDetail::PART_ID => TRUE );
        $condition[ProcurementOrder::MP_USER_ID] = $mpUserId;
        $condition[ProcurementOrder::ORDER_ID] = $orderId;
        $dataDetail= ProcurementOrderDetail::fetchRows(['*'] ,$condition, null, $ranking, $paging, $outputColumns );
        log_debug("======================",$dataDetail);
        $dataDetailProgress = [];
        $partProgress = [];
        foreach($dataDetail as $key => $value)
        {
            $partProgress[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partProgress = array_unique($partProgress);
        foreach($dataDetail as $key => $value)
        {
            foreach($partProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v )
                {
                    $dataDetailProgress[$v][] = $value;
                }
            }
        }
        // 订单详情
        $paging = [];
        $dataDetailContent = [];
        foreach($dataDetailProgress as $key => $value)
        {
            $shownColumnsDetail  = [

                'title'      => [Table::COLUMN_TITLE => '商品名称',Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',],

                'price'      => [Table::COLUMN_TITLE => '单价',
                    Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            return$row[ProcurementOrderDetail::PRICE]."元";
                        }
                ],
                'product_unit'      => [Table::COLUMN_TITLE => '单位',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return ProductUnitType::getDisplayName($row[ProcurementOrderDetail::PRODUCT_UNIT]);
                        }
                ],
                'count'      => [Table::COLUMN_TITLE => '数量',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',],
                'this_total_price'      => [Table::COLUMN_TITLE => '金额',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return $row[ProcurementOrderDetail::PRICE]*$row[ProcurementOrderDetail::COUNT]."元";
                        }
                ],
            ];

            $tableDetail         = Table::fromDbData( $value, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumnsDetail,
                [ 'class' => 'table-bordered table-striped table-hover' ] );
            $tableDetail->showRecordNo = FALSE;
            $part = new Part([Part::PART_ID => $key]);
            $dataDetailContent[$key]["part"] = $part->getTitle();
            $dataDetailContent[$key]["content"] = $tableDetail;
        }

        $this->_view->set( 'table_detail_content', $dataDetailContent );
        // 订单历史记录
        $dataChangeLog= ProcurementOrderBusiness::getListChangeLog( $condition, $paging, $r = [], $outputColumns );
        $shownColumnsChangeLog  = [
            'operator' => [Table::COLUMN_TITLE => '操作人',Table::COLUMN_CELL_STYLE => 'width:15%',],
            'change_time'    => [Table::COLUMN_TITLE => '操作时间',Table::COLUMN_CELL_STYLE => 'width:25%',],
            'status_before'      => [Table::COLUMN_TITLE => '变更前状态',Table::COLUMN_CELL_STYLE => 'width:20%',
                Table::COLUMN_FUNCTION   => function(array $row){
                        return ProcurementOrderStatus::getDisplayName($row[OrderChangeLog::STATUS_BEFORE]);
                    },],
            'status_after'      => [Table::COLUMN_TITLE => '变更后状态',Table::COLUMN_CELL_STYLE => 'width:20%',
                Table::COLUMN_FUNCTION   => function(array $row){
                        return ProcurementOrderStatus::getDisplayName($row[OrderChangeLog::STATUS_AFTER]);
                    },],
            'comment'      => [Table::COLUMN_TITLE => '注释',Table::COLUMN_CELL_STYLE => 'width:20%',],
        ];

        $tableChangeLog         = Table::fromDbData( $dataChangeLog, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumnsChangeLog,
            [ 'class' => 'table-bordered table-striped table-hover ' ] );
        $tableChangeLog->showRecordNo = FALSE;
        $this->_view->set( 'table_change_log', $tableChangeLog );

        // 订货员初次下单记录
        $condition[ProcurementOrder::STATUS] = "chef_verify";
        $dataChefVerify = ProcurementOrderChangeDetail::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
        log_debug("======================",$dataChefVerify);
        $dataChefVerifyProgress = [];
        $partProgress = [];
        foreach($dataChefVerify as $key => $value)
        {
            $partProgress[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partProgress = array_unique($partProgress);
        foreach($dataChefVerify as $key => $value)
        {
            foreach($partProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v )
                {
                    $dataChefVerifyProgress[$v][] = $value;
                }
            }
        }
        $paging = [];
        $dataChefVerifyContent = [];
        foreach($dataChefVerifyProgress as $key => $value)
        {
            $shownColumnsChefVerify  = [

                'title'      => [Table::COLUMN_TITLE => '商品名称',Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',],

                'price'      => [Table::COLUMN_TITLE => '单价',
                    Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            return$row[ProcurementOrderDetail::PRICE]."元";
                        }
                ],
                'product_unit'      => [Table::COLUMN_TITLE => '单位',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return ProductUnitType::getDisplayName($row[ProcurementOrderDetail::PRODUCT_UNIT]);
                        }
                ],
                'count'      => [Table::COLUMN_TITLE => '数量',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',],
                'this_total_price'      => [Table::COLUMN_TITLE => '金额',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return $row[ProcurementOrderDetail::PRICE]*$row[ProcurementOrderDetail::COUNT]."元";
                        }
                ],
            ];

            $tableDetail         = Table::fromDbData( $value, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumnsChefVerify,
                [ 'class' => 'table-bordered table-striped table-hover' ] );
            $tableDetail->showRecordNo = FALSE;
            $part = new Part([Part::PART_ID => $key]);
            $dataChefVerifyContent[$key]["part"] = $part->getTitle();
            $dataChefVerifyContent[$key]["content"] = $tableDetail;
        }

        $this->_view->set( 'table_chef_verify_content', $dataChefVerifyContent  );

        // 厨师长记录
        $condition[ProcurementOrder::STATUS] = "supply_verify";
        $dataSupplyVerify = ProcurementOrderChangeDetail::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
        log_debug("======================",$dataSupplyVerify);
        $dataSupplyVerifyProgress = [];
        $partProgress = [];
        foreach($dataSupplyVerify as $key => $value)
        {
            $partProgress[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partProgress = array_unique($partProgress);
        foreach($dataSupplyVerify as $key => $value)
        {
            foreach($partProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v )
                {
                    $dataSupplyVerifyProgress[$v][] = $value;
                }
            }
        }
        // 订单详情
        $paging = [];
        $dataSupplyVerifyContent = [];
        foreach($dataSupplyVerifyProgress as $key => $value)
        {
            $shownColumnsSupplyVerify  = [

                'title'      => [Table::COLUMN_TITLE => '商品名称',Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',],

                'price'      => [Table::COLUMN_TITLE => '单价',
                    Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            return$row[ProcurementOrderDetail::PRICE]."元";
                        }
                ],
                'product_unit'      => [Table::COLUMN_TITLE => '单位',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return ProductUnitType::getDisplayName($row[ProcurementOrderDetail::PRODUCT_UNIT]);
                        }
                ],
                'count'      => [Table::COLUMN_TITLE => '数量',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',],
                'this_total_price'      => [Table::COLUMN_TITLE => '金额',
                    Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                    Table::COLUMN_FUNCTION => function (array $row)
                        {
                            return $row[ProcurementOrderDetail::PRICE]*$row[ProcurementOrderDetail::COUNT]."元";
                        }
                ],
            ];

            $tableDetail         = Table::fromDbData( $value, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumnsSupplyVerify,
                [ 'class' => 'table-bordered table-striped table-hover' ] );
            $tableDetail->showRecordNo = FALSE;
            $part = new Part([Part::PART_ID => $key]);
            $dataSupplyVerifyContent[$key]["part"] = $part->getTitle();
            $dataSupplyVerifyContent[$key]["content"] = $tableDetail;
        }

        $this->_view->set( 'table_supply_verify_content', $dataSupplyVerifyContent  );

        // 订货员再次记录
        $chefCount = [];
        for($i=1;$i<100;$i++)
        {
            $condition = [ProcurementOrderChangeDetail::STATUS => "examine",ProcurementOrderChangeDetail::CHEF_COUNT => $i];
            $condition[ProcurementOrder::MP_USER_ID] = $mpUserId;
            $condition[ProcurementOrder::ORDER_ID] = $orderId;
            $dataSupplyExamine = ProcurementOrderChangeDetail::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
            $dataSupplyExamineProgress = [];
            $partProgress = [];
            foreach($dataSupplyExamine as $key => $value)
            {
                $partProgress[] = $value[ProcurementOrderDetail::PART_ID];
            }
            $partProgress = array_unique($partProgress);
            foreach($dataSupplyExamine as $key => $value)
            {
                foreach($partProgress as $k => $v)
                {
                    if($value[ProcurementOrderDetail::PART_ID] == $v )
                    {
                        $dataSupplyExamineProgress[$v][] = $value;
                    }
                }
            }
            if(count($dataSupplyExamine) != 0)
            {
                $chefCount[$i]['times'] = $i;
                log_debug("======================",$dataSupplyExamine);
                // 订单详情
                $paging = [];
                $dataSupplyVerifyContent = [];
                foreach($dataSupplyExamineProgress as $key => $value)
                {
                    $shownColumnsSupplyExamine  = [

                        'title'      => [Table::COLUMN_TITLE => '商品名称',Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',],

                        'price'      => [Table::COLUMN_TITLE => '单价',
                            Table::COLUMN_CELL_STYLE => 'width:20%;text-align:center;',
                            Table::COLUMN_FUNCTION => function(array $row)
                                {
                                    return$row[ProcurementOrderDetail::PRICE]."元";
                                }
                        ],
                        'product_unit'      => [Table::COLUMN_TITLE => '单位',
                            Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                            Table::COLUMN_FUNCTION => function (array $row)
                                {
                                    return ProductUnitType::getDisplayName($row[ProcurementOrderDetail::PRODUCT_UNIT]);
                                }
                        ],
                        'count'      => [Table::COLUMN_TITLE => '数量',
                            Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',],
                        'this_total_price'      => [Table::COLUMN_TITLE => '金额',
                            Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                            Table::COLUMN_FUNCTION => function (array $row)
                                {
                                    return $row[ProcurementOrderDetail::PRICE]*$row[ProcurementOrderDetail::COUNT]."元";
                                }
                        ],
                    ];

                    $tableDetail         = Table::fromDbData( $value, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumnsSupplyExamine,
                        [ 'class' => 'table-bordered table-striped table-hover' ] );
                    $tableDetail->showRecordNo = FALSE;
                    $part = new Part([Part::PART_ID => $key]);
                    $dataSupplyVerifyContent[$key]["part"] = $part->getTitle();
                    $dataSupplyVerifyContent[$key]["content"] = $tableDetail;
                }



                $chefCount[$i]['content'] = $dataSupplyVerifyContent;
            }
            else
            {
                break;
            }
        }

        //log_debug("===================================",$chefCount);
        if(count($chefCount) != 0 )
        {
            $this->_view->set( 'chef_count', $chefCount);
        }


    }


    //显示订单列表总店
    public function totalAction()
    {
        $paging = []; // 先初始化为空
        $outputColumns = ProcurementOrder::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 20;

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityType = $community->getCommunityType();
        $this->_view->set( "community_type", $communityType);


        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $partID = $supplyID=$storeID = $orderId = $name = $status = $statusSo = $orderTimeStart = $orderTimeEnd = null;

        $orderId = $this->_request->get( 'order_id' );
        $name    = $this->_request->get( 'customer_name' );
        $storeID    = $this->_request->get( 'store_id' );
        $partID    = $this->_request->get( 'part_id' );
        $supplyID    = $this->_request->get( 'supply_id' );
        $status  = $this->_request->get( 'status' );
        $statusSo  = $this->_request->get( 'status_so' );
        $paging[Database::KW_SQL_PAGE_INDEX]    = $this->_request->get( 'page' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');



        $this->_view->set('orderId',$orderId);
        $this->_view->set('customer_name',$name);
        $this->_view->set('store_id',$storeID);
        $this->_view->set('part_id',$partID);
        $this->_view->set('supply_id',$supplyID);
        $supply = new Store([Store::STORE_ID => $supplyID]);
        $this->_view->set('supply_name',$supply->getTitle());
        $part = new Part([Part::PART_ID => $partID]);
        $this->_view->set('part_name',$part->getTitle());
        $status = strict_in_array($status, ['all','chef_verify','supply_verify','examine','supply_examine','finished','refund','refund_finished']) ? $status : 'all';
        $this->_view->set('status', $status);
        $this->_view->set('status_so', $statusSo);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);


        $pageArr    = $this->_request->get( '*PAGING*' );
        $page = $pageArr['page'];
        if (!empty($page))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = intval($page);
        }
        else
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $this->_view->set('page', $paging[Database::KW_SQL_PAGE_INDEX]);


        // order
        $outputColumns = ProcurementOrder::s_metadata()->getFilterOptions();
        $ranking       = array( ProcurementOrder::ORDER_ID => TRUE );
        $condition = [];

        $condition[ProcurementOrder::MP_USER_ID] = $mpUserId;

        //“查看是供应商还是餐厅”
        if($communityType == CommunityType::PROCUREMENT_RESTAURANT)
        {
            $condition[ProcurementOrder::COMMUNITY_ID] = $communityId;
        }
        elseif($communityType == CommunityType::PROCUREMENT_SUPPLY)
        {
            $condition[ProcurementOrder::BOUND_COMMUNITY_ID] = $communityId;
        }
        elseif($communityType == CommunityType::PROCUREMENT_TOTAL)
        {
            $restaurantData = Restaurant::fetchRows(["*"],[Restaurant::COMMUNITY_ID => $communityId]);
            $communityIds = [];

            foreach($restaurantData as $key => $value)
            {
                $communityIds[] = $value[Restaurant::BOUND_COMMUNITY_ID];
            }
            $condition[ProcurementOrder::COMMUNITY_ID] = $communityIds;
            $this->_view->set( "restaurant_data", $restaurantData );
        }



        if (!empty($orderId))
        {
            $condition[ProcurementOrder::ORDER_ID] = $orderId;
        }

        if (!empty($name))
        {
            $condition[ProcurementOrder::CUSTOMER_NAME] = $name;
        }

        if (!empty($storeID))
        {
            $condition[ProcurementOrder::COMMUNITY_ID] = $storeID;

        }

        if (!empty($supplyID))
        {
           $condition[ProcurementOrder::STORE_ID] = $supplyID;
        }

        if(!empty($statusSo))
        {
            $condition[ProcurementOrder::STATUS] = $statusSo;
        }
        else
        {
            $condition[ProcurementOrder::STATUS] = $status;
        }

        $this->_view->set('c_status', $condition[ProcurementOrder::STATUS]);

        if(!empty($orderTimeStart))
        {
            if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
                $this->_view->set("o_time_end_current",$orderTimeEnd);
            }

            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }

        if($condition[ProcurementOrder::STATUS] == 'all')
        {
            unset($condition[ProcurementOrder::STATUS]);
        }


        $pagingOrder = [];
        $data = ProcurementOrder::fetchRows( [ '*' ], $condition, null, $ranking, $pagingOrder, $outputColumns );
        $dataTotalPrice = "";

        // order_detail
        $orderIds = [];
        if (count($data) > 0) {
            foreach($data as $item)
            {
                $orderIds[] = $item[ProcurementOrder::ORDER_ID];
                //$dataTotalPrice += $item[ProcurementOrder::TOTAL_PRICE];
            }
        }

        $orderDetails = [];

        if (count($orderIds) > 0)
        {
            $conditionDetail = [];
            $conditionDetail[ProcurementOrderDetail::ORDER_ID] = $orderIds;
            if (!empty($partID))
            {
                $conditionDetail[ProcurementOrderDetail::PART_ID] = $partID;
            }
            $orderDetails = ProcurementOrderDetail::fetchRowsWithCount( [ '*' ], $conditionDetail, null, $ranking, $paging, $outputColumns );
            $dataTotalPrice = '';
            foreach($orderDetails as $key => $value)
            {
                $price = $value[ProcurementOrderDetail::PRICE]*$value[ProcurementOrderDetail::COUNT];
                $dataTotalPrice += $price;
            }
            $this->_view->set( "data_total_price", $dataTotalPrice);
        }
        //order_change_log
        $order_change_log=[];
        if (count($orderIds) > 0) {
            $query = OrderChangeLog::fetchRows(['*'],[OrderChangeLog::ORDER_ID => $orderIds]);
            if (count($query) > 0) {
                foreach($query as $item) {
                    $order_change_log[$item[OrderChangeLog::ORDER_ID]][] = $item;
                }
            }
        }


        $power = $this->checkChangePower("order_rw","order_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('order_rw', $checkReadPower);

        $shownColumns  = [
            'order_id'        => [  Table::COLUMN_TITLE       => '订单号',
                Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;',
                Table::COLUMN_FUNCTION   => function(array $row)use($communityType,$communityId)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $partID=$row[ProcurementOrderDetail::PART_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        $mpUserID=$order->getMpUserID();
                        $orderID_a=substr($orderID,0,-9);
                        $orderID_b=substr($orderID,-9);
                        $orderID_c=$orderID_a."<br>".$orderID_b;//截取字符串节省空间
                        return "<a target=\"_blank\"   href=\"/mp_admin/procurement_order/detail?mp_user_id={$mpUserID}&part_id={$partID}&order_id={$orderID}&community_id={$communityId}&type_procurement=total\"  >{$orderID_c}</a>";
                    }
            ],
            ProcurementOrder::CREATE_TIME    => [ Table::COLUMN_TITLE      => '创建时间',
                Table::COLUMN_CELL_STYLE => 'width:8%;text-align:center;',                                                                    Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        return $order->getCreateTime();
                    }],
            'customer_name'    => [ Table::COLUMN_TITLE      => '下单者姓名',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        return $order->getCustomerName();

                    }],


            'status'           => [ Table::COLUMN_TITLE      => '交易状态',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        $status = ProcurementOrderStatus::getDisplayName($order->getStatus());
                        return $status;
                    }],
            'store_id'           => [ Table::COLUMN_TITLE      => '供应商',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        $store = new Store([Store::STORE_ID => $order->getStoreID()]);
                        return $store->getTitle();
                    }],
            'part_id'           => [ Table::COLUMN_TITLE      => '档口',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $partID=$row[ProcurementOrderDetail::PART_ID];
                        $part = new Part([Part::PART_ID => $partID]);
                        return $part->getTitle();
                    }],
            'bound_store_id'           => [ Table::COLUMN_TITLE      => '餐厅',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        $community = new Community([Community::COMMUNITY_ID =>$order->getCommunityID() ]);
                        return $community->getName();
                    }],
            'title'           => [ Table::COLUMN_TITLE      => '产品名称',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;'],
            'price'           => [ Table::COLUMN_TITLE      => '单价',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;'],
            'count'           => [ Table::COLUMN_TITLE      => '数量',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
            ],
            'total_price'           => [ Table::COLUMN_TITLE      => '总价',
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $totalPrice = (float)$row['price']*(float)$row['count'];
                        return $totalPrice;
                    }],


            ProcurementOrder::COMMENT => [ Table::COLUMN_TITLE => "备注" ,
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',                                                                    Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $orderID=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
                        return $order->getComment();
                    }],

        ];

        if($checkReadPower)
        {
            $shownColumns['change'] = [ Table::COLUMN_TITLE      =>'操作',
                Table::COLUMN_CELL_STYLE  => 'width:8%',
                Table::COLUMN_FUNCTION =>function(array $row)use($power)
                    {
                        $order_id=$row[ProcurementOrder::ORDER_ID];
                        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $order_id]);
                        $old_status=$order->getStatus();
                        $community_id = $row[ProcurementOrder::COMMUNITY_ID];
                        $mp_user_id=$row[ProcurementOrder::MP_USER_ID];
                        //$print="<a href=\"/mp_admin/order/print?mp_user_id={$mp_user_id}&order_id={$order_id}&community_id={$community_id}\"  target=\"_blank\" >打印</a>";
                        $link= new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/procurement_order_dialog/order_update?order_id={$order_id}&old_status={$old_status}&community_id={$community_id}',function() { reloadPage();});");
                        return $link;
                    }   ];
        }


        $table               = Table::fromDbData( $orderDetails, $outputColumns, ProcurementOrder::ORDER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );
    }
}

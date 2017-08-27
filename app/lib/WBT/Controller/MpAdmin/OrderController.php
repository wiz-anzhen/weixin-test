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
use MP\Model\Mp\Order;
use MP\Model\Mp\OrderDetail;
use MP\Model\Mp\OrderStatus;
use MP\Model\Mp\PayMethod;
use MP\Model\Mp\Product;
use MP\Model\Mp\Community;
use MP\Model\Mp\OrderChangeLog;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\ReasonType;
use MP\Model\Mp\WxUser;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\OrderBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CustomerSpecialistGroup;


class OrderController extends CommunityControllerBase
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
        $outputColumns = Order::s_metadata()->getFilterOptions();
        $payMethod = $this->_request->get('pay_method');//此处位置不可调换
        $payFinished = $this->_request->get('pay_finished');//此处位置不可调换
        $condition = $this->_request->getQueryParams();

        if($payMethod == "online")
        {
            unset($condition[Order::PAY_METHOD]);
        }
        if($condition[Order::PAY_FINISHED] == "all")
        {
            unset($condition[Order::PAY_FINISHED]);
        }
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 20;




        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $mpUserId = $this->_request->get( 'mp_user_id' );


        $tel = $orderId = $name = $status = $statusSo = $timeStart = $timeEnd = $orderTimeStart = $orderTimeEnd = null;

            $tel     = $this->_request->get( 'tel' );
            $orderId = $this->_request->get( 'order_id' );
            $name    = $this->_request->get( 'customer_name' );
            $status  = $this->_request->get( 'status' );
            $statusSo  = $this->_request->get( 'status_so' );
            $paging[Database::KW_SQL_PAGE_INDEX]    = $this->_request->get( 'page' );
            $timeStart = $this->_request->get('time_start');//交易完成开始时间
            $timeEnd = $this->_request->get('time_end');//交易完成结束时间
            $orderTimeStart = $this->_request->get('order_time_start');
            $orderTimeEnd = $this->_request->get('order_time_end');


        $this->_view->set('tel',$tel);
        $this->_view->set('orderId',$orderId);
        $this->_view->set('customer_name',$name);
        $status = strict_in_array($status, ['all','submitted_to_pay','paid_to_verify','verified_to_ship','dispatched','arrived','finished','closed','reject','refund']) ? $status : 'submitted_to_pay';
        $this->_view->set('status', $status);
        $this->_view->set('pay_method', $payMethod);

        $this->_view->set('pay_finished', $payFinished);

        $defaultStatus = OrderStatus::DEFAULT_STATUS;
        $submittedToPay = OrderStatus::SUBMITTED_TO_PAY;//待付款
        $paidToVerify = OrderStatus::PAID_TO_VERIFY;//待审核
        $verifiedToShip = OrderStatus::VERIFIED_TO_SHIP;// 待发货
        $dispatched = OrderStatus::DISPATCHED;// 已发货
        $arrived = OrderStatus::ARRIVED;//已到达服务网点
        $finished = OrderStatus::FINISHED;//交易成功
        $closed = OrderStatus::CLOSED;//交易关闭
        $reject = OrderStatus::REJECT;//已拒收
        $refund = OrderStatus::REFUND;//退款退货中

        if($statusSo == $defaultStatus)
        {
            $this->_view->set("selected1",true);
        }
        else if($statusSo == $submittedToPay)
        {
            $this->_view->set("selected2",true);
        }
        else if($statusSo == $paidToVerify)
        {
            $this->_view->set("selected3",true);
        }
        else if($statusSo == $verifiedToShip)
        {
            $this->_view->set("selected4",true);
        }
        else if($statusSo == $dispatched)
        {
            $this->_view->set("selected5",true);
        }
        else if($statusSo == $arrived)
        {
            $this->_view->set("selected6",true);
        }
        else if($statusSo == $finished)
        {

            $this->_view->set("selected7",true);
        }
        else if($statusSo == $closed)
        {
            $this->_view->set("selected8",true);
        }
        else if($statusSo == $reject)
        {
            $this->_view->set("selected9",true);
        }
        else if($statusSo == $refund)
        {
            $this->_view->set("selected10",true);
        }
        else
        {
            $this->_view->set("selected0",true);
        }

        $conn =[CustomerSpecialistGroup::COMMUNITY_ID => $communityId];
        $csGroup =  CustomerSpecialistGroup::fetchRows( [ '*' ],$conn);
        $this->_view->set('group', $csGroup);
        $group_id    = $this->_request->get( 'group_name' );
        $this -> _view -> set("group_id",$group_id);
        $connCs = [CustomerSpecialist::COMMUNITY_ID => $communityId, CustomerSpecialist::MP_USER_ID => $mpUserId];
        $cs =  CustomerSpecialist::fetchRows( [ '*' ],$connCs);
        $this->_view->set('cs_arr', $cs);
        $cs_id    = $this->_request->get( 'cs' );
        $customerSpecialist = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $cs_id]);
        $this -> _view -> set("cs_name",$customerSpecialist->getName());
        $this -> _view -> set("cs_id",$cs_id);

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

        $this->_view->set( 'mp_user_id', $mpUserId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );
        // order
        $outputColumns = Order::s_metadata()->getFilterOptions();
        $ranking       = array( Order::ORDER_ID => TRUE );

        $condition[Order::MP_USER_ID] = $mpUserId;
        $condition[Order::COMMUNITY_ID] = $communityId;

        if (!empty($tel))
        {
            $condition[Order::TEL] = $tel;
        }

        if (!empty($orderId))
        {
            $condition[Order::ORDER_ID] = $orderId;
        }

        if (!empty($name))
        {
            $condition[Order::CUSTOMER_NAME] = $name;
        }

       // if ($status != 'all') $condition[Order::STATUS] = $status;

        if(!empty($statusSo))
        {
            $condition[Order::STATUS] = $statusSo;
        }
        else
        {
           $condition[Order::STATUS] = $status;
        }

        if(!empty($group_id) && empty($cs_id))
        {
            $condition[Order::CS_GROUP_ID] = $group_id;
        }

        if(!empty($cs_id))
        {
            $condition[Order::CS_ID] = $cs_id;
        }

        if (!empty($payMethod))
        {
            if($payMethod == "online")
            {

                $expr = sprintf("`pay_method` != '%s' ","cash_pay");
                $con = new \Bluefin\Data\DbCondition($expr);
                $condition[] = $con;
            }
            else
            {
                $condition[Order::PAY_METHOD] = $payMethod;
            }
        }

        if ($payFinished != "all")
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
        $this->_view->set("c_tel",$condition['tel']);
        $this->_view->set("c_order_id",$condition['order_id']);
        $this->_view->set("c_customer_name",$condition['customer_name']);
        $this->_view->set("c_status",$condition['status']);
        $this->_view->set("c_cs_group_id",$condition['cs_group_id']);
        $this->_view->set("c_cs_id",$condition['cs_id']);
        $this->_view->set("c_time_start",$timeStart);
        $this->_view->set("c_time_end",$timeEnd);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);


        if($condition[Order::STATUS] == 'all')
        {
            unset($condition[Order::STATUS]);
        }
        unset($condition['status_so']);
        unset($condition['group_name']);
        unset($condition['cs']);
        unset($condition['order_time_start']);
        unset($condition['order_time_end']);
        unset($condition['time_start']);
        unset($condition['time_end']);

        if($payFinished == '0')
        {
            $condition = array_filter($condition);
            $condition[Order::PAY_FINISHED] = 0;
        }
        else
        {
            $condition = array_filter($condition);
        }

        log_debug("order query condition:", $condition);
        $data = OrderBusiness::getList( $condition, $paging, $ranking, $outputColumns );
        // order_detail
        $orderIds = [];
        if (count($data) > 0) {
            foreach($data as $item) {
                $orderIds[] = $item[Order::ORDER_ID];
            }
        }
        $orderDetails = [];
        if (count($orderIds) > 0)
        {
            $query = OrderDetail::fetchRows(['*'], [OrderDetail::ORDER_ID => $orderIds]);
            if (count($query) > 0)
            {
                foreach($query as $item)
                {
                    $orderDetails[$item[OrderDetail::ORDER_ID]][] = $item;
                }

            }
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
                                    Table::COLUMN_FUNCTION   => function(array $row)
                                    {
                                            $orderID=$row[Order::ORDER_ID];
                                            $communityID = $row[Order::COMMUNITY_ID];
                                            $mpUserID=$row[Order::MP_USER_ID];
                                            $orderID_a=substr($orderID,0,-9);
                                            $orderID_b=substr($orderID,-9);
                                            $orderID_c=$orderID_a."<br>".$orderID_b;//截取字符串节省空间
                                     return "<a target=\"_blank\"   href=\"/mp_admin/order/detail?mp_user_id={$mpUserID}&order_id={$orderID}&community_id={$communityID}\"  >{$orderID_c}</a>";
                                        }
            ],
            'customer_name'    => [ Table::COLUMN_TITLE      => '收货人姓名',
                                    Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',   ],
            'cs_name' => [
                Table::COLUMN_TITLE => '客服专员',
                Table::COLUMN_CELL_STYLE => 'width:7%;text-align:center;',
                Table::COLUMN_FUNCTION =>function($row){
                        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $row[Order::CS_ID]]);
                        return $cs->getName();
                    }
            ],
            'cs_group_name' => [
                Table::COLUMN_TITLE => '客服组',
                Table::COLUMN_CELL_STYLE => 'width:7%;text-align:center;',
                Table::COLUMN_FUNCTION =>function($row){
                        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $row[Order::CS_GROUP_ID]]);
                        return $csGroup->getGroupName();
                    }
            ],
            'detail'           => [ Table::COLUMN_TITLE      => '订单详情',
                                    Table::COLUMN_CELL_STYLE  => 'width:18%;text-align:center;',
                                    Table::COLUMN_FUNCTION   => function(array $row) use ($orderDetails)
                                        {
                                            if(!isset($orderDetails[$row[Order::ORDER_ID]]))
                                            {
                                                return '';
                                            }

                                            $communityID = $row[Order::COMMUNITY_ID];
                                            $mpUserID=$row[Order::MP_USER_ID];
                                            $ret = '<table style="border:0 solid white">';
                                            foreach($orderDetails[$row[Order::ORDER_ID]] as $orderDetail)
                                            {
                                                $orderDetail[OrderDetail::TITLE]= "<a target=\"_blank\"   href=\"/mp_admin/store/product?mp_user_id={$mpUserID}&product_id={$orderDetail[OrderDetail::PRODUCT_ID]}&community_id={$communityID}\"  >{$orderDetail[OrderDetail::TITLE]}</a>";
                                                $ret .= sprintf("
                                       <tr style=\"border:0 solid white\">
                                       <td style=\"border-style:hidden;padding-right:0\">%s</td>
                                       </tr>
                                       <tr style=\"border:0 solid white\">
                                       <td style=\"border-style:hidden;\">%.2f&nbsp&nbsp</td>
                                       <td style=\"border-style:hidden;padding-left: 4px\">%d&nbsp&nbsp</td></tr> ",  $orderDetail[OrderDetail::TITLE], $orderDetail[OrderDetail::PRICE], $orderDetail[OrderDetail::COUNT]);
                                            }
                                            $ret .= "</table>";
                                            return $ret;
                                        }],
            'status'           => [ Table::COLUMN_TITLE      => '交易状态',
                                    Table::COLUMN_CELL_STYLE => 'width:7%;text-align:center;', ],
            'total_price'           => [ Table::COLUMN_TITLE      => '总价',
                                    Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;', ],
            'total_num'           => [ Table::COLUMN_TITLE      => '总数量',
                                         Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;', ],
            Order::PAY_METHOD => [ Table::COLUMN_TITLE => "支付方式" ,
                                   Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',],
            Order::COMMENT => [ Table::COLUMN_TITLE => "备注" ,
                Table::COLUMN_CELL_STYLE => 'width:12%;text-align:center;',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        if($row[Order::REASON ] == "other")
                        {
                            $reason = $row[Order::COMMENT];
                        }
                        else
                        {
                            $reason = ReasonType::getDisplayName($row[Order::REASON]);
                        }

                        if($row[Order::STATUS] == OrderStatus::REJECT)
                        {
                            return "拒收原因：<br>".$reason;
                        }
                        elseif($row[Order::STATUS] == OrderStatus::REFUND)
                        {
                            return "退款退货原因：<br>".$reason;
                        }
                        elseif($row[Order::STATUS] == OrderStatus::CLOSED)
                        {
                            return "取消原因：<br>".$reason;
                        }

                    }],
            Order::PAY_FINISHED => [Table::COLUMN_TITLE => "支付状态",
                                    Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',
                                    Table::COLUMN_FUNCTION => function(array $row)
                                        {
                                            if($row[Order::PAY_FINISHED] == 0)
                                            {
                                                return "待支付";
                                            }
                                            else
                                            {
                                                 return "已支付" ;
                                            }
                                        }]

        ];

        if($checkReadPower)
        {
            $shownColumns['action']  = [Table::COLUMN_TITLE      =>'交易操作',
                Table::COLUMN_CELL_STYLE  => 'width:10%;text-align:center;',
                Table::COLUMN_FUNCTION    => function(array $row) {
                        $orderId=$row[Order::ORDER_ID];
                        $old_status = $row[Order::STATUS];
                        $ret=[
                            new Link('<p style=" margin-bottom:2px;text-align: center">取消</p>', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_status_update?order_id={$orderId}&reject=cancel');"),

                            new Link('<button class="btn btn-success" style=" margin-bottom:2px;">发货</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=dispatched&old_status={$old_status}',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),

                            new Link('<button class="btn btn-success" style=" margin-bottom:2px;">确认收款</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=finished&old_status={$old_status}',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),
                            new Link('<button class="btn btn-danger" style=" margin-bottom:2px;">交易关闭</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=closed&old_status={$old_status}',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),
                            new Link('<p style=" margin-bottom:2px;text-align: center">退款/退货</p>', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_status_update?order_id={$orderId}&reject=refund');"),
                            new Link('<p style=" margin-bottom:2px;text-align: center">拒收</p>', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_status_update?order_id={$orderId}&reject=reject');"),];
                        if($row[Order::STATUS] == OrderStatus::SUBMITTED_TO_PAY)
                        {
                            return $ret[0];
                        }
                        if($row[Order::STATUS] == OrderStatus::VERIFIED_TO_SHIP)
                        {
                            if($row[Order::PAY_METHOD] == PayMethod::CASH_PAY)
                            {
                                return $ret[1].$ret[0];
                            }
                            else
                            {
                                if($row[Order::PAY_FINISHED] == 0)
                                {
                                    return $ret[1].$ret[0];
                                }
                                else
                                {
                                    return $ret[1].$ret[0].$ret[4];
                                }

                            }

                        }
                        if($row[Order::STATUS] == OrderStatus::DISPATCHED)
                        {
                            if($row[Order::PAY_METHOD] == PayMethod::CASH_PAY)
                            {
                                return $ret[2].$ret[5];
                            }
                            else
                            {
                                return $ret[2].$ret[4];
                            }
                        }

                    }, ];
            $shownColumns['change'] = [ Table::COLUMN_TITLE      =>'操作',
                                        Table::COLUMN_CELL_STYLE  => 'width:8%',
                                        Table::COLUMN_FUNCTION =>function(array $row)use($power)
                                            {
                                                $old_status=$row[Order::STATUS];
                                                $order_id=$row[Order::ORDER_ID];
                                                $community_id = $row[Order::COMMUNITY_ID];
                                                $mp_user_id=$row[Order::MP_USER_ID];
                                                $print="<a href=\"/mp_admin/order/print?mp_user_id={$mp_user_id}&order_id={$order_id}&community_id={$community_id}\"  target=\"_blank\" >打印</a>";
                                                $link= new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_update?order_id={$order_id}&old_status={$old_status}&community_id={$community_id}',function() { reloadPage();});");
                                                return $link."<br>".$print;
                                            }   ];
        }


        $table               = Table::fromDbData( $data, $outputColumns, Order::ORDER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );
    }


    //打印订单
    public function printAction()
    {
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'sale_list_name', $mpUser->getSaleListName() );

        $orderId =  $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderId );
        $order = new Order([Order::ORDER_ID => $orderId]);
        $this->_view->set( 'address', $order->getAddress() );
        $this->_view->set( 'tel', $order->getTel() );
        $this->_view->set( 'total_price', $order->getTotalPrice() );
        $this->_view->set( 'total_num', $order->getTotalNum() );
        $this->_view->set( 'name', $order->getCustomerName() );
        $timePrint = date("Y-m-d H:i:s");
        $this->_view->set( 'time_print', $timePrint );
        $cs= new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $order->getCsID()]);
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $order->getCsGroupID() ]);
        if($cs->isEmpty())
        {
            $this->_view->set( 'cs_name', "客户服务中心" );
            $this->_view->set( 'cs_group_name',"客户服务中心" );
            $this->_view->set( 'cs_tel', $community->getPhone() );
        }
        else
        {
            $this->_view->set( 'cs_name', $cs->getName() );
            $this->_view->set( 'cs_group_name', $csGroup->getGroupName() );
            $this->_view->set( 'cs_tel', $cs->getPhone() );
        }

        $outputColumns = Order::s_metadata()->getFilterOptions();
        $ranking       =  null;
        $condition[Order::MP_USER_ID] = $mpUserId;
        $condition[Order::ORDER_ID] = $orderId;
        $data= OrderBusiness::getListDetail( $condition, $paging, $ranking, $outputColumns );

        // 订单详情
        $shownColumns  = [
            'product_id' => [Table::COLUMN_TITLE => '商品编号',Table::COLUMN_CELL_STYLE => 'width:15%',],
            'title'      => [Table::COLUMN_TITLE => '商品名称',Table::COLUMN_CELL_STYLE => 'width:20%',],
            'count'      => [Table::COLUMN_TITLE => '数量',Table::COLUMN_CELL_STYLE => 'width:20%',],
            'price'      => [Table::COLUMN_TITLE => '单价',Table::COLUMN_CELL_STYLE => 'width:20%',],
        ];

        $table         = Table::fromDbData( $data, $outputColumns, Order::ORDER_ID, $paging, $shownColumns,
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

        $orderId =  $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderId );
        $order = new Order([Order::ORDER_ID => $orderId]);
        $this->_view->set( 'address', $order->getAddress() );
        $this->_view->set( 'tel', $order->getTel() );
        $this->_view->set( 'total_price', $order->getTotalPrice() );
        $this->_view->set( 'total_num', $order->getTotalNum() );
        $this->_view->set( 'name', $order->getCustomerName() );
        if($community->getCommunityType() == CommunityType::NONE)
        {
            $cs= new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $order->getCsID()]);
            $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $order->getCsGroupID() ]);
            if($cs->isEmpty())
            {
                $this->_view->set( 'cs_name', "客户服务中心" );
                $this->_view->set( 'cs_group_name',"客户服务中心" );
                $this->_view->set( 'cs_tel', $community->getPhone() );
            }
            else
            {
                $this->_view->set( 'cs_name', $cs->getName() );
                $this->_view->set( 'cs_group_name', $csGroup->getGroupName() );
                $this->_view->set( 'cs_tel', $cs->getPhone() );
            }
        }


        $outputColumns = Order::s_metadata()->getFilterOptions();
        $ranking       = [];
        $condition[Order::MP_USER_ID] = $mpUserId;
        $condition[Order::ORDER_ID] = $orderId;
        $dataDetail= OrderBusiness::getListDetail( $condition, $paging, $ranking, $outputColumns );
        // 订单详情
        $shownColumnsDetail  = [
            'product_id' => [Table::COLUMN_TITLE => '商品编号',Table::COLUMN_CELL_STYLE => 'width:15%',],
            'img_url'    => [Table::COLUMN_TITLE => '商品图片',
                             Table::COLUMN_FUNCTION => function(array $row){
                                     return sprintf('<img src="%s" width="40px" height="40px" alt="没有图片"/>',
                                     $row[OrderDetail::IMG_URL] );
                                 }],
            'title'      => [Table::COLUMN_TITLE => '商品名称',Table::COLUMN_CELL_STYLE => 'width:20%',],
            'count'      => [Table::COLUMN_TITLE => '数量',Table::COLUMN_CELL_STYLE => 'width:20%',],
            'price'      => [Table::COLUMN_TITLE => '单价',Table::COLUMN_CELL_STYLE => 'width:20%',],
        ];

        $tableDetail         = Table::fromDbData( $dataDetail, $outputColumns, Order::ORDER_ID, $paging, $shownColumnsDetail,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $tableDetail->showRecordNo = FALSE;
        $this->_view->set( 'table_detail', $tableDetail );
        // 订单历史记录
        $dataChangeLog= OrderBusiness::getListChangeLog( $condition, $paging, $ranking, $outputColumns );
        $shownColumnsChangeLog  = [
            'operator' => [Table::COLUMN_TITLE => '操作人',Table::COLUMN_CELL_STYLE => 'width:15%',],
            'change_time'    => [Table::COLUMN_TITLE => '操作时间',Table::COLUMN_CELL_STYLE => 'width:25%',],
            'status_before'      => [Table::COLUMN_TITLE => '变更前状态',Table::COLUMN_CELL_STYLE => 'width:20%',
                                     Table::COLUMN_FUNCTION   => function(array $row){
                                            return OrderStatus::getDisplayName($row[OrderChangeLog::STATUS_BEFORE]);
                                         },],
            'status_after'      => [Table::COLUMN_TITLE => '变更后状态',Table::COLUMN_CELL_STYLE => 'width:20%',
                                    Table::COLUMN_FUNCTION   => function(array $row){
                    return OrderStatus::getDisplayName($row[OrderChangeLog::STATUS_AFTER]);
                },],
            'comment'      => [Table::COLUMN_TITLE => '注释',Table::COLUMN_CELL_STYLE => 'width:20%',],
        ];

        $tableChangeLog         = Table::fromDbData( $dataChangeLog, $outputColumns, Order::ORDER_ID, $paging, $shownColumnsChangeLog,
            [ 'class' => 'table-bordered table-striped table-hover ' ] );
        $tableChangeLog->showRecordNo = FALSE;
        $this->_view->set( 'table_change_log', $tableChangeLog );


    }

    //显示订单列表
    public function restaurantAction()
    {
        $paging = []; // 先初始化为空
        $outputColumns = Community::s_metadata()->getFilterOptions();
        $payMethod = $this->_request->get('pay_method');//此处位置不可调换
        $payFinished = $this->_request->get('pay_finished');//此处位置不可调换
        $condition = $this->_request->getQueryParams();

        if($payMethod == "online")
        {
            unset($condition[Order::PAY_METHOD]);
        }
        if($condition[Order::PAY_FINISHED] == "all")
        {
            unset($condition[Order::PAY_FINISHED]);
        }
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 20;




        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $mpUserId = $this->_request->get( 'mp_user_id' );


        $tel = $orderId = $name = $status = $statusSo = $timeStart = $timeEnd = $orderTimeStart = $orderTimeEnd = null;

        $tel     = $this->_request->get( 'tel' );
        $orderId = $this->_request->get( 'order_id' );
        $name    = $this->_request->get( 'customer_name' );
        $status  = $this->_request->get( 'status' );
        $statusSo  = $this->_request->get( 'status_so' );
        $paging[Database::KW_SQL_PAGE_INDEX]    = $this->_request->get( 'page' );
        $timeStart = $this->_request->get('time_start');//交易完成开始时间
        $timeEnd = $this->_request->get('time_end');//交易完成结束时间
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');


        $this->_view->set('tel',$tel);
        $this->_view->set('orderId',$orderId);
        $this->_view->set('customer_name',$name);
        $status = strict_in_array($status, ['all','submitted_to_pay','paid_to_verify','verified_to_ship','dispatched','arrived','finished','closed','reject','refund']) ? $status : 'submitted_to_pay';
        $this->_view->set('status', $status);
        $this->_view->set('pay_method', $payMethod);

        $this->_view->set('pay_finished', $payFinished);

        $defaultStatus = OrderStatus::DEFAULT_STATUS;
        $submittedToPay = OrderStatus::SUBMITTED_TO_PAY;//待付款
        $paidToVerify = OrderStatus::PAID_TO_VERIFY;//待审核
        $verifiedToShip = OrderStatus::VERIFIED_TO_SHIP;// 待发货
        $dispatched = OrderStatus::DISPATCHED;// 已发货
        $arrived = OrderStatus::ARRIVED;//已到达服务网点
        $finished = OrderStatus::FINISHED;//交易成功
        $closed = OrderStatus::CLOSED;//交易关闭
        $reject = OrderStatus::REJECT;//已拒收
        $refund = OrderStatus::REFUND;//退款退货中

        if($statusSo == $defaultStatus)
        {
            $this->_view->set("selected1",true);
        }
        else if($statusSo == $submittedToPay)
        {
            $this->_view->set("selected2",true);
        }
        else if($statusSo == $paidToVerify)
        {
            $this->_view->set("selected3",true);
        }
        else if($statusSo == $verifiedToShip)
        {
            $this->_view->set("selected4",true);
        }
        else if($statusSo == $dispatched)
        {
            $this->_view->set("selected5",true);
        }
        else if($statusSo == $arrived)
        {
            $this->_view->set("selected6",true);
        }
        else if($statusSo == $finished)
        {

            $this->_view->set("selected7",true);
        }
        else if($statusSo == $closed)
        {
            $this->_view->set("selected8",true);
        }
        else if($statusSo == $reject)
        {
            $this->_view->set("selected9",true);
        }
        else if($statusSo == $refund)
        {
            $this->_view->set("selected10",true);
        }
        else
        {
            $this->_view->set("selected0",true);
        }

        $conn =[CustomerSpecialistGroup::COMMUNITY_ID => $communityId];
        $csGroup =  CustomerSpecialistGroup::fetchRows( [ '*' ],$conn);
        $this->_view->set('group', $csGroup);
        $group_id    = $this->_request->get( 'group_name' );
        $this -> _view -> set("group_id",$group_id);
        $connCs = [CustomerSpecialist::COMMUNITY_ID => $communityId, CustomerSpecialist::MP_USER_ID => $mpUserId];
        $cs =  CustomerSpecialist::fetchRows( [ '*' ],$connCs);
        $this->_view->set('cs_arr', $cs);
        $cs_id    = $this->_request->get( 'cs' );
        $customerSpecialist = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $cs_id]);
        $this -> _view -> set("cs_name",$customerSpecialist->getName());
        $this -> _view -> set("cs_id",$cs_id);

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

        $this->_view->set( 'mp_user_id', $mpUserId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );
        // order
        $outputColumns = Order::s_metadata()->getFilterOptions();
        $ranking       = array( Order::ORDER_ID => TRUE );

        $condition[Order::MP_USER_ID] = $mpUserId;
        $condition[Order::COMMUNITY_ID] = $communityId;

        if (!empty($tel))
        {
            $condition[Order::TEL] = $tel;
        }

        if (!empty($orderId))
        {
            $condition[Order::ORDER_ID] = $orderId;
        }

        if (!empty($name))
        {
            $condition[Order::CUSTOMER_NAME] = $name;
        }

        // if ($status != 'all') $condition[Order::STATUS] = $status;

        if(!empty($statusSo))
        {
            $condition[Order::STATUS] = $statusSo;
        }
        else
        {
            $condition[Order::STATUS] = $status;
        }

        if(!empty($group_id) && empty($cs_id))
        {
            $condition[Order::CS_GROUP_ID] = $group_id;
        }

        if(!empty($cs_id))
        {
            $condition[Order::CS_ID] = $cs_id;
        }

        if (!empty($payMethod))
        {
            if($payMethod == "online")
            {

                $expr = sprintf("`pay_method` != '%s' ","cash_pay");
                $con = new \Bluefin\Data\DbCondition($expr);
                $condition[] = $con;
            }
            else
            {
                $condition[Order::PAY_METHOD] = $payMethod;
            }
        }

        if ($payFinished != "all")
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
        $this->_view->set("c_tel",$condition['tel']);
        $this->_view->set("c_order_id",$condition['order_id']);
        $this->_view->set("c_customer_name",$condition['customer_name']);
        $this->_view->set("c_status",$condition['status']);
        $this->_view->set("c_cs_group_id",$condition['cs_group_id']);
        $this->_view->set("c_cs_id",$condition['cs_id']);
        $this->_view->set("c_time_start",$timeStart);
        $this->_view->set("c_time_end",$timeEnd);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);


        if($condition[Order::STATUS] == 'all')
        {
            unset($condition[Order::STATUS]);
        }
        unset($condition['status_so']);
        unset($condition['group_name']);
        unset($condition['cs']);
        unset($condition['order_time_start']);
        unset($condition['order_time_end']);
        unset($condition['time_start']);
        unset($condition['time_end']);

        if($payFinished == '0')
        {
            $condition = array_filter($condition);
            $condition[Order::PAY_FINISHED] = 0;
        }
        else
        {
            $condition = array_filter($condition);
        }

        log_debug("order query condition:", $condition);
        $data = OrderBusiness::getList( $condition, $paging, $ranking, $outputColumns );
        // order_detail
        $orderIds = [];
        if (count($data) > 0) {
            foreach($data as $item) {
                $orderIds[] = $item[Order::ORDER_ID];
            }
        }
        $orderDetails = [];
        if (count($orderIds) > 0)
        {
            $query = OrderDetail::fetchRows(['*'], [OrderDetail::ORDER_ID => $orderIds]);
            if (count($query) > 0)
            {
                foreach($query as $item)
                {
                    $orderDetails[$item[OrderDetail::ORDER_ID]][] = $item;
                }

            }
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
                Table::COLUMN_FUNCTION   => function(array $row)
                    {
                        $orderID=$row[Order::ORDER_ID];
                        $communityID = $row[Order::COMMUNITY_ID];
                        $mpUserID=$row[Order::MP_USER_ID];
                        $orderID_a=substr($orderID,0,-9);
                        $orderID_b=substr($orderID,-9);
                        $orderID_c=$orderID_a."<br>".$orderID_b;//截取字符串节省空间
                        return "<a target=\"_blank\"   href=\"/mp_admin/order/detail?mp_user_id={$mpUserID}&order_id={$orderID}&community_id={$communityID}\"  >{$orderID_c}</a>";
                    }
            ],
            'customer_name'    => [ Table::COLUMN_TITLE      => '收货人姓名',
                Table::COLUMN_CELL_STYLE => 'width:8%;text-align:center;',   ],

            'detail'           => [ Table::COLUMN_TITLE      => '订单详情',
                Table::COLUMN_CELL_STYLE  => 'width:15%;text-align:center;',
                Table::COLUMN_FUNCTION   => function(array $row) use ($orderDetails)
                    {
                        if(!isset($orderDetails[$row[Order::ORDER_ID]]))
                        {
                            return '';
                        }

                        $communityID = $row[Order::COMMUNITY_ID];
                        $mpUserID=$row[Order::MP_USER_ID];
                        $ret = '<table style="border:0 solid white">';
                        foreach($orderDetails[$row[Order::ORDER_ID]] as $orderDetail)
                        {
                            $orderDetail[OrderDetail::TITLE]= "<a target=\"_blank\"   href=\"/mp_admin/store/product?mp_user_id={$mpUserID}&product_id={$orderDetail[OrderDetail::PRODUCT_ID]}&community_id={$communityID}\"  >{$orderDetail[OrderDetail::TITLE]}</a>";
                            $ret .= sprintf("
                                       <tr style=\"border:0 solid white\">
                                       <td style=\"border-style:hidden;padding-right:0\">%s</td>
                                       </tr>
                                       <tr style=\"border:0 solid white\">
                                       <td style=\"border-style:hidden;\">%.2f&nbsp&nbsp</td>
                                       <td style=\"border-style:hidden;padding-left: 4px\">%d&nbsp&nbsp</td></tr> ",  $orderDetail[OrderDetail::TITLE], $orderDetail[OrderDetail::PRICE], $orderDetail[OrderDetail::COUNT]);
                        }
                        $ret .= "</table>";
                        return $ret;
                    }],
            'status'           => [ Table::COLUMN_TITLE      => '交易状态',
                Table::COLUMN_CELL_STYLE => 'width:10%;text-align:center;', ],
            'total_price'           => [ Table::COLUMN_TITLE      => '总价',
                Table::COLUMN_CELL_STYLE => 'width:7%;text-align:center;', ],
            'total_num'           => [ Table::COLUMN_TITLE      => '总数量',
                Table::COLUMN_CELL_STYLE => 'width:8%;text-align:center;', ],
            Order::PAY_METHOD => [ Table::COLUMN_TITLE => "支付方式" ,
                Table::COLUMN_CELL_STYLE => 'width:5%;text-align:center;',],
            Order::COMMENT => [ Table::COLUMN_TITLE => "备注" ,
                                 Table::COLUMN_CELL_STYLE => 'width:12%;text-align:center;',
                                 Table::COLUMN_FUNCTION => function(array $row)
                                     {
                                       if($row[Order::REASON]== "other")
                                       {
                                             $reason = $row[Order::COMMENT];
                                       }
                                       else
                                       {
                                           $reason = ReasonType::getDisplayName($row[Order::REASON]);
                                       }

                                        if($row[Order::STATUS] == OrderStatus::REJECT)
                                        {
                                            return "拒收原因：<br>".$reason;
                                        }
                                         elseif($row[Order::STATUS] == OrderStatus::REFUND)
                                         {
                                             return "退款退货原因：<br>".$reason;
                                         }
                                        elseif($row[Order::STATUS] == OrderStatus::CLOSED)
                                        {
                                            return "取消原因：<br>".$reason;
                                        }

                                     }],
            Order::PAY_FINISHED => [Table::COLUMN_TITLE => "支付状态",
                Table::COLUMN_CELL_STYLE => 'width:8%;text-align:center;',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        if($row[Order::PAY_FINISHED] == 0)
                        {
                            return "待支付";
                        }
                        else
                        {
                            return "已支付" ;
                        }
                    }]

        ];

        if($checkReadPower)
        {
            $shownColumns['action']  = [Table::COLUMN_TITLE      =>'交易操作',
                Table::COLUMN_CELL_STYLE  => 'width:10%;text-align:center;',
                Table::COLUMN_FUNCTION    => function(array $row) {
                        $orderId=$row[Order::ORDER_ID];
                        $old_status = $row[Order::STATUS];
                        $ret=[
                            new Link('<button class="btn btn-success" style=" margin-bottom:2px;">确认订单</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=verified_to_ship&old_status={$old_status}&check=check',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),

                            new Link('<p style=" margin-bottom:2px;text-align: center">取消</p>', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_status_update?order_id={$orderId}&reject=cancel');"),

                            new Link('<button class="btn btn-success" style=" margin-bottom:2px;">发货</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=dispatched&old_status={$old_status}',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),

                            new Link('<button class="btn btn-success" style=" margin-bottom:2px;">确认收款</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=finished&old_status={$old_status}',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),
                            new Link('<button class="btn btn-danger" style=" margin-bottom:2px;">交易关闭</button>', "javascript:bluefinBH.confirm('确定要修改吗？', function() { javascript:wbtAPI.call('../fcrm/order/order_status_update?order_id={$orderId}&status=closed&old_status={$old_status}',  null, function(){bluefinBH.showInfo('修改成功', function() { location.reload();}); }); })"),
                            new Link('<p style=" margin-bottom:2px;text-align: center">退款/退货</p>', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_status_update?order_id={$orderId}&reject=refund');"),
                            new Link('<p style=" margin-bottom:2px;text-align: center">拒收</p>', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_status_update?order_id={$orderId}&reject=reject');"),];
                        if($row[Order::STATUS] == OrderStatus::SUBMITTED_TO_PAY)
                        {
                            return $ret[0].$ret[1];
                        }
                        if($row[Order::STATUS] == OrderStatus::VERIFIED_TO_SHIP)
                        {
                            if($row[Order::PAY_METHOD] == PayMethod::CASH_PAY)
                            {
                                return $ret[2].$ret[1];
                            }
                            else
                            {

                                if($row[Order::PAY_FINISHED] == 0)
                                {
                                    return $ret[2].$ret[1];
                                }
                                else
                                {
                                    return $ret[2].$ret[1].$ret[5];
                                }
                            }

                        }
                        if($row[Order::STATUS] == OrderStatus::DISPATCHED)
                        {
                            if($row[Order::PAY_METHOD] == PayMethod::CASH_PAY)
                            {
                                return $ret[3].$ret[6];
                            }
                            else
                            {
                                return $ret[3].$ret[5];
                            }

                        }

                    }, ];
            $shownColumns['change'] = [ Table::COLUMN_TITLE      =>'操作',
                Table::COLUMN_CELL_STYLE  => 'width:15%',
                Table::COLUMN_FUNCTION =>function(array $row)use($power)
                    {
                        $old_status=$row[Order::STATUS];
                        $order_id=$row[Order::ORDER_ID];
                        $community_id = $row[Order::COMMUNITY_ID];
                        $mp_user_id=$row[Order::MP_USER_ID];
                        $print="<a href=\"/mp_admin/order/print?mp_user_id={$mp_user_id}&order_id={$order_id}&community_id={$community_id}\"  target=\"_blank\" >打印</a>";
                        $link= new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/order_dialog/order_update?order_id={$order_id}&old_status={$old_status}&community_id={$community_id}',function() { reloadPage();});");
                        return $link."<br>".$print;
                    }   ];
        }


        $table               = Table::fromDbData( $data, $outputColumns, Order::ORDER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = FALSE;
        $this->_view->set( 'table', $table );
    }

}

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
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\WxPayRecord;

class OrderBusiness extends BaseBusiness
{
    public static function getList( array $condition, &$paging, $ranking,
                                    array $outputColumns = null )
    {
        return Order::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getListInform( array $condition, &$paging, $ranking,
                                          array $outputColumns = null )
    {
        return Order::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    public static function getListDetail( array $condition, &$paging, $ranking,
                                          array $outputColumns = null )
    {
        return OrderDetail::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    public static function getListChangeLog( array $condition, &$paging, $ranking,
                                          array $outputColumns = null )
    {
        return OrderChangeLog::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function orderUpdate( $id, $data_status )
    {
        $obj = new Order([ Order::ORDER_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Order($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $data_status=explode("|",$data_status);
        $change_time='';
        try {
            if($obj->setStatus($data_status[1])->update())
            {
                $change_time=date('Y-m-d H:i:s');
                if($data_status[1] == 'finished')
                {
                    $obj->setFinishTime($change_time)->setPayFinished(1)->update();
                    $communityID = $obj -> getCommunityID();
                    $wxUserID = $obj->getWxUserID();
                    $mpUserID = $obj->getMpUserID();
                    $orderDetail = new OrderDetail([OrderDetail::ORDER_ID => $id]);
                    $productID = $orderDetail->getProductID();
                    $product = new Product([Product::PRODUCT_ID => $productID]);
                    $storeID = $product->getStoreID();
                    $wxUser = new WxUser([WxUser::WX_USER_ID=> $wxUserID]);
                    $nick = $wxUser->getNick();
                    $host =  ConfigBusiness::getHost();//获取主机名
                    log_debug("[id:$id][status:$data_status[1][community-id:$communityID][wxUserID:$wxUserID][mpUserID:$mpUserID][nick:$nick][host:$host][storeID:$storeID][pID:$productID]");
                    $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
                    $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_ORDER_NOTIFY_ID];//通知模板id
                    $storeType = $obj->getStoreType();
                    $url = sprintf("%s/wx_user/order/comment?wx_user_id=%s&mp_user_id=%s&community_id=%s&store_id=%s&order_id=%s&store_type=%s",$host,$wxUserID,$mpUserID,$communityID,$storeID,$id,$storeType);


                    $remark = "\\n欢迎点击“详情”进行评价";
                    $template = array( 'touser' => $wxUserID,
                        'template_id' => "$templateID",
                        'url' => $url,
                        'topcolor' => "#62c462",
                        'data'   => array('first' => array('value' => urlencode("尊敬的：".$nick),
                            'color' =>"#222", ),
                            'OrderSn' => array('value' => urlencode($id),'color' =>"#222", ),
                            'OrderStatus' => array('value' => urlencode(OrderStatus::getDisplayName($data_status[1])),'color' =>"#222", ),
                            'remark' => array('value' => urlencode("$remark") ,'color' =>"#222" ,))
                    );
                    WxApiBusiness::sentTemplateMessage($mpUserID,$template);
                    $wxPayRecord = new WxPayRecord([WxPayRecord::ORDER_ID => $id]);
                    if($wxPayRecord->isEmpty())
                    {
                        $wxPayRecord = new WxPayRecord();
                        $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUser->getNick())->setPayFinished(1)->setPayValue($obj->getTotalPrice())->setPayMethod($obj->getPayMethod())->setPayIterm("商城支付")->setOrderID($id)->setPayStartDate($obj->getCreateTime())->setPayEndDate(time())->insert();

                    }
                }
            }
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        $change_log = new OrderChangeLog();
        $data=[OrderChangeLog::ORDER_ID=>$id,OrderChangeLog::OPERATOR=>UserBusiness::getLoginUsername(),
               OrderChangeLog::COMMENT=>'无',OrderChangeLog::CHANGE_TIME=>$change_time,
               OrderChangeLog::STATUS_BEFORE=>$data_status[0],OrderChangeLog::STATUS_AFTER=>$data_status[1],
        ];
        $change_log->apply( $data );
        try {
            $change_log->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }
        return [ 'errno' => 0 ];
    }

    public static function orderUpdateComment($id,$reject, $comment ,$reason)
    {
        $obj = new Order([ Order::ORDER_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Order($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        if($reject == "cancel")
        {
            $obj->setComment($comment)->setStatus(OrderStatus::CLOSED)->setReason($reason)->update();
        }
        elseif($reject == "refund")
        {
            $obj->setComment($comment)->setStatus(OrderStatus::REFUND)->setReason($reason)->update();
        }
        elseif($reject == "reject")
        {
            $obj->setComment($comment)->setStatus(OrderStatus::REJECT)->setReason($reason)->update();
        }




        return [ 'errno' => 0 ];
    }

    public static function OrderStatusUpdate( $id, $status,$check )
    {
        $status=explode("|",$status);
        $obj = new Order([ Order::ORDER_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Order($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $change_time='';
        $communityID = $obj -> getCommunityID();
        $wxUserID = $obj->getWxUserID();
        $mpUserID = $obj->getMpUserID();
        $orderDetail = new OrderDetail([OrderDetail::ORDER_ID => $id]);
        $productID = $orderDetail->getProductID();
        $product = new Product([Product::PRODUCT_ID => $productID]);
        $storeID = $product->getStoreID();
        $wxUser = new WxUser([WxUser::WX_USER_ID=> $wxUserID]);
        $nick = $wxUser->getNick();
        $host =  ConfigBusiness::getHost();//获取主机名
        log_debug("[id:$id][status:$status[0][community-id:$communityID][wxUserID:$wxUserID][mpUserID:$mpUserID][nick:$nick][host:$host][storeID:$storeID][pID:$productID]");
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_ORDER_NOTIFY_ID];//通知模板id
        if($check == "check")
        {

            if($obj->getPayMethod() == "cash_pay")
            {
                $remark = "\\n你的订单已经确认,请耐心等待";
                $url = "";
            }
            else
            {
                $remark = "\\n你的订单已经确认,点击进入微信支付";
                $url = sprintf("%s/wx_user/pay/store?order_id=%s&total_fee=%s&mp_user_id=%s&community_id=%s&store_id=%s&wx_user_id=%s&pay_method=%s",$host,$id,$obj->getTotalPrice(),$mpUserID,$communityID,$storeID,$wxUserID,$obj->getPayMethod());
            }

            $template = array( 'touser' => $wxUserID,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array('first' => array('value' => urlencode("尊敬的：".$nick),
                    'color' =>"#222", ),
                    'OrderSn' => array('value' => urlencode($id),'color' =>"#222", ),
                    'OrderStatus' => array('value' => urlencode('已确认'),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("$remark") ,'color' =>"#222" ,))
            );
            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }
        //向用户发送确认信息
        try {
            if($obj->setStatus($status[0])->update())
            {
                $change_time=date('Y-m-d H:i:s');
                if($status[0] == 'finished')
                {
                    $obj->setFinishTime($change_time)->setPayFinished(1)->update();
                    $storeType = $obj->getStoreType();
                    $url = sprintf("%s/wx_user/order/comment?wx_user_id=%s&mp_user_id=%s&community_id=%s&store_id=%s&order_id=%s&store_type=%s",$host,$wxUserID,$mpUserID,$communityID,$storeID,$id,$storeType);
                    $remark = "\\n欢迎点击“详情”进行评价";
                    $template = array( 'touser' => $wxUserID,
                                       'template_id' => "$templateID",
                                       'url' => $url,
                                       'topcolor' => "#62c462",
                                       'data'   => array('first' => array('value' => urlencode("尊敬的：".$nick),
                                                                          'color' =>"#222", ),
                                                         'OrderSn' => array('value' => urlencode($id),'color' =>"#222", ),
                                                         'OrderStatus' => array('value' => urlencode(OrderStatus::getDisplayName($status[0])),'color' =>"#222", ),
                                                         'remark' => array('value' => urlencode("$remark") ,'color' =>"#222" ,))
                    );
                    WxApiBusiness::sentTemplateMessage($mpUserID,$template);
                    $wxPayRecord = new WxPayRecord([WxPayRecord::ORDER_ID => $id]);
                    if($wxPayRecord->isEmpty())
                    {
                        $wxPayRecord = new WxPayRecord();
                        $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUser->getNick())->setPayFinished(1)->setPayValue($obj->getTotalPrice())->setPayMethod($obj->getPayMethod())->setPayIterm("商城支付")->setOrderID($id)->setPayStartDate($obj->getCreateTime())->setPayEndDate(time())->insert();

                    }
                }
            }
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }
        $change_log = new OrderChangeLog();
        $data=[OrderChangeLog::ORDER_ID=>$id,OrderChangeLog::OPERATOR=>UserBusiness::getLoginUsername(),
               OrderChangeLog::COMMENT=>'无',OrderChangeLog::CHANGE_TIME=>$change_time,
               OrderChangeLog::STATUS_AFTER=>$status[0],
               OrderChangeLog::STATUS_BEFORE=>$status[1],];
        $change_log->apply( $data );
        try {
            $change_log->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }
        return [ 'errno' => 0 ];
    }

    public static function changeOrder($orderId, $productId, $action)
    {
        $order = new Order([Order::ORDER_ID => $orderId]);
        if ($order->isEmpty() || ($order->getStatus() != OrderStatus::SUBMITTED_TO_PAY)) {
            return ['errno' => 1, 'error' => '找不到订单，或订单已锁定'];
        }

        $orderDetail = new OrderDetail([ OrderDetail::ORDER_ID => $orderId,
                                         OrderDetail::PRODUCT_ID => $productId ]);
        /**
         *          +           -
         * 空      new        success
         *
         * 非空     +1        =1  delete
         *                   >1  -1
         */
        try {
            if ($orderDetail->isEmpty()) {
                if ($action == 1) {
                    $orderDetail->setOrderID($orderId)
                        ->setProductID($productId)
                        ->setCount(1)
                        ->insert();
                    return ['errno' => 0, 'error' => '订单新增了一个产品'];
                } elseif ($action == -1) {
                    return ['errno' => 0, 'error' => '未执行任何操作'];
                }
            } else {
                $count = $orderDetail->getCount();
                if ($action == 1) {
                    $orderDetail->setCount($count + 1)->update();
                    return ['errno' => 0, 'error' => '已有产品数量增 1'];
                } elseif ($action == -1) {
                    $count -= 1;
                    if ($count < 1) {
                        $orderDetail->delete();
                        return ['errno' => 0, 'error' => '从订单删除了一个产品'];
                    } else {
                        $orderDetail->setCount($count)->update();
                        return ['errno' => 0, 'error' => '已有产品数量减 1'];
                    }
                }
            }
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0, 'error' => '意外的返回结果'];
    }

    public static function getNewOrder($wxUserId, $storeId)
    {
        $condition = [ Order::WX_USER_ID => $wxUserId,
                       Order::STATUS     => OrderStatus::SUBMITTED_TO_PAY, ];
        $ranking = [ Order::CREATE_TIME => true ];
        $query = Order::fetchRows([Order::ORDER_ID], $condition, null, $ranking);
        $latest = array_shift($query);
        // 同时只能有一个最新订单，否则除最近的那个，其余全部逻辑删除
        if (count($query) > 0) {
            foreach($query as $order) {
                $obj = new Order([Order::ORDER_ID => $order[Order::ORDER_ID]]);
                if (!$obj->isEmpty()) {
                    $obj->delete();
                    log_info('逻辑删除冗余的新订单：' . $order[Order::ORDER_ID]);
                }
            }
        }
        if (!empty($latest))
        {
            return $latest[Order::ORDER_ID];
        }
        else
        {
            $store = new Store([Store::STORE_ID => $storeId]);
            $order = new Order();
            $order->setWxUserID( $wxUserId )
                ->setCreateTime(time())
                ->setOrderID( self::genNewOrderId() )
                ->setMpUserID($store->getMpUserID())
                ->setStatus( OrderStatus::SUBMITTED_TO_PAY )
                ->insert();
            log_info('新建了一个订单：' . $order->getOrderID());
            return $order->getOrderID();
        }
    }

    public static function changeCart($cartId, $productId, $action)
    {
        $cart = new Cart([Cart::CART_ID => $cartId]);
        if ($cart->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到购物车，或创建购物车失败'];
        }

        $cartDetail = new CartDetail([ CartDetail::CART_ID => $cartId,
                                       CartDetail::PRODUCT_ID => $productId ]);
        /**
         *          +           -
         * 空      new        success
         *
         * 非空     +1        =1  delete
         *                   >1  -1
         */
        try {
            if ($cartDetail->isEmpty()) {
                if ($action == 1) {
                    $cartDetail->setCartID($cartId)
                        ->setProductID($productId)
                        ->setCount(1)
                        ->insert();
                    return ['errno' => 0, 'error' => '购物车新增了一个产品'];
                } elseif ($action == -1) {
                    return ['errno' => 0, 'error' => '未执行任何操作'];
                }
            } else {
                $count = $cartDetail->getCount();
                if ($action == 1) {
                    $cartDetail->setCount($count + 1)->update();
                    return ['errno' => 0, 'error' => '已有产品数量增 1'];
                } elseif ($action == -1) {
                    $count -= 1;
                    if ($count < 1) {
                        $cartDetail->delete();
                        return ['errno' => 0, 'error' => '从购物车删除了一个产品'];
                    } else {
                        $cartDetail->setCount($count)->update();
                        return ['errno' => 0, 'error' => '已有产品数量减 1'];
                    }
                }
            }
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }

        return ['errno' => 0, 'error' => '意外的返回结果'];
    }

    public static function getCartId($wxUserId, $storeId)
    {
        $condition = [ Cart::WX_USER_ID => $wxUserId,
                       Cart::STORE_ID   => $storeId, ];
        $ranking = [ Cart::_CREATED_AT => true ];
        $query = Cart::fetchRows([Cart::CART_ID], $condition, null, $ranking);
        $latest = array_shift($query);
        // 同时只能有一个最新订单，否则除最近的那个，其余全部逻辑删除
        if (count($query) > 0)
        {
            foreach($query as $cart)
            {
                $obj = new Cart([Cart::CART_ID => $cart[Cart::CART_ID]]);
                if (!$obj->isEmpty())
                {
                    $obj->delete();
                    log_info('逻辑删除冗余的购物车：' . $cart[Cart::CART_ID]);
                }
            }
        }

        if (!empty($latest))
        {
            return $latest[Cart::CART_ID];
        }
        else
        {
            $store = new Store([Store::STORE_ID => $storeId]);
            $cart = new Cart();
            $cart->setWxUserID( $wxUserId )
                ->setStoreID( $storeId )
                ->setMpUserID($store->getMpUserID())
                ->insert(true);
            log_info('新建了一个购物车：' . $cart->getCartID());
            return $cart->getCartID();
        }
    }

    public static function genNewOrderId()
    {
        $id = '';
        while (1)
        {
            $id  = date('YmdHis') . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $obj = new Order([ Order::ORDER_ID => $id ]);
            if ($obj->isEmpty())
            {
                break;
            }
        }

        return $id;
    }

    public static function getCartData($wxUserId, $storeId)
    {
        $ret = [];

        $cartId = self::getCartId($wxUserId, $storeId);
        $cart   = new Cart([Cart::CART_ID => $cartId]);
        if ($cart->isEmpty()) return $ret;

        $ret[Cart::CART_ID] = $cartId;

        $data  = [ ];
        $query = CartDetail::fetchRows(['*'], [CartDetail::CART_ID => $cartId]);
        $productIds = [ ];
        if (count($query) > 0)
        {
            foreach($query as $cartDetail)
            {
                $data[$cartDetail[CartDetail::PRODUCT_ID]] =
                    [
                    'id'    => $cartDetail[CartDetail::PRODUCT_ID],//此id为购物车产品id
                    'name'  => 'undefined',
                    'price' => 0,
                    'num'   => $cartDetail[CartDetail::COUNT],
                    ];

                $productIds[] = $cartDetail[CartDetail::PRODUCT_ID];
            }
        }
        $products = [];
        if (count($productIds) > 0)
        {
            $query = Product::fetchRows(['*'],
                                        [
                                        Product::PRODUCT_ID => $productIds,
                                        Product::IS_ON_SHELF =>1,
                                        Product::IS_DELETE => 0,
                                        ]);

            if (count($query) > 0)
            {
                foreach($query as $item)
                {
                    $products[$item[Product::PRODUCT_ID]] = $item;
                }
            }
        }

        foreach($data as $key => $item)
        {
            if (array_key_exists($key, $products))
            {
                $data[$key]['name']  = $products[$key][Product::TITLE];
                $data[$key]['price'] = $products[$key][Product::PRICE];
            }
            else
            {
                // 从购物车中移除无效的商品
                unset($data[$key]);
            }
        }

        $ret['data'] = $data;
        return $ret;
    }


    public static function getSelectByTimeCondition($timeStart, $timeEnd)
    {
        $yearE = substr($timeEnd,0,4);
        $monthE = substr($timeEnd,4,2);
        $dayE = substr($timeEnd,6,2);
        $yearS = substr($timeStart,0,4);
        $monthS = substr($timeStart,4,2);
        $dayS = substr($timeStart,6,2);

        $newTimeStart = $yearS . "-" . $monthS . "-" . $dayS . " " . "00:00:00";
        $newTimeEnd = $yearE . "-" . $monthE . "-" . $dayE . " " . "23:59:59";
        $exprWx = sprintf("`finish_time` >= '%s' and `finish_time` <= '%s'",$newTimeStart,$newTimeEnd);

        $con = new \Bluefin\Data\DbCondition($exprWx);
        return $con;
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
    /**
     * 生成orderID
     * @return int
     */
    public static function generateOrderID()
    {
        $orderID = date('YmdHis');

        $rand1 = rand(0, 9);
        $rand2 = rand(0, 9);
        $rand3 = rand(0, 9);
        $rand4 = rand(0, 9);

        $orderID = $orderID . $rand1 . $rand2 . $rand3 . $rand4;
        return intval($orderID);
    }
}

<?php

namespace WBT\Controller\App;

use MP\Model\Mp\Order;
use MP\Model\Mp\OrderDetail;
use MP\Model\Mp\OrderStatus;
use MP\Model\Mp\Product;
use MP\Model\Mp\ProductComment;
use MP\Model\Mp\WxUser;
use WBT\Controller\WxUserControllerBase;
use Common\Helper\BaseController;
use MP\Model\Mp\AppUser;
class OrderController extends BaseController
{
    public function historyAction()
    {
        $phone = $this->_request->get( 'phone' );
        $condition = [ Order::WX_USER_ID  => $phone];
        $ranking = [Order::CREATE_TIME => true]; // 按时间逆序排
        $grouping = null;
        $orders = Order::fetchRowsWithCount(['*'], $condition, $grouping, $ranking);

        foreach($orders as $key => $order)
        {
            $order['status'] = OrderStatus::getDisplayName($order['status']);
            $orders[$key] = $order;
        }
        $this->_view->set( 'orders', $orders );

        $orderIds = [];
        foreach($orders as $key => $order) {
            $orderIds[] = $order[Order::ORDER_ID];
        }
        $orderDetailQuery = OrderDetail::fetchRows(['*'], [OrderDetail::ORDER_ID => $orderIds]);
        $orderDetails = [];
        $productIds = [];
        foreach($orderDetailQuery as $orderDetail) {
            $orderDetails[$orderDetail[OrderDetail::ORDER_ID]][] = $orderDetail;
            $productIds[] = $orderDetail[OrderDetail::PRODUCT_ID];
        }
        $this->_view->set('order_details', $orderDetails);

        $productQuery = Product::fetchRows(['*'], [Product::PRODUCT_ID => $productIds]);
        $products = [];
        foreach($productQuery as $product) {
            $products[$product[Product::PRODUCT_ID]] = $product;
        }
        $this->_view->set('products', $products);
    }
    //我的订单列表
    public function personAction()
    {
        $phone = $this->_request->get( 'phone' );
        $condition = [ Order::WX_USER_ID  => $phone];
        $mpUserID = $this->_request->get( 'mp_user_id' );
        //$mpUserID = $this->_mpUserID;
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $this->_view->set("phone",$phone);
        $this->_view->set("mp_user_id",$mpUserID);
log_debug("--------------------------[w:$phone][m:$mpUserID]----------------------------------------");
        $ranking = [Order::CREATE_TIME => true]; // 按时间逆序排
        $grouping = null;
        $orders = Order::fetchRowsWithCount(['*'], $condition, $grouping, $ranking);

        foreach($orders as $key => $order)
        {
            $createTimes[] = $order[Order::CREATE_TIME];
            $order['status'] = OrderStatus::getDisplayName($order['status']);
            $order['create_time'] = substr( $order['create_time'],0,10);
            $orders[$key] = $order;
        }
        $this->_view->set( 'orders', $orders );
        $orderIds = [];
        $createTimes = [];
        foreach($orders as $key => $order) {
            $orderIds[] = $order[Order::ORDER_ID];
        }
        $orderDetailQuery = OrderDetail::fetchRows(['*'], [OrderDetail::ORDER_ID => $orderIds,Order::MP_USER_ID=> $mpUserID]);
        $orderDetails = [];
        $productIds = [];
        foreach($orderDetailQuery as $orderDetail) {
            $orderDetails[$orderDetail[OrderDetail::ORDER_ID]][] = $orderDetail;
            $productIds[] = $orderDetail[OrderDetail::PRODUCT_ID];
        }
        $this->_view->set('order_details', $orderDetails);

        $productQuery = Product::fetchRows(['*'], [Product::PRODUCT_ID => $productIds]);
        $products = [];
        foreach($productQuery as $product) {
            $products[$product[Product::PRODUCT_ID]] = $product;
        }
        $this->_view->set('products', $products);
        $this->_view->set('community_id', $productQuery[0][Product::COMMUNITY_ID]);//没有添加小区ID
        $this->_view->set("store_id",$productQuery[0][Product::STORE_ID]);
//获取商品评论
        $commentData = ProductComment::fetchRows(['*'], [ProductComment::PRODUCT_ID => $productIds,ProductComment::WX_USER_ID => $phone,ProductComment::ORDER_FINISH_TIME => $createTimes,ProductComment::MP_USER_ID => $mpUserID]);
        $comments = [];
        foreach($commentData as $comment)
        {
            $comments[$comment[ProductComment::ORDER_ID]] = $comment;
        }
        $this->_view->set('comments', $comments);
    }

    //订单详细
    public function detailAction()
    {
        $orderID = $this->_request->get( 'order_id' );
        $phone = $this->_request->get( 'phone' );
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $communityID = $this->_request->get( 'community_id' );
        $storeID = $this->_request->get( 'store_id' );

        $this->_view->set("community_id",$communityID);
        $this->_view->set("phone",$phone);
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("order_id",$orderID);
        $this->_view->set("store_id",$storeID);

        $order = new Order([Order::ORDER_ID =>$orderID]);
        $createTime = $order->getCreateTime();
        $status = $order->getStatus();
        $status = OrderStatus::getDisplayName($status);
        $payMethod = $order->getPayMethod();
        $this->_view->set("create_time",$createTime);
        $this->_view->set("pay_method",$payMethod);
        $this->_view->set("status",$status);


        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $nick = $appUser->getNick();
        $phone = $appUser->getPhone();
        $address = $appUser->getAddress();
        $this->_view->set("nick",$nick);
        $this->_view->set("phone",$phone);
        $this->_view->set("address",$address);

        $orderDetailQuery = OrderDetail::fetchRows(['*'], [OrderDetail::ORDER_ID => $orderID,Order::MP_USER_ID=>$mpUserID]);
        $productIds = [];
        $orderDetailNum = [];
        $totalNum = 0;
        $totalMoney = 0.00;
        foreach($orderDetailQuery as $orderDetail) {

            $productIds[] = $orderDetail[OrderDetail::PRODUCT_ID];
            $orderDetailNum[$orderDetail[OrderDetail::PRODUCT_ID]] = $orderDetail;
            $num = $orderDetail[OrderDetail::COUNT];
            $price = $orderDetail[OrderDetail::PRICE];
            $totalNum = $totalNum + $num;
            $priceA = number_format($num * $price,2,'.','');
            $totalMoney = number_format($totalMoney + $priceA,2,'.','');
        }
        $this->_view->set("total_money",$totalMoney);
        $this->_view->set("total_num",$totalNum);
        $this->_view->set('orderDetailNum', $orderDetailNum);

        $productData = Product::fetchRows(['*'], [ProductComment::PRODUCT_ID => $productIds]);
        $this->_view->set('productData', $productData);

        //获取商品评论
        $commentData = ProductComment::fetchRows(['*'], [ProductComment::PRODUCT_ID => $productIds,ProductComment::WX_USER_ID => $phone,ProductComment::ORDER_ID => $orderID,ProductComment::MP_USER_ID => $mpUserID]);
        $comments = [];
        foreach($commentData as $comment)
        {
            $comments[$comment[ProductComment::ORDER_ID]] = $comment;
        }
        $this->_view->set('comments', $comments);


    }

    //评价      星级最小是   1
    public function commentAction()
    {
        $orderID = $this->_request->get( 'order_id' );
        $phone = $this->_request->get( 'phone' );
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $communityID = $this->_request->get( 'community_id' );
        $storeID = $this->_request->get( 'store_id' );

        $this->_view->set("community_id",$communityID);
        $this->_view->set("phone",$phone);
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("order_id",$orderID);
        $this->_view->set("store_id",$storeID);

        $orderDetailQuery = OrderDetail::fetchRows(['*'], [OrderDetail::ORDER_ID => $orderID,Order::MP_USER_ID =>$mpUserID]);
        $productIds = [];
        foreach($orderDetailQuery as $orderDetail) {
            $productIds[] = $orderDetail[OrderDetail::PRODUCT_ID];
        }
        //获取订单号对应的商品
        $productData = Product::fetchRows(['*'], [ProductComment::PRODUCT_ID => $productIds]);
        $this->_view->set('productData', $productData);
        $count = count($productData);
        $this->_view->set("cishu",$count);

        $productCommentData = ProductComment::fetchColumn(ProductComment::PRODUCT_COMMENT_ID);
        $maxPCID = end($productCommentData);
        $this->_view->set("max_pcid",$maxPCID);
    }
    //评价成功
    public function commentsAction()
    {
        $phone = $this->_request->get( 'phone' );
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $communityID = $this->_request->get( 'community_id' );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("phone",$phone);
        $this->_view->set("community_id",$communityID);
        $this->_view->set("store_id",$storeID);

    }
}
<?php

use Bluefin\Service;
use MP\Model\Mp\Product;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\Order;
use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use MP\Model\Mp\OrderStatus;
use MP\Model\Mp\OrderDetail;
use WBT\Business\Weixin\OrderBusiness;
use MP\Model\Mp\Store;
use MP\Model\Mp\OrderChangeLog;
use MP\Model\Mp\HouseMember;
use Bluefin\App;
use MP\Model\Mp\ProductComment;

// 普通用户点菜相关 api
class OrderService extends Service
{
    public function changeOrder()
    {
        $fields = [ 'wx_user_id', 'store_id', 'product_id', 'action' ];
        $data   = $this->_app->request()->getArray( $fields );

        if (empty($data['store_id'])) {
            return ['errno' => 1, 'error' => '非法操作，缺少商店ID'];
        }

        if (empty($data['wx_user_id'])) {
            return ['errno' => 0, 'error' => '与用户无关的演示操作'];
        } else {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $data['wx_user_id']]);
            if ($wxUser->isEmpty()) {
                return ['errno' => 0, 'error' => '找不到用户，视为演示操作'];
            }
        }

        $product = new Product([Product::PRODUCT_ID => $data['product_id']]);
        if ($product->isEmpty()) {
            return ['errno' => 1, 'error' => '找不到商品或商品已下架'];
        }

        $newOrderId = OrderBusiness::getNewOrder($data['wx_user_id'], $data['store_id']);
        return OrderBusiness::changeOrder($newOrderId, $data['product_id'], $data['action']);
    }

    public function checkout()
    {
        $storeId  = $this->_app->request()->getPostParam( Cart::STORE_ID );
        $wxUserId = $this->_app->request()->getPostParam( Cart::WX_USER_ID );
        $name     = $this->_app->request()->getPostParam( 'name' );
        $tel      = $this->_app->request()->getPostParam( Order::TEL );
        $address  = $this->_app->request()->getPostParam( Order::ADDRESS );
        $store = new Store([Store::STORE_ID => $storeId]);
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        if(!$wxUser->isEmpty())
        {
            $wxUser->setNick($name)->setAddress($address)->setPhone($tel)->setCurrentCommunityID($store->getCommunityID())->update();
        }
        $payMethod = $this->_app->request()->getPostParam( "pay_method");

        $cartData = OrderBusiness::getCartData($wxUserId, $storeId);
        $totalPrice = 0;
        $totalNum = 0;
        $productIds = [];
        if (count($cartData['data']) > 0)
        {
            foreach ($cartData['data'] as $item)
            {
                $totalPrice += $item['price'] * $item['num'];
                $totalNum +=  $item['num'];
                $productIds[] = $item['id'];//购物车详情 产品id
            }
        }
        else
        {
            return [ 'errno' => 1, 'error' => '购物车为空或订单已提交，请返回商城重新下单，谢谢' ];
        }
        $products = [];
        if(count($productIds) > 0) {
            $query = Product::fetchRows(['*'], [Product::PRODUCT_ID => $productIds]);
            if (count($query) > 0) {
                foreach($query as $item) {
                    $products[$item[Product::PRODUCT_ID]] = $item;
                }
            }
        }
        // todo: 判断商品是否都在售


        $hm = new HouseMember([HouseMember::WX_USER_ID => $wxUserId]);
        $csID = $hm->getCurrentCsID();
        $csGroupID = $hm->getCurrentCsGroupID();
        $status = "submitted_to_pay";
        $order = new Order();
        $order->setOrderID(OrderBusiness::genNewOrderId())
            ->setMpUserID($store->getMpUserID())->setCommunityID($store->getCommunityID())
            ->setWxUserID($wxUserId)
            ->setStatus($status)
            ->setCustomerName($name)
            ->setCreateTime(time())
            ->setTel($tel)
            ->setAddress($address)->setTotalNum($totalNum)
            ->setTotalPrice($totalPrice)->setCsID($csID)->setCsGroupID($csGroupID)->setPayMethod($payMethod)->setStoreType("restaurant")
            ->insert();

        if (count($cartData['data']) > 0)
        {
            foreach($cartData['data'] as $item)
            {
                $orderDetail = new OrderDetail();
                $orderDetail->setOrderID($order->getOrderID())
                    ->setProductID($item['id'])
                    ->setImgUrl($products[$item['id']][Product::IMG_URL])
                    ->setPrice($products[$item['id']][Product::PRICE])->setTitle($products[$item['id']][Product::TITLE])
                    ->setDescription($products[$item['id']][Product::DESCRIPTION])
                    ->setCount($item['num'])
                    ->insert();
            }
        }

        $orderChangeLog = new OrderChangeLog();
        $orderChangeLog->setOrderID($order->getOrderID())
            ->setStatusBefore(OrderStatus::DEFAULT_STATUS)
            ->setStatusAfter(OrderStatus::SUBMITTED_TO_PAY)
            ->setOperator('auto')
            ->setChangeTime(date('Y-m-d H:i:s'))
            ->setComment('无')
            ->insert();

        $cartDetail = new CartDetail();
        $cartDetail->delete([CartDetail::CART_ID => $cartData['cart_id']]);

        $cart = new Cart();
        $cart->delete([Cart::CART_ID => $cartData['cart_id']]);
        $orderID = $order->getOrderID();
        $mpUserID = $store->getMpUserID();

        return [ 'errno' => 0, 'error' => '提交成功,客服会与您电话联系确定订单！' ,"order_id" =>$orderID,"pay_method" => $payMethod,
            "mp_user_id" => $mpUserID];
    }

    public function changeCart()
    {
        $fields = [ 'wx_user_id', 'store_id', 'product_id', 'action', 'mp_user_id'];
        $data   = $this->_app->request()->getArray( $fields );
        $data['wx_user_id'] = \WBT\Business\Weixin\WxUserBusiness::getCookieWxUserID($data['mp_user_id']);
        if (empty($data['store_id'])) {
            return ['errno' => 1, 'error' => '非法操作，缺少商店ID'];
        }

        if (empty($data['wx_user_id'])) {
            return ['errno' => 1, 'error' => '非法操作，缺少WX_USER_ID'];
        } else {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $data['wx_user_id']]);
            if ($wxUser->isEmpty()) {
                return ['errno' => 0, 'error' => '找不到用户，视为演示操作'];
            }
        }

        $product = new Product([Product::PRODUCT_ID => $data['product_id']]);
        if ($product->isEmpty() )
        {
            return ['errno' => 1, 'error' => '找不到商品或商品已下架'];
        }

        //$newOrderId = OrderBusiness::getNewOrder($data['wx_user_id'], $data['store_id']);
        //return OrderBusiness::changeOrder($newOrderId, $data['product_id'], $data['action']);
        $cartId = OrderBusiness::getCartId($data['wx_user_id'], $data['store_id']);
        return OrderBusiness::changeCart($cartId, $data['product_id'], $data['action']);
    }

    public function addComment()
    {
        $res = ['errno' => 0];
        $stars = App::getInstance()->request()->get('stars');
        $textareas = App::getInstance()->request()->get('textareas');
        $pids = App::getInstance()->request()->get('pids');
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $communityID = App::getInstance()->request()->get('community_id');
        $orderID = App::getInstance()->request()->get('order_id');
        $maxPCID = App::getInstance()->request()->get('max_pcid');
        $commentTime = date("Y-m-d H:m:s");

        $arrS = explode('_',$stars);
        $arrT = explode('_',$textareas);
        $arrP = explode('_',$pids);
        unset($arrS[0]);
        unset($arrT[0]);
        unset($arrP[0]);
        $count = count($arrS);
        log_debug("-----------------------[$mpUserID][$wxUserID][$communityID][$orderID][$commentTime][$stars][$textareas][$pids][$maxPCID]--------------------");
        for($i=1;$i<=$count;$i++)
        {
            log_debug("------------------------------------ok0------------------------------------------");
            $star = $arrS[$i];
            $textarea = $arrT[$i];
            $pid = $arrP[$i];
            $product = new Product([Product::PRODUCT_ID => $pid]);
            $title = $time = '';
            if(!$product->isEmpty())
            {
                $title = $product->getTitle();
            }
            $order = new Order([Order::ORDER_ID => $orderID]);
            if(!$order->isEmpty())
            {
                $time = $order->getCreateTime();
            }
            $productComment = new ProductComment();//此处未加小区id
                log_debug("------------------------------------ok8------------------------------------------");
                $maxPCID = $maxPCID + 1;
                $productComment -> setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setProductID($pid)->setCommentLevel($star)->setComment($textarea)->setProductTitle($title)->setOrderFinishTime($time)->setCommentTime($commentTime)->setProductCommentID($maxPCID)->setOrderID($orderID)->setNick($wxUser->getNick())->setHeadPic($wxUser->getHeadPic())->insert();
                log_debug("------------------------------------ok9------------------------------------------");
        }
        return $res;
    }


    public function cancel()
    {
        $fields = [ 'wx_user_id', 'order_id', 'cancel', 'other'];
        $data   = $this->_app->request()->getArray( $fields );

        $order =  new Order([Order::ORDER_ID => $data['order_id']]);

        if ($order->isEmpty() )
        {
            return ['errno' => 1, 'error' => '找不到订单'];
        }
        else
        {
            $order->setStatus(OrderStatus::CLOSED)->setComment($data['other'])->setReason($data['cancel'])->update();
            return ['errno' => 0];
        }
    }

    public function refund()
    {
        $fields = [ 'wx_user_id', 'order_id', 'cancel', 'other'];
        $data   = $this->_app->request()->getArray( $fields );

        $order =  new Order([Order::ORDER_ID => $data['order_id']]);

        if ($order->isEmpty() )
        {
            return ['errno' => 1, 'error' => '找不到订单'];
        }
        else
        {
            $order->setStatus(OrderStatus::REFUND)->setComment($data['other'])->setReason($data['cancel'])->update();
            return ['errno' => 0];
        }
    }
}






























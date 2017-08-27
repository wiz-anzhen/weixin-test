<?php
/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-8-13
 * Time: 下午4:24
 */
use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\HouseMemberType;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\CsChatRecord;
use MP\Model\Mp\ReocrdContentType;
use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use WBT\Business\Weixin\CartDetailBusiness;
use WBT\Business\Weixin\OrderBusiness;
use MP\Model\Mp\Order;
use MP\Model\Mp\Product;
use MP\Model\Mp\OrderDetail;
use MP\Model\Mp\OrderChangeLog;
use MP\Model\Mp\OrderStatus;

class MallService extends Service{
    public function shopping()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $storeID = App::getInstance()->request()->get('store_id');
        $productID = App::getInstance()->request()->get('product_id');

        $cartNew = new Cart([Cart::MP_USER_ID => $mpUserID,Cart::WX_USER_ID => $wxUserID,Cart::STORE_ID => $storeID]);
        if($cartNew->isEmpty())
        {
            $cartNew -> setMpUserID($mpUserID)->setStoreID($storeID)->setWxUserID($wxUserID)->insert();
        }
        //获取购物车ID
        $cartID = $cartNew -> getCartID();
        $cartDetail = new CartDetail([CartDetail::CART_ID => $cartID,CartDetail::PRODUCT_ID => $productID]);
        if(!$cartDetail->isEmpty())
        {
            //如果存在Cart_id更新购物详情表
            $count = $cartDetail -> getCount();
            $count = $count + 1;
            $cartDetail->setCartID($cartID)->setProductID($productID)->setCount($count)->update();
        }
        else
        {
            //如果不存在Cart_id插入购物详情表
            $cartDetail->setCartID($cartID)->setProductID($productID)->setCount(1)->insert();
        }

        $condetail = [CartDetail::CART_ID => $cartID];
        $ranking = null;
        $dataDetail = CartDetailBusiness::getCartDetailList($condetail,$paging,$ranking,null);
        $num = 0;
        foreach($dataDetail as $v)
        {
            $num += $v[CartDetail::COUNT];
        }
        return $num;
    }
    public function shoppingAdd()
    {
        $res = ['errno' => 0];
        $productID = App::getInstance()->request()->get('product_id');
        $cartID = App::getInstance()->request()->get('cart_id');

        $cartDetail = new CartDetail([CartDetail::PRODUCT_ID => $productID,CartDetail::CART_ID => $cartID]);
        if($cartDetail->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "此购物车已提交，请返回首页重新下单";
            return $res;
        }
        $num = $cartDetail->getCount();
        $num = $num +1;
        $cartDetail->setCount($num)->update();
        return $res;
    }
    public function shoppingReduce()
    {
        $res = ['errno' => 0];
        $productID = App::getInstance()->request()->get('product_id');
        $cartID = App::getInstance()->request()->get('cart_id');
log_debug("------service----------[$productID][$cartID]-----------------");
        $cartDetail = new CartDetail([CartDetail::PRODUCT_ID => $productID,CartDetail::CART_ID => $cartID]);
        if($cartDetail->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "此购物车已提交，请返回首页重新下单";
            return $res;
        }
        $num = $cartDetail->getCount();
        if($num <= 0)
        {
            $num = 0;
        }
        else
        {
            $num = $num - 1;
        }
        $cartDetail->setCount($num)->update();
        return $res;
    }
    public function orderDetail()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $communityID = App::getInstance()->request()->get('community_id');
        $nick = App::getInstance()->request()->get('nick');
        $phone = App::getInstance()->request()->get('phone');
        $address = App::getInstance()->request()->get('address');
        $payMethod = $this->_app->request()->getPostParam( "pay_method");
        if($payMethod == "cash_pay")
        {
             $status=OrderStatus::VERIFIED_TO_SHIP;
        }
        else
        {
            $status=OrderStatus::SUBMITTED_TO_PAY;
        }

        $createTime=time();
        $orderID = OrderBusiness::generateOrderID();
        log_debug("===============".$orderID);
        $totalNum = App::getInstance()->request()->get('total_num');
        $totalMoney = App::getInstance()->request()->get('total_price');

        log_debug("-------------[$mpUserID][$wxUserID][$communityID][$nick][$phone][$address][$status][$createTime][$orderID][$totalNum][$totalMoney]------------------");

        //从house_membe表中获取客服信息
        $currentCsGroupID = $currentCsID = '';
        $houseMember = new HouseMember([HouseMember::MP_USER_ID => $mpUserID,HouseMember::WX_USER_ID => $wxUserID]);
        if(!$houseMember->isEmpty())
        {
            $currentCsID = $houseMember ->getCurrentCsID();//专员id
            $currentCsGroupID = $houseMember->getCurrentCsGroupID();//所在组id
        }
        $order = new \MP\Model\Mp\Order([Order::ORDER_ID => $orderID]);
        if(!$order->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "购物车为空或订单已提交，请返回商城重新下单，谢谢";
            return $res;
        }
        $order->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setAddress($address)->setTel($phone)->setCustomerName($nick)->setCreateTime($createTime)->setStatus($status)->setOrderID($orderID)->setTotalNum($totalNum)->setTotalPrice($totalMoney)->setCsGroupID($currentCsGroupID)->setCsID($currentCsID)->setPayMethod($payMethod)->setStoreType("mall")->insert();

//提交订单后，获取产品信息，写入order_detail表，再清空购物车
        $cartID = App::getInstance()->request()->get('cart_id');
        log_debug("-------------------------[cartID:$cartID]-----------------------------");
        //写入order_detail表
        $condition = [CartDetail::CART_ID => $cartID];
        $expr = " count >= 0";
        $con =  new \Bluefin\Data\DbCondition($expr);
        $condition[] = $con;

        $paging = [];
        $ranking = null;
        $data = CartDetailBusiness::getCartDetailList($condition,$paging,$ranking,null);

        if(!empty($data))
        {
            foreach($data  as $v)
            {
                $productID = $v['product_id'];
                $cartDetail = new CartDetail([CartDetail::CART_ID => $cartID,CartDetail::PRODUCT_ID => $productID]);
                if($cartDetail -> isEmpty())
                {
                    $res['errno'] = 1;
                    //   $res['error'] = "丢失".$productID.$cartID;
                    $res['error'] = "购物车为空或订单已提交，请返回商城重新下单，谢谢";
                    return $res;
                }
                $perTotalNum = $cartDetail -> getCount();
                $product = new Product([Product::PRODUCT_ID => $productID]);
                $price = $product -> getPrice();
                $title = $product -> getTitle();
                $imgUrl = $product ->getImgUrl();
                $detail = $product->getDetail();

                $orderDetail = new OrderDetail();
                $orderDetail->setOrderID($orderID)->setProductID($productID)->setImgUrl($imgUrl)->setPrice($price)->setTitle($title)->setDescription($detail)->setCount($perTotalNum)->insert();

            }
        }

//添加order-change-log
        $orderChangeLog = new OrderChangeLog();
        $orderChangeLog->setOrderID($order->getOrderID())
            ->setStatusBefore(OrderStatus::DEFAULT_STATUS)
            ->setStatusAfter(OrderStatus::SUBMITTED_TO_PAY)
            ->setOperator('auto')
            ->setChangeTime(date('Y-m-d H:i:s'))
            ->setComment('无')
            ->insert();

        //清空购物车
        $cartDetail = new CartDetail([Cart::CART_ID => $cartID]);
        $cart = new Cart([Cart::CART_ID => $cartID]);
        if(empty($cartID))
        {
            $res['errno'] = 1;
            //   $res['error'] = "丢失".$productID.$cartID;
            $res['error'] = "购物车为空或订单已提交，请返回商城重新下单，谢谢";
            return $res;
        }
        $cartDetail -> delete([CartDetail::CART_ID => $cartID]);
        $cart->delete([Cart::CART_ID => $cartID]);
        return [ 'errno' => 0, "order_id" =>(string)$orderID,"pay_method" => $payMethod, "mp_user_id" => $mpUserID];


    }
}
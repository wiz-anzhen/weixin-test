<?php
/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-8-13
 * Time: 下午4:24
 */
use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use WBT\Business\ConfigBusiness;
use WBT\Business\Weixin\CartDetailBusiness;
use WBT\Business\Weixin\OrderBusiness;
use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\Product;
use MP\Model\Mp\Category;

use MP\Model\Mp\ProcurementOrderChangeLog;
use MP\Model\Mp\ProcurementOrderStatus;

use MP\Model\Mp\Store;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\Community;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\Weixin\UserNotifyBusiness;
use MP\Model\Mp\ProcurementOrderChangeDetail;

use MP\Model\Mp\Part;
use MP\Model\Mp\ProcurementOrderDetail;
use MP\Model\Mp\ProcurementCart;
use MP\Model\Mp\ProcurementCartDetail;
class ProcurementService extends Service
{

    public function shoppingAdd()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $storeID = App::getInstance()->request()->get('store_id');
        $partID = App::getInstance()->request()->get('part_id');
        $productID = App::getInstance()->request()->get('product_id');
        $cartID = App::getInstance()->request()->get('cart_id');
        log_debug("=====================================".$cartID);
        log_debug("=====================================".$productID);
        $product = new Product([Product::PRODUCT_ID => $productID]);
        $productUnit =  $product->getProductUnit();
        //查看是否已建立用户购物车
        if(empty($cartID))
        {
           $cart = new ProcurementCart([ProcurementCart::MP_USER_ID => $mpUserID,ProcurementCart::STORE_ID => $storeID,ProcurementCart::WX_USER_ID => $wxUserID,ProcurementCart::PART_ID => $partID]);
            if($cart->isEmpty())
            {
                $cartNew = new ProcurementCart();
                $cartNew -> setMpUserID($mpUserID)->setStoreID($storeID)->setWxUserID($wxUserID)->setPartID($partID)->insert();
                //获取购物车ID
                $cartID = $cartNew ->getProcurementCartID();
                $cartDetail = new ProcurementCartDetail();
                if($productUnit == "kilo" or $productUnit == "tael" or $productUnit == "kg" )
                {
                    $cartDetail->setCartID($cartID)->setProductID($productID)->setCount(0.1)->setPartID($partID)->insert();
                }
                else
                {
                    $cartDetail->setCartID($cartID)->setProductID($productID)->setCount(1)->setPartID($partID)->insert();
                }

            }
            else
            {
                $cartID = $cart->getProcurementCartID();

                $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::PRODUCT_ID => $productID,ProcurementCartDetail::CART_ID => $cartID,ProcurementCartDetail::PART_ID => $partID]);
                if(!$cartDetail->isEmpty())
                {
                    //如果存在Cart_id更新购物详情表
                    $num = $cartDetail->getCount();
                    if($productUnit == "kilo" or $productUnit == "tael" or $productUnit == "kg" )
                    {
                        $num = $num + 0.1;
                    }
                    else
                    {
                        $num = $num + 1;
                    }

                    $cartDetail->setCount($num)->update();
                }
                else
                {
                    //如果不存在Cart_id插入购物详情表
                    if($productUnit == "kilo" or $productUnit == "tael" or $productUnit == "kg" )
                    {
                        $num =  0.1;
                    }
                    else
                    {
                        $num = 1;
                    }
                    $cartDetail->setCartID($cartID)->setProductID($productID)->setCount($num)->setPartID($partID)->insert();
                }

            }

        }
        else
        {

            $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::PRODUCT_ID => $productID,ProcurementCartDetail::CART_ID => $cartID,ProcurementCartDetail::PART_ID => $partID]);
            if(!$cartDetail->isEmpty())
            {
                //如果存在Cart_id更新购物详情表
                $num = $cartDetail->getCount();
                if($productUnit == "kilo" or $productUnit == "tael" or $productUnit == "kg" )
                {
                    $num = $num + 0.1;
                }
                else
                {
                    $num = $num + 1;
                }

                $cartDetail->setCount($num)->update();
            }
            else
            {
                //如果不存在Cart_id插入购物详情表
                if($productUnit == "kilo" or $productUnit == "tael" or $productUnit == "kg" )
                {
                    $num =  0.1;
                }
                else
                {
                    $num = 1;
                }
                $cartDetail->setCartID($cartID)->setProductID($productID)->setCount($num)->setPartID($partID)->insert();
            }

        }
        $res = ['cart_id' => $cartID];
        log_debug("=====================================".$res['cart_id']);
        return $res;
    }

    public function shoppingChange()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $storeID = App::getInstance()->request()->get('store_id');
        $partID = App::getInstance()->request()->get('part_id');
        $productID = App::getInstance()->request()->get('product_id');

        $cartID = App::getInstance()->request()->get('cart_id');
        $productValue = App::getInstance()->request()->get('product_value');

        log_debug("=====================================".$cartID);
        log_debug("=====================================".$productID);
        //查看是否已建立用户购物车
        if(empty($cartID))
        {
            $cart = new ProcurementCart([ProcurementCart::MP_USER_ID => $mpUserID,ProcurementCart::STORE_ID => $storeID,ProcurementCart::WX_USER_ID => $wxUserID,ProcurementCart::PART_ID => $partID]);
            if($cart->isEmpty())
            {
                $cartNew = new ProcurementCart();
                $cartNew -> setMpUserID($mpUserID)->setStoreID($storeID)->setWxUserID($wxUserID)->setPartID($partID)->insert();
                //获取购物车ID
                $cartID = $cartNew->getProcurementCartID();;
                $cartDetail = new ProcurementCartDetail();
                $cartDetail->setCartID($cartID)->setProductID($productID)->setCount($productValue)->setPartID($partID)->insert();
            }
            else
            {
                $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::PRODUCT_ID => $productID,ProcurementCartDetail::CART_ID => $cartID,ProcurementCartDetail::PART_ID => $partID]);
                if(!$cartDetail->isEmpty())
                {
                    //如果存在Cart_id更新购物详情表
                    $cartDetail->setCount($productValue)->update();
                }
                else
                {
                    //如果不存在Cart_id插入购物详情表
                    $cartDetail->setCartID($cartID)->setProductID($productID)->setCount($productValue)->setPartID($partID)->insert();
                }
                $cartID = $cart->getCartID();
            }

        }
        else
        {
            $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::PRODUCT_ID => $productID,ProcurementCartDetail::CART_ID => $cartID,ProcurementCartDetail::PART_ID => $partID]);
            if(!$cartDetail->isEmpty())
            {
                //如果存在Cart_id更新购物详情表
                $cartDetail->setCount($productValue)->update();
            }
            else
            {
                //如果不存在Cart_id插入购物详情表
                $cartDetail->setCartID($cartID)->setProductID($productID)->setCount($productValue)->setPartID($partID)->insert();
            }

        }
        $res = ['cart_id' => $cartID];
        log_debug("=====================================".$res['cart_id']);
        return $res;
    }


    public function shoppingReduce()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $storeID = App::getInstance()->request()->get('store_id');
        $partID = App::getInstance()->request()->get('part_id');
        $productID = App::getInstance()->request()->get('product_id');
        $product = new Product([Product::PRODUCT_ID => $productID]);
        $productUnit =  $product->getProductUnit();
        $cartID = App::getInstance()->request()->get('cart_id');
        //查看是否已建立用户购物车
        if(empty($cartID))
        {
            return $res;

        }
        else
        {
            $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::PRODUCT_ID => $productID,ProcurementCartDetail::CART_ID => $cartID,ProcurementCartDetail::PART_ID => $partID]);
            if(!$cartDetail->isEmpty())
            {
                //如果存在Cart_id更新购物详情表
                $num = $cartDetail->getCount();
                if($productUnit == "kilo" or $productUnit == "tael" or $productUnit == "kg" )
                {
                    $num = $num - 0.1;
                }
                else
                {
                    $num = $num - 1;
                }

                if($num <= 0)
                {
                    $num = 0.0;
                }
                $cartDetail->setCount($num)->update();
            }

        }
        $res = ['cart_id' => $cartID];
        return $res;
    }
//订货处理并发送给厨师长
    public function shoppingOrder()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $topDirectoryID = App::getInstance()->request()->get('top_directory_id');
        $storeID = App::getInstance()->request()->get('store_id');
        $selfType = App::getInstance()->request()->get('self_type');
        $cartTotalPrice = App::getInstance()->request()->get('cart_total_price');

        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $mpUserID = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();

        $createTime=time();
        $orderID = OrderBusiness::generateOrderID();
        log_debug("===============".$orderID);
        log_debug("-------------[$mpUserID][$wxUserID][$createTime][$orderID]------------------");

        $procurementOrder = new procurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        if(!$procurementOrder->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "购物车为空或订单已提交，请返回商城重新下单，谢谢";
            return $res;
        }

        $store = new Store([Store::STORE_ID => $storeID]);
        $category = new Category([Category::STORE_ID => $storeID,Category::IS_ON_SHELF => "1"]);
        $procurementOrder->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setTel($wxUser->getPhone())->setCustomerName($wxUser->getNick())->setCreateTime($createTime)->setStatus("chef_verify")->setOrderID($orderID)->setTotalPrice($cartTotalPrice)->setStoreID($storeID)->setBoundCommunityID($store->getBoundCommunityID())->setBoundStoreID($store->getBoundStoreID())->setCategoryID($category->getCategoryID())->setOrderSelf($selfType)->insert();

        $cartIDs = ProcurementCart::fetchRows(["*"],[ProcurementCart::WX_USER_ID => $wxUser->getWxUserID(),ProcurementCart::STORE_ID => $storeID]);
//提交订单后，获取产品信息，写入order_detail表，再清空购物车
        log_debug("-------------------------[cartID:$cartID]-----------------------------");
        //写入order_detail表
        foreach($cartIDs as $k => $value)
        {
            $cartID = $value[ProcurementCart::PROCUREMENT_CART_ID];
            $partID = $value[ProcurementCart::PART_ID];
            $condition = [ProcurementCartDetail::CART_ID => $cartID];
            $expr = " count >= 0";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;

            $paging = [];
            $ranking = null;
            $data = ProcurementCartDetail::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );

            if(!empty($data))
            {
                foreach($data  as $v)
                {
                    $productID = $v['product_id'];
                    $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::CART_ID => $cartID,ProcurementCartDetail::PRODUCT_ID => $productID,ProcurementCartDetail::PART_ID => $partID]);
                    if($cartDetail -> isEmpty())
                    {
                        $res['errno'] = 1;
                        $res['error'] = "购物车为空或订单已提交，请返回商城重新下单，谢谢";
                        return $res;
                    }
                    $perTotalNum = $cartDetail -> getCount();
                    if($perTotalNum > 0)
                    {
                        $product = new Product([Product::PRODUCT_ID => $productID]);
                        $price = $product -> getPrice();
                        $title = $product -> getTitle();
                        $imgUrl = $product ->getImgUrl();
                        $detail = $product->getDetail();
                        $productUnit = $product->getProductUnit();
                        $orderDetail = new ProcurementOrderDetail();
                        $orderDetail->setOrderID($orderID)->setProductID($productID)->setImgUrl($imgUrl)->setPrice($price)->setTitle($title)->setDescription($detail)->setCount($perTotalNum)->setProductUnit($productUnit)->setPartID($partID)->insert();

                        $procurementOrderChangeDetail = new ProcurementOrderChangeDetail();
                        $procurementOrderChangeDetail->setProductUnit($productUnit)->setCount($perTotalNum)->setDescription($detail)->setImgUrl($imgUrl)->setProductID($productID)->setStatus("chef_verify")->setTitle($title)->setOrderID($orderID)->setPrice($price)->setPartID($partID)->insert();

                    }

                }
            }
            //清空购物车
            $cartDetail = new ProcurementCartDetail([ProcurementCartDetail::CART_ID => $cartID]);
            $cart = new ProcurementCart([ProcurementCart::PROCUREMENT_CART_ID => $cartID]);

            $cartDetail ->delete([ProcurementCartDetail::CART_ID => $cartID]);
            $cart->delete([ProcurementCart::PROCUREMENT_CART_ID => $cartID]);

        }

//添加order-change-log
        $orderChangeLog = new ProcurementOrderChangeLog();
        $orderChangeLog->setOrderID($procurementOrder->getOrderID())
            ->setStatusBefore("none")
            ->setStatusAfter("chef_verify")
            ->setOperator($wxUser->getNick())
            ->setChangeTime(date('Y-m-d H:i:s'))
            ->setComment('无')
            ->insert();



        //发送模板消息
        $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

        $newWxUserIDs = [];
        foreach($wxUserIDs as $value)
        {
            $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);

            if($house->getMemberType() == "chef")
            {
                $newWxUserIDs[] = $value;
            }
        }

        $host =  ConfigBusiness::getHost();//获取主机名
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
        $community = new Community([Community::COMMUNITY_ID => $store->getBoundCommunityID()]);

        foreach($newWxUserIDs as $value)
        {
            $url = sprintf("%s/wx_user/procurement/chef_order_detail?mp_user_id=%s&store_title=%s&order_id=%s&wx_user_id=%s&top_directory_id=%s&template=template",$host,$mpUserID,$store->getTitle(),$orderID,$value,$topDirectoryID);
            $first = "采购单通知，等待厨师长确认";
            $nick = $community->getName();
            $content = "厨师长需要确认订单";

            $template = array( 'touser' => $value,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode("供应商："."$nick"),'color' =>"#cf3134", ),
                    'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("点击查看") ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }
        return [ 'errno' => 0];


    }

    public function changeOrder()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $orderID = App::getInstance()->request()->get('order_id');
        $count = App::getInstance()->request()->get('count');
        $productOldID = App::getInstance()->request()->get('product_id');

        $productID = explode("_",$productOldID);
        $productID = $productID[1];
log_debug("====================".$productID);log_debug("====================".$orderID);log_debug("====================".$count);
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);

        $procurementOrder = new procurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        if($procurementOrder->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "对不起，没有此订单";
            return $res;
        }

        $orderDetail = new OrderDetail([OrderDetail::ORDER_ID => $orderID,OrderDetail::PRODUCT_ID => $productID]);
        if($orderDetail->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "对不起，没有此产品";
            return $res;
        }

        if($count == 0)
        {
            $orderDetail->delete();
        }
        else
        {
            $orderDetail->setCount($count)->update();
        }

        $orderDetailData = OrderDetail::fetchRows(['*'],[OrderDetail::ORDER_ID => $orderID]);
        $totalPrice = "";
        foreach($orderDetailData as $value)
        {
            $totalPrice += $value[OrderDetail::PRICE]*$value[OrderDetail::COUNT];
        }

        $procurementOrder->setTotalPrice($totalPrice)->update();

//添加order-change-log
        $orderChangeLog = new ProcurementOrderChangeLog();
        $orderChangeLog->setOrderID($procurementOrder->getOrderID())
            ->setStatusBefore("chef_verify")
            ->setStatusAfter("chef_verify")
            ->setOperator($wxUser->getNick())
            ->setChangeTime(date('Y-m-d H:i:s'))
            ->setComment('无')
            ->insert();

        return ['errno' => 0,'count' => $count,'product_id' => $productOldID,'total_price' => $totalPrice];


    }
// 处理发送催促通知——等待供应商确认，退货进度，无修改的厨师长确认
    public function sendSupply()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $topDirectoryID = App::getInstance()->request()->get('top_directory_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $chefName = $wxUser->getNick();
        $orderID = App::getInstance()->request()->get('order_id');
        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        $type = App::getInstance()->request()->get('type');
        $store = new Store([Store::STORE_ID => $order->getStoreID()]);
        $orderSelf = $order->getOrderSelf();
        if($type != "send")
        {
            if($orderSelf == 'order_self')
            {
                if($type == "supply_verify")
                {
                    $order->setStatus('examine')->update();
                }
            }
            else
            {
                $order->setStatus($type)->update();
            }

        }


        $mpUserID  = $order->getMpUserID();
        $communityID = "";
        $host =  ConfigBusiness::getHost();//获取主机名
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
// 处理发送无修改的厨师长确认
        if($type == "supply_verify" and $orderSelf != 'order_self')
        {
            $communityID = $order->getBoundCommunityID();
            $url = sprintf("%s/wx_user/supply/order_detail?mp_user_id=%s&community_id=%s&order_id=%s&top_directory_id=%s",$host,$mpUserID,$order->getCommunityID(),$orderID,$topDirectoryID);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first = "订货单确认通知";
            $nick = $community->getName();
            $content = "等待您的确认";
            $sendType = "order";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("chef_verify")
                ->setStatusAfter($type)
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
            $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
            foreach($orderDetailData as $key => $value)
            {
                $procurementOrderChangeDetail = new ProcurementOrderChangeDetail();
                $procurementOrderChangeDetail->setProductUnit($value[ProcurementOrderDetail::PRODUCT_UNIT])->setCount($value[ProcurementOrderDetail::COUNT])->setDescription($value[ProcurementOrderDetail::DESCRIPTION])->setImgUrl($value[ProcurementOrderDetail::IMG_URL])->setProductID($value[ProcurementOrderDetail::PRODUCT_ID])->setStatus("supply_verify")->setTitle($value[ProcurementOrderDetail::TITLE])->setOrderID($value[ProcurementOrderDetail::ORDER_ID])->setPrice($value[ProcurementOrderDetail::PRICE])->setPartID($value[ProcurementOrderDetail::PART_ID])->insert();
            }

        }
// 处理发送催促通知——等待供应商确认
        if($type == "send")
        {
            $communityID = $order->getBoundCommunityID();
            $url = sprintf("%s/wx_user/supply/order_detail?mp_user_id=%s&community_id=%s&order_id=%s&top_directory_id=%s",$host,$mpUserID,$order->getCommunityID(),$orderID,$topDirectoryID);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first = "订货单催促通知";
            $nick = $community->getName();
            $content = "等待您的确认";
            $sendType = "order";
        }
// 处理发送催促通知——退货进度
        if($type == "refund")
        {
            $communityID =  $order->getBoundCommunityID();

            $url = sprintf("%s/wx_user/supply/return_detail?mp_user_id=%s&community_id=%s&order_id=%s&top_directory_id=%s",$host,$mpUserID,$communityID,$orderID,$topDirectoryID);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first ="退货单催促通知";
            $nick = $community->getName();
            $content = "等待您的确认";
            $sendType = "examine";
        }

        //发送模板消息
        $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

        $newWxUserIDs = [];
        foreach($wxUserIDs as $value)
        {
            $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);

            $housePower = $house->getProcurementPowerType();
            $housePower = explode(",",$housePower);
            if(strict_in_array($sendType,$housePower))
            {
                $newWxUserIDs[] = $value;
            }

        }


        foreach($newWxUserIDs as $value)
        {
            $url = $url."&template=template";
            $template = array( 'touser' => $value,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode("餐厅："."$nick"),'color' =>"#cf3134", ),
                    'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("点击查看") ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }

        // 处理发送通知——厨师长确认,发送给订货员
        if($type == "supply_verify" and $orderSelf != 'order_self')
        {
            $communityID = $order->getCommunityID();

            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=订货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $community = new Community([Community::COMMUNITY_ID => $order->getBoundCommunityID()]);
            $first ="订货单确认通知";
            $nick = $community->getName();
            $content = "厨师长已确认";
            $sendType = "order";

        }

        if($type == "supply_verify" and $orderSelf == 'order_self')
        {
            $communityID = $order->getCommunityID();

            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=订货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $community = new Community([Community::COMMUNITY_ID => $order->getBoundCommunityID()]);
            $first ="订货单确认通知";
            $nick = $community->getName();
            $content = "等待验货";
            $sendType = "order";

        }

        $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

        $newWxUserIDs = [];
        foreach($wxUserIDs as $value)
        {
            $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);
            $housePower = $house->getProcurementPowerType();
            $housePower = explode(",",$housePower);
            if(strict_in_array($sendType,$housePower))
            {
                $newWxUserIDs[] = $value;
            }

        }


        foreach($newWxUserIDs as $value)
        {
            $url = $url."&template=template";
            $template = array( 'touser' => $value,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode("供应商:".$nick),'color' =>"#cf3134", ),
                    'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("点击查看") ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }
        return ['errno' => 0];


    }
// 处理发送催促通知——修改的厨师长确认，验货确认
    public function sendSupplyAlter()
    {

        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);

        $orderID = App::getInstance()->request()->get('order_id');
        $type = App::getInstance()->request()->get('type');
        $chefCount = App::getInstance()->request()->get('chef_count');
        log_debug("================".$chefCount);
        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        $orderSelf = $order->getOrderSelf();
            if($orderSelf == 'order_self')
            {
                if($type == "supply_verify")
                {
                    $order->setStatus('examine')->update();
                }
                if($type == "supply_examine")
                {
                    $order->setStatus('finished')->update();
                }
            }
            else
            {
                $order->setStatus($type)->update();
            }

        $store = new Store([Store::STORE_ID => $order->getStoreID()]);

        $productData = App::getInstance()->request()->get('product_data');

        log_debug("================",$productData);
        $productData = explode(',',$productData[0]);
        log_debug("================",$productData);
        $totalPrice = "";
        foreach($productData as $key => $value)
        {
            if(!empty($value))
            {
                $process = explode(":",$value);
                log_debug("================",$process);
                $productIdCount = $process[1];
                $key_process = explode("_",$process[0]);
                $productID = $key_process[1];
                $productIDProgress = explode("part",$productID);
                $productID = $productIDProgress[0];
                $partID = $productIDProgress[1];
                $orderDetail = new ProcurementOrderDetail([ProcurementOrderDetail::ORDER_ID => $orderID,ProcurementOrderDetail::PRODUCT_ID => $productID,ProcurementOrderDetail::PART_ID => $partID]);
                $totalPrice += $productIdCount*$orderDetail->getPrice();
                if(!$orderDetail->isEmpty())
                {
                    if($productIdCount > 0)
                    {
                        $orderDetail->setCount($productIdCount)->update();
                    }
                    else
                    {
                        $orderDetail->delete();
                    }

                }

            }
        }
        $order->setTotalPrice($totalPrice)->update();
        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        $totalPrice = "";
        foreach($orderDetailData as $key => $value)
        {
            $totalPrice += ($value[ProcurementOrderDetail::PRICE])*($value[ProcurementOrderDetail::COUNT]);
        }

        $totalPrice = number_format($totalPrice, 2,'.','');

        //发送模板消息
        $mpUserID  = $order->getMpUserID();
        $communityID = "";
        $host =  ConfigBusiness::getHost();//获取主机名
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
// 修改的厨师长确认
        if($type == "supply_verify" and $orderSelf != 'order_self')
        {
            $communityID = $order->getBoundCommunityID();

            $url = sprintf("%s/wx_user/supply/order_detail?mp_user_id=%s&community_id=%s&order_id=%s",$host,$mpUserID,$order->getCommunityID(),$orderID);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first ="订货单确认通知";
            $nick = $community->getName();
            $content = "等待您的确认";
            $sendType = "order";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("chef_verify")
                ->setStatusAfter("supply_verify")
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
            $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
            if($chefCount == "second")
            {
                $typeStatus = "examine";
                $first ="订货单修改确认通知";
                $chefCountTime = 1;
                $chefCountTimes = ProcurementOrderChangeDetail::fetchColumn([ProcurementOrderChangeDetail::CHEF_COUNT],[ProcurementOrderChangeDetail::ORDER_ID => $orderID]);
                rsort($chefCountTimes);
                if(empty($chefCountTimes[0]))
                {
                    $chefCountTime = 1;
                }
                else
                {
                    $chefCountTime = $chefCountTimes[0]+1;
                }
            }
            else
            {
                $chefCountTime = 0;
                $typeStatus = "supply_verify";
            }

            foreach($orderDetailData as $key => $value)
            {
                $procurementOrderChangeDetail = new ProcurementOrderChangeDetail();
                $procurementOrderChangeDetail->setProductUnit($value[ProcurementOrderDetail::PRODUCT_UNIT])->setCount($value[ProcurementOrderDetail::COUNT])->setDescription($value[ProcurementOrderDetail::DESCRIPTION])->setImgUrl($value[ProcurementOrderDetail::IMG_URL])->setProductID($value[ProcurementOrderDetail::PRODUCT_ID])->setStatus($typeStatus)->setTitle($value[ProcurementOrderDetail::TITLE])->setOrderID($value[ProcurementOrderDetail::ORDER_ID])->setPrice($value[ProcurementOrderDetail::PRICE])->setChefCount($chefCountTime)->setPartID($value[ProcurementOrderDetail::PART_ID])->insert();
            }
        }
// ——验货确认
        if($type == "supply_examine"  and $orderSelf != 'order_self')
        {
            $communityID = $order->getBoundCommunityID();
            $url = sprintf("%s/wx_user/supply/examine_detail?mp_user_id=%s&community_id=%s&order_id=%s",$host,$mpUserID,$order->getCommunityID(),$orderID);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first = "验货单确认通知";
            $nick = $community->getName();
            $content = "等待您的确认";
            $sendType = "examine";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("examine")
                ->setStatusAfter("supply_examine")
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
        }

        if($orderSelf != 'order_self')
        {
            $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

            $newWxUserIDs = [];
            foreach($wxUserIDs as $value)
            {
                $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);
                $housePower = $house->getProcurementPowerType();
                $housePower = explode(",",$housePower);
                if(strict_in_array($sendType,$housePower))
                {
                    $newWxUserIDs[] = $value;
                }

            }


            foreach($newWxUserIDs as $value)
            {
                $url = $url."&template=template";
                $template = array( 'touser' => $value,
                    'template_id' => "$templateID",
                    'url' => $url,
                    'topcolor' => "#62c462",
                    'data'   => array(
                        'first' => array('value' => urlencode("餐厅:".$nick),'color' =>"#cf3134", ),
                        'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                        'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                        'remark' => array('value' => urlencode("点击查看") ,
                            'color' =>"#222" ,))
                );

                WxApiBusiness::sentTemplateMessage($mpUserID,$template);
            }
        }

        // 修改的厨师长确认,发送给订货员

        if($type == "supply_verify" and $orderSelf != 'order_self')
        {
            $communityID = $order->getCommunityID();
            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=订货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $community = new Community([Community::COMMUNITY_ID => $order->getBoundCommunityID()]);
            $first ="订货单确认通知";
            $nick = $community->getName();
            $content = "厨师长已确认";
            $sendType = "order";

        }
        if($type == "supply_verify" and $orderSelf == 'order_self')
        {
            $communityID = $order->getCommunityID();
            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=订货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $community = new Community([Community::COMMUNITY_ID => $order->getBoundCommunityID()]);
            $first ="订货单确认通知";
            $nick = $community->getName();
            $content = "等待验货";
            $sendType = "order";
        }
// 处理发送通知——验货确认，发送给验货员
        if($type == "supply_examine" )
        {
            $communityID = $order->getCommunityID();
            $url = sprintf("%s/wx_user/supply/examine_detail?mp_user_id=%s&community_id=%s&order_id=%s&from_type=restaurant",$host,$mpUserID,$order->getCommunityID(),$orderID);
            $community = new Community([Community::COMMUNITY_ID => $order->getBoundCommunityID()]);
            $first = "验货单确认通知";
            $nick = $community->getName();
            $content = "验货单已确认";
            $sendType = "examine";

        }


        $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

        $newWxUserIDs = [];
        foreach($wxUserIDs as $value)
        {
            $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);
            $housePower = $house->getProcurementPowerType();
            $housePower = explode(",",$housePower);
            if(strict_in_array($sendType,$housePower))
            {
                $newWxUserIDs[] = $value;
            }

        }


        foreach($newWxUserIDs as $value)
        {
            $url = $url."&template=template";
            $template = array( 'touser' => $value,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode("供应商:".$nick),'color' =>"#cf3134", ),
                    'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("点击查看") ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }

        return ['errno' => 0, 'total_price'=> $totalPrice];
    }
    // 处理退货变更
    public function refund()
    {
        $host =  ConfigBusiness::getHost();//获取主机名

        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);

        $orderID = App::getInstance()->request()->get('order_id');
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $alterCount = App::getInstance()->request()->get('alter_count');
        $describe = App::getInstance()->request()->get('describe');
        log_debug("==================".$describe);
        $productID = App::getInstance()->request()->get('product_id');
        $partID = App::getInstance()->request()->get('part_id');
        $productPrice = App::getInstance()->request()->get('product_price');
        $type = App::getInstance()->request()->get('type');
        $selfType = App::getInstance()->request()->get('self_type');
        if($selfType == "order_self")
        {
            $type = "refund_finished";
        }
        if($selfType == "order_supply")
        {
            $type = "refund";
        }
        $imgFirst = App::getInstance()->request()->get('img_first');
        $imgSecond = App::getInstance()->request()->get('img_second');
        $imgThird = App::getInstance()->request()->get('img_third');
        if(empty($imgFirst))
        {
            $imgFirst = $host."/images/none.png";
        }
        if(empty($imgSecond))
        {
            $imgSecond = $host."/images/none.png";
        }
        if(empty($imgThird))
        {
            $imgThird  = $host."/images/none.png";
        }

        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);//原订单
        $oldOrderDetail = new ProcurementOrderDetail([ProcurementOrderDetail::ORDER_ID => $orderID,ProcurementOrderDetail::PRODUCT_ID => $productID,ProcurementOrderDetail::PART_ID => $partID]);
        $oldOrderDetail->setRefund(1)->update();

        $createTime=time();

        log_debug("========11111111111111111111111=======".$orderID);

        $orderIDNew = OrderBusiness::generateOrderID();
        $procurementOrder = new procurementOrder([ProcurementOrder::ORDER_ID => $orderIDNew]);
        if(!$procurementOrder->isEmpty())
        {
            $res['errno'] = 1;
            $res['error'] = "购物车为空或订单已提交，请返回商城重新下单，谢谢";
            return $res;
        }


        $totalPrice = $alterCount*$productPrice;
        $product = new Product([Product::PRODUCT_ID => $productID]);
        $store = new Store([Store::STORE_ID => $product->getStoreID()]);
        $procurementOrder->setMpUserID($mpUserID)->setCommunityID($order->getCommunityID())->setWxUserID($wxUserID)->setTel($wxUser->getPhone())->setCustomerName($wxUser->getNick())->setCreateTime($createTime)->setStatus($type)->setOrderID($orderIDNew)->setTotalPrice($totalPrice)->setStoreID($product->getStoreID())->setBoundCommunityID($store->getBoundCommunityID())->setBoundStoreID($store->getBoundStoreID())->setCategoryID($product->getCategoryID())->setRefundDescribe($describe)->setRefundOrderID($orderID)->setRefundImgFirst($imgFirst)->setRefundImgSecond($imgSecond)->setRefundImgThird($imgThird)->setOrderSelf($selfType)->insert();


        $price = $product -> getPrice();
        $title = $product -> getTitle();
        $imgUrl = $product ->getImgUrl();
        $detail = $product->getDetail();
        $productUnit = $product->getProductUnit();

        $orderDetail = new ProcurementOrderDetail();
        $orderDetail->setOrderID($orderIDNew)->setProductID($productID)->setImgUrl($imgUrl)->setPrice($price)->setTitle($title)->setDescription($detail)->setCount($alterCount)->setProductUnit($productUnit)->setPartID($partID)->insert();
        log_debug("========11111111111111111111111=======".$procurementOrder->getStoreID());
        //发送模板消息
        if($selfType == "order_supply")
        {
            $mpUserID  = $order->getMpUserID();

            $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
            $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id

            $communityID = $order->getBoundCommunityID();

            $url = sprintf("%s/wx_user/supply/return_detail?mp_user_id=%s&community_id=%s&order_id=%s",$host,$mpUserID,$communityID,$orderIDNew);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first = "退货单确认通知";
            $nick = $community->getName();
            $content = "等待您的确认";

            $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

            $newWxUserIDs = [];
            foreach($wxUserIDs as $value)
            {
                $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);
                $housePower = $house->getProcurementPowerType();
                $housePower = explode(",",$housePower);
                if(strict_in_array("refund",$housePower))
                {
                    $newWxUserIDs[] = $value;
                }

            }


            foreach($newWxUserIDs as $value)
            {
                $url = $url."&template=template";
                $template = array( 'touser' => $value,
                    'template_id' => "$templateID",
                    'url' => $url,
                    'topcolor' => "#62c462",
                    'data'   => array(
                        'first' => array('value' => urlencode("餐厅:".$nick),'color' =>"#cf3134", ),
                        'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                        'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                        'remark' => array('value' => urlencode("点击查看") ,
                            'color' =>"#222" ,))
                );

                WxApiBusiness::sentTemplateMessage($mpUserID,$template);
            }


        }

        if($selfType == "order_self")
        {
            $mpUserID  = $order->getMpUserID();

            $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
            $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id

            $communityID = $order->getCommunityID();

            $url = sprintf("%s/wx_user/supply/return_detail?mp_user_id=%s&community_id=%s&order_id=%s",$host,$mpUserID,$communityID,$orderIDNew);
            $community = new Community([Community::COMMUNITY_ID => $order->getCommunityID()]);
            $first = "自退货通知";
            $nick = $community->getName();
            $content = "退货完成";

            $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

            $newWxUserIDs = [];
            foreach($wxUserIDs as $value)
            {
                $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);
                $housePower = $house->getProcurementPowerType();
                $housePower = explode(",",$housePower);
                if(strict_in_array("refund",$housePower))
                {
                    $newWxUserIDs[] = $value;
                }

            }


            foreach($newWxUserIDs as $value)
            {
                $url = $url."&template=template";
                $template = array( 'touser' => $value,
                    'template_id' => "$templateID",
                    'url' => $url,
                    'topcolor' => "#62c462",
                    'data'   => array(
                        'first' => array('value' => urlencode("餐厅:".$nick),'color' =>"#cf3134", ),
                        'keyword1' => array('value' => urlencode($first),'color' =>"#222", ),
                        'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                        'remark' => array('value' => urlencode("点击查看") ,
                            'color' =>"#222" ,))
                );

                WxApiBusiness::sentTemplateMessage($mpUserID,$template);
            }

        }

        return ['errno' => 0];
    }


    public function returnSupply()
    {
        $restaurantID    = App::getInstance()->request()->get('restaurant_id');
        $condition = [Store::COMMUNITY_ID => $restaurantID,Store::IS_DELETE => "0"];
        $store = Store::fetchRows(['*'],$condition);
        log_debug("=========================",$store);
        return $store;
    }
}
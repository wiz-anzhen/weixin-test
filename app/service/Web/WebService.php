<?php

use Bluefin\Service;
use RCRM\Model\Rcrm\Order;
use RCRM\Model\Rcrm\OrderDetail;
use RCRM\Model\Rcrm\RestaurantMenu;
use RCRM\Model\Rcrm\UserMenuCount;
use WBT\Business\Weixin\HouseMemberBusiness;

class WebService extends Service
{
    public function orderMenu() {
        $request  = $this->_app->request();
        $mpUserId = $request->get( 'mp_user_id' );
        $wxUserId = $request->get( 'wx_user_id' );
        $orderId  = $request->get( 'order_id' );
        /*$menuId   = $request->get( 'menu_id' );*/
        $action   = $request->get( 'action' );

        $order = new Order([ Order::ORDER_ID => $orderId ]);
        if ($order->isEmpty()) {
            return [ 'errno' => 1, 'message' => '当前点单已失效', ];
        } elseif ($order->getYmd() != intval( date( 'Ymd' ) )) {
            return [ 'errno' => 1, 'message' => '点单仅当日有效', ];
        } elseif ($order->getIsSubmitted()) {
            return [ 'errno' => 1, 'message' => '点单已提交', ];
        } elseif (OrderDetail::fetchCount( [ OrderDetail::ORDER_ID => $orderId ] ) > 100) {
            return [ 'errno' => 1, 'message' => '数量已超过上限', ];
        }

        /*$menu = new RestaurantMenu([ 'mp_user_id' => $mpUserId, 'menu_id' => $menuId ]);
        if ($menu->isEmpty()) {
            return [ 'errno' => 1, 'message' => '菜编号不存在', ];
        }*/

        $orderDetail = new OrderDetail([ 'order_id'   => $orderId,
                                       'menu_auto_id' => $menu->getRestaurantMenuID() ]);
        if ($orderDetail->isEmpty() && $action == 1) {
            $menuPrice   = $menu->getMenuPrice();
            $onSalePrice = $menu->getOnSalePrice();
            if (!empty($onSalePrice) && strlen( $menu->getOnSaleDay() ) > 0)
            {
                if (strpos( $menu->getOnSaleDay(), $w = date( 'w' ) ) !== FALSE)
                {
                    $menuPrice = $onSalePrice;
                }
            }
            $orderDetail->setMenuCount( 1 )->setMenuAutoID( $menu->getRestaurantMenuID() );
            $orderDetail->setMenuName( $menu->getMenuName() )->setMenuPrice( $menuPrice );
            $orderDetail->setMpUserID( $mpUserId )->setOrderID( $orderId )->setWxUserID( $wxUserId );
            $orderDetail->insert( TRUE );

            $userMenuCount = new UserMenuCount([ 'wx_user_id' => $wxUserId,
                                               'menu_auto_id' => $menu->getRestaurantMenuID() ]);
            if ($userMenuCount->isEmpty())
            {
                $userMenuCount->setMenuAutoID( $menu->getRestaurantMenuID() )->setMenuCount( 1 );
                $userMenuCount->setWxUserID( $wxUserId )->setMpUserID( $mpUserId )->insert();
            }
            else
            {
                $userMenuCount->setMenuCount( $userMenuCount->getMenuCount() + 1 )->save();
            }
        }
        elseif ($action == 1)
        {
            $menuCount = $orderDetail->getMenuCount() + 1;
            $orderDetail->setMenuCount( $menuCount )->update();
        } elseif ($action == -1)
        {
            $menuCount = $orderDetail->getMenuCount() - 1;
            $menuCount = max( $menuCount, 0 );
            $orderDetail->setMenuCount( $menuCount )->update();
        }

        // TODO check posted orderId equals to $mpUser->getCurOrderId()

        return [ 'errno' => 0 ];
    }

    public function syncOrder() {
        $request  = $this->_app->request();
        $mpUserId = $request->get( 'mp_user_id' );
        $wxUserId = $request->get( 'wx_user_id' );
        $orderId  = $request->get( 'order_id' );

        $ret = [ 'errno' => 0, 'message' => '', 'data' => [ ] ];

        // code here

        return $ret;
    }

    public function changeOrderFood() {
        $request  = $this->_app->request();
        $mpUserID = $request->get( 'mp_user_id' );
        $wxUserID = $request->get( 'wx_user_id' );
        $orderId  = $request->get( 'order_id' );
        $menuAutoID   = $request->get( 'menu_id' );
        $action   = $request->get( 'action' );

        if ($action != 1) {
            $action = -1;
        }

        $order = new Order([ Order::ORDER_ID => $orderId ]);
        if ($order->isEmpty()) {
            $res['errno'] = 1;
            $res['error'] = '未找到点单';

            return $res;
        }

        if ($order->getYmd() != intval( date( 'Ymd' ) )) {
            $res['errno'] = 1;
            $res['error'] = '点单已过期，无法修改';

            return $res;
        }

        if ($order->getIsSubmitted() == 1) {
            $res['errno'] = 1;
            $res['error'] = '点单已提交，无法修改';

            return $res;
        }

        $orderDetail = new OrderDetail([ OrderDetail::ORDER_ID => $orderId, OrderDetail::MENU_AUTO_ID => $menuAutoID ]);

        if ($orderDetail->isEmpty()) {
            //$restaurantMenu = new RestaurantMenu($menuId);
            $restaurantMenu = new RestaurantMenu([\RCRM\Model\Rcrm\RestaurantMenu::RESTAURANT_MENU_ID => $menuAutoID]);

            if ($restaurantMenu->isEmpty()) {
                return [ 'errno' => 1, 'error' => '菜单ID不存在', ];
            }

            $orderDetail->setMenuAutoID( $menuAutoID )->setMenuName( $restaurantMenu->getMenuName() )->setMpUserID( $mpUserID );
            $orderDetail->setWxUserID( $wxUserID )->setMenuCount( 0 )->setOrderID( $orderId );
            // set price
            if (strpos( $restaurantMenu->getOnSaleDay(), date( 'w' ) ) !== FALSE) { //on_sale_day
                $orderDetail->setMenuPrice( $restaurantMenu->getOnSalePrice() );
            } else {
                $orderDetail->setMenuPrice( $restaurantMenu->getMenuPrice() );
            }

            // 更新user_menu_count
            $userMenuCount =  new \RCRM\Model\Rcrm\UserMenuCount([\RCRM\Model\Rcrm\UserMenuCount::WX_USER_ID => $wxUserID,
                \RCRM\Model\Rcrm\UserMenuCount::MENU_AUTO_ID => $menuAutoID]);

            if($userMenuCount->isEmpty())
            {
                $userMenuCount->setMenuAutoID($menuAutoID)
                    ->setMenuCount(1)
                    ->setWxUserID($wxUserID)
                    ->setMpUserID($mpUserID)
                    ->insert();
            }
            else
            {
                $count = $userMenuCount->getMenuCount() + 1;
                $userMenuCount->setMenuCount($count)->save();
            }

        }
        $orderDetail->setMenuCount( $orderDetail->getMenuCount() + $action );

        if ($orderDetail->getMenuCount() > 0) {
            $orderDetail->save();
        } elseif ($orderDetail->getMenuCount() == 0) {
            $orderDetail->delete();
        }

        return [ 'errno' => 0 ];
    }

    public function getOrderDetail() {
        $request = $this->_app->request();
        $orderId = $request->get( 'order_id' );
        $order   = new Order($orderId);

        $ret = [ 'errno' => 0,
                 'data'  => [ 'checkouted' => $order->getIsSubmitted(),
                              'arr'        => HouseMemberBusiness::getOrderListForWeb( $orderId ) ] ];

        return $ret;
    }

    public function checkoutOrder() {
        $request = $this->_app->request();
        $orderId = $request->get( 'order_id' );
        $comment = $request->get( 'comment' );

        return HouseMemberBusiness::submitOrder( $orderId, $comment );
    }
}
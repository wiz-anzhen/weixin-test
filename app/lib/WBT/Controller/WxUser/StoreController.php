<?php

namespace WBT\Controller\WxUser;

use MP\Model\Mp\MpUser;
use MP\Model\Mp\Product;
use MP\Model\Mp\Store;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\OrderBusiness;
use WBT\Business\Weixin\StoreBusiness;
use WBT\Business\Weixin\ZhongaoBusiness;
use WBT\Controller\WxUserControllerBase;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\Community;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;
use Common\Helper\BaseController;

class StoreController extends WxUserControllerBase
{
    public function indexAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());


        $storeId = $this->_request->get( Store::STORE_ID );
        $store   = new Store([ Store::STORE_ID => $storeId ]);
        /*
        $mpUserID = $store->getMpUserID();
        $communityIDs = Community::fetchColumn([Community::COMMUNITY_ID],[Community::MP_USER_ID => $mpUserID]);
        foreach($communityIDs as $value)
        {
            $community = new Community([Community::COMMUNITY_ID => $value]);
            $longitudeUserAddress = $community->getLongitude();
            $latitudeAddress = $community->getLatitude();
            log_debug("11111111111111==========================".$longitudeUserAddress);
            log_debug("1111111111111111==========================".$latitudeAddress);

            $longitudeUser = $wxUser->getLongitudeuser();
            $latitudeUser = $wxUser->getLatitudeuser();
            log_debug("2222222222222==========================".$longitudeUser);
            log_debug("222222222222222==========================".$latitudeUser);
            $distance =  WxUserBusiness::getDistance($longitudeUserAddress,$latitudeAddress,$longitudeUser,$latitudeUser);
            log_debug("33333333333333333333333==========================".$distance);
        }
        */

        if ($store->isEmpty())
        {
            log_warn('非法的请求，store_id 为空');
            $this->_redirectToErrorPage('无效的商城');
        }
        $this->_view->set( 'store', $store->data() );

        $data = StoreBusiness::getStoreData( $storeId );//获取商城信息（产品)更改产品显示条件可进入business
        $this->_view->appendData( $data );

        $tmpProducts = [];
        if (count( $data['products'] ) > 0) {
            foreach ($data['products'] as $product) {
                $tmpProducts = array_merge($tmpProducts, $product);
            }
        }
        $products = [ ];
        if (count( $tmpProducts ) > 0) {
            foreach ($tmpProducts as $product) {
                $products[] = [ 'title' => $product[Product::TITLE], 'product_id' => $product[Product::PRODUCT_ID] ];
            }
        }
        $this->_view->set( 'all_products', json_encode( $products ) );
        $this->_view->set( 'show_img',true);
        $cartData = OrderBusiness::getCartData($this->_wxUserID, $storeId);
        $data = ['arr' => $cartData['data']];
        $this->_view->set( 'ini_food_num', json_encode( $data ) );

        $view = $this->_request->get('view');
        if ($view == 2) {
            $this->changeView('WBT/WxUser/Store.index2.html');
        }
    }

    public function settleAction()
    {
        $storeId = $this->_request->get( Store::STORE_ID );
        $store   = new Store([ Store::STORE_ID => $storeId ]);
        if ($store->isEmpty()) {
            log_error('非法的请求，store_id 为空');
            exit('StoreId 不能为空');
        }
        $this->_view->set( 'store', $store->data() );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $addr = $wxUser->getAddress();
        if(empty($addr))
        {
            $house = new \MP\Model\Mp\HouseMember(
                [\MP\Model\Mp\HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID(),
                \MP\Model\Mp\HouseMember::WX_USER_ID => $wxUser->getWxUserID()]);

            if(!$house->isEmpty())
            {
                $addr = $house->getHouseAddress();
                $wxUser->setAddress($addr)->update();
            }
        }

        $this->_view->set('wx_user', $wxUser->data());
        log_debug("wxUserID====",$this->_wxUserID);
        log_debug("storeId====",$storeId);
        $cartData = OrderBusiness::getCartData($this->_wxUserID, $storeId);
        log_debug("cartData====",$cartData);
        $this->_view->set('cart_data', $cartData['data']);
        $mpUserID = $store->getMpUserID();
        $payType = ConfigBusiness::mpUserConfig($mpUserID);
        $payType = $payType[MpUserConfigType::WX_PAY];
        if(!empty($payType))
        {
            $this->_view->set("pay_type",$payType);
        }
    }

    public function zhongaoAction()
    {
        $store = ['title' => '中澳网上商城'];
        $this->_view->set('store', $store);
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user', $wxUser->data());
        $categories = ZhongaoBusiness::getCategories();
        $products = ZhongaoBusiness::getProducts();
        $this->_view->set('categories', $categories);
        $this->_view->set('products', $products);

        $tmpProducts = [];
        if (count( $products ) > 0)
        {
            foreach ($products as $product)
            {
                $tmpProducts = array_merge($tmpProducts, $product);
            }
        }
        $allProducts = [ ];
        if (count( $tmpProducts ) > 0)
        {
            foreach ($tmpProducts as $product)
            {
                $allProducts[] = [ 'title' => $product[Product::TITLE], 'product_id' => $product[Product::PRODUCT_ID] ];
            }
        }
        $this->_view->set( 'all_products', json_encode( $allProducts ) );
        $this->_view->set( 'show_img', true );
        $this->changeView('WBT/WxUser/Store.index.html');
    }
}
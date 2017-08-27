<?php

namespace WBT\Controller\WxUser;

use MP\Model\Mp\Product;
use MP\Model\Mp\Store;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\OrderBusiness;
use WBT\Business\Weixin\StoreBusiness;
use WBT\Business\Weixin\ZhongaoBusiness;
use WBT\Controller\WxUserControllerBase;
use Common\Helper\BaseController;

class StoreDemoController extends BaseController
{
    public function indexAction()
    {

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
                $wxUser->setAddress($addr);
            }
        }

        $this->_view->set('wx_user', $wxUser->data());
        log_debug("wxUserID====",$this->_wxUserID);
        log_debug("storeId====",$storeId);
        $cartData = OrderBusiness::getCartData($this->_wxUserID, $storeId);
        log_debug("cartData====",$cartData);
        $this->_view->set('cart_data', $cartData['data']);
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
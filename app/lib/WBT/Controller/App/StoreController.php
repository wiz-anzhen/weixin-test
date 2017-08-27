<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\Product;
use MP\Model\Mp\Store;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\OrderBusiness;
use WBT\Business\Weixin\StoreBusiness;
use WBT\Business\Weixin\ZhongaoBusiness;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\AppUser;
class StoreController extends BaseController
{
    public function indexAction()
    {
        $storeId = $this->_request->get( Store::STORE_ID );
        $store   = new Store([ Store::STORE_ID => $storeId ]);
        if ($store->isEmpty())
        {
            log_warn('非法的请求，store_id 为空');
            $this->_redirectToErrorPage('无效的商城');
        }
        $this->_view->set( 'store', $store->data() );
        $phone = $this->_request->get( 'phone' );
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('app_user', $appUser->data());
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
        $cartData = OrderBusiness::getCartData($phone, $storeId);
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
        $phone = $this->_request->get( 'phone' );
        $appUser = new AppUser([AppUser::PHONE=>$phone]);

        $addr = $appUser->getAddress();
        if(empty($addr))
        {
            $house = new \MP\Model\Mp\HouseMember(
                [\MP\Model\Mp\HouseMember::COMMUNITY_ID => $appUser->getCurrentCommunityID(),
                \MP\Model\Mp\HouseMember::PHONE1 => $phone]);

            if(!$house->isEmpty())
            {
                $addr = $house->getHouseAddress();
                $appUser->setAddress($addr);
            }
        }

        $this->_view->set('app_user', $appUser->data());
        $cartData = OrderBusiness::getCartData($phone, $storeId);
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
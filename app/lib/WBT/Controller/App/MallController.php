<?php
/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-8-6
 * Time: 下午12:48
 */
namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\Category;
use MP\Model\Mp\MpUserConfigType;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\Store;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\StoreBusiness;
use MP\Model\Mp\Product;
use WBT\Business\Weixin\ProductBusiness;
use WBT\Business\Weixin\CateGoryBusiness;
use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use WBT\Business\Weixin\CartDetailBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\ProductComment;
use Bluefin\Data\Database;
use WBT\Business\Weixin\ProductCommentBusiness;
use MP\Model\Mp\AppUser;

class MallController extends BaseController
{

    public function listAction()
    {
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set('store_id',$storeID);
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $phone = $this->_request->get( 'phone' );
        $categoryID = $this->_request->get( 'category_id' );
        $categoryName = $this->_request->get( 'name' );
        $paging = [];
        $ranking = [Product::SORT_NO];
        /*
        $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID];

        if(!empty($categoryID))
        {
            $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID,Product::CATEGORY_ID => $categoryID];
        }

        $data = ProductBusiness::getList($condition,$paging,$ranking,null);
*/
        $con = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID];
        $dataCategory = CateGoryBusiness::getList($con,$paging,$ranking,null);

        $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID];
        //第一次进入，默认取分类目录里面的一个目录显示

        if(empty($categoryID))//首次进入,显示精选商品列表
        {
         //   $dataDefault = $dataCategory[0];
        //    unset($dataCategory[0]);
        //    $categoryID = $dataDefault[Category::CATEGORY_ID];
         //   $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID,Product::CATEGORY_ID => $categoryID];
            $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID];
            $categoryName = '精选商品';
        }
        else//非首次进入，$categoryID 不为空
        {
            $categoryName = $this->_request->get( 'name' );
            if($categoryName == '精选商品')
            {//不对数组进行删除操作，条件中不含目录id值
                $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID];
            }
            else
            {//不是精选商品，对传来的categoryid，从数组中删除对应下标，同时将‘精选商品’拼接到数组中，作为目录列表一项
                foreach($dataCategory as $k => $v)
                {
                    //找到数组中，category_id值为传过来的 下标号，进行删除该标号对应操作
                    //或者不用删除，变更title值为‘精选商品’
                    if($v[Category::CATEGORY_ID] == $categoryID)
                    {
                     //   $dataDefault = $dataCategory[$k];
                     //   unset($dataCategory[$k]);
                        $dataCategory[$k][Category::TITLE] = '精选商品';
                        break;
                    }
                }
                $condition = [Product::MP_USER_ID => $mpUserID,Product::STORE_ID => $storeID,Product::CATEGORY_ID => $categoryID];
            }
        }


//  拼接产品表中   is_delete为 0 ,is_on_shelf == 1 的产品，也就是没有被删除的产品
        $expr = " is_delete = 0 and is_on_shelf = 1";
        $con =  new \Bluefin\Data\DbCondition($expr);
        $condition[] = $con;
        $data = ProductBusiness::getList($condition,$paging,$ranking,null);
        if(empty($data))
        {
            $this->_view->set('store_id',$storeID);
        }
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("product",$data);
        $this->_view->set("category",$dataCategory);
        $this->_view->set("category_name",$categoryName);
        //购物车数量
        //$wxUserID = $this->_wxUserID;
        $this->_view->set("phone",$phone);
        $cart = new Cart([Cart::MP_USER_ID => $mpUserID,Cart::WX_USER_ID => $phone,Cart::STORE_ID => $storeID]);
        if(!$cart->isEmpty())
        {
            $cartID = $cart -> getCartID();
            $condetail = [CartDetail::CART_ID => $cartID];
            $ranking = null;
            $dataDetail = CartDetailBusiness::getCartDetailList($condetail,$paging,$ranking,null);
            $num = 0;
            foreach($dataDetail as $v)
            {
                $num += $v[CartDetail::COUNT];
            }
            $this->_view->set('num',$num);
        }
    }
    public function detailAction()
    {
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set('store_id',$storeID);
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $this->_view->set("mp_user_id",$mpUserID);
        $productID = $this -> _request-> get('product_id');
        $condition = [Product::MP_USER_ID => $mpUserID,Product::PRODUCT_ID =>$productID];
        $paging = [];
        $ranking = null;
        $data = ProductBusiness::getList($condition,$paging,$ranking,null);
        $this->_view->set("p",$data[0]);
        //number_format(10000, 2, '.', '');//10000.00
        $shengPrice = number_format($data[0][Product::REFERENCE_PRICE]-$data[0][Product::PRICE],2,'.','');
        $this->_view->set("shengPrice",$shengPrice);
        $phone = $this->_request->get( 'phone' );
        $this->_view->set("phone",$phone);
        $storeID = $data[0][Product::STORE_ID];
        $productID = $data[0][Product::PRODUCT_ID];
        //购物车数量
        $cart = new Cart([Cart::MP_USER_ID => $mpUserID,Cart::WX_USER_ID => $phone,Cart::STORE_ID => $storeID]);
        if(!$cart->isEmpty())
        {
            $cartID = $cart -> getCartID();
            $condetail = [CartDetail::CART_ID => $cartID];
            $dataDetail = CartDetailBusiness::getCartDetailList($condetail,$paging,$ranking,null);
            $num = 0;
            foreach($dataDetail as $v)
            {
                $num += $v[CartDetail::COUNT];
            }
            $this->_view->set('num',$num);
        }
//读取评论
        $paging = [];
        $outputColumns = ProductComment::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking = [ProductComment::COMMENT_TIME => true];
        $condition = [ProductComment::MP_USER_ID => $mpUserID,ProductComment::WX_USER_ID => $phone,ProductComment::PRODUCT_ID => $productID];
        $data = ProductCommentBusiness::getProductCommentList($condition,$paging,$ranking,null);
        foreach($data as $k => $v)
        {
            $data[$k][ProductComment::NICK] = substr_unicode($data[$k][ProductComment::NICK],0,1);
            $data[$k][ProductComment::COMMENT_TIME] = substr_unicode($data[$k][ProductComment::COMMENT_TIME],0,10);
            if(empty($data[$k][ProductComment::COMMENT]))
            {
                $data[$k][ProductComment::COMMENT]='目前还没有评论';
            }
        }
        //log_debug("-------------nick--------------",$data);
        if(!empty($data))
        {
            $this -> _view -> set("productComment",$data);
        }
        else
        {
            $this -> _view -> set("comment",true);
        }

    }

    //购物车页面
    public function shoppingAction()
    {
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $phone = $this->_request->get( 'phone' );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set("store_id",$storeID);
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("phone",$phone);
        log_debug("[$mpUserID][$phone][$storeID]");
        $cart = new Cart([Cart::MP_USER_ID => $mpUserID,Cart::WX_USER_ID => $phone,Cart::STORE_ID => $storeID]);
        if(!$cart->isEmpty())
        {
            $cartID = $cart -> getCartID();

            $condition = [CartDetail::CART_ID => $cartID];

            //取购物详情表中，count ！= 0 的数据
            $expr = " count > 0";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;

            $paging = [];
            $ranking = null;
            $data = CartDetailBusiness::getCartDetailList($condition,$paging,$ranking,null);
            //根据产品ID获取对应的产品详情
            $productIDArr = [];
            foreach($data as $v)
            {
                $productIDArr[] = $v[CartDetail::PRODUCT_ID];
            }
            $connProduct = [Product::PRODUCT_ID => $productIDArr];
            $dataProduct = ProductBusiness::getList($connProduct,$paging,$ranking,null);
            //拼接单一产品总数量、产品总价，所有产品总价，所有产品总数量
            $totalMoney = 0.00;
            $totalNum = 0;
            foreach($productIDArr as $k => $v)
            {
                $productID = $productIDArr[$k];
                $cartDetail = new CartDetail([CartDetail::PRODUCT_ID => $productID,CartDetail::CART_ID => $cartID]);
               log_debug("-----------------------[productID:$productID][$cartID]----------------------------");
                if(!$cartDetail -> isEmpty())
                {
                    $num = $cartDetail -> getCount();
                    $dataProduct[$k]['num'] = $num;
                    $priceA = number_format($num * $dataProduct[$k][Product::PRICE],2,'.','');
                  //  $priceA = $num * $dataProduct[$k][Product::PRICE];
                    $dataProduct[$k]['priceA'] = $priceA;
                    $totalMoney = number_format($totalMoney + $priceA,2,'.','');
                   // $totalMoney = $totalMoney + $priceA;
                    $totalNum = $totalNum + $num;
                }

            }
     //       log_debug("------------------------parr----------------",$dataProduct);
    //        log_debug("------------------------totalMoney----------------",$totalMoney);
            $this->_view->set("productArr",$dataProduct);
            $this->_view->set("count",count($dataProduct));
            $this->_view->set("cart_id",$cartID);
            $this->_view->set("total_money",$totalMoney);
            $this->_view->set("total_num",$totalNum);

        }
    }

    public function shoppingEmptyAction()
    {
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $this->_view->set("mp_user_id",$mpUserID);
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set('store_id',$storeID);
        $phone = $this->_request->get( 'phone' );
        $this->_view->set('phone',$phone);
   //     log_debug("----------------------[$mpUserID][$communityID][$storeID]----------------");
    }

    public function orderDetailAction()
    {
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set("community_id",$communityID);
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set('store_id',$storeID);


        $mpUserID = $this->_request->get( 'mp_user_id' );
        $phone = $this->_request->get( 'phone' );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set("store_id",$storeID);
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("phone",$phone);



        $cart = new Cart([Cart::MP_USER_ID => $mpUserID,Cart::WX_USER_ID => $phone,Cart::STORE_ID => $storeID]);
        if(!$cart->isEmpty())
        {
            $cartID = $cart -> getCartID();
            $condition = [CartDetail::CART_ID => $cartID];
            //取购物详情表中，count ！= 0 的数据
            $expr = " count > 0";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;

            $paging = [];
            $ranking = null;
            $data = CartDetailBusiness::getCartDetailList($condition,$paging,$ranking,null);
            //根据产品ID获取对应的产品详情
            $productIDArr = [];
            foreach($data as $v)
            {
                $productIDArr[] = $v[CartDetail::PRODUCT_ID];
            }
            $connProduct = [Product::PRODUCT_ID => $productIDArr];
            //产品详情
            $dataProduct = ProductBusiness::getList($connProduct,$paging,$ranking,null);

            $totalMoney = $totalNum = 0;
            foreach($productIDArr as $k => $v)
            {
                $productID = $productIDArr[$k];
                $cartDetail = new CartDetail([CartDetail::PRODUCT_ID => $productID,CartDetail::CART_ID => $cartID]);
                if(!$cartDetail -> isEmpty())
                {
                    $num = $cartDetail -> getCount();
                    $dataProduct[$k]['num'] = $num;
                    $priceA = $num * $dataProduct[$k][Product::PRICE];
                    $dataProduct[$k]['priceA'] = $priceA;
                    $totalMoney = $totalMoney + $priceA;
                    $totalNum = $totalNum + $num;
                }

            }
            $this->_view->set("productArr",$dataProduct);
            $this->_view->set("cart_id",$cartID);
            $this->_view->set("total_money",$totalMoney);
            $this->_view->set("total_num",$totalNum);
        }
    //获取用户信息
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if(!$appUser->isEmpty())
        {
            $nick = $appUser->getNick();
            $phone = $appUser->getPhone();
            $address = $appUser->getAddress();
            if(!$address)$address = "暂无";
            if(!$phone)$phone = "暂无";
            $this->_view->set("nick",$nick);
            $this->_view->set("phone",$phone);
            $this->_view->set("address",$address);
            log_debug("-----------------[$nick][$phone][$address]--------------");
        }
        $payType = ConfigBusiness::mpUserConfig($mpUserID);
        $payType = $payType[MpUserConfigType::WX_PAY];
        if(!empty($payType))
        {
            $this->_view->set("pay_type",$payType);
        }
    }
}
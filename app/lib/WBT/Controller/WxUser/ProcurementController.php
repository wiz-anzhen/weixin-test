<?php

namespace WBT\Controller\WxUser;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\ProductComment;
use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\StoreBusiness;
use WBT\Business\Weixin\OrderBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\Cart;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Part;
use MP\Model\Mp\CartDetail;
use MP\Model\Mp\ProcurementOrderDetail;
use MP\Model\Mp\ProcurementCart;
use MP\Model\Mp\ProcurementCartDetail;
use WBT\Business\Weixin\CartDetailBusiness;
use Common\Helper\BaseController;
use WBT\Controller\WxUserControllerBase;


use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\ProductUnitType;
use MP\Model\Mp\ProcurementOrderStatus;

class ProcurementController extends WxUserControllerBase
{
    //发送给供应商确认报价单
    public function inquiryAction()
    {

        $wxUserID = $this->_request->getQueryParam("wx_user_id");
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $this->_view->set('wx_user', $wxUser->data());

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $categoryId = $this->_request->getQueryParam( Product::CATEGORY_ID );
        $category = new Category([Category::CATEGORY_ID => $categoryId]);
        $store = new Store([Store::STORE_ID => $category->getStoreID()]);


        $this->_view->set('store', $store->data());
        $this->_view->set('category', $category->data());
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );

        $communityId = $category->getCommunityID();
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $paging = []; // 先初始化为空
        $outputColumns = Product::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $condition     = [ Product::CATEGORY_ID => $categoryId,Product::IS_DELETE => "0" ];

        $ranking       = [ Product::SORT_NO ];
        $data          = StoreBusiness::getProductList( $condition, $paging, $ranking, $outputColumns );

        $outputColumns = Product::s_metadata()->getFilterOptions();

        $showColumns = [ Product::TITLE,
            Product::PRICE,
            Product::PRODUCT_UNIT,
            Product::COMMENT,
        ];

        $table               = Table::fromDbData( $data, $outputColumns, Product::PRODUCT_ID, $paging,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );

    }

    //错误页面
    public function rightAction()
    {

    }

    //订货
    public function shopAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'order')
            {
                $readPower = true;
                 break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        //找出对应档口
        $part = $house->getPartID();
        log_debug("===============================".$part);
        $part = explode(",",$part);
        $partData=[];
        foreach($part as $value)
        {
            if(!empty($value))
            {
                $progress = explode("_",$value);
                $partData[] = $progress[1];
            }
        }
        log_debug("===============================",$partData);
        //找出对应供应商
        $supplyID = [];
        foreach($partData as $key => $value)
        {
            if(!empty($value))
            {
                $part = new Part([Part::PART_ID => $value]);
                $supply = $part->getBoundStoreID();
                $supply = explode(",",$supply);
                foreach($supply as $v)
                {
                    if(!empty($v))
                    {
                        $progress = explode("_",$v);
                        $supplyID[] = $progress[1];
                    }

                }
            }
        }
        log_debug("===============================",$supplyID);
        $supplyID = array_unique($supplyID);
        log_debug("===============================",$supplyID);
        if(!empty($supplyID))
        {
            $storeData = Store::fetchRows(['*'],[Store::STORE_ID => $supplyID,Store::IS_DELETE => '0']);
        }
        else
        {
            $storeData = [];
        }

        $this->_view->set( 'store_data', $storeData );

        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

    }

    //订货挑选货品
    public function shopSelectAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set( 'store_id', $storeID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        //找出对应的档口
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUser->getWxUserID(),HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $part = $house->getPartID();
        $part = explode(",",$part);
        $partData=[];
        foreach($part as $value)
        {
            if(!empty($value))
            {
                $progress = explode("_",$value);
                $partData[] = $progress[1];
            }
        }
        $partDataProgress = [];
        foreach($partData as $key => $value)
        {
            $part = new Part([Part::PART_ID => $value]);
            $boundStore = $part->getBoundStoreID();
            $boundStoreProgress = explode(",",$boundStore);
            $boundStoreID = [];
            foreach($boundStoreProgress as $k => $v)
            {
                if(!empty($v))
                {
                    $progress = explode("_",$v);
                    $boundStoreID[] = $progress[1];
                }
            }
            if(strict_in_array($storeID,$boundStoreID))
            {
                $partDataProgress[] = $value;
            }
        }

        if(!empty($partDataProgress))
        {
            $partDataContent = Part::fetchRows(['*'],[Part::PART_ID => $partDataProgress]);
        }
        else
        {
            $partDataContent = [];
        }
        $this->_view->set( 'part_data', $partDataContent );

        $partID = $this->_request->get( 'part_id' );
        if(empty($partID))
        {
            $partID = $partDataContent[0][Part::PART_ID];
        }
        $this->_view->set( 'part_id', $partID );


        $category = new Category([Category::STORE_ID => $storeID,Category::IS_ON_SHELF => 1]);

        $productData = Product::fetchRows(['*'],[Product::CATEGORY_ID => $category->getCategoryID(),Product::IS_ON_SHELF => '1']);

        //查看有无购物车

        //匹配购物车内每样商品的数量
        foreach($productData as $key => $value)
        {
            $productData[$key][Product::PRODUCT_UNIT] = ProductUnitType::getDisplayName($value[Product::PRODUCT_UNIT]);

        }

        $cart = new ProcurementCart([ProcurementCart::MP_USER_ID => $category->getMpUserID(),ProcurementCart::WX_USER_ID => $wxUser->getWxUserID(),ProcurementCart::STORE_ID => $storeID,ProcurementCart::PART_ID => $partID]);
        if(!$cart->isEmpty())
        {
            $cartID = $cart ->getProcurementCartID();

            $condition = [ProcurementCartDetail::CART_ID => $cartID];

            //取购物详情表中，count ！= 0 的数据
            $expr = " count > 0";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;

            $paging = [];
            $ranking = null;
            $dataCartDetail =  ProcurementCartDetail::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );


            $this->_view->set("cart_id",$cartID);

            //匹配购物车内每样商品的数量
            foreach($productData as $key => $value)
            {
                   $productData[$key]['num'] = "0.0";
                   foreach($dataCartDetail as $detail)
                   {
                       if($value[Product::PRODUCT_ID] == $detail[ProcurementCartDetail::PRODUCT_ID])
                       {
                           $productData[$key]['num'] = $detail[ProcurementCartDetail::COUNT];
                           $productData[$key]['num'] = number_format($productData[$key]['num'], 1,'.','');
                           if($productData[$key]['num'] == 0)
                           {
                               $productData[$key]['num'] = "0.0";
                           }

                       }

                   }
            }

        }
        else
        {
            $this->_view->set("cart_id",false);
        }

        $this->_view->set( 'product_data', $productData );
    }

    //订货下单
    public function shopVerifyAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $selfType = $this->_request->get( 'self_type' );
        $this->_view->set( 'self_type', $selfType );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set("store_id",$storeID);
        $cartIDs = ProcurementCart::fetchRows(["*"],[ProcurementCart::WX_USER_ID => $wxUser->getWxUserID(),ProcurementCart::STORE_ID => $storeID]);
        $dataDetailContent = [];
        $totalPrice = 0;
        log_debug("=================",$cartIDs);
        foreach($cartIDs as $k => $v)
        {
            $cartID = $v[ProcurementCart::PROCUREMENT_CART_ID];
            $conDetail = [CartDetail::CART_ID => $cartID];
            $ranking = null;
            $dataDetail = ProcurementCartDetail::fetchRowsWithCount( [ '*' ], $conDetail, null, $ranking, $paging, $outputColumns );
            $cartTotalPrice = "";
            foreach($dataDetail as $key => $value)
            {
                $product = new Product([Product::PRODUCT_ID => $value[CartDetail::PRODUCT_ID]]);
                $dataDetail[$key]['title'] = $product->getTitle();
                $dataDetail[$key]['price'] = $product->getPrice();
                $dataDetail[$key]['product_unit'] = ProductUnitType::getDisplayName($product->getProductUnit());
                $dataProgress = explode("/",$dataDetail[$key]['product_unit']);
                $dataDetail[$key]['product_unit_name'] = $dataProgress[1];
                $dataDetail[$key][CartDetail::COUNT] = $value[CartDetail::COUNT];
                $dataDetail[$key]['total_price'] = $dataDetail[$key]['price']*$value[CartDetail::COUNT];
                $cartTotalPrice += $dataDetail[$key]['total_price'] ;
                if($value[CartDetail::COUNT] == 0)
                {
                    unset($dataDetail[$key]);
                }
            }
            $dataDetailContent[$k]['content'] = $dataDetail;
            $dataDetailContent[$k]['price'] = $cartTotalPrice;
            $partID = $v[ProcurementCart::PART_ID];
            $part = new Part([Part::PART_ID => $partID]);
            $dataDetailContent[$k]['part'] = $part->getTitle();
            $totalPrice += (float)$cartTotalPrice ;
        }

        log_debug("=================",$dataDetailContent);
        $this->_view->set( 'data_detail', $dataDetailContent );
        $this->_view->set( 'cart_total_price', $totalPrice );
    }

    //订货完成
    public function shopSuccessAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );

        $type = $this->_request->get( 'type' );
        $this->_view->set( 'type', $type );
        $orderSelf = $this->_request->get( 'order_self' );
        $this->_view->set( 'order_self', $orderSelf );
        $title = "";
        if($type == 'shop')
        {
            $title = "下单成功";
        }
        elseif($type == 'chef')
        {
            $title = "确认完成";
        }
        elseif($type == 'supply')
        {
            $title = "通知确认成功";
        }
        elseif($type == 'examine')
        {
            $title = "验货成功";
        }
        elseif($type == 'refund')
        {
            $title = "退货提交完成";
        }

        $this->_view->set( 'title', $title );

    }

    //厨师长确认
    public function chefVerifyAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $wxUserID = $wxUser->getWxUserID();

        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);

        $housePower = $house->getMemberType();

        if ($housePower != "chef")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }

        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        $storeData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => '0']);


        foreach($storeData as $key => $value)
        {
            $count = ProcurementOrder::fetchCount([ProcurementOrder::STORE_ID => $value[Store::STORE_ID],ProcurementOrder::STATUS => "chef_verify"]);
            $storeData[$key]['order_count'] = $count;

        }

        $this->_view->set( 'store_data', $storeData );

        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
    }

    //厨师长确认
    public function chefOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $storeID = $this->_request->get( 'store_id' );
        $store =  new Store([Store::STORE_ID => $storeID]);
        $this->_view->set( 'store_title', $store->getTitle() );
        $this->_view->set( 'store_id', $storeID );


        $condition = [ProcurementOrder::STORE_ID => $storeID,ProcurementOrder::STATUS => ProcurementOrderStatus::CHEF_VERIFY];

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking       = [ ProcurementOrder::CREATE_TIME => true ]);

        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }


        $this->_view->set( 'order_data', $orderData );
    }


    //厨师长确认
    public function chefOrderDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeTitle = $this->_request->get( 'store_title' );
        $this->_view->set( 'store_title', $storeTitle );
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }
        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );

    }


    //等待供应商确认
    public function supplyVerifyAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'order')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }

        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();

        //找出对应档口
        $part = $house->getPartID();
        log_debug("===============================".$part);
        $part = explode(",",$part);
        $partData=[];
        foreach($part as $value)
        {
            if(!empty($value))
            {
                $progress = explode("_",$value);
                $partData[] = $progress[1];
            }
        }
        log_debug("===============================",$partData);
        //找出对应供应商
        $supplyID = [];
        foreach($partData as $key => $value)
        {
            if(!empty($value))
            {
                $part = new Part([Part::PART_ID => $value]);
                $supply = $part->getBoundStoreID();
                $supply = explode(",",$supply);
                foreach($supply as $v)
                {
                    if(!empty($v))
                    {
                        $progress = explode("_",$v);
                        $supplyID[] = $progress[1];
                    }

                }
            }
        }
        log_debug("===============================",$supplyID);
        $supplyID = array_unique($supplyID);
        log_debug("===============================",$supplyID);
        if(!empty($supplyID))
        {
            $storeData = Store::fetchRows(['*'],[Store::STORE_ID => $supplyID,Store::IS_DELETE => '0']);
        }
        else
        {
            $storeData = [];
        }

        foreach($storeData as $key => $value)
        {
            $count = ProcurementOrder::fetchCount([ProcurementOrder::STORE_ID => $value[Store::STORE_ID],ProcurementOrder::STATUS => "supply_verify"]);
            $storeData[$key]['order_count'] = $count;

        }
        $this->_view->set( 'store_data', $storeData );

        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

    }

    //等待供应商确认
    public function supplyOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $storeID = $this->_request->get( 'store_id' );
        $store =  new Store([Store::STORE_ID => $storeID]);
        $this->_view->set( 'store_title', $store->getTitle() );
        $this->_view->set( 'store_id', $storeID );




        $condition = [ProcurementOrder::STORE_ID => $storeID,ProcurementOrder::STATUS => ProcurementOrderStatus::SUPPLY_VERIFY];

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);

        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        $this->_view->set( 'order_data', $orderData );
    }


    //等待供应商确认
    public function supplyOrderDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeTitle = $this->_request->get( 'store_title' );
        $this->_view->set( 'store_title', $storeTitle );
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );

    }

    //验货
    public function examineAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'examine')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }

        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();

        //找出对应档口
        $part = $house->getPartID();
        log_debug("===============================".$part);
        $part = explode(",",$part);
        $partData=[];
        foreach($part as $value)
        {
            if(!empty($value))
            {
                $progress = explode("_",$value);
                $partData[] = $progress[1];
            }
        }
        log_debug("===============================",$partData);
        //找出对应供应商
        $supplyID = [];
        foreach($partData as $key => $value)
        {
            if(!empty($value))
            {
                $part = new Part([Part::PART_ID => $value]);
                $supply = $part->getBoundStoreID();
                $supply = explode(",",$supply);
                foreach($supply as $v)
                {
                    if(!empty($v))
                    {
                        $progress = explode("_",$v);
                        $supplyID[] = $progress[1];
                    }

                }
            }
        }
        log_debug("===============================",$supplyID);
        $supplyID = array_unique($supplyID);
        log_debug("===============================",$supplyID);
        if(!empty($supplyID))
        {
            $storeData = Store::fetchRows(['*'],[Store::STORE_ID => $supplyID,Store::IS_DELETE => '0']);
        }
        else
        {
            $storeData = [];
        }

        foreach($storeData as $key => $value)
        {
            $count = ProcurementOrder::fetchCount([ProcurementOrder::STORE_ID => $value[Store::STORE_ID],ProcurementOrder::STATUS => "examine"]);
            $storeData[$key]['order_count'] = $count;

        }

        $this->_view->set( 'store_data', $storeData );

        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
    }

    //验货
    public function examineOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $storeID = $this->_request->get( 'store_id' );
        $store =  new Store([Store::STORE_ID => $storeID]);
        $this->_view->set( 'store_title', $store->getTitle() );
        $this->_view->set( 'store_id', $storeID );


        $condition = [ProcurementOrder::STORE_ID => $storeID,ProcurementOrder::STATUS => ProcurementOrderStatus::EXAMINE];

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);

        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }


    //验货
    public function examineOrderDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeTitle = $this->_request->get( 'store_title' );
        $this->_view->set( 'store_title', $storeTitle );
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );

    }

    //等待供应商验货确认
    public function supplyExamineAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'examine')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }

        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        //找出对应档口
        $part = $house->getPartID();
        log_debug("===============================".$part);
        $part = explode(",",$part);
        $partData=[];
        foreach($part as $value)
        {
            if(!empty($value))
            {
                $progress = explode("_",$value);
                $partData[] = $progress[1];
            }
        }
        log_debug("===============================",$partData);
        //找出对应供应商
        $supplyID = [];
        foreach($partData as $key => $value)
        {
            if(!empty($value))
            {
                $part = new Part([Part::PART_ID => $value]);
                $supply = $part->getBoundStoreID();
                $supply = explode(",",$supply);
                foreach($supply as $v)
                {
                    if(!empty($v))
                    {
                        $progress = explode("_",$v);
                        $supplyID[] = $progress[1];
                    }

                }
            }
        }
        log_debug("===============================",$supplyID);
        $supplyID = array_unique($supplyID);
        log_debug("===============================",$supplyID);
        if(!empty($supplyID))
        {
            $storeData = Store::fetchRows(['*'],[Store::STORE_ID => $supplyID,Store::IS_DELETE => '0']);
        }
        else
        {
            $storeData = [];
        }


        foreach($storeData as $key => $value)
        {
            $count = ProcurementOrder::fetchCount([ProcurementOrder::STORE_ID => $value[Store::STORE_ID],ProcurementOrder::STATUS => "supply_examine"]);
            $storeData[$key]['order_count'] = $count;

        }
        $this->_view->set( 'store_data', $storeData );

        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
    }

    //等待供应商验货确认
    public function supplyExamineOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $storeID = $this->_request->get( 'store_id' );
        $store =  new Store([Store::STORE_ID => $storeID]);
        $this->_view->set( 'store_title', $store->getTitle() );
        $this->_view->set( 'store_id', $storeID );


        $condition = [ProcurementOrder::STORE_ID => $storeID,ProcurementOrder::STATUS => ProcurementOrderStatus::SUPPLY_EXAMINE];

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);

        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        $this->_view->set( 'order_data', $orderData );
    }

    //等待供应商验货确认
    public function supplyExamineOrderDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeTitle = $this->_request->get( 'store_title' );
        $this->_view->set( 'store_title', $storeTitle );
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );
    }
    //退货
    public function returnAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;

        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'refund')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }

        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        //找出对应档口
        $part = $house->getPartID();
        log_debug("===============================".$part);
        $part = explode(",",$part);
        $partData=[];
        foreach($part as $value)
        {
            if(!empty($value))
            {
                $progress = explode("_",$value);
                $partData[] = $progress[1];
            }
        }
        log_debug("===============================",$partData);
        //找出对应供应商
        $supplyID = [];
        foreach($partData as $key => $value)
        {
            if(!empty($value))
            {
                $part = new Part([Part::PART_ID => $value]);
                $supply = $part->getBoundStoreID();
                $supply = explode(",",$supply);
                foreach($supply as $v)
                {
                    if(!empty($v))
                    {
                        $progress = explode("_",$v);
                        $supplyID[] = $progress[1];
                    }

                }
            }
        }
        log_debug("===============================",$supplyID);
        $supplyID = array_unique($supplyID);
        log_debug("===============================",$supplyID);
        if(!empty($supplyID))
        {
            $storeData = Store::fetchRows(['*'],[Store::STORE_ID => $supplyID,Store::IS_DELETE => '0']);
        }
        else
        {
            $storeData = [];
        }


        foreach($storeData as $key => $value)
        {
            $count = ProcurementOrder::fetchCount([ProcurementOrder::STORE_ID => $value[Store::STORE_ID],ProcurementOrder::STATUS => "finished"]);
            $storeData[$key]['order_count'] = $count;

        }

        $this->_view->set( 'store_data', $storeData );

        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
    }

    //退货
    public function returnOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $storeID = $this->_request->get( 'store_id' );
        $store =  new Store([Store::STORE_ID => $storeID]);
        $this->_view->set( 'store_title', $store->getTitle() );
        $this->_view->set( 'store_id', $storeID );

        $monthBefore = strtotime("-2 month");
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);

        $condition = [$con,ProcurementOrder::STORE_ID => $storeID,ProcurementOrder::STATUS => ProcurementOrderStatus::FINISHED];

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }

    //退货
    public function returnOrderDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeTitle = $this->_request->get( 'store_title' );
        $this->_view->set( 'store_title', $storeTitle );
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );

    }

    //退货
    public function returnApplyAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeTitle = $this->_request->get( 'store_title' );
        $this->_view->set( 'store_title', $storeTitle );


        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );
        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $productID = $this->_request->get( 'product_id' );
        $productIDProgress = explode("part",$productID);
        $productID = $productIDProgress[0];
        $partID = $productIDProgress[1];
        $this->_view->set( 'part_id', $partID );
        $this->_view->set( 'product_id', $productID );
        $product = new Product([Product::PRODUCT_ID => $productID]);
        $this->_view->set( 'store_id', $product->getStoreID() );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID ,ProcurementOrderDetail::PRODUCT_ID => $productID,ProcurementOrderDetail::PART_ID => $partID]);
        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }

        $this->_view->set( 'order_detail', $orderDetailData[0] );

    }

    //退货
    public function returnProgressAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $storeID = $this->_request->get( 'store_id' );
        $store =  new Store([Store::STORE_ID => $storeID]);
        $communityBound = new Community([Community::COMMUNITY_ID => $store->getBoundCommunityID()]);
        $phone = $communityBound->getPhone();
        if(empty($phone))
        {
            $phone = "暂无";
        }
        $this->_view->set( 'phone', $phone );
        $this->_view->set( 'store_title', $store->getTitle() );
        $this->_view->set( 'store_id', $storeID );

        $condition = [ProcurementOrder::STORE_ID => $storeID];

        $monthBefore = strtotime("-1 month");
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition[] = $con;


        $exprWx = sprintf("`status` = '%s' or `status` = '%s' ","refund","refund_finished");
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition[] = $con;


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ProcurementOrder::STATUS  , ProcurementOrder::CREATE_TIME => true ]);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        foreach($orderData as $key => $value)
        {
            if($value[ProcurementOrder::STATUS] == 'refund')
            {
                $orderData[$key]['refund'] = "等待供应商确认";
            }
            elseif($value[ProcurementOrder::STATUS] == 'refund_finished')
            {
                $orderData[$key]['refund'] = "供应商已经确认";
            }
            $orderDetailData = new  ProcurementOrderDetail([ProcurementOrderDetail::ORDER_ID => $value[ProcurementOrder::ORDER_ID]]);
            $orderData[$key]['detail_title'] = $orderDetailData->getTitle();
            $partID = $orderDetailData->getPartID();
            $part = new Part([Part::PART_ID => $partID]);
            $orderData[$key]['part'] = $part->getTitle();
            $orderData[$key]['detail_count'] = $orderDetailData->getCount();
            $orderData[$key]['detail_price'] = ($orderDetailData->getCount())*($orderDetailData->getPrice());
            $orderData[$key]['one_price'] = $orderDetailData->getPrice();
            $orderData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($orderDetailData->getProductUnit());
        }

        $this->_view->set( 'order_data', $orderData );
    }



    //等待供应商验货确认
    public function detailAction()
    {
        $templateType = $this->_request->get( 'template_type' );
        $this->_view->set( 'template_type', $templateType );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $storeID = $orderData->getStoreID();
        $store = new Store([Store::STORE_ID => $storeID]);
        $storeTitle = $store->getTitle();
        $this->_view->set( 'store_title', $storeTitle );


        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );

    }

    // 老板查看订单

    public function managerOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();
        if($communityType != CommunityType::PROCUREMENT_TOTAL)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $supplyID=$restaurantID =  $status = $orderTimeStart = $orderTimeEnd = null;

        $restaurantID    = $this->_request->get( 'restaurant_id' );
        $supplyID    = $this->_request->get( 'supply_id' );
        $status  = $this->_request->get( 'status' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');




        $this->_view->set('restaurant_id',$restaurantID);
        $this->_view->set('supply_id',$supplyID);
        $this->_view->set('status', $status);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);

        $condition = [];
        $condition[ProcurementOrder::MP_USER_ID] = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $monthBefore = strtotime("-2 month");
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [];

        $restaurantData = Restaurant::fetchRows(["*"],[Restaurant::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityIds = [];
        foreach($restaurantData as $key => $value)
        {
            $communityIds[] = $value[Restaurant::BOUND_COMMUNITY_ID];
        }


        $this->_view->set( "restaurant_data", $restaurantData );

        if (!empty($restaurantID))
        {
            $condition[ProcurementOrder::COMMUNITY_ID] = $restaurantID;
            $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $restaurantID,Store::IS_DELETE => 0]);
        }
        else
        {
            $supplyData = "";
            $condition[ProcurementOrder::COMMUNITY_ID] = $communityIds;
        }
        $this->_view->set( "supply_data", $supplyData );
        $statusDataAll = ProcurementOrderStatus::getDictionary();
        $statusData = [];
        foreach($statusDataAll as $key  => $value)
        {
            if($key != "none")
            {
                $statusData[$key] = $value;
            }

        }
        $this->_view->set( "status_data", $statusData);

        if (!empty($supplyID))
        {
            $condition[ProcurementOrder::STORE_ID] = $supplyID;
        }


        if(!empty($orderTimeStart))
        {
             if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
            }

            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        else
        {
            $condition[] = $con;
        }

        if(!empty($status))
        {
           $condition[ProcurementOrder::STATUS] = $status;
        }


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }

    // 老板查看订单

    public function managerSelectAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();
        if($communityType != CommunityType::PROCUREMENT_TOTAL)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());



        $supplyID=$restaurantID =  $status = $orderTimeStart = $orderTimeEnd = null;

        $restaurantID    = $this->_request->get( 'restaurant_id' );
        $supplyID    = $this->_request->get( 'supply_id' );
        $status  = $this->_request->get( 'status' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');




        $this->_view->set('restaurant_id',$restaurantID);
        $this->_view->set('supply_id',$supplyID);
        $this->_view->set('status', $status);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);

        $condition = [];
        $condition[ProcurementOrder::MP_USER_ID] = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $monthBefore = strtotime("-2 month");

        $monthCurrentTwo = date("Y-m-d",$monthBefore);
        $monthCurrent = date("Y-m-d");
        $this->_view->set("monthCurrentTwo",$monthCurrentTwo);
        $this->_view->set("monthCurrent",$monthCurrent);
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [];

        $restaurantData = Restaurant::fetchRows(["*"],[Restaurant::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityIds = [];
        foreach($restaurantData as $key => $value)
        {
            $communityIds[] = $value[Restaurant::BOUND_COMMUNITY_ID];
        }


        $this->_view->set( "restaurant_data", $restaurantData );

        if (!empty($restaurantID))
        {
            $condition[ProcurementOrder::COMMUNITY_ID] = $restaurantID;
            $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $restaurantID,Store::IS_DELETE => 0]);
        }
        else
        {
            $supplyData = "";
            $condition[ProcurementOrder::COMMUNITY_ID] = $communityIds;
        }
        $this->_view->set( "supply_data", $supplyData );
        $statusDataAll = ProcurementOrderStatus::getDictionary();
        $statusData = [];
        foreach($statusDataAll as $key  => $value)
        {
            if($key != "none")
            {
                $statusData[$key] = $value;
            }

        }
        $this->_view->set( "status_data", $statusData);

        if (!empty($supplyID))
        {
            $condition[ProcurementOrder::STORE_ID] = $supplyID;
        }


        if(!empty($orderTimeStart))
        {
            if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
            }

            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        else
        {
            $condition[] = $con;
        }

        if(!empty($status))
        {
            $condition[ProcurementOrder::STATUS] = $status;
        }


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }


    // 老板查看订单

    public function managerMonthAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();
        if($communityType != CommunityType::PROCUREMENT_TOTAL)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $month    = $this->_request->get( 'month' );
        if(empty($month))
        {
            $lastMonth = strtotime("-1 month");
            $month = date("Y-m",$lastMonth);
        }
        $this->_view->set( 'month', $month );
        $restaurantData = Restaurant::fetchRows(["*"],[Restaurant::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);

        $bossTotalPrice = [];

        foreach($restaurantData as $key => $value)
        {
            $communityID = $value[Restaurant::BOUND_COMMUNITY_ID];
            $condition =[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => "0"];
            $storeData = Store::fetchRows(['*'],$condition);
            $restaurantTotalPrice = "";
            $storeDataTotalPrice = [];
            foreach($storeData as $sk => $sv)
            {
                $orderTimeStart = $month."-01";
                $orderTimeEnd = $month."-31";

                    $orderTimeStart = explode("-",$orderTimeStart);
                    $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
                    $orderTimeEnd = explode("-",$orderTimeEnd);
                    $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
                log_debug("==========================".$orderTimeStart.$orderTimeEnd);
                    $con = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);

                $orderData = ProcurementOrder::fetchColumn([ProcurementOrder::TOTAL_PRICE],[ProcurementOrder::STORE_ID => $sv[Store::STORE_ID],ProcurementOrder::STATUS => "finished",$con]);

                $totalPrice = array_sum($orderData);
                $restaurantTotalPrice += $totalPrice;
                $storeData[$sk]['supply_total_price'] = $totalPrice;
                $storeDataTotalPrice[] = $totalPrice;
            }
            $storeDataTotalPrice = array_unique($storeDataTotalPrice);
            rsort($storeDataTotalPrice);
            $storeDataProgress = [];
            foreach($storeDataTotalPrice as $tk => $tv)
            {
                foreach($storeData as $tsk => $tsv)
                {
                    if($tv == $tsv['supply_total_price'])
                    {
                        $storeDataProgress [] = $tsv;
                    }
                }
            }
            $storeData = $storeDataProgress;
            $bossTotalPrice[] = $restaurantTotalPrice;

            $restaurantData[$key]['total_price'] = $restaurantTotalPrice;
            $restaurantData[$key]['supply_data'] = $storeData;
        }

        if(array_sum($bossTotalPrice) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'boss_total_price', array_sum($bossTotalPrice) );
        $bossTotalPrice = array_unique($bossTotalPrice);
        rsort($bossTotalPrice);
        $restaurantDataProgress = [];
        foreach($bossTotalPrice as $key => $value)
        {
            foreach($restaurantData as $rk => $rv)
            {
                if($value == $rv['total_price'])
                {
                    $restaurantDataProgress[] = $rv;
                }
            }
        }

        $this->_view->set( "restaurant_data", $restaurantData );
    }

    public function managerDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $storeID = $orderData->getStoreID();
        $store = new Store([Store::STORE_ID => $storeID]);
        $storeTitle = $store->getTitle();
        $this->_view->set( 'store_title', $storeTitle );
        $storeBoundId = $orderData->getBoundStoreID();
        $storeBound = new Store([Store::STORE_ID => $storeBoundId]);
        $this->_view->set( 'store_bound_title', $storeBound->getTitle() );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );

    }

    public function managerMonthOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();
        if($communityType != CommunityType::PROCUREMENT_TOTAL)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $month    = $this->_request->get( 'month' );
        $storeID    = $this->_request->get( 'store_id' );


        $this->_view->set('month',$month);
        $this->_view->set('store_id',$storeID);
        $orderTimeStart = $month."-01";
        $orderTimeEnd = $month."-31";
        $condition = [];
        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }

        if(!empty($storeID))
        {
            $condition[ProcurementOrder::STORE_ID] = $storeID;
        }
        $condition[ProcurementOrder::STATUS] = "finished";

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }

    // 单个餐厅经理查看订单

    public function singleOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $supplyID= $status = $orderTimeStart = $orderTimeEnd = null;


        $supplyID    = $this->_request->get( 'supply_id' );
        $status  = $this->_request->get( 'status' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');


        $this->_view->set('supply_id',$supplyID);
        $this->_view->set('status', $status);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);

        $condition = [];
        $condition[ProcurementOrder::MP_USER_ID] = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $monthBefore = strtotime("-2 month");
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [];


        $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => 0]);
        $this->_view->set( "supply_data", $supplyData );
        $statusDataAll = ProcurementOrderStatus::getDictionary();
        $statusData = [];
        foreach($statusDataAll as $key  => $value)
        {
            if($key != "none")
            {
                $statusData[$key] = $value;
            }

        }
        $this->_view->set( "status_data", $statusData);

        $condition[ProcurementOrder::COMMUNITY_ID] = $communityID;

        if (!empty($supplyID))
        {
            $condition[ProcurementOrder::STORE_ID] = $supplyID;
        }


        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        else
        {
            $condition[] = $con;
        }

        if(!empty($status))
        {
            $condition[ProcurementOrder::STATUS] = $status;
        }


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }

    // 老板查看订单

    public function singleSelectAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $partID= $supplyID= $status = $orderTimeStart = $orderTimeEnd = null;


        $supplyID    = $this->_request->get( 'supply_id' );
        $partID    = $this->_request->get( 'part_id' );
        $status  = $this->_request->get( 'status' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');


        $this->_view->set('supply_id',$supplyID);
        $this->_view->set('part_id',$partID);
        $this->_view->set('status', $status);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);

        $condition = [];
        $condition[ProcurementOrder::MP_USER_ID] = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $monthBefore = strtotime("-2 month");
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $monthCurrentTwo = date("Y-m-d",$monthBefore);
        $monthCurrent = date("Y-m-d");
        $this->_view->set("monthCurrentTwo",$monthCurrentTwo);
        $this->_view->set("monthCurrent",$monthCurrent);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [];



        $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => 0]);
        $this->_view->set( "supply_data", $supplyData );
        $partData = Part::fetchRows(['*'],[Part::COMMUNITY_ID => $communityID]);
        $this->_view->set( "part_data", $partData );
        $statusDataAll = ProcurementOrderStatus::getDictionary();
        $statusData = [];
        foreach($statusDataAll as $key  => $value)
        {
            if($key != "none")
            {
                $statusData[$key] = $value;
            }

        }
        $this->_view->set( "status_data", $statusData);

        $condition[ProcurementOrder::COMMUNITY_ID] = $communityID;
        if (!empty($supplyID))
        {
            $condition[ProcurementOrder::STORE_ID] = $supplyID;
        }


        if(!empty($orderTimeStart))
        {
            if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
            }
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        else
        {
            $condition[] = $con;
        }

        if(!empty($status))
        {
            $condition[ProcurementOrder::STATUS] = $status;
        }


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }


    // 单个餐厅经理查看订单

    public function singleMonthAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $month    = $this->_request->get( 'month' );
        if(empty($month))
        {
            $lastMonth = strtotime("-1 month");
            $month = date("Y-m",$lastMonth);
        }
        $this->_view->set( 'month', $month );
        $totalPriceProgress = [];

            $communityID = $wxUser->getCurrentCommunityID();
            $condition =[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => "0"];
            $storeData = Store::fetchRows(['*'],$condition);
            $restaurantTotalPrice = "";
            foreach($storeData as $sk => $sv)
            {
                $orderTimeStart = $month."-01";
                $orderTimeEnd = $month."-31";

                $orderTimeStart = explode("-",$orderTimeStart);
                $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
                $orderTimeEnd = explode("-",$orderTimeEnd);
                $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
                log_debug("==========================".$orderTimeStart.$orderTimeEnd);
                $con = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);

                $orderData = ProcurementOrder::fetchColumn([ProcurementOrder::TOTAL_PRICE],[ProcurementOrder::STORE_ID => $sv[Store::STORE_ID],ProcurementOrder::STATUS => "finished",$con]);

                $totalPrice = array_sum($orderData);
                $totalPriceProgress[] = $totalPrice;
                $restaurantTotalPrice += $totalPrice;
                $storeData[$sk]['supply_total_price'] = $totalPrice;

            }


        if($restaurantTotalPrice == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'boss_total_price', $restaurantTotalPrice );
        $totalPriceProgress = array_unique($totalPriceProgress);
        rsort($totalPriceProgress);
        $storeDataProgress = [];
        foreach($totalPriceProgress as $key => $value)
        {
            foreach($storeData as $k => $v)
            {
                if($value == $v['supply_total_price'])
                {
                    $storeDataProgress[] = $v;
                }
            }
        }
        $this->_view->set( "restaurant_data", $storeDataProgress );
    }

    public function singleMonthOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $month    = $this->_request->get( 'month' );
        $storeID    = $this->_request->get( 'store_id' );


        $this->_view->set('month',$month);
        $this->_view->set('store_id',$storeID);
        $orderTimeStart = $month."-01";
        $orderTimeEnd = $month."-31";
        $condition = [];
        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }

        if(!empty($storeID))
        {
            $condition[ProcurementOrder::STORE_ID] = $storeID;
        }
        $condition[ProcurementOrder::STATUS] = "finished";

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }

    // 老板查看订单

    public function managerGraphAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        //$communityID = "137";

        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $communityID]);
        $community = new Community([Community::COMMUNITY_ID => $communityID]);
        $communityType = $community->getCommunityType();
        if($communityType != CommunityType::PROCUREMENT_TOTAL)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $month    = $this->_request->get( 'month' );
        if(empty($month))
        {
            $lastMonth = strtotime("-1 month");
            $month = date("Y-m",$lastMonth);
        }
        $this->_view->set( 'month', $month );
        $restaurantData = Restaurant::fetchRows(["*"],[Restaurant::COMMUNITY_ID => $communityID]);
        $supplyData = [];
        foreach($restaurantData as $key => $value)
        {
            $communityIDv = $value[Restaurant::BOUND_COMMUNITY_ID];
            $condition =[Store::COMMUNITY_ID => $communityIDv,Store::IS_DELETE => "0"];
            $supply = Store::fetchColumn([Store::BOUND_COMMUNITY_ID],$condition);
            $supplyData = array_merge($supplyData,$supply);
        }
        log_debug("======================",$supplyData);
        $supplyData = array_unique($supplyData);
        log_debug("======================",$supplyData);
        $bossTotalPrice = [];
        $supplyDataTotal = [];

        foreach($supplyData as $key => $value)
        {
            $orderTimeStart = $month."-01";
            $orderTimeEnd = $month."-31";

            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];

            $con = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);

            $orderData = ProcurementOrder::fetchColumn([ProcurementOrder::TOTAL_PRICE],[ProcurementOrder::BOUND_COMMUNITY_ID => $value,ProcurementOrder::STATUS => "finished",$con]);

            $community = new Community([Community::COMMUNITY_ID => $value]);
            $totalPrice = array_sum($orderData);

            $supplyDataTotal[$key]['supply_total_price'] = $totalPrice;
            $supplyDataTotal[$key]['supply_name'] = $community->getName();
            $supplyDataTotal[$key]['community_id'] = $community->getCommunityID();
            $bossTotalPrice[] = $totalPrice;

        }


        if(array_sum($bossTotalPrice) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        log_debug("======================",$supplyDataTotal);
        //按供应商总额拍需要
        $progressTotalPrice = $bossTotalPrice;
        log_debug("======================",$progressTotalPrice);
        $progressTotalPrice = array_unique($progressTotalPrice);
        rsort($progressTotalPrice);
        log_debug("======================",$progressTotalPrice);
        $supplyDataTotalProgress = [];
        foreach($progressTotalPrice as $key => $value)
        {
            foreach($supplyDataTotal as $sk => $sv)
            {
                if($value == $sv['supply_total_price'])
                {
                    $supplyDataTotalProgress[] = $sv;
                }
            }
        }
        $supplyDataTotal = $supplyDataTotalProgress;
        $bossTotalPrice = array_sum($bossTotalPrice);
        $percentageData = [];

        foreach($supplyDataTotal as $key => $value)
        {
            $percentage = ($value['supply_total_price']/$bossTotalPrice)*100;
            $supplyDataTotal[$key]['supply_percentage'] = round($percentage,2);
            $percentageData[] = round($percentage,2);

            if($value['supply_total_price'] >= 1000)
            {
                $progress =  $value['supply_total_price']/10000;
                $progress = round($progress,3);
                $supplyDataTotal[$key]['supply_total_price'] = $progress."万元";
            }
            else
            {
                $supplyDataTotal[$key]['supply_total_price'] = $value['supply_total_price']."元";
            }
        }
        log_debug("======================",$supplyDataTotal);

        rsort($percentageData);
        if($percentageData[0] <= 30 )
        {
             $percentageAdd = 1;
            foreach($supplyDataTotal as $key => $value)
            {
                $supplyDataTotal[$key]['supply_percentage_position'] = $value['supply_percentage']*14.1/5;
            }
        }
        if($percentageData[0] > 30 and $percentageData[0] <= 40)
        {
            $percentageAdd = 2;
            foreach($supplyDataTotal as $key => $value)
            {
                $supplyDataTotal[$key]['supply_percentage_position'] = $value['supply_percentage']*14.1/5/2;
            }
        }
        if($percentageData[0] > 40 and $percentageData[0] <= 60)
        {
            $percentageAdd = 3;
            foreach($supplyDataTotal as $key => $value)
            {
                $supplyDataTotal[$key]['supply_percentage_position'] = $value['supply_percentage']*14.1/5/3;
            }
        }

        if($percentageData[0] < 60 )
        {
            $this->_view->set( 'percent_first', 5*$percentageAdd);
            $this->_view->set( 'percent_second', 10*$percentageAdd);
            $this->_view->set( 'percent_third', 15*$percentageAdd);
            $this->_view->set( 'percent_fourth', 20*$percentageAdd);
            $this->_view->set( 'percent_fifth', 25*$percentageAdd);
            $this->_view->set( 'percent_sixth', 30*$percentageAdd);
        }


        if($percentageData[0] > 60 )
        {
            $percentageAdd = 6;
            foreach($supplyDataTotal as $key => $value)
            {
                $supplyDataTotal[$key]['supply_percentage_position'] = $value['supply_percentage']*14.1/5/6;
            }
            $this->_view->set( 'percent_first', 5*$percentageAdd);
            $this->_view->set( 'percent_second', 10*$percentageAdd);
            $this->_view->set( 'percent_third', 15*$percentageAdd);
            $this->_view->set( 'percent_fourth', "");
            $this->_view->set( 'percent_fifth', "");
            $this->_view->set( 'percent_sixth', "");
        }

        log_debug("======================",$supplyDataTotal);

        $this->_view->set( 'boss_total_price', $bossTotalPrice);
        $this->_view->set( "supply_data", $supplyDataTotal );
    }
}
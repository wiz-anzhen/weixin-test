<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;

use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\StoreBusiness;
use WBT\Controller\CommunityControllerBase;

class StoreController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "store";
        parent::_init();
    }
    //显示
    public function storeAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $paging = []; // 先初始化为空
        $outputColumns = Store::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ Store::MP_USER_ID => $mpUserId,Store::COMMUNITY_ID => $communityId,Store::IS_DELETE => "0"];
        $ranking       = [ Store::STORE_ID ];
        $data          = StoreBusiness::getStoreList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $shownColumns = [
            Store::TITLE => [ Table::COLUMN_TITLE => "商城名称"],
            Store::COMMENT,
            Store::STORE_ID => [ Table::COLUMN_TITLE => "商城id"],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:23%',
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[Store::COMMUNITY_ID];
                        $mpUserID = $row[Store::MP_USER_ID];
                        $storeID = $row[Store::STORE_ID];
                        $productList =  new Link("产品分类", "/mp_admin/store/category?store_id={$storeID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $address1 = new Link("商城地址（小图）", "/wx_user/store/index?store_id={$storeID}&community_id={$communityID}&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                        $address2 = new Link("商城地址（大图）","/wx_user/mall/list?store_id={$storeID}&community_id={$communityID}&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                        $address3 = new Link("商城地址（多店）","/wx_user/store/index?store_id={$storeID}&view=2&community_id={$communityID}&mp_user_id={$mpUserID}",
                            [ 'target' => '_blank' ]);


                        $ret = $productList."<br>".$address1 . "<br>" . $address2 ."<br>" . $address3;
                        return $ret;
                    } ], ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =  [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:23%',
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[Store::COMMUNITY_ID];
                        $mpUserID = $row[Store::MP_USER_ID];
                        $storeID = $row[Store::STORE_ID];
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/store_dialog/store_update?store_id={$storeID}&community_id={$communityID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该商城下的分类和产品都将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/store/store_delete?store_id={$storeID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        $productList =  new Link("产品分类", "/mp_admin/store/category?store_id={$storeID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $address1 = new Link("商城地址（小图）", "/wx_user/store/index?store_id={$storeID}&community_id={$communityID}&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                        $address2 = new Link("商城地址（大图）","/wx_user/mall/list?store_id={$storeID}&community_id={$communityID}&mp_user_id={$mpUserID}", [ 'target' => '_blank' ]);
                        $address3 = new Link("商城地址（多店）","/wx_user/store/index?store_id={$storeID}&view=2&community_id={$communityID}&mp_user_id={$mpUserID}",
                            [ 'target' => '_blank' ]);


                        $ret = $update."<br>".$productList."<br>".$address1."<br>".$address2 ."<br>" . $address3;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    } ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, Store::STORE_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function categoryAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $storeId = $this->_request->getQueryParam( Store::STORE_ID );
        $store = new Store([Store::STORE_ID => $storeId]);
        $this->_view->set('store', $store->data());
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $paging = []; // 先初始化为空
        $outputColumns = Category::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ Category::STORE_ID => $storeId,Category::IS_DELETE => "0" ];
        $ranking       = [ Category::SORT_NO ];
        $data          = StoreBusiness::getCategoryList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $showColumns = [
            Category::TITLE=> [ Table::COLUMN_TITLE => "分类名称"],
            Category::CATEGORY_ID => [Table::COLUMN_TITLE => "分类id"],
            Category::COVER_IMG => [Table::COLUMN_TITLE => "封面图片",
                                    Table::COLUMN_FUNCTION => function(array $row) {
                    return sprintf( '<img src="%s" width="100px" height="100px" alt="没有图片"/>',
                        $row[Category::COVER_IMG] );
                }],
            Category::DESCRIPTION,
            Category::SORT_NO,
            Category::COMMENT,
            Category::IS_ON_SHELF => [Table::COLUMN_TITLE    => '是否上架',                                                              Table::COLUMN_FUNCTION => function (array $row)                                                                        {
                    return $row[Category::IS_ON_SHELF] == 1 ? '是' : '否';                                                                   },],
            Table::COLUMN_OPERATIONS => [ Table::COLUMN_OPERATIONS => [
                new Link("产品", "/mp_admin/store/product?store_id={{this.store_id}}&category_id={{this.category_id}}&mp_user_id={$mpUserId}&community_id={{this.community_id}}"),
            ], ], ];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[Category::COMMUNITY_ID];
                        $mpUserID = $row[Category::MP_USER_ID];
                        $categoryID = $row[Category::CATEGORY_ID];
                        $storeID = $row[Category::STORE_ID];
                        $update =   new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/store_dialog/category_update?category_id={$categoryID}&community_id={$communityID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该分类下的产品都将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/store/category_delete?category_id={$categoryID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        $productList =  new Link("产品", "/mp_admin/store/product?store_id={$storeID}&category_id={$categoryID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $ret = $update."<br>".$productList;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    } ];
        }


        $table               = Table::fromDbData( $data, $outputColumns, Category::CATEGORY_ID, $paging,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function productAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $productId = $this->_request->getQueryParam( Product::PRODUCT_ID );
        if(isset($productId))
        {
            $product = new Product([Product::PRODUCT_ID => $productId]);
            $storeId = $product->getStoreID();
            $categoryId = $product->getCategoryID();
        }
        else
        {
            $storeId = $this->_request->getQueryParam( Product::STORE_ID );
            $categoryId = $this->_request->getQueryParam( Product::CATEGORY_ID );
        }
        $store = new Store([Store::STORE_ID => $storeId]);
        $category = new Category([Category::CATEGORY_ID => $categoryId]);
        $this->_view->set('store', $store->data());
        $this->_view->set('category', $category->data());
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $paging = []; // 先初始化为空
        $outputColumns = Category::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        if(isset($productId))
        {
            $condition = [ Product::PRODUCT_ID => $productId,Product::IS_DELETE => "0" ];
        }
        else
        {
            $condition     = [ Product::CATEGORY_ID => $categoryId,Product::IS_DELETE => "0" ];
        }
        $ranking       = [ Product::SORT_NO ];
        $data          = StoreBusiness::getProductList( $condition, $paging, $ranking, $outputColumns );

        $outputColumns = Product::s_metadata()->getFilterOptions();
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $showColumns = [ Product::TITLE,
            Product::IMG_URL => [ Table::COLUMN_FUNCTION => function(array $row) {
                    return sprintf( '<img src="%s" width="100px" height="100px" alt="没有图片"/>',
                        $row[Product::IMG_URL] );
                } ],
            Product::BIG_IMG_URL =>[Table::COLUMN_FUNCTION => function(array $row)
                {
                    if(strlen($row[Product::BIG_IMG_URL]) > 0)
                    {
                        return sprintf('<a href="%s" target="_blank">点击查看</a>', $row[Product::BIG_IMG_URL]);
                    }
                    else
                    {
                        return '暂无大图片';
                    }

                }],
            Product::PRICE,Product::COST_PRICE,Product::REFERENCE_PRICE,Product::PROFIT,Product::COMMISSIONS ,
            Product::IS_ON_SHELF => [ Table::COLUMN_TITLE    => '是否上架',
                                      Table::COLUMN_FUNCTION => function ( array $row )
                                          {
                                              return $row[Product::IS_ON_SHELF] == 1 ? '是' : '否';
                                          }, ],

            Product::DETAIL_URL => [ Table::COLUMN_FUNCTION => function(array $row) {
                    if (strlen($row[Product::DETAIL_URL]) > 0) {
                        return sprintf('<a href="%s" target="_blank">点击查看</a>', $row[Product::DETAIL_URL]);
                    } else {
                        return '暂无详情页';
                    }
                } ],
            Product::SORT_NO,
            Product::COMMENT,
            Table::COLUMN_OPERATIONS => [Table::COLUMN_TITLE => "操作",
                                         Table::COLUMN_FUNCTION => function (array $row)
                                             {
                                                 $communityID = $row[Product::COMMUNITY_ID];
                                                 $productID = $row[Product::PRODUCT_ID];
                                                 $mpUserID = $row[Product::MP_USER_ID];
                                                 $readComment =  new Link("查看评论",
                                                     "/mp_admin/product_comment/total?product_id={$productID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                                                 return $readComment;
                                             }]
        ];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[Product::COMMUNITY_ID];
                        $productID = $row[Product::PRODUCT_ID];
                        $mpUserID = $row[Product::MP_USER_ID];
                        $readComment =  new Link("查看评论",
                            "/mp_admin/product_comment/total?product_id={$productID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/store_dialog/product_update?product_id={$productID}&community_id={$communityID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/store/product_delete?product_id={$productID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");

                        $ret = $update."<br>".$readComment;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    } ];
        }



        $table               = Table::fromDbData( $data, $outputColumns, Product::PRODUCT_ID, $paging,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    //采购
    public function storeProcurementAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $communityType = $community->getCommunityType();
        //标题名称变更
        if($communityType == "procurement_supply")
        {
            $communityTypeName = "餐厅";
            $titleTypeName = "供应商";
        }
        else
        {
            $communityTypeName = "供应商";
            $titleTypeName = "餐厅";
        }
        $this->_view->set( "community_type", $communityType );
        $paging = []; // 先初始化为空
        $outputColumns = Store::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ Store::MP_USER_ID => $mpUserId,Store::COMMUNITY_ID => $communityId,Store::IS_DELETE => "0"];
        $ranking       = [ Store::STORE_ID ];
        $data          = StoreBusiness::getStoreList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $shownColumns = [
            Store::COMMUNITY_ID => [Table::COLUMN_TITLE => $titleTypeName,
                Table::COLUMN_FUNCTION => function (array $row)use($communityName){
                        return $communityName;
                    }],
            Store::TITLE => [ Table::COLUMN_TITLE => $communityTypeName],
            Store::COMMENT,
            Store::STORE_ID => [ Table::COLUMN_TITLE => $communityTypeName."id"],
            Store::BOUND_COMMUNITY_ID => [ Table::COLUMN_TITLE => "绑定社区id"],
            Store::BOUND_STORE_ID => [ Table::COLUMN_TITLE => "绑定商城id"],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:23%',
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[Store::COMMUNITY_ID];
                        $mpUserID = $row[Store::MP_USER_ID];
                        $storeID = $row[Store::STORE_ID];
                        $productList =  new Link("报价单", "/mp_admin/store/category_procurement?store_id={$storeID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $ret = $productList;
                        return $ret;
                    } ], ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =  [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:23%',
                Table::COLUMN_FUNCTION => function(array $row)use($power,$communityType)
                    {
                        $communityID = $row[Store::COMMUNITY_ID];
                        $mpUserID = $row[Store::MP_USER_ID];
                        $storeID = $row[Store::STORE_ID];
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/store_dialog/store_update_procurement?store_id={$storeID}&community_id={$communityID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该供应商下的报价单和产品都将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/store/store_delete?store_id={$storeID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        $productList =  new Link("报价单", "/mp_admin/store/category_procurement?store_id={$storeID}&mp_user_id={$mpUserID}&community_id={$communityID}");



                        if($communityType == "procurement_supply")
                        {
                            $ret = $productList;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                        }
                        else
                        {
                            $ret = $productList;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                        }
                        return $ret;
                    } ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, Store::STORE_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function categoryProcurementAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $storeId = $this->_request->getQueryParam( Store::STORE_ID );
        $store = new Store([Store::STORE_ID => $storeId]);
        $this->_view->set('store', $store->data());
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $communityType = $community->getCommunityType();
        //标题名称变更
        if($communityType == "procurement_supply")
        {
            $communityTypeName = "餐厅";
            $titleTypeName = "供应商";
        }
        else
        {
            $communityTypeName = "供应商";
            $titleTypeName = "餐厅";
        }
        $this->_view->set( "community_type", $communityType );
        $paging = []; // 先初始化为空
        $outputColumns = Category::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ Category::STORE_ID => $storeId,Category::IS_DELETE => "0" ];
        $ranking       = [ Category::SORT_NO ];
        $data          = StoreBusiness::getCategoryList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $showColumns = [
            Category::COMMUNITY_ID => [Table::COLUMN_TITLE => $titleTypeName,
            Table::COLUMN_FUNCTION => function (array $row)use($communityName){
                    return $communityName;
                }],
            Category::STORE_ID => [Table::COLUMN_TITLE => $communityTypeName,
                Table::COLUMN_FUNCTION => function (array $row){
                        $store = new Store([Store::STORE_ID => $row[Category::STORE_ID ]]);
                        return $store->getTitle();
    }],
            Category::TITLE => [Table::COLUMN_TITLE => "报价单名称"],
            Category::CATEGORY_ID => [Table::COLUMN_TITLE => "报价单id"],
            Category::DESCRIPTION => [Table::COLUMN_TITLE => "报价单描述"],
            Category::SORT_NO,
            Category::COMMENT,
            Category::SEND_AUTHOR,
            Category::SEND_TIME,

            Category::IS_ON_SHELF => [Table::COLUMN_TITLE    => '是否上架',                                                              Table::COLUMN_FUNCTION => function (array $row)                                                                        {
                    return $row[Category::IS_ON_SHELF] == 1 ? '是' : '否';                                                                   },],
            Category::SHELF_TIME => [Table::COLUMN_TITLE    => '上架时间',                                                              Table::COLUMN_FUNCTION => function (array $row)                                                                             {
                    return str_replace(":","-",$row[Category::SHELF_TIME]);                                                                   },],

                Table::COLUMN_OPERATIONS => [
                    Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power,$communityType)
                    {
                        $communityID = $row[Category::COMMUNITY_ID];
                        $mpUserID = $row[Category::MP_USER_ID];
                        $categoryID = $row[Category::CATEGORY_ID];
                        $storeID = $row[Category::STORE_ID];
                        $productList =  new Link("产品", "/mp_admin/store/product_procurement?store_id={$storeID}&category_id={$categoryID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $downLoad =  new Link("导出Excel", "/api//fcrm/store/category_download?category_id={$categoryID}&community_id={$communityID}");
                        $ret = $downLoad."<br>".$productList;
                        return $ret;
                    }
            ],

        ];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power,$communityType)
                    {
                        $communityID = $row[Category::COMMUNITY_ID];
                        $mpUserID = $row[Category::MP_USER_ID];
                        $categoryID = $row[Category::CATEGORY_ID];
                        $storeID = $row[Category::STORE_ID];
                        $update =   new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/store_dialog/category_update_procurement?category_id={$categoryID}&community_id={$communityID}');");
                        //$delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该分类下的产品都将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/store/category_delete?category_id={$categoryID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        $send = new Link(' 发布', "javascript:bluefinBH.confirm('确定要发布吗？', function() { javascript:wbtAPI.call('../fcrm/store/category_send?category_id={$categoryID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('发布成功', function() { location.reload(); }); }); })");
                        $productList =  new Link("产品", "/mp_admin/store/product_procurement?store_id={$storeID}&category_id={$categoryID}&mp_user_id={$mpUserID}&community_id={$communityID}");
                        $downLoad =  new Link("导出Excel", "/api//fcrm/store/category_download?category_id={$categoryID}&community_id={$communityID}");

                        $ret = $update."<br>".$productList;
                        if($communityType == "procurement_restaurant")
                        {
                                $ret .= "<br>".$send."<br>".$downLoad;

                        }
                        else
                        {
                            $ret = $productList."<br>".$downLoad;
                        }

                        return $ret;
                    } ];
        }


        $table               = Table::fromDbData( $data, $outputColumns, Category::CATEGORY_ID, $paging,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

    public function productProcurementAction()
    {
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $productId = $this->_request->getQueryParam( Product::PRODUCT_ID );
        if(isset($productId))
        {
            $product = new Product([Product::PRODUCT_ID => $productId]);
            $storeId = $product->getStoreID();
            $categoryId = $product->getCategoryID();
        }
        else
        {
            $storeId = $this->_request->getQueryParam( Product::STORE_ID );
            $categoryId = $this->_request->getQueryParam( Product::CATEGORY_ID );
        }
        $store = new Store([Store::STORE_ID => $storeId]);
        $category = new Category([Category::CATEGORY_ID => $categoryId]);
        $categorySend = $category->getSendTime();
        $this->_view->set('store', $store->data());
        $this->_view->set('category', $category->data());
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $communityType = $community->getCommunityType();
        //标题名称变更
        if($communityType == "procurement_supply")
        {
            $communityTypeName = "餐厅";
            $titleTypeName = "供应商";
        }
        else
        {
            $communityTypeName = "供应商";
            $titleTypeName = "餐厅";
        }
        $this->_view->set( "community_type", $communityType );
        $paging = []; // 先初始化为空
        $outputColumns = Category::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        if(isset($productId))
        {
            $condition = [ Product::PRODUCT_ID => $productId,Product::IS_DELETE => "0" ];
        }
        else
        {
            $condition     = [ Product::CATEGORY_ID => $categoryId,Product::IS_DELETE => "0" ];
        }
        $ranking       = [ Product::SORT_NO ];
        $data          = StoreBusiness::getProductList( $condition, $paging, $ranking, $outputColumns );

        $outputColumns = Product::s_metadata()->getFilterOptions();
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $showColumns = [ Product::TITLE,

            Product::PRICE => [Table::COLUMN_TITLE => "价格"],
            Product::PRODUCT_UNIT => [Table::COLUMN_TITLE => "单位"],
            /*
            Product::IS_ON_SHELF => [ Table::COLUMN_TITLE    => '是否上架',
                Table::COLUMN_FUNCTION => function ( array $row )
                    {
                        return $row[Product::IS_ON_SHELF] == 1 ? '是' : '否';
                    }, ],
*/
            Product::SORT_NO,
            Product::COMMENT,
        ];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power,$communityType,$categorySend)
                    {
                        $communityID = $row[Product::COMMUNITY_ID];
                        $productID = $row[Product::PRODUCT_ID];
                        $mpUserID = $row[Product::MP_USER_ID];
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/store_dialog/product_update_procurement?product_id={$productID}&community_id={$communityID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/store/product_delete?product_id={$productID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");

                        $ret = "";
                        if($communityType == "procurement_restaurant")
                        {
                                $ret = $update;
                                if($power["delete"])
                                {
                                    $ret .= "<br>".$delete;
                                }


                        }

                        return $ret;
                    } ];
        }



        $table               = Table::fromDbData( $data, $outputColumns, Product::PRODUCT_ID, $paging,
            $showColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }

}
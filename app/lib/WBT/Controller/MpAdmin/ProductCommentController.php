<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;

use MP\Model\Mp\ProductComment;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\ProductCommentBusiness;
use WBT\Controller\CommunityControllerBase;

use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;

class ProductCommentController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "store";
        parent::_init();
    }
    //显示
    public function totalAction()
    {
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserID ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityID = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityID]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityID );


        $paging = []; // 先初始化为空
        $outputColumns = ProductComment::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $productID = $this->_request->get( 'product_id');
        if(!empty($productID))
        {
            $condition     = [ ProductComment::PRODUCT_ID => $productID];
        }
        else
        {
            $condition     = [ ProductComment::COMMUNITY_ID => $communityID];
        }

        $ranking       = [ ProductComment::COMMENT_TIME => true ];
        $data          = ProductCommentBusiness::getProductCommentList( $condition, $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("store_rw","store_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('store_rw', $checkReadPower);
        $shownColumns = [
            ProductComment::NICK => [Table::COLUMN_TITLE => "昵称",
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $wxUser =new WxUser([WxUser::WX_USER_ID => $row[ProductComment::WX_USER_ID]]);
                        return  $wxUser->getNick();
                    }
            ],
            ProductComment::HEAD_PIC => [Table::COLUMN_TITLE => "头像",
                                         Table::COLUMN_FUNCTION => function(array $row)
                                             {
                                                 $wxUser =new WxUser([WxUser::WX_USER_ID => $row[ProductComment::WX_USER_ID]]);
                                                 $pic =  $wxUser->getHeadPic();
                                                 return sprintf('<img src="%s" height="50px" width="50px"/>',$pic);
                                             }
                                         ],
            ProductComment::PRODUCT_TITLE,
            ProductComment::COMMENT,
            ProductComment::COMMENT_LEVEL,
            ProductComment::COMMENT_TIME,
            ProductComment::ORDER_FINISH_TIME,
             ];
        if($power["delete"])
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =  [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $productCommentID = $row[ProductComment::PRODUCT_COMMENT_ID];
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该评论将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/product_comment/delete?product_comment_id={$productCommentID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        return $delete;
                    } ];
        }

        $table               = Table::fromDbData( $data, $outputColumns, Store::STORE_ID, $paging, $shownColumns,
                               [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );


        if(!empty($productID))
        {
            $product = new Product([Product::PRODUCT_ID => $productID]);
            $storeID = $product->getStoreID();
            $categoryID = $product->getCategoryID();
            $store = new Store([Store::STORE_ID => $storeID]);
            $category = new Category([Category::CATEGORY_ID => $categoryID]);
            $this->_view->set('store', $store->data());
            $this->_view->set('category', $category->data());
            $this->_view->set('product_id', $productID);
            $this->changeView('WBT/MpAdmin/ProductComment.single.html');
        }

    }

}
<?php


use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\StoreBusiness;
use MP\Model\Mp\UserNotify;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\IndustryType;
use WBT\Business\UserBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\UserNotifySendStatus;

require_once 'MpUserServiceBase.php';
set_include_path( LIB . '/PHPExcel' . PATH_SEPARATOR . get_include_path() );
require_once LIB . '/PHPExcel/PHPExcel.php';
require_once 'MpUserServiceBase.php';
class StoreService extends MpUserServiceBase
{
    // 商城
    public function storeUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Store::STORE_ID );
        $data = $this->_app->request()->getArray( [ Store::TITLE, Store::COMMENT, ] );

        return StoreBusiness::storeUpdate( $id, $data );
    }

    public function storeInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Store::MP_USER_ID, Store::TITLE, Store::COMMENT, Store::COMMUNITY_ID] );

        return StoreBusiness::storeInsert( $data );
    }

    public function storeCopy()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Store::MP_USER_ID, Store::STORE_ID, Store::COMMUNITY_ID] );

        return StoreBusiness::storeCopy( $data );
    }

    public function storeDelete()
    {
        $id = $this->_app->request()->get( Store::STORE_ID );
        $category = new Category([Category::STORE_ID => $id ,Category::IS_DELETE => '0']);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $category->getMpUserID()]);
        if(!$category->isEmpty() and $mpUser->getIndustry() != 'procurement')
        {
            return['errno' => 1,'error' => '此商城下还有产品不能删除'];
        }
        else
        {
            return StoreBusiness::storeDelete( $id );
        }

    }

    // 分类
    public function categoryInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Category::MP_USER_ID, Category::STORE_ID, Category::TITLE, Category::COVER_IMG, Category::DESCRIPTION, Category::SORT_NO, Category::COMMENT, Category::IS_ON_SHELF,Category::COMMUNITY_ID,Category::SHELF_TIME] );

        return StoreBusiness::categoryInsert( $data );
    }

    public function categoryCopy()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Category::MP_USER_ID, Category::STORE_ID,Category::CATEGORY_ID,Category::COMMUNITY_ID] );

        return StoreBusiness::categoryCopy( $data );
    }

    public function categoryUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Category::CATEGORY_ID );
        $data = $this->_app->request()->getArray( [ Category::TITLE, Category::COVER_IMG, Category::DESCRIPTION, Category::SORT_NO, Category::COMMENT,Category::IS_ON_SHELF,Category::SHELF_TIME ] );

        if(!isset($data[Category::IS_ON_SHELF]))
        {
            unset($data[Category::IS_ON_SHELF]);
        }

        return StoreBusiness::categoryUpdate( $id, $data );
    }

    public function categoryDelete()
    {
        $id = $this->_app->request()->get( Category::CATEGORY_ID );
        $product= new Product([Product::CATEGORY_ID => $id ,Product::IS_DELETE => '0']);
        if(!$product->isEmpty())
        {
            return['errno' => 1,'error' => '此类别下还有产品不能删除'];
        }
        else
        {
            return StoreBusiness::categoryDelete( $id );
        }
    }

    // 产品
    // 产品
    public function productInsert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ Product::MP_USER_ID, Product::STORE_ID, Product::CATEGORY_ID,Product::TITLE, Product::IMG_URL,Product::BIG_IMG_URL, Product::PRICE, Product::COST_PRICE,Product::REFERENCE_PRICE,Product::PROFIT,Product::IS_ON_SHELF,Product::DETAIL, Product::COMMISSIONS ,Product::SORT_NO,Product::DETAIL_URL, Product::COMMENT,Product::COMMUNITY_ID,Product::PRODUCT_UNIT ] );

        return StoreBusiness::productInsert( $data );
    }

    public function productUpdate()
    {
        $id   = $this->_app->request()->getQueryParam( Product::PRODUCT_ID );
        $data = $this->_app->request()->getArray( [ Product::TITLE, Product::PRICE,Product::COST_PRICE,Product::REFERENCE_PRICE, Product::COMMISSIONS ,Product::PROFIT,Product::IS_ON_SHELF, Product::IMG_URL, Product::BIG_IMG_URL,Product::DETAIL,Product::SORT_NO,Product::DETAIL_URL, Product::COMMENT,Product::PRODUCT_UNIT  ] );
        if(!isset($data[Product::IS_ON_SHELF]))
        {
            $data[Product::IS_ON_SHELF] = "1";
        }
        return StoreBusiness::productUpdate( $id, $data );
    }

    public function productDelete()
    {
        $id = $this->_app->request()->get( Product::PRODUCT_ID );

        return StoreBusiness::productDelete( $id );
    }

    // 发送报价单
    public function categorySend()
    {
        $id   = $this->_app->request()->getQueryParam( Category::CATEGORY_ID );
        $category = new Category([Category::CATEGORY_ID => $id]);

        $mpUserID =  $category->getMpUserID();
        $communityID = $category->getCommunityID();
        $community = new Community([Community::COMMUNITY_ID => $communityID]);
        $communityName = $community->getName();

        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
        $store = new Store([Store::STORE_ID => $category->getStoreID()]);
        $storeName = $store->getTitle();
        $categoryName = $category->getTitle();
        log_debug("====================".$categoryName);
        //查看供应商
        $procurementCommunity = new Community([Community::COMMUNITY_ID => $store->getBoundCommunityID(),Community::MP_USER_ID => $mpUserID]);
        if($procurementCommunity ->isEmpty())
        {
            return['errno' => 1,'error' => '您的供应商不再系统中，请与您的供应商确认'];
        }

        //在供货商内生成报价单
        log_debug("===================================".$categoryName.$storeName);
        $procurementStore = new Store([Store::STORE_ID => $store->getBoundStoreID()]);
        if($procurementStore ->isEmpty())
        {
            return['errno' => 1,'error' => '您还没有被供应商加入，请与您的供应商确认'];
        }
        //发送模板消息
        $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$procurementCommunity->getCommunityID(),"",$mpUserID);
        log_debug("====================",$wxUserIDs);
        $host =  ConfigBusiness::getHost();//获取主机名
        foreach($wxUserIDs as $value)
        {
            $wxUser = new WxUser([WxUser::WX_USER_ID => $value]);
            $url = sprintf("%s/wx_user/procurement/inquiry?wx_user_id=%s&mp_user_id=%s&category_id=%s",$host,$value,$mpUserID,$id);
            $first = $community->getName().",给您留言了";
            $nick = $wxUser->getNick();
            $content = $community->getName().",向您发送".$categoryName."报价单";

            $template = array( 'touser' => $value,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode("$first"),'color' =>"#222", ),
                    'keyword1' => array('value' => urlencode($content),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode("报价单通知"),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("") ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }

        $category->setSendTime(time())->setSendAuthor(UserBusiness::getLoginUsername())->update();


        //复制分类
        $newCategory = new Category();
        $newCategory->setCommunityID($procurementStore->getCommunityID())->setMpUserID($procurementStore->getMpUserID())->setStoreID($procurementStore->getStoreID())->setTitle($category->getTitle())->setComment($category->getComment())->setIsDelete($category->getIsDelete())->setIsOnShelf($category->getIsOnShelf())->setCoverImg($category->getCoverImg())->setDescription($category->getDescription())->insert();
        //复制产品
        $product  = Product::fetchColumn([Product::PRODUCT_ID],[Product::CATEGORY_ID => $id ]);
        log_debug("===================================",$product);

        foreach($product as $v)
        {
            $dataProduct = Product::fetchOneRow(['*'],[Product::PRODUCT_ID => $v]);
            $dataProduct[Product::MP_USER_ID] = $newCategory->getMpUserID();
            $dataProduct[Product::COMMUNITY_ID] = $newCategory->getCommunityID();
            $dataProduct[Product::STORE_ID] =  $newCategory->getStoreID();
            $dataProduct[Product::CATEGORY_ID] = $newCategory->getCategoryID();
            unset($dataProduct[Product::PRODUCT_ID]);
            unset($dataProduct[Product::_CREATED_AT]);
            log_debug("===================================",$dataProduct);
            $newProduct = new Product();
            $newProduct->apply($dataProduct)->insert();
        }
        return [ 'errno' => 0];
    }

    public function categoryDownload()
    {
        $categoryID = $this->_app->request()->getQueryParam( 'category_id' );
        $category = new Category([Category::CATEGORY_ID => $categoryID]);
        $community = new Community([Community::COMMUNITY_ID => $category->getCommunityID()]);
        $communityType = $community->getCommunityType();
        $store =  new Store([Store::STORE_ID => $category->getStoreID()]);
        if($communityType == "procurement_supply")
        {
            $restaurant = $store->getTitle();
            $supply= $community->getName();
        }
        else
        {
            $restaurant = $community->getName();
            $supply= $store->getTitle();
        }

        $condition = [Category::CATEGORY_ID => $categoryID];
        log_debug("====================",$condition);
        $dataTotal = StoreBusiness::getProductList( $condition, $paging=NULL, NULL, NULL );
        log_debug("====================",$dataTotal);

        //处理订单信息
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('报价单'.$category->getTitle());
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
//标题栏：订单编号 、订单状态、收货人姓名、收货人电话、 客服组、客服专员、商品名称、商品销售价、商品提成、商品数量

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '商品名称' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '商品价格' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '商品单位' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '备注' );

        foreach ($dataTotal as $data)
        {
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $data[Product::TITLE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $data[Product::PRICE]);
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row,  $productUnit = \MP\Model\Mp\ProductUnitType::getDisplayName($data[Product::PRODUCT_UNIT])  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $data[Product::COMMENT]  );
        }


        $filename = $supply.'-报价单'.$category->getTitle();

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache - Control:must - revalidate, post - check = 0, pre - check = 0" );
        header( "Content-Type:application/force-download" );
        header( "Content-Type:application/vnd.ms-execl" );
        header( "Content-Type:application/octet-stream" );
        header( "Content-Type:application/download" );;
        header( 'Content-Disposition:attachment;filename="' . $filename . '.xlsx"' );
        header( "Content-Transfer-Encoding:binary" );
        $objWriter->save( 'php://output' );
    }


}
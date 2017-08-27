<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\Community;
use MP\Model\Mp\Directory;
use MP\Model\Mp\Store;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;
use MP\Model\Mp\CommunityType;
use WBT\Business\Weixin\SendTemplateBusiness;

class StoreBusiness extends BaseBusiness
{
    public static function getStoreList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return Store::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function storeInsert( $data )
    {
        $store = new Store([Store::TITLE =>  $data[Store::TITLE],Store::COMMUNITY_ID => $data[Store::COMMUNITY_ID],Store::IS_DELETE => '0']);
        if(!$store->isEmpty())
        {
            return [ 'errno' => 1, 'error' => $data[Store::TITLE].'已存在，请重新输入' ];
        }
        $obj = new Store();
        $obj->apply( $data );
        $obj->insert();

        $communityRestaurant = new Community([Community::COMMUNITY_ID => $data[Store::COMMUNITY_ID]]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_RESTAURANT)
        {//找出同名的供应商
            $community = new Community([Community::NAME => $data[Store::TITLE],Community::MP_USER_ID => $data[Store::MP_USER_ID]]);
            if($community->isEmpty())
            {//没有同名的供应商，建立同名的供应商，并在其下添加餐厅
                $community = new Community();
                $community->setName($data[Store::TITLE])->setMpUserID($data[Store::MP_USER_ID])->setIsVirtual(0)->setCommunityType('procurement_supply')->setCity("待填")->setAddress("待填")->insert();

                SendTemplateBusiness::sendNewNotify($data[Store::MP_USER_ID],$communityRestaurant->getName(),$data[Store::TITLE]);
                $store = new Store();
                $store->setTitle($communityRestaurant->getName())->setMpUserID($community->getMpUserID())->setCommunityID($community->getCommunityID())->setBoundCommunityID($data[Store::COMMUNITY_ID])->setBoundStoreID($obj->getStoreID())->insert();
                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '224',Directory::COMMUNITY_ID => $store->getCommunityID(),Directory::MP_USER_ID => $store->getMpUserID()]);
            }
            else
            {//有同名的供应商，并在其下添加餐厅
                $store = new Store([Store::TITLE => $communityRestaurant->getName(),Store::COMMUNITY_ID => $community->getCommunityID(),Store::IS_DELETE => 0]);
                if($store->isEmpty())
                {
                    $store = new Store();
                    $store->setTitle($communityRestaurant->getName())->setMpUserID($community->getMpUserID())->setCommunityID($community->getCommunityID())->setBoundCommunityID($data[Store::COMMUNITY_ID])->setBoundStoreID($obj->getStoreID())->insert();
                }

            }
            $data[Store::BOUND_COMMUNITY_ID] = $community->getCommunityID();
            $data[Store::BOUND_STORE_ID] = $store->getStoreID();
        }


        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function storeCopy( $data )
    {
        log_debug("===================================",$data);
        $store = new Store([Store::STORE_ID => $data[Store::STORE_ID],Store::IS_DELETE => 0]);
        if ($store->isEmpty()) {
            return [ 'errno' => 1, 'error' => '找不到该ID对应的记录，请核对id' ];
        }
        //复制商城
        $newStore = new Store();
        $newStore->setCommunityID($data[Store::COMMUNITY_ID])->setMpUserID($data[Store::MP_USER_ID])->setComment($store->getComment())->setTitle($store->getTitle())->setIsDelete('0')->setBoundCommunityID($store->getBoundCommunityID())->setBoundStoreID($store->getBoundStoreID())->insert();
        //复制分类
        $category  = Category::fetchColumn([Category::CATEGORY_ID],[Category::STORE_ID => $data[Store::STORE_ID]]);
        log_debug("===================================",$category);
        foreach($category as $key => $value)
        {
            $dataCategory =  Category::fetchOneRow(['*'],[Category::CATEGORY_ID => $value]);
            $dataCategory[Category::MP_USER_ID] = $data[Store::MP_USER_ID];
            $dataCategory[Category::COMMUNITY_ID] = $data[Store::COMMUNITY_ID];
            $dataCategory[Category::STORE_ID] = $newStore->getStoreID();

            unset($dataCategory[Category::CATEGORY_ID]);
            unset($dataCategory[Category::_CREATED_AT]);
            unset($dataCategory[Category::SEND_TIME]);
            unset($dataCategory[Category::SEND_AUTHOR]);

            log_debug("===================================",$dataCategory);
            $newCategory = new Category();
            $newCategory->apply($dataCategory)->insert();

            //复制产品
            $product_id = Product::fetchColumn([Product::PRODUCT_ID],[Product::CATEGORY_ID => $value]);
            log_debug("===================================",$product_id);
            foreach($product_id as $v)
            {
                $dataProduct = Product::fetchOneRow(['*'],[Product::PRODUCT_ID => $v]);
                $dataProduct[Product::MP_USER_ID] = $data[Store::MP_USER_ID];
                $dataProduct[Product::COMMUNITY_ID] = $data[Store::COMMUNITY_ID];
                $dataProduct[Product::STORE_ID] = $newStore->getStoreID();
                $dataProduct[Product::CATEGORY_ID] = $newCategory->getCategoryID();

                unset($dataProduct[Product::PRODUCT_ID]);
                unset($dataProduct[Product::_CREATED_AT]);
                log_debug("===================================",$dataProduct);
                $newProduct = new Product();
                $newProduct->apply($dataProduct)->insert();
            }

        }
       //在供应商里加入此餐厅
        $communityRestaurant = new Community([Community::COMMUNITY_ID => $data[Store::COMMUNITY_ID]]);
        log_debug("=================================".$communityRestaurant->getCommunityType());
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_RESTAURANT)
        {//找出同名的供应商
            $community = new Community([Community::NAME => $store->getTitle(),Community::MP_USER_ID  => $store->getMpUserID()]);
            if($community->isEmpty())
            {//没有同名的供应商，建立同名的供应商，并在其下添加餐厅
                    $community = new Community();
                    $community->setName($store->getTitle())->setMpUserID($data[Store::MP_USER_ID])->setIsVirtual(0)->setCommunityType('procurement_supply')->setCity("待填")->setAddress("待填")->insert();
                SendTemplateBusiness::sendNewNotify($data[Store::MP_USER_ID],$communityRestaurant->getName(),$store->getTitle());
                    $store = new Store();
                    $store->setTitle($communityRestaurant->getName())->setMpUserID($community->getMpUserID())->setCommunityID($community->getCommunityID())->setBoundCommunityID($newStore->getCommunityID())->setBoundStoreID($newStore->getStoreID())->insert();
                    $newStore->setBoundCommunityID($community->getCommunityID())->update();
                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '224',Directory::COMMUNITY_ID => $store->getCommunityID(),Directory::MP_USER_ID => $store->getMpUserID()]);
            }
            else
            {//有同名的供应商，并在其下添加餐厅
                $store = new Store([Store::TITLE => $communityRestaurant->getName(),Store::COMMUNITY_ID =>$community->getCommunityID() ,Store::IS_DELETE => 0]);

                if($store->isEmpty())
                {
                    $store = new Store();
                    $store->setTitle($communityRestaurant->getName())->setMpUserID($community->getMpUserID())->setCommunityID($community->getCommunityID())->setBoundCommunityID($newStore->getCommunityID())->setBoundStoreID($newStore->getBoundStoreID())->insert();
                }

            }

        }
        return [ 'errno' => 0 ];
    }

    public static function storeUpdate( $id, $data )
    {
        $obj = new Store([ Store::STORE_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $expr = sprintf("`store_id` != '%s' ",$id);
        $dbCondition = new \Bluefin\Data\DbCondition($expr);
        $condition = [$dbCondition,Store::TITLE =>  $data[Store::TITLE],Store::COMMUNITY_ID => $obj->getCommunityID(),Store::IS_DELETE => 0];
        $storeCounts = Store::fetchCount($condition);
        if($storeCounts >= 1)
        {
            return [ 'errno' => 1, 'error' => $data[Store::TITLE].'已存在，请重新输入' ];
        }
/*
        $communityRestaurant = new Community([Community::COMMUNITY_ID => $obj->getCommunityID()]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_RESTAURANT)
        { //找出同名的供应商
            $community = new Community([Community::NAME => $data[Store::TITLE],Community::MP_USER_ID => $obj->getMpUserID()]);
            if($community->isEmpty())
            { //没有同名的供应商，建立同名的供应商，并在其下添加餐厅
                $community = new Community();
                $community->setName($data[Store::TITLE])->setMpUserID($obj->getMpUserID())->setIsVirtual(0)->setCommunityType('procurement_supply')->setCity("待填")->setAddress("待填")->insert();
                SendTemplateBusiness::sendNewNotify($obj->getMpUserID(),$communityRestaurant->getName(),$data[Store::TITLE]);
                $store = new Store();
                $store->setTitle($communityRestaurant->getName())->setMpUserID($community->getMpUserID())->setCommunityID($community->getCommunityID())->setBoundCommunityID($obj->getCommunityID())->setBoundStoreID($id)->insert();
                DirectoryBusiness::copyTop([Directory::TOP_DIRECTORY_ID => '224',Directory::COMMUNITY_ID => $store->getCommunityID(),Directory::MP_USER_ID => $store->getMpUserID()]);
            }
            else
            {//有同名的供应商，并在其下添加餐厅
                $store = new Store([Store::TITLE => $communityRestaurant->getName(),Store::COMMUNITY_ID => $community->getCommunityID(),Store::IS_DELETE => 0]);
                if($store->isEmpty())
                {
                    $store = new Store();
                    $store->setTitle($communityRestaurant->getName())->setMpUserID($community->getMpUserID())->setCommunityID($community->getCommunityID())->setBoundCommunityID($obj->getCommunityID())->setBoundStoreID($id)->insert();
                }
            }

            $data[Store::BOUND_COMMUNITY_ID] = $community->getCommunityID();
            $data[Store::BOUND_STORE_ID] = $store->getStoreID();
        }
*/
        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function storeDelete( $id )
    {
        $obj = new Store([ Store::STORE_ID => $id ]);
        log_debug("=====================",$obj->getBoundCommunityID());
        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $communityRestaurant = new Community([Community::COMMUNITY_ID => $obj->getCommunityID()]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_RESTAURANT)
        {
            //找出同名的供应商
            $community = new Community([Community::COMMUNITY_ID => $obj->getBoundCommunityID(),Community::MP_USER_ID => $obj->getMpUserID() ,Community::COMMUNITY_TYPE => CommunityType::PROCUREMENT_SUPPLY ]);
            log_debug("==1111111111111111111===================",$community->getName());
            if($community->isEmpty())
            {
            }
            else
            {
                //删除此供应商下的餐厅
                $store = new Store([Store::STORE_ID => $obj->getBoundStoreID(),Store::COMMUNITY_ID => $community->getCommunityID(),Store::IS_DELETE => 0]);
                log_debug("======113333333333333333333333333===============",$store->getTitle());
                if(!$store->isEmpty())
                {
                    $store->setIsDelete("1")->update();
                }
            }
        }
        $obj->setIsDelete("1")->update();


        return [ 'errno' => 0 ];
    }

    public static function getCategoryList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return Category::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function categoryInsert( $data )
    {
        $obj = new Category();
        $communityRestaurant = new Community([Community::COMMUNITY_ID => $data[Category::COMMUNITY_ID]]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_RESTAURANT and $data[Category::IS_ON_SHELF] == 1)
        {
             $category = new Category([Category::IS_ON_SHELF => 1,Category::IS_DELETE => 0,Category::STORE_ID => $data[Category::STORE_ID]]);
            if(!$category->isEmpty())
            {
                return [ 'errno' => 1, 'error' => $category->getTitle()."已上架，请先下架此报价单" ];
            }
        }
        $obj->apply( $data );

        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function categoryUpdate( $id, $data )
    {
        $obj = new Category([ Category::CATEGORY_ID => $id ]);
        $communityRestaurant = new Community([Community::COMMUNITY_ID => $obj->getCommunityID()]);
        if($communityRestaurant->getCommunityType() == CommunityType::PROCUREMENT_RESTAURANT and $data[Category::IS_ON_SHELF] == 1)
        {
            $expr = sprintf("`category_id` != '%s' ",$id);
            $dbCondition = new \Bluefin\Data\DbCondition($expr);
            $condition = [$dbCondition,Category::IS_ON_SHELF => 1,Category::IS_DELETE => 0,Category::STORE_ID => $obj->getStoreID()];
            $category = new Category($condition);
            if(!$category->isEmpty() )
            {
                return [ 'errno' => 1, 'error' => $category->getTitle()."已上架，请先下架此报价单" ];
            }
        }
        if ($obj->isEmpty()) {
            log_debug( "Could not find Category($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->apply( $data );
        $obj->update();

        return [ 'errno' => 0 ];
    }

    public static function categoryCopy( $data )
    {
        log_debug("===================================",$data);
        $category = new Category([Category::CATEGORY_ID =>  $data[Category::CATEGORY_ID],Category::IS_DELETE => 0]);
        if ($category->isEmpty()) {
            return [ 'errno' => 1, 'error' => '找不到该ID对应的记录，请核对id' ];
        }
        //复制分类
        $newCategory = new Category();
        $newCategory->setCommunityID($data[Category::COMMUNITY_ID])->setMpUserID($data[Category::MP_USER_ID])->setStoreID($data[Category::STORE_ID])->setTitle($category->getTitle())->setComment($category->getComment())->setIsDelete('0')->setIsOnShelf('0')->setCoverImg($category->getCoverImg())->setDescription($category->getDescription())->insert();
        //复制产品
        $product  = Product::fetchColumn([Product::PRODUCT_ID],[Product::CATEGORY_ID => $data[Category::CATEGORY_ID]]);
        log_debug("===================================",$product);

            foreach($product as $v)
            {
                $dataProduct = Product::fetchOneRow(['*'],[Product::PRODUCT_ID => $v]);
                $dataProduct[Product::MP_USER_ID] = $data[Store::MP_USER_ID];
                $dataProduct[Product::COMMUNITY_ID] = $data[Store::COMMUNITY_ID];
                $dataProduct[Product::STORE_ID] =  $data[Category::STORE_ID];
                $dataProduct[Product::CATEGORY_ID] = $newCategory->getCategoryID();
                unset($dataProduct[Product::PRODUCT_ID]);
                unset($dataProduct[Product::_CREATED_AT]);
                log_debug("===================================",$dataProduct);
                $newProduct = new Product();
                $newProduct->apply($dataProduct)->insert();
            }


        return [ 'errno' => 0 ];
    }


    public static function categoryDelete( $id )
    {
        $obj = new Category([ Category::CATEGORY_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Category($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        try {
            $obj->setIsDelete("1")->setIsOnShelf('0')->update();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function getProductList( array $condition, array &$paging = null, $ranking,
                                           array $outputColumns = null)
    {

        return Product::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function productInsert( $data )
    {
        $obj = new Product();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function productUpdate( $id, $data )
    {
        $obj = new Product([ Product::PRODUCT_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Product($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->apply( $data );

        try {
            $obj->update();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function productDelete( $id )
    {
        $obj = new Product([ Product::PRODUCT_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find Product($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        try {
            $obj->setIsDelete("1")->setIsOnShelf('0')->update();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function getStoreData( $id )
    {
        $ret = [];
        $fields = [ Category::CATEGORY_ID, Category::TITLE, Category::DESCRIPTION ];
        $rows = Category::fetchRows(['*'], [Category::STORE_ID => $id,Category::IS_ON_SHELF => '1',Category::IS_DELETE => '0'], null, [Category::SORT_NO]);
        $ret['max_categories_id'] = end($rows)[Category::CATEGORY_ID];
        $ret['min_categories_id'] = $rows[0][Category::CATEGORY_ID];
        $ret['categories'] = [];
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $ret['categories'][] = $row;
            }
        }

        $fields = [ Product::PRODUCT_ID, Product::CATEGORY_ID, Product::TITLE, Product::DETAIL_URL,
                    Product::IMG_URL, Product::PRICE, Product::DESCRIPTION , Product::COMMENT];
        $rows = Product::fetchRows(['*'], [Product::STORE_ID => $id,Product::IS_DELETE => "0",Product::IS_ON_SHELF => '1'], null, [Category::SORT_NO]);
        $ret['products'] = [];
        if (count($rows) > 0) {
            foreach($rows as $row) {
                $row[Product::PRICE] = number_format($row[Product::PRICE],2);
                $ret['products'][$row[Product::CATEGORY_ID]][] = $row;
            }
        }

        return $ret;
    }
}
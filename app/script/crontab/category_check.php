<?php

require_once '../../../lib/Bluefin/bluefin.php';

use Bluefin\App;

use WBT\Business\Weixin\ReportBusiness;
use WBT\Business\Weixin\DirectoryBusiness;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\Category;
use MP\Model\Mp\Product;


category_check();


function category_check()
{
    date_default_timezone_set("Asia/Shanghai");
    $currentDate = date('Y:m:d');
    $mpUserID =  MpUser::fetchColumn([MpUser::MP_USER_ID],[MpUser::VALID => 1,MpUser::INDUSTRY => IndustryType::PROCUREMENT]);
    $category = Category::fetchRows(['*'],[MpUser::MP_USER_ID => $mpUserID]);
    log_debug("================".$currentDate);
    foreach($category as $key => $value)
    {
        $currentDate = explode(":",$currentDate);
        $currentDate = $currentDate[0].$currentDate[1].$currentDate[2];
        $valueDate= explode(":",$value[Category::SHELF_TIME]);
        $valueDate= $valueDate[0].$valueDate[1].$valueDate[2];

        if(  (int)$valueDate ==  (int)$currentDate)
        {
            $category = new Category([Category::CATEGORY_ID => $value[Category::CATEGORY_ID]]);
            if($category->getIsOnShelf() == 0)
            {
                $category->setIsOnShelf(1)->update();
                $storeID = $category->getStoreID();
                $expr = sprintf("`category_id` != '%s' ",$category->getCategoryID());
                $dbCondition = new \Bluefin\Data\DbCondition($expr);
                $condition = [$dbCondition,Category::STORE_ID => $storeID];
                $category->setIsOnShelf(0)->update($condition);
            }
        }

    }
}





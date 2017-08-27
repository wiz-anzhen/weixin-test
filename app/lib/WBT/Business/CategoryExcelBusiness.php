<?php

require_once realpath(__DIR__) .'/../../../../lib/Bluefin/bluefin.php';

set_include_path(get_include_path() . PATH_SEPARATOR . LIB.'/PHPExcel');


require_once LIB."/PHPExcel/PHPExcel.php";
require_once LIB.'/PHPExcel/PHPExcel/IOFactory.php';



use MP\Model\Mp\ProductUnitType;

// 业主录入
class CategoryExcelBusiness
{
    //菜单开始的行编号
    const OWNER_LINE = 2;

    //菜单属性列编号
    const COL_OWNER_TITLE= 1;
    const COL_OWNER_PRICE = 2;
    const COL_OWNER_UNIT = 3;

    //最大列编号
    const COL_OWNER_MAX_INDEX = 3;

    public static function readCommunityFromExcel($filename, $ext)
    {
        $res = ['errno' => 0, 'error' =>null, 'owner' => null ];
        $objReader = null;
        if($ext == 'xls')
        {
            $objReader = PHPExcel_IOFactory::createReader('Excel5');
        }
        elseif($ext == 'xlsx')
        {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }
        else
        {
            $res['errno'] = 1;
            $res['error'] = "不支持后缀为{$ext}的文件";
            log_error("error",$res);
            return $res;
        }

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);

        $objWorksheet  = $objPHPExcel->getActiveSheet();


        // 从1开始编号，遍历行
        $rowIndex = 1;
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            if($rowIndex >= self::OWNER_LINE)
            {
                //从编号1开始，遍历列
                $colIndex = 1;
                $owner = [];
                foreach ($cellIterator as $cell)
                {
                    $value = $cell->getValue();
                    // 菜单第一列不能为空
                    if($colIndex == 1 && empty($value))
                    {
                        break;
                    }

                    $r = self::updateOwnerRow($rowIndex,$colIndex, $value, $owner, $cell, $objWorksheet);
                    if($r['errno'] !== 0)
                    {
                        $res['errno'] = $r['errno'];
                        $res['error'] = $r['error'];
                        log_error("[error:{$r['error']}]");
                        return $res;
                    }

                    $colIndex++;
                    if($colIndex > self::COL_OWNER_MAX_INDEX)
                    {
                        break;
                    }
                }
                $res['owner'][] = $owner;
            }
            $rowIndex++;
        }
        return $res;
    }

    public static function updateOwnerRow($rowIndex,$colIndex, $value, &$row, $cell, $workSheet)
    {
        $res = ['errno' => 0, 'error' => ''];
        if ($colIndex ==  self::COL_OWNER_TITLE)
        {
            $row['title'] = trim($value);
            return $res;
        }

        if ($colIndex ==  self::COL_OWNER_PRICE)
        {
            $row['price'] = trim($value);
            return $res;
        }


        if ($colIndex ==  self::COL_OWNER_UNIT)
        {
            $row['unit'] = trim($value);
            $row['unit'] = "元/".$row['unit'];
            $unitTypes = ProductUnitType::getDictionary();
            // 数字格式
            if(!strict_in_array($row['unit'],$unitTypes))
            {
                $res = ['errno' => 1, 'error' => "[行:{$rowIndex}][列:{$colIndex}][内容:$value]单位错误"];
                return $res;
            }
            foreach($unitTypes as $key => $value)
            {
                if($row['unit'] == $value)
                {
                    $row['unit'] = $key;
                    break;
                }
            }
            return $res;
        }
        $res['errno'] = 1;
        $res['error'] = "[行:{$rowIndex}][列:{$colIndex}]未知列编号";
        return $res;

    }

    public static function insertFromExcel($data, $mpUserID, $communityId,$storeID,$categoryID)
    {
        if($data['errno'] != 0)
        {
            return false;
        }
        //log_debug("========================",$data);
        //循环遍历每个业主，进行操作
        try
        {
            foreach ($data['owner'] as $owner)
            {
                if(empty($owner))
                {
                    continue;
                }

                    $product = new \MP\Model\Mp\Product();
                $product->setMpUserID($mpUserID)
                        ->setCommunityID($communityId)
                       ->setStoreID($storeID)->setCategoryID($categoryID)->setTitle($owner['title'])->setPrice($owner['price'])->setProductUnit($owner['unit'])->setIsOnShelf("1")
                        ->insert(true);
            }
            return true;
        }
        catch (\Exception $e)
        {
            log_error("exception:",$e->getMessage());
            return false;
        }
    }

}
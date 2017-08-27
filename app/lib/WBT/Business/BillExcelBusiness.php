<?php


require_once realpath(__DIR__) .'/../../../../lib/Bluefin/bluefin.php';

set_include_path(get_include_path() . PATH_SEPARATOR . LIB.'/PHPExcel');


require_once LIB."/PHPExcel/PHPExcel.php";
require_once LIB.'/PHPExcel/PHPExcel/IOFactory.php';
require_once LIB.'/PHPExcel/PHPExcel/Worksheet.php';



use MP\Model\Mp\Community;

class BillExcelBusiness
{
    //菜单开始的行编号
    const OWNER_LINE = 6;
    //从第三行读取小区名称
    const COMMUNITY_NAME_LINE=3;
    //菜单属性列编号
    const COL_HOUSE_NO = 2;
    const COL_PHONE = 6;
    const COL_NAME = 5;
    const COL_HOUSE_ADDRESS = 3;
    const COL_HOUSE_COL = 4;
    const COL_TOTAL_PAYMENT = 11;
    const COL_BILL_DETAIL_NAME = 7;
    const COL_BILLING_CYCLE = 8;
    const COL_DETAIL_PAYMENT = 9;
    const COL_DETAIL_REMARKS = 13;

    //最大列编号
    const COL_OWNER_MAX_INDEX = 13;

    public static function readBillFromExcel($filename, $ext,$communityId)
    {
        $res = ['errno' => 0, 'error' =>null, 'billOwner' => null ];
        //获取小区名称
        $community = new Community([Community::COMMUNITY_ID=>$communityId]);
        $communityName = $community->getName();

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
            //获取小区名称
            if($rowIndex == self::COMMUNITY_NAME_LINE)
            {
                $colIndex =1;
                foreach($cellIterator as $cell)
                {
                    $value = $cell->getValue();
                    if($colIndex==2 && (!empty($value)))
                    {
                        $communityName = self::getCommunityName($rowIndex,$colIndex, $value);
                    }
                    $colIndex++;
                    if($colIndex > 2)
                    {
                        break;
                    }
                }
            }

            if($rowIndex >= self::OWNER_LINE)
            {
                //从编号1开始，遍历列
                $colIndex = 1;
                $owner = [];//bill表数据
                foreach ($cellIterator as $cell)
                {
                    $value = $cell->getValue();
                    //组合bill表数据
                    if(($colIndex == 2 || $colIndex == 3 || $colIndex == 4 || $colIndex == 5 || $colIndex == 6|| $colIndex == 11) && (!empty($value)))
                    {

                        $result=self::updateBillOwnerRow($communityName,$rowIndex,$colIndex, $value, $owner);
                        if($result['errno'] !== 0)
                        {
                            $res['errno'] = $result['errno'];
                            $res['error'] = $result['error'];
                            log_error("[error:{$result['error']}]");
                           return $res;
                        }
                    }
                    //组合bill_detail数据
                    if(($colIndex == 7 ||$colIndex == 8|| $colIndex == 9|| $colIndex == 13) && !empty($value))
                    {
                        $r = self::updateBillDetail($rowIndex,$colIndex,$value,$owner);
                        if($r['errno'] !== 0)
                        {
                            $res['errno'] = $r['errno'];
                            $res['error'] = $r['error'];
                            log_error("[error:{$r['error']}]");
                            return $res;
                        }
                    }

                    $colIndex++;
                    if($colIndex > self::COL_OWNER_MAX_INDEX)
                    {
                        break;
                    }

                }

                $res['billOwner'][] = $owner;
            }

            $rowIndex++;
        }
        return $res;
    }

    public static function updateBillDetail($rowIndex,$colIndex, $value, &$row)
    {
        $res = ['errno' => 0, 'error' => ''];
        if ($colIndex ==  self::COL_BILL_DETAIL_NAME)
        {
            if(!empty($value))
            {
                $row['bill_detail_name'] = trim($value);
            }
            else
            {
                $res = ['errno' =>1, 'error' => 'Error:欠费项目名称不能为空'];
            }
            return $res;
        }
        if ($colIndex ==  self::COL_BILLING_CYCLE)
        {
            if(!empty($value))
            {
                $row['billing_cycle'] = trim($value);
            }
            else
            {
                $res = ['errno' =>1, 'error' => 'Error:计费周期不能为空'];
            }
            return $res;
        }
        if ($colIndex ==  self::COL_DETAIL_PAYMENT)
        {
            if(is_numeric($value))
            {
                $row['detail_payment'] = trim($value);
            }
            else
            {
                $res = ['errno' =>1, 'error' => "Error:[行:{$rowIndex}][列:{$colIndex}]应收金额为数字"];
            }
            return $res;
        }
        if ($colIndex ==  self::COL_DETAIL_REMARKS)
        {
            $row['detail_remarks'] = trim($value);
            return $res;
        }
        $res['errno'] = 1;
        $res['error'] = "[行:{$rowIndex}][列:{$colIndex}]未知列编号";
        return $res;
    }



    public static function updateBillOwnerRow($communityName,$rowIndex,$colIndex, $value, &$row)
    {
        $res = ['errno' => 0, 'error' => ''];
        if ($colIndex ==  self::COL_HOUSE_NO)
        {
            $row['house_no'] = trim($value);
            return $res;
        }
        if ($colIndex ==  self::COL_NAME)
        {
            if(!empty($value))
            {
                $row['name'] = trim($value);
            }
            else
            {
                $res = ['errno' =>1, 'error' => "Error:[行:{$rowIndex}][列:{$colIndex}]业主姓名不能为空"];
            }

            return $res;
        }
        if($colIndex ==  self::COL_PHONE)
        {
            $row['phone'] = trim($value);
            return $res;
        }
        if ($colIndex ==  self::COL_HOUSE_ADDRESS)
        {
            if(!empty($value))
            {

                $row['house_address'] = trim($value);

            }
            else
            {
                $res = ['errno' =>1, 'error' => "Error:[行:{$rowIndex}][列:{$colIndex}]地址不能为空"];
            }

            return $res;
        }
        if ($colIndex ==  self::COL_HOUSE_COL)
        {
            $row['house_col'] = trim($value);
            return $res;
        }
        if ($colIndex ==  self::COL_TOTAL_PAYMENT )
        {
            if(is_numeric($value))
            {
                $row['total_payment'] = trim($value);

            }
            else
            {
                $res = ['errno' =>1, 'error' => "Error:[行:{$rowIndex}][列:{$colIndex}]应收金额为数字"];
            }
            return $res;

        }
        $res['errno'] = 1;
        $res['error'] = "[行:{$rowIndex}][列:{$colIndex}]未知列编号";
        return $res;
    }


    public static function getCommunityName($rowIndex,$colIndex, $value)
    {
        $res = "";
        preg_match('/小区： (.*?) 记录数/',$value,$matches);
        if(!empty($matches[1]))
        {
            $res=trim($matches[1]);
        }
        else
        {
            $res = "[行:{$rowIndex}][列:{$colIndex}]获取不到小区名称";
        }

        return $res;
    }
}
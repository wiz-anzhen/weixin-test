<?php


require_once realpath(__DIR__) .'/../../../../lib/Bluefin/bluefin.php';

set_include_path(get_include_path() . PATH_SEPARATOR . LIB.'/PHPExcel');


require_once LIB."/PHPExcel/PHPExcel.php";
require_once LIB.'/PHPExcel/PHPExcel/IOFactory.php';





// 业主录入
class HouseMemberExcelBusiness
{
    //菜单开始的行编号
    const OWNER_LINE = 4;

    //菜单属性列编号
    const COL_OWNER_HOUSE_NO = 1;
    const COL_OWNER_HOUSE_ADDRESS = 2;
    const COL_OWNER_NAME = 3;
    const COL_OWNER_BIRTHDAY = 4;
    const COL_OWNER_TEL = 5;

    //最大列编号
    const COL_OWNER_MAX_INDEX = 5;

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
        if ($colIndex ==  self::COL_OWNER_HOUSE_NO)
        {
            $row['house_no'] = trim($value);
            return $res;
        }

        if ($colIndex ==  self::COL_OWNER_HOUSE_ADDRESS)
        {
            $row['house_address'] = trim($value);
            return $res;
        }

        if ($colIndex ==  self::COL_OWNER_NAME)
        {
            $row['name'] = $value;
            return $res;
        }

        if ($colIndex ==  self::COL_OWNER_BIRTHDAY)
        {
            $row['birthday'] = null;

            if(empty($value))
            {
                return $res;
            }

            // 数字格式
            if($cell->getDataType()==PHPExcel_Cell_DataType::TYPE_NUMERIC)
            {
                $format =  $workSheet->getStyle( $cell->getCoordinate())->getNumberFormat()->getFormatCode();
                $phpValue = PHPExcel_Shared_Date::ExcelToPHP($value);
                $dateValue = date('Ymd',$phpValue);
                $row['birthday'] = $dateValue;
                return $res;
            }


            // 文本格式
            $arrBirthday    = explode("-", $value);
            if(count($arrBirthday) != 3)
            {
                $res = ['errno' => 1, 'error' => "[行:{$rowIndex}][列:{$colIndex}][内容:$value]生日格式错误,格式示例：1983-1-1"];
                return $res;
            }

            if (is_numeric($arrBirthday[0]) && is_numeric($arrBirthday[1]) && $arrBirthday[2])
            {
                if ($arrBirthday[1] < 10)
                {
                    $arrBirthday[1] = "0" . $arrBirthday[1];
                }

                if ($arrBirthday[2] < 10)
                {
                    $arrBirthday[2] = "0" . $arrBirthday[2];
                }

                $value           = implode("", $arrBirthday);
                $row['birthday'] = $value;
            }
            else
            {
                $res = ['errno' => 1, 'error' => "[行:{$rowIndex}][列:{$colIndex}][内容:$value]生日格式错误,格式示例：1983-1-1"];
            }

            return $res;
        }

        if ($colIndex ==  self::COL_OWNER_TEL)
        {

            $row['phone1'] = null;
            $row['phone2'] = null;
            $row['phone3'] = null;

            $arrPhone = explode(",",$value);
            $validPhone = [];
            foreach($arrPhone as $phone)
            {
                $arrP = explode(' ', $phone);
                foreach($arrP as $p)
                {
                    $p = trim($p);
                    if(is_numeric($p) && strlen($p) == 11)
                    {
                        $validPhone[] = $p;
                    }
                    else
                    {
                        log_warn("[行:{$rowIndex}][列:{$colIndex}]invalid phone:$p");
                    }
                }
            }

            if(isset($validPhone[0]))
            {
                $row['phone1'] = $validPhone[0];
            }


            if(isset($validPhone[1]))
            {
                $row['phone2'] = $validPhone[1];
            }


            if(isset($validPhone[2]))
            {
                $row['phone3'] = $validPhone[2];
            }

            return $res;
        }

        $res['errno'] = 1;
        $res['error'] = "[行:{$rowIndex}][列:{$colIndex}]未知列编号";
        return $res;

    }

}
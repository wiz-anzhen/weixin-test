<?php

require_once '../lib/Bluefin/bluefin.php';
require_once APP . "/lib/WBT/Business/BillExcelBusiness.php";
require_once 'MpUserServiceBase.php';

use Bluefin\App;
use MP\Model\Mp\BillDay;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\Bill;

use MP\Model\Mp\Directory;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\BillBusiness;


define('EXCEL_PATH', ROOT . "/excel/");

class BillFileUploadService extends MpUserServiceBase{
    //上传

    public function fileUpload()
    {
        $result = ['errno' => 0];
        $mpUserID = $_GET['mp_user_id'];
        $communityId = $_GET['community_id'];
        $billDay = $_GET['bill_day'];
        $relativePath = self::getRelativeDir();
        if(empty($relativePath))
        {
            return [ 'errno' => 1, 'error' => '上传失败' ];
        }

        if (!empty($_FILES) )
        {
            $fileParts = pathinfo($_FILES['Filedata']['name']);
            $ext = $fileParts['extension'];
            $fileType = strtolower($ext);

            if ($fileType == 'xlsx') // File extensions
            {
                $fileTmpName = $_FILES['Filedata']['tmp_name'];
                log_debug("[$fileTmpName][$fileType]");
                try
                {
                    $res = BillExcelBusiness::readBillFromExcel($fileTmpName, $fileType,$communityId);

                    if ($res['errno'] == 0)
                    {
                        $checkResult = BillBusiness::checkHouseMemberAddress($res,$communityId);
                        //$checkResult['errno'] =0;
                        if($checkResult['errno'] == 0)
                        {
                            if (!BillBusiness::insertFromExcel($res, $mpUserID, $communityId,$billDay))
                            {
                                $result['errno'] = 1;
                                $result['error'] = '写入数据库时出错。';
                            }
                        }
                        else
                        {
                            $result['errno'] = $checkResult['errno'];
                            $result['error'] = $checkResult['error'];
                        }

                    }
                    else
                    {
                        $result['errno'] = $res['errno'];
                        $result['error'] = $res['error'];
                    }


                }
                catch (\Exception $e)
                {
                    $result['errno'] = 1;
                    $result['error'] = $e->getMessage();
                }

            }
            else
            {
                $result['errno'] = 1;
                $result['error'] = "请使用后缀为.xlsx的Excle文件";
            }
        }

        return $result;
    }

    private function getRelativeDir()
    {
        $ymd =  date( 'Ymd' );
        // name and path
        $relativePath = "/ueditor/php/upload/$ymd/";

        $imgRealPath = WEB_ROOT . $relativePath;
        if (!is_dir( $imgRealPath ))
        {
            mkdir( $imgRealPath );
        }

        if (!is_dir( $imgRealPath ))
        {
            log_fatal("创建文件夹失败。[imgRealPath:$imgRealPath]");
            return null;
        }

        return $relativePath;
    }
}


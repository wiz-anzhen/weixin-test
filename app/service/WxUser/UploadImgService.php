<?php

use Bluefin\Service;

class UploadImgService extends Service
{
    public function upload()
    {
        $res["error"] = "";//错误信息
        $res["img_url"] = "";//图片地址
        $date_time = date("Ymd");
        $upload_dir = WEB_ROOT."/ueditor/php/upload/".$date_time;
        log_debug("1111111111111".$upload_dir);
        if(!is_dir($upload_dir)){
            mkdir($upload_dir , 0755);
        }
        header("Content-type: text/html; charset=utf-8");
        $fileTypes = array('jpg','JPG','jpeg','JPEG','png','PNG','gif','GIF','bmp','BMP');

        $maxSize = 1 * pow(2,200);
        $myFile = $_FILES['fileToUpload'];
        $myFileType = substr($myFile['name'], strrpos($myFile['name'], ".") + 1);
        if ($myFile['size'] > $maxSize)
        {
            $res["error"] = "图片尺寸最大为".$maxSize."请选择小一些的图片";
        }
        else if (!in_array($myFileType, $fileTypes))
        {
            $res["error"] = "'错误的文件类型，必须是 jpg/jpeg/png/gif/bmp 格式图片'";
        }
        else if (is_uploaded_file($myFile['tmp_name']))
        {
            date_default_timezone_set('PRC');
            $timStamp = date("His");
            $rand = sprintf("%06d",rand());
            $task_no =  $timStamp.$rand;
            $toFile = $upload_dir.'/' .$task_no.'.'. $myFileType;
            log_debug("--------99999------".$toFile);
            if (move_uploaded_file($myFile['tmp_name'], $toFile))
            {
                $img_url = explode("webroot/",$toFile);
                $host = \WBT\Business\ConfigBusiness::getHost();
                $res["img_url"] = $host."/".$img_url[1];
                log_debug("-----0000000000000---------".$res["img_url"]);
            } else {
                $res["error"] = "上传失败";
            }
        }
        else
        {
            $res["error"] = "上传失败";
        }

        echo json_encode($res);

    }
}

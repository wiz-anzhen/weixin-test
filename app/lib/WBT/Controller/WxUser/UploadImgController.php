<?php
/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-8-6
 * Time: 下午12:48
 */
namespace WBT\Controller\WxUser;

use Common\Helper\BaseController;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\AppUser;
class UploadImgController extends BaseController
{

    public function indexAction()
    {
        $res["error"] = "";//错误信息
        $res["img_url"] = "";//图片地址
        $id = $this->_request->get('id');
        log_debug("----------------------------------------------".$id);
        $date_time = date("Ymd");
        $upload_dir = WEB_ROOT."/ueditor/php/upload/" .$date_time;
        log_debug("--------------------------------------------".$upload_dir);
        if(!is_dir($upload_dir)){
            mkdir($upload_dir , 0755);
        }

        header("Content-type: text/html; charset=utf-8");
        $fileTypes = array('jpg','JPG','jpeg','JPEG','png','PNG','gif','GIF','bmp','BMP');

        $maxSize = 1 * pow(2,200);
        $myFile = $_FILES[$id];
        log_debug("==========================",$myFile);
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
             log_debug("=========================".$toFile);
            if (move_uploaded_file($myFile['tmp_name'], $toFile))
            {
                $img_url = explode("webroot/",$toFile);
                $host =  ConfigBusiness::getHost();//获取主机名
                $res["img_url"] = $host."/".$img_url[1];
                log_debug("=========================".$res["img_url"]);
            }
            else {
                $res["error"] = "上传失败";
            }
        }
        else
        {
            $res["error"] = "上传失败";
        }
        $id = explode("_",$id);
        $res["id"] = $id[1];
        echo json_encode($res);
    }

    public function appUploadAction()
    {
        $res["error"] = "";//错误信息
        $res["img_url"] = "";//图片地址
        $id = $this->_request->get('filename');
        $phone = $this->_request->get('phone');
        log_debug("----------------------------------------------".$id);
        $date_time = date("Ymd");
        $upload_dir = WEB_ROOT."/ueditor/php/upload/" .$date_time;
        log_debug("--------------------------------------------".$upload_dir);
        if(!is_dir($upload_dir)){
            mkdir($upload_dir , 0755);
        }

        header("Content-type: text/html; charset=utf-8");
        $fileTypes = array('jpg','JPG','jpeg','JPEG','png','PNG','gif','GIF','bmp','BMP');

        $maxSize = 1 * pow(2,200);
        $myFile = $_FILES[$id];
        log_debug("==========================",$myFile);
        log_debug("files========================",$_FILES);
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
            log_debug("=========================".$toFile);
            if (move_uploaded_file($myFile['tmp_name'], $toFile))
            {
                $img_url = explode("webroot/",$toFile);
                $host =  ConfigBusiness::getHost();//获取主机名
                $res["img_url"] = $host."/".$img_url[1];
                $appUser =  new AppUser([AppUser::PHONE=>$phone]);
                if(!$appUser->isEmpty())
                {
                    $appUser->setHeadPic($res['img_url'])->update();
                }
                log_debug("=========================".$res["img_url"]);
            }
            else {
                $res["error"] = "上传失败";
            }
        }
        else
        {
            $res["error"] = "上传失败";
        }
        $id = explode("_",$id);
        $res["id"] = $id[1];
        $result['errno'] = 0;
        $result['msg'] = '上传成功';
        $result['data'] = $res;
        echo json_encode($result);
    }

}
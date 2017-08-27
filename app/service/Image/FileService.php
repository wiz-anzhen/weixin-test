<?php

use Bluefin\App;
use Bluefin\Service;


class FileService extends Service
{
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

    private function getRandomFileName()
    {
        return md5( uniqid( php_uname( 'n' ), true ) );
    }


    // 以拖动方式上传文件， 仅支持HTML5的浏览器有效
    public function uploadByDrag()
    {
        $request  = App::getInstance()->request();
        $imgData  = $request->get( 'img_data' );
        $fileType = $request->get( 'file_type' );

        $supportedTypes = [ 'image/jpeg', 'image/jpg', 'image/png','image/gif','image/bmp' ];

        // file type
        if (!strict_in_array( $fileType, $supportedTypes ))
        {
            log_warn("错误的文件后缀，[fileType:$fileType]");
            return [ 'errno' => 1, 'error' => '错误的文件类型，必须是 jpg/jpeg/png/gif/bmp 格式图片' ];
        }

        $relativePath = self::getRelativeDir();
        if(empty($relativePath))
        {
            return [ 'errno' => 1, 'error' => '图片上传失败' ];
        }

        $fileExt   = '.jpg';

        switch ($fileType) {
            case 'image/jpg':
                $fileExt = '.jpg';
                break;
            case 'image/jpeg':
                $fileExt = '.jpeg';
                break;
            case 'image/png':
                $fileExt = '.png';
                break;
            case 'image/gif':
                $fileExt = '.gif';
                break;
            case 'image/bmp':
                $fileExt = '.bmp';
                break;
            default:
                break;
        }

        $imgName     = self::getRandomFileName() . $fileExt;
        $imgRealPath = WEB_ROOT . $relativePath;

        $imgFullName = $imgRealPath . $imgName;

        // save file
        $fp = fopen( $imgFullName, 'w' );
        fwrite( $fp, base64_decode( $imgData ) );
        fclose( $fp );
        if (!file_exists( $imgFullName ))
        {
            log_fatal("写文件失败。[imgFullName:$imgFullName]");
            return [ 'errno' => 1, 'error' => '文件上传失败' ];
        }

        $url = get_host() . $relativePath . $imgName;

        return [ 'errno' => 0, 'url' => $url ];
    }

    /*
    第三方插件 Uploadify，通过flash上传文件
    Copyright (c) 2012 Reactive Apps, Ronnie Garcia
    Released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
    */
    public function uploadByFlash()
    {
        $relativePath = self::getRelativeDir();
        if(empty($relativePath))
        {
            return [ 'errno' => 1, 'error' => '图片上传失败' ];
        }

        if (!empty($_FILES) )
        {

            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;

            // Validate the file type
            $fileTypes = array('jpg','jpeg','gif','png','bmp'); // File extensions
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            $ext = $fileParts['extension'];
            $ext = strtolower($ext);
            if (!strict_in_array($ext,$fileTypes))
            {
                log_warn("错误的文件后缀，[fileType:$ext]");
                return [ 'errno' => 1, 'error' => '错误的文件类型，必须是 jpg/jpeg/png/gif/bmp 格式图片' ];
            }

            $imgName     = self::getRandomFileName() . '.'. $ext;
            $imgRealPath = WEB_ROOT . $relativePath;
            $imgFullName = $imgRealPath . $imgName;
            move_uploaded_file($tempFile, $imgFullName);

            if (!file_exists($imgFullName))
            {
                log_fatal("写文件失败。[imgFullName:$imgFullName]");
                return ['errno' => 1, 'error' => '文件上传失败'];
            }
            $url = get_host() . $relativePath . $imgName;
            return [ 'errno' => 0, 'url' => $url ];
        }

        return [ 'errno' => 1, 'error' => '空的文件' ];
    }
}
<?php

use Bluefin\App;

require_once 'ServiceBase.php';

class CommonService extends ServiceBase
{
    public function uploadImg()
    {
        $request  = App::getInstance()->request();
        $imgData  = $request->get( 'img_data' );
        $fileType = $request->get( 'file_type' );
        $mpUserId = $request->get( 'mp_user_id' );
        if (empty($mpUserId)) $mpUserId = 'default';

        $supportedTypes = [ 'image/jpeg', 'image/jpg', 'image/png' ];
        $fileName       = md5( uniqid( php_uname( 'n' ), true ) . '_' . md5( mt_rand() ) );

        // file type
        if (!in_array( $fileType, $supportedTypes ))
            return [ 'errno' => 1, 'error' => '错误的文件类型，必须是图片（jpg/jpeg/png）' ];

        // name and path
        $relativePath = '/images/upload/' . $mpUserId . '/';
        $fileExt      = '.jpg';
        if ($fileType == 'image/png') $fileExt = '.png';
        $imgName     = $fileName . $fileExt;
        $imgRealPath = WEB_ROOT . $relativePath;
        if (!is_dir( $imgRealPath )) mkdir( $imgRealPath );
        if (!is_dir( $imgRealPath )) return [ 'errno' => 1, 'error' => "创建存储文件夹($relativePath)失败，请联系技术支持单位解决。" ];
        $imgFullName = $imgRealPath . $imgName;

        // save file
        $fp = fopen( $imgFullName, 'w' );
        fwrite( $fp, base64_decode( $imgData ) );
        fclose( $fp );
        if (!file_exists( $imgFullName ))
            return [ 'errno' => 1, 'error' => '保存文件失败，请重试或联系技术支持单位。' ];

        // store to db
        $imgUrl = get_host() . $relativePath . $imgName;

        return [ 'errno' => 0, 'img_url' => $imgUrl ];
    }
}
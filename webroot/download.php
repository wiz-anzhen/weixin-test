<?php

$env = get_cfg_var('kingcores_app_env');
if ("online" == $env)
{
    //生成内容目录
    define( 'BASE_DIR' , '/kingcores/git/changweibo/rnd-general/changweibo/wwwroot' );

    //png页面生成目录
    define( 'IMG_DIR' , BASE_DIR . '/user_img/');
}
else
{
    //生成内容目录
    define( 'BASE_DIR' , '/mnt/hgfs/git/rnd-general/changweibo/wwwroot' );

    //png页面生成目录
    define( 'IMG_DIR' , BASE_DIR . '/user_img/');
}

$file_name = $_REQUEST['file'] .'.png';
$absolute_file_name = IMG_DIR . $file_name ;

if (!file_exists($absolute_file_name)) {
    echo "文件不存在";
    exit; 
} else {
    $file = fopen($absolute_file_name,"r"); // 打开文件
    Header("Content-type: application/octet-stream");
    Header("Accept-Ranges: bytes");
    Header("Accept-Length: ".filesize($absolute_file_name));
    //Header("Content-Disposition: attachment; filename=" . $file_name);
    Header("Content-Disposition: attachment; filename=changweibo.png");
    echo fread($file,filesize($absolute_file_name));
    fclose($file);
    exit;
}


?>

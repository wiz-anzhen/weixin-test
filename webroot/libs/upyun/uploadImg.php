<?php

require_once '../../../lib/Bluefin/bluefin.php';
use Bluefin\App;
use WBT\Business\UserBusiness;
use CWB\Business\CwbBusiness;

include_once("WeibotuiUpyun.php");

function imgupload()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $imgFile = $_FILES['logo_file'];
        $imgFileName = $imgFile['name'];
        $imgFileTmpName = $imgFile['tmp_name'];
        $imgFileType = substr($imgFileName, strrpos($imgFile['name'], ".") + 1);
        $imgName = $imgFileTmpName . '.' . $imgFileType;
        sleep(5);
        move_uploaded_file($imgFileTmpName, $imgName);
        $url = WeibotuiUpyun::uploadImage($imgName);
    }
    return $url;
}

function imgtoUpyun($imgName)
{
    $url = WeibotuiUpyun::uploadImage($imgName);
    return $url;
}

$fileName = imgupload();
$url = imgtoUpyun($fileName);

$userID = UserBusiness::getLoginUserID();

if (empty($userID))
{
    $result = 0;
}
$result = $url;
CwbBusiness::updateUserLogoPath($userID, $result);
?>

<script type="text/javascript">
    window.top.window.stopUpload("<?php echo $result; ?>");
</script>
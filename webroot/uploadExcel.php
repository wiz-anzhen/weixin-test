<?php

require_once '../lib/Bluefin/bluefin.php';
require_once APP. "/lib/WBT/Business/ExcelBusiness.php";

use \WBT\Business\Weixin\HouseMemberBusiness;

define('EXCEL_PATH', ROOT . "/excel/");

$mpUserID = $_POST['mp_user_id'];
$communityId = $_POST['community_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $result = array( 'errno' => 1, 'error' => null);
    $file = $_FILES['img_file'];
    $fileName = $file['name'];
    $fileType = substr($fileName, strrpos($file['name'], ".") + 1);

    if($fileType == 'xls' || $fileType == 'xlsx')
    {
        $fileTmpName = $file['tmp_name'];

        $res = BillExcelBusiness::readCommunityFromExcel($fileTmpName, $fileType);
        if ($res['errno'] == 0)
        {
            if (HouseMemberBusiness::insertFromExcel($res, $mpUserID, $communityId))
            {
                $result['errno'] = 0;
            }
            else
            {
                $result['errno'] = 1;
                $result['error'] = '写入数据库时出错。';
            }
        }
        else
        {
            $result['errno'] = $res['errno'];
            $result['error'] = $res['error'];
        }
    }
    else
    {
        $result['errno'] = 1;
        $result['error'] = "不支持{$fileType}格式的文件";
    }
}
?>

<script type="text/javascript">
    window.top.window.stopUpload("<?php echo $result['errno']; ?>", "<?php echo $result['error']; ?>");
</script>
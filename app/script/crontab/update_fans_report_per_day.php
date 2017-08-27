<?php

require_once '../../../lib/Bluefin/bluefin.php';

use Bluefin\App;

use WBT\Business\Weixin\ReportBusiness;
use WBT\Business\Weixin\DirectoryBusiness;
use MP\Model\Mp\MpUser;



update_fans_report();


function update_fans_report()
{
    $rows =  MpUser::fetchRows([MpUser::MP_USER_ID],[MpUser::VALID => 1]);
    foreach($rows as $row)
    {
        $mpUserID = $row[MpUser::MP_USER_ID];
        log_info("[mpUserID:$mpUserID]");
        ReportBusiness::initReport($mpUserID);
    }
}





<?php

require_once '../../../lib/Bluefin/bluefin.php';

use Bluefin\App;

use MP\Model\Mp\Report;
use MP\Model\Mp\MpUser;
use WBT\Business\Weixin\ReportBusiness;
use WBT\Business\Weixin\MpUserBusiness;
use Bluefin\Data\Database;
use Bluefin\HTML\Table;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\CommunityReport;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\HouseMemberBusiness;
use WBT\Business\Weixin\CommunityBusiness;

use MP\Model\Mp\Directory;
use MP\Model\Mp\DirectoryDailyTraffic;
use MP\Model\Mp\DirectoryWxUserVisit;

sendEmail();


function sendEmail() {
    //公众账号级别报表
    $mpUsers = MpUser::fetchRows( [ MpUser::MP_USER_ID, MpUser::MP_NAME ],
        [ MpUser::VALID => 1, MpUser::SEND_REPORT => 1] );
    log_info( count( $mpUsers ) . '个店需要检查。' );
    if (count($mpUsers) == 0)
    {
        exit;
    }
    $totalValidOrderCount = 0;
    $yesterdayTitle = date('Y-m-d', strtotime('-1 day'));
    $ymd = date('Ymd', strtotime('-1 day'));
    //$ymd = '20140519';
    foreach ($mpUsers as $mpUser)
    {
        $tableString = "<table><tbody>";
        $mpUserId = $mpUser[MpUser::MP_USER_ID];
        $tableString .= "<tr><td><table  style='border: 1px solid #777;width: 800px;' cellspacing='0' border='1' cellpadding='0'>
    <thead><th colspan='8'>{$yesterdayTitle}-{$mpUser[MpUser::MP_NAME]}-运营报表</th></thead>
    <tbody>
    <tr><td width='18%'>日期</td><td width='10%'>粉丝总数</td><td width='13%'>认证住户数</td><td width='10%'>住户总数</td><td width='13%'>认证业主数</td><td width='10%'>业主总数</td></tr>
    ";
        for($i=1;$i<=60;$i++)
        {
            $yesterday = date('Ymd', strtotime('-'.$i.' day'));
            $condition = [Report::MP_USER_ID => $mpUserId, Report::YMD => $yesterday, ];
            if(Report::fetchOneRow(['*'], $condition)){
                if(MpUserBusiness::setMpUserCounts($mpUserId,$ymd))
                {
                /** 报表字段：日期、公众帐号名称、粉丝总数、活跃粉丝数（uv），粉丝访问量（pv），认证住户数、住户总数、认证业主数，业主总数 。* */
                $data = Report::fetchOneRow(['*'], $condition);
                if (count($data) == 0) continue;
                $totalValidOrderCount += $data['valid_order_count'];
                    $tableString .= "
            <tr><td>{$yesterday}</td><td>{$data['fans_total_count']}</td><td>{$data['zhuhu_verify']}</td><td>{$data['zhuhu_count']}</td><td>{$data['yezhu_verify']}</td><td>{$data['yezhu_count']}</td></tr>
    ";
                }
            }
    }
        $tableString .= "</tbody></table>
</td></tr>";
    $tableString .= '</tbody></table>';
        log_info( '开始执行到家小区报表发送。' );
        //小区级别报表
        $communityArrs = Community::fetchRows( [ Community::COMMUNITY_ID, Community::NAME ],
            [ Community::IS_VIRTUAL => 1, Community::MP_USER_ID => $mpUserId] );
        log_info( count( $communityArrs ) . '个小区需要检查。' );
        $totalValidOrderCount = 0;
        //$yesterday = date('Ymd', strtotime('-1 day'));
        $yesterdayTitle = date('Y-m-d', strtotime('-1 day'));
        $tableStringT = "<table> <tbody>
        <tr><td><table  style='border: 1px solid #777;width: 800px;' cellspacing='0' border='1' cellpadding='0'>
        <athead> <th colspan='9'>{$yesterdayTitle}-{$mpUser[MpUser::MP_NAME]}-小区运营报表</th></thead>
        <tbody>
        <tr><td width='20%'>小区</td><td width='20%'>认证住户数</td><td width='20%'>住户总数</td><td width='20%'>认证业主数</td><td width='20%'>业主总数</td></tr>
        ";

        foreach ($communityArrs as $communityArr)
        {
            $communityID = $communityArr[Community::COMMUNITY_ID];
            if(CommunityBusiness::setCounts($communityID,$mpUserId,$ymd))
            {
                $condition = [CommunityReport::MP_USER_ID => $mpUserId,CommunityReport::COMMUNITY_ID => $communityID, CommunityReport::YMD => $ymd];
                $data = CommunityReport::fetchOneRow(['*'], $condition);
                if (count($data) == 0) continue;
                $totalValidOrderCount += $data['valid_order_count'];
                $cp = new Community([Community::MP_USER_ID => $mpUserId,Community::COMMUNITY_ID => $communityID]);
                $communityName = $cp->getName();
                //小区报表字段：小区、认证住户数、住户总数、认证业主数，业主总数。
                $tableStringT .= "
                <tr><td>{$communityName}</td><td>{$data['zhuhu_verify']}</td><td>{$data['zhuhu_count']}</td><td>{$data['yezhu_verify']}</td><td>{$data['yezhu_count']}</td></tr>
                ";
            }
        }
        $tableStringT .= '</tbody></table>
        </td></tr></tbody></table><br/><br/>';

    $table = $tableStringT . $tableString;
   // $recipient = [ 'lizhicai@kingcores.com', 'cuiguangbin@kingcores.com', 'anzhen@kingcores.com' ];
    $recipient = [ 'auto-report@kingcores.com'];
    $title = '[' . date('Y-m-d', time()-3600*24) . '][spm]'.$mpUser[MpUser::MP_NAME].'业务报表';
    ReportBusiness::sendMailToMultiRecipients($recipient, $title, $table);
  }
}


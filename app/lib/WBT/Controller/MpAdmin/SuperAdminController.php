<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\MpUserConfig;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\SuperAdminBusiness;
use WBT\Controller\WBTControllerBase;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\ProcurementOrder;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use WBT\Business\Weixin\TotalUserBusiness;
class SuperAdminController extends WBTControllerBase
{
    public function mpUserProfileAction()
    {

        if(!$this->_isMpAdmin)
        {
            if(!$this->_isCompanyAdmin)
            {
                $this->_redirectToErrorPage("你没有权限访问此页面。");
            }

        }

        $mpUserId = $this->_mpUserID;
        //取出此公众账号用户相关数据
        //用户总数量
        $wxUserIDTotal = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::MP_USER_ID => $mpUserId]);
        $this->_view->set( "wx_user_total", count($wxUserIDTotal) );
        //关注总数量
        $wxUserIDTotalSubscribe = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::MP_USER_ID => $mpUserId,WxUser::IS_FANS => 1]);
        $this->_view->set( "wx_user_total_subscribe", count($wxUserIDTotalSubscribe) );
        //取消关注数量
        $wxUserIDTotalUnSubscribe = count($wxUserIDTotal)-count($wxUserIDTotalSubscribe);
        $this->_view->set( "wx_user_total_un_subscribe", $wxUserIDTotalUnSubscribe );
        //认证数量
        $wxUserIDTotalUnVerify = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::MP_USER_ID => $mpUserId,WxUser::CURRENT_COMMUNITY_ID => 0]);
        $this->_view->set( "wx_user_total_un_verify", count($wxUserIDTotalUnVerify) );
        //未认证数量
        $wxUserIDTotalVerify = count($wxUserIDTotal)-count($wxUserIDTotalUnVerify);
        $this->_view->set( "wx_user_total_verify", $wxUserIDTotalVerify );

        $mpUser   = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);

        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $this->_view->set( MpUser::MP_NAME, $mpUser->getMpName() );
        $this->_view->set( MpUser::COMMENT, $mpUser->getComment() );
        $this->_view->set( MpUser::API_ID, $mpUser->getApiID() );
        $this->_view->set( 'host', get_host() );
        $this->_view->set( 'token', $mpUser->getToken() );
        $this->_view->set( MpUser::APP_ID, $mpUser->getAppID() );
        $this->_view->set( MpUser::APP_SECRET, $mpUser->getAppSecret() );
        $this->_view->set( MpUser::PARTNER_ID, $mpUser->getPartnerID() );
        $this->_view->set( MpUser::PARTNER_KEY, $mpUser->getPartnerKey() );
        $this->_view->set( MpUser::PAY_SIGN_KEY, $mpUser->getPaySignKey() );
        $this->_view->set( MpUser::MCHID, $mpUser->getMchid() );
        $this->_view->set( MpUser::PAY_KEY, $mpUser->getPayKey() );
        $this->_view->set( MpUser::JS_API_CALL_URL, $mpUser->getJsApiCallUrl() );
        $this->_view->set( MpUser::SSLCERT_PATH, $mpUser->getSslcertPath());
        $this->_view->set( MpUser::SSLKEY_PATH, $mpUser->getSslkeyPath() );
        $this->_view->set( MpUser::NOTIFY_URL, $mpUser->getNotifyUrl() );
        $this->_view->set( MpUser::CURL_TIMEOUT, $mpUser->getCurlTimeout() );
        $this->_view->set( MpUser::SHARE_PIC, $mpUser->getSharePic() );
        $this->_view->set( MpUser::CARD_LOGO, $mpUser->getCardLogo());
        $this->_view->set( MpUser::CARD_BACKGROUND, $mpUser->getCardBackground());
        $this->_view->set( MpUser::OPEN_DATE, $mpUser->getOpenDate() );
        //$this->_view->set( MpUser::SEND_REPORT, $mpUser->getSendReport() );
        $this->_view->set( MpUser::FOLLOWED_CONTENT,str_replace("\n", '<br/>', $mpUser->getFollowedContent() ) );
        $this->_view->set( MpUser::CARD_LIST_DIRECTORY, $mpUser->getCardListDirectory() );
        $this->_view->set( MpUser::ORDER_NOTIFY_MUSIC, $mpUser->getOrderNotifyMusic() );
        $this->_view->set( MpUser::INDUSTRY, IndustryType::getDisplayName($mpUser->getIndustry()) );
        $mpUserIndustry = $mpUser->getIndustry();
        if($mpUserIndustry == 'procurement')
        {
            $this->_view->set( 'is_procurement',true );
            $procurementData = TotalUserBusiness::getProcurementData($mpUserId);
            $hour = date('H',time());
            $this->_view->set( 'hour',$hour );
            $this->_view->set( 'procurementData',$procurementData );
        }
        else
        {
            $this->_view->set( 'is_procurement',false );
        }
        $mpUserType = $mpUser->getMpUserType();
        if($mpUserType == 1)
        {
            $this->_view->set( MpUser::MP_USER_TYPE,"服务号" );
        }
        else
        {
            $this->_view->set( MpUser::MP_USER_TYPE,"订阅号" );
        }
        $csVisible = $mpUser->getCsVisible();
        if($csVisible == '1')
        {
            $this->_view->set( MpUser::CS_VISIBLE,"显示" );
        }else{
            $this->_view->set( MpUser::CS_VISIBLE,"不显示" );
        }
        $valid = $mpUser->getValid();
        if($valid == 1)
        {
            $this->_view->set( MpUser::VALID,"有效" );
        }
        else
        {
            $this->_view->set( MpUser::VALID,"无效" );
        }
        $send_report = $mpUser->getSendReport();
        if($send_report == 1)
        {
            $this->_view->set( MpUser::SEND_REPORT,"发送" );
        }
        else
        {
            $this->_view->set( MpUser::SEND_REPORT,"不发送" );
        }
        $this->_view->set( MpUser::SALE_LIST_NAME, $mpUser->getSaleListName() );
        $superAdmin = new SuperAdmin([ SuperAdmin::USERNAME => $this->_username ]);
        if (!$superAdmin->isEmpty())
        {
            $this->_view->set( SuperAdmin::HAS_DELETE_POWER, $superAdmin->getHasDeletePower() );
        }


        $paging = [];
        $ranking = [MpUserConfig::CONFIG_TYPE];
        $condition = [MpUserConfig::MP_USER_ID => $mpUserId];
        $outputColumns = MpUserConfig::s_metadata()->getFilterOptions();
        $data = SuperAdminBusiness::getMpUserConfigList($condition, $paging, $ranking, $outputColumns);
        $shownColumns =
         [
            MpUserConfig::CONFIG_TYPE ,
            MpUserConfig::CONFIG_TYPE_TYPE ,
            MpUserConfig::CONFIG_VALUE =>
                [
                Table::COLUMN_FUNCTION => function (array $row)
                {
                    if($row[MpUserConfig::CONFIG_TYPE_TYPE] == 'img')
                    {
                        return sprintf('<img src="%s" width="120px" height="120px" alt="图片"/>',$row[MpUserConfig::CONFIG_VALUE]);
                    }
                    else if($row[MpUserConfig::CONFIG_TYPE_TYPE] == 'url')
                    {
                        $ret = $row[MpUserConfig::CONFIG_VALUE];
                        return "<a target=\"_blank\"  href=\"$ret\" >链接到这里</a>";
                    }
                    else if($row[MpUserConfig::CONFIG_TYPE_TYPE] == 'bool')
                    {
                        if($row[MpUserConfig::CONFIG_VALUE] == 0)
                        {
                            return "否";
                        }
                        else
                        {
                            return "是";
                        }
                    }
                    else
                    {
                        return $row[MpUserConfig::CONFIG_VALUE] ;
                    }

                }],

            Table::COLUMN_OPERATIONS =>
            [
               Table::COLUMN_OPERATIONS =>
               [
                 new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/super_admin_dialog/mp_user_config_update?mp_user_config_id={{this.mp_user_config_id}}&mp_user_id={{this.mp_user_id}}');"),
                 new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function() { javascript:wbtAPI.call('../fcrm/super_admin/mp_user_config_delete?mp_user_config_id={{this.mp_user_config_id}}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })"),
               ],
            ]
        ];

        $table = Table::fromDbData($data,$outputColumns,$ranking,$paging,$shownColumns ,[ 'class' => 'table-border table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );

    }
}
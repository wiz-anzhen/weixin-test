<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\MpUser;
use WBT\Business\UserBusiness;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\CompanyAdmin;
use WBT\Business\Weixin\MpUserBusiness;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\CommunityBusiness;
use Common\Helper\BaseController;
use MP\Model\Mp\IndustryType;

class CommunityController extends BaseController
{
    protected $_userID;
    protected $_username;
    protected $_mpUserId;
    protected $_isCommunityAdmin;
    protected $_isMpAdmin;
    protected $_isCompanyAdmin;


    protected function _init()
    {

        $this->_isMpAdmin = false;
        $this->_userID   = UserBusiness::getLoginUser()->getUserID();
        $this->_username = UserBusiness::getLoginUser()->getUsername();
        $this->_view->set('username', $this->getSimpleUsername($this->_username));
        $this->_view->set('username_all', $this->_username);
        $this->_mpUserId = $this->_request->get('mp_user_id');
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserId]);
        $industry = $mpUser->getIndustry();
        if($industry == IndustryType::RESTAURANT)
        {
            $this->_view->set('restaurant', true);
        }

        log_debug("[userId:{$this->_userID}][mpUserId:{$this->_mpUserId}]");
        log_debug(strlen($this->_mpUserId));

        $auth = $this->_requireAuth('weibotui');

        $communityAdmin            = new CommunityAdmin([CommunityAdmin::USERNAME => $this->_username]);
        $communityAdminCommunityId = null;
        if (!$communityAdmin->isEmpty()) {
            $communityAdminCommunityId = $communityAdmin->getCommunityID();
        }


        if (empty($this->_mpUserId)) {
            log_warn("[mpUserId:{$this->_mpUserId}] 缺少公共账号 ID 参数");
            $this->_redirectToErrorPage('缺少公共账号 ID 参数');
        }
        $this->_urlSignature = 'hou8e';
        $superAdmin          = new SuperAdmin([SuperAdmin::USERNAME => $this->_username]);
        if (!$superAdmin->isEmpty())
        {
            $this->_view->set( 'is_super_admin', true );
            $this->_view->set( 'is_mp_admin', true );
            $this->_isMpAdmin = true;
        }
        elseif (MpAdmin::fetchCount([MpAdmin::MP_USER_ID => $this->_mpUserId,
                MpAdmin::USERNAME   => UserBusiness::getLoginUsername()]) > 0
        )
        {
            $this->_view->set( 'is_mp_admin', true );
            $this->_isMpAdmin = true;
        }
        elseif (CompanyAdmin::fetchCount([CompanyAdmin::MP_USER_ID => $this->_mpUserId,
                CompanyAdmin::USERNAME => $this->_username]) > 0)
        {
            $this->_view->set( 'is_company_admin', true );
            $this->_isCompanyAdmin = true;
        }
        elseif (!empty($communityAdminCommunityId))
        {
            $this->_isCommunityAdmin = true;
            $this->_view->set('is_community_admin', true);
        }
        else
        {
            log_warn("[userID:{$this->_userID}] 没有权限访问该页面");
            $this->_redirectToErrorPage('试图访问未授权的公共账号信息.....');
        }

        parent::_init();
    }

    public function listAction()
    {
        $mpUserId = $this->_request->get('mp_user_id');
        $this->_view->set(MpUser::MP_USER_ID, $mpUserId);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $industry = $mpUser->getIndustry();
        if($industry == IndustryType::RESTAURANT)
        {
            $this->_view->set('restaurant', true);
        }
        $this->_view->set('industry', $industry);
        $this->_view->set('mp_name', $mpUser->getMpName());
        $this->_view->set('user_id', $userID = UserBusiness::getLoginUser()->getUserID());

        $paging = []; // 先初始化为空
        $outputColumns = Community::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        if ($this->_isCommunityAdmin)
        {
            $communityIdArray = CommunityAdmin::fetchColumn(CommunityAdmin::COMMUNITY_ID, [CommunityAdmin::USERNAME => $this->_username]);
            $condition        = [Community::COMMUNITY_ID => $communityIdArray,Community::VALID => 1];
        }
        else
        {
            $condition = [Community::MP_USER_ID => $mpUserId];
        }
        $ranking       = [Community::COMMUNITY_ID];
        $data          = CommunityBusiness::getCommunityList($condition, $paging, $ranking, $outputColumns);
        if($industry == IndustryType::PROCUREMENT)
        {
            $shownColumns =
                [
                    Community::NAME =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:8%',
                            Table::COLUMN_FUNCTION => function (array $row)
                            {
                                return $directory = new Link($row[Community::NAME],
                                    "/mp_admin/function/list?mp_user_id={$row[Community::MP_USER_ID]}&community_id={$row[Community::COMMUNITY_ID]}&community_name={$row[Community::NAME]}");
                            }],
                    Community::COMMUNITY_TYPE => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::PHONE                  =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function ($row)
                                {
                                    return new Link($row[Community::PHONE],
                                        "/wx_user/community_phone/index?community_id={$row[Community::COMMUNITY_ID]}#http://mp.weixin.qq.com");
                                }  ],

                    Community::ADMIN_EMAIL  => [Table::COLUMN_CELL_STYLE => 'width:8%'],

                    Community::ADMIN_CC_EMAIL => [Table::COLUMN_CELL_STYLE => 'width:8%'],

                    Community::IS_VIRTUAL =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function($row)
                                {
                                    if($row[Community::IS_VIRTUAL])
                                    {
                                        return '是';
                                    }
                                    else
                                    {
                                        return '否';
                                    }
                                }
                        ],
                    Community::IS_APP =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function($row)
                                {
                                    if($row[Community::IS_APP])
                                    {
                                        return '是';
                                    }
                                    else
                                    {
                                        return '否';
                                    }
                                }
                        ],
                    Community::VALID =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function($row)
                                {
                                    if($row[Community::VALID])
                                    {
                                        return '有效';
                                    }
                                    else
                                    {
                                        return '无效';
                                    }
                                }
                        ],
                    Community::PROVINCE => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::CITY => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::AREA => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::ADDRESS => [
                        Table::COLUMN_TITLE => '详细地址',
                        Table::COLUMN_CELL_STYLE => 'width:6%',
                        Table::COLUMN_FUNCTION => function(array $row)
                            {
                                return sprintf('<div class="hide_change" onMouseOver="this.className=\'show_change\'" onMouseOut="this.className=\'hide_change\'">%s</div>',$row[Community::ADDRESS]);
                            }],


                    Community::COMMENT => [
                        Table::COLUMN_TITLE => '备注',
                        Table::COLUMN_CELL_STYLE => 'width:5%',
                        Table::COLUMN_FUNCTION => function(array $row)
                            {
                                return sprintf('<div class="hide_beizhu" onMouseOver="this.className=\'show_change\'" onMouseOut="this.className=\'hide_beizhu\'">%s</div>',$row[Community::COMMENT]);
                            }],


                ];

        }
        else
        {
            $shownColumns =
                [
                    Community::NAME =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:8%', Table::COLUMN_FUNCTION => function (array $row)
                            {
                                return $directory = new Link($row[Community::NAME],
                                    "/mp_admin/function/list?mp_user_id={$row[Community::MP_USER_ID]}&community_id={$row[Community::COMMUNITY_ID]}&community_name={$row[Community::NAME]}");
                            }],
                    Community::COMMUNITY_TYPE => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::PHONE                  =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function ($row)
                                {
                                    return new Link($row[Community::PHONE],
                                        "/wx_user/community_phone/index?community_id={$row[Community::COMMUNITY_ID]}#http://mp.weixin.qq.com");
                                }  ],

                    Community::ADMIN_EMAIL  => [Table::COLUMN_CELL_STYLE => 'width:8%'],

                    Community::ADMIN_CC_EMAIL => [Table::COLUMN_CELL_STYLE => 'width:8%'],

                    Community::IS_VIRTUAL =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function($row)
                                {
                                    if($row[Community::IS_VIRTUAL])
                                    {
                                        return '是';
                                    }
                                    else
                                    {
                                        return '否';
                                    }
                                }
                        ],
                    Community::IS_APP =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function($row)
                                {
                                    if($row[Community::IS_APP])
                                    {
                                        return '是';
                                    }
                                    else
                                    {
                                        return '否';
                                    }
                                }
                        ],
                    Community::VALID =>
                        [
                            Table::COLUMN_CELL_STYLE => 'width:5%',
                            Table::COLUMN_FUNCTION => function($row)
                                {
                                    if($row[Community::VALID])
                                    {
                                        return '有效';
                                    }
                                    else
                                    {
                                        return '无效';
                                    }
                                }
                        ],
                    Community::BILL_NAME => [
                        Table::COLUMN_TITLE => "收费通知单名称",
                        Table::COLUMN_CELL_STYLE => 'width:9%',
                        Table::COLUMN_FUNCTION => function(array $row)
                            {
                                return sprintf('<div class="hide_change" onMouseOver="this.className=\'show_change\'" onMouseOut="this.className=\'hide_change\'">%s</div>',$row[Community::BILL_NAME]);
                            }],

                    Community::BILL_COMMENT => [
                        Table::COLUMN_TITLE => '收费通知单提示',
                        Table::COLUMN_CELL_STYLE => 'width:9%',
                        Table::COLUMN_FUNCTION => function(array $row)
                            {
                                if(empty($row[Community::BILL_COMMENT]))
                                {
                                    return null;
                                }
                                else{
                                    return sprintf('<div class="hide_comment" onMouseOver="this.className=\'show_change\'" onMouseOut="this.className=\'hide_comment\'">
                        %s</div>',$row[Community::BILL_COMMENT]);
                                }

                            }],
                    Community::PROVINCE => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::CITY => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::AREA => [Table::COLUMN_CELL_STYLE => 'width:5%'],
                    Community::ADDRESS => [
                        Table::COLUMN_TITLE => '详细地址',
                        Table::COLUMN_CELL_STYLE => 'width:6%',
                        Table::COLUMN_FUNCTION => function(array $row)
                            {
                                return sprintf('<div class="hide_change" onMouseOver="this.className=\'show_change\'" onMouseOut="this.className=\'hide_change\'">%s</div>',$row[Community::ADDRESS]);
                            }],


                    Community::COMMENT => [
                        Table::COLUMN_TITLE => '备注',
                        Table::COLUMN_CELL_STYLE => 'width:5%',
                        Table::COLUMN_FUNCTION => function(array $row)
                            {
                                return sprintf('<div class="hide_beizhu" onMouseOver="this.className=\'show_change\'" onMouseOut="this.className=\'hide_beizhu\'">%s</div>',$row[Community::COMMENT]);
                            }],


                ];

        }

        if (!$this->_isCommunityAdmin)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =
                [
                    Table::COLUMN_TITLE => " 操作",
                    Table::COLUMN_CELL_STYLE => 'width:14%',
                    Table::COLUMN_FUNCTION => function(array $row)use($industry)
                        {
                            if($industry == IndustryType::PROCUREMENT)
                            {
                                if($this->_isMpAdmin)
                                {
                                    $link1 =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/community_dialog/community_update??mp_user_id={$row[COMMUNITY::MP_USER_ID]}&community_id={$row[COMMUNITY::COMMUNITY_ID]}');");
                                }
                                else
                                {
                                    $link1 =  "";
                                }
                            }
                            else
                            {
                                $link1 =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/community_dialog/community_update??mp_user_id={$row[COMMUNITY::MP_USER_ID]}&community_id={$row[COMMUNITY::COMMUNITY_ID]}');");
                            }

                            $link2 = '';
                            if(!$this->_isCompanyAdmin)
                            {
                                $link2 = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该小区内容将消失，且无法恢复。<br/><br/>确定要删除吗？',
        function() { javascript:wbtAPI.call('../fcrm/community/community_delete?mp_user_id={$row[COMMUNITY::MP_USER_ID]}&community_id={$row[COMMUNITY::COMMUNITY_ID]}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                            }

                            $link3 = new Link('社区参数', "/mp_admin/community_config/list?mp_user_id={$row[COMMUNITY::MP_USER_ID]}&community_id={$row[COMMUNITY::COMMUNITY_ID]}");
                            $link4 = new Link('社区管理员', "/mp_admin/community_admin/list?mp_user_id={$row[COMMUNITY::MP_USER_ID]}&community_id={$row[COMMUNITY::COMMUNITY_ID]}");

                            return $link1."<br>".$link2."<br>".$link3."<br>".$link4."<br>";
                        }
                ];
        }

        if ($this->_isCommunityAdmin)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =
                [
                    Table::COLUMN_TITLE => " 操作",
                    Table::COLUMN_CELL_STYLE => 'width:14%',
                    Table::COLUMN_FUNCTION => function(array $row)use($industry)
                        {
                            $link4 = new Link('社区管理员', "/mp_admin/community_admin/list?mp_user_id={$row[COMMUNITY::MP_USER_ID]}&community_id={$row[COMMUNITY::COMMUNITY_ID]}");
                            return $link4;
                        }
                ];
        }

        $table               = Table::fromDbData($data, $outputColumns, Community::COMMUNITY_ID, $paging, $shownColumns, ['class' => 'table-bordered table-striped table-hover']);
        $table->showRecordNo = false;
        $this->_view->set('table', $table);
    }
}
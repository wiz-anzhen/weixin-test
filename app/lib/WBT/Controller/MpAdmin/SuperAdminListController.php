<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Common\Helper\BaseController;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpAdmin;
use WBT\Model\Weibotui\UserStatus;
use Common\Data\Event;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\MpUserBusiness;
use Bluefin\Auth\AuthInterface;
use MP\Model\Mp\TotalUser;
use WBT\Business\Weixin\TotalUserBusiness;
use MP\Model\Mp\CompanyAdmin;
class SuperAdminListController extends BaseController
{
    protected $_userID;
    protected $_username;
    protected $_mpUserId;

    protected function _init()
    {
        $this->_userID   = UserBusiness::getLoginUser()->getUserID();
        $this->_username = UserBusiness::getLoginUser()->getUsername();
        $this->_view->set('username', $this->getSimpleUsername($this->_username));

        log_debug( "[userId:{$this->_userID}]" );

        $auth = $this->_requireAuth( 'weibotui' );
        $this->_checkAccountStatus( $auth );

        $this->_urlSignature = 'hou8e';

        $superAdmin = new SuperAdmin([ SuperAdmin::USERNAME => $this->_username ]);
        if (!$superAdmin->isEmpty())
        {
            $this->_view->set( 'is_super_admin', TRUE );
        }

        parent::_init();
    }

    protected function getSimpleUsername($username)
    {
        $pos = strpos($username,'@');
        if($pos)
        {
            return substr($username,0,$pos);
        }

        return $username;
    }

    protected function _checkAccountStatus( AuthInterface $auth ) {
        $accountStatus = $auth->getData( 'status' );

        switch ($accountStatus) {
            case UserStatus::NONACTIVATED:
                $this->_gateway->redirect( $this->_gateway->path( 'home/weibotui/nonactivated' ) );
                break;

            case UserStatus::DISABLED:
                $this->_showEventMessage( Event::E_ACCOUNT_DISABLED, Event::SRC_AUTH );
                break;
        }
    }

    public function listAction() {

        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }


        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;


        $this->_view->set('user_id', $this->_userID);
        $count = MpAdmin::fetchCount( [ MpAdmin::USERNAME => $this->_username ] );
        if ($count > 0) {
            $this->_view->set( 'is_admin', TRUE );
        }

        $outputColumns = MpUser::s_metadata()->getFilterOptions();
        $ranking       = [ MpUser::MP_USER_ID ];

        $data         = MpUserBusiness::getMpUserList($this->_username, $ranking, $paging, $outputColumns );
        $shownColumns = [ MpUser::MP_NAME => [ Table::COLUMN_TITLE      => '公众帐号名称',
                                               Table::COLUMN_CELL_STYLE => 'width:10%',
                                               Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                       $communityProperty = new Link($row['mp_name'],
                                                           "/mp_admin/community/list?mp_user_id={$row['mp_user_id']}");
                                                       return $communityProperty;
                                                   } ],
                          MpUser::INDUSTRY ,
                          MpUser::VALID => [Table::COLUMN_CELL_STYLE => 'width:7%',
                              Table::COLUMN_FUNCTION => function($row)
                                  {
                                      if($row[MpUser::VALID])
                                      {
                                          return '有效';
                                      }
                                      else
                                      {
                                          return '无效';
                                      }
                                  }

                          ],
                          MpUser::MP_USER_ID => [Table::COLUMN_TITLE      => '二级帐号管理员',
                              Table::COLUMN_FUNCTION => function($row)
                                  {
                                      $companyUser = new CompanyAdmin([CompanyAdmin::MP_USER_ID=>$row[MpUser::MP_USER_ID]]);
                                      if(!$companyUser->isEmpty())
                                      {
                                          return $companyUser->getUsername();
                                      }
                                      else
                                      {
                                          return '';
                                      }
                                  }
                          ],
                          MpUser::COMMENT  => [ Table::COLUMN_TITLE      => '备注',
                                                Table::COLUMN_CELL_STYLE => 'width:40%', ],
                                                       ];

        $table = Table::fromDbData($data, $outputColumns, MpUser::MP_USER_ID, $paging, $shownColumns,
        [ 'class' => 'table-bordered table-striped table-hover' ]);
        $table->showRecordNo = TRUE;
        $this->_view->set('table', $table);

        if (strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $this->_view->set( 'weixin', true );
        }
        //获取统计用户相关数据
        $countData = TotalUserBusiness::getCountData();
        $this->_view->set('total_user_num', $countData['total_user_num']);
        $this->_view->set('hour', $countData['hour']);
        $this->_view->set('active_user_num', $countData['active_user_num']);
        $this->_view->set('yesterday_active_user_num', $countData['yesterday_active_user_num']);
    }



    public static function totalUserTable()
    {
        $condition = [];
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }


        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 1;
        $outputColumns = TotalUser::s_metadata()->getFilterOptions();
        $ranking       = [ TotalUser::TOTAL_USER_ID ];
        $data         = TotalUserBusiness::getTotalUserList($condition, $ranking, $paging, $outputColumns );
        $shownColumns = [ TotalUser::TOTAL_USER_NUM => [ Table::COLUMN_TITLE      => '公共账号有效用户数',
                        Table::COLUMN_CELL_STYLE => 'width:30%',
                         ],
                        TotalUser::ACTIVE_USER_NUM  => [ Table::COLUMN_TITLE      => '活跃用户数',
                            Table::COLUMN_CELL_STYLE => 'width:30%', ],
                        TotalUser::INSERT_HOUR => [Table::COLUMN_TITLE      => '统计时间段',
                            Table::COLUMN_CELL_STYLE => 'width:30%',],
        ];
        $table = Table::fromDbData($data, $outputColumns, TotalUser::TOTAL_USER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ]);
        return $table;
    }
    function DownAction()
    {/*
        $communityId = $this->_request->get( 'community_id');
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( "community_id", $communityId );
        $this->_view->set( "mp_user_id", $mpUserId );*/
    }
}
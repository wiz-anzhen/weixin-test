<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\MpUser;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\MpUserBusiness;
use WBT\Controller\WBTControllerBase;

class MpUserController extends WBTControllerBase
{
    public function listAction() {
        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition( $condition, $outputColumns, $paging, $ranking );

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX])) {
            ($paging[Database::KW_SQL_PAGE_INDEX] = 1);
        }
        if (!isset($paging[Database::KW_SQL_ROWS_PER_PAGE])) {
            ($paging[Database::KW_SQL_ROWS_PER_PAGE] = Database::DEFAULT_ROWS_PER_PAGE);
        }

        $userID = $this->_userID;
        $this->_view->set( 'user_id', $userID );
        $count = MpAdmin::fetchCount( [ MpAdmin::USERNAME => UserBusiness::getLoginUsername() ] );
        if ($count > 0) {
            $this->_view->set( 'is_admin', TRUE );
        }


        $outputColumns = MpUser::s_metadata()->getFilterOptions();
        $data          = MpUserBusiness::getMpUserByMpAdminId( $userID );
        $shownColumns  = [ MpUser::MP_NAME          => [ Table::COLUMN_TITLE      => '公众账号名称',
                                                         Table::COLUMN_CELL_STYLE => 'width:30%',
                                                         Table::COLUMN_FUNCTION   => function ( array $row ) {
                                                             return new Link($row[MpUser::MP_NAME], "/mp_admin/nav/list?mp_user_id={$row['mp_user_id']}");
                                                         } ],

                           MpUser::LOCATION_X       => [ Table::COLUMN_TITLE      => '地理X坐标',
                                                         Table::COLUMN_CELL_STYLE => 'width:15%', ],
                           MpUser::LOCATION_Y       => [ Table::COLUMN_TITLE      => '地理Y坐标',
                                                         Table::COLUMN_CELL_STYLE => 'width:15%', ],
                           Table::COLUMN_OPERATIONS => [ Table::COLUMN_TITLE      => '操作',
                                                         Table::COLUMN_CELL_STYLE => 'width:30%',
                                                         Table::COLUMN_OPERATIONS => [ new Link('修改地理位置', "javascript:bluefinBH.ajaxDialog('/mp_admin/mp_user_dialog/edit/?mp_user_id={{ this.mp_user_id }}');"), ], ], ];

        $table               = Table::fromDbData( $data, $outputColumns, MpUser::MP_USER_ID, NULL, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = TRUE;
        $this->_view->set( 'table', $table );
    }
}
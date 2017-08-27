<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\DirectoryType;

use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Directory;
use MP\Model\Mp\TopDirectory;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\CommunityBusiness;
use WBT\Business\Weixin\CommunityPhoneBookBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\CommunityPhoneBook;



class CommunityPhoneBookController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "phone_book";
        parent::_init();
    }
    //显示
    public function listAction()
    {
        $mpUserID = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userId = UserBusiness::getLoginUser()->getUserID() );
        $this->_view->set( 'community_id', $communityId);

        $paging = []; // 先初始化为空
        $outputColumns = CommunityPhoneBook::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking       = [ CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID ];
        $condition= [CommunityPhoneBook::MP_USER_ID => $mpUserID,CommunityPhoneBook::COMMUNITY_ID => $communityId] ;

        $data          = CommunityPhoneBookBusiness::getCommunityPhoneBookList( $condition, $paging, $ranking, $outputColumns );

        $power = $this->checkChangePower("phone_book_rw","phone_book_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('phone_book_rw', $checkReadPower);
        $shownColumns = [
            CommunityPhoneBook::NAME => [Table::COLUMN_CELL_STYLE => 'width:45%',],
            CommunityPhoneBook::PHONE  => [
                Table::COLUMN_CELL_STYLE => 'width:30%',
                Table::COLUMN_FUNCTION => function ($row)
                    {
                        return new Link($row[CommunityPhoneBook::PHONE], "/wx_user/community_phone_book/index?community_phone_book_id={$row[CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID]}#http://mp.weixin.qq.com");
                    }
            ], ];
        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] =  [
                Table::COLUMN_CELL_STYLE => 'width:15%',
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_FUNCTION => function(array $row)use($power)
                    {
                        $communityID = $row[CommunityPhoneBook::COMMUNITY_ID];
                        $mpUserID = $row[CommunityPhoneBook::MP_USER_ID];
                        $communityPhoneBookID = $row[CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID];
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/community_phone_book_dialog/update?mp_user_id={$mpUserID}&community_id={$communityID}&community_phone_book_id={$communityPhoneBookID}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，目录下所有的内容都将丢失，且无法回复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/community_phone_book/delete?community_phone_book_id={$communityPhoneBookID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('移除成功',function(){location.reload();});});});");
                        $ret = $update;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    } ];
        }


        $table  = Table::fromDbData( $data, $outputColumns,  CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }


}
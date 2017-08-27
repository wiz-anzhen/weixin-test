<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\HTML\Link;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;
use Bluefin\Data\Database;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Controller\CommunityControllerBase;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\AppUserBusiness;
use MP\Model\Mp\AppUser;
class AppUserController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "app_user";
        parent::_init();
    }

    public function listAction()
    {
        $condition = $this->_request->getQueryParams();
        Database::extractQueryCondition( $condition, $outputColumns, $paging, $ranking );
        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        if (!isset($paging[Database::KW_SQL_ROWS_PER_PAGE]))
        {
            $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        }

        $mpUserID = $this->_request->get( WxUser::MP_USER_ID );
        $this->_view->set( WxUser::MP_USER_ID, $mpUserID );
        $mpUser = new mpUser([MpUser::MP_USER_ID => $mpUserID]);
        $mpUserName = $mpUser->getMpName();
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $communityId   = $this->_request->get('community_id');
        $community     = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set("community_name", $communityName);
        $this->_view->set('community_id', $communityId);
        //查询条件
        $tel = $name =  $city = $community_name_name  = '';
        $tel     = $this->_request->get( 'tel' );
        $this -> _view -> set("tel",$tel);
        $name     = $this->_request->get( 'name' );
        $this -> _view -> set("name",$name);
        $city    = $this->_request->get( 'city' );
        $this -> _view -> set("city",$city);
        $community_name_name    = $this->_request->get( 'community_name_name' );
        $this -> _view -> set("community_name_name",$community_name_name);
        $pageArr    = $this->_request->get( '*PAGING*' );
        $page = $pageArr['page'];
        if (!empty($page))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = intval($page);
        }
        else
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $this->_view->set('page', $paging[Database::KW_SQL_PAGE_INDEX]);
        log_debug('mpUsername===============',$mpUserName);
        log_debug('communityName ===================',$communityName);
       /* if($mpUserName == $communityName)
        {
            $condition = [];
        }
        else
        {*/
            $condition = [AppUser::CURRENT_COMMUNITY_ID => $communityId];
        //}
        //判断它是左邻有你YOU app帐号对应的小区名字
        //暂时这么写   以后如果有多个app帐号怎么区分用户还需要讨论
        if($communityId == 72)
        {
            $condition = [];
        }
        else
        {
            $condition = [AppUser::CURRENT_COMMUNITY_ID => 10000000];
        }

        if(!empty($name))
        {
            $expr = " nick like '%$name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($city))
        {
            $expr = "address like '%$city%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($tel))
        {
            $tel = str_replace(' ','',$tel);
            $expr = sprintf("`phone` = '%s'",$tel);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($community_name_name))
        {
            $expr = " community_name like '%$community_name_name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }


        $timeRegisterStart = $this->_request->get("time_register_start");
        $timeRegisterEnd = $this->_request->get("time_register_end");
        if(!empty($timeRegisterStart) && !empty($timeRegisterEnd))
        {
            $expr = sprintf("`create_time` >= '%s' and `create_time` <= '%s'",$timeRegisterStart,$timeRegisterEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $this->_view->set('time_register_start', $timeRegisterStart);
        $this->_view->set('time_register_end', $timeRegisterEnd);

        $ranking    = $this->_request->get( 'rank' );
        $this->_view->set( 'rank', $ranking);

        if(empty($ranking))
        {
            $ranking = [AppUser::CREATE_TIME];
        }
        else
        {
            if($ranking == 'create_time_reduce')
            {
                $ranking = [AppUser::CREATE_TIME => true];
            }
            else
            {
                $ranking       = [AppUser::CREATE_TIME];
            }
        }

        $outputColumns = AppUser::s_metadata()->getFilterOptions();

        $data          = AppUserBusiness::getAppUserList( $condition, $paging, $ranking,
            $outputColumns );
        $this->_view->set('app_count', $paging['total']);
        $shownColumns        = [
            AppUser::HEAD_PIC => [Table::COLUMN_TITLE => "头像",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $headPic = $row[AppUser::HEAD_PIC];
                        return sprintf('<img src="%s" width="50px" height="" alt="无图"/>', $headPic);
                    }],
            AppUser::VIP_NO           => [ Table::COLUMN_TITLE => 'APP会员号', ],
            AppUser::PHONE            => [ Table::COLUMN_TITLE => '电话', ],
            AppUser::NICK      => [ Table::COLUMN_TITLE => '昵称', ],
            AppUser::CITY,
            AppUser::COMMUNITY_NAME,
            AppUser::EMAIL,
            AppUser::BIRTH,
            AppUser::LATITUDEUSER,
            AppUser::LONGITUDEUSER,
            AppUser::CREATE_TIME => [Table::COLUMN_TITLE => '注册时间'],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => '操作',
            /*Table::COLUMN_OPERATIONS => [
                new Link('修改会员等级', "javascript:bluefinBH.ajaxDialog('/mp_admin/wx_user_dialog/edit?wx_user_id={{this.wx_user_id}}&mp_user_id={{this.mp_user_id}}');"),
            ], */ ],];
        $table               = Table::fromDbData( $data, $outputColumns, '', $paging,
            $shownColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }
}

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

class WxUserController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "member";
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
        $tel = $subscribe = $name =  $address = $vipNo = '';
        $tel     = $this->_request->get( 'tel' );
        $this -> _view -> set("tel",$tel);
        $subscribe     = $this->_request->get( 'subscribe' );
        $this -> _view -> set("subscribe",$subscribe);
        $name     = $this->_request->get( 'name' );
        $this -> _view -> set("name",$name);
        $address    = $this->_request->get( 'address' );
        $this -> _view -> set("address",$address);
        $vipNo    = $this->_request->get( 'vip_no' );
        $this -> _view -> set("vip_no",$vipNo);
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
        if($mpUserName == $communityName)
        {
            $condition = [WxUser::MP_USER_ID => $mpUserID];
        }
        else
        {
            $condition = [WxUser::MP_USER_ID => $mpUserID,WxUser::CURRENT_COMMUNITY_ID => $communityId];
        }

        if(!empty($name))
        {
            $expr = " nick like '%$name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($address))
        {
            $expr = "address like '%$address%'";
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
        if(!empty($subscribe) )
        {
           if($subscribe == "subscribe")
           {
               $subscribe = 1;
               $condition[WxUser::IS_FANS] = $subscribe;
           }
            if($subscribe == "un_subscribe")
            {
                $subscribe = 0;
                $condition[WxUser::IS_FANS] = $subscribe;
            }

        }
        if(!empty($vipNo))
        {
            $condition[WxUser::VIP_NO] = $vipNo;
        }

        $timeVerifyStart = $this->_request->get("time_verify_start");
        $timeVerifyEnd = $this->_request->get("time_verify_end");
        if(!empty($timeVerifyStart) && !empty($timeVerifyEnd))
        {
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
            $expr = sprintf("`create_time` >= '%s' and `create_time` <= '%s'",$timeVerifyStart,$timeVerifyEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $this->_view->set('time_verify_start', $timeVerifyStart);
        $this->_view->set('time_verify_end', $timeVerifyEnd);

        $timeRegisterStart = $this->_request->get("time_register_start");
        $timeRegisterEnd = $this->_request->get("time_register_end");
        if(!empty($timeRegisterStart) && !empty($timeRegisterEnd))
        {
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
            $expr = sprintf("`register_time` >= '%s' and `register_time` <= '%s'",$timeRegisterStart,$timeRegisterEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $this->_view->set('time_register_start', $timeRegisterStart);
        $this->_view->set('time_register_end', $timeRegisterEnd);

        $ranking    = $this->_request->get( 'rank' );
        $this->_view->set( 'rank', $ranking);

        if(empty($ranking))
        {
            $ranking = [WxUser::CREATE_TIME => true];
        }
        else
        {
            if($ranking == 'create_time_reduce')
            {
                $ranking = [WxUser::CREATE_TIME => true];
            }
            elseif($ranking == 'register_time_reduce')
            {
                $ranking       = [ WxUser::REGISTER_TIME => true ];
            }
            elseif($ranking == 'register_time_increase')
            {
                $ranking       = [ WxUser::REGISTER_TIME ];
            }
            else
            {
                $ranking       = [WxUser::CREATE_TIME];
            }
        }

        $outputColumns = WxUser::s_metadata()->getFilterOptions();

        $data          = WxUserBusiness::getWxUserList( $condition, $paging, $ranking,
            $outputColumns );
        $this->_view->set('weixin_count', $paging['total']);
        $shownColumns        = [
            WxUser::HEAD_PIC => [Table::COLUMN_TITLE => "头像",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $headPic = $row[WxUser::HEAD_PIC];
                        return sprintf('<img src="%s" width="50px" height="" alt="无图"/>', $headPic);
                    }],
            WxUser::VIP_NO    => [ Table::COLUMN_TITLE => '微信会员号', ],
            WxUser::PHONE     => [ Table::COLUMN_TITLE => '电话', ],
            WxUser::NICK      => [ Table::COLUMN_TITLE => '昵称', ],
            WxUser::NAME      => [ Table::COLUMN_TITLE => '姓名', ],
            WxUser::W_PROVINCE,
            WxUser::W_CITY,
            WxUser::ADDRESS,
            WxUser::EMAIL,
            WxUser::BIRTH,
            WxUser::IS_FANS => [Table::COLUMN_TITLE => "关注状态",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        if($row[WxUser::IS_FANS] == 1)
                        {
                            return "已关注";
                        }
                        else
                        {
                            return "未关注";
                        }
                    }],
            WxUser::CREATE_TIME => [Table::COLUMN_TITLE => '关注时间'],
            WxUser::REGISTER_TIME => [Table::COLUMN_TITLE => '注册时间'],
            WxUser::USER_LEVEL       => [
                Table::COLUMN_TITLE => '会员等级',
                Table::COLUMN_FUNCTION =>  function (array $row)
                    {
                        return "";
                    },],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => '操作',
                /*Table::COLUMN_OPERATIONS => [
                    new Link('修改会员等级', "javascript:bluefinBH.ajaxDialog('/mp_admin/wx_user_dialog/edit?wx_user_id={{this.wx_user_id}}&mp_user_id={{this.mp_user_id}}');"),
                ], */ ],];
        $table               = Table::fromDbData( $data, $outputColumns, WxUser::WX_USER_ID, $paging,
            $shownColumns, [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }
}

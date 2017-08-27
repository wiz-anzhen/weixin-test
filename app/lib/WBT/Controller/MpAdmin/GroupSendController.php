<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\GroupSendRangeType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\GroupSend;
use MP\Model\Mp\GroupSendItem;
use MP\Model\Mp\GroupSendContentType;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\GroupSendBusiness;
use WBT\Business\UserBusiness;
use Bluefin\Data\Database;
use WBT\Controller\CommunityControllerBase;

class GroupSendController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "send_by_group";
        parent::_init();
    }
    //社区级别
    public function indexAction()
    {

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $mpUserType = $mpUser->getMpUserType();
        $this->_view->set( 'mp_type', $mpUserType);
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $paging = []; // 先初始化为空
        $outputColumns = GroupSend::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $condition     = [ GroupSend::MP_USER_ID => $mpUserId,GroupSend::COMMUNITY_ID => $communityId,GroupSend::SEND_TYPE => "community"];


        $ranking       = [ GroupSend::CREATE_TIME => true];
        $data          = GroupSendBusiness::getGroupSendList( $condition, $paging, $ranking, $outputColumns );
        $error = [
            "err(10001)"=>"涉嫌广告",
            "err(20001)"=>" 涉嫌政治" ,
            "err(20004)"=> "涉嫌社会" ,
            "err(20002)"=> "涉嫌色情 ",
            "err(20006)"=> "涉嫌违法犯罪" ,
            "err(20008)"=> "涉嫌欺诈" ,
            "err(20013)"=> "涉嫌版权" ,
            "err(22000)"=> "涉嫌互推(互相宣传)" ,
            "err(21000)"=> "涉嫌其他",
        ];

        //每月剩余发送数量
        $currentTime = date("Ym");
        $firstDay = strtotime($currentTime."00 00:00:00");
        $lastDay = strtotime($currentTime."31 23:59:59");
        $groupSendTime = GroupSend::fetchColumn([GroupSend::GROUP_SEND_TIME],[GroupSend::COMMUNITY_ID => $communityId,GroupSend::GROUP_SEND_RANGE => GroupSendRangeType::SEND_TO_WHOLE_COMMUNITY]);
        $groupSendTimeMp = GroupSend::fetchColumn([GroupSend::GROUP_SEND_TIME],[GroupSend::COMMUNITY_ID => $communityId,GroupSend::GROUP_SEND_RANGE => GroupSendRangeType::SEND_TO_MP_USER]);
        foreach($groupSendTimeMp as $key=>$value)
        {
               if(empty($value))
               {
                   unset($groupSendTimeMp[$key]);
               }
        }

        if($mpUserType)
        {
            $count = [];
            foreach($groupSendTime as $value)
            {
                if(strtotime($value) >= $firstDay and strtotime($value) <= $lastDay)
                {
                    $count[] = $value;
                }
            }
            $remainCount = 4-count($count)-count($groupSendTimeMp);
            if($remainCount < 0)
            {
                $remainCount = 0;
            }
        }
        else
        {
            $remainCount = 1;
            /*
            foreach($groupSendTime as $value)
            {
                if(substr($value,0,10) == date("Y-m-d") )
                {
                    $remainCount = 0;
                }
            }
            */
        }

        $this->_view->set( "remain_count", $remainCount );
        $shownColumns = [
            GroupSend::TITLE,
            GroupSend::CONTENT_TYPE,
            GroupSend::CONTENT_VALUE => [
                                        Table::COLUMN_TITLE => '消息内容',
                                        Table::COLUMN_FUNCTION => function (array $row)
                                            {
                                                if($row[GroupSend::CONTENT_TYPE] == GroupSendContentType::CUSTOM_NEWS)
                                                {
                                                    $url = sprintf("/mp_admin/group_send/content?mp_user_id=%s&group_send_id=%s&community_id=%s&from=%s", $row[GroupSend::MP_USER_ID], $row[GroupSend::GROUP_SEND_ID], $row[GroupSend::COMMUNITY_ID], $row[GroupSend::SEND_TYPE]);
                                                    $link = new Link("编辑",$url);
                                                    return $link;
                                                }
                                                return $row[GroupSend::CONTENT_VALUE];
                                            }
            ],
            GroupSend::GROUP_SEND_TIME,
            GroupSend::GROUP_SEND_AUTHOR,
            GroupSend::GROUP_SEND_RANGE,
            GroupSend::GROUP_SEND_NO =>
                [
                Table::COLUMN_FUNCTION => function (array $row){
                        $groupSendNo = explode("\n",$row[GroupSend::GROUP_SEND_NO]);
                        $ret = "";
                        foreach($groupSendNo as $value)
                        {
                             $ret.=$value."<br>";
                        }
                        return $ret;
                }],
            GroupSend::MSG_ID,
            GroupSend::STATUS =>
                [
                       Table::COLUMN_TITLE => "发送状态",
                       Table::COLUMN_FUNCTION => function(array $row)use($error){
                               if($row[GroupSend::STATUS] == "send success")
                               {
                                   return $res = "发送成功";
                               }
                               else if ($row[GroupSend::STATUS] == "send job submission success")
                               {
                                   return $res = "提交成功";
                               }
                               else if(empty($row[GroupSend::STATUS]))
                               {
                                   return $res = "";
                               }
                               else if(strict_in_array($row[GroupSend::STATUS],array_keys($error)))
                               {
                                   return $res = $error[$row[GroupSend::STATUS]];
                               }
                               else
                               {
                                   log_warning("unknown status".$row[GroupSend::STATUS]);
                                   return $res = "发送失败（其他原因）";
                               }

                           }

                 ],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE  => "width:5%",
                Table::COLUMN_FUNCTION =>function(array $row)use($remainCount){
                    $community_id = $row[GroupSend::COMMUNITY_ID];
                    $mpUserID = $row[GroupSend::MP_USER_ID];
                    $groupSendId = $row[GroupSend::GROUP_SEND_ID];
                    $update = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/group_send_dialog/update?group_send_id={$groupSendId}&community_id={$community_id}&from=community');");
                    $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该群发信息将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/group_send/delete?group_send_id={$groupSendId}&community_id={$community_id}&from=community', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");                           $send = new Link('发布', "javascript:bluefinBH.confirm('确定要发布消息吗？', function() { javascript:wbtAPI.call('../fcrm/group_send/send?group_send_id={$groupSendId}&mp_user_id={$mpUserID}&community_id={$community_id}&from=community', null, function(){bluefinBH.showInfo('发布成功', function() { location.reload(); }); }); })");
                    $copy = new Link(' 复制', "javascript:bluefinBH.confirm('确定要复制此消息吗？', function() { javascript:wbtAPI.call('../fcrm/group_send/copy?group_send_id={$groupSendId}&mp_user_id={$mpUserID}&community_id={$community_id}&from=community', null, function(){bluefinBH.showInfo('复制成功', function() { location.reload(); }); }); })");
                    $preview =  new Link('预览', "javascript:bluefinBH.ajaxDialog('/mp_admin/group_send_dialog/preview?group_send_id={$groupSendId}&community_id={$community_id}&from=community');");
                     if(empty($row[GroupSend::GROUP_SEND_TIME]))
                     {
                         if($remainCount != 0)
                         {
                             $ret = $update."<br>".$delete."<br>".$send."<br>".$preview;
                         }
                         else
                         {
                             $ret = $update."<br>".$delete."<br>".$preview;
                         }

                     }
                     else
                     {
                         $ret = $copy."<br>".$delete;
                     }
                     return $ret;
                    }, ],
        ];


        $table               = Table::fromDbData( $data, $outputColumns, GroupSend::GROUP_SEND_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }

    //显示群发消息内容
    public function contentAction()
    {

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $groupSendID = $this->_request->get( 'group_send_id' );
        $this->_view->set( "group_send_id", $groupSendID );
        $groupSend = new GroupSend([GroupSend::GROUP_SEND_ID => $groupSendID]);
        $this->_view->set( "group_title", $groupSend->getTitle() );
        $from = $this->_request->get( 'from' );
        $this->_view->set( "from", $from );
        $paging = []; // 先初始化为空
        $outputColumns = GroupSendItem::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ GroupSendItem::MP_USER_ID => $mpUserId,GroupSendItem::COMMUNITY_ID => $communityId,GroupSendItem::GROUP_SEND_ID => $groupSendID];
        $ranking       = [ GroupSendItem::SORT_NO ];
        $data          = GroupSendBusiness::getGroupSendItemList( $condition, $paging, $ranking, $outputColumns );
        $groupSendItemNumber = count($data);
        $this->_view->set( "group_send_item_number", $groupSendItemNumber );
        $shownColumns = [
            GroupSendItem::TITLE,
            GroupSendItem::DESCRIPTION => [Table::COLUMN_CELL_STYLE => 'width:12%',],
            GroupSendItem::AUTHOR,
            GroupSendItem::PIC_URL => [

                Table::COLUMN_TITLE      => '图片',
                Table::COLUMN_CELL_STYLE => 'width:20%',
                Table::COLUMN_FUNCTION   => function ( array $row ) {
                          return "<a href=\"{$row[GroupSendItem::PIC_URL]}\" target=\"_blank\"><img style='width:60px' src=\"{$row[GroupSendItem::PIC_URL]}\" alt=\"无图片\" /></a>";
                                                } ],
            GroupSendItem::CONTENT => [
                Table::COLUMN_TITLE => "消息内容",
                Table::COLUMN_CELL_STYLE => "width:30%"
            ],
            GroupSendItem::CONTENT_SOURCE_URL => [
                Table::COLUMN_CELL_STYLE => 'width:8%',
                Table::COLUMN_TITLE      => '原文链接',
                Table::COLUMN_FUNCTION   => function ( array $row ) {
                        return "<a href=\"{$row[GroupSendItem::CONTENT_SOURCE_URL]}\" target=\"_blank\">点击查看</a>";
                    } ],
            GroupSendItem::SORT_NO,
            GroupSendItem::SHOW_COVER_PIC,
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_CELL_STYLE => 'width:8%',
                Table::COLUMN_OPERATIONS => [
                    new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/group_send_dialog/update_content?group_send_item_id={{this.group_send_item_id}}&community_id={{this.community_id}}');"),

                    new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该群发信息内容将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/group_send/delete_content?group_send_item_id={{this.group_send_item_id}}&community_id={{this.community_id}}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })"),
                ], ], ];

        $table               = Table::fromDbData( $data, $outputColumns, GroupSendItem::GROUP_SEND_ITEM_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }
}
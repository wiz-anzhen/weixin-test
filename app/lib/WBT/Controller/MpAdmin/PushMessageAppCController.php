<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\UserNotifySendStatus;
use WBT\Business\UserBusiness;
use MP\Model\Mp\PushMessage;
use MP\Model\Mp\Community;
use WBT\Business\App\PushMessageBusiness;
use WBT\Controller\CommunityControllerBase;

class PushMessageAppCController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "push_message_app_c";
        parent::_init();
    }
    //显示
    public function listAction()
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

        $paging = []; // 先初始化为空
        $outputColumns = PushMessage::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ PushMessage::MP_USER_ID => $mpUserId,PushMessage::COMMUNITY_ID => $communityId,PushMessage::SEND_TYPE=>'app_c'];
        $ranking       = [ PushMessage::PUSH_MESSAGE_ID =>true ];
        $data          = PushMessageBusiness::getList( $condition, $paging, $ranking, $outputColumns );
       /* //信息编号和摘要注释说明
        $industry = $mpUser->getIndustry();
        $infoidTitle= "信息编号";
        $descriptionTitle = "摘要";
        if($industry == IndustryType::FIANCE)
        {
            $infoidTitle = "来源";
            $descriptionTitle = "备注";
        }*/
        $shownColumns = [
            PushMessage::TITLE ,
            PushMessage::CONTENT =>[Table::COLUMN_TITLE => "内容",],

            PushMessage::INFOID =>[Table::COLUMN_TITLE => "信息编号",Table::COLUMN_CELL_STYLE => 'width:8%',],
            PushMessage::SEND_RANGE => [Table::COLUMN_TITLE => "发送范围类型"] ,
            PushMessage::SEND_STATUS => [Table::COLUMN_TITLE => "发送状态"] ,
            PushMessage::SEND_TIME=> [Table::COLUMN_TITLE => "发布时间",Table::COLUMN_CELL_STYLE => "width:9%"],
            PushMessage::SEND_AUTHOR ,
            PushMessage::CREATE_TIME => [Table::COLUMN_TITLE => "创建时间",Table::COLUMN_CELL_STYLE => "width:9%"],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $community_id = $row[PushMessage::COMMUNITY_ID];
                        $mpUserID = $row[PushMessage::MP_USER_ID];
                        $pushMessageId = $row[PushMessage::PUSH_MESSAGE_ID];
                        $update = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/push_message_dialog/update?push_message_id={$pushMessageId}&community_id={$community_id}&from=app_c');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该群发信息将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/push_message/delete?push_message_id={$pushMessageId}&community_id={$community_id}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        $send = new Link('发布', "javascript:bluefinBH.confirm('确定要发布消息吗？', function() { javascript:wbtAPI.call('../fcrm/push_message/send?push_message_id={$pushMessageId}&mp_user_id={$mpUserID}&community_id={$community_id}&from=app_c', null, function(){bluefinBH.showInfo('发布成功', function() { location.reload(); }); }); })");
                        //$preview =  new Link('预览', "javascript:bluefinBH.ajaxDialog('/mp_admin/user_notify_dialog/preview?user_notify_id={$userNotifyId}&community_id={$community_id}');");
                        $copy = new Link(' 复制', "javascript:bluefinBH.confirm('确定要复制此消息吗？', function() { javascript:wbtAPI.call('../fcrm/push_message/copy?push_message_id={$pushMessageId}&mp_user_id={$mpUserID}&community_id={$community_id}&from=app_c', null, function(){bluefinBH.showInfo('复制成功', function() { location.reload(); }); }); })");
                        if($row[PushMessage::SEND_STATUS] != UserNotifySendStatus::SEND_FINISHED)
                        {
                             $ret = $update."<br>".$delete."<br>".$send;
                        }
                        else
                        {
                            $ret = $copy."<br>".$delete;
                        }
                        return $ret;
                    } ], ];


        $table               = Table::fromDbData( $data, $outputColumns, PushMessage::PUSH_MESSAGE_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }



}
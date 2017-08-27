<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\UserNotifySendStatus;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;

use MP\Model\Mp\UserNotify;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\UserNotifyBusiness;
use WBT\Controller\CommunityControllerBase;

class UserNotifyAppMpController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "user_notify_app_mp";
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
        $outputColumns = UserNotify::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ UserNotify::MP_USER_ID => $mpUserId,UserNotify::COMMUNITY_ID => $communityId,UserNotify::SEND_TYPE => "app_mp"];
        $ranking       = [ UserNotify::USER_NOTIFY_ID =>true ];
        $data          = UserNotifyBusiness::getList( $condition, $paging, $ranking, $outputColumns );
        //信息编号和摘要注释说明
        $industry = $mpUser->getIndustry();
        $infoidTitle= "任务名称";
        $descriptionTitle = "通知类型";
        if($industry == IndustryType::FIANCE)
        {
            $infoidTitle = "来源";
            $descriptionTitle = "备注";
        }
        $shownColumns = [
            UserNotify::TITLE ,
            UserNotify::DESCRIPTION =>[Table::COLUMN_TITLE => $descriptionTitle,],
            UserNotify::CONTENT_URL => [Table::COLUMN_TITLE => "消息链接",
                                          Table::COLUMN_CELL_STYLE => "width:10%",
                                          Table::COLUMN_FUNCTION => function (array $row){
                                                  $url = $row[UserNotify::CONTENT_URL];
                                                  return "<a href=\"$url\">链接到这里</a>";
                                              }],
            UserNotify::INFOID =>[Table::COLUMN_TITLE => $infoidTitle,Table::COLUMN_CELL_STYLE => 'width:8%',],
            UserNotify::SEND_RANGE => [Table::COLUMN_TITLE => "发送范围类型"] ,
            UserNotify::SEND_NO ,
            UserNotify::SPECIALIST_GROUP =>
                [
                    Table::COLUMN_CELL_STYLE => 'width:15%',
                    Table::COLUMN_FUNCTION => function(array $row)
                        {
                            $specialistGroup = $row[UserNotify::SPECIALIST_GROUP];
                            $specialistGroup = explode(",",$specialistGroup);
                            $group="";
                            foreach($specialistGroup as $value)
                            {
                                if(empty($group))
                                {
                                    $group= $value;
                                }
                                else
                                {
                                    $group.= "<br>".$value;
                                }
                            }
                            return $group;
                        }
                ],
            UserNotify::SEND_STATUS => [Table::COLUMN_TITLE => "发送状态"] ,
            UserNotify::SEND_TIME=> [Table::COLUMN_TITLE => "发布时间",Table::COLUMN_CELL_STYLE => "width:9%"],
            UserNotify::SEND_AUTHOR ,
            UserNotify::CREATE_TIME => [Table::COLUMN_TITLE => "创建时间",Table::COLUMN_CELL_STYLE => "width:9%"],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => "操作",
                Table::COLUMN_CELL_STYLE => 'width:10%',
                Table::COLUMN_FUNCTION => function(array $row)
                    {
                        $community_id = $row[UserNotify::COMMUNITY_ID];
                        $mpUserID = $row[UserNotify::MP_USER_ID];
                        $userNotifyId = $row[UserNotify::USER_NOTIFY_ID];
                        $update = new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/user_notify_dialog/update?user_notify_id={$userNotifyId}&community_id={$community_id}&from=app_mp');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，该群发信息将丢失，且无法恢复。<br/><br/>确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/user_notify/delete?user_notify_id={$userNotifyId}&community_id={$community_id}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                        $send = new Link('发布', "javascript:bluefinBH.confirm('确定要发布消息吗？', function() { javascript:wbtAPI.call('../fcrm/user_notify/send?user_notify_id={$userNotifyId}&mp_user_id={$mpUserID}&community_id={$community_id}&from=app_mp', null, function(){bluefinBH.showInfo('发布成功', function() { location.reload(); }); }); })");
                        $preview =  new Link('预览', "javascript:bluefinBH.ajaxDialog('/mp_admin/user_notify_dialog/preview?user_notify_id={$userNotifyId}&community_id={$community_id}');");
                        $copy = new Link(' 复制', "javascript:bluefinBH.confirm('确定要复制此消息吗？', function() { javascript:wbtAPI.call('../fcrm/user_notify/copy?user_notify_id={$userNotifyId}&mp_user_id={$mpUserID}&community_id={$community_id}&from=app_mp', null, function(){bluefinBH.showInfo('复制成功', function() { location.reload(); }); }); })");
                        if($row[UserNotify::SEND_STATUS] != UserNotifySendStatus::SEND_FINISHED)
                        {
                             $ret = $update."<br>".$delete."<br>".$preview."<br>".$send;
                        }
                        else
                        {
                            $ret = $copy."<br>".$delete;
                        }
                        return $ret;
                    } ], ];


        $table               = Table::fromDbData( $data, $outputColumns, UserNotify::USER_NOTIFY_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }



}
<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;

use MP\Model\Mp\CsChatRecord;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\CsChatBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\ReocrdContentType;

class CsChatRecordController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "house_member";
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
        $wxUserID = $this->_request->get('wx_user_id');
        $name = $this->_request->get('name');
        $this->_view->set( "name", $name );
        $csGroupName = $this->_request->get('cs_group_name');
        $this->_view->set( "cs_group_name", $csGroupName );
        $csGroupID = $this->_request->get('cs_group_id');
        $this->_view->set( "cs_group_id", $csGroupID );

        $paging = []; // 先初始化为空
        $outputColumns = CsChatRecord::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $condition     = [ CsChatRecord::MP_USER_ID => $mpUserId,CsChatRecord::COMMUNITY_ID => $communityId,CsChatRecord::WX_USER_ID => $wxUserID];
        $ranking       = [ CsChatRecord::RECORD_TIME => true ];
        $data          = CsChatBusiness::getCsChatRecordList( $condition, $paging, $ranking, $outputColumns );

        $shownColumns = [
            "value" =>
                [
                Table::COLUMN_TITLE => "记录值",
                Table::COLUMN_FUNCTION => function (array $row){
                        $ret_answer = $row[CsChatRecord::CONTENT_VALUE];
                        $pattern="/^http:/";
                        if($row[CsChatRecord::CONTENT_TYPE] == ReocrdContentType::PIC)
                        {
                            $ret_answer = " <a href=\"$ret_answer\"><img src=\"$ret_answer\" width=\"120px\" height=\"\"></a>";
                        }
                        elseif($row[CsChatRecord::CONTENT_TYPE] == ReocrdContentType::VOICE)
                        {
                                $pv = '<object width="120" height="50" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab">';
                                $pv .= '<param name="src" value='.$ret_answer.'>';
                                $pv .= '<param name="controller" value="true">';
                                $pv .= '<param name="type" value="video/quicktime">';
                                $pv .= '<param name="autoplay" value="false">';
                                $pv .= '<param name="target" value="myself">';
                                $pv .= '<param name="bgcolor" value="black">';
                                $pv .= '<param name="pluginspage" value="http://www.apple.com/quicktime/download/index.html">';
                                $pv .= '<embed src='.$ret_answer.' width="120" height="50" controller="true" align="middle" bgcolor="black" target="myself" autostart="false" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/index.html"></embed>';
                                $pv .= '</object>';
                                $ret_answer = $pv;

                        }
                        else if(preg_match($pattern,$ret_answer))
                        {
                            $ret_answer = " <a href=\"$ret_answer\">$ret_answer</a>";
                        }



                        $chatValue = $ret_answer;
                        if(empty($row[CsChatRecord::CS_ID]))
                        {
                          $ret = $row[CsChatRecord::WX_USER_NAME]."(业主)："."<div>$chatValue</div>".$row[CsChatRecord::RECORD_TIME];
                            return "<div style=\"float:left\">$ret</div>";
                        }
                        else
                        {
                            $name = $row[CsChatRecord::CS_NAME]."(客服专员)：";
                            $recordTime = $row[CsChatRecord::RECORD_TIME];
                             $ret = "<div style=\"float:right\">$name</div>"."<br>"."<div style=\"float:right\">$chatValue</div>"."<br>"."<div style=\"float:right\">$recordTime</div>";
                            return "<div style=\"float:right\">$ret</div>";
                        }

    },
                ]
        ];

        $table               = Table::fromDbData( $data, $outputColumns, CsChatRecord::CS_CHAT_RECORD_ID, $paging, $shownColumns,
                               [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
        //type类型分为在用户信息管理显示和在客服组显示两种
        $type = $this->_request->get( 'type' );
        if (!empty($type))
        {
            $this->changeView('WBT/MpAdmin/CsChatRecord.csAnswerList.html');
        }
    }

    //客服专员组所对应的所有客户列表
    public function csGroupAnswerListAction()
    {
        $mpUserID = $this->_request->getQueryParam("mp_user_id");
        $this->_view->set( MpUser::MP_USER_ID, $mpUserID );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserID ]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );

        $csGroupID = $this->_request->getQueryParam("cs_group_id");
        $csGroupName = $this->_request->getQueryParam("cs_group_name");
        $this->_view->set( 'cs_group_name', $csGroupName );


        $paging = []; // 先初始化为空
        $outputColumns = CsChatRecord::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking = [CsChatRecord::RECORD_TIME => true];
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
        $houseMemberWxUserId =  HouseMember::fetchColumn([HouseMember::WX_USER_ID],[HouseMember::CURRENT_CS_GROUP_ID => $csGroupID]);
        $arrayWxUserId = [];
        foreach($houseMemberWxUserId as $value)
        {
            if(!empty($value))
            {
                $arrayWxUserId[] = $value;
            }
        }

        if(empty($arrayWxUserId))
        {
            $arrayWxUserId = [0];
        }
        $csChatRecord = CsChatRecord::fetchRowsWithCount(['*'],[CsChatRecord::WX_USER_ID => $arrayWxUserId],null, $ranking, $paging, $outputColumns);
        //csChatId作为数组下标;
        $csChatRecordInfoChatId = [];
        foreach($csChatRecord as $value)
        {
            $csChatRecordInfoChatId[$value[CsChatRecord::CS_CHAT_RECORD_ID]] = $value;
        }
        //找出同一个微信id下最新消息
        $csChatRecordInfo = [];
        foreach($csChatRecord as $value)
        {
            $csChatRecordInfo[$value[CsChatRecord::WX_USER_ID]][]= $value[CsChatRecord::CS_CHAT_RECORD_ID];
        }
        $csChatIdMax = [];//同一个微信账号下最新消息csChatId
        foreach($csChatRecordInfo as $key => $value)
        {
            $csChatIdMax[$key] = max($value);
        }
        // 找出所有客服专员所对的微信用户最新消息
        $data = [];
        foreach($csChatIdMax as $key => $value)
        {
            $data[] = $csChatRecordInfoChatId[$value];
        }

        $shownColumns = [
            CsChatRecord::WX_USER_NAME,
            CsChatRecord::CS_NAME => [Table::COLUMN_TITLE => "客服专员名称"],
            Table::COLUMN_OPERATIONS => [
                Table::COLUMN_TITLE => '操作',
                Table::COLUMN_FUNCTION => function (array $row)use($mpUserID,$csGroupID)
                    {

                        $wxUserID = $row[CsChatRecord::WX_USER_ID];
                        $url = sprintf(" <a href='/mp_admin/cs_chat_record/list?wx_user_id=%s&mp_user_id=%s&community_id=%s&name=%s&type=%s&cs_group_id=%s&cs_group_name=%s' >查看</a>",$wxUserID,$mpUserID,$row[CsChatRecord::COMMUNITY_ID],$row[CsChatRecord::WX_USER_NAME],"cs_group",$row[CsChatRecord::CS_GROUP_ID],$row[CsChatRecord::GROUP_NAME]);
                        return $url;
                    }
            ],

        ];

        $table               = Table::fromDbData( $data, $outputColumns,
            CsChatRecord::CS_CHAT_RECORD_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered ' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );


    }

}
<?php

namespace WBT\Controller\WxUser;

use MP\Model\Mp\ChatRoomRecord;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\ChannelBusiness;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use Common\Helper\BaseController;
use Bluefin\Data\Database;
use Bluefin\HTML\Table;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\UrgentNoticeReadRecord;
use WBT\Business\Weixin\CsChatBusiness;
use MP\Model\Mp\ArticleType;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\ReocrdContentType;
use WBT\Controller\WxUserControllerBase;
class ChatRoomController extends WxUserControllerBase
{
    public function chatAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $mpUserID = $this->_request->getQueryParam("mp_user_id");


        $outputColumns = ChatRoomRecord::s_metadata()->getFilterOptions();
        $paging = []; // 先初始化为空
        $ranking = [ChatRoomRecord::RECORD_TIME];
        $condition = $this->_request->getQueryParams();
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
        $chatRoomRecord = ChatRoomRecord::fetchRows(['*'],[ChatRoomRecord::MP_USER_ID => $mpUserID],null, $ranking, $paging, $outputColumns);
        //取出最新的用户记录
        $userNewRecord = array_slice($chatRoomRecord,-1,1);
        $this->_view->set("user_new_record_id",$userNewRecord[0][ChatRoomRecord::CHAT_ROOM_RECORD_ID]);
        $chatRoomRecordSlice = array_slice($chatRoomRecord,-6);//取出前5个数据
        $chatRoomRecordRemain = array_slice($chatRoomRecord,-100,-6);//取出剩下数据
        $houseMemberType =  HouseMember::fetchColumn([HouseMember::MEMBER_TYPE],[HouseMember::WX_USER_ID => $wxUserID]);
        if(strict_in_array('owner',$houseMemberType))
        {
            $status = "业主";
        }
        else
        {
            $status = "住户";
        }
        $shownColumns = [
            'name' => [
                Table::COLUMN_TITLE => 'name',
                Table::COLUMN_FUNCTION => function (array $row)use($status,$wxUserID){
                        $wxUser = new WxUser([WxUser::WX_USER_ID => $row[ChatRoomRecord::WX_USER_ID]]);
                        $headPic = $wxUser->getHeadPic();
                        $name = $row[ChatRoomRecord::WX_USER_NAME];
                        //获取时间字段
                        $ret = CsChatBusiness::getAnswerTimeRoom($row[ChatRoomRecord::RECORD_TIME],$row[ChatRoomRecord::CHAT_ROOM_RECORD_ID],$row[ChatRoomRecord::WX_USER_ID]);
                        $ret = explode("%",$ret);
                        $ret_time = $ret[0];//正常显示日期
                        $ret_check = $ret[1];//比对天数日期
                        $showTime = $ret[2];
                        $ret_answer = $row[ChatRoomRecord::CONTENT_VALUE];
                        $pattern="/^http:/";
                        if($row[ChatRoomRecord::CONTENT_TYPE] == ReocrdContentType::PIC)
                        {
                            $ret_answer = " <a href=\"$ret_answer\"><img src=\"$ret_answer\" width=\"120px\" height=\"\"></a>";
                        }
                        elseif($row[ChatRoomRecord::CONTENT_TYPE] == ReocrdContentType::VOICE)
                        {
                            if(strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
                            {
                                if(strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "android"))
                                {
                                    $ret_answer = "<audio src=".$ret_answer." controls=\"controls\" style=\"width:120px;\"></audio>";
                                }
                                else
                                {
                                    $ret_answer = "暂不支持语音播放";
                                }

                            }
                            else
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

                        }
                        else if(preg_match($pattern,$ret_answer))
                        {
                            $ret_answer = " <a href=\"$ret_answer\">$ret_answer</a>";
                        }

                        if($wxUserID  != $row[ChatRoomRecord::WX_USER_ID])
                        {
                            $ret = "<div class=\"owner-name\">$name</div>"."<span  class=\"owner\"><img src = \"$headPic\" width=\"45px\" height=\"45px\"/></span>"."<span class='owner triangle-border left'>$ret_answer</span>";
                            if($showTime == "show")
                            {
                                if($ret_check == get_current_ymd())
                                {
                                    return "<div style=\"width:70px\" class=\"owner-time\">$ret_time</div>".$ret;
                                }
                                else if($ret_check ==  date('Ymd' , strtotime('-1 day')))
                                {
                                    return "<div style=\"width:95px\" class=\"owner-time\">$ret_time</div>".$ret;
                                }
                                else
                                {
                                    return "<div style=\"width:160px\" class=\"owner-time\">$ret_time</div>".$ret;
                                }
                            }
                            else
                            {
                                return $ret;
                            }

                        }
                        else
                        {
                            $ret = "<div class=\"customer-name\">$name</div>"."<div><span  class=\"customer\"><img src = \"$headPic\" width=\"45px\" height=\"45px\"/></span>"."<span  class='customer triangle-border-a right'>$ret_answer</span></div>";
                            if($showTime == "show")
                            {
                                if($ret_check == get_current_ymd())
                                {
                                    return "<div style=\"width:70px\" class=\"customer-time\">$ret_time</div>".$ret;
                                }
                                else if($ret_check ==  date('Ymd' , strtotime('-1 day')))
                                {
                                    return "<div style=\"width:95px\" class=\"customer-time\">$ret_time</div>".$ret;
                                }
                                else
                                {
                                    return "<div style=\"width:160px\" class=\"customer-time\">$ret_time</div>".$ret;
                                }
                            }
                            else
                            {
                                return $ret;
                            }


                        }
                    }
            ],

        ];

        $table = Table::fromDbData( $chatRoomRecordSlice, $outputColumns, ChatRoomRecord::CHAT_ROOM_RECORD_ID, $paging, $shownColumns,
            [ 'class' => '' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
        $tableRemain  = Table::fromDbData( $chatRoomRecordRemain, $outputColumns, $ranking, $paging, $shownColumns,
            [ 'class' => '' ] );
        $tableRemain ->showRecordNo = false;
        if(!empty($chatRoomRecordRemain))
        {
            $this->_view->set( 'remain', $tableRemain  );
        }


        $this->_view->set("wx_user_id",$wxUserID);
        $this->_view->set("mp_user_id",$mpUserID);

        $this->_view->set("wx_user_name",$wxUser->getNick());
        $this->_view->set("wx_user_type",$status);

        if(strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $this->_view->set("weixin",true);
        }
        else
        {
            $this->_view->set("weixin",false);
        }
    }

}
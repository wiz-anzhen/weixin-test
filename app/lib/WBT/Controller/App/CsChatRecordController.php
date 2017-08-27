<?php

namespace WBT\Controller\App;

use MP\Model\Mp\CsChatRecord;
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
use WBT\Controller\CwbControllerBase;
class CsChatRecordController extends CwbControllerBase
{
    public function answerAction()
    {
        $wxUserID = $this->_request->getQueryParam("wx_user_id");
        $mpUserID = $this->_request->getQueryParam("mp_user_id");
        $csID = $this->_request->getQueryParam("cs_id");
        $csWxUserID = $this->_request->getQueryParam("cs_wx_user_id");
        $type = $this->_request->getQueryParam("type");

        $outputColumns = CsChatRecord::s_metadata()->getFilterOptions();
        $paging = []; // 先初始化为空
        $ranking = [CsChatRecord::RECORD_TIME];
        $condition = $this->_request->getQueryParams();
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
        $csChatRecord = CsChatRecord::fetchRows(['*'],[CsChatRecord::WX_USER_ID => $wxUserID],null, $ranking, $paging, $outputColumns);
        //取出最新的用户记录
        $userNewRecord = array_slice($csChatRecord,-1,1);
        $this->_view->set("user_new_record_id",$userNewRecord[0][CsChatRecord::CS_CHAT_RECORD_ID]);
        $csChatRecordSlice = array_slice($csChatRecord,-6);//取出前5个数据
        $csChatRecordRemain = array_slice($csChatRecord,-100,-6);//取出剩下数据
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
                Table::COLUMN_FUNCTION => function (array $row)use($status){
                        $wxUser = new WxUser([WxUser::WX_USER_ID => $row[CsChatRecord::WX_USER_ID]]);
                        $headPic = $wxUser->getHeadPic();
                        //获取时间字段
                        $ret = CsChatBusiness::getAnswerTime($row[CsChatRecord::RECORD_TIME],$row[CsChatRecord::CS_CHAT_RECORD_ID],$row[CsChatRecord::WX_USER_ID]);
                        $ret = explode("%",$ret);
                        $ret_time = $ret[0];//正常显示日期
                        $ret_check = $ret[1];//比对天数日期
                        $showTime = $ret[2];
                        $ret_answer = $row[CsChatRecord::CONTENT_VALUE];
                        $pattern="/^http:/";
                        if($row[CsChatRecord::CONTENT_TYPE] == ReocrdContentType::PIC)
                        {
                            $ret_answer = " <a href=\"$ret_answer\"><img src=\"$ret_answer\" width=\"120px\" height=\"\"></a>";
                        }
                        elseif($row[CsChatRecord::CONTENT_TYPE] == ReocrdContentType::VOICE)
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

                        if(empty($row[CsChatRecord::CS_ID]))
                        {
                            $name = $row[CsChatRecord::WX_USER_NAME];
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
                            $csID = $row[CsChatRecord::CS_ID];
                            $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $csID]);
                            $wxUserID = $cs->getWxUserID();
                            $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
                            $headPic = $wxUser->getHeadPic();
                            $name = $row[CsChatRecord::CS_NAME];
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

        $table = Table::fromDbData( $csChatRecordSlice, $outputColumns, CsChatRecord::CS_CHAT_RECORD_ID, $paging, $shownColumns,
            [ 'class' => '' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
        $tableRemain  = Table::fromDbData( $csChatRecordRemain, $outputColumns, $ranking, $paging, $shownColumns,
            [ 'class' => '' ] );
        $tableRemain ->showRecordNo = false;
        if(!empty($csChatRecordRemain))
        {
            $this->_view->set( 'remain', $tableRemain  );
        }
        $hm = new HouseMember([HouseMember::WX_USER_ID=>$wxUserID]);
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID=>$csID]);
        $csGroupName = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID=>$cs->getCustomerSpecialistGroupID()]);
        $this->_view->set("wx_user_id",$wxUserID);
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("cs_id",$csID);
        $this->_view->set("type",$type);
        $this->_view->set("cs_wx_user_id",$csWxUserID);
        $this->_view->set("wx_user_name",$hm->getName());
        $this->_view->set("wx_user_type",$status);
        $this->_view->set("cs_name",$cs->getName());
        $this->_view->set("cs_group_name",$csGroupName->getGroupName());
        if(strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $this->_view->set("weixin",true);
        }
        else
        {
            $this->_view->set("weixin",false);
        }
    }

//客服专员所对应的所有客户列表
    public function answerTableAction()
    {
        $mpUserID = $this->_request->getQueryParam("mp_user_id");
        $csGroupID = $this->_request->getQueryParam("cs_group_id");
        $csWxUserID = $this->_request->getQueryParam("cs_wx_user_id");//同组客服专员微信id
        if(strpos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger"))
        {
            $wxUserID = $this->_wxUserID;
            $csGroupWxUserIDs = CustomerSpecialist::fetchColumn([CustomerSpecialist::WX_USER_ID],[CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);
            if(!strict_in_array($wxUserID,$csGroupWxUserIDs))
            {
                $this->_redirectToErrorPage("您没有权限访问此页面。");
            }
        }
        $outputColumns = CsChatRecord::s_metadata()->getFilterOptions();
        $paging = []; // 先初始化为空
        $ranking = [CsChatRecord::RECORD_TIME => true];
        $condition = $this->_request->getQueryParams();
        // 从url中提取condition和panging
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
        $wxUser = WxUser::fetchRows([WxUser::WX_USER_ID,WxUser::HEAD_PIC,WxUser::NICK],[WxUser::WX_USER_ID => $arrayWxUserId]);
        log_debug("222222222222222222222222222",$wxUser);
        $wxUserInfo = [];// 用户头像，姓名信息
        foreach($wxUser as $value)
        {
            $wxUserInfo[$value[WxUser::WX_USER_ID]] = $value;

        }
        if(empty($arrayWxUserId))
        {
            $arrayWxUserId = [0];
        }
        $limitTime = date("YmdHis" , strtotime("-3 week"));
        log_debug("=================".$limitTime);
        $expr = sprintf("`record_time`> '%s'",(int)$limitTime);
        $con = new \Bluefin\Data\DbCondition($expr);
        $condition = [$con, CsChatRecord::WX_USER_ID => $arrayWxUserId];
        $csChatRecord = CsChatRecord::fetchRows(['*'],$condition,null, $ranking, $paging, $outputColumns);
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
        //log_debug(print_r($wxUserInfo));
        //log_debug(print_r($data));
        $shownColumns = [
            'name' => [
                Table::COLUMN_TITLE => 'name',
                Table::COLUMN_FUNCTION => function (array $row)use($wxUserInfo,$mpUserID,$csWxUserID)
                    {

                        $wxUserID = $row[CsChatRecord::WX_USER_ID];//聊天记录用户信息id
                        $hm = new HouseMember([HouseMember::WX_USER_ID => $wxUserID]);
                        $csID = $hm->getCurrentCsID();
                        if(empty($csWxUserID))
                        {
                            $type = 1;
                        }
                        else
                        {
                            $type = 2;
                        }
                        //查找客服专员最后一次回答时间
                        $expr = " `cs_id` is not null";
                        $con = new \Bluefin\Data\DbCondition($expr);
                        $condition = [$con, 'wx_user_id' => $wxUserID];
                        $recordTime = CsChatRecord::fetchColumn([CsChatRecord::RECORD_TIME],$condition);
                        rsort($recordTime);
                        //查找客服专员最后一次回答时间之前业主提问数量
                        $lastTime = $recordTime[0];//客服专员最后一次回复时间
                        $expr = sprintf("`record_time`> '%s' and `cs_id` is null",$lastTime);
                        $con = new \Bluefin\Data\DbCondition($expr);
                        $condition = [$con, 'wx_user_id' => $wxUserID];
                        $noAnswerNumber = CsChatRecord::fetchCount($condition);
                        //获取时间字段
                        $ret_time = CsChatBusiness::getAnswerTableTime($row[CsChatRecord::RECORD_TIME]);
                        $ret_name = $wxUserInfo[$row[CsChatRecord::WX_USER_ID]][WxUser::NICK];
                        $ret_head_pic = $wxUserInfo[$row[CsChatRecord::WX_USER_ID]][WxUser::HEAD_PIC];
                        if(!isset($row[CsChatRecord::CS_ID]))
                        {
                            $ret_answer = $row[CsChatRecord::CONTENT_VALUE];
                        }
                        else
                        {
                            $ret_answer = $row[CsChatRecord::CS_NAME]."：".$row[CsChatRecord::CONTENT_VALUE];

                        }

                        if($noAnswerNumber == 0)
                        {
                            $retValue =  "<div style='padding:8px;'><div class=\"time\">$ret_time</div>"."<div><span class=\"pic\"><img src = \"$ret_head_pic\" width=\"45px\" height=\"45px\"/></span><div ><div class=\"name\">$ret_name</div><div class=\"answer\">$ret_answer</div></div>"."<div style='height:3px;background-color:white;line-height:5px;clear:both;display:block;overflow:hidden'></div>";
                        }
                        else
                        {
                            $retValue =  "<div style='padding:8px;'><div class=\"time\">$ret_time</div>"."<div class=\"number\">$noAnswerNumber</div>"."<div><span class=\"pic\"><img src = \"$ret_head_pic\" width=\"45px\" height=\"45px\"/></span><div ><div class=\"name\">$ret_name</div><div class=\"answer\">$ret_answer</div></div>"."<div style='height:3px;background-color:white;line-height:5px;clear:both;display:block;overflow:hidden'></div>";
                        }
                        $host =  ConfigBusiness::getHost();//获取主机名
                        if($type == 1)
                        {
                            $url = sprintf(" <a href='%s/wx_user/cs_chat_record/answer?type=1&wx_user_id=%s&mp_user_id=%s&cs_id=%s#bottom-body'>$retValue</a>",$host,$wxUserID,$mpUserID,$csID);
                        }
                        else
                        {
                            $url = sprintf("<a href='%s/wx_user/cs_chat_record/answer?type=2&wx_user_id=%s&mp_user_id=%s&cs_id=%s&cs_wx_user_id=%s#bottom-body'>$retValue</a>",$host,$wxUserID,$mpUserID,$csID,$csWxUserID);
                        }

                        return $url;
                    }
            ],

        ];

        $table               = Table::fromDbData( $data, $outputColumns,
            CsChatRecord::CS_CHAT_RECORD_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered ' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);
        $this->_view->set("cs_group_name",$csGroup->getGroupName());

    }

}
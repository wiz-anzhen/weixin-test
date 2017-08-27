<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-6-26
 * Time: 上午10:55
 */

namespace WBT\Business\Weixin;
use MP\Model\Mp\ChatRoomRecord;
use MP\Model\Mp\Community;
use MP\Model\Mp\CountryHotLine;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\WxUser;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\CsChatRecord;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\ReocrdContentType;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\CommunityConfigType;
use MP\Model\Mp\CommunityConfig;
use MP\Model\Mp\WeixinMessageType;
class CsChatBusiness {
    public static function processCsChat(MpUser &$mpUser, WxUser &$wxUser, $userMessage,$type)
    {
        //是否为图片消息
        $userMessageImg = "";
        if($type == WeixinMessageType::IMAGE)
        {
            $userMessageImg = "消息为图片消息";
            $type = "pic";
        }
        elseif($type == WeixinMessageType::VOICE)
        {
            $userMessageImg = "消息为语音消息";
            $type = "voice";
        }
        else
        {
            $type = "text";
            $userMessageImg = $userMessage;
        }
        $mpUserID = $mpUser->getMpUserID();
        $wxUserID = $wxUser->getWxUserID();
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityName = $community->getName();
        $currentTime = date('Y-m-d H:i:s',time());
        $formatTime = '';
        if( "am"==date("a"))
        {
            $formatTime = '上午 '.date('H:i',time());
        }elseif("pm"==date("a"))
        {
            $formatTime = '下午 '.date('H:i',time());
        }
        $hm = new HouseMember([HouseMember::MP_USER_ID=>$mpUserID,
                              HouseMember::WX_USER_ID=>$wxUserID,
                              HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()
                              ]);
        $csGroupID = $hm->getCurrentCsGroupID();
        $gn = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID=>$csGroupID]);
        $workTime = $gn->getWorkTime();
        $workTime = explode("-",$workTime);
        $hourMinute = strtotime(date("H:i",time()));//当前小时秒时间戳
        $workCheck = "out";
        foreach($workTime as $value)
        {
            $time = explode(":",$value);
            $startTime = strtotime($time[0].$time[1]);
            $endTime = strtotime($time[2].$time[3]);
            if($hourMinute>$startTime and  $hourMinute<$endTime)
            {
                $workCheck = "in";
                break;
            }
        }
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID=>$hm->getCurrentCsID(),CustomerSpecialist::COMMUNITY_ID=>$hm->getCommunityID()]);
        $csGroup = CustomerSpecialist::fetchColumn(CustomerSpecialist::WX_USER_ID,[CustomerSpecialist::VALID=>1,CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID=>$cs->getCustomerSpecialistGroupID()]);//取出同一客服组所有客服专员的微信id
        $csHoliday = [];//判断是否所有客服专员都休假
        foreach($csGroup as $value)
        {
            if(isset($value))
            {
                if(self::checkWork($value))
                {
                    $csHoliday[] = "work";
                }
                else
                {
                    $csHoliday[] = "holiday";
                }
            }
        }
        $csWork = "work";//如果所有客服专员都休假csHoliday中没有work
        if(!strict_in_array($csWork ,$csHoliday))
        {
            $csWork = "holiday";
        }
        $host =  ConfigBusiness::getHost();//获取主机名
        //判断是业主是否有对应的客服专员,如果为空那么向业主发送下面消息，并返回
        if($cs->isEmpty())
        {
            $userMessage = sprintf("您好，欢迎使用到家服务！\n客服连线需要先设置个人的客服专员，<a href='%s/wx_user/profile/change_customer_id?current_cs_id=%s&wx_user_id=%s&mp_user_id=%s'>请点击本链接进行设置客服专员。</a>",$host, 0,$wxUser->getWxUserID(),$mpUserID);
            WxApiBusiness::sentTextMessage($mpUserID,$wxUserID,$userMessage);
            return;
        }
        //如果业主有对应的客服专员，程序继续执行
        if($workCheck == "in" and $csWork == "work")
        {
            self::updateHouseMemberReplyTime($hm->getHouseMemberID(),$currentTime);
            if(empty($userMessage))
            {
                $userMessage = "物业客服为您服务";
            }
            $csChatRecord = new CsChatRecord();
            $csChatRecord->setMpUserID($mpUserID)
                ->setWxUserID($wxUserID)
                ->setWxUserName($hm->getName())
                ->setCommunityID($wxUser->getCurrentCommunityID())
                ->setContentValue($userMessage)
                ->setVipNo($wxUser->getVipNo())
                ->setContentType($type)
                ->setRecordTime($currentTime)
                ->insert(true);
            //将业主留言发送到对应的客服人员
            $mpUserConfig= ConfigBusiness::mpUserConfig($cs->getMpUserID());
            $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
            $url = sprintf("%s/wx_user/cs_chat_record/answer_table?type=1&wx_user_id=%s&mp_user_id=%s&cs_id=%s&cs_group_id=%s#bottom-body",$host,$wxUserID,$mpUserID,$cs->getCustomerSpecialistID(),$csGroupID);
           //如果客服专员休假则不再向客服专员发送信息，并告知用户客服专员休假信息
            if(self::checkWork($cs->getWxUserID()))
            {
                $remark = "\\n你需要尽快回复";
                $template = array( 'touser' => $cs->getWxUserID(),
                                   'template_id' => "$templateID",
                                   'url' => $url,
                                   'topcolor' => "#62c462",
                                   'data'   => array('first' => array('value' => urlencode("留言者：".$wxUser->getNick()
                                       ."\\n小区名称：".$communityName."\\n客服专员：".$cs->getName()),'color' =>"#222", ),
                                                     'keyword1' => array('value' => urlencode($userMessageImg),'color' =>"#222", ),
                                                     'keyword2' => array('value' => urlencode($formatTime),'color' =>"#222", ),
                                                     'remark' => array('value' => urlencode("$remark") ,
                                                                       'color' =>"#222" ,))
                );

                WxApiBusiness::sentTemplateMessage($cs->getMpUserID(),$template);
            }
            else
            {
                $hotLine = $community->getPhone();
                $message = sprintf("您的客服专员%s今天休假，您的信息已转发到客服主管，如有紧急需要，请联系我们服务热线%s",$cs->getName(),$hotLine);
                WxApiBusiness::sentTextMessage($mpUserID,$wxUserID,$message);
            }
            foreach($csGroup as $csWxID)
            {
                if($cs->getWxUserID()!=$csWxID && isset($csWxID))
                {
                    $url = sprintf("%s/wx_user/cs_chat_record/answer_table?type=2&wx_user_id=%s&mp_user_id=%s&cs_id=%s&cs_wx_user_id=%s&cs_group_id=%s#bottom-body",$host,$wxUserID,$mpUserID,$cs->getCustomerSpecialistID(),$csWxID,$csGroupID);
                    $remark = "\\n"."该问题应该由".$cs->getName()."回复，但如果".$cs->getName()."较长时间未回复，你可以帮忙回复";
                    if(!(self::checkWork($cs->getWxUserID())))
                    {
                        $remark = "\\n".$cs->getName()."正在休假请帮助回复";

                    }
                    if(self::checkWork($csWxID))
                    {
                        $template = array( 'touser' => $csWxID,
                                           'template_id' => "$templateID",
                                           'url' => $url,
                                           'topcolor' => "#62c462",
                                           'data'   => array('first' => array('value' => urlencode("留言者：".$wxUser->getNick()
                                               ."\\n小区名称：".$communityName."\\n客服专员：".$cs->getName()),
                                                                              'color' =>"#222", ),
                                                             'keyword1' => array('value' => urlencode($userMessageImg),'color' =>"#222", ),
                                                             'keyword2' => array('value' => urlencode($formatTime),
                                                                                 'color' =>"#222", ),
                                                             'remark' => array('value' => urlencode("$remark") ,
                                                                               'color' =>"#222" ,))
                        );

                        WxApiBusiness::sentTemplateMessage($cs->getMpUserID(),$template);
                    }
                }
            }

        }
        else if($workCheck == "out" or $csWork == "holiday")
        {
            $communityID = $hm->getCommunityID();
            $config = ConfigBusiness::communityConfig($communityID);
            if(!isset($config[CommunityConfigType::CS_ANSWER]))
            {
                //业主收到的信息内容
                $userMessage =  "请拨打客服中心电话";
            }
            else
            {
                $userMessage =  $config[CommunityConfigType::CS_ANSWER];
            }
            WxApiBusiness::sentTextMessage($mpUserID,$wxUserID,$userMessage);
        }

    }


    public static function updateHouseMemberReplyTime($houseMemberID,$currentTime)
    {
        $hm = new HouseMember([HouseMember::HOUSE_MEMBER_ID => $houseMemberID]);
        if($hm->isEmpty())
        {
            log_debug("Could not find houseMember($houseMemberID)");
        }
        $hm->setReplyTime($currentTime)->update();
    }

    public static function checkWork($wxUserID)
    {
        // 如果客服专员正在休假返回false
        $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $wxUserID]);
        if($cs->isEmpty())
        {
            log_debug("Could not find CustomerSpecialist($wxUserID)");
        }
        $holiday = $cs->getHoliday();
        $currentDay = date('Y-m-d',time());
        if(in_array_after_explode($currentDay,$holiday,","))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function getCsChatRecordList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return CsChatRecord::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getAnswerTableTime($ret_time)
    {
        $ret_time_hour = substr($ret_time,11,2);
        $ret_time_minute = substr($ret_time,11,5);
        $ret_time_day = substr($ret_time,0,10);
        $ret_time_day = explode("-",$ret_time_day);
        $ret_time_day_now = substr($ret_time,2,8);

        if(intval($ret_time_hour)>12)
        {

            $ret_time_bucket = "下午";
        }
        else
        {
            $ret_time_bucket = "上午";
        }
        $yesterday = date('Ymd', strtotime("-1 day"));
        if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] == $yesterday)
        {
            $ret_time = "昨天".$ret_time_bucket;
        }
        else if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] == get_current_ymd())
        {
            $ret_time = $ret_time_bucket.$ret_time_minute;
        }
        else
        {
            $ret_time = $ret_time_day_now;
        }
        return $ret_time;
    }

    public static function getAnswerTime($ret_time,$csRecordID,$wxUserID)
    {
        $ret_time_hour = substr($ret_time,11,2);
        $ret_time_minute = substr($ret_time,11,5);
        $ret_time_day = substr($ret_time,0,10);
        $ret_time_day = explode("-",$ret_time_day);
        $ret_time_day_now = $ret_time_day[0]."年".$ret_time_day[1]."月".$ret_time_day[2]."日";
        if(intval($ret_time_hour)>12)
        {

            $ret_time_bucket = "下午";
        }
        else
        {
            $ret_time_bucket = "上午";
        }

        if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] == get_current_ymd())
        {
            $retTime = $ret_time_bucket.$ret_time_minute;
        }
        else if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] ==  date('Ymd' , strtotime('-1 day')))
        {
            $retTime = "昨天"."&nbsp".$ret_time_bucket.$ret_time_minute;
        }
        else
        {
            $retTime = $ret_time_day_now."&nbsp".$ret_time_bucket.$ret_time_minute;
        }
        $showTime = "show";
        if($csRecordID != 1)
        {
            $csRecordTime = CsChatRecord::fetchColumn([CsChatRecord::RECORD_TIME],[CsChatRecord::WX_USER_ID => $wxUserID]);
            $lastTime = "";
            foreach($csRecordTime as $key => $value)
            {
                if($value == $ret_time)
                {
                    $lastTime = $key - 1;
                }
            }
            $lastTime = $csRecordTime[$lastTime];
            $diff = strtotime($ret_time) - strtotime($lastTime);
            $minute = $diff % 3600;
            $minute = floor($minute/60);//相差多少分
            if($minute < 2)
            {
                $showTime = "hide";
            }
            else
            {
                $showTime = "show";
            }
        }
        return $retTime."%".$ret_time_day[0].$ret_time_day[1].$ret_time_day[2]."%".$showTime;
    }

    public static function getAnswerTimeRoom($ret_time,$csRecordRoomID,$wxUserID)
    {
        $ret_time_hour = substr($ret_time,11,2);
        $ret_time_minute = substr($ret_time,11,5);
        $ret_time_day = substr($ret_time,0,10);
        $ret_time_day = explode("-",$ret_time_day);
        $ret_time_day_now = $ret_time_day[0]."年".$ret_time_day[1]."月".$ret_time_day[2]."日";
        if(intval($ret_time_hour)>12)
        {

            $ret_time_bucket = "下午";
        }
        else
        {
            $ret_time_bucket = "上午";
        }

        if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] == get_current_ymd())
        {
            $retTime = $ret_time_bucket.$ret_time_minute;
        }
        else if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] ==  date('Ymd' , strtotime('-1 day')))
        {
            $retTime = "昨天"."&nbsp".$ret_time_bucket.$ret_time_minute;
        }
        else
        {
            $retTime = $ret_time_day_now."&nbsp".$ret_time_bucket.$ret_time_minute;
        }
        $showTime = "show";
        if($csRecordRoomID != 1)
        {
            $csRecordTime = ChatRoomRecord::fetchColumn([ChatRoomRecord::RECORD_TIME],[CsChatRecord::WX_USER_ID => $wxUserID]);
            $lastTime = "";
            foreach($csRecordTime as $key => $value)
            {
                if($value == $ret_time)
                {
                    $lastTime = $key - 1;
                }
            }
            $lastTime = $csRecordTime[$lastTime];
            $diff = strtotime($ret_time) - strtotime($lastTime);
            $minute = $diff % 3600;
            $minute = floor($minute/60);//相差多少分
            if($minute < 2)
            {
                $showTime = "hide";
            }
            else
            {
                $showTime = "show";
            }
        }
        return $retTime."%".$ret_time_day[0].$ret_time_day[1].$ret_time_day[2]."%".$showTime;
    }
   //获取评论时间格式
    public static function getCommentTime($ret_time)
    {
        $ret_time_hour = substr($ret_time,11,2);
        $ret_time_minute = substr($ret_time,11,5);
        $ret_time_day = substr($ret_time,0,10);
        $ret_time_day = explode("-",$ret_time_day);
        $ret_time_day_now = $ret_time_day[0]."年".$ret_time_day[1]."月".$ret_time_day[2]."日";
        if(intval($ret_time_hour)>12)
        {

            $ret_time_bucket = "下午";
        }
        else
        {
            $ret_time_bucket = "上午";
        }

        if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] == get_current_ymd())
        {
            $retTime = $ret_time_bucket.$ret_time_minute;
        }
        else if($ret_time_day[0].$ret_time_day[1].$ret_time_day[2] ==  date('Ymd' , strtotime('-1 day')))
        {
            $retTime = "昨天"."&nbsp".$ret_time_bucket.$ret_time_minute;
        }
        else
        {
            $retTime = $ret_time_day_now."&nbsp".$ret_time_bucket.$ret_time_minute;
        }

        return $retTime;
    }

     //长微博客服沟通
    public static function processCsChatCwb(MpUser &$mpUser, WxUser &$wxUser, $userMessage,$type)
    {
        //是否为图片消息
        $userMessageImg = "";
        if($type == WeixinMessageType::IMAGE)
        {
            $userMessageImg = "消息为图片消息";
            $type = "pic";
        }
        elseif($type == WeixinMessageType::VOICE)
        {
            $userMessageImg = "消息为语音消息";
            $type = "voice";
        }
        else
        {
            $type = "text";
            $userMessageImg = $userMessage;
        }
        $mpUserID = $mpUser->getMpUserID();
        $wxUserID = $wxUser->getWxUserID();

        $currentTime = date('Y-m-d H:i:s',time());
        $formatTime = '';
        if( "am"==date("a"))
        {
            $formatTime = '上午 '.date('H:i',time());
        }elseif("pm"==date("a"))
        {
            $formatTime = '下午 '.date('H:i',time());
        }

        $csGroupArray = CustomerSpecialistGroup::fetchRows([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID],[CustomerSpecialistGroup::MP_USER_ID => $mpUserID]);

        foreach($csGroupArray as $csGroupID)
        {
            $gn = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);
            $workTime = $gn->getWorkTime();
            $workTime = explode("-",$workTime);
            $hourMinute = strtotime(date("H:i",time()));//当前小时秒时间戳
            $workCheck = "out";
            foreach($workTime as $value)
            {
                $time = explode(":",$value);
                $startTime = strtotime($time[0].$time[1]);
                $endTime = strtotime($time[2].$time[3]);
                if($hourMinute>$startTime and  $hourMinute<$endTime)
                {
                    $workCheck = "in";
                    break;
                }
            }

            $csGroup = CustomerSpecialist::fetchColumn(CustomerSpecialist::WX_USER_ID,[CustomerSpecialist::VALID=>1,CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);//取出同一客服组所有客服专员的微信id

            foreach($csGroup as $value)
            {
                $csHoliday = [];//判断是否所有客服专员都休假
                if(isset($value))
                {
                    if(self::checkWork($value))
                    {
                        $csHoliday[] = "work";
                    }
                    else
                    {
                        $csHoliday[] = "holiday";
                    }
                }
                $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $value]);

                $csWork = "work";//如果所有客服专员都休假csHoliday中没有work
                if(!strict_in_array($csWork ,$csHoliday))
                {
                    $csWork = "holiday";
                }
                $host =  ConfigBusiness::getHost();//获取主机名

                //如果业主有对应的客服专员，程序继续执行
                if($workCheck == "in" and $csWork == "work")
                {
                    //将业主留言发送到对应的客服人员
                    $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
                    $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
                    $url = sprintf("%s/wx_user/cs_chat_record_cwb/answer_cwb_table?type=1&wx_user_id=%s&mp_user_id=%s&cs_id=%s&cs_group_id=%s#bottom-body",$host,$wxUserID,$mpUserID,$cs->getCustomerSpecialistID(),$csGroupID);
                    //如果客服专员休假则不再向客服专员发送信息，并告知用户客服专员休假信息
                    if(self::checkWork($cs->getWxUserID()))
                    {
                        //$remark = "\\n你需要尽快回复";
                        $remark = '';
                        $template = array( 'touser' => $cs->getWxUserID(),
                            'template_id' => "$templateID",
                            'url' => $url,
                            'topcolor' => "#62c462",
                            'data'   => array(
                                'first' => array('value' => urlencode("留言者：".$wxUser->getNick()
                              /*  ."\\n客服专员：".$cs->getName() */ )  ,'color' =>"#222", ),
                                'keyword1' => array('value' => urlencode($userMessageImg),'color' =>"#222", ),
                                'keyword2' => array('value' => urlencode($formatTime),'color' =>"#222", ),
                                'remark' => array('value' => urlencode("$remark") ,
                                    'color' =>"#222" ,))
                        );

                        WxApiBusiness::sentTemplateMessage($cs->getMpUserID(),$template);
                    }

                }
            }
        }
        if(empty($userMessage))
        {
            if($mpUserID == '45829' )
            {
                $userMessage = "采购助手客服";
            }
            else
            {
                $userMessage = "长微博客服";
            }

        }
        $csChatRecord = new CsChatRecord();
        $csChatRecord->setMpUserID($mpUserID)
            ->setWxUserID($wxUserID)
            ->setWxUserName($wxUser->getNick())
            ->setCommunityID($wxUser->getCurrentCommunityID())
            ->setContentValue($userMessage)
            ->setVipNo($wxUser->getVipNo())
            ->setContentType($type)
            ->setRecordTime($currentTime)
            ->insert(true);

    }

}
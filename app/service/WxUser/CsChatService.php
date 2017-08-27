<?php

use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\HouseMemberType;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\CsChatRecord;
use MP\Model\Mp\ReocrdContentType;
use WBT\Business\Weixin\CsChatBusiness;
use MP\Model\Mp\MpUserConfigType;

class CsChatService extends Service
{
    public function replyCustomer()
    {
        $res = ['errno' => 0];
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $answer = App::getInstance()->request()->get('answer');
        $csID = App::getInstance()->request()->get('cs_id');
        $type = App::getInstance()->request()->get('type');
        $csWxUserID = App::getInstance()->request()->get('cs_wx_user_id');
        $answerMethod = App::getInstance()->request()->get('answer_method');
        if(!isset($answerMethod))
        {
            $answerMethod = "text";
        }
        $currentTime = date('Y-m-d H:i:s',time());
        $formatTime = '';
        if( "am"==date("a"))
        {
            $formatTime = '上午 '.date('H:i',time());
        }elseif("pm"==date("a"))
        {
            $formatTime = '下午 '.date('H:i',time());
        }
        $cs = CustomerSpecialist::fetchOneRow(['*'],[CustomerSpecialist::CUSTOMER_SPECIALIST_ID=>$csID]);
        $csColleague = CustomerSpecialist::fetchOneRow(['*'],[CustomerSpecialist::WX_USER_ID=>$csWxUserID]);
        $wxUser = WxUser::fetchOneRow(['*'],[WxUser::MP_USER_ID=>$mpUserId,WxUser::WX_USER_ID=>$wxUserId]);
        $hm = new HouseMember([HouseMember::MP_USER_ID=>$mpUserId,HouseMember::WX_USER_ID=>$wxUserId]);
        $csGroup = CustomerSpecialist::fetchColumn(CustomerSpecialist::WX_USER_ID,[CustomerSpecialist::VALID=>1,CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID=>$cs[CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID]]);
        $host =  ConfigBusiness::getHost();//获取主机名
        $csName = "";
        log_debug("type==============",$type);
        if('1'==$type)
        {
            $csName = $cs[CustomerSpecialist::NAME];
        }
        elseif('2'==$type)
        {
            $csName = $csColleague[CustomerSpecialist::NAME];
            $csID = $csColleague[CustomerSpecialist::CUSTOMER_SPECIALIST_ID];
        }

        $houseMember_status = "";
        if("业主"==HouseMemberType::getDisplayName($hm->getMemberType()))
        {
            $houseMember_status = "业主";
        }else
        {
            $houseMember_status = "住户";
        }
        $csChatRecord = new CsChatRecord();
        if(empty($answer))
        {
            $answer = "物业客服为您服务";
        }
        $csChatRecord->setMpUserID($mpUserId)
            ->setWxUserID($wxUserId)
            ->setWxUserName($wxUser[WxUser::NICK])
            ->setCommunityID($wxUser[WxUser::CURRENT_COMMUNITY_ID])
            ->setContentValue($answer)
            ->setVipNo($wxUser[WxUser::VIP_NO])
            ->setCsName($csName)
            ->setGroupName($hm->getCsGroupName())
            ->setCsGroupID($hm->getCurrentCsGroupID())
            ->setCsID($csID)
            ->setContentType($answerMethod)
            ->setRecordTime($currentTime)
            ->insert(true);
//客服回复为图片时
        if($answerMethod == "pic")
        {
            if(WxApiBusiness::sentImgMessage($mpUserId,$wxUserId,$answer))
            {
                $res = ['errno' => 0];
            }
            else
            {
                return $res = ['errno' => 1,'error' => "图片过大，请重新选择图片"];
            }
        }
//客服回复为文本时
        if($answerMethod == "text")
        {
            $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserId);
            $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
            //专属客服专员回复给业主   业主收到的信息 与 同组客服收到的信息  客服回复  业主收
            if($type==1)
            {
                //业主收到的信息内容
                $userMessage =  sprintf("您好，您的客服专员【%s】回复：\n\n%s\n回复时间：%s",$cs[CustomerSpecialist::NAME],$answer,$formatTime);
                if(WxApiBusiness::sentTextMessage($mpUserId,$wxUserId,$userMessage))
                {
                    $res = ['errno' => 0];
                }else
                {
                    return $res = ['errno' => 1,'error' => "客服专员回复内容发送失败"];
                }
                //同组其他客服人员收到的内容

                foreach($csGroup as $csWxID)
                {
                    if($cs[CustomerSpecialist::WX_USER_ID]!=$csWxID && isset($csWxID))
                    {
                        $url = sprintf("%s/wx_user/cs_chat_record/answer_table?type=2&wx_user_id=%s&mp_user_id=%s&cs_id=%s&cs_wx_user_id=%s&cs_group_id=%s#bottom-body",$host,$wxUserId,$mpUserId,$csID,$csWxID,$hm->getCurrentCsGroupID());
                        $otherUserMessage = "\\n$csName".":\\n".$answer."\\n回复".$wxUser[WxUser::NICK];
                        $remark = "\\n点击了解详情";
                        $template = array( 'touser' => $csWxID,
                            'template_id' => "$templateID",
                            'url' => $url,
                            'topcolor' => "#62c462",
                            'data'   => array('first' => array('value' => urlencode(""),
                                'color' =>"#222", ),
                                'keyword1' => array('value' => urlencode($otherUserMessage),'color' =>"#222", ),
                                'keyword2' => array('value' => urlencode($formatTime),
                                    'color' =>"#222", ),
                                'remark' => array('value' => urlencode("$remark") ,
                                    'color' =>"#222" ,))
                        );

                        WxApiBusiness::sentTemplateMessage($mpUserId,$template);
                    }
                }

            }
            else if($type==2) //同组客服回复给业主  客服发 业主收
            {
                //业主收到的信息内容

                if(\WBT\Business\Weixin\CsChatBusiness::checkWork($cs[CustomerSpecialist::WX_USER_ID]))
                {
                    $csColleagueMessage =  sprintf("您好，您的客服专员【%s】正在【忙碌】，由同事【%s】回复:\n\n%s\n回复时间：%s",$cs[CustomerSpecialist::NAME],$csColleague[CustomerSpecialist::NAME],$answer,$formatTime);
                }
                else
                {
                    $csColleagueMessage =  sprintf("您好，您的客服专员【%s】正在【休假】，由同事【%s】回复:\n\n%s\n回复时间：%s",$cs[CustomerSpecialist::NAME],$csColleague[CustomerSpecialist::NAME],$answer,$formatTime);
                }

                if(WxApiBusiness::sentTextMessage($mpUserId,$wxUserId,$csColleagueMessage))
                {
                    $res = ['errno' => 0];
                }else
                {
                    return $res = ['errno' => 1,'error' => "客服专员回复内容发送失败"];
                }

                foreach($csGroup as $csWxID)
                {
                    if($csColleague[CustomerSpecialist::WX_USER_ID]!=$csWxID && isset($csWxID))
                    {
                        $url = sprintf("%s/wx_user/cs_chat_record/answer_table?type=2&wx_user_id=%s&mp_user_id=%s&cs_id=%s&cs_wx_user_id=%s&cs_group_id=%s#bottom-body",$host,$wxUserId,$mpUserId,$csID,$csWxID,$hm->getCurrentCsGroupID());
                        $otherUserMessage = "\\n$csName".":\\n".$answer."\\n已帮助回复".$wxUser[WxUser::NICK];
                        $remark = "\\n点击了解详情";
                        $template = array( 'touser' => $csWxID,
                            'template_id' => "$templateID",
                            'url' => $url,
                            'topcolor' => "#62c462",
                            'data'   => array('first' => array('value' => urlencode(""),
                                'color' =>"#222", ),
                                'keyword1' => array('value' => urlencode($otherUserMessage),'color' =>"#222", ),
                                'keyword2' => array('value' => urlencode($formatTime),
                                    'color' =>"#222", ),
                                'remark' => array('value' => urlencode("$remark") ,
                                    'color' =>"#222" ,))
                        );

                        WxApiBusiness::sentTemplateMessage($mpUserId,$template);

                    }
                }
            }
        }

        //返回回复信息的内容和客服专员信息；客服专员信息由CsID决定
        $resAnswer = ['answer' => $answer];
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $csID]);
        $wxUserID = $cs->getWxUserID();
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $name = ['name' => $cs->getName()];
        $headPic = ['head' => $wxUser->getHeadPic()];
        $resTime = CsChatBusiness::getAnswerTime($currentTime,$csChatRecord->getCsChatRecordID(),$wxUserId);
        $resTime = explode("%",$resTime);
        $time = ['time' => $resTime[0]];
        $isShow = ['isShow' => $resTime[2]];
        $res = array_merge($res,$name,$headPic,$resAnswer,$time,$isShow);
        return $res;

    }
    //长微博回复
    public function replyCustomerCwb()
    {
        $res = ['errno' => 0];
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $answer = App::getInstance()->request()->get('answer');
        $csID = App::getInstance()->request()->get('cs_id');
        $answerMethod = App::getInstance()->request()->get('answer_method');
        if(!isset($answerMethod))
        {
            $answerMethod = "text";
        }

        $currentTime = date('Y-m-d H:i:s',time());

        $cs = CustomerSpecialist::fetchOneRow(['*'],[CustomerSpecialist::CUSTOMER_SPECIALIST_ID=>$csID]);

        $wxUser = WxUser::fetchOneRow(['*'],[WxUser::MP_USER_ID=>$mpUserId,WxUser::WX_USER_ID=>$wxUserId]);
        $csName = $cs[CustomerSpecialist::NAME];
        $csGroupID = $cs[CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID];
        $csGroup = new CustomerSpecialistGroup([CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $csGroupID]);
        if(empty($answer))
        {
            $answer = "物业客服为您服务";
        }
        $csChatRecord = new CsChatRecord();
        $csChatRecord->setMpUserID($mpUserId)
            ->setWxUserID($wxUserId)
            ->setWxUserName($wxUser[WxUser::NICK])
            ->setCommunityID($wxUser[WxUser::CURRENT_COMMUNITY_ID])
            ->setContentValue($answer)
            ->setVipNo($wxUser[WxUser::VIP_NO])
            ->setCsName($csName)
            ->setGroupName($csGroup->getGroupName())
            ->setCsGroupID($csGroupID)
            ->setCsID($csID)
            ->setContentType($answerMethod)
            ->setRecordTime($currentTime)
            ->insert(true);

        $userMessage =  $answer;
        if($answerMethod == "text")
        {
            if(WxApiBusiness::sentTextMessage($mpUserId,$wxUserId,$userMessage))
            {
                $res = ['errno' => 0];
            }else
            {
                return $res = ['errno' => 1,'error' => "客服专员回复内容发送失败"];
            }
        }

        if($answerMethod == "pic")
        {
            if(WxApiBusiness::sentImgMessage($mpUserId,$wxUserId,$userMessage))
            {
                $res = ['errno' => 0];
            }else
            {
                return $res = ['errno' => 1,'error' => "图片过大，请重新选择图片"];
            }
        }


        //返回回复信息的内容和客服专员信息；客服专员信息由CsID决定
        $resAnswer = ['answer' => $answer];
        $cs = new CustomerSpecialist([CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $csID]);
        $wxUserID = $cs->getWxUserID();
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $name = ['name' => $cs->getName()];
        $headPic = ['head' => $wxUser->getHeadPic()];
        $resTime = CsChatBusiness::getAnswerTime($currentTime,$csChatRecord->getCsChatRecordID(),$wxUserId);
        $resTime = explode("%",$resTime);
        $time = ['time' => $resTime[0]];
        $isShow = ['isShow' => $resTime[2]];
        $answerMethod = ['answer_method' => $answerMethod];
        $res = array_merge($res,$name,$headPic,$resAnswer,$time,$isShow,$answerMethod);
        return $res;

    }
    //长微博更新页面记录update_chat_record_cwb
    public function updateChatRecordCwb()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $userNewRecordId = App::getInstance()->request()->get('user_new_record_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        $head = $wxUser->getHeadPic();
        $expr = sprintf("`cs_chat_record_id`> '%s' and `cs_id` is null",$userNewRecordId);
        $con = new \Bluefin\Data\DbCondition($expr);
        $condition = [$con, 'wx_user_id' => $wxUserId];
        $ranking = [CsChatRecord::RECORD_TIME];
        $userNewChatRecord =  CsChatRecord::fetchRows(['*'],$condition,null, $ranking);

        if(empty($userNewChatRecord))
        {
            return ["user_new_chat_record_number" => 0];
        }
        else
        {
            foreach($userNewChatRecord as $key=>$value)
            {
                //获取时间字段
                $ret = CsChatBusiness::getAnswerTime($value[CsChatRecord::RECORD_TIME],$value[CsChatRecord::CS_CHAT_RECORD_ID],$value[CsChatRecord::WX_USER_ID]);
                $ret = explode("%",$ret);
                $userNewChatRecord[$key][CsChatRecord::RECORD_TIME] = $ret[0];
                $userNewChatRecord[$key]["head"] = $head;
            }
            return $userNewChatRecord;
        }


    }

}

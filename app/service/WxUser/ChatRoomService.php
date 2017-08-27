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
use MP\Model\Mp\ChatRoomRecord;
use MP\Model\Mp\ReocrdContentType;
use WBT\Business\Weixin\CsChatBusiness;
use MP\Model\Mp\MpUserConfigType;

class ChatRoomService extends Service
{
    public function reply()
    {
        $res = ['errno' => 0];
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id');
        $answer = App::getInstance()->request()->get('answer');
        $answerMethod = App::getInstance()->request()->get('answer_method');
        if(!isset($answerMethod))
        {
            $answerMethod = "text";
        }
        $currentTime = date('Y-m-d H:i:s',time());


        $wxUser = WxUser::fetchOneRow(['*'],[WxUser::MP_USER_ID=>$mpUserId,WxUser::WX_USER_ID=>$wxUserId]);

        $chatRoomRecord = new ChatRoomRecord();
        if(empty($answer))
        {
            $answer = "物业客服为您服务";
        }
        $chatRoomRecord->setMpUserID($mpUserId)
            ->setWxUserID($wxUserId)
            ->setWxUserName($wxUser[WxUser::NICK])
            ->setCommunityID($wxUser[WxUser::CURRENT_COMMUNITY_ID])
            ->setContentValue($answer)
            ->setVipNo($wxUser[WxUser::VIP_NO])
            ->setContentType($answerMethod)
            ->setRecordTime($currentTime)
            ->insert(true);

        //返回回复信息的内容和客服专员信息；客服专员信息由CsID决定
        $resAnswer = ['answer' => $answer];
        $name = ['name' => $wxUser[WxUser::NICK]];
        $headPic = ['head' => $wxUser[WxUser::HEAD_PIC]];
        $resTime = CsChatBusiness::getAnswerTimeRoom($currentTime,$chatRoomRecord->getChatRoomRecordID(),$wxUserId);
        $resTime = explode("%",$resTime);
        $time = ['time' => $resTime[0]];
        $isShow = ['isShow' => $resTime[2]];
        $answerMethod = ['answer_method' => $answerMethod];
        $res = array_merge($res,$name,$headPic,$resAnswer,$time,$isShow,$answerMethod);
        return $res;

    }

    public function updateChatRecord()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $userNewRecordId = App::getInstance()->request()->get('user_new_record_id');

        $expr = sprintf("`chat_room_record_id`> '%s'",$userNewRecordId);
        $con = new \Bluefin\Data\DbCondition($expr);
        //$condition = [$con, 'wx_user_id' => $wxUserId];
        $condition = [$con];
        $ranking = [ChatRoomRecord::RECORD_TIME];
        $userNewChatRecord =  ChatRoomRecord::fetchRows(['*'],$condition,null, $ranking);

        if(empty($userNewChatRecord))
        {
            return ["user_new_chat_record_number" => 0];
        }
        else
        {
            foreach($userNewChatRecord as $key=>$value)
            {
                //获取时间字段
                $ret = CsChatBusiness::getAnswerTimeRoom($value[ChatRoomRecord::RECORD_TIME],$value[ChatRoomRecord::CHAT_ROOM_RECORD_ID],$value[ChatRoomRecord::WX_USER_ID]);
                $ret = explode("%",$ret);
                $userNewChatRecord[$key][ChatRoomRecord::RECORD_TIME] = $ret[0];
                $wxUser = new WxUser([WxUser::WX_USER_ID => $value[ChatRoomRecord::WX_USER_ID]]);
                $head = $wxUser->getHeadPic();
                $userNewChatRecord[$key]["head"] = $head;
            }
            return $userNewChatRecord;
        }


    }


}

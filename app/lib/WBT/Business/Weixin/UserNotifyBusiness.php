<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\UserNotify;
use MP\Model\Mp\UserNotifySendStatus;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\MpUser;
class UserNotifyBusiness extends BaseBusiness
{
    public static function getList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return UserNotify::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getCount(array $condition)
    {
        return UserNotify::fetchCount($condition);
    }

    public static function insert( $data )
    {
        $obj = new UserNotify();
        $createTime = date('Y-m-d H:i:s',time());
        $data[UserNotify::CREATE_TIME] = $createTime;
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function update( $id, $data )
    {
        $obj = new UserNotify([ UserNotify::USER_NOTIFY_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find UserNotify($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function delete( $id )
    {
        $obj = new UserNotify([ UserNotify::USER_NOTIFY_ID => $id ]);
        $obj->delete();
        return [ 'errno' => 0 ];
    }

    public static function copy( $id,$from  )
    {
        //复制模板消息内容
        $userNotify = new UserNotify([ UserNotify::USER_NOTIFY_ID => $id ]);
        $data = [];
        $data[UserNotify::MP_USER_ID] = $userNotify->getMpUserID();
        $data[UserNotify::COMMUNITY_ID] = $userNotify->getCommunityID();
        if($from == "mp")
        {
            $data[UserNotify::SEND_RANGE] = UserNotifySendRangeType::SEND_TOTAL;
        }
        elseif($from == "app_mp")
        {
            $data[UserNotify::SEND_RANGE] = UserNotifySendRangeType::SEND_TO_WHOLE_APP;
        }
        elseif($from == "app_c")
        {
            $data[UserNotify::SEND_RANGE] = UserNotifySendRangeType::SEND_APP_COMMUNITY;
        }
        else
        {
            $data[UserNotify::SEND_RANGE] = UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY;
        }

        $data[UserNotify::TITLE] = $userNotify->getTitle();
        $data[UserNotify::CONTENT_URL] = $userNotify->getContentUrl();
        $data[UserNotify::INFOID] = $userNotify->getInfoid();
        $data[UserNotify::DESCRIPTION] = $userNotify->getDescription();
        $data[UserNotify::SEND_TYPE] = $from;
        //插入数据库
        $userNotify = new UserNotify();
        $createTime =time();
        $data[UserNotify::CREATE_TIME] = $createTime;
        $userNotify->apply( $data );
        $userNotify->insert();
        return [ 'errno' => 0 ];
    }

//取出wx_user_id
    public static function getWxUserId( $sendType,$communityID ,$sendNo,$mpUserID)
    {
        if($sendType == UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY)
        {
            $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
            $mpUserType = $mpUser->getMpUserType();
            if($mpUserType == 1)
            {
                $wxUserID = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::CURRENT_COMMUNITY_ID => $communityID]);
            }
            else
            {
                $wxUserID = WxUser::fetchColumn([WxUser::WX_USER_ID]);
            }
            log_debug("全小区============================",$wxUserID);
            return $wxUserID;
        }
        else
        {
            $groupSendNos = explode("\n",$sendNo);
            $wxUserIDs = [];
            foreach($groupSendNos as $value)
            {
                if(!empty($value))
                {
                    $groupSendNo = explode(",",$value);
                    $groupSendNoStart = (string)$groupSendNo[0];
                    $groupSendNoEnd = (string)$groupSendNo[1];
                    $houseMember = HouseMember::fetchRows(['*'],[UserNotify::COMMUNITY_ID => $communityID]);
                    foreach($houseMember as $houseValue)
                    {
                        $houseNo = (string)$houseValue[HouseMember::HOUSE_NO];
                        if($houseNo >= $groupSendNoStart and $houseNo <= $groupSendNoEnd)
                        {
                            $wxUserIDs[] = $houseValue[HouseMember::WX_USER_ID];//取出wxUserID
                        }
                    }
                }
            }

            log_debug("提取============================",$wxUserIDs);
            //对wxUserIDs进行处理
            $wxUserID = [];
            foreach($wxUserIDs as $value)
            {
                if(!empty($value))
                {
                    array_push($wxUserID,$value);
                }
            }
            log_debug("最后============================",$wxUserID);
            return $wxUserID;
        }


    }
}
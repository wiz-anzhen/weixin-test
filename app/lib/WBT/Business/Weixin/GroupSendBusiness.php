<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\GroupSend;
use MP\Model\Mp\GroupSendContentType;
use MP\Model\Mp\GroupSendItem;
use MP\Model\Mp\GroupSendRangeType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\HouseMemberBusiness;
use MP\Model\Mp\MpUser;

class GroupSendBusiness extends BaseBusiness
{
    public static function getGroupSendList( array $condition, array &$paging = null, $ranking,
                                         array $outputColumns = null )
    {
        return GroupSend::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function getGroupSendItemList( array $condition, array &$paging = null, $ranking,
                                             array $outputColumns = null )
    {
        return GroupSendItem::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    public static function insert( $data )
    {
        $obj = new GroupSend();
        $createTime = date('Y-m-d H:i:s',time());
        $data[GroupSend::CREATE_TIME] = $createTime;
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
        $obj = new GroupSend([ GroupSend::GROUP_SEND_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find GroupSend($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function delete( $id )
    {
        $obj = new GroupSend([ GroupSend::GROUP_SEND_ID => $id ]);
        $obj->delete();
        $groupSendItem = new GroupSendItem();
        $groupSendItem->delete([GroupSendItem::GROUP_SEND_ID => $id]);
        return [ 'errno' => 0 ];
    }


    public static function insertContent( $data )
    {
        $obj = new GroupSendItem();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }

    public static function updateContent( $id, $data )
    {
        $obj = new GroupSendItem([ GroupSendItem::GROUP_SEND_ITEM_ID => $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find GroupSendItem($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }

        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    public static function deleteContent( $id )
    {
        $obj = new GroupSendItem([ GroupSendItem::GROUP_SEND_ITEM_ID=> $id ]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find GroupSendItem($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->delete();
        return [ 'errno' => 0 ];
    }

    public static function copy(  $id,$from  )
    {
        //复制groupSend内容
        $groupSend = new GroupSend([GroupSend::GROUP_SEND_ID => $id]);
        $data = [];
        $data[GroupSend::MP_USER_ID] = $groupSend->getMpUserID();
        $data[GroupSend::COMMUNITY_ID] = $groupSend->getCommunityID();
        $data[GroupSend::TITLE] = $groupSend->getTitle();
        $data[GroupSend::CONTENT_TYPE] = $groupSend->getContentType();
        $data[GroupSend::CONTENT_VALUE] = $groupSend->getContentValue();
        $data[GroupSend::SEND_TYPE] = $from;
        if($from == "mp")
        {
            $data[GroupSend::GROUP_SEND_RANGE] = GroupSendRangeType::SEND_TO_MP_USER;
        }
        //插入数据库
        $groupSend = new GroupSend();
        $createTime =time();
        $data[GroupSend::CREATE_TIME] = $createTime;
        $groupSend->apply( $data );
        $groupSend->insert();

        $groupSendID = $groupSend->getGroupSendID();
        $groupSendItem = GroupSendItem::fetchRows(['*'],[GroupSendItem::GROUP_SEND_ID => $id]);
        //复制groupSendItem项目内容

        foreach($groupSendItem as $value)
        {
            //插入数据库
            unset($value[GroupSendItem::GROUP_SEND_ITEM_ID]);
            $value[GroupSendItem::GROUP_SEND_ID] = $groupSendID;
            self::insertContent($value);
        }
        return [ 'errno' => 0 ];
    }
//取出wx_user_id
    public static function getWxUserId( $groupSendType,$communityID ,$groupSendNo,$mpUserID)
    {
        if($groupSendType == GroupSendRangeType::SEND_TO_WHOLE_COMMUNITY)
        {
            $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
            $mpUserType = $mpUser->getMpUserType();
            $wxUserID = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::CURRENT_COMMUNITY_ID => $communityID]);

            log_debug("全小区============================",$wxUserID);
            return $wxUserID;
        }
        else
        {
            $groupSendNos = explode("\n",$groupSendNo);
            $wxUserIDs = [];
            foreach($groupSendNos as $value)
            {
                if(!empty($value))
                {
                    $groupSendNo = explode(",",$value);
                    $groupSendNoStart = $groupSendNo[0];
                    $groupSendNoEnd = $groupSendNo[1];

                    $houseMember = HouseMember::fetchRows(['*'],[HouseMember::COMMUNITY_ID => $communityID]);
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
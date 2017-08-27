<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 15-3-11
 * Time: 下午2:33
 */
use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\Cart;
use MP\Model\Mp\CartDetail;
use WBT\Business\Weixin\CartDetailBusiness;
use WBT\Business\Weixin\OrderBusiness;
use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\Product;
use MP\Model\Mp\Category;
use MP\Model\Mp\OrderDetail;
use MP\Model\Mp\ProcurementOrderChangeLog;
use MP\Model\Mp\ProcurementOrderStatus;

use MP\Model\Mp\Store;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\Community;
use MP\Model\Mp\UserNotifySendRangeType;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\Weixin\UserNotifyBusiness;
use MP\Model\Mp\Part;
use MP\Model\Mp\ProcurementOrderDetail;
use MP\Model\Mp\ProcurementCart;
use MP\Model\Mp\ProcurementCartDetail;
class SupplyService extends Service
{
    public function sendSupply()
    {
        $res = ['errno' => 0];
        $wxUserID = App::getInstance()->request()->get('wx_user_id');
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserID]);
        $chefName = $wxUser->getNick();
        $orderID = App::getInstance()->request()->get('order_id');
        $order = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);

        $type = App::getInstance()->request()->get('type');
        $store = new Store([Store::STORE_ID => $order->getStoreID()]);
        $order->setStatus($type)->update();

        $mpUserID  = $order->getMpUserID();
        $communityID = "";
        $host =  ConfigBusiness::getHost();//获取主机名
        $mpUserConfig= ConfigBusiness::mpUserConfig($mpUserID);
        $templateID = $mpUserConfig[MpUserConfigType::TEMPLATE_MESSAGE_NOTIFY_ID];//通知模板id
// 处理发送确认订货单通知supply_send
        if($type == "supply_send")
        {
            $communityID = $order->getCommunityID();
            $community = new Community([Community::COMMUNITY_ID => $communityID]);
            $communityBoundID = $order->getBoundCommunityID();
            $communityBound = new Community([Community::COMMUNITY_ID => $communityBoundID]);
            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=订货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $first =$community->getName(). "确认订货单通知";
            $nick = $communityBound->getName();
            $content = "确认通知";
            $sendType = "order";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("supply_verify")
                ->setStatusAfter($type)
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
        }
        // 处理发送确认订货单通知supply_send
        if($type == "examine")
        {
            $communityID = $order->getCommunityID();
            $community = new Community([Community::COMMUNITY_ID => $communityID]);
            $communityBoundID = $order->getBoundCommunityID();
            $communityBound = new Community([Community::COMMUNITY_ID => $communityBoundID]);
            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=发货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $first =$community->getName(). "确认发货单通知";
            $nick = $communityBound->getName();
            $content = "确认通知";
            $sendType = "order";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("supply_send")
                ->setStatusAfter($type)
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
        }
// 处理发送催促通知——等待供应商确认
        if($type == "finished")
        {
            $communityID = $order->getCommunityID();
            $community = new Community([Community::COMMUNITY_ID => $communityID]);
            $communityBoundID = $order->getBoundCommunityID();
            $communityBound = new Community([Community::COMMUNITY_ID => $communityBoundID]);
            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=验货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $first =$community->getName(). "验货单已确认";
            $nick = $communityBound->getName();
            $content = "确认通知";
            $sendType = "examine";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("supply_examine")
                ->setStatusAfter($type)
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
        }
// 处理发送催促通知——退货进度
        if($type == "refund_finished")
        {
            $communityID = $order->getCommunityID();
            $community = new Community([Community::COMMUNITY_ID => $communityID]);
            $communityBoundID = $order->getBoundCommunityID();
            $communityBound = new Community([Community::COMMUNITY_ID => $communityBoundID]);
            $url = sprintf("%s/wx_user/procurement/detail?mp_user_id=%s&community_id=%s&order_id=%s&store_title=%s&template_type=退货单",$host,$mpUserID,$communityID,$orderID,$store->getTitle());
            $first =$community->getName(). "退货单已确认";
            $nick = $communityBound->getName();
            $content = "确认通知";
            $sendType = "refund";
            //添加order-change-log
            $orderChangeLog = new ProcurementOrderChangeLog();
            $orderChangeLog->setOrderID($orderID)
                ->setStatusBefore("refund")
                ->setStatusAfter($type)
                ->setOperator($wxUser->getNick())
                ->setChangeTime(date('Y-m-d H:i:s'))
                ->setComment('无')
                ->insert();
        }

        //发送模板消息
        $wxUserIDs =  UserNotifyBusiness::getWxUserId(UserNotifySendRangeType::SEND_TO_WHOLE_COMMUNITY,$communityID,"",$mpUserID);

        $newWxUserIDs = [];
        foreach($wxUserIDs as $value)
        {
            $house = new HouseMember([HouseMember::WX_USER_ID => $value,HouseMember::COMMUNITY_ID => $communityID]);
            $housePower = $house->getProcurementPowerType();
            $housePower = explode(",",$housePower);
            if(strict_in_array($sendType,$housePower))
            {
                $newWxUserIDs[] = $value;
            }
            if($sendType == "refund" and $house->getMemberType() == "chef")
            {
                $newWxUserIDs[] = $value;
            }
        }


        foreach($newWxUserIDs as $value)
        {
            $template = array( 'touser' => $value,
                'template_id' => "$templateID",
                'url' => $url,
                'topcolor' => "#62c462",
                'data'   => array(
                    'first' => array('value' => urlencode("供应商:".$nick),'color' =>"#cf3134", ),
                    'keyword1' => array('value' => urlencode("$first"),'color' =>"#222", ),
                    'keyword2' => array('value' => urlencode($content),'color' =>"#222", ),
                    'remark' => array('value' => urlencode("点击查看") ,
                        'color' =>"#222" ,))
            );

            WxApiBusiness::sentTemplateMessage($mpUserID,$template);
        }

        return ['errno' => 0];


    }
}
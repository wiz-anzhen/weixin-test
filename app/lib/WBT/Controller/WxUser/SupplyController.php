<?php

namespace WBT\Controller\WxUser;

use Bluefin\Controller;
use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\Store;
use MP\Model\Mp\Community;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\OrderDetail;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\Product;
use MP\Model\Mp\ProductUnitType;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\OrderBusiness;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\CommunityType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\ProductComment;
use MP\Model\Mp\Category;
use WBT\Business\Weixin\StoreBusiness;
use MP\Model\Mp\Cart;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\CartDetail;
use WBT\Business\Weixin\CartDetailBusiness;
use Common\Helper\BaseController;
use MP\Model\Mp\ProcurementOrderStatus;
use MP\Model\Mp\ProcurementOrderChangeDetail;
use MP\Model\Mp\Part;
use MP\Model\Mp\ProcurementOrderDetail;
use MP\Model\Mp\ProcurementCart;
use MP\Model\Mp\ProcurementCartDetail;
class SupplyController extends WxUserControllerBase
{
    //确认订货单
    public function orderVerifyAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'order')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        $this->_view->set( 'community_id', $communityID );
        $storeData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => '0']);
        foreach($storeData as $key=>$value)
        {
            $procurementOrder = new ProcurementOrder([
                ProcurementOrder::BOUND_COMMUNITY_ID=>$value[Store::COMMUNITY_ID],
                ProcurementOrder::BOUND_STORE_ID=>$value[Store::STORE_ID],
                ProcurementOrder::STATUS=>'supply_verify'
            ]);
            if($procurementOrder->isEmpty())
            {
                $storeData[$key]['new'] = false;
            }
            else
            {
                $storeData[$key]['new'] = true;
            }
        }
        log_debug('store==========',$storeData);
        $this->_view->set( 'store_data', $storeData );
    }
    //订单列表
    public function orderListAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set( 'store_id', $storeID );
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set( 'community_id', $communityID );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $boundCommunityId = $this->_request->get( 'bound_community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$boundCommunityId]);
        $this->_view->set( 'store_name', $community->getName() );
        $orderList = ProcurementOrder::fetchRows(['*'],[ProcurementOrder::BOUND_COMMUNITY_ID=>$communityID,
            ProcurementOrder::BOUND_STORE_ID=>$storeID,
            ProcurementOrder::STATUS=>'supply_verify'], $grouping = null,$ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug('orderList=======',$orderList);
        if(count($orderList) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        $this->_view->set( 'order_list', $orderList );
    }
    //订单明细
    public function orderDetailAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $this->_view->set( 'wx_user_id', $wxUserID );
        $orderID = $this->_request->get( 'order_id' );

        $procurementOrder = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        $this->_view->set( 'procurement_order', $procurementOrder );

        $this->_view->set( 'order_id', $orderID );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $communityId = $this->_request->get( 'community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$communityId]);
        $this->_view->set( 'store_name', $community->getName() );

        $totalPrice = $procurementOrder->getTotalPrice();
        $this->_view->set( 'total_price', $totalPrice );
        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );
    }
    //确认完成
    public function orderSubmitAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $orderID = $this->_request->get( 'order_id' );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );

    }

    //确认验货单
    public function examineVerifyAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'examine')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        $this->_view->set( 'community_id', $communityID );
        $storeData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => '0']);
        foreach($storeData as $key=>$value)
        {
            $procurementOrder = new ProcurementOrder([
                ProcurementOrder::BOUND_COMMUNITY_ID=>$value[Store::COMMUNITY_ID],
                ProcurementOrder::BOUND_STORE_ID=>$value[Store::STORE_ID],
                ProcurementOrder::STATUS=>'supply_examine'
            ]);
            if($procurementOrder->isEmpty())
            {
                $storeData[$key]['new'] = false;
            }
            else
            {
                $storeData[$key]['new'] = true;
            }
        }
        log_debug('store==========',$storeData);
        $this->_view->set( 'store_data', $storeData );
    }
    //验货单列表
    public function examineListAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set( 'store_id', $storeID );
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set( 'community_id', $communityID );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $boundCommunityId = $this->_request->get( 'bound_community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$boundCommunityId]);
        $this->_view->set( 'store_name', $community->getName() );
        $examineList = ProcurementOrder::fetchRows(['*'],[
            ProcurementOrder::BOUND_COMMUNITY_ID=>$communityID,
            ProcurementOrder::BOUND_STORE_ID=>$storeID,
            ProcurementOrder::STATUS=>'supply_examine'
        ],$grouping = null,$ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug('examineList=======',$examineList);
        if(count($examineList) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        $this->_view->set( 'examine_list', $examineList );
    }

    //验货单明细
    public function examineDetailAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $fromType = $this->_request->get( 'from_type' );
        $this->_view->set( 'from_type', $fromType );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $this->_view->set( 'wx_user_id', $wxUserID );
        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $procurementOrder = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        $this->_view->set( 'procurement_order', $procurementOrder );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $communityId = $this->_request->get( 'community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$communityId]);
        $this->_view->set( 'store_name', $community->getName() );

        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }
        log_debug('22222222222222222222222222==============',$orderDetailDataProgress);

        $chefCountTimes = ProcurementOrderChangeDetail::fetchColumn([ProcurementOrderChangeDetail::CHEF_COUNT],[ProcurementOrderChangeDetail::ORDER_ID => $orderID]);
        rsort($chefCountTimes);
        if(empty($chefCountTimes[0]))
        {
            //厨师长确认后的订单
            $chefCountTime = 1;
            $examineDetailChange = ProcurementOrderChangeDetail::fetchRows(['*'],[OrderDetail::ORDER_ID => $orderID,ProcurementOrderChangeDetail::STATUS => "supply_verify"]);
        }
        else
        {//厨师长确认后，订货员再次修改的记录
            $chefCountTime = $chefCountTimes[0];
            $examineDetailChange = ProcurementOrderChangeDetail::fetchRows(['*'],[OrderDetail::ORDER_ID => $orderID,ProcurementOrderChangeDetail::STATUS => "examine",ProcurementOrderChangeDetail::CHEF_COUNT => $chefCountTime]);
        }
       //记录总价，单位的补充
        foreach($examineDetailChange as $key=> $value)
        {
            $examineDetailChange[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $examineDetailChange[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $examineDetailChange[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$examineDetailChange[$key]['product_unit_new']);
            $examineDetailChange[$key]['product_unit_name'] = $dataProgress[1];
        }
        log_debug('33333333333333333333333333333333==============',$examineDetailChange);

        $orderDetailDataProgressChange = [];
        //获取档口id
        $partIDsChange = [];
        foreach($examineDetailChange as $key => $value)
        {
            $partIDsChange[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDsChange = array_unique($partIDsChange);
        $partIDProgress = [];
        foreach($partIDsChange as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($examineDetailChange as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgressChange[$k]['content'][] = $value;
                    $orderDetailDataProgressChange[$k]['part'] = $v['title'];
                    $orderDetailDataProgressChange[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgressChange[$k]['price'] += $value['total_price'];
                }
            }
        }
        //找出前后变化的档口差异数组
        $diff = array_diff($partIDsChange,$partIDs);
        //验货前后的比较
        foreach($orderDetailDataProgressChange as $key => $value)
        {
            foreach($orderDetailDataProgress as $k => $v)
            {//如果此档口在差异数组里，则全部变成更改过，当前值默认为0
                if(strict_in_array($value['part_id'],$diff))
                {
                    $orderDetailDataProgressChange[$key]['price'] = 0;
                    foreach($value['content'] as $ck => $cv)
                    {
                        $orderDetailDataProgressChange[$key]['content'][$ck]["current_count"] = "0";
                        $orderDetailDataProgressChange[$key]['content'][$ck]["change"] = "yes";
                    }
                }
                elseif($value['part_id'] == $v['part_id'])
                {//如果此档口不在差异数组里，逐个比较
                    $orderDetailDataProgressChange[$key]['price'] = $v['price'];
                    foreach($value['content'] as $ck => $cv)
                    {
                        $orderDetailDataProgressChange[$key]['content'][$ck]["current_count"] = "0";
                        $orderDetailDataProgressChange[$key]['content'][$ck]["change"] = "yes";
                        foreach($v['content'] as $ok => $ov)
                        {
                            if($ov[ProcurementOrderChangeDetail::PRODUCT_ID] == $cv[ProcurementOrderChangeDetail::PRODUCT_ID])
                            {
                                $orderDetailDataProgressChange[$key]['content'][$ck]["current_count"] = $ov[ProcurementOrderChangeDetail::COUNT];
                                if($ov[ProcurementOrderChangeDetail::COUNT] == $cv[ProcurementOrderChangeDetail::COUNT])
                                {
                                    $orderDetailDataProgressChange[$key]['content'][$ck]["change"] = "no";
                                }
                            }
                        }
                    }
                }

            }
        }
        log_debug('66666666666666666666666666666666664444444444444==============',$orderDetailDataProgressChange);

        if(count($examineDetailChange) == 0)
        {
            $this->_view->set( 'change', "no" );
            $this->_view->set( 'examine_detail', $orderDetailDataProgress );
        }
        else
        {
            $this->_view->set( 'change', "yes" );
            $this->_view->set( 'examine_detail', $orderDetailDataProgressChange );
        }

        $totalPrice = $procurementOrder->getTotalPrice();
        $this->_view->set( 'total_price', $totalPrice );
    }
    //确认完成
    public function examineSubmitAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $orderID = $this->_request->get( 'order_id' );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );

    }

    //确认退货单
    public function returnVerifyAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'refund')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        $this->_view->set( 'community_id', $communityID );
        $returnData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => '0']);

        //
        foreach($returnData as $key=>$value)
        {
            $procurementOrder = new ProcurementOrder([
                ProcurementOrder::BOUND_COMMUNITY_ID=>$value[Store::COMMUNITY_ID],
                ProcurementOrder::BOUND_STORE_ID=>$value[Store::STORE_ID],
                ProcurementOrder::STATUS=>'refund'
            ]);
            if($procurementOrder->isEmpty())
            {
                $returnData[$key]['new'] = false;
            }
            else
            {
                $returnData[$key]['new'] = true;
            }
        }
        log_debug('returnData==========',$returnData);
        $this->_view->set( 'return_data', $returnData );
    }
    //退货列表
    public function returnListAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $this->_view->set( 'wx_user_id', $wxUserID );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set( 'store_id', $storeID );
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set( 'community_id', $communityID );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $boundCommunityId = $this->_request->get( 'bound_community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$boundCommunityId]);
        $this->_view->set( 'store_name', $community->getName() );
        $exprWx = sprintf("`status` in ('%s','%s')",'refund','refund_finished');
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [$con,ProcurementOrder::BOUND_COMMUNITY_ID=>$communityID,
            ProcurementOrder::BOUND_STORE_ID => $storeID];
        $returnList = ProcurementOrder::fetchRows(['*'],$condition,$grouping = null,$ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        foreach($returnList as $key=>$value)
        {
            $orderDetail = new ProcurementOrderDetail([ProcurementOrderDetail::ORDER_ID => $value[ProcurementOrder::ORDER_ID]]);
            $part = new Part([Part::PART_ID => $orderDetail->getPartID()]);
            $returnList[$key]['part_title'] = $part->getTitle();
            $returnList[$key]['detail_title'] = $orderDetail->getTitle();
            $returnList[$key]['detail_price'] = $orderDetail->getPrice();
            $returnList[$key]['detail_count'] = $orderDetail->getCount();
            $returnList[$key]['detail_total'] = ($orderDetail->getPrice())*($orderDetail->getCount());
            $returnList[$key][Product::PRODUCT_UNIT] = ProductUnitType::getDisplayName($orderDetail->getProductUnit());
        }
        log_debug('returnList=======',$returnList);
        if(count($returnList) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        $this->_view->set( 'return_list', $returnList );
    }
    //退货明细
    public function returnDetailAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $orderID = $this->_request->get( 'order_id' );
        $procurementOrder = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        $this->_view->set( 'procurement_order', $procurementOrder );

        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        //$orderDetail = new ProcurementOrder([ProcurementOrder::ORDER_ID=>$orderID]);
        $orderDetail = ProcurementOrder::fetchOneRow(['*'],[ProcurementOrder::ORDER_ID=>$orderID]);

        log_debug('order_detail============'.$orderID);

        $community = new Community([Community::COMMUNITY_ID => $orderDetail[ProcurementOrder::COMMUNITY_ID]]);
        $orderDetail['community_name'] = $community->getName();
        $detail = new ProcurementOrderDetail([ProcurementOrderDetail::ORDER_ID=>$orderID]);
        $partID = $detail->getPartID();
        $part = new Part([Part::PART_ID => $partID]);
        $orderDetail['title'] = $detail->getTitle();
        $orderDetail['price'] = $detail->getPrice();
        $orderDetail['count'] = $detail->getCount();
        $orderDetail['total'] = ($detail->getPrice())*($detail->getCount());
        $orderDetail['part'] = $part->getTitle();

        $orderDetail[Product::PRODUCT_UNIT] = ProductUnitType::getDisplayName($detail->getProductUnit());

        $boundOrderDetail = new ProcurementOrder([ProcurementOrder::ORDER_ID=>$orderDetail[ProcurementOrder::REFUND_ORDER_ID]]);
        $orderDetail['order_time'] = $boundOrderDetail->getCreateTime();
        $orderDetail['return_time'] = $orderDetail[ProcurementOrder::CREATE_TIME];
        log_debug('order_detail============',$orderDetail);
        $this->_view->set('order_detail',$orderDetail);
    }

    //退货成功
    public function returnSubmitAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $orderID = $this->_request->get( 'order_id' );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );

    }

    // 单个餐厅经理查看订单

    public function singleMonthAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());


        $month    = $this->_request->get( 'month' );
        if(empty($month))
        {
            $lastMonth = strtotime("-1 month");
            $month = date("Y-m",$lastMonth);
        }
        $this->_view->set( 'month', $month );


        $communityID = $wxUser->getCurrentCommunityID();
        $condition =[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => "0"];
        $storeData = Store::fetchRows(['*'],$condition);
        $restaurantTotalPrice = "";
        $totalPriceProgress = [];
        foreach($storeData as $sk => $sv)
        {
            $orderTimeStart = $month."-01";
            $orderTimeEnd = $month."-31";

            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            log_debug("==========================".$orderTimeStart.$orderTimeEnd);
            $con = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);

            $orderData = ProcurementOrder::fetchColumn([ProcurementOrder::TOTAL_PRICE],[ProcurementOrder::BOUND_STORE_ID => $sv[Store::STORE_ID],ProcurementOrder::STATUS => "finished",$con]);

            $totalPrice = array_sum($orderData);
            $totalPriceProgress[] = $totalPrice;
            $restaurantTotalPrice += $totalPrice;
            $storeData[$sk]['supply_total_price'] = $totalPrice;

        }


        if($restaurantTotalPrice == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'boss_total_price', $restaurantTotalPrice );
        $totalPriceProgress = array_unique($totalPriceProgress);
        rsort($totalPriceProgress);
        $storeDataProgress = [];
        foreach($totalPriceProgress as $key => $value)
        {
            foreach($storeData as $k => $v)
            {
                if($value == $v['supply_total_price'])
                {
                    $storeDataProgress[] = $v;
                }
            }
        }
        $this->_view->set( "restaurant_data", $storeDataProgress );
    }

    public function singleMonthOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $month    = $this->_request->get( 'month' );
        $storeID    = $this->_request->get( 'store_id' );


        $this->_view->set('month',$month);
        $this->_view->set('store_id',$storeID);
        $orderTimeStart = $month."-01";
        $orderTimeEnd = $month."-31";
        $condition = [];
        if(!empty($orderTimeStart) && !empty($orderTimeEnd))
        {
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }

        if(!empty($storeID))
        {
            $condition[ProcurementOrder::BOUND_STORE_ID] = $storeID;
        }
        $condition[ProcurementOrder::STATUS] = "finished";

        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }


    public function managerDetailAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $orderID = $this->_request->get( 'order_id' );
        $this->_view->set( 'order_id', $orderID );

        $condition = [ProcurementOrder::ORDER_ID => $orderID];

        $orderData = new ProcurementOrder($condition);
        $this->_view->set( 'order_data', $orderData );

        $storeID = $orderData->getStoreID();
        $store = new Store([Store::STORE_ID => $storeID]);
        $storeTitle = $store->getTitle();
        $this->_view->set( 'store_title', $storeTitle );
        $storeBoundId = $orderData->getBoundStoreID();
        $storeBound = new Store([Store::STORE_ID => $storeBoundId]);
        $this->_view->set( 'store_bound_title', $storeBound->getTitle() );

        $orderDetailData = OrderDetail::fetchRows(["*"],[OrderDetail::ORDER_ID => $orderID]);
        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][OrderDetail::COUNT] = $value[OrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[OrderDetail::COUNT]*$value[OrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[OrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        log_debug("===========================",$orderDetailData);
        $this->_view->set( 'order_detail_data', $orderDetailData );

    }

    // 单个餐厅经理查看订单

    public function singleOrderAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $supplyID= $status = $orderTimeStart = $orderTimeEnd = null;


        $supplyID    = $this->_request->get( 'supply_id' );
        $status  = $this->_request->get( 'status' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');


        $this->_view->set('supply_id',$supplyID);
        $this->_view->set('status', $status);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);

        $condition = [];
        $condition[ProcurementOrder::MP_USER_ID] = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $monthBefore = strtotime("-2 month");
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [];


        $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => "0"]);
        $this->_view->set( "supply_data", $supplyData );
        $statusDataAll = ProcurementOrderStatus::getDictionary();
        $statusData = [];
        foreach($statusDataAll as $key  => $value)
        {
            if($key != "none")
            {
                $statusData[$key] = $value;
            }

        }
        $this->_view->set( "status_data", $statusData);

        $condition[ProcurementOrder::BOUND_COMMUNITY_ID] = $communityID;

        if (!empty($supplyID))
        {
            $condition[ProcurementOrder::BOUND_STORE_ID] = $supplyID;
        }


        if(!empty($orderTimeStart))
        {
            if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
            }
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        else
        {
            $condition[] = $con;
        }

        if(!empty($status))
        {
            $condition[ProcurementOrder::STATUS] = $status;
        }


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }

    // 老板查看订单

    public function singleSelectAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $community = new Community([Community::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $communityType = $community->getCommunityType();

        $housePower = $house->getMemberType();

        if ($housePower != "manager")
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user_id', $wxUser->getWxUserID());

        $supplyID= $status = $orderTimeStart = $orderTimeEnd = null;


        $supplyID    = $this->_request->get( 'supply_id' );
        $status  = $this->_request->get( 'status' );
        $orderTimeStart = $this->_request->get('order_time_start');
        $orderTimeEnd = $this->_request->get('order_time_end');


        $this->_view->set('supply_id',$supplyID);
        $this->_view->set('status', $status);
        $this->_view->set("o_time_start",$orderTimeStart);
        $this->_view->set("o_time_end",$orderTimeEnd);

        $condition = [];
        $condition[ProcurementOrder::MP_USER_ID] = $wxUser->getMpUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $monthBefore = strtotime("-2 month");
        $monthCurrentTwo = date("Y-m-d",$monthBefore);
        $monthCurrent = date("Y-m-d");
        $this->_view->set("monthCurrentTwo",$monthCurrentTwo);
        $this->_view->set("monthCurrent",$monthCurrent);
        $exprWx = sprintf("`create_time` >= '%s'",$monthBefore);
        $con = new \Bluefin\Data\DbCondition($exprWx);
        $condition = [];


        $supplyData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => "0"]);
        $this->_view->set( "supply_data", $supplyData );
        $statusDataAll = ProcurementOrderStatus::getDictionary();
        $statusData = [];
        foreach($statusDataAll as $key  => $value)
        {
            if($key != "none")
            {
                $statusData[$key] = $value;
            }

        }
        $this->_view->set( "status_data", $statusData);

        $condition[ProcurementOrder::BOUND_COMMUNITY_ID] = $communityID;

        if (!empty($supplyID))
        {
            $condition[ProcurementOrder::BOUND_STORE_ID] = $supplyID;
        }


        if(!empty($orderTimeStart))
        {
            if(empty($orderTimeEnd))
            {
                $orderTimeEnd = date("Y-m-d");
            }
            $orderTimeStart = explode("-",$orderTimeStart);
            $orderTimeStart = $orderTimeStart[0].$orderTimeStart[1].$orderTimeStart[2];
            $orderTimeEnd = explode("-",$orderTimeEnd);
            $orderTimeEnd = $orderTimeEnd[0].$orderTimeEnd[1].$orderTimeEnd[2];
            $condition[] = OrderBusiness::getSelectByOrderTimeCondition($orderTimeStart, $orderTimeEnd);
        }
        else
        {
            $condition[] = $con;
        }

        if(!empty($status))
        {
            $condition[ProcurementOrder::STATUS] = $status;
        }


        $orderData = ProcurementOrder::fetchRows(['*'],$condition,  $grouping = null,   $ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug("===================",$condition);
        foreach($orderData as $key => $value)
        {
            $store = new Store([Store::STORE_ID => $value[ProcurementOrder::STORE_ID]]);
            $community = new Community([Community::COMMUNITY_ID => $value[ProcurementOrder::COMMUNITY_ID]]);
            $orderData[$key]["restaurant_name"] = $community->getName();
            $orderData[$key]["bound_name"] = $store->getTitle();
            $orderData[$key]['status_name'] = ProcurementOrderStatus::getDisplayName($value[ProcurementOrder::STATUS]);
        }
        log_debug("===================",$orderData);
        if(count($orderData) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }

        $this->_view->set( 'order_data', $orderData );
    }


    //确认发货单
    public function orderSendAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $house = new HouseMember([HouseMember::WX_USER_ID => $wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $readPower = false;
        $housePower = $house->getProcurementPowerType();
        $housePower = explode(",",$housePower);
        foreach($housePower as $key => $value)
        {
            if($value == 'order')
            {
                $readPower = true;
                break;
            }
        }
        if (!$readPower)
        {
            $this->changeView('WBT/WxUser/Procurement.right.html');
        }
        $this->_view->set('wx_user', $wxUser->data());
        $communityID = $wxUser->getCurrentCommunityID();
        $this->_view->set( 'community_id', $communityID );
        $storeData = Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityID,Store::IS_DELETE => '0']);
        foreach($storeData as $key=>$value)
        {
            $procurementOrder = new ProcurementOrder([
                ProcurementOrder::BOUND_COMMUNITY_ID=>$value[Store::COMMUNITY_ID],
                ProcurementOrder::BOUND_STORE_ID=>$value[Store::STORE_ID],
                ProcurementOrder::STATUS=>'supply_send'
            ]);
            if($procurementOrder->isEmpty())
            {
                $storeData[$key]['new'] = false;
            }
            else
            {
                $storeData[$key]['new'] = true;
            }
        }
        log_debug('store==========',$storeData);
        $this->_view->set( 'store_data', $storeData );
    }
    //订单列表
    public function orderSendListAction()
    {
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set( 'store_id', $storeID );
        $communityID = $this->_request->get( 'community_id' );
        $this->_view->set( 'community_id', $communityID );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $boundCommunityId = $this->_request->get( 'bound_community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$boundCommunityId]);
        $this->_view->set( 'store_name', $community->getName() );
        $orderList = ProcurementOrder::fetchRows(['*'],[ProcurementOrder::BOUND_COMMUNITY_ID=>$communityID,
            ProcurementOrder::BOUND_STORE_ID=>$storeID,
            ProcurementOrder::STATUS=>'supply_send'],$grouping = null,$ranking = [ ProcurementOrder::CREATE_TIME => true ]);
        log_debug('orderList=======',$orderList);
        if(count($orderList) == 0)
        {
            $this->_view->set( 'count', 'none' );
        }
        $this->_view->set( 'order_list', $orderList );
    }
    //订单明细
    public function orderSendDetailAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $this->_view->set( 'wx_user_id', $wxUserID );
        $orderID = $this->_request->get( 'order_id' );

        $procurementOrder = new ProcurementOrder([ProcurementOrder::ORDER_ID => $orderID]);
        $this->_view->set( 'procurement_order', $procurementOrder );

        $this->_view->set( 'order_id', $orderID );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );
        $communityId = $this->_request->get( 'community_id' );
        $community = new Community([Community::COMMUNITY_ID=>$communityId]);
        $this->_view->set( 'store_name', $community->getName() );
        $totalPrice = $procurementOrder->getTotalPrice();
        $this->_view->set( 'total_price', $totalPrice );
        $orderDetailData = ProcurementOrderDetail::fetchRows(["*"],[ProcurementOrderDetail::ORDER_ID => $orderID]);
        //获取档口id
        $partIDs = [];
        foreach($orderDetailData as $key => $value)
        {
            $partIDs[] = $value[ProcurementOrderDetail::PART_ID];
        }
        $partIDs = array_unique($partIDs);
        $partIDProgress = [];
        foreach($partIDs as $k => $v)
        {
            $part = new Part([Part::PART_ID => $v]);
            $partIDProgress[$k]['id'] = $v;
            $partIDProgress[$k]['title'] = $part->getTitle();
        }

        foreach($orderDetailData as $key=> $value)
        {
            $orderDetailData[$key][ProcurementOrderDetail::COUNT] = $value[ProcurementOrderDetail::COUNT];
            $orderDetailData[$key]['total_price'] = $value[ProcurementOrderDetail::COUNT]*$value[ProcurementOrderDetail::PRICE];
            $orderDetailData[$key]['product_unit_new'] = ProductUnitType::getDisplayName($value[ProcurementOrderDetail::PRODUCT_UNIT]);
            $dataProgress = explode("/",$orderDetailData[$key]['product_unit_new']);
            $orderDetailData[$key]['product_unit_name'] = $dataProgress[1];
        }
        $orderDetailDataProgress = [];
        foreach($orderDetailData as $key=> $value)
        {
            foreach($partIDProgress as $k => $v)
            {
                if($value[ProcurementOrderDetail::PART_ID] == $v['id'])
                {
                    $orderDetailDataProgress[$k]['content'][] = $value;
                    $orderDetailDataProgress[$k]['part'] = $v['title'];
                    $orderDetailDataProgress[$k]['part_id'] = $v['id'];
                    $orderDetailDataProgress[$k]['price'] += $value['total_price'];
                }
            }
        }


        $this->_view->set( 'order_detail_data', $orderDetailDataProgress );
    }
    //确认完成
    public function orderSendSubmitAction()
    {
        $template = $this->_request->get( 'template' );
        $this->_view->set( 'template', $template );
        $topDirectoryID = $this->_request->get( 'top_directory_id' );
        $this->_view->set( 'top_directory_id', $topDirectoryID );
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $orderID = $this->_request->get( 'order_id' );
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( 'mp_user_id', $mpUserId );

    }

}
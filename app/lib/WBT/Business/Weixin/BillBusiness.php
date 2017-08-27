<?php
/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-3-25
 * Time: 下午3:31
 */
namespace WBT\Business\Weixin;

use MP\Model\Mp\Bill;
use MP\Model\Mp\BillDay;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\HouseMember;

class BillBusiness extends BaseBusiness{
    //账单日期列表的显示
    public static function getBillDayList(array $condition, array &$paging = null, $ranking,
                                       array $outputColumns = null)
    {
        return BillDay::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    //账单内容列表显示
    public static function getBillList(array $condition, array &$paging = null, $ranking,
                                          array $outputColumns = null)
    {
        return Bill::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    //账单日期数据的录入
    public static function insertBillDay($data)
    {
        $obj = new BillDay();
        $obj->apply( $data );
        if($obj->insertInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他账单信息重复'];
        }
    }
//账单数据数据的录入
    public static function insertBill($data)
    {
        $obj = new Bill();
        $obj->apply( $data );
        if($obj->insertInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他账单信息重复'];
        }
    }
    //账单详情数据数据的录入
    public static function insertBillDetail($data)
    {
        $obj = new BillDetail();
        $obj->apply( $data );
        $obj->insert();
        return [ 'errno' => 0 ];
    }
    //删除账单日期
    public static function deleteBillDay( $billDay,$communityId )
    {
        $condition = [ BillDay::BILL_DAY => $billDay ,BillDay::COMMUNITY_ID => $communityId];
        $billDay = new BillDay($condition);
        $bill = new Bill($condition);
        $billDetail = new BillDetail($condition);
        if ($billDay->isEmpty()) {
            log_debug( "Could not find Directory($billDay)" );
            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $billDay->delete($condition);
        $bill->delete($condition);
        $billDetail->delete($condition);

        return ['errno' => 0];
    }
    //删除账单内容
    public static function deleteBill( $billID,$communityId )
    {
        $condition = [ Bill::BILL_ID => $billID ,BillDay::COMMUNITY_ID => $communityId];
        $bill = new Bill($condition);
        $billDetail = new BillDetail($condition);
        if ($bill->isEmpty()) {
            log_debug( "Could not find Directory($billID)" );
            return ['errno' => 1, 'error' => '找不到记录'];
        }
        $bill->delete($condition);
        $billDetail->delete($condition);

        return ['errno' => 0];
    }
    //删除账单详情内容
    public static function deleteBillDetail( $billDetailID,$communityId )
    {
        $condition = [ BillDetail::BILL_DETAIL_ID => $billDetailID ,BillDetail::COMMUNITY_ID => $communityId];
        $billDetail = new BillDetail($condition);
        if ($billDetail->isEmpty()) {
            log_debug( "Could not find Directory($billDetailID)" );
            return ['errno' => 1, 'error' => '找不到记录'];
        }
        $billDetail->delete($condition);

        return ['errno' => 0];
    }
    //修改账单内容
    public static function updateBill( $billID,$data )
    {
        $obj = new Bill([Bill::BILL_ID => $billID]);
        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($billID)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->apply( $data );
        if($obj->updateInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他账单信息重复'];
        }
    }
    //修改账单详情内容
    public static function updateBillDetail( $billDetailID,$data )
    {
        $obj = new BillDetail([BillDetail::BILL_DETAIL_ID => $billDetailID]);
        if ($obj->isEmpty()) {
            log_debug( "Could not find Store($billDetailID)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->apply( $data );
        $obj->update();
        return [ 'errno' => 0 ];
    }

    //更新缴费通知单阅读时间
    public static function checkRead($billID)
    {
        log_debug("=============".$billID);
        $bill = new Bill([Bill::BILL_ID => $billID]);
        if($bill->isEmpty())
        {
            return [ 'errno' => 1, 'error' => '找不到此记录' ];
        }
        else
        {

            $currentTime = date('Y-m-d H:i:s',time());
            log_debug("=============".$currentTime);
            $bill->setReadTime($currentTime)->update();
            return [ 'errno' => 0 ];
        }
    }

    //check housememberaddress
    public static function checkHouseMemberAddress($data,$communityId)
    {

        if($data['errno'] != 0)
        {
            return [ 'errno' => 1 ];
        }
        $condition = [
            HouseMember::COMMUNITY_ID=>$communityId
        ];
        $houseMemberAddressArray = HouseMember::fetchColumn(HouseMember::HOUSE_ADDRESS,$condition);
        foreach($data['billOwner'] as $billOwner)
        {
            if(empty($billOwner))
            {
                continue;
            }
            if(!empty($billOwner['house_address']))
            {
                if(!strict_in_array($billOwner['house_address'],$houseMemberAddressArray))
                {
                    return [ 'errno' => 1, 'error' => "序号".$billOwner['house_no']."的".$billOwner['name']."业主:在Excel中房产地址与管理后台认证用户的房产地址不一致,请检查修改后再导入。"];
                }
            }

        }
        return [ 'errno' => 0 ];
    }

    //insert bill  billdetail表
    public static function insertFromExcel($data, $mpUserID, $communityId,$billDay)
    {
        $billID = null;

        if($data['errno'] != 0)
        {
            return false;
        }
        //循环遍历每个业主，进行操作
        try
        {
            foreach ($data['billOwner'] as $billOwner)
            {
                if(empty($billOwner))
                {
                    continue;
                }

                $bill = new Bill();
                $billDetail = new BillDetail();
                if(!empty($billOwner['house_address']) && $billOwner['house_col']!="小计")
                {
                    if($bill->setCommunityID($communityId)
                         ->setMpUserID($mpUserID)
                         ->setHouseNo($billOwner['house_no'])
                         ->setBillDay($billDay)
                         ->setName($billOwner['name'])
                         ->setHouseAddress($billOwner['house_address'])
                         ->setHouseArea($billOwner['house_col'])
                         ->setPhone($billOwner['phone'])
                         ->setTotalPayment($billOwner['total_payment'])
                         ->insertInRestraintOfUniqueKey())
                    {
                        $billID = $bill->getBillID();
                    }
                    else
                    {
                        $bill_update = new Bill([Bill::HOUSE_ADDRESS=>$billOwner['house_address'],Bill::COMMUNITY_ID=>$communityId,Bill::BILL_DAY=>$billDay]);
                        $bill_update->setHouseNo($billOwner['house_no'])
                                    ->setHouseArea($billOwner['house_col'])
                                    ->setName($billOwner['name'])
                                    ->setPhone($billOwner['phone'])
                                    ->setTotalPayment($billOwner['total_payment'])
                                    ->update();
                        $billID = $bill_update->getBillID();
                        $condition = [
                            BillDetail::BILL_ID=>$billID
                        ];
                        $billDetailDelete = new BillDetail($condition);
                        $billDetailDelete->delete($condition);
                    }


                }

                if(!empty($billOwner['detail_payment']) && !empty($billOwner['bill_detail_name']))
                {

                    $billDetail->setBillDay($billDay)
                        ->setCommunityID($communityId)
                        ->setBillID($billID)
                        ->setBillDetailName($billOwner['bill_detail_name'])
                        ->setBillingCycle($billOwner['billing_cycle'])
                        ->setDetailPayment($billOwner['detail_payment'])
                        ->setDetailRemarks($billOwner['detail_remarks'])
                        ->insert(true);
                    $billID = $billDetail->getBillID();
                }
                if($billOwner['house_col']=="小计")
                {
                    $bill_total_update = new Bill([Bill::BILL_ID=>$billID]);
                    $bill_total_update->setTotalPayment($billOwner['total_payment'])->update();
                }

            }
            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

}
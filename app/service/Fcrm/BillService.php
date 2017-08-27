<?php

use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\BillDay;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\Bill;

use MP\Model\Mp\Directory;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\BillBusiness;

require_once 'MpUserServiceBase.php';

class BillService extends MpUserServiceBase{
    //添加账单日期
    public function insertBillDay()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
           BillDay::MP_USER_ID,
           BillDay::COMMUNITY_ID,
           BillDay::BILL_DAY
        ] );
        $billDay = $data[BillDay::BILL_DAY];
        if((is_numeric($billDay)) && (strlen($billDay) == 8))
        {
            return BillBusiness::insertBillDay( $data );

        }
        else
        {
            return [ 'errno' => 1, 'error' => '账单日期格式错误，应该为8位数字，例如：20120506' ];
        }

    }
   //添加账单内容
    public function insertBill()
    {
        $billDay = $this->_app->request()->get(Bill::BILL_DAY);
        $request = $this->_app->request();
        $data    = $request->getArray( [
            Bill::HOUSE_NO,
            Bill::NAME,
            Bill::HOUSE_ADDRESS,
            Bill::HOUSE_AREA,
            Bill::PHONE,
            Bill::TOTAL_PAYMENT,
            Bill::MP_USER_ID,
            Bill::COMMUNITY_ID,
            Bill::PAY_FINISHED,
            Bill::BILL_PAY_METHOD
        ] );
        $data[Bill::BILL_DAY] = $billDay;
        $house = HouseMember::fetchColumn([HouseMember::HOUSE_ADDRESS],[HouseMember::COMMUNITY_ID => $data[Bill::COMMUNITY_ID],HouseMember::NAME => $data[Bill::NAME]]);
        if(!strict_in_array($data[Bill::HOUSE_ADDRESS],$house))
        {
            return [ 'errno' => 1, 'error' => '请核对您的信息，录入的房间地址有误' ];
        }
        if(!is_numeric($data[Bill::HOUSE_AREA]))
        {
            return [ 'errno' => 1, 'error' => '面积应为数字' ];
        }
        elseif(!is_numeric($data[Bill::TOTAL_PAYMENT]))
        {
            return [ 'errno' => 1, 'error' => '合计应为数字' ];
        }
        else
        {
            return BillBusiness::insertBill( $data );
        }

    }
   //添加账单详情内容
    public function insertBillDetail()
    {
        $billDay = $this->_app->request()->get(BillDetail::BILL_DAY);
        $billID = $this->_app->request()->get( BillDetail::BILL_ID);
        $request = $this->_app->request();
        $data    = $request->getArray( [
            BillDetail::BILL_DETAIL_NAME,
            BillDetail::BILLING_CYCLE,
            BillDetail::DETAIL_PAYMENT,
            BillDetail::COMMUNITY_ID,
            BillDetail::DETAIL_REMARKS,
        ] );
        $data[BillDetail::BILL_DAY] = $billDay;
        $data[BillDetail::BILL_ID] = $billID;
        if(!is_numeric($data[BillDetail::DETAIL_PAYMENT]))
        {
            return [ 'errno' => 1, 'error' => '金额应为数字' ];
        }
        else
        {
            return BillBusiness::insertBillDetail( $data );
        }

    }
    //删除账单日期
    public function removeBillDay()
    {
        $billDay = $this->_app->request()->get( BillDay::BILL_DAY);
        $communityId = $this->_app->request()->getQueryParam( BillDay::COMMUNITY_ID );

        return BillBusiness::deleteBillDay( $billDay,$communityId);
    }
  //删除账单内容
    public function removeBill()
    {
        $billID = $this->_app->request()->get( Bill::BILL_ID);

        $communityId = $this->_app->request()->getQueryParam( BillDay::COMMUNITY_ID );

        return BillBusiness::deleteBill( $billID,$communityId);
    }
    //修改账单内容
    public function updateBill()
    {
        $billID = $this->_app->request()->get( Bill::BILL_ID);
        $fields   = [
            Bill::HOUSE_NO,
            Bill::NAME,
            Bill::HOUSE_ADDRESS,
            Bill::HOUSE_AREA,
            Bill::PHONE,
            Bill::TOTAL_PAYMENT,
            Bill::PAY_FINISHED,
            Bill::BILL_PAY_METHOD
        ];
        $data = $this->_app->request()->getArray( $fields );
        if(!is_numeric($data[Bill::HOUSE_AREA]))
        {
            return [ 'errno' => 1, 'error' => '面积应为数字' ];
        }
        elseif(!is_numeric($data[Bill::TOTAL_PAYMENT]))
        {
            return [ 'errno' => 1, 'error' => '合计应为数字' ];
        }
        else
        {
            return BillBusiness::updateBill( $billID,$data);
        }

    }
    //修改账单详情内容
    public function updateBillDetail()
    {
        $billDetailID = $this->_app->request()->get( BillDetail::BILL_DETAIL_ID);
        $fields   = [
            BillDetail::BILL_DETAIL_NAME,
            BillDetail::BILLING_CYCLE,
            BillDetail::DETAIL_PAYMENT,
            BillDetail::DETAIL_REMARKS,
        ];
        $data = $this->_app->request()->getArray( $fields );
        if(!is_numeric($data[BillDetail::DETAIL_PAYMENT]))
        {
            return [ 'errno' => 1, 'error' => '金额应为数字' ];
        }
        else
        {
            return BillBusiness::updateBillDetail( $billDetailID,$data);
        }

    }
    //删除账单详情内容
    public function removeBillDetail()
    {
        $billDetailID = $this->_app->request()->get( BillDetail::BILL_DETAIL_ID);
        $communityId = $this->_app->request()->getQueryParam( BillDay::COMMUNITY_ID );

        return BillBusiness::deleteBillDetail( $billDetailID,$communityId);
    }


}


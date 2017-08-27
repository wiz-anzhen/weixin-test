<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\BillDay;
use MP\Model\Mp\Directory;
use MP\Model\Mp\Bill;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\TopDirectory;

class BillDialogController extends Controller{
    //添加收费通知单日期
    public function addBillDayAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( BillDay::COMMUNITY_ID );
        $fields   = [
           BillDay::BILL_DAY,
        ];

        $form = Form::fromModelMetadata( BillDay::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '缴费通知单';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/bill/insert_bill_day?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
   //添加收费通知单内容
    public function addBillAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( Bill::COMMUNITY_ID );
        $billDay = $this -> _request->getQueryParam( Bill::BILL_DAY );
        $fields   = [
            Bill::HOUSE_NO,
            Bill::NAME,
            Bill::PHONE,
            Bill::HOUSE_ADDRESS,
            Bill::HOUSE_AREA,
            Bill::TOTAL_PAYMENT,
            Bill::BILL_PAY_METHOD,
            Bill::PAY_FINISHED => [Form::FIELD_TAG => Form::COM_CHECK_BOX]
        ];

        $form = Form::fromModelMetadata( Bill::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '缴费通知单';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/bill/insert_bill?mp_user_id={$mpUserId}&community_id={$communityId}&bill_day={$billDay}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
    //修改收费通知单内容
    public function updateBillAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( Bill::COMMUNITY_ID );
        $billID = $this -> _request->getQueryParam( Bill::BILL_ID);
        $obj = new Bill([Bill::BILL_ID => $billID]);
        $data = $obj->data();
        $fields   = [
            Bill::HOUSE_NO,
            Bill::NAME,
            Bill::HOUSE_ADDRESS,
            Bill::HOUSE_AREA,
            Bill::PHONE,
            Bill::TOTAL_PAYMENT,
            Bill::BILL_PAY_METHOD,
            Bill::PAY_FINISHED => [Form::FIELD_TAG => Form::COM_CHECK_BOX]
        ];

        $form = Form::fromModelMetadata( Bill::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '缴费通知单修改';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/bill/update_bill?mp_user_id={$mpUserId}&community_id={$communityId}&bill_id={$billID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('修改', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
    //添加收费通知单明细内容
    public function addBillDetailAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( BillDetail::COMMUNITY_ID );
        $billID = $this -> _request->getQueryParam( BillDetail::BILL_ID );
        $billDay = $this -> _request->getQueryParam( BillDetail::BILL_DAY );
        $fields   = [
            BillDetail::BILL_DETAIL_NAME,
            BillDetail::BILLING_CYCLE,
            BillDetail::DETAIL_PAYMENT,
            BillDetail::DETAIL_REMARKS,
        ];

        $form = Form::fromModelMetadata( BillDetail::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '缴费通知单详情';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/bill/insert_bill_detail?mp_user_id={$mpUserId}&community_id={$communityId}&bill_day={$billDay}&bill_id={$billID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
    //修改收费通知明细单内容
    public function updateBillDetailAction()
    {
        $communityId = $this->_request->getQueryParam( Bill::COMMUNITY_ID );
        $billDetailID = $this -> _request->getQueryParam( BillDetail::BILL_DETAIL_ID);
        $obj = new BillDetail([BillDetail::BILL_DETAIL_ID => $billDetailID]);
        $data = $obj->data();
        $fields   = [
            BillDetail::BILL_DETAIL_NAME,
            BillDetail::BILLING_CYCLE,
            BillDetail::DETAIL_PAYMENT,
            BillDetail::DETAIL_REMARKS,
        ];

        $form = Form::fromModelMetadata( BillDetail::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '缴费通知单详情修改';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/bill/update_bill_detail?community_id={$communityId}&bill_detail_id={$billDetailID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('修改', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}

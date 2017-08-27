<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\ProcurementOrder;
use MP\Model\Mp\ProcurementOrderStatus;

class ProcurementOrderDialogController extends Controller
{
    public function orderUpdateAction()
    {
        $id  = $this->_request->getQueryParam( 'order_id' );
        $old_status  = $this->_request->getQueryParam( 'old_status' );
        $obj = new ProcurementOrder([ ProcurementOrder::ORDER_ID => $id ]);

        $dataStatus = ProcurementOrderStatus::getDictionary();

        $data   = $obj->data();
        $fields = [ ProcurementOrder::STATUS => [Form::FIELD_LABEL => "交易状态",Form::FIELD_DATA => $dataStatus], ];

        $form = Form::fromModelMetadata( ProcurementOrder::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改订单状态';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/procurement_order/order_update?order_id={$id}&old_status={$old_status}',
PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }




}
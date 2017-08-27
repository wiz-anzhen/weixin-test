<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\Order;
use MP\Model\Mp\OrderStatus;

class OrderDialogController extends Controller
{
    public function orderUpdateAction()
    {
        $id  = $this->_request->getQueryParam( 'order_id' );
        $old_status  = $this->_request->getQueryParam( 'old_status' );
        $obj = new Order([ Order::ORDER_ID => $id ]);

        $dataStatus = OrderStatus::getDictionary();
        unset($dataStatus[OrderStatus::PAID_TO_VERIFY]);
        unset($dataStatus[OrderStatus::ARRIVED]);
        $data   = $obj->data();
        $fields = [ Order::STATUS => [Form::FIELD_LABEL => "交易状态",Form::FIELD_DATA => $dataStatus], ];

        $form = Form::fromModelMetadata( Order::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改订单状态';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/order/order_update?order_id={$id}&old_status={$old_status}',
PARAMS,
        function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";


        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function orderStatusUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Order::ORDER_ID );
        $reject  = $this->_request->getQueryParam( "reject" );
        $obj = new Order([ Order::ORDER_ID => $id ]);
        if($reject == "cancel")
        {
            $fieldLabel = "取消原因";
        }
        elseif($reject == "refund")
        {
            $fieldLabel = "退款/退货原因";
        }
        elseif($reject == "reject")
        {
            $fieldLabel = "拒收原因";
        }
        $data   = $obj->data();
        $fields = [Order::REASON =>
            [Form::FIELD_LABEL => $fieldLabel ,
            'onChange = "change();"' ],
            Order::COMMENT ];

        $form = Form::fromModelMetadata( Order::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改订单状态';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/order/order_update_comment?order_id={$id}&reject={$reject}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    private function  getJavaScript()
    {
        $bodyScript = <<<JS

            change();

            function change()
            {
                var reason = $("[name='reason']").val();
                var comment = $("[name='comment']").parent().parent();


                if(reason == 'other')
                {
                    comment.show();
                }
                else
                {
                    comment.hide();
                }

            }
JS;
        return $bodyScript;
    }
}
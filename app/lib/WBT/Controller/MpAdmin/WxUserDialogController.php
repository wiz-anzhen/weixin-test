<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\WxUser;

class WxUserDialogController extends Controller
{
    public function editAction()
    {
        $wxUserId = $this->_request->getQueryParam( 'wx_user_id' );
        $mpUserId = $this->_request->getQueryParam(WxUser::MP_USER_ID);
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        $data = $wxUser->data();
        $fields = [ WxUser::USER_LEVEL => [ Form::FIELD_LABEL => '会员等级', ], ];

        $form = Form::fromModelMetadata( WxUser::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改会员等级';
        $form->ajaxForm = TRUE;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/wx_user/update?wx_user_id={$wxUserId}&mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}
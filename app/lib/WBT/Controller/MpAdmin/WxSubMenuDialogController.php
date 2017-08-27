<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\WxSubMenu;
use MP\Model\Mp\AccessAuthorityType;
class WxSubMenuDialogController extends Controller
{
    public function addAction() {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $wxMenuID = $this->_request->getQueryParam( 'wx_menu_id' );
        $key = $mpUserId . $wxMenuID . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $fields   = [WxSubMenu::WX_MENU_NAME    => [Form::FIELD_LABEL => '名称'],
                     WxSubMenu::WX_MENU_TYPE    => [Form::FIELD_LABEL => '类型', Form::COM_CHECK_BOX],
                     WxSubMenu::WX_MENU_KEY     => [Form::FIELD_LABEL => 'KEY', Form::FIELD_VALUE => $key, Form::FIELD_CLASS => 'uneditable-input',Form::COM_READONLY_TEXT],
                     WxSubMenu::CONTENT_TYPE    => [Form::FIELD_LABEL => '微信菜单内容类型', Form::COM_CHECK_BOX],
                     WxSubMenu::CONTENT_VALUE   => [Form::FIELD_LABEL => '微信菜单内容', Form::FIELD_LABEL => "微信菜单内容(仅对自定义文本消息适用)"],
                     WxSubMenu::URL             => [Form::FIELD_LABEL => 'view网址'],
                     WxSubMenu::SORT_NO         => [Form::FIELD_LABEL => '排序'],
                     WxSubMenu::ACCESS_AUTHORITY ,
        ];

        $form = Form::fromModelMetadata( WxSubMenu::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加子菜单';
        $form->ajaxForm = TRUE;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/wx_sub_menu/insert?mp_user_id={$mpUserId}&wx_menu_id={$wxMenuID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editAction()
    {
        $wxSubMenuID = $this->_request->getQueryParam( 'wx_sub_menu_id' );
        $mpUserId = $this->_request->getQueryParam(WxSubMenu::MP_USER_ID);
        $wxSubMenu = new WxSubMenu([WxSubMenu::WX_SUB_MENU_ID => $wxSubMenuID]);
        $data = $wxSubMenu->data();
        $fields   = [WxSubMenu::WX_MENU_NAME    => [Form::FIELD_LABEL => '名称'],
            WxSubMenu::WX_MENU_TYPE    => [Form::FIELD_LABEL => '类型', Form::COM_CHECK_BOX],
            WxSubMenu::CONTENT_TYPE    => [Form::FIELD_LABEL => '微信菜单内容类型', Form::COM_CHECK_BOX],
            WxSubMenu::CONTENT_VALUE   => [Form::FIELD_LABEL => '微信菜单内容', Form::FIELD_LABEL => "微信菜单内容(仅对自定义文本消息适用)"],
            WxSubMenu::URL             => [Form::FIELD_LABEL => 'view网址'],
            WxSubMenu::SORT_NO         => [Form::FIELD_LABEL => '排序'],
            WxSubMenu::ACCESS_AUTHORITY ,
        ];

        $form = Form::fromModelMetadata( WxSubMenu::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改子菜单';
        $form->ajaxForm = TRUE;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/wx_sub_menu/update?wx_sub_menu_id={$wxSubMenuID}&mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}
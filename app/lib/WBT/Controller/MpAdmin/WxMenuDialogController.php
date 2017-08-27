<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\MpRuleNewsItem;
use MP\Model\Mp\WxMenu;

class WxMenuDialogController extends Controller
{
    public function addAction() {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $fields = [ WxMenu::NAME             => [ Form::FIELD_LABEL => '名称', ],
                    WxMenu::SORT_NO          => [ Form::FIELD_LABEL => '排序', ], ];

        $form = Form::fromModelMetadata( WxMenu::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加菜单';
        $form->ajaxForm = TRUE;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/wx_menu/insert?mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editAction()
    {
        $wxMenuID = $this->_request->getQueryParam( 'wx_menu_id' );
        $mpUserId = $this->_request->getQueryParam(WxMenu::MP_USER_ID);
        $wxMenu = new WxMenu([WxMenu::WX_MENU_ID => $wxMenuID]);
        $data = $wxMenu->data();
        $fields = [ WxMenu::NAME    => [ Form::FIELD_LABEL => '名称', ],
                    WxMenu::SORT_NO => [ Form::FIELD_LABEL => '排序', ], ];

        $form = Form::fromModelMetadata( WxMenu::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改菜单';
        $form->ajaxForm = TRUE;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/wx_menu/update?wx_menu_id={$wxMenuID}&mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addNewsAction() {
        $mpUserId           = $this->_request->getQueryParam( 'mp_user_id' );
        $wxSubMenuID        = $this->_request->getQueryParam( 'wx_sub_menu_id' );
        $fields             = [ 'title'       => [ Form::FIELD_LABEL => '标题', ],
            'description' => [ Form::FIELD_LABEL => '摘要', ],
            'pic_url'     => [ Form::FIELD_LABEL => '图片网址', Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ],
            'url'         => [ Form::FIELD_LABEL => '跳转链接' ],
            'top_dir_no' => [Form::FIELD_LABEL => '一级目录编号'],
            'sort_no'     => [ Form::FIELD_LABEL => '排序'],
        ];
        $form               = Form::fromModelMetadata( MpRuleNewsItem::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );
        $form->legend       = '添加图文消息';
        $form->ajaxForm     = TRUE;
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_rule/insert_news_for_wx?mp_user_id={$mpUserId}&wx_sub_menu_id={$wxSubMenuID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}
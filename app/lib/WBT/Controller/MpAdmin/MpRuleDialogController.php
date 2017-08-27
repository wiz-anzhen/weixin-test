<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\MpRule;
use MP\Model\Mp\MpRuleNewsItem;

class MpRuleDialogController extends Controller
{
    public function editAction()
    {
        $id = $this->_request->getQueryParam( 'mp_rule_id' );
        $mpUserId = $this->_request->getQueryParam(MpRule::MP_USER_ID);

        $mpRule = new MpRule($id);
        $data   = $mpRule->data();

        //表单的字段
        $fields = [ 'mp_rule_id'   => [ Form::FIELD_LABEL => '编号',
                                        Form::FIELD_TAG   => Form::COM_READONLY_TEXT, ],
                    'name'         => [ Form::FIELD_LABEL => '规则名称', ],
                    'keyword'      => [ Form::FIELD_LABEL => '关键词', ],
                    'content_type' => [ Form::FIELD_LABEL => '消息类型',
                                        Form::FIELD_TAG   => Form::COM_RADIO_GROUP,
                                        Form::FIELD_DATA  => [ 'text' => '文本',
                                                               'news' => '图文（若从图文消息改为文本消息，保存后再次编辑即可修改规则内容）', ], ],
                    'content'      => [ Form::FIELD_LABEL => '规则内容',
                                        Form::FIELD_CLASS => 'big_area', ], ];

        if ($mpRule->getContentType() === 'news') {
            $fields['content'][Form::FIELD_TAG] = Form::COM_READONLY_TEXT;
        }

        $form = Form::fromModelMetadata( MpRule::s_metadata(), $fields, $data, [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改规则';
        $form->ajaxForm = TRUE;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_rule/update?mp_rule_id={$id}&mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        /*if ($this->_request->getMethod() === Common::HTTP_METHOD_POST) { }*/
        $fields             = [ 'name'         => [ Form::FIELD_LABEL => '规则名称', ],
                                'keyword'      => [ Form::FIELD_LABEL => '关键词', ],
                                'content_type' => [ Form::FIELD_LABEL => '消息类型',
                                                    Form::FIELD_TAG   => Form::COM_RADIO_GROUP,
                                                    Form::FIELD_DATA  => [ 'text' => '文本',
                                                                           'news' => '图文（若为图文消息，则忽略规则内容）', ], ],
                                'content'      => [ Form::FIELD_LABEL => '规则内容', Form::FIELD_REQUIRED => FALSE ], ];
        $form               = Form::fromModelMetadata( MpRule::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );
        $form->legend       = '添加规则';
        $form->ajaxForm     = TRUE;
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_rule/insert?mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editNewsAction()
    {
        $ruleNewsItemId = $this->_request->getQueryParam( 'mp_rule_news_item_id' );
        $wxSubMenuID = $this->_app->request()->get( 'wx_sub_menu_id' );
        $mpUserId = $this->_request->getQueryParam(MpRule::MP_USER_ID);

        $mpRuleNewsItem = new MpRuleNewsItem([MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $ruleNewsItemId]);
        $data           = $mpRuleNewsItem->data();

        //表单的字段
        $fields = [ 'mp_rule_news_item_id' => [ Form::FIELD_LABEL => '编号',
                                                Form::FIELD_TAG   => Form::COM_READONLY_TEXT, ],
                    'title'                => [ Form::FIELD_LABEL => '标题', ],
                    'description'          => [ Form::FIELD_LABEL => '摘要', ],
                    'pic_url'              => [ Form::FIELD_LABEL => '图片网址',
                                                Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD, ],
                    'url'                  => [ Form::FIELD_LABEL => '跳转链接' ],
                    'top_dir_no'           => [ Form::FIELD_LABEL => '一级目录编号'],
                    'sort_no'              => [ Form::FIELD_LABEL => '排序' ]];

        $form = Form::fromModelMetadata( MpRuleNewsItem::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改图文消息';
        $form->ajaxForm = TRUE;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_rule/update_news?mp_rule_news_item_id={$ruleNewsItemId}&mp_user_id={$mpUserId}&wx_sub_menu_id={$wxSubMenuID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addNewsAction()
    {
        $mpUserId           = $this->_request->getQueryParam( 'mp_user_id' );
        $mpRuleId           = $this->_request->getQueryParam( 'mp_rule_id' );
        $fields             = [ 'title'       => [ Form::FIELD_LABEL => '标题', ],
                                'description' => [ Form::FIELD_LABEL => '描述', ],
                                'pic_url'     => [ Form::FIELD_LABEL => '图片网址' ],
                                'url'         => [ Form::FIELD_LABEL => '跳转链接' ],
            ];

        $form               = Form::fromModelMetadata( MpRuleNewsItem::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );

        $form->legend       = '添加图文消息';
        $form->ajaxForm     = TRUE;
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_rule/insert_news?mp_user_id={$mpUserId}&mp_rule_id={$mpRuleId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}
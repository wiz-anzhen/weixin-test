<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\WjChoice;
use MP\Model\Mp\WjQuestion;
use MP\Model\Mp\WjQuestionnaire;

class QuestionnaireDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $fields   = [ WjQuestionnaire::TITLE            => [ Form::FIELD_LABEL => '问卷标题', ],
                      WjQuestionnaire::HEAD_DESC        => [ Form::FIELD_LABEL => '卷首语', ],
                      WjQuestionnaire::TAIL_DESC        => [ Form::FIELD_LABEL => '卷尾语', ],
                      WjQuestionnaire::CUSTOMER_PROFILE => [ Form::FIELD_LABEL => '用户信息', ],
                      WjQuestionnaire::COMMENT          => [ Form::FIELD_LABEL => '备注', ], ];

        $form = Form::fromModelMetadata( WjQuestionnaire::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '新建问卷';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/questionnaire/insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editAction()
    {
        $mpUserId          = $this->_request->getQueryParam( WjQuestionnaire::MP_USER_ID );
        $WjQuestionnaireId = $this->_request->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );
        $WjQuestionnaire   = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $WjQuestionnaireId ]);
        $communityId = $this->_request->getQueryParam( 'community_id');

        $data   = $WjQuestionnaire->data();
        $fields = [ WjQuestionnaire::TITLE            => [ Form::FIELD_LABEL => '问卷标题', ],
                    WjQuestionnaire::HEAD_DESC        => [ Form::FIELD_LABEL => '卷首语', ],
                    WjQuestionnaire::TAIL_DESC        => [ Form::FIELD_LABEL => '卷尾语', ],
                    WjQuestionnaire::CUSTOMER_PROFILE => [ Form::FIELD_LABEL => '用户信息', ],
                    WjQuestionnaire::COMMENT          => [ Form::FIELD_LABEL => '备注', ], ];

        $form = Form::fromModelMetadata( WjQuestionnaire::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改问卷';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/questionnaire/update?wj_questionnaire_id={$WjQuestionnaireId}&mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addQuestionAction()
    {
        $mpUserId          = $this->_request->getQueryParam( WjQuestion::MP_USER_ID );
        $WjQuestionnaireId = $this->_request->getQueryParam( WjQuestion::WJ_QUESTIONNAIRE_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $fields            = [ WjQuestion::QUESTION_TYPE => [ Form::FIELD_LABEL   => '问题类型', ],
                               WjQuestion::CONTENT       => [ Form::FIELD_LABEL   => '问题内容', ],
                               WjQuestion::PLACEHOLDER   => [ Form::FIELD_LABEL   => '输入框提示语',
                                                              Form::FIELD_MESSAGE => '仅当“问题类型”为“单行输入框”或“多行输入框”时有效',
                               ],
                               WjQuestion::COMMENT       => [ Form::FIELD_LABEL   => '备注', ],
                               WjQuestion::SORT_NO       => [ Form::FIELD_LABEL   => '排序', ] ];

        $form = Form::fromModelMetadata( WjQuestion::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '新建问题';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/questionnaire/insert_question?mp_user_id={$mpUserId}&wj_questionnaire_id={$WjQuestionnaireId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editQuestionAction()
    {
        $mpUserId     = $this->_request->getQueryParam(WjQuestion::MP_USER_ID);
        $WjQuestionId = $this->_request->getQueryParam(WjQuestion::WJ_QUESTION_ID);
        $WjQuestion   = new WjQuestion([WjQuestion::WJ_QUESTION_ID => $WjQuestionId]);

        $data   = $WjQuestion->data();
        $fields = [ WjQuestion::QUESTION_TYPE => [ Form::FIELD_LABEL   => '问题类型', ],
                    WjQuestion::CONTENT       => [ Form::FIELD_LABEL   => '问题内容', ],
                    WjQuestion::PLACEHOLDER   => [ Form::FIELD_LABEL   => '输入框提示语',
                                                   Form::FIELD_MESSAGE => '仅当“问题类型”为“单行输入框”或“多行输入框”时有效', ],
                    WjQuestion::COMMENT       => [ Form::FIELD_LABEL   => '备注', ],
                    WjQuestion::SORT_NO       => [ Form::FIELD_LABEL   => '排序' ], ];

        $form = Form::fromModelMetadata( WjQuestion::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改问题';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/questionnaire/update_question?wj_question_id={$WjQuestionId}&mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addChoiceAction()
    {
        $mpUserId     = $this->_request->getQueryParam( WjChoice::MP_USER_ID );
        $WjQuestionId = $this->_request->getQueryParam( WjChoice::WJ_QUESTION_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $fields       = [ WjChoice::CONTENT => [ Form::FIELD_LABEL => '选项内容', ],
                          WjChoice::COMMENT => [ Form::FIELD_LABEL => '备注', ],
                          WjChoice::SORT_NO => [ Form::FIELD_LABEL => '排序', ] ];

        $form = Form::fromModelMetadata( WjChoice::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '新建选项';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/questionnaire/insert_choice?mp_user_id={$mpUserId}&wj_question_id={$WjQuestionId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editChoiceAction()
    {
        $mpUserId   = $this->_request->getQueryParam( WjChoice::MP_USER_ID );
        $WjChoiceId = $this->_request->getQueryParam( WjChoice::WJ_CHOICE_ID );
        $WjChoice   = new WjChoice([ WjChoice::WJ_CHOICE_ID => $WjChoiceId ]);

        $data   = $WjChoice->data();
        $fields = [ WjChoice::CONTENT => [ Form::FIELD_LABEL => '选项内容', ],
                    WjChoice::COMMENT => [ Form::FIELD_LABEL => '备注', ],
                    WjChoice::SORT_NO => [ Form::FIELD_LABEL => '排序' ], ];

        $form = Form::fromModelMetadata( WjChoice::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改选项';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/questionnaire/update_choice?wj_choice_id={$WjChoiceId}&mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
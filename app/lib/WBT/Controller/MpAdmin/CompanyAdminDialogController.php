<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\CheckGroup;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\CompanyAdmin;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\CompanyAdminPowerType;
use MP\Model\Mp\MpUser;
class CompanyAdminDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $mpUser = new MpUser([MpUser::MP_USER_ID=>$mpUserId]);
        $fields   = [
            CompanyAdmin::USERNAME => [ Form::FIELD_LABEL => "管理员邮箱"],
            CompanyAdmin::MP_USER_ID => [Form::FIELD_LABEL => "公共账号ID",
                                          Form::FIELD_VALUE => $mpUserId,
                                          Form::FIELD_TAG => Form::COM_READONLY_TEXT ],
            CompanyAdmin::MP_NAME => [Form::FIELD_LABEL => "公共账号名称",
                Form::FIELD_VALUE => $mpUser->getMpName(),
                Form::FIELD_TAG => Form::COM_READONLY_TEXT ],
            CompanyAdmin::POWER =>
                [
                    Form::FIELD_LABEL => "权限",
                    Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                    CheckGroup::COUNT_PER_LINE => 1,
                    Form::FIELD_DATA =>  CompanyAdminPowerType::getDictionary(),
                ],
            CompanyAdmin::COMMENT,
            'password' => [ Form::FIELD_LABEL => "设置密码",
                     Form::FIELD_TAG => Form::COM_INPUT,
                     Form::FIELD_CLASS => "input-xlarge"],
        ];
        $form = Form::fromModelMetadata( CompanyAdmin::s_metadata(), $fields, null, [ 'class' => 'form-horizontal' ] );
        $form->legend   = '添加二级管理员';
        $form->ajaxForm = true;
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/company_admin/insert?mp_user_id={$mpUserId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        echo $form;
        echo SimpleComponent::$scripts;

    }

    public function updateAction()
    {
        $id  = $this->_request->getQueryParam( CompanyAdmin::COMPANY_ADMIN_ID );
        $obj = new CompanyAdmin([ CompanyAdmin::COMPANY_ADMIN_ID => $id ]);

        $data   = $obj->data();
        $fields   =
            [
                CompanyAdmin::USERNAME => [Form::FIELD_LABEL => "管理员邮箱",
                    Form::FIELD_TAG => Form::COM_READONLY_TEXT ],
                CompanyAdmin::POWER =>
                [
                    Form::FIELD_LABEL => "权限",
                    CheckGroup::COUNT_PER_LINE => 1,
                    Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                    Form::FIELD_DATA => CompanyAdminPowerType::getDictionary(),
                ],
                CommunityAdmin::COMMENT,
            ];
        $form = Form::fromModelMetadata( CompanyAdmin::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改二级管理员权限';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/company_admin/update?company_admin_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updatePasswordAction()
    {
        $id  = $this->_request->getQueryParam( CompanyAdmin::COMPANY_ADMIN_ID );
        $obj = new CompanyAdmin([ CompanyAdmin::COMPANY_ADMIN_ID => $id ]);

        $data   = $obj->data();
        $fields   =
            [
                CompanyAdmin::USERNAME => [Form::FIELD_LABEL => "管理员邮箱",
                                         Form::FIELD_TAG => Form::COM_HIDDEN],
                'password' =>
                    [
                        Form::FIELD_LABEL => "密码",
                        Form::FIELD_TAG => Form::COM_INPUT,
                    ],
            ];
        $form = Form::fromModelMetadata( CompanyAdmin::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改密码';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/company_admin/update_password?company_admin_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\CheckGroup;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\CompanyAdmin;
use WBT\Model\Weibotui\User;
use WBT\Business\UserBusiness;
use MP\Model\Mp\CompanyAdminPowerType;
class CommunityAdminDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $company_admin = $this->_request->get('company_admin');
        $userName = UserBusiness::getLoginUser()->getUsername();
        $power =  CommunityAdminPowerType::getDictionary();
        //checkPower
        if($company_admin == 1)
        {
            $powerArr = UserBusiness::checkPower($userName,$communityId,$mpUserId);
            foreach($powerArr as $value)
            {
                if(array_key_exists($value.'_r',CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value.'_r'] = CommunityAdminPowerType::getDisplayName($value."_r");
                }
                if(array_key_exists($value.'_d',CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value.'_d'] = CommunityAdminPowerType::getDisplayName($value."_d");
                }
                if(array_key_exists($value.'_rw',CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value.'_rw'] = CommunityAdminPowerType::getDisplayName($value."_rw");
                }
                if(array_key_exists($value,CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value] = CommunityAdminPowerType::getDisplayName($value);
                }
            }
            $power = $newPower;
        }
        $fields   = [
            CommunityAdmin::USERNAME => [ Form::FIELD_LABEL => "管理员邮箱"],
            CommunityAdmin::POWER =>
                          [
                              Form::FIELD_LABEL => "权限",
                              Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                              CheckGroup::COUNT_PER_LINE => 1,
                              Form::FIELD_DATA =>  $power,
                          ],
            CommunityAdmin::COMMENT,
            /*
            'password' => [ Form::FIELD_LABEL => "设置密码",
                     Form::FIELD_TAG => Form::COM_INPUT,
                     Form::FIELD_CLASS => "input-xlarge"],
            */
        ];
        $form = Form::fromModelMetadata( CommunityAdmin::s_metadata(), $fields, null, [ 'class' => 'form-horizontal' ] );
        $form->legend   = '添加小区管理员';
        $form->ajaxForm = true;
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_admin/insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        echo $form;
        echo SimpleComponent::$scripts;

    }

    public function updateAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $id  = $this->_request->getQueryParam( CommunityAdmin::COMMUNITY_ADMIN_ID );
        $obj = new CommunityAdmin([ CommunityAdmin::COMMUNITY_ADMIN_ID => $id ]);

        $data   = $obj->data();
        $userName = UserBusiness::getLoginUser()->getUsername();
        $companyAdmin = new CompanyAdmin([CompanyAdmin::USERNAME => $userName]);
        $power =  CommunityAdminPowerType::getDictionary();
        //checkPower
        if(!$companyAdmin->isEmpty())
        {
            $powerArr = UserBusiness::checkPower($userName,$communityId,$mpUserId);
            foreach($powerArr as $value)
            {
                if(array_key_exists($value.'_r',CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value.'_r'] = CommunityAdminPowerType::getDisplayName($value."_r");
                }
                if(array_key_exists($value.'_d',CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value.'_d'] = CommunityAdminPowerType::getDisplayName($value."_d");
                }
                if(array_key_exists($value.'_rw',CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value.'_rw'] = CommunityAdminPowerType::getDisplayName($value."_rw");
                }
                if(array_key_exists($value,CommunityAdminPowerType::getDictionary()))
                {
                    $newPower[$value] = CommunityAdminPowerType::getDisplayName($value);
                }
            }
            $power = $newPower;
        }
        $fields   =
            [
                CommunityAdmin::USERNAME => [Form::FIELD_LABEL => "管理员邮箱",
                    Form::FIELD_TAG => Form::COM_READONLY_TEXT ],
                CommunityAdmin::POWER =>
                [
                    Form::FIELD_LABEL => "权限",
                    CheckGroup::COUNT_PER_LINE => 1,
                    Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                    Form::FIELD_DATA => $power,
                ],
                CommunityAdmin::COMMENT,
            ];
        $form = Form::fromModelMetadata( CommunityAdmin::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改小区管理员';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_admin/update?community_admin_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updatePasswordAction()
    {
        $id  = $this->_request->getQueryParam( CommunityAdmin::COMMUNITY_ADMIN_ID );
        $obj = new CommunityAdmin([ CommunityAdmin::COMMUNITY_ADMIN_ID => $id ]);

        $data   = $obj->data();
        $fields   =
            [
                CommunityAdmin::USERNAME => [Form::FIELD_LABEL => "管理员邮箱",
                                         Form::FIELD_TAG => Form::COM_HIDDEN],
                'password' =>
                    [
                        Form::FIELD_LABEL => "密码",
                        Form::FIELD_TAG => Form::COM_INPUT,
                        Form::FIELD_DATA =>  CommunityAdminPowerType::getDictionary(),
                    ],
            ];
        $form = Form::fromModelMetadata( CommunityAdmin::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改密码';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_admin/update_password?community_admin_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
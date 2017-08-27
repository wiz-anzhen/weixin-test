<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\CheckGroup;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use WBT\Model\Weibotui\User;
use MP\Model\Mp\CommunityConfig;
use MP\Model\Mp\CommunityConfigType;

class CommunityConfigDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [
            CommunityConfig::CONFIG_TYPE ,
            CommunityConfig::CONFIG_VALUE
        ];
        $form = Form::fromModelMetadata( CommunityConfig::s_metadata(), $fields, null, [ 'class' => 'form-horizontal' ] );
        $form->legend   = '添加小区配置';
        $form->ajaxForm = true;
        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_config/insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        echo $form;
        echo SimpleComponent::$scripts;

    }

    public function updateAction()
    {
        $id  = $this->_request->getQueryParam( CommunityConfig::COMMUNITY_CONFIG_ID );
        $obj = new CommunityConfig([ CommunityConfig::COMMUNITY_CONFIG_ID => $id ]);
        $data   = $obj->data();
        $data[CommunityConfig::CONFIG_TYPE] = CommunityConfigType::getDisplayName($data[CommunityConfig::CONFIG_TYPE]);
        $fields = [
            CommunityConfig::CONFIG_TYPE => [Form::FIELD_TAG => Form::COM_READONLY_TEXT,],
            CommunityConfig::CONFIG_VALUE
        ];
        $form = Form::fromModelMetadata( CommunityConfig::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改配置';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_config/update?community_config_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }



}
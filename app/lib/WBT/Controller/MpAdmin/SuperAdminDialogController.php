<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\MpUserConfig;
use WBT\Business\Weixin\SuperAdminBusiness;
use Bluefin\Data\Database;
use WBT\Controller\WBTControllerBase;

use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

class SuperAdminDialogController extends WBTControllerBase
{
    public function mpUserConfigAddAction()
    {
        $mpUserID = $this->_request->getQueryParam( 'mp_user_id' );
        $fields =
            [
            MpUserConfig::CONFIG_TYPE ,
            MpUserConfig::CONFIG_TYPE_TYPE => ['onChange = "change_config_value();"' ],
            MpUserConfig::CONFIG_VALUE,
            "bool" =>
            [
                Form::FIELD_LABEL => '公众账号配置值',
                Form::FIELD_TAG => Form::COM_CHECK_BOX  ,
            ],

            "img" =>
            [
                Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ,
            ],
            ];
        $form = Form::fromModelMetadata(MpUserConfig::s_metadata(),$fields,null,[ 'class' => 'form-horizontal' ]);
        $form->legend = '添加配置';
        $form->ajaxForm = 'true';
        $successMessage  = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/super_admin/mp_user_config_insert?mp_user_id={$mpUserID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );

        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;

    }

    public function mpUserConfigUpdateAction()
    {
        $mpUserID = $this->_request->getQueryParam( 'mp_user_id' );
        $mpUserConfigID = $this->_request->getQueryParam( 'mp_user_config_id' );
        $obj = new MpUserConfig([ MpUserConfig::MP_USER_CONFIG_ID => $mpUserConfigID ]);
        $data   = $obj->data();
        $data[MpUserConfig::CONFIG_TYPE] = MpUserConfigType::getDisplayName($data[MpUserConfig::CONFIG_TYPE]);
        $fields = [
            MpUserConfig::CONFIG_TYPE => [Form::FIELD_TAG => Form::COM_READONLY_TEXT,],
            MpUserConfig::CONFIG_TYPE_TYPE => ['onChange = "change_config_value();"' ],
            "bool" =>
                [
                    Form::FIELD_LABEL => '公众账号配置值',
                    Form::FIELD_TAG => Form::COM_CHECK_BOX  ,
                ],

            "img" =>
                [
                    Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ,
                ],
            "text" =>
                [
                    Form::FIELD_LABEL => '公众账号配置值',
                    Form::FIELD_TAG => Form::COM_TEXT_AREA,
                ],
            "hide" => [Form::FIELD_TAG => Form::COM_HIDDEN,
                       Form::FIELD_VALUE => "hide"],
        ];
        if($data[MpUserConfig::CONFIG_TYPE_TYPE ]== 'bool')
        {
            $fields[MpUserConfig::CONFIG_VALUE] = [Form::FIELD_LABEL => '公众账号配置值',
                                                   Form::FIELD_TAG => Form::COM_CHECK_BOX ,];
        }
        else if($data[MpUserConfig::CONFIG_TYPE_TYPE ]== 'img')
        {
            $fields[MpUserConfig::CONFIG_VALUE] = [
                                                   Form::FIELD_LABEL => '',
                                                   Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD ,];
        }
        else
        {
            $fields[MpUserConfig::CONFIG_VALUE] = [Form::FIELD_LABEL => '公众账号配置值',
                                                   Form::FIELD_TAG => Form::COM_TEXT_AREA,];
        }
        $form = Form::fromModelMetadata(MpUserConfig::s_metadata(),$fields,$data,[ 'class' => 'form-horizontal' ]);
        $form->legend = '修改配置';
        $form->ajaxForm = 'true';
        $successMessage  = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/super_admin/mp_user_config_update?mp_user_config_id={$mpUserConfigID}&mp_user_id={$mpUserID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('修改', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaUpdateScript();
        echo $form;
        echo SimpleComponent::$scripts;

    }


//更新公众账号属性配置
    private function  getJavaUpdateScript()
    {
        $bodyScript = <<<JS
            var configTypeType_bool = $("#form1Bool").parent().parent();
            var configTypeType_img = $("#form1Img_mainImg").parent().parent();
            var form1Text = $("#form1Text").parent().parent();
            configTypeType_bool.hide();
            configTypeType_img.hide();
            form1Text.hide();

            function change_config_value(){
                var configType = $("[name='config_type']").val();
                var configTypeType = $("[name='config_type_type']").val();
                var configValue = $("#form1ConfigValue").parent().parent();
                var configTypeType_bool = $("#form1Bool").parent().parent();
                var configTypeType_img = $("#form1Img_mainImg").parent().parent();
                var form1ConfigValue_mainImg = $("#form1ConfigValue_mainImg").parent().parent();
                var form1Text = $("#form1Text").parent().parent();
                var form1Hide = $("#form1Hide").val('change');


                if(configTypeType == 'bool')
                {
                    configValue.hide();
                    configTypeType_bool.show();
                    configTypeType_img.hide();
                    form1ConfigValue_mainImg.hide();
                    form1Text.hide();
                }
                else if(configTypeType == 'img')
                {
                    configValue.hide();
                    configTypeType_bool.hide();
                    configTypeType_img.show();
                    form1Text.hide();
                    form1ConfigValue_mainImg.hide();
                }else if(configTypeType == 'text' ||configTypeType == 'url')
                {
                    configValue.hide();
                    configTypeType_bool.hide();
                    configTypeType_img.hide();
                    form1ConfigValue_mainImg.hide();
                    form1Text.show();
                }

          }
JS;
        return $bodyScript;
    }

    private function  getJavaScript()
    {
        $bodyScript = <<<JS

            change_config_value();

            function change_config_value(){
                var configType = $("[name='config_type']").val();
                var configTypeType = $("[name='config_type_type']").val();
                var configValue = $("[name='config_value']").parent().parent();
                var configTypeType_bool = $("#form1Bool").parent().parent();
                var configTypeType_img = $("#form1Img_mainImg").parent().parent();

                if(configTypeType == 'bool')
                {
                    configValue.hide();
                    configTypeType_bool.show();
                    configTypeType_img.hide();
                }
                else if(configTypeType == 'img')
                {
                    configValue.hide();
                    configTypeType_bool.hide();
                    configTypeType_img.show();
                }else
                {
                    configValue.show();
                    configTypeType_bool.hide();
                    configTypeType_img.hide();
                }

          }
JS;
        return $bodyScript;
    }

}
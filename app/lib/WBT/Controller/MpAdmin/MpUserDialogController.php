<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use WBT\Business\UserBusiness;

class MpUserDialogController extends Controller
{
    public function editAction() {
        $mpUserID = $this->_request->getQueryParam( 'mp_user_id' );

        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $data   = $mpUser->data();

        //表单的字段
        $fields = [ 'mp_user_id' => [ Form::FIELD_LABEL => '编号',
            Form::FIELD_TAG   => Form::COM_READONLY_TEXT, ],
        ];

        $form = Form::fromModelMetadata( MpUser::s_metadata(), $fields, $data, [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改公共账号信息';
        $form->ajaxForm = TRUE;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_user/update?mp_user_id={$mpUserID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function superAdminAction() {
        $mpUserID = $this->_request->getQueryParam( 'mp_user_id' );
        $mpUser   = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $data     = $mpUser->data();

        $fields = [
            MpUser::MP_NAME             => [ Form::FIELD_LABEL => '账号名称', ],
            MpUser::SHARE_PIC           => [ Form::FIELD_LABEL => '文章分享图片', Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD, ],
            MpUser::COMMENT             => [ Form::FIELD_LABEL => '备注', ],
            MpUser::APP_ID              => [ Form::FIELD_LABEL => 'APP_ID', ],
            MpUser::APP_SECRET          => [ Form::FIELD_LABEL => 'APP_SECRET', ],
            MpUser::PARTNER_ID          => [ Form::FIELD_LABEL => 'PARTNER_ID', ],
            MpUser::PARTNER_KEY          => [ Form::FIELD_LABEL => 'PARTNER_KEY', ],
            MpUser::PAY_SIGN_KEY          => [ Form::FIELD_LABEL => 'PAY_SIGN_KEY', ],
            MpUser::MCHID          => [ Form::FIELD_LABEL => '受理商ID', ],
            MpUser::PAY_KEY          => [ Form::FIELD_LABEL => '商户支付密钥Key', ],
            MpUser::JS_API_CALL_URL          => [ Form::FIELD_LABEL => 'jsapi跳转支付页面url', ],
            MpUser::SSLCERT_PATH          => [ Form::FIELD_LABEL => 'apiclient_cert证书路径', ],
            MpUser::SSLKEY_PATH          => [ Form::FIELD_LABEL => 'apiclient_key证书路径', ],
            MpUser::NOTIFY_URL          => [ Form::FIELD_LABEL => '异步通知url', ],
            MpUser::CURL_TIMEOUT          => [ Form::FIELD_LABEL => '请求最短时间', ],
            MpUser::MP_USER_TYPE          => [ Form::FIELD_LABEL => '公众账号属性',
                Form::FIELD_MESSAGE => '是否是服务号'],
            MpUser::INDUSTRY          => [ Form::FIELD_LABEL => '公众账号行业类型'],
            MpUser::CARD_LIST_DIRECTORY => [ Form::FIELD_LABEL => '会员卡页目录ID', ],
            MpUser::CARD_BACKGROUND     => [ Form::FIELD_LABEL => '会员卡背景', Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD,],
            MpUser::CS_VISIBLE          => [ Form::FIELD_LABEL => '显示客服专员', ],
            MpUser::SALE_LIST_NAME      => [ Form::FIELD_LABEL => '出库销售单标题', ],
            MpUser::OPEN_DATE           => [ Form::FIELD_LABEL => '服务开通日期', ],
            MpUser::FOLLOWED_CONTENT    => [ Form::FIELD_LABEL => '关注欢迎语',
                Form::FIELD_CLASS => 'big_area', ],
            MpUser::VALID          => [ Form::FIELD_LABEL => '是否有效', ],
            MpUser::SEND_REPORT => [Form::FIELD_LABEL => '是否E-MAIL发送报表',],
        ];

        $form               = Form::fromModelMetadata( MpUser::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );
        $form->legend       = '修改公共账号信息';
        $form->ajaxForm     = TRUE;
        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_user/super_admin_update?mp_user_id={$mpUserID}', PARAMS, function(){bluefinBH.closeDialog(FORM);bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    //导出微信用户
    public function downAction() {
        $mpUserID = $this->_request->getQueryParam( 'mp_user_id' );
        $mpUser   = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $data     = $mpUser->data();

        $fields = [
            MpUser::MP_NAME            => [ Form::FIELD_LABEL => '账号名称', Form::FIELD_VALUE => $mpUser->getMpName(),
                Form::FIELD_TAG => Form::COM_READONLY_TEXT],
        ];

        $form               = Form::fromModelMetadata( MpUser::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );
        $form->legend       = '导入微信用户';
        $form->ajaxForm     = TRUE;
        $successMessage     = '导入成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_user/down?mp_user_id={$mpUserID}', PARAMS, function(){bluefinBH.closeDialog(FORM);bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";
        $form->addButtons( [ new Button('确认', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addAction() {
        $username   = UserBusiness::getLoginUsername();
        $superAdmin = new SuperAdmin([SuperAdmin::USERNAME => $username]);
        if ($superAdmin->isEmpty()) exit;

        $fields = [
            MpUser::MP_NAME                  => [ Form::FIELD_LABEL => '账号名称', ],
            MpUser::COMMENT                  => [ Form::FIELD_LABEL => '备注', ],
            MpUser::VALID                    => [ Form::FIELD_LABEL => '是否有效',
                Form::FIELD_TAG   => Form::COM_CHECK_BOX, ],
        ];

        $form               = Form::fromModelMetadata( MpUser::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );
        $form->legend       = '添加公共账号';
        $form->ajaxForm     = TRUE;
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_user/super_admin_add', PARAMS, function(){bluefinBH.closeDialog(FORM);bluefinBH.showInfo('{$successMessage}', location.reload())});";
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
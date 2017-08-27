<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use Bluefin\HTML\CheckGroup;


use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\CustomerSpecialistGroup;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\UserNotify;
use MP\Model\Mp\UserNotifySendRangeType;


class UserNotifyDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        //信息编号和摘要注释说明
        $industry = $mpUser->getIndustry();
        $infoidTitle= "任务名称";
        $descriptionTitle = "通知类型";
        if($industry == IndustryType::FIANCE)
        {
            $infoidTitle = "来源";
            $descriptionTitle = "备注";
        }
        $communityId = $this->_request->get('community_id');
        $from = $this->_request->get('from');
        $sendRange = UserNotifySendRangeType::getDictionary();
        if($from == "mp")
        {
            $sendRange = array_slice($sendRange,3,3);
        }
        elseif($from == "app_mp")
        {
            $sendRange = array_slice($sendRange,6,1);
        }
        elseif($from == "app_c")
        {
            $sendRange = array_slice($sendRange,7);
        }
        else
        {
            $sendRange = array_slice($sendRange,0,3);
        }
        $specialistGroup = CustomerSpecialistGroup::fetchRows(['*'],[CustomerSpecialistGroup::COMMUNITY_ID => $communityId]);
        $specialistGroupNew = [];
        foreach($specialistGroup as $key => $value)
        {
            $specialistGroupNew[$value[CustomerSpecialistGroup::GROUP_NAME]] = $value[CustomerSpecialistGroup::GROUP_NAME];
        }
        $fields   = [
            UserNotify::TITLE ,
            UserNotify::INFOID => [Form::FIELD_LABEL => $infoidTitle,Form::FIELD_TAG => Form::COM_TEXT_AREA,],
            UserNotify::DESCRIPTION => [Form::FIELD_LABEL => $descriptionTitle],
            UserNotify::CONTENT_URL ,

            UserNotify::SEND_RANGE => [ Form::FIELD_LABEL => "发送范围类型" ,
                                         Form::FIELD_DATA => $sendRange,
                                         'onChange = "change_send_range();"' ],
            UserNotify::SEND_NO ,
            UserNotify::SPECIALIST_GROUP =>
                [
                    Form::FIELD_LABEL => "客服组",
                    Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                    CheckGroup::COUNT_PER_LINE => 2,
                    Form::FIELD_DATA => $specialistGroupNew,
                ]
        ];

        $form = Form::fromModelMetadata( UserNotify::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加模板消息';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/user_notify/insert?mp_user_id={$mpUserId}&community_id={$communityId}&send_type={$from}&infoid_title={$infoidTitle}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updateAction()
    {
        $id  = $this->_request->getQueryParam( UserNotify::USER_NOTIFY_ID );
        $obj = new UserNotify([ UserNotify::USER_NOTIFY_ID => $id ]);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $obj->getMpUserID()]);
        //信息编号和摘要注释说明
        $industry = $mpUser->getIndustry();
        $infoidTitle= "任务名称";
        $descriptionTitle = "通知类型";
        if($industry == IndustryType::FIANCE)
        {
            $infoidTitle = "来源";
            $descriptionTitle = "备注";
        }
        $communityId = $obj->getCommunityID();
        $from = $this->_request->get('from');
        $sendRange = UserNotifySendRangeType::getDictionary();
        if($from == "mp")
        {
            $sendRange = array_slice($sendRange,3,3);
        }
        elseif($from == "app_mp")
        {
            $sendRange = array_slice($sendRange,6,1);
        }
        elseif($from == "app_c")
        {
            $sendRange = array_slice($sendRange,7);
        }
        else
        {
            $sendRange = array_slice($sendRange,0,3);
        }

        $specialistGroup = CustomerSpecialistGroup::fetchRows(['*'],[CustomerSpecialistGroup::COMMUNITY_ID => $communityId]);
        $specialistGroupNew = [];
        foreach($specialistGroup as $key => $value)
        {
            $specialistGroupNew[$value[CustomerSpecialistGroup::GROUP_NAME]] = $value[CustomerSpecialistGroup::GROUP_NAME];
        }
        $data   = $obj->data();

        $fields = [
            UserNotify::TITLE ,
            UserNotify::INFOID => [Form::FIELD_LABEL => $infoidTitle,Form::FIELD_TAG => Form::COM_TEXT_AREA,],
            UserNotify::DESCRIPTION => [Form::FIELD_LABEL => $descriptionTitle],
            UserNotify::CONTENT_URL ,

            UserNotify::SEND_RANGE => [Form::FIELD_LABEL => "发送范围类型" ,
                                        Form::FIELD_DATA => $sendRange,
                                        'onChange = "change_send_range();"' ],
            UserNotify::SEND_NO ,
            UserNotify::SPECIALIST_GROUP  =>
                [
                    Form::FIELD_LABEL => "客服组",
                    Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                    CheckGroup::COUNT_PER_LINE => 2,
                    Form::FIELD_DATA => $specialistGroupNew,
                ],
        ];
        log_debug("222222222222222222",$fields);
        $form = Form::fromModelMetadata( UserNotify::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改模板消息';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/user_notify/update?user_notify_id={$id}&infoid_title={$infoidTitle}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function previewAction()
    {
        $id  = $this->_request->getQueryParam( UserNotify::USER_NOTIFY_ID );
        $obj = new UserNotify([ UserNotify::USER_NOTIFY_ID => $id ]);

        $data   = $obj->data();
        $fields = [
            'vip_no' => [Form::FIELD_LABEL => "会员号" ,
                          Form::FIELD_TAG => Form::COM_INPUT,
                          Form::FIELD_REQUIRED => true,],

        ];
        $form = Form::fromModelMetadata( UserNotify::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '预览模板消息';
        $form->ajaxForm = true;

        $successMessage     = '预览成功';
        $form->submitAction = "wbtAPI.call('../fcrm/user_notify/preview?user_notify_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('确认', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    private function  getJavaScript()
    {
        $bodyScript = <<<JS

            change_send_range();

            function change_send_range()
            {
                var sendRange = $("[name='send_range']").val();
                var sendNo = $("[name='send_no']").parent().parent();
                var specialistGroup = $("[name='specialist_group']").parent().parent();

                if(sendRange == 'send_by_house_no')
                {
                    sendNo.show();
                    specialistGroup.hide();
                }
                else if(sendRange == 'send_customer')
                {
                    sendNo.hide();
                    specialistGroup.show();
                }
                else
                {
                    sendNo.hide();
                    specialistGroup.hide();
                }

            }
JS;
        return $bodyScript;
    }
}
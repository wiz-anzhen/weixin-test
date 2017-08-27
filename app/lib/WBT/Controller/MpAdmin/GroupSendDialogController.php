<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;


use MP\Model\Mp\GroupSend;
use MP\Model\Mp\GroupSendItem;
use MP\Model\Mp\Community;
use MP\Model\Mp\GroupSendRangeType;
use MP\Model\Mp\MpUser;

class GroupSendDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $from= $this->_request->get('from');
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $mpUserType = $mpUser->getMpUserType();
        if($from == "mp")
        {
            $dict = [GroupSendRangeType::SEND_TO_MP_USER => "本公众账号所有微信用户"];
            $fields = [
                GroupSend::TITLE,
                GroupSend::CONTENT_TYPE => ['onChange = "change_content_value();"' ],
                GroupSend::CONTENT_VALUE,
                GroupSend::GROUP_SEND_RANGE => [ Form::FIELD_LABEL => '群发范围类型',Form::FIELD_DATA =>$dict,],
                GroupSend::GROUP_SEND_NO,
            ];

        }
        else
        {
            if($mpUserType == 1)
            {
                $dict = [GroupSendRangeType::SEND_TO_WHOLE_COMMUNITY => "本社区所有用户",GroupSendRangeType::SEND_BY_HOUSE_NO => "指定用户编号（房间编号）"];
                $fields   = [
                    GroupSend::TITLE,
                    GroupSend::CONTENT_TYPE => ['onChange = "change_content_value();"' ],
                    GroupSend::CONTENT_VALUE,
                    GroupSend::GROUP_SEND_RANGE => ['onChange = "change_send_range();"' ,Form::FIELD_DATA =>$dict,],
                    GroupSend::GROUP_SEND_NO,
                ];
            }
            else
            {
                $dict = [GroupSendRangeType::SEND_TO_WHOLE_COMMUNITY => "本社区所有用户"];
                $fields = [
                    GroupSend::TITLE,
                    GroupSend::CONTENT_TYPE => ['onChange = "change_content_value();"' ],
                    GroupSend::CONTENT_VALUE,
                    GroupSend::GROUP_SEND_RANGE => [ Form::FIELD_LABEL => '群发范围类型',Form::FIELD_DATA =>$dict,],
                    GroupSend::GROUP_SEND_NO,
                ];
            }
        }


        $form = Form::fromModelMetadata( GroupSend::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加群发消息';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/group_send/insert?mp_user_id={$mpUserId}&community_id={$communityId}&send_type={$from}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updateAction()
    {
        $id  = $this->_request->getQueryParam( GroupSend::GROUP_SEND_ID );
        $from= $this->_request->get('from');
        $obj = new GroupSend([ GroupSend::GROUP_SEND_ID => $id ]);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $obj->getMpUserID()]);
        $mpUserType = $mpUser->getMpUserType();
        $data   = $obj->data();
        if($from == "mp")
        {
            $dict = [GroupSendRangeType::SEND_TO_MP_USER => "本公众账号所有微信用户"];
            $fields = [
                GroupSend::TITLE,
                GroupSend::CONTENT_TYPE => ['onChange = "change_content_value();"' ],
                GroupSend::CONTENT_VALUE,
                GroupSend::GROUP_SEND_RANGE => [ Form::FIELD_LABEL => '群发范围类型',Form::FIELD_DATA =>$dict,],
                GroupSend::GROUP_SEND_NO,
            ];

        }
        else
        {
            if($mpUserType == 1)
            {
                $dict = [GroupSendRangeType::SEND_TO_WHOLE_COMMUNITY => "本社区所有用户",GroupSendRangeType::SEND_BY_HOUSE_NO => "指定用户编号（房间编号）"];
                $fields   = [
                    GroupSend::TITLE,
                    GroupSend::CONTENT_TYPE => ['onChange = "change_content_value();"' ],
                    GroupSend::CONTENT_VALUE,
                    GroupSend::GROUP_SEND_RANGE => ['onChange = "change_send_range();"',Form::FIELD_DATA =>$dict, ],
                    GroupSend::GROUP_SEND_NO,
                ];
            }
            else
            {
                $dict = [GroupSendRangeType::SEND_TO_WHOLE_COMMUNITY => "本社区所有用户"];
                $fields = [
                    GroupSend::TITLE,
                    GroupSend::CONTENT_TYPE => ['onChange = "change_content_value();"' ],
                    GroupSend::CONTENT_VALUE,
                    GroupSend::GROUP_SEND_RANGE => [ Form::FIELD_LABEL => '群发范围类型',Form::FIELD_DATA =>$dict,],
                    GroupSend::GROUP_SEND_NO,
                ];
            }
        }




        $form = Form::fromModelMetadata( GroupSend::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改群发';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/group_send/update?group_send_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addContentAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $groupSendID = $this->_request->get( 'group_send_id' );
        $fields   = [
            GroupSendItem::TITLE,
            GroupSendItem::DESCRIPTION,
            GroupSendItem::AUTHOR,
            GroupSendItem::PIC_URL => [
                Form::FIELD_LABEL => '图片',
                Form::FIELD_MESSAGE => "<div style=\"font-size:8px;\">图片大小最大为1M</div>",
                Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD
            ],
            GroupSendItem::CONTENT => [
                Form::FIELD_LABEL => '消息内容',
                Form::FIELD_TAG => Form::COM_RICH_TEXT, ],
            GroupSendItem::CONTENT_SOURCE_URL,
            GroupSendItem::SORT_NO,
            GroupSendItem::SHOW_COVER_PIC
        ];

        $form = Form::fromModelMetadata( GroupSendItem::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加群发消息内容';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/group_send/insert_content?mp_user_id={$mpUserId}&community_id={$communityId}&group_send_id={$groupSendID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function previewAction()
    {
        $id  = $this->_request->getQueryParam( GroupSend::GROUP_SEND_ID );
        $obj = new GroupSend([ GroupSend::GROUP_SEND_ID => $id ]);

        $data   = $obj->data();
        $fields = [
            'vip_no' => [Form::FIELD_LABEL => "会员号" ,
                Form::FIELD_TAG => Form::COM_INPUT,
                Form::FIELD_REQUIRED => true,],

        ];
        $form = Form::fromModelMetadata( GroupSend::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '预览';
        $form->ajaxForm = true;

        $successMessage     = '预览成功';
        $form->submitAction = "wbtAPI.call('../fcrm/group_send/preview?group_send_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('确认', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }


    public function updateContentAction()
    {
        $id  = $this->_request->getQueryParam( GroupSendItem::GROUP_SEND_ITEM_ID );
        $obj = new GroupSendItem([ GroupSendItem::GROUP_SEND_ITEM_ID => $id ]);

        $data   = $obj->data();
        $fields = [
            GroupSendItem::TITLE,
            GroupSendItem::DESCRIPTION,
            GroupSendItem::AUTHOR,
            GroupSendItem::PIC_URL => [
                Form::FIELD_LABEL => '图片',
                Form::FIELD_MESSAGE => "<div style=\"font-size:8px;\">图片大小最大为1M</div>",
                Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD
            ],
            GroupSendItem::CONTENT => [
                Form::FIELD_LABEL => '消息内容',
                Form::FIELD_TAG => Form::COM_RICH_TEXT, ],
            GroupSendItem::CONTENT_SOURCE_URL,
            GroupSendItem::SORT_NO,
            GroupSendItem::SHOW_COVER_PIC
        ];

        $form = Form::fromModelMetadata( GroupSendItem::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改群发内容';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/group_send/update_content?group_send_item_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    private function  getJavaScript()
    {
        $bodyScript = <<<JS

            change_content_value();
            change_send_range();
            function change_content_value()
            {
                var contentType = $("[name='content_type']").val();
                var contentValue = $("[name='content_value']").parent().parent();

                if(contentType == 'custom_text')
                {
                    contentValue.show();
                }
                else if(contentType == 'custom_news')
                {
                    contentValue.hide();
                }

            }

            function change_send_range()
            {
                var groupSendRange = $("[name='group_send_range']").val();
                var groupSendNo = $("[name='group_send_no']").parent().parent();

                if(groupSendRange == 'send_by_house_no')
                {
                   groupSendNo.show();
                }
                else
                {
                     groupSendNo.hide();
                }

            }
JS;
        return $bodyScript;
    }
}
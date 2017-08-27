<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\CustomerSpecialistGroup;


class CustomerSpecialistGroupDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [
            CustomerSpecialistGroup::GROUP_NAME,
            CustomerSpecialistGroup::COMMENT,
            "work_time_1" =>
                [
                    Form::FIELD_LABEL =>'工作时间段1',
                    Form::FIELD_TAG => Form::COM_TIME_RANGE,
                ],
            "work_time_2" =>
                [
                    Form::FIELD_LABEL =>'工作时间段2',
                    Form::FIELD_TAG => Form::COM_TIME_RANGE,
                ],
        ];

        $form = Form::fromModelMetadata( CustomerSpecialistGroup::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加电话';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/customer_specialist_group/insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updateAction()
    {
        $id  = $this->_request->getQueryParam( CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID );
        $obj = new CustomerSpecialistGroup([ CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID => $id ]);

        $data   = $obj->data();
        $workTime = explode("-",$data[CustomerSpecialistGroup::WORK_TIME]);
        $time = [];
        foreach( $workTime as $value)
        {
            $time [] = $value;
        }
        $data["work_time_1"] = $time[0];
        $data["work_time_2"] = $time[1];
        $fields   =
            [
            CustomerSpecialistGroup::GROUP_NAME,
            CustomerSpecialistGroup::COMMENT,
            "work_time_1" =>
                [
                    Form::FIELD_LABEL =>'工作时间段1',
                    Form::FIELD_TAG => Form::COM_TIME_RANGE,
                ],
            "work_time_2" =>
                [
                    Form::FIELD_LABEL =>'工作时间段2',
                    Form::FIELD_TAG => Form::COM_TIME_RANGE,
                ],
            ];

        $form = Form::fromModelMetadata( CustomerSpecialistGroup::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改电话';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/customer_specialist_group/update?customer_specialist_group_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
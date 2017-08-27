<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\CheckBox;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\ArticleType;
use WBT\Business\Weixin\CustomerSpecialistBusiness;
use Bluefin\HTML\CheckGroup;


class CustomerSpecialistDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $customerSpecialistGroupID =  $this->_request->get( CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID);
        $holiday=[];
        for($i=7;$i>0;$i--)
        {
            $day = date("Y-m-d",strtotime("-$i day")) ;
            $holiday[$day] = $day;
        }
        for($i=0;$i<21;$i++)
        {
          $day = date("Y-m-d",strtotime("+$i day")) ;
          $holiday[$day] = $day;
        }

        $fields   =
            [
            CustomerSpecialist::NAME ,
            CustomerSpecialist::PHONE,
            CustomerSpecialist::VIP_NO  ,
            CustomerSpecialist::COMMENT ,
            CustomerSpecialist::STAFF_ID ,
            CustomerSpecialist::HOLIDAY  => [
                Form::FIELD_LABEL => "休假日期",
                Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                CheckGroup::COUNT_PER_LINE => 2,
                Form::FIELD_DATA => $holiday,
                                              ]
            ];

        $form = Form::fromModelMetadata( CustomerSpecialist::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加客服专员';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/customer_specialist/insert?mp_user_id={$mpUserId}&community_id={$communityId}&customer_specialist_group_id={$customerSpecialistGroupID}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updateAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $holiday=[];
        for($i=7;$i>0;$i--)
        {
            $day = date("Y-m-d",strtotime("-$i day")) ;
            $holiday[$day] = $day;
        }
        for($i=0;$i<21;$i++)
        {
            $day = date("Y-m-d",strtotime("+$i day")) ;
            $holiday[$day] = $day;
        }
        $id  = $this->_request->getQueryParam( CustomerSpecialist::CUSTOMER_SPECIALIST_ID );
        $obj = new CustomerSpecialist([ CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $id ]);

        $data   = $obj->data();
        $fields   = [
            CustomerSpecialist::NAME ,
            CustomerSpecialist::PHONE,
            CustomerSpecialist::VIP_NO  ,
            CustomerSpecialist::VALID  ,
            CustomerSpecialist::COMMENT ,
            CustomerSpecialist::STAFF_ID ,
            CustomerSpecialist::HOLIDAY  => [
                Form::FIELD_LABEL => "休假日期",
                Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                CheckGroup::COUNT_PER_LINE => 2,
                Form::FIELD_DATA => $holiday,
            ]
        ];

        $form = Form::fromModelMetadata( CustomerSpecialist::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改电话';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/customer_specialist/update?mp_user_id={$mpUserId}&customer_specialist_id={$id}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
    //修改客服组
    public function updateGroupAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $csID  = $this->_request->getQueryParam( CustomerSpecialist::CUSTOMER_SPECIALIST_ID );
        $obj = new CustomerSpecialist([ CustomerSpecialist::CUSTOMER_SPECIALIST_ID => $csID ]);
        $data   = $obj->data();
        $dict = CustomerSpecialistBusiness::getGroupName($mpUserId, $communityId);
        $fields   =
            [
                CustomerSpecialist::NAME => [Form::FIELD_LABEL => '客服专员姓名','readonly',],
                CustomerSpecialist::CUSTOMER_SPECIALIST_GROUP_ID =>
                [
                    Form::FIELD_LABEL => '客服组名称',
                    Form::FIELD_TAG => Form::COM_COMBO_BOX,
                    Form::FIELD_DATA =>$dict,
                ],
            ];

        $form = Form::fromModelMetadata( CustomerSpecialist::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改客服组';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/customer_specialist/update_group',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}
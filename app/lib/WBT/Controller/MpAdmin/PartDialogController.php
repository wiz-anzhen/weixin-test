<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\Store;
use MP\Model\Mp\Restaurant;
use MP\Model\Mp\Part;
use MP\Model\Mp\Community;
use Bluefin\HTML\CheckGroup;

class PartDialogController extends Controller
{
    public function partAddAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $supply =  Store::fetchRows(['*'],[Store::COMMUNITY_ID => $communityId,Store::IS_DELETE => '0']);
        $supplyData = [];
        foreach($supply as $key => $value)
        {
            $supplyData[$value[Store::TITLE]."_".$value[Store::STORE_ID]] = $value[Store::TITLE];
        }

        $fields   = [ Part::TITLE,
                      Part::BOUND_STORE_ID => [ Form::FIELD_LABEL => "供应商选择",
                                                  Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                                                  CheckGroup::COUNT_PER_LINE => 1,
                                                  Form::FIELD_DATA => $supplyData, ],
                      Part::COMMENT, ];

        $form = Form::fromModelMetadata( Part::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加档口';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/part/part_insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }



    public function partUpdateAction()
    {
        $id  = $this->_request->getQueryParam( Part::PART_ID );
        $obj = new Part([ Part::PART_ID => $id ]);

        $supply =  Store::fetchRows(['*'],[Store::COMMUNITY_ID => $obj->getCommunityID(),Store::IS_DELETE => '0']);
        $supplyData = [];
        foreach($supply as $key => $value)
        {
            $supplyData[$value[Store::TITLE]."_".$value[Store::STORE_ID]] = $value[Store::TITLE];
        }
        $data   = $obj->data();
        $fields   = [ Part::TITLE,
                Part::BOUND_STORE_ID => [ Form::FIELD_LABEL => "供应商选择",
                Form::FIELD_TAG => Form::COM_CHECK_GROUP,
                CheckGroup::COUNT_PER_LINE => 1,
                Form::FIELD_DATA => $supplyData,
            ],
            Part::COMMENT, ];

        $form = Form::fromModelMetadata( Part::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改档口';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/part/part_update?part_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
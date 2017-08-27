<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;

use MP\Model\Mp\CommunityPhoneBook;


class CommunityPhoneBookDialogController extends Controller
{
    public function addAction()
    {
        $mpUserId = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId = $this->_request->get('community_id');
        $fields   = [ CommunityPhoneBook::NAME, CommunityPhoneBook::PHONE, ];

        $form = Form::fromModelMetadata( CommunityPhoneBook::s_metadata(), $fields, null,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '添加电话';
        $form->ajaxForm = true;

        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_phone_book/insert?mp_user_id={$mpUserId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        $form->addButtons( [ new Button('添加', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function updateAction()
    {
        $id  = $this->_request->getQueryParam( CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID );
        $obj = new CommunityPhoneBook([ CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID => $id ]);

        $data   = $obj->data();
        $fields   = [ CommunityPhoneBook::NAME, CommunityPhoneBook::PHONE, ];

        $form = Form::fromModelMetadata( CommunityPhoneBook::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改电话';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/community_phone_book/update?community_phone_book_id={$id}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
            new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

}
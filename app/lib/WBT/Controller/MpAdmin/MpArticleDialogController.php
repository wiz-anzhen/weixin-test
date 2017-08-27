<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\WxUserLevel;

class MpArticleDialogController extends Controller
{
    public function editUserLevelAction()
    {
        $wxUserId    = $this->_request->getQueryParam( 'wx_user_id' );
        $mpUserId    = $this->_request->getQueryParam( MpArticle::MP_USER_ID );
        $mpArticleId = $this->_request->getQueryParam( MpArticle::MP_ARTICLE_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $mpArticle   = new MpArticle([ MpArticle::MP_ARTICLE_ID => $mpArticleId ]);
        $data        = $mpArticle->data();
        $levels      = explode(',', $mpArticle->getUserLevel());
        foreach(array_keys(WxUserLevel::getDictionary()) as $level)
        {
            $data[$level] = in_array($level,$levels) ? 1 : 0;
        }
        $fields      = [ ];
        foreach(array_keys(WxUserLevel::getDictionary()) as $key)
        {
            $fields[$key] = [ Form::FIELD_LABEL => WxUserLevel::getDisplayName( $key ),
                              Form::FIELD_TAG   => Form::COM_CHECK_BOX, ];
        }

        $form = Form::fromModelMetadata( MpArticle::s_metadata(), $fields, $data,
            [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改会员等级';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/mp_article/update_user_level?wx_user_id={$wxUserId}&mp_user_id={$mpUserId}&mp_article_id={$mpArticleId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }
}
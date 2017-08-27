<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Form;
use Bluefin\HTML\Button;
use Bluefin\HTML\SimpleComponent;
use MP\Model\Mp\ArticleType;
use MP\Model\Mp\Channel;
use MP\Model\Mp\ChannelArticle;
use WBT\Business\UserBusiness;

class ChannelDialogController extends Controller
{
    public function editAction()
    {
        $mpUserId  = $this->_request->getQueryParam( 'mp_user_id' );
        $channelId = $this->_request->getQueryParam( Channel::CHANNEL_ID );
        $communityId = $this->_request->get('community_id');

        $channel = new Channel([ Channel::CHANNEL_ID => $channelId ]);
        $data    = $channel->data();

        //表单的字段
        $fields = [ Channel::TITLE => [ Form::FIELD_LABEL => '频道名称', ], ];

        $form = Form::fromModelMetadata( Channel::s_metadata(), $fields, $data, [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改频道';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/channel/update?mp_user_id={$mpUserId}&channel_id={$channelId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function addAction() {
        $username = UserBusiness::getLoginUsername();
        $mpUserID = $this->_request->get('mp_user_id');
        $communityId = $this->_request->get('community_id');
        $channelID =$this->_request->get('channel_id');

        $fields = [ Channel::TITLE => [ Form::FIELD_LABEL => '频道名称', ], ];

        $form               = Form::fromModelMetadata( Channel::s_metadata(), $fields, NULL,
            [ 'class' => 'form-horizontal' ] );
        $form->legend       = '添加频道';
        $form->ajaxForm     = TRUE;
        $successMessage     = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/channel/add?mp_user_id={$mpUserID}&community_id={$communityId}', PARAMS, function(){bluefinBH.closeDialog(FORM);bluefinBH.showInfo('{$successMessage}', location.reload())});";
        $form->addButtons( [ new Button('保存', NULL, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', NULL, [ 'class' => 'btn-cancel' ]), ] );
        echo $form;
        echo SimpleComponent::$scripts;
    }

    public function editArticleAction()
    {
        $mpUserId  = $this->_request->getQueryParam( 'mp_user_id' );
        $channelArticleId = $this->_request->getQueryParam( ChannelArticle::CHANNEL_ARTICLE_ID );
        $communityId = $this->_request->get( 'community_id');

        $channelArticle = new ChannelArticle([ ChannelArticle::CHANNEL_ARTICLE_ID => $channelArticleId ]);
        $data    = $channelArticle->data();
        $dict = ArticleType::getDictionary();
        //表单的字段
        $fields = [ ChannelArticle::ARTICLE_TITLE => [ Form::FIELD_LABEL => '文章标题', ],
                    ChannelArticle::SHARE_URL     => [ Form::FIELD_LABEL => '分享图片',
                                                       Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD, ],
                    ChannelArticle::ARTICLE_DESC  => [ Form::FIELD_LABEL => '摘要', ],

                    ChannelArticle::RELEASE_DATE  => [ Form::FIELD_LABEL => '发布日期',
                                                       Form::FIELD_TAG => Form::COM_INPUT,
                                                       Form::FIELD_ALT_NAME => '格式：YYYY-MM-DD',
                                                       Form::FIELD_VALUE => date('Y-m-d'), ],
                    ChannelArticle::ARTICLE_TYPE  =>[ Form::FIELD_LABEL => '文章类型','onchange = "article_type_on_change();"',Form::FIELD_DATA =>$dict, ],
                    ChannelArticle::ARTICLE_DETAIL => [ Form::FIELD_LABEL => '正文',Form::FIELD_TAG => Form::COM_RICH_TEXT, ],
                    ChannelArticle::ARTICLE_URL   => [ Form::FIELD_LABEL => '文章链接', ],
                    ChannelArticle::KEEP_TOP      => [ Form::FIELD_TAG => Form::COM_CHECK_BOX, ], ];

        $form = Form::fromModelMetadata( ChannelArticle::s_metadata(), $fields, $data, [ 'class' => 'form-horizontal' ] );

        $form->legend   = '修改频道文章';
        $form->ajaxForm = true;

        $successMessage     = '修改成功';
        $form->submitAction = "wbtAPI.call('../fcrm/channel/update_article?mp_user_id={$mpUserId}&channel_article_id={$channelArticleId}&community_id={$communityId}',PARAMS,function() { bluefinBH.closeDialog(FORM); bluefinBH.showInfo('{$successMessage}', function(){ location.reload(); } ) });";

        //设置表单按钮
        $form->addButtons( [ new Button('保存', null, [ 'type' => Button::TYPE_SUBMIT, 'class' => 'btn-success' ]),
                           new Button('取消', null, [ 'class' => 'btn-cancel' ]), ] );
        $form->showButtonsAtHeaderAndFooter = true;//控制顶部按钮
        $form->bodyScript = $this->getJavaScript();
        echo $form;
        echo SimpleComponent::$scripts;
    }
    private function  getJavaScript()
    {
        $bodyScript = <<<JS

            var isInit = false;

            article_type_on_change();

            function article_type_on_change(){
                var type = $("[name='article_type']").val();
                var articleUrl = $("[name='article_url']").parent().parent();
                var articleDetail = $("[name='article_detail']").parent().parent();

                if(type == 'article_ours')
                {
                    articleUrl.hide();
                    articleDetail.show();
                }

                if(type == 'article_third_party')
                {
                    articleDetail.hide();
                    articleUrl.show();
                }

                if(isInit)
                {
                   var dialogBody  = articleUrl.parent().parent().parent();

                   bluefinBH.updateDialogSize(dialogBody);
                }
                else
                {
                   isInit = true;
                }
          }
JS;
        return $bodyScript;
    }

    public function addArticleAction() {
        $username = UserBusiness::getLoginUsername();
        $mpUserID = $this->_request->get('mp_user_id');
        $channelId = $this->_request->get(ChannelArticle::CHANNEL_ID);
        $communityId = $this->_request->get( 'community_id');

        $dict = ArticleType::getDictionary();

        $fields = [ ChannelArticle::ARTICLE_TITLE => [ Form::FIELD_LABEL => '文章标题', ],
                    ChannelArticle::SHARE_URL     => [ Form::FIELD_LABEL => '分享图片',
                                                       Form::FIELD_TAG => Form::COM_IMAGE_UPLOAD, ],
                    ChannelArticle::ARTICLE_DESC  => [ Form::FIELD_LABEL => '摘要', ],

                    ChannelArticle::RELEASE_DATE  => [ Form::FIELD_LABEL => '发布日期',
                                                       Form::FIELD_TAG => Form::COM_INPUT,
                                                       Form::FIELD_ALT_NAME => '格式：YYYY-MM-DD',
                                                       Form::FIELD_VALUE => date('Y-m-d'), ],
                    ChannelArticle::ARTICLE_TYPE  =>[ Form::FIELD_LABEL => '文章类型','onChange = "article_type_on_change();"',Form::FIELD_DATA =>$dict, ],
                    ChannelArticle::ARTICLE_DETAIL => [ Form::FIELD_LABEL => '正文',
                        Form::FIELD_TAG => Form::COM_RICH_TEXT, ],
                    ChannelArticle::ARTICLE_URL   => [ Form::FIELD_LABEL => '文章链接', ],
                    ChannelArticle::KEEP_TOP      => [ Form::FIELD_TAG => Form::COM_CHECK_BOX, ], ];

        $form = Form::fromModelMetadata(ChannelArticle::s_metadata(), $fields, NULL,
            ['class' => 'form-horizontal']);
        $form->bodyScript = $this->getJavaScript();
        $form->legend = '添加频道文章';
        $form->ajaxForm = TRUE;
        $successMessage = '添加成功';
        $form->submitAction = "wbtAPI.call('../fcrm/channel/add_article?mp_user_id={$mpUserID}&channel_id={$channelId}&community_id={$communityId}', PARAMS,
        function(){ bluefinBH.closeDialog(FORM);bluefinBH.showInfo('{$successMessage}',
        function(){ location.reload();} )});";
        $form->addButtons([new Button('保存', NULL, ['type' => Button::TYPE_SUBMIT, 'class' => 'btn-success']),
            new Button('取消', NULL, ['class' => 'btn-cancel']),]);
        echo $form;
       echo SimpleComponent::$scripts;
    }
}
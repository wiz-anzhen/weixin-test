<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;

use MP\Model\Mp\WjQuestionnaire;
use MP\Model\Mp\WjUserAnswer;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\QuestionnaireBusiness;
use MP\Model\Mp\AppUser;
use WBT\Controller\WxUserControllerBase;

class QuestionController extends BaseController
{
    public function questionAction()
    {
        $mpUserId          = $this->_request->getQueryParam( WjQuestionnaire::MP_USER_ID );
        $wjQuestionnaireId = $this->_request->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );
        $communityId          = $this->_request->getQueryParam( 'community_id' );
        $phone =  $this->_request->getQueryParam( 'phone' );

        $wjQuestionnaire = new WjQuestionnaire([ WjQuestionnaire::MP_USER_ID        => $mpUserId,
                                               WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ]);
        if ($wjQuestionnaire->isEmpty())
        {
            exit('错误的问卷ID：' . $wjQuestionnaireId);
        }
        /*
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        if (!$wxUser->isEmpty()) {
            $this->_view->set('wx_user', $wxUser->data());
        } else {
            $this->_view->set('wx_user', null);
        }
        */
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if (!$appUser->isEmpty()) {
            $this->_view->set('app_user', $appUser->data());
        } else {
            $this->_view->set('app_user', null);
        }
        $this->_view->set( 'mp_user_id', $mpUserId );
        $this->_view->set( 'community_id', $communityId  );
        $this->_view->set( 'phone', $phone );
        $this->_view->set( 'questionnaire', $wjQuestionnaire->data() );
        $questions = QuestionnaireBusiness::getStructuredQuestionList( $mpUserId, $wjQuestionnaireId );
        $this->_view->set( 'questions', $questions);

    }

    public function submitAnswerAction()
    {
        $mpUserId          = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId       = $this->_request->getQueryParam( 'community_id' );
        $wjQuestionnaireId = $this->_request->getQueryParam( 'wj_questionnaire_id' );
        $phone          = $this->_request->getQueryParam( 'phone' );
        $basic             = $this->_request->get( 'basic' );
        $answer            = $this->_request->get( 'question' );

        QuestionnaireBusiness::submitAppAnswer( $communityId ,$mpUserId, $phone, $wjQuestionnaireId, $basic, $answer );
        $this->_view->set( 'title', '已成功提交!' );
        $this->_view->set( 'desc', '你提交的建议、需求或预约等，如有需要，我们的客服专员会尽快联系你。谢谢！' );

    }

    public function demoAction()
    {

    }
}

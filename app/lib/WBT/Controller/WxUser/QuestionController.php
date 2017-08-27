<?php

namespace WBT\Controller\WxUser;

use Common\Helper\BaseController;

use MP\Model\Mp\WjQuestionnaire;
use MP\Model\Mp\WjUserAnswer;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\QuestionnaireBusiness;
use WBT\Controller\WxUserControllerBase;

class QuestionController extends WxUserControllerBase
{
    public function questionAction()
    {
        $mpUserId          = $this->_request->getQueryParam( WjQuestionnaire::MP_USER_ID );
        $wjQuestionnaireId = $this->_request->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );
        $communityId          = $this->_request->getQueryParam( 'community_id' );


        $wjQuestionnaire = new WjQuestionnaire([ WjQuestionnaire::MP_USER_ID        => $mpUserId,
                                               WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ]);
        if ($wjQuestionnaire->isEmpty())
        {
            exit('错误的问卷ID：' . $wjQuestionnaireId);
        }
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        if (!$wxUser->isEmpty()) {
            $this->_view->set('wx_user', $wxUser->data());
        } else {
            $this->_view->set('wx_user', null);
        }
        $this->_view->set( 'mp_user_id', $mpUserId );
        $this->_view->set( 'community_id', $communityId  );
        $this->_view->set( 'wx_user_id', $this->_wxUserID );
        $this->_view->set( 'questionnaire', $wjQuestionnaire->data() );
        $questions = QuestionnaireBusiness::getStructuredQuestionList( $mpUserId, $wjQuestionnaireId );
        $this->_view->set( 'questions', $questions);

    }

    public function submitAnswerAction()
    {
        $mpUserId          = $this->_request->getQueryParam( 'mp_user_id' );
        $communityId       = $this->_request->getQueryParam( 'community_id' );
        $wjQuestionnaireId = $this->_request->getQueryParam( 'wj_questionnaire_id' );
        $basic             = $this->_request->get( 'basic' );
        $answer            = $this->_request->get( 'question' );

        QuestionnaireBusiness::submitAnswer( $communityId ,$mpUserId, $this->_wxUserID, $wjQuestionnaireId, $basic, $answer );
        $this->_view->set( 'title', '已成功提交!' );
        $this->_view->set( 'desc', '你提交的建议、需求或预约等，如有需要，我们的客服专员会尽快联系你。谢谢！' );

    }

    public function demoAction()
    {

    }
}

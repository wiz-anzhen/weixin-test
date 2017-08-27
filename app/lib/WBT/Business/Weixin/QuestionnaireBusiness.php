<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\AppUser;
use MP\Model\Mp\WjChoice;
use MP\Model\Mp\WjQuestion;
use MP\Model\Mp\WjQuestionnaire;
use MP\Model\Mp\WjQuestionType;
use MP\Model\Mp\WjUserAnswer;
use MP\Model\Mp\WxUser;
use WBT\Business\MailBusiness;
use MP\Model\Mp\Community;

class QuestionnaireBusiness extends BaseBusiness
{
    // 以下关于问卷
    public static function getList( array $condition, array &$paging = null, $ranking, array $outputColumns = null )
    {
        return WjQuestionnaire::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function insert($communityId, $mpUserId, $data )
    {
        $vote = new WjQuestionnaire();
        $vote->setMpUserID( $mpUserId )->setCommunityID( $communityId )
            ->apply( $data )
            ->insert();

        return true;
    }

    public static function update( $wjQuestionnaireId, $data )
    {
        $WjQuestionnaire = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ]);

        if ($WjQuestionnaire->isEmpty())
        {
            log_debug( "Could not find WjQuestionnaire($wjQuestionnaireId)" );

            return false;
        }

        $WjQuestionnaire->apply( $data )->update();

        return true;
    }

    public static function delete( $WjQuestionnaireId )
    {
        $WjQuestionnaire = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $WjQuestionnaireId ]);

        if ($WjQuestionnaire->isEmpty())
        {
            log_debug( "Could not find WjQuestionnaire($WjQuestionnaireId)" );

            return false;
        }

        $WjQuestionnaire->delete();

        // TODO 删除题目、选项和答题记录

        return true;
    }

    // 以下关于问题
    public static function getQuestionList( array $condition, array &$paging = null, $ranking,
                                            array $outputColumns = null )
    {
        return WjQuestion::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function insertQuestion( $communityId,$mpUserId, $data )
    {
        $WjQuestion = new WjQuestion();
        $WjQuestion->setMpUserID( $mpUserId )->setCommunityID($communityId)
            ->apply( $data )
            ->insert();

        return true;
    }

    public static function updateQuestion( $WjQuestionId, $data )
    {
        $WjQuestion = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $WjQuestionId ]);

        if ($WjQuestion->isEmpty())
        {
            log_debug( "Could not find WjQuestion($WjQuestionId)" );

            return false;
        }

        $WjQuestion->apply( $data )->update();

        return true;
    }

    public static function deleteQuestion( $WjQuestionId )
    {
        $WjQuestion = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $WjQuestionId ]);

        if ($WjQuestion->isEmpty())
        {
            log_debug( "Could not find WjQuestion($WjQuestionId)" );

            return false;
        }

        $WjQuestion->delete();

        // TODO 删除选项和答题记录

        return true;
    }

    // 以下关于选项
    public static function getChoiceList( array $condition, array &$paging = null, $ranking,
                                          array $outputColumns = null )
    {
        return WjChoice::fetchRows( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }

    public static function insertChoice( $communityId,$mpUserId, $data )
    {
        $wjQuestionId = $data[WjChoice::WJ_QUESTION_ID];
        $wjQuestion   = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $wjQuestionId ]);
        if ($wjQuestion->isEmpty())
        {
            return false;
        }

        $data[WjChoice::WJ_QUESTIONNAIRE_ID] = $wjQuestion->getWjQuestionnaireID();

        $WjChoice = new WjChoice();
        $WjChoice->setMpUserID( $mpUserId )->setCommunityID( $communityId )
            ->apply( $data )
            ->insert();

        return true;
    }

    public static function updateChoice( $WjChoiceId, $data )
    {
        $WjChoice = new WjChoice([ WjChoice::WJ_CHOICE_ID => $WjChoiceId ]);

        if ($WjChoice->isEmpty())
        {
            log_debug( "Could not find WjChoice($WjChoiceId)" );

            return false;
        }

        $WjChoice->apply( $data )->update();

        return true;
    }

    public static function deleteChoice( $WjChoiceId )
    {
        $WjChoice = new WjChoice([ WjChoice::WJ_CHOICE_ID => $WjChoiceId ]);

        if ($WjChoice->isEmpty())
        {
            log_debug( "Could not find WjChoice($WjChoiceId)" );

            return false;
        }

        $WjChoice->delete();

        return true;
    }

    // 前端逻辑
    public static function getStructuredQuestionList( $mpUserId, $wjQuestionnaireId )
    {
        // 选项
        $choices   = [ ];
        $selected  = [ WjChoice::WJ_CHOICE_ID, WjChoice::WJ_QUESTION_ID, WjChoice::CONTENT ];
        $condition = [ WjChoice::MP_USER_ID => $mpUserId, WjChoice::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ];
        $rows      = WjChoice::fetchRows( $selected, $condition, null, [ WjChoice::SORT_NO ] );
        if (count( $rows ) > 0)
        {
            foreach ($rows as $row)
            {
                $choices[$row[WjChoice::WJ_QUESTION_ID]][$row[WjChoice::WJ_CHOICE_ID]] = $row;
            }
        }

        // 问题
        $questions = [ ];
        $selected  = [ WjQuestion::WJ_QUESTION_ID, WjQuestion::CONTENT, WjQuestion::QUESTION_TYPE, WjQuestion::PLACEHOLDER ];
        $condition = [ WjQuestion::MP_USER_ID => $mpUserId, WjQuestion::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ];
        $rows      = WjQuestion::fetchRows( $selected, $condition, null, [ WjChoice::SORT_NO ] );
        if (count( $rows ) > 0)
        {
            foreach ($rows as $row)
            {
                $questions[$row[WjQuestion::WJ_QUESTION_ID]] = $row;

                // 选择题选项
                if (in_array( $row[WjQuestion::QUESTION_TYPE],
                    [ WjQuestionType::CHOICE_MULTIPLE, WjQuestionType::CHOICE_SINGLE ] )
                )
                {
                    if (!array_key_exists( $row[WjQuestion::WJ_QUESTION_ID], $choices ))
                    {
                        log_warn( sprintf( 'ID 为%d的选择题没有选项', $row[WjQuestion::WJ_QUESTION_ID] ) );
                    }
                    $questions[$row[WjQuestion::WJ_QUESTION_ID]]['choices'] = $choices[$row[WjChoice::WJ_QUESTION_ID]];
                }
            }
        }

        return $questions;
    }

    public static function submitAnswer( $communityId ,$mpUserId, $wxUserId, $wjQuestionnaireId, $basic, $answer )
    {
        $wjUserAnswer = new WjUserAnswer();
        $wjUserAnswer->setMpUserID( $mpUserId )->setCommunityID($communityId)
            ->setWxUserID( $wxUserId )
            ->setWjQuestionnaireID( $wjQuestionnaireId )
            ->setAnswer( json_encode( $answer ) );
        if (!empty($basic[WjUserAnswer::NAME]))
        {
            $wjUserAnswer->setName( $basic[WjUserAnswer::NAME] );
        }

        $wjUserAnswer->setGender( '' );

        if (!empty($basic[WjUserAnswer::TEL]))
        {
            $wjUserAnswer->setTel( $basic[WjUserAnswer::TEL] );
        }

        $wjUserAnswer->setEmail( $basic[WjUserAnswer::EMAIL] );

        $wjUserAnswer->insert();

        // 记录选择题选项被选次数
        foreach ($answer as $key => $questionAnswer)
        {
            $wjQuestion = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $key ]);
            if ($wjQuestion->isEmpty())
            {
                continue;
            }
            // 单选和多选
            if ($wjQuestion->getQuestionType() == WjQuestionType::CHOICE_MULTIPLE)
            {
                foreach ($questionAnswer as $choiceId)
                {
                    $wjChoice = new WjChoice([ WjChoice::WJ_CHOICE_ID => $choiceId ]);
                    if (!$wjChoice->isEmpty())
                    {
                        $wjChoice->setSelectTimes( $wjChoice->getSelectTimes() + 1 )->update();
                    }
                }
            }
            elseif ($wjQuestion->getQuestionType() == WjQuestionType::CHOICE_SINGLE)
            {
                $wjChoice = new WjChoice([ WjChoice::WJ_CHOICE_ID => $questionAnswer ]);
                if (!$wjChoice->isEmpty())
                {
                    $wjChoice->setSelectTimes( $wjChoice->getSelectTimes() + 1 )->update();
                }
            }
        }

        self::sendEmail($mpUserId, $communityId,$wxUserId, $wjQuestionnaireId, $basic, $answer);
    }

    /**
     * @param $communityId
     * @param $mpUserId
     * @param $phone
     * @param $wjQuestionnaireId
     * @param $basic
     * @param $answer
     */
    public static function submitAppAnswer( $communityId ,$mpUserId, $phone, $wjQuestionnaireId, $basic, $answer )
    {
        $wjUserAnswer = new WjUserAnswer();
        $wjUserAnswer->setMpUserID( $mpUserId )->setCommunityID($communityId)
            ->setWxUserID( $phone )
            ->setWjQuestionnaireID( $wjQuestionnaireId )
            ->setAnswer( json_encode( $answer ) );
        if (!empty($basic[WjUserAnswer::NAME]))
        {
            $wjUserAnswer->setName( $basic[WjUserAnswer::NAME] );
        }

        $wjUserAnswer->setGender( '' );

        if (!empty($basic[WjUserAnswer::TEL]))
        {
            $wjUserAnswer->setTel( $basic[WjUserAnswer::TEL] );
        }

        $wjUserAnswer->setEmail( $basic[WjUserAnswer::EMAIL] );

        $wjUserAnswer->insert();

        // 记录选择题选项被选次数
        foreach ($answer as $key => $questionAnswer)
        {
            $wjQuestion = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $key ]);
            if ($wjQuestion->isEmpty())
            {
                continue;
            }
            // 单选和多选
            if ($wjQuestion->getQuestionType() == WjQuestionType::CHOICE_MULTIPLE)
            {
                foreach ($questionAnswer as $choiceId)
                {
                    $wjChoice = new WjChoice([ WjChoice::WJ_CHOICE_ID => $choiceId ]);
                    if (!$wjChoice->isEmpty())
                    {
                        $wjChoice->setSelectTimes( $wjChoice->getSelectTimes() + 1 )->update();
                    }
                }
            }
            elseif ($wjQuestion->getQuestionType() == WjQuestionType::CHOICE_SINGLE)
            {
                $wjChoice = new WjChoice([ WjChoice::WJ_CHOICE_ID => $questionAnswer ]);
                if (!$wjChoice->isEmpty())
                {
                    $wjChoice->setSelectTimes( $wjChoice->getSelectTimes() + 1 )->update();
                }
            }
        }

        self::sendAppEmail($mpUserId, $communityId,$phone, $wjQuestionnaireId, $basic, $answer);
    }

    /**
     * @param $mpUserId
     * @param $communityId
     * @param $phone
     * @param $wjQuestionnaireId
     * @param $basic
     * @param $answer
     */

    public static function sendAppEmail($mpUserId, $communityId,$phone, $wjQuestionnaireId, $basic, $answer)
    {
        $questionnaire = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ]);
        $questions     = self::getQuestions($wjQuestionnaireId );
        $choices       = self::getChoices($wjQuestionnaireId );
        $name = $tel = $email = '';
        if (!empty($basic[WjUserAnswer::NAME]))
        {
            $name = $basic[WjUserAnswer::NAME];
        }
        if (!empty($basic[WjUserAnswer::TEL]))
        {
            $tel = $basic[WjUserAnswer::TEL];
        }
        if (!empty($basic[WjUserAnswer::EMAIL]))
        {
            $email = $basic[WjUserAnswer::EMAIL];
        }


        $mpUserName = MpUserBusiness::getMpUserName($mpUserId);
        $communityName = CommunityBusiness::getCommunityName($communityId);

        $title = sprintf( "[%s][%s][%s] 用户提交信息",$mpUserName, $communityName, $questionnaire->getTitle());
        $html  = "\n{$title}<br/><br/>";
        $html .= "姓名：" . $name . "<br/>";
        $html .= "电话：" . $tel . "<br/>";
        $html .= "邮箱：" . $email . "<br/>";
        $html .= "用户提交时间：" . date( 'Y年m月d日 H:i' ) . "<br/><br/>";

        foreach($questions as $questionId => $question)
        {
            $content = $question[WjQuestion::CONTENT];
            $html .= "{$content}<br/>";
            if (in_array($question[WjQuestion::QUESTION_TYPE], [WjQuestionType::INPUT_SINGLE, WjQuestionType::INPUT_MULTIPLE], true)) {
                if (empty($answer[$questionId])) {
                    $html .= "内容：--空--<br/><br/>";
                } else {
                    $html .= "内容：" . $answer[$questionId] . "<br/><br/>";
                }
            } elseif($question[WjQuestion::QUESTION_TYPE] == WjQuestionType::CHOICE_SINGLE) {
                $html .= "选择：<ol><li>" . $choices[$answer[$questionId]] . "</li></ol><br/>";
            } elseif ($question[WjQuestion::QUESTION_TYPE] == WjQuestionType::CHOICE_MULTIPLE) {
                $html .= "选择：<ol>";
                foreach($answer[$questionId] as $choiceId) {
                    $html .= "<li>" . $choices[$choiceId] . "</li>";
                }
                $html .= "</ol><br/>";
            }
            $html .= "\n";
        }

        $html .= "<br/><br/>此信为系统自动邮件，请不要直接回复。";


        // 发送给小区管理员
        $c =  new  Community([Community::COMMUNITY_ID => $communityId]);
        $mailAddr = [];
        $ccMailAddr = [];
        CommunityBusiness::getCommunityAdminEmail($c,$mailAddr, $ccMailAddr);
        MailBusiness::sendMailAsyn($mailAddr,$ccMailAddr, $title, $html);

        // 单独给用户发一封邮件
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $appUserMail = $appUser->getEmail();
        if(!empty($appUserMail))
        {
            MailBusiness::sendMailAsyn($appUserMail,null, $title, $html);
        }
    }

    /**
     * @param $mpUserId
     * @param $communityId
     * @param $wxUserId
     * @param $wjQuestionnaireId
     * @param $basic
     * @param $answer
     */
    public static function sendEmail($mpUserId, $communityId,$wxUserId, $wjQuestionnaireId, $basic, $answer)
    {
        $questionnaire = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ]);
        $questions     = self::getQuestions($wjQuestionnaireId );
        $choices       = self::getChoices($wjQuestionnaireId );
        $name = $tel = $email = '';
        if (!empty($basic[WjUserAnswer::NAME]))
        {
            $name = $basic[WjUserAnswer::NAME];
        }
        if (!empty($basic[WjUserAnswer::TEL]))
        {
            $tel = $basic[WjUserAnswer::TEL];
        }
        if (!empty($basic[WjUserAnswer::EMAIL]))
        {
            $email = $basic[WjUserAnswer::EMAIL];
        }


        $mpUserName = MpUserBusiness::getMpUserName($mpUserId);
        $communityName = CommunityBusiness::getCommunityName($communityId);

        $title = sprintf( "[%s][%s][%s] 用户提交信息",$mpUserName, $communityName, $questionnaire->getTitle());
        $html  = "\n{$title}<br/><br/>";
        $html .= "姓名：" . $name . "<br/>";
        $html .= "电话：" . $tel . "<br/>";
        $html .= "邮箱：" . $email . "<br/>";
        $html .= "用户提交时间：" . date( 'Y年m月d日 H:i' ) . "<br/><br/>";

        foreach($questions as $questionId => $question)
        {
            $content = $question[WjQuestion::CONTENT];
            $html .= "{$content}<br/>";
            if (in_array($question[WjQuestion::QUESTION_TYPE], [WjQuestionType::INPUT_SINGLE, WjQuestionType::INPUT_MULTIPLE], true)) {
                if (empty($answer[$questionId])) {
                    $html .= "内容：--空--<br/><br/>";
                } else {
                    $html .= "内容：" . $answer[$questionId] . "<br/><br/>";
                }
            } elseif($question[WjQuestion::QUESTION_TYPE] == WjQuestionType::CHOICE_SINGLE) {
                $html .= "选择：<ol><li>" . $choices[$answer[$questionId]] . "</li></ol><br/>";
            } elseif ($question[WjQuestion::QUESTION_TYPE] == WjQuestionType::CHOICE_MULTIPLE) {
                $html .= "选择：<ol>";
                foreach($answer[$questionId] as $choiceId) {
                    $html .= "<li>" . $choices[$choiceId] . "</li>";
                }
                $html .= "</ol><br/>";
            }
            $html .= "\n";
        }

        $html .= "<br/><br/>此信为系统自动邮件，请不要直接回复。";


        // 发送给小区管理员
        $c =  new  Community([Community::COMMUNITY_ID => $communityId]);
        $mailAddr = [];
        $ccMailAddr = [];
        CommunityBusiness::getCommunityAdminEmail($c,$mailAddr, $ccMailAddr);
        MailBusiness::sendMailAsyn($mailAddr,$ccMailAddr, $title, $html);

        // 单独给用户发一封邮件
        $wxUser = new WxUser([WxUser::WX_USER_ID => $wxUserId]);
        $wxUserMail = $wxUser->getEmail();
        if(!empty($wxUserMail))
        {
            MailBusiness::sendMailAsyn($wxUserMail,null, $title, $html);
        }
    }

    public static function getInputQuestionAnswer( $WjQuestionnaireId, $WjQuestionId, array &$paging )
    {
        $condition = [ WjUserAnswer::WJ_QUESTIONNAIRE_ID => $WjQuestionnaireId ];
        $ranking   = [ WjUserAnswer::_CREATED_AT ];
        $rets      = WjUserAnswer::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging );
        foreach ($rets as $key => $ret)
        {
            $decodedAnswer = json_decode( $ret[WjUserAnswer::ANSWER], true );
            $answer        = '';
            if (array_key_exists( $WjQuestionId, $decodedAnswer ))
            {
                $answer = $decodedAnswer[$WjQuestionId];
            }
            if ($answer == '')
            {
                $answer = '--空--';
            }

            $rets[$key][WjUserAnswer::ANSWER] = $answer;
        }

        return $rets;
    }

    public static function getUserAnswers( array $condition, array &$paging = null, $ranking = null, array $outputColumns = null )
    {
       return WjUserAnswer::fetchRowsWithCount( [ '*' ], $condition, null,$ranking, $paging,  $outputColumns );

    }

    public static function getQuestions($wjQuestionnaireId )
    {
        $query = WjQuestion::fetchRows( [ '*' ], [  WjQuestion::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ] );
        $ret   = [ ];
        if (count( $query ) > 0)
        {
            foreach ($query as $v)
            {
                $ret[$v[WjQuestion::WJ_QUESTION_ID]] = $v;
            }
        }

        return $ret;
    }

    public static function getChoices( $wjQuestionnaireId )
    {
        $query = WjChoice::fetchRows( [ WjChoice::WJ_CHOICE_ID, WjChoice::CONTENT ], [WjChoice::WJ_QUESTIONNAIRE_ID => $wjQuestionnaireId ] );
        $ret   = [ ];
        if (count( $query ) > 0)
        {
            foreach ($query as $v)
            {
                $ret[$v[WjChoice::WJ_CHOICE_ID]] = $v[WjChoice::CONTENT];
            }
        }

        return $ret;
    }

    public static function getUserAnswersExcel( $condition, $paging, $ranking )
    {
        $ret = WjUserAnswer::fetchRows( [ '*' ], $condition, null,$ranking, $paging);
        if (count($ret)>0)
        {
            foreach($ret as $k => $v)
            {
                $v[WjUserAnswer::ANSWER] = json_decode($v[WjUserAnswer::ANSWER], true);
                $ret[$k] = $v;
            }
        }

        return $ret;
    }

}
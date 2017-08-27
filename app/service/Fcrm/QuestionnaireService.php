<?php

use WBT\Business\Weixin\QuestionnaireBusiness;
use WBT\Business\MailBusiness;
use MP\Model\Mp\WjQuestion;
use MP\Model\Mp\WjChoice;
use MP\Model\Mp\WjCustomerProfile;
use MP\Model\Mp\WjQuestionnaire;
use MP\Model\Mp\WjUserAnswer;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpUser;
set_include_path( LIB . '/PHPExcel' . PATH_SEPARATOR . get_include_path() );
require_once LIB . '/PHPExcel/PHPExcel.php';
require_once 'MpUserServiceBase.php';

class QuestionnaireService extends MpUserServiceBase
{
    public function update()
    {
        $res               = array( 'errno' => 0 );
        $WjQuestionnaireId = $this->_app->request()->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );
        $data              = $this->_app->request()->getArray( [ WjQuestionnaire::TITLE,
                                                               WjQuestionnaire::HEAD_DESC,
                                                               WjQuestionnaire::TAIL_DESC,
                                                               WjQuestionnaire::CUSTOMER_PROFILE,
                                                               WjQuestionnaire::COMMENT, ] );

        if (empty($data[WjQuestionnaire::TITLE]))
        {
            return [ 'errno' => 1, 'error' => '问卷标题不能为空', ];
        }
        if (empty($data[WjQuestionnaire::HEAD_DESC]))
        {
            return [ 'errno' => 1, 'error' => '卷首语不能为空', ];
        }
        if (empty($data[WjQuestionnaire::TAIL_DESC]))
        {
            return [ 'errno' => 1, 'error' => '卷尾语不能为空', ];
        }
        if (!(new WjCustomerProfile())->validate($data[WjQuestionnaire::CUSTOMER_PROFILE]))
        {
            return [ 'errno' => 1, 'error' => '非法的用户信息值' ];
        }

        if (!QuestionnaireBusiness::update( $WjQuestionnaireId, $data ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        return $res;
    }

    public function remove()
    {
        $res               = array( 'errno' => 0 );
        $WjQuestionnaireId = $this->_app->request()->get( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );

        if (!QuestionnaireBusiness::delete( $WjQuestionnaireId ))
        {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insert()
    {
        $res      = array( 'errno' => 0 );
        $request  = $this->_app->request();
        $mpUserId = $request->get( 'mp_user_id' );
        $communityId = $request->get( 'community_id' );

        $data = $request->getArray( [ WjQuestionnaire::TITLE,
                                    WjQuestionnaire::HEAD_DESC,
                                    WjQuestionnaire::TAIL_DESC,
                                    WjQuestionnaire::CUSTOMER_PROFILE,
                                    WjQuestionnaire::COMMENT ] );

        if (empty($data[WjQuestionnaire::TITLE]))
        {
            return [ 'errno' => 1, 'error' => '问卷标题不能为空', ];
        }
        if (empty($data[WjQuestionnaire::HEAD_DESC]))
        {
            return [ 'errno' => 1, 'error' => '卷首语不能为空', ];
        }
        if (empty($data[WjQuestionnaire::TAIL_DESC]))
        {
            return [ 'errno' => 1, 'error' => '卷尾语不能为空', ];
        }
        if (!(new WjCustomerProfile())->validate($data[WjQuestionnaire::CUSTOMER_PROFILE]))
        {
            return [ 'errno' => 1, 'error' => '非法的用户信息值' ];
        }

        if (!QuestionnaireBusiness::insert( $communityId,$mpUserId, $data ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insertQuestion()
    {
        $res = array('errno' => 0);
        $request = $this->_app->request();
        $mpUserId = $request->get(WjQuestion::MP_USER_ID);
        $communityId = $request->get('community_id');
        $data                                  = $request->getArray( [ WjQuestion::QUESTION_TYPE,
                                                                     WjQuestion::CONTENT,
                                                                     WjQuestion::PLACEHOLDER,
                                                                     WjQuestion::COMMENT,
                                                                     WjQuestion::SORT_NO ] );
        $data[WjQuestion::WJ_QUESTIONNAIRE_ID] = $request->getQueryParam( WjQuestion::WJ_QUESTIONNAIRE_ID );

        $data[WjQuestion::SORT_NO] = floatval( $data[WjQuestion::SORT_NO] );

        if (empty($data[WjQuestion::QUESTION_TYPE]))
        {
            return [ 'errno' => 1, 'error' => '问题类型不能为空', ];
        }
        if (empty($data[WjQuestion::CONTENT]))
        {
            return [ 'errno' => 1, 'error' => '问题内容不能为空', ];
        }

        if (!QuestionnaireBusiness::insertQuestion( $communityId,$mpUserId, $data ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function updateQuestion()
    {
        $res          = array( 'errno' => 0 );
        $WjQuestionId = $this->_app->request()->getQueryParam( WjQuestion::WJ_QUESTION_ID );
        $data         = $this->_app->request()->getArray( [ WjQuestion::QUESTION_TYPE,
                                                          WjQuestion::CONTENT,
                                                          WjQuestion::PLACEHOLDER,
                                                          WjQuestion::COMMENT,
                                                          WjQuestion::SORT_NO ] );

        $data[WjQuestion::SORT_NO] = floatval( $data[WjQuestion::SORT_NO] );

        if (empty($data[WjQuestion::QUESTION_TYPE]))
        {
            return [ 'errno' => 1, 'error' => '问题类型不能为空', ];
        }
        if (empty($data[WjQuestion::CONTENT]))
        {
            return [ 'errno' => 1, 'error' => '问题内容不能为空', ];
        }

        if (!QuestionnaireBusiness::updateQuestion( $WjQuestionId, $data ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        return $res;
    }

    public function removeQuestion()
    {
        $res          = array( 'errno' => 0 );
        $WjQuestionId = $this->_app->request()->get( WjQuestion::WJ_QUESTION_ID );

        if (!QuestionnaireBusiness::deleteQuestion( $WjQuestionId ))
        {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function insertChoice()
    {
        $res         = array('errno' => 0);
        $request     = $this->_app->request();
        $mpUserId    = $request->get(WjChoice::MP_USER_ID);
        $communityId = $request->get('community_id');
        $data                           = $request->getArray( [ WjChoice::CONTENT,
                                                              WjChoice::COMMENT,
                                                              WjChoice::SORT_NO ] );
        $data[WjChoice::WJ_QUESTION_ID] = $request->getQueryParam( WjChoice::WJ_QUESTION_ID );

        $data[WjChoice::SORT_NO] = floatval( $data[WjChoice::SORT_NO] );

        if (empty($data[WjChoice::CONTENT]))
        {
            return [ 'errno' => 1, 'error' => '选项内容不能为空', ];
        }

        if (!QuestionnaireBusiness::insertChoice( $communityId,$mpUserId, $data ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function updateChoice()
    {
        $res        = array( 'errno' => 0 );
        $WjChoiceId = $this->_app->request()->getQueryParam( WjChoice::WJ_CHOICE_ID );
        $data       = $this->_app->request()->getArray( [ WjChoice::CONTENT,
                                                        WjChoice::COMMENT,
                                                        WjChoice::SORT_NO ] );

        $data[WjChoice::SORT_NO] = floatval( $data[WjChoice::SORT_NO] );

        if (empty($data[WjChoice::CONTENT]))
        {
            return [ 'errno' => 1, 'error' => '选项内容不能为空', ];
        }

        if (!QuestionnaireBusiness::updateChoice( $WjChoiceId, $data ))
        {
            $error        = '保存失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );

            return $res;
        }

        return $res;
    }

    public function removeChoice()
    {
        $res        = array( 'errno' => 0 );
        $WjChoiceId = $this->_app->request()->get( WjChoice::WJ_CHOICE_ID );

        if (!QuestionnaireBusiness::deleteChoice( $WjChoiceId ))
        {
            $error        = '删除失败';
            $res['errno'] = 1;
            $res['error'] = $error;
            log_error( "[error:$error]" );
        }

        return $res;
    }

    public function userAnswerDownload()
    {
        $mpUserId        = $this->_app->request()->getQueryParam( WjQuestionnaire::MP_USER_ID );
        $questionnaireId = $this->_app->request()->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );

        $questionnaire = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $questionnaireId ]);

        $condition = [WjUserAnswer::MP_USER_ID => $mpUserId,
                      WjUserAnswer::WJ_QUESTIONNAIRE_ID => $questionnaireId];

        $ranking = [WjUserAnswer::_CREATED_AT => true ];
        $paging = null;

        $userAnswers   = QuestionnaireBusiness::getUserAnswersExcel( $condition, $paging, $ranking );
        $questions     = QuestionnaireBusiness::getQuestions( $questionnaireId );
        $choices       = QuestionnaireBusiness::getChoices( $questionnaireId );

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('调查问卷统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '性别' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '手机' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '出生日期' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '电子邮件' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '提交时间' );
        $col = 6;
        foreach($questions as $question)
        {
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( $col ) . $row, $question[WjQuestion::CONTENT] );
            $col++;
        }

        foreach ($userAnswers as $userAnswer)
        {
            $row++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $userAnswer[WjUserAnswer::NAME] );
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row,
                    $userAnswer[WjUserAnswer::GENDER] == 'male' ? '男' : '女' );
            $objPHPExcel->getActiveSheet()
                ->getCell(PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row)
                ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)
                ->setValueExplicit($userAnswer[WjUserAnswer::TEL]);
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $userAnswer[WjUserAnswer::BIRTH] );
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $userAnswer[WjUserAnswer::EMAIL] );
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $userAnswer[WjUserAnswer::_CREATED_AT] );
            $col = 6;
            foreach($questions as $question)
            {
                $value = '';
                switch ($question[WjQuestion::QUESTION_TYPE]){
                    case 'input_single':
                    case 'input_multiple':
                        $value = $userAnswer['answer'][$question[WjQuestion::WJ_QUESTION_ID]];
                        break;
                    case 'choice_multiple':
                        foreach($userAnswer['answer'][$question[WjQuestion::WJ_QUESTION_ID]] as $choiceId)
                        {
                            $value .= $choices[$choiceId] . "\n";
                        }
                        break;
                    case 'choice_single':
                    default:
                        $value = $choices[$userAnswer['answer'][$question[WjQuestion::WJ_QUESTION_ID]]];
                        break;
                }
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( $col ) . $row, $value );
                $col++;
            }
        }

        $filename = $questionnaire->getTitle() . date('Y-m-d') . '调查结果统计';

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache - Control:must - revalidate, post - check = 0, pre - check = 0" );
        header( "Content-Type:application/force-download" );
        header( "Content-Type:application/vnd.ms-execl" );
        header( "Content-Type:application/octet-stream" );
        header( "Content-Type:application/download" );;
        header( 'Content-Disposition:attachment;filename="' . $filename . '.xlsx"' );
        header( "Content-Transfer-Encoding:binary" );
        $objWriter->save( 'php://output' );
    }

    public function sendExcel()
    {
        $mpUserId        = $this->_app->request()->getQueryParam( WjQuestionnaire::MP_USER_ID );
        $questionnaireId = $this->_app->request()->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );
        $communityID = $this->_app->request()->getQueryParam( WjQuestionnaire::COMMUNITY_ID );
        $questionnaire = new WjQuestionnaire([ WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $questionnaireId ]);

        $condition = [WjUserAnswer::MP_USER_ID => $mpUserId,
                      WjUserAnswer::WJ_QUESTIONNAIRE_ID => $questionnaireId];

        $ranking = [WjUserAnswer::_CREATED_AT => true ];
        $paging = null;

        $userAnswers   = QuestionnaireBusiness::getUserAnswers( $condition, $paging, $ranking);
        $questions     = QuestionnaireBusiness::getQuestions($questionnaireId );
        $choices       = QuestionnaireBusiness::getChoices( $questionnaireId );


        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('调查问卷统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '性别' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '手机' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '出生日期' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '电子邮件' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '提交时间' );
        $col = 6;
        foreach($questions as $question)
        {
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( $col ) . $row, $question[WjQuestion::CONTENT] );
            $col++;
        }

        foreach ($userAnswers as $userAnswer)
        {
            $row++;
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $userAnswer[WjUserAnswer::NAME] );
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row,
                    $userAnswer[WjUserAnswer::GENDER] == 'male' ? '男' : '女' );
            $objPHPExcel->getActiveSheet()
                ->getCell(PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row)
                ->setDataType(PHPExcel_Cell_DataType::TYPE_STRING2)
                ->setValueExplicit($userAnswer[WjUserAnswer::TEL]);
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $userAnswer[WjUserAnswer::BIRTH] );
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $userAnswer[WjUserAnswer::EMAIL] );
            $objPHPExcel->getActiveSheet()
                ->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $userAnswer[WjUserAnswer::_CREATED_AT] );
            $col = 6;
            foreach($questions as $question)
            {
                $value = '';
                switch ($question[WjQuestion::QUESTION_TYPE]){
                    case 'input_single':
                    case 'input_multiple':
                        $value = $userAnswer['answer'][$question[WjQuestion::WJ_QUESTION_ID]];
                        break;
                    case 'choice_multiple':
                        foreach($userAnswer['answer'][$question[WjQuestion::WJ_QUESTION_ID]] as $choiceId)
                        {
                            $value .= $choices[$choiceId] . "\n";
                        }
                        break;
                    case 'choice_single':
                    default:
                        $value = $choices[$userAnswer['answer'][$question[WjQuestion::WJ_QUESTION_ID]]];
                        break;
                }
                $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( $col ) . $row, $value );
                $col++;
            }
        }

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);

        $filename     = 'baobiao' . date( 'Y-m-d' ) . '调查结果统计';
        if (!is_dir(CACHE . '/baobiao')){
            mkdir(CACHE . '/baobiao');
        }
        $fullPathName = CACHE . '/baobiao/' . $filename .rand(0, 10000). '.xlsx';

        $objWriter->save( $fullPathName );

        if (!file_exists($fullPathName))
        {
            return ['errno' => 1, 'error' => '保存文件失败'];
        }
        // 发邮件
        $community = new  Community([Community::COMMUNITY_ID => $communityID]);
        if ($community->isEmpty())
        {
            return ['errno' => 1, 'error' => '没找到收件人'];
        }
        $recipients = [];
        $recipientsCc = [];

        \WBT\Business\Weixin\CommunityBusiness::getCommunityAdminEmail($community, $recipients, $recipientsCc);

        $communityName = $community->getName();
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);
        $mpName = $mpUser->getMpName();
        $content = '[' . date( 'Y-m-d' ) . ']'.'[' . $mpName . ']'.'[' . $communityName  . ']'.'['.$questionnaire->getTitle().']' . "统计结果" ;

        log_info("发送统计报表[to:".implode(',', $recipients)."][filename:".$fullPathName."]");

        //MailBusiness::sendMailToMultiRecipients( $recipients, $content, $content, $fullPathName );
        MailBusiness::sendMailAsyn($recipients, $recipientsCc,$content, $content, $fullPathName);
        return ['errno' => 0];
    }
}
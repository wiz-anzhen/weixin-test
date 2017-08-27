<?php

namespace WBT\Controller\MpAdmin;

use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WjChoice;
use MP\Model\Mp\WjQuestion;
use MP\Model\Mp\WjQuestionnaire;
use MP\Model\Mp\WjQuestionType;
use MP\Model\Mp\WjUserAnswer;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\QuestionnaireBusiness;
use WBT\Controller\CommunityControllerBase;
use Bluefin\Data\Database;

class QuestionnaireController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "questionnaire";
        parent::_init();
    }
    public function listAction()
    {
        $mpUserId = $this->_request->get( MpUser::MP_USER_ID );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $communityId = $this->_request->get( WjQuestionnaire::COMMUNITY_ID );
        $this->_view->set( 'community_id', $communityId);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $paging        = array();
        $outputColumns = WjQuestionnaire::s_metadata()->getFilterOptions();
        $ranking       = [ WjQuestionnaire::_CREATED_AT ];
        $condition = [ WjQuestionnaire::COMMUNITY_ID => $communityId,WjQuestionnaire::MP_USER_ID => $mpUserId ];
        $data          = QuestionnaireBusiness::getList($condition , $paging, $ranking,
            $outputColumns );
        $power = $this->checkChangePower("questionnaire_rw","questionnaire_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('questionnaire_rw', $checkReadPower);

        $showColumns =
            [
                WjQuestionnaire::TITLE            => [ Table::COLUMN_TITLE => '问卷标题', ],
                WjQuestionnaire::HEAD_DESC        => [ Table::COLUMN_TITLE => '卷首语', ],
                WjQuestionnaire::TAIL_DESC        => [ Table::COLUMN_TITLE => '卷尾语', ],

                WjQuestionnaire::CUSTOMER_PROFILE =>
                [
                    Table::COLUMN_TITLE      => '用户信息',
                    Table::COLUMN_CELL_STYLE => 'width: 9%',
                ],

                WjQuestionnaire::COMMENT          => [ Table::COLUMN_TITLE => '备注', ],

                WjQuestionnaire::_CREATED_AT      =>
                [
                    Table::COLUMN_TITLE      => '创建时间',
                    Table::COLUMN_CELL_STYLE => 'width:20%',
                ],

                Table::COLUMN_OPERATIONS =>
                [
                    Table::COLUMN_CELL_STYLE => 'width:14%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function(array $row)use($power)
                        {
                            $communityID = $row[WjQuestionnaire::COMMUNITY_ID];
                            $mpUserID = $row[WjQuestionnaire::MP_USER_ID];
                            $wjID = $row[WjQuestionnaire::WJ_QUESTIONNAIRE_ID];
                            $question = new Link("题目", "/mp_admin/questionnaire/question?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}");
                            $address =  new Link('问卷地址', "/wx_user/question/question?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}", ['target' => '_blank']);
                            $submit = new Link('提交情况', "/mp_admin/questionnaire/user_answer?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}");
                            $upload = new Link('下载Excel', "/api/fcrm/questionnaire/user_answer_download?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}");
                            $sendExcel = new Link('发送报表', "javascript:bluefinBH.confirm('确定要发送Excel报表吗？', function() { javascript:wbtAPI.call('../fcrm/questionnaire/send_excel?wj_questionnaire_id={$wjID}&mp_user_id={$mpUserID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('发送成功', function() { location.reload(); }); }); })");

                            $ret = $question."<br>".$address."<br>".$submit."<br>".$upload."<br>".$sendExcel;
                            return $ret;
                        }
                ],
            ];


        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] =
                [
                    Table::COLUMN_CELL_STYLE => 'width:14%',
                    Table::COLUMN_TITLE => "操作",
                    Table::COLUMN_FUNCTION => function(array $row)use($power)
                        {
                            $communityID = $row[WjQuestionnaire::COMMUNITY_ID];
                            $mpUserID = $row[WjQuestionnaire::MP_USER_ID];
                            $wjID = $row[WjQuestionnaire::WJ_QUESTIONNAIRE_ID];
                            $update =   new Link('编辑', "javascript:bluefinBH.ajaxDialog('/mp_admin/questionnaire_dialog/edit?wj_questionnaire_id={$wjID}&mp_user_id={$mpUserID}&community_id={$communityID}');");
                            $delete = new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/questionnaire/remove?wj_questionnaire_id={$wjID}&mp_user_id={$mpUserID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");
                            $question = new Link("题目", "/mp_admin/questionnaire/question?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}");
                            $address =  new Link('问卷地址', "/wx_user/question/question?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}", ['target' => '_blank']);
                            $submit = new Link('提交情况', "/mp_admin/questionnaire/user_answer?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}");
                            $upload = new Link('下载Excel', "/api/fcrm/questionnaire/user_answer_download?mp_user_id={$mpUserID}&wj_questionnaire_id={$wjID}&community_id={$communityID}");
                            $sendExcel = new Link('发送报表', "javascript:bluefinBH.confirm('确定要发送Excel报表吗？', function() { javascript:wbtAPI.call('../fcrm/questionnaire/send_excel?wj_questionnaire_id={$wjID}&mp_user_id={$mpUserID}&community_id={$communityID}', null, function(){bluefinBH.showInfo('发送成功', function() { location.reload(); }); }); })");

                            $ret = $update."<br>".$question."<br>".$address."<br>".$submit."<br>".$upload."<br>".$sendExcel;
                            if($power["delete"])
                            {
                                $ret .= "<br>".$delete;
                            }
                            return $ret;
                        } ];

        }


        $table   = Table::fromDbData( $data, $outputColumns, WjQuestionnaire::WJ_QUESTIONNAIRE_ID, $paging,
            $showColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );

        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }

    public function QuestionAction()
    {
        $this->_view->set( WjQuestion::MP_USER_ID, $this->_mpUserID );
        $WjQuestionnaireId = $this->_request->getQueryParam( WjQuestion::WJ_QUESTIONNAIRE_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $this->_view->set( WjQuestion::WJ_QUESTIONNAIRE_ID, $WjQuestionnaireId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $paging        = array();
        $outputColumns = WjQuestion::s_metadata()->getFilterOptions();
        $ranking       = [ WjQuestion::SORT_NO ];
        $data          = QuestionnaireBusiness::getQuestionList( [ WjQuestion::MP_USER_ID => $this->_mpUserID,WjQuestion::WJ_QUESTIONNAIRE_ID => $WjQuestionnaireId ],
            $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("questionnaire_rw","questionnaire_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('questionnaire_rw', $checkReadPower);
        $showColumns = [ WjQuestion::QUESTION_TYPE => [ Table::COLUMN_TITLE => '问题类型' ],
                         WjQuestion::CONTENT       => [ Table::COLUMN_TITLE => '问题内容' ],
                         WjQuestion::PLACEHOLDER   => [ Table::COLUMN_TITLE => '输入框提示语' ],
                         WjQuestion::COMMENT       => [ Table::COLUMN_TITLE => '备注', ],
                         WjQuestion::SORT_NO       => [ Table::COLUMN_TITLE => '排序', ],
                         Table::COLUMN_OPERATIONS  => [
                             Table::COLUMN_FUNCTION => function ( array $row )
                                 {
                                     if (in_array( $row[WjQuestion::QUESTION_TYPE],
                                         [ WjQuestionType::CHOICE_MULTIPLE, WjQuestionType::CHOICE_SINGLE ] )
                                     )
                                     {
                                         $ret = ' ' . new Link("选项详情", "/mp_admin/questionnaire/choice?mp_user_id={$row['mp_user_id']}&wj_question_id={$row['wj_question_id']}&community_id={$row['community_id']}");
                                     }
                                     else
                                     {
                                         $ret = ' ' . new Link("答题详情", "/mp_admin/questionnaire/input_answer?mp_user_id={$row['mp_user_id']}&wj_question_id={$row['wj_question_id']}&community_id={$row['community_id']}");
                                     }

                                     return $ret;
                                 }
                         ]
        ];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                                 Table::COLUMN_FUNCTION => function ( array $row )
                                     {
                                         $ret = new Link('编辑', "javascript:bluefinBH.ajaxDialog('/mp_admin/questionnaire_dialog/edit_question?wj_question_id={$row['wj_question_id']}&mp_user_id={$row['mp_user_id']}&community_id={$row['community_id']}');")
                                             . ' ' . new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/questionnaire/remove_question?wj_question_id={$row['wj_question_id']}&mp_user_id={$row['mp_user_id']}&community_id={$row['community_id']}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })");

                                         if (in_array( $row[WjQuestion::QUESTION_TYPE],
                                             [ WjQuestionType::CHOICE_MULTIPLE, WjQuestionType::CHOICE_SINGLE ] )
                                         )
                                         {
                                             $ret .= ' ' . new Link("选项详情", "/mp_admin/questionnaire/choice?mp_user_id={$row['mp_user_id']}&wj_question_id={$row['wj_question_id']}&community_id={$row['community_id']}");
                                         }
                                         else
                                         {
                                             $ret .= ' ' . new Link("答题详情", "/mp_admin/questionnaire/input_answer?mp_user_id={$row['mp_user_id']}&wj_question_id={$row['wj_question_id']}&community_id={$row['community_id']}");
                                         }

                                         return $ret;
                                     }
            ];
        }


        $table               = Table::fromDbData( $data, $outputColumns, WjQuestion::WJ_QUESTION_ID, $paging,
            $showColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }

    public function ChoiceAction()
    {
        $this->_view->set( WjChoice::MP_USER_ID, $this->_mpUserID );
        $WjQuestionId = $this->_request->getQueryParam( WjChoice::WJ_QUESTION_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $this->_view->set( WjChoice::WJ_QUESTION_ID, $WjQuestionId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $WjQuestion = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $WjQuestionId ]);
        $this->_view->set( WjChoice::WJ_QUESTIONNAIRE_ID, $WjQuestion->getWjQuestionnaireID() );
        $this->_view->set( 'question_content', $WjQuestion->getContent() );
        $paging        = array();
        $outputColumns = WjChoice::s_metadata()->getFilterOptions();
        $ranking       = [ WjChoice::SORT_NO ];
        $data          = QuestionnaireBusiness::getChoiceList( [ WjChoice::MP_USER_ID   => $this->_mpUserID,
                                                               WjChoice::WJ_QUESTION_ID => $WjQuestionId ],
            $paging, $ranking, $outputColumns );
        $power = $this->checkChangePower("questionnaire_rw","questionnaire_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('questionnaire_rw', $checkReadPower);
        $showColumns = [ WjChoice::CONTENT        => [ Table::COLUMN_TITLE => '选项内容' ],
                         WjChoice::SELECT_TIMES   => [ Table::COLUMN_TITLE => '选择统计', ],
                         WjChoice::COMMENT        => [ Table::COLUMN_TITLE => '备注', ],
                         WjChoice::SORT_NO        => [ Table::COLUMN_TITLE => '排序', ],];
        if($checkReadPower)
        {
            $showColumns[Table::COLUMN_OPERATIONS] = [
                                 Table::COLUMN_OPERATIONS => [
                                     new Link('编辑', "javascript:bluefinBH.ajaxDialog('/mp_admin/questionnaire_dialog/edit_choice?wj_choice_id={{this.wj_choice_id}}&mp_user_id={{this.mp_user_id}}&community_id={{this.community_id}}');"),
                                     new Link('删除', "javascript:bluefinBH.confirm('确定要删除吗？', function() { javascript:wbtAPI.call('../fcrm/questionnaire/remove_choice?wj_choice_id={{this.wj_choice_id}}&mp_user_id={{this.mp_user_id}}&community_id={{this.community_id}}', null, function(){bluefinBH.showInfo('删除成功', function() { location.reload(); }); }); })"),
                                 ],];
        }



        $table               = Table::fromDbData( $data, $outputColumns, WjChoice::WJ_CHOICE_ID, $paging,
            $showColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }

    public function InputAnswerAction()
    {
        $this->_view->set( WjChoice::MP_USER_ID, $this->_mpUserID );
        $WjQuestionId = $this->_request->getQueryParam( WjChoice::WJ_QUESTION_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $this->_view->set( WjChoice::WJ_QUESTION_ID, $WjQuestionId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $WjQuestion = new WjQuestion([ WjQuestion::WJ_QUESTION_ID => $WjQuestionId ]);
        $this->_view->set( WjChoice::WJ_QUESTIONNAIRE_ID, $WjQuestion->getWjQuestionnaireID() );
        $this->_view->set( 'question_content', $WjQuestion->getContent() );

        $paging        = array();
        $outputColumns = WjUserAnswer::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 3;
        $data          = QuestionnaireBusiness::getInputQuestionAnswer( $WjQuestion->getWjQuestionnaireID(),
            $WjQuestionId, $paging );
        $showColumns   = [ WjUserAnswer::_CREATED_AT => [ Table::COLUMN_TITLE => '提交时间', ],
                           WjUserAnswer::NAME        => [ Table::COLUMN_TITLE => '用户姓名', ],
                           WjUserAnswer::GENDER      => [ Table::COLUMN_TITLE => '性别',
                                                          Table::COLUMN_FUNCTION => function(array $row){
                                                              return $row[WjUserAnswer::GENDER] == 'male' ? '男' : '女';
                                                          } , ],
                           WjUserAnswer::TEL         => [ Table::COLUMN_TITLE => '手机', ],
                           WjUserAnswer::BIRTH       => [ Table::COLUMN_TITLE => '生日', ],
                           WjUserAnswer::EMAIL       => [ Table::COLUMN_TITLE => '电子邮件', ],
                           WjUserAnswer::ANSWER      => [ Table::COLUMN_TITLE => '答题情况', ],
        ];
        $table = Table::fromDbData( $data, $outputColumns, WjUserAnswer::WJ_USER_ANSWER_ID, $paging, $showColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set('table', $table);
    }

    public function UserAnswerAction()
    {
        $mpUserId        = $this->_request->getQueryParam( WjQuestionnaire::MP_USER_ID );
        $questionnaireId = $this->_request->getQueryParam( WjQuestionnaire::WJ_QUESTIONNAIRE_ID );
        $communityId = $this->_request->getQueryParam( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $questionnaire = new WjQuestionnaire([WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $questionnaireId]);
        $this->_view->set("questionnaire_title",$questionnaire->getTitle());
        $paging = []; // 先初始化为空
        $outputColumns = WjUserAnswer::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $ranking = [WjUserAnswer::_CREATED_AT => true ];
        $condition = [WjUserAnswer::MP_USER_ID => $mpUserId,WjUserAnswer::COMMUNITY_ID => $communityId,WjUserAnswer::WJ_QUESTIONNAIRE_ID => $questionnaireId];
        $data = QuestionnaireBusiness::getUserAnswers( $condition, $paging, $ranking, $outputColumns  );
        $questions = QuestionnaireBusiness::getQuestions($questionnaireId );
        $choices = QuestionnaireBusiness::getChoices($questionnaireId );
        $shownColumns = [
            WjUserAnswer::NAME,
            WjUserAnswer::GENDER =>
                [
                    Table::COLUMN_TITLE => '性别',
                    Table::COLUMN_FUNCTION => function (array$row)
                        {
                           if($row[WjUserAnswer::GENDER] == "male")
                           {
                               return "男";
                           }
                           elseif($row[WjUserAnswer::GENDER] == "female")
                            {
                                return "女";
                            }
                            else
                            {
                                return "";
                            }
                        }
                ],
            WjUserAnswer::TEL,
            WjUserAnswer::BIRTH,
            WjUserAnswer::EMAIL,
            WjUserAnswer::_CREATED_AT => [Table::COLUMN_TITLE => '提交时间'],
            WjUserAnswer::ANSWER      =>
            [
                Table::COLUMN_TITLE => '答题情况',
                Table::COLUMN_FUNCTION => function (array $row)use($questions,$choices)
                    {
                        $decodedAnswer = json_decode( $row[WjUserAnswer::ANSWER], true );

                        $ret = '<table style="border:0 solid white">';
                        foreach($decodedAnswer as $key => $answer)
                        {
                            $answerContent = "";
                            if(strict_in_array($questions[$key][WjQuestion::QUESTION_TYPE],[ 'input_single', 'input_multiple' ]))
                            {
                                $answerContent = $answer;
                            }
                            elseif($questions[$key][WjQuestion::QUESTION_TYPE] == 'choice_single')
                            {
                                $answerContent = $choices[$answer];
                            }
                            elseif($questions[$key][WjQuestion::QUESTION_TYPE] == 'choice_multiple')
                            {
                                $answerContent = [];
                                foreach($answer as $value)
                                {
                                    $answerContent[]= $choices[$value];
                                }
                                $answerContent = implode(",",$answerContent);
                            }
                            if(empty($answerContent))
                            {
                                $answerContent = "==空==";
                            }
                            $ret .= '<tr style="border:0 solid white">';
                            $ret .= sprintf("<td style=\"border-style:hidden\">%s&nbsp&nbsp</td>
                                             <td style=\"border-style:hidden\">%s&nbsp&nbsp</td>",                                                                 $questions[$key][WjQuestion::CONTENT],$answerContent);
                            $ret .= '</tr>';
                        }
                        $ret .= "</table>";
                        return $ret;
                    }
            ],
        ];
        $table = Table::fromDbData($data,$outputColumns,$ranking,$paging,$shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );
    }
}
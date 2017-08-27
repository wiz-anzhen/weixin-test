<?php
require_once 'MpUserServiceBase.php';

use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\Order;
use MP\Model\Mp\WjUserAnswer;
use MP\Model\Mp\WjQuestionnaire;
use MP\Model\Mp\CompanyAdmin;
use WBT\Business\Weixin\CommunityAdminBusiness;
use WBT\Model\Weibotui\User;
use WBT\Business\UserBusiness;
class CommunityAdminService extends MpUserServiceBase
{
    public function update()
    {
        $id   = $this->_app->request()->getQueryParam( CommunityAdmin::COMMUNITY_ADMIN_ID );
        $data = $this->_app->request()->getArray(
                [
                    CommunityAdmin::COMMENT,
                    CommunityAdmin::POWER,
                ]);
        return CommunityAdminBusiness::update( $id, $data );


    }

    public function insert()
    {
        $userName = $this->_app->request()->get( CommunityAdmin::USERNAME );
        $superAdmin = new SuperAdmin([SuperAdmin::USERNAME => $userName]);
        $mpAdmin  = new MpAdmin([MpAdmin::USERNAME => $userName]);
        $companyAdmin = new CompanyAdmin([CompanyAdmin::USERNAME => $userName]);
        $adminUserName =  UserBusiness::getLoginUser()->getUsername();
        $request = $this->_app->request();

        $data    = $request->getArray(
            [
            CommunityAdmin::MP_USER_ID,
            CommunityAdmin::USERNAME,
            CommunityAdmin::COMMENT,
            CommunityAdmin::POWER,
            CommunityAdmin::COMMUNITY_ID,
            ] );
        $data[CommunityAdmin::ADMIN_USERNAME] = $adminUserName;
        $communityAdmin = new CommunityAdmin(
            [
            CommunityAdmin::COMMUNITY_ID =>  $data[CommunityAdmin::COMMUNITY_ID],
            CommunityAdmin::USERNAME => $data[CommunityAdmin::USERNAME],
            ]);

        if(! $communityAdmin->isEmpty())
        {
            return ['errno' => 1, 'error' => $userName.'已经是本区小区管理员，不能重复添加'];
        }


        if($superAdmin->isEmpty() && $mpAdmin->isEmpty()  && $companyAdmin->isEmpty())
        {
             $password = $this->_app->request()->get('password');
             $ret = CommunityAdminBusiness::insert($data,$password,$userName);
             return $ret;
        }
        else
        {
            return ['errno' => 1, 'error' => $userName.'已经是超级管理员或公众帐号管理员或二级管理员，不能添加为小区管理员'];
        }


    }

    public function delete()
    {
        $id = $this->_app->request()->get( CommunityAdmin::COMMUNITY_ADMIN_ID );

        return CommunityAdminBusiness::delete( $id );
    }

    public function updatePassword()
    {
        $userName = $this->_app->request()->get( CommunityAdmin::USERNAME );
        $password = $this->_app->request()->get( "password");

        return CommunityAdminBusiness::updatePassword($password,$userName);

    }

    public function saveNotifyTime()
    {
        $userName = $this->_app->request()->get( "username" );
        $time = date('Y-m-d H:i:s');

        return CommunityAdminBusiness::saveNotifyTime($time,$userName);

    }

    public function saveAnswerId()
    {
        $userName = $this->_app->request()->get( "username" );
        $answerID = WjUserAnswer::fetchColumn([WjUserAnswer::WJ_USER_ANSWER_ID]);
        rsort($answerID);
        return CommunityAdminBusiness::saveAnswerId($answerID[0],$userName);

    }


    public function countNotify()
    {
        $userName = $this->_app->request()->get( "username" );
        $communityID = $this->_app->request()->get( "community_id" );

        $communityAdmin = new CommunityAdmin([CommunityAdmin::USERNAME => $userName]);
        if(!$communityAdmin->isEmpty())
        {
            //获得新订单数量
            $orderNotifyTime = $communityAdmin->getOrderNotifyTime();
            $orderTime = Order::fetchColumn([Order::CREATE_TIME],[Order::COMMUNITY_ID => $communityID]);
            $orderNotify = [];
            foreach($orderTime as $time )
            {

                if(strtotime($time)>strtotime($orderNotifyTime))
                {
                    $orderNotify[] = $time;
                }
            }
            $orderNotify = count($orderNotify);

            //获得闻卷调查数量
            $answerNotifyID = $communityAdmin->getAnswerNotifyID();
            $answers = WjUserAnswer::fetchRows([ '*' ],[WjUserAnswer::COMMUNITY_ID => $communityID ]);
            $answerNotify = [];
            $answerQuestionnaireId = [];
            foreach($answers as $answer)
            {
                if($answer[WjUserAnswer::WJ_USER_ANSWER_ID]>$answerNotifyID)
                {
                    $answerNotify[] = $answer[WjUserAnswer::WJ_USER_ANSWER_ID];
                    $answerQuestionnaireId[] = $answer[WjUserAnswer::WJ_QUESTIONNAIRE_ID];
                }
            }
            $answerNotify = count($answerNotify);
            $answerContent = '';
            if($answerNotify > 0)
            {
                $answerContent = WjQuestionnaire::fetchRows(['*'],[WjQuestionnaire::WJ_QUESTIONNAIRE_ID => $answerQuestionnaireId]);
            }

            $notify['order_notify'] = $orderNotify;
            $notify['answer_notify'] = $answerNotify;
            $notify['content'] = $answerContent;
            $notify = json_encode($notify);
            return $notify;
        }
        else
        {
            return "0";
        }


    }

}
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
use WBT\Business\Weixin\CompanyAdminBusiness;
use WBT\Model\Weibotui\User;
use MP\Model\Mp\MpUser;
class CompanyAdminService extends MpUserServiceBase
{
    public function update()
    {
        $data = $this->_app->request()->getArray(
            [
                CompanyAdmin::POWER,
            ]);
        $id   = $this->_app->request()->getQueryParam( CompanyAdmin::COMPANY_ADMIN_ID );
        $companyAdmin = new CompanyAdmin([CompanyAdmin::COMPANY_ADMIN_ID=>$id]);
        $adminUserName = $companyAdmin->getUsername();

        $communityAdmin = new CommunityAdmin([CommunityAdmin::ADMIN_USERNAME => $adminUserName]);
        $dataPowerArr = explode(',',$data[CompanyAdmin::POWER]);
        if(!$communityAdmin->isEmpty())
        {
            $userNameArr = CommunityAdmin::fetchRows(['*'],[CommunityAdmin::ADMIN_USERNAME=>$adminUserName]);
            $newPower = '';
            foreach($userNameArr as $userName)
            {
               $powerArr = explode(',',$userName[CommunityAdmin::POWER]);
               foreach($powerArr as $power)
               {
                   if(strstr($power,'_rw'))
                   {
                       $pow = substr($power,0,-3);
                       if(strict_in_array($pow,$dataPowerArr))
                       {
                           $newPower .= ','.$power;
                       }
                   }elseif(strstr($power,'_d'))
                   {
                       $pow = substr($power,0,-2);
                       if(strict_in_array($pow,$dataPowerArr))
                       {
                           $newPower .= ','.$power;
                       }
                   }elseif(strstr($power,'_r'))
                   {
                       $pow = substr($power,0,-2);
                       if(strict_in_array($pow,$dataPowerArr))
                       {
                           $newPower .= ','.$power;
                       }
                   }else
                   {
                       if(strict_in_array($power,$dataPowerArr))
                       {
                           $newPower .= ','.$power;
                       }
                   }
               }
                $newData = [CommunityAdmin::COMMENT => $data[CommunityAdmin::COMMENT],CommunityAdmin::POWER=>$newPower];

                CommunityAdminBusiness::update($userName[CommunityAdmin::COMMUNITY_ADMIN_ID],$newData);
            }

        }

        return ['errno' => 0];
    }

    public function insert()
    {
        $userName = $this->_app->request()->get( CompanyAdmin::USERNAME );
        $mpUserId = $this->_app->request()->get( 'mp_user_id' );

        $communityAdmin = new CommunityAdmin([CommunityAdmin::USERNAME => $userName]);
        if(!$communityAdmin->isEmpty())
        {
            return ['errno' => 1, 'error' => $userName.'已经是小区管理员，不能添加为小区管理员'];
        }

        $superAdmin = new SuperAdmin([SuperAdmin::USERNAME => $userName]);
        $mpAdmin  = new MpAdmin([MpAdmin::USERNAME => $userName]);

        $request = $this->_app->request();

        $data    = $request->getArray(
            [
                CompanyAdmin::MP_USER_ID,
                CompanyAdmin::MP_NAME,
                CompanyAdmin::USERNAME,
                CompanyAdmin::COMMENT,
                CompanyAdmin::POWER,
            ] );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserId]);

        $data[CompanyAdmin::MP_NAME] = $mpUser->getMpName();
        if($superAdmin->isEmpty() && $mpAdmin->isEmpty())
        {
             $password = $this->_app->request()->get('password');
             $companyAdmin = new CompanyAdmin([CommunityAdmin::MP_USER_ID=>$mpUserId]);
             if($companyAdmin->isEmpty())
             {
                 $ret = CompanyAdminBusiness::insert($data,$password,$userName);
                 return $ret;
             }
             else
             {
                 return ['errno' => 1, 'error' => $mpUser->getMpName().'该公共账号已经拥有二级管理员帐号,不能再添加'];
             }

        }
        else
        {
            return ['errno' => 1, 'error' => $userName.'已经是超级管理员或公众帐号管理员，不能添加为小区管理员'];
        }
    }

    public function delete()
    {
        $id = $this->_app->request()->get( CompanyAdmin::COMPANY_ADMIN_ID );

        return CompanyAdminBusiness::delete( $id );
    }

    public function updatePassword()
    {
        $userName = $this->_app->request()->get( CompanyAdmin::USERNAME );
        $password = $this->_app->request()->get( "password");

        return CompanyAdminBusiness::updatePassword($password,$userName);

    }


}